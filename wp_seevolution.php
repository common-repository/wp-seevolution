<?php
/*
Plugin Name: Heatmaps and Analytics by SeeVolution
Plugin URI: http://www.seevolution.com
Description: See a detailed, real-time traffic breakdown and click heatmap of your website's visitors compiled and displayed immediately in a movable, transparent overlay on your website.
Version: 2.6
Author: SeeVolution, Inc.
Author URI: http://www.seevolution.com
*/

/*  Copyright 2012  SeeVolution, Inc. (http://www.seevolution.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}


load_plugin_textdomain('wpsi');

define('WPSI_TRACKING_CODE', '<!-- BEGIN: SeeVolution --><img width=0 height=0 src="https://c.svlu.net/pixel.aspx"/><script type="text/javascript" src="https://assets.seevolution.com/cjs.aspx"></script><!-- END: SeeVolution  -->');
define('WPSI_CONVERSION_TRACKING_CODE', '<!-- BEGIN: SeeVolution --><script>svluArea="Conversion";</script><img width=0 height=0 src="https://c.svlu.net/pixel.aspx" /><script type="text/javascript" src="https://c.svlu.net/JInitScript.js"></script><!-- END: SeeVolution -->');

function wpsi_init() {
	add_action('wp_footer', 'wpsi_add_code');
}
add_action('init', 'wpsi_init');

function wpsi_add_code() {
	global	$post;
	$tracking_code = WPSI_TRACKING_CODE;
	$conversion_code = WPSI_CONVERSION_TRACKING_CODE;
	$conversion_page_id = get_option('wpsi_conversion_page_id');
	echo $tracking_code;
	if ($conversion_page_id && $conversion_code && $post->ID == $conversion_page_id) {
		echo $conversion_code;
	}
}

function wpsi_request_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {

			case 'wpsi_TODO':
				break;

		}
	}
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {

			case 'wpsi_update_settings':
				check_admin_referer('wpsi_update_settings');
				wpsi_update_settings();
				wp_redirect(admin_url('options-general.php?page='.basename(__FILE__).'&updated=true'));
				die();
				break;
		}
	}
}
add_action('init', 'wpsi_request_handler');


function wpsi_resources() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {

			case 'wpsi_admin_js':
				wpsi_admin_js();
				break;
			case 'wpsi_admin_css':
				wpsi_admin_css();
				die();
				break;
		}
	}
}
add_action('init', 'wpsi_resources', 1);


function wpsi_admin_js() {
	header('Content-type: text/javascript');
// TODO
	die();
}

if (is_admin() && $_GET['page'] == basename(__FILE__)) {
	wp_enqueue_script('wpsi_admin_js', admin_url('?cf_action=wpsi_admin_js'), array('jquery'));
}


function wpsi_admin_css() {
	header('Content-type: text/css');
?>
fieldset.options div.option {
	background: #EAF3FA;
	margin-bottom: 8px;
	padding: 10px;
}
fieldset.options div.option label {
	display: block;
	float: left;
	font-weight: bold;
	margin-right: 10px;
	width: 150px;
}
fieldset.options div.option span.help {
	color: #666;
	font-size: 11px;
	margin-left: 8px;
}
<?php
	die();
}

if (is_admin() && $_GET['page'] == basename(__FILE__)) {
	wp_enqueue_style('wpsi_admin_css', site_url('?cf_action=wpsi_admin_css'), array(), '1.0','screen');
}


/*
$example_settings = array(
	'key' => array(
		'type' => 'int',
		'label' => 'Label',
		'default' => 5,
		'help' => 'Some help text here',
	),
	'key' => array(
		'type' => 'select',
		'label' => 'Label',
		'default' => 'val',
		'help' => 'Some help text here',
		'options' => array(
			'value' => 'Display'
		),
	),
);
*/
$wpsi_settings = array(
	// 'wpsi_tracking_code' => array(
	// 	'type' => 'textarea',
	// 	'label' => 'Seevolution Tracking Code',
	// 	'default' => '',
	// 	'help' => 'Enter your Seevolution Tracking Code here',
	// ),
	// 'wpsi_conversion_code' => array(
	// 	'type' => 'textarea',
	// 	'label' => 'Seevolution Conversion Tracking Code',
	// 	'default' => '',
	// 	'help' => 'Enter your Seevolution Conversion Tracking Code here',
	// ),
	'wpsi_conversion_page_id' => array(
		'type' => 'int',
		'label' => 'Conversion Page ID',
		'default' => '',
		'help' => '(Optional) Enter the Post ID for you Conversion Page here. The Conversion Tracker code will only appear on the page with this id.',
	),
	// 'wpsi_' => array(
	// 	'type' => 'select',
	// 	'label' => '',
	// 	'default' => '',
	// 	'help' => '',
	// 	'options' => array(
	// 		'' => ''
	// 	),
	// ),

);

function wpsi_setting($option) {
	$value = get_option($option);
	if (empty($value)) {
		global $wpsi_settings;
		$value = $wpsi_settings[$option]['default'];
	}
	return $value;
}

function wpsi_admin_menu() {
	if (current_user_can('manage_options')) {
		add_options_page(
			__('Setup SeeVolution Codes', 'wpsi')
			, __('SeeVolution', 'wpsi')
			, 10
			, basename(__FILE__)
			, 'wpsi_settings_form'
		);
	}
}
add_action('admin_menu', 'wpsi_admin_menu');

function wpsi_plugin_action_links($links, $file) {
	$plugin_file = basename(__FILE__);
	if (basename($file) == $plugin_file) {
		$settings_link = '<a href="options-general.php?page='.$plugin_file.'">'.__('Settings', 'wpsi').'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'wpsi_plugin_action_links', 10, 2);

if (!function_exists('cf_settings_field')) {
	function cf_settings_field($key, $config) {
		$option = get_option($key);
		if (empty($option) && !empty($config['default'])) {
			$option = $config['default'];
		}
		$label = '<label for="'.$key.'">'.$config['label'].'</label>';
		$help = '<span class="help">'.$config['help'].'</span>';
		switch ($config['type']) {
			case 'select':
				$output = $label.'<select name="'.$key.'" id="'.$key.'">';
				foreach ($config['options'] as $val => $display) {
					$option == $val ? $sel = ' selected="selected"' : $sel = '';
					$output .= '<option value="'.$val.'"'.$sel.'>'.htmlspecialchars($display).'</option>';
				}
				$output .= '</select>'.$help;
				break;
			case 'textarea':
				$output = $label.'<textarea name="'.$key.'" id="'.$key.'">'.htmlspecialchars($option).'</textarea>'.$help;
				break;
			case 'string':
			case 'int':
			default:
				$output = $label.'<input name="'.$key.'" id="'.$key.'" value="'.htmlspecialchars($option).'" />'.$help;
				break;
		}
		return '<div class="option">'.$output.'<div class="clear"></div></div>';
	}
}

function wpsi_settings_form() {
	global $wpsi_settings;


	print('
<div class="wrap">
	<h2>'.__('Setup SeeVolution Codes', 'wpsi').'</h2>
	<form id="wpsi_settings_form" name="wpsi_settings_form" action="'.admin_url('options-general.php').'" method="post">
		<input type="hidden" name="cf_action" value="wpsi_update_settings" />
		<fieldset class="options">
	');
	foreach ($wpsi_settings as $key => $config) {
		echo cf_settings_field($key, $config);
	}
	print('
		</fieldset>
		<p class="submit">
			<input type="submit" name="submit" value="'.__('Save Settings', 'wpsi').'" class="button-primary" />
		</p>
	');
	wp_nonce_field('wpsi_update_settings');
	print('
	</form>
</div>
	');
}

function wpsi_update_settings() {
	if (!current_user_can('manage_options')) {
		return;
	}
	global $wpsi_settings;
	foreach ($wpsi_settings as $key => $option) {
		$value = '';
		switch ($option['type']) {
			case 'int':
				$value = intval($_POST[$key]);
				break;
			case 'select':
				$test = stripslashes($_POST[$key]);
				if (isset($option['options'][$test])) {
					$value = $test;
				}
				break;
			case 'string':
			case 'textarea':
			default:
				$value = stripslashes($_POST[$key]);
				break;
		}
		update_option($key, $value);
	}
}

//a:23:{s:11:"plugin_name";s:13:"WP SeeVolution";s:10:"plugin_uri";s:23:"http://wphelpcenter.com";s:18:"plugin_description";s:59:"Allows for the installation of SeeVolution code in WordPress";s:14:"plugin_version";s:2:".5";s:6:"prefix";s:4:"wpsi";s:12:"localization";s:4:"wpsi";s:14:"settings_title";s:22:"Setup SeeVolution Codes";s:13:"settings_link";s:10:"SeeVolution";s:4:"init";s:1:"1";s:7:"install";b:0;s:9:"post_edit";b:0;s:12:"comment_edit";b:0;s:6:"jquery";b:0;s:6:"wp_css";b:0;s:5:"wp_js";b:0;s:9:"admin_css";s:1:"1";s:8:"admin_js";s:1:"1";s:8:"meta_box";b:0;s:15:"request_handler";b:0;s:6:"snoopy";b:0;s:11:"setting_cat";b:0;s:14:"setting_author";b:0;s:11:"custom_urls";b:0;}

?>