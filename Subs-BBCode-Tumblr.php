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
		),			
		'content' => '{width}',
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
	global $sourcedir, $txt, $context, $settings;
	static $js_included = false;

	// Set up for a run through the bbcode:
	if (empty($data))
		return ($tag['content'] = $txt['tumblr_no_post_id']);
	$data = strtr($data, array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;

	// Is this a Tumblr post URL?  If not, abort:
	$tag['content'] = $txt['tumblr_no_post_id'];
	if (!preg_match('#(http|https)://(|.+?.)\.tumblr\.com/(post|image)/(\d+)(|/(.+?))#i', $data, $parts))
		return;

	// Create the html content and add related Javascript stuff if not already done:
	list($width) = explode('|', $tag['content']);
	$tag['content'] = '<a class="embedly-card" href="' . $data . '"' . (!empty($width) ? ' data-card-width="' . $width . '"' : '') . '>' . $data . '</a>';
	if (empty($js_included))
	{
		$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		do {
			var  = document.getElementsByClassName("card");
		} while (var !== null);
		document.write("<link rel=\"stylesheet\" type=\"text/css\" href=\"' . $settings['default_theme_url'] . '/css/BBCode-Tumblr.css\" />");
	</script>';
		$js_included = true;
	}

	// Are we running Tapatalk?  If so, return ONLY the link!!!
	if (defined('IN_MOBIQUO'))
		$tag['content'] = '<a href="' . $data . '">' . $data . '</a>';
	// Otherwise, add the Facebook URL if admin says so:
	elseif (!empty($modSettings['tumblr_include_link']))
		$tag['content'] .= '<br /><a href="' . $data . '">' . $data . '</a>';
}

function BBCode_Tumblr_LoadTheme()
{
	global $context;
	$context['html_headers'] .= '
	<script type="text/javascript" src="//cdn.embedly.com/widgets/platform.js" charset="UTF-8"></script>';
}

function BBCode_Tumblr_Embed(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	$replace = (strpos($cache_id, 'sig') !== false ? '[url]$0[/url]' : '[tumblr]$0[/tumblr]');
	$pattern = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)([a-zA-Z0-9_-]+)\.tumblr\.com/(post|image)/(\d+)(/([a-zA-Z0-9_-]+)?|)(\#([a-zA-Z0-9_-]+)|)+\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	if (strpos($cache_id, 'sig') !== false)
		$message = preg_replace('#\[tumblr.*\](.*)\[\/tumblr\]#i', '[url]$1[/url]', $message);
}

?>