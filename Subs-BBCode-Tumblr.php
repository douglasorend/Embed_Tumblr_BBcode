<?php
/**********************************************************************************
* Subs-BBCode-Tumblr.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/
if (!defined('SMF')) 
	die('Hacking attempt...');

function BBCode_Tumblr(&$bbc)
{
	// Format: [Tumblr width=x]{Tumblr URL}[/Tumblr]
	$bbc[] = array(
		'tag' => 'tumblr',
		'type' => 'unparsed_content',
		'parameters' => array(
			'width' => array('optional' => true, 'match' => '(\d+)'),
			'length' => array('optional' => true, 'match' => '(\d+)'),
			'padding' => array('optional' => true, 'match' => '(\d+)'),
		),			
		'content' => '{width}|{length}|{padding}',
		'validate' => 'BBCode_Tumblr_Validate',
		'disabled_content' => '$1',
	);

	// Format: [Tumblr]{Tumblr URL}[/Tumblr]
	$bbc[] = array(
		'tag' => 'tumblr',
		'type' => 'unparsed_content',
		'content' => '0|0|10',
		'validate' => 'BBCode_Tumblr_Validate',
		'disabled_content' => '',
	);
}

function BBCode_Tumblr_Button(&$buttons)
{
	$buttons[count($buttons) - 1][] = array(
		'image' => 'tumblr',
		'code' => 'tumblr',
		'description' => 'Tumblr',
		'before' => '[tumblr]',
		'after' => '[/tumblr]',
	);
}

function BBCode_Tumblr_Validate(&$tag, &$data, &$disabled)
{
	global $sourcedir, $txt;

	// Get the pieces of the URL necessary to access the Tumblr API:
	if (empty($data))
		return;
	$data = strtr(trim($data), array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;
	$pattern = '#(http|https)://(.+?).tumblr.com/post/(\d+)(|/(.+?))#i';
	preg_match($pattern, $data, $parts);
	if (!isset($parts[3]))
		return $txt['tumblr_no_post_id'];
	$url = (isset($parts[1]) ? $parts[1] : 'http') . '://' . (isset($parts[2]) ? $parts[2] : 'www') . '.tumblr.com/api/read?id=' . $parts[3];
	$md5 = md5($url);

	// IF not already cached, parse the Tumblr post into the two pieces we need:
	if (($pieces = cache_get_data('tumblr_' . $md5, 3600)) == null)
	{
		require_once($sourcedir . '/Subs-Package.php');
		$search_results = fetch_web_data($url);
		$pattern = '~<' . '\?xml\sversion="\d+\.\d+"\sencoding=".+?"\?' . '>\s*(<tumblr(.+?)>.+?</tumblr>)~is';
		if (!$search_results || preg_match($pattern, $search_results, $matches) != true)
			return ($tag['content'] = '');
		$search_results = $matches[1];
		loadClassFile('Class-Package.php');
		$results = new xmlArray($search_results, false);
		if (!$results->exists('tumblr'))
			return ($tag['content'] = '');
		$results = $results->path('tumblr[0]');
		if (!$results->exists('posts'))
			return ($tag['content'] = '');
		$results = $results->path('posts[0]');
		if (!$results->exists('post'))
			return ($tag['content'] = '');
		$results = $results->path('post[0]');
		$pieces['title'] = $results->fetch('regular-title');
		$pieces['body'] = $results->fetch('regular-body');
		$pieces['url'] = $data;
		cache_put_data('tumblr_' . $md5, $pieces, 3600);
	}

	// Output the tumblr post to the user, styled correctly:
	list($width, $length, $padding) = explode('|', $tag['content']);
	$padding = (empty($padding) ? 10 : $padding);
	if (!empty($length))
	{
		$longer = strlen($pieces['body']) > $length;
		$pieces['body'] = substr($pieces['body'], 0, $length);
	}
	$tag['content'] = '<div style="' . (!empty($width) ? 'width: ' . $width . 'px;' : '') . ' background-color: #ffffff; padding: ' . $padding . 'px;">
		<div class="cat_bar"><h3 class="catbg"><a href="' . $pieces['url'] . '">' . $pieces['title'] . '</a></h3></div>
		' . $pieces['body'] . (!empty($longer) ? '...' : '') . '</br><div style="text-align: right;"><a target=frame2 href="' . $url . '">' . $txt['tumblr_read_more'] . '</a></div></div>';
}

?>