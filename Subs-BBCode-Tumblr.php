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
		'content' => '',
		'validate' => 'BBCode_Tumblr_Validate',
		'disabled_content' => '<a href="$1" class="bbc_link" target="_blank">$1</a>',
	);

	// Format: [Tumblr]{Tumblr URL}[/Tumblr]
	$bbc[] = array(
		'tag' => 'tumblr',
		'type' => 'unparsed_content',
		'content' => '',
		'validate' => 'BBCode_Tumblr_Validate',
		'disabled_content' => '<a href="$1" class="bbc_link" target="_blank">$1</a>',
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

	// Validate that this is a Tumblr URL:
	if (empty($data))
		return ($tag['content'] = $txt['tumblr_no_post_id']);
	$data = strtr(trim($data), array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;
	if (!preg_match('#(http|https)://(|.+?.)\.tumblr.com/(post|image)/(\d+)(|/(.+?))#i', $data, $parts))
		return ($tag['content'] = $txt['tumblr_no_post_id']);

	// If not already cached, get the title and then build the HTML we need to show it:
	$md5 = md5($data);
	if (($tag['content'] = cache_get_data('tumblr2_' . $md5, 86400)) == null)
	{
		require_once($sourcedir . '/Subs-Package.php');
		$title = fetch_web_data($data);
		$pattern = '#<title>(.+?)</title>#i';
		if (!$title || preg_match($pattern, $title, $matches) != true)
			return ($tag['content'] = $txt['tumblr_no_post_id']);
		$title = $matches[1];
		$tag['content'] = '<a class="embedly-card" href="' . $data . '">' . $title . '</a><script async src="//cdn.embedly.com/widgets/platform.js" charset="UTF-8"></script>';
		cache_put_data('tumblr2_' . $md5, $tag['content'], 86400);
	}
}

function BBCode_Tumblr_Embed(&$message)
{
	$pattern = '#(http|https)://(|.+?.\.)tumblr.com/(post|image)/(\d+)(|/(.+?))#i';
	$message = preg_replace($pattern, '[tumblr]$1://$2tumblr.com/$3/$4$5[/tumblr]', $message);
}

?>