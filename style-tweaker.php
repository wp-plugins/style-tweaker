<?php 
/*
Plugin Name: Style Tweaker
Plugin URI: http://www.4-14.org.uk/style-tweaker
Description: Allows custom CSS style tweaking within Wordpress. You can either make your tweaks permanently visible for all users, or you can experiment with new tweaks without them being made public.
Author: Mark Barnes
Version: 0.10
Author URI: http://www.4-14.org.uk/
Copyright (c) 2008 Mark Barnes
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

define('ST_CURRENT_VERSION', '0.10');
add_action('init', 'st_init'); 							// Initialise the plugin
add_action('admin_menu', 'st_add_admin_menus');			// Add menus to admin
add_action('wp_head', 'st_add_headers', 0);				// Add CSS and javascript to frontend
add_action('wp_head', 'wp_print_styles', 9); 			// Force styles output in header
add_action('shutdown', 'st_add_custom_warning');		// Add warning if custom styles being used

// Initialisation
function st_init () {
	global $st_domain;
	$st_domain = 'style-tweaker';
	load_plugin_textdomain($st_domain, '', 'style-tweaker');
	$plugin_url = trailingslashit(get_option('siteurl').'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)));
	wp_register_style('st_style', $plugin_url.'style.php', false, get_option('st_style_update_timestamp'));
}

// Returns the appropriate option name for the current user and theme
function st_option_name ($current_user_only=FALSE, $theme = '') {
	global $user_ID;
	if ($theme == '')
		$theme = get_option('template');
	if ($current_user_only)
		return 'st_style_'.$theme.'_'.$user_ID;
	else
		return 'st_style_'.$theme;
}

// Adds the style to the HEAD when appropriate
function st_add_headers() {
	if (get_option('st_style_generic').get_option(st_option_name()).get_option(st_option_name(TRUE)) != '')
		wp_enqueue_style ('st_style');
}

// Add sub-menu in admin
function st_add_admin_menus() {
	global $st_domain;
	add_submenu_page('themes.php', __('Style Tweaker', $st_domain), __('Style Tweaker', $st_domain), 'edit_themes', 'style-tweaker/edit.php', 'st_edit_styles');
}

// Allows user to edit the styles
function st_edit_styles () {
	global $st_domain;
	if (function_exists('current_user_can')&&!current_user_can('edit_themes'))
			wp_die(__("You do not have the correct permissions to tweak the styles", $st_domain));
	if ($_POST['save'] || $_POST['clear']) {
		$generic_style = $_POST['generic_style'];
		$public_style = $_POST['public_style'];
		$private_style = $_POST['private_style'];
	    if($_POST['clear']){
		    delete_option('st_generic_style');
		    delete_option(st_option_name());
			delete_option(st_option_name(TRUE));
		} else {
			update_option('st_style_generic', base64_encode($generic_style));
			update_option(st_option_name(), base64_encode($public_style));
			update_option(st_option_name(TRUE), base64_encode($private_style));
			update_option('st_style_update_timestamp', strtotime('now'));
			echo '<div id="message" class="updated fade"><p><b>';
			_e('Tweaks saved successfully.', $st_domain);
			echo '</b></p></div>';
		}
	}
	?>
	<form method="post">
	<div class="wrap">
		<h2><?php _e('Style Tweaker', $st_domain) ?></h2>
		<br/>
		<table border="0" class="widefat">
			<tr>
				<td align="right"><b><?php _e('Private style tweaks', $st_domain) ?>: </b><br />
					<?php _e('These styles will display for the current user, in the current theme only. If there is anything entered in this section, the public style tweaks will NOT display for the current user. You can use this section for testing new tweaks.', $st_domain) ?></td>
				<td>
					<?php st_build_textarea('private_style', get_option(st_option_name(TRUE))) ?>
				</td>
			</tr>
			<tr>
				<td align="right"><b><?php _e('Public style tweaks', $st_domain) ?>: </b><br />
					<?php _e('These styles will display for all users, in the current theme only', $st_domain) ?></td>
				<td>
					<?php st_build_textarea('public_style', get_option(st_option_name())) ?>
				</td>
			</tr>
			<tr>
				<td align="right" width="25%"><b><?php _e('Generic style tweaks', $st_domain) ?>: </b><br />
					<?php _e('These styles will display for all users, in all themes', $st_domain) ?></td>
				<td>
					<?php st_build_textarea('generic_style', get_option('st_style_generic')) ?>
				</td>
			</tr>
		</table>				
		<p class="submit"><input type="submit" name="clear" value="<?php _e('Clear these styles', $st_domain) ?>"  />&nbsp;<input type="submit" name="save" value="<?php _e('Save', $st_domain) ?> &raquo;" /></p> 
	</div>		
	</form>
<?php 
}

// Show the textarea input
function st_build_textarea($name, $html) {
	$out = '<textarea name="'.$name.'" cols="75" rows="20" style="width:100%">';
	$out .= stripslashes(str_replace('\r\n', "\n", base64_decode($html))); 
	$out .= '</textarea>';
	echo $out;
}

function st_add_custom_warning () {
	if (get_option(st_option_name(TRUE)) != '')
		if (stristr($_SERVER['PHP_SELF'], '/wp-admin/') == FALSE)
			echo "\r\t<div id=\"st-custom-style-warning\" style=\"position:fixed !important; right:0 !important; top: 0 !important; background: #f3ff6d !important; color: red !important; font-weight: bold !important; padding: 3px 5px !important; border: 2px solid red !important; display:block !important; font-size: 12px !important; width: 150px !important; visibility: visible;\">You are currently using a <a href =\"".get_option('siteurl')."/wp-admin/themes.php?page=style-tweaker/edit.php\">custom style</a> not visible to other users.</div>";
}
?>