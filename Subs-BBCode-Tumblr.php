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

	if (empty($data))
		return ($tag['content'] = $txt['tumblr_no_post_id']);
	$data = strtr($data, array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;
	if (preg_match('#(http|https)://(|.+?.)\.tumblr\.com/(post|image)/(\d+)(|/(.+?))#i', $data, $parts))
		$tag['content'] = '<a class="embedly-card" href="' . $data . '">' . $data . '</a>';
	else
		$tag['content'] = $txt['tumblr_no_post_id'];
}

function BBCode_Tumblr_Embed(&$message)
{
	$pattern = '#(|\[tumblr(|.+?)\](([<br />]+)?))(http|https):\/\/([a-zA-Z0-9_-]+)\.tumblr\.com/(post|image)/(\d+)(/([a-zA-Z0-9_-]+)?|)(\#([a-zA-Z0-9_-]+)|)(([<br />]+)?)(\[/tumblr\]|)#i';
	$message = preg_replace($pattern, '[tumblr$2]$5://$6.tumblr.com/$7/$8$9$11[/tumblr]$13', $message);
	$pattern = '#\[code(|(.+?))\](|.+?)\[tumblr(|.+?)\](.+?)\[/tumblr\](|.+?)\[/code\]#i';
	$message = preg_replace($pattern, '[code$1]$3$5$6[/code]', $message);
}

function BBCode_Tumblr_LoadTheme()
{
	global $context;
	$context['html_headers'] .= '
	<script async src="//cdn.embedly.com/widgets/platform.js" charset="UTF-8"></script>';
}

?>