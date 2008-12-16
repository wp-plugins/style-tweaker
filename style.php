<?php
// Outputs tweaked styles as a CSS file
$wordpressRealPath = str_replace('\\', '/', dirname(dirname(dirname(dirname(__FILE__)))));
if (file_exists($wordpressRealPath.'/wp-load.php')) {
	require_once($wordpressRealPath.'/wp-load.php');
} else {
	require_once($wordpressRealPath.'/wp-config.php');
}

// Prints the required style
function st_print_style ($style_name) {
	$style = get_option($style_name);
	if ($style != '')
		echo stripcslashes(base64_decode($style)."\r");	
}

// Sets correct HTTP headers
header('Content-Type: text/css');
$lastModifiedDate = get_option('st_style_update_timestamp');
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModifiedDate) {
	if (php_sapi_name()=='CGI') {
		Header("Status: 304 Not Modified");
	} else {
		Header("HTTP/1.0 304 Not Modified");
	}
} else {
	$gmtDate = gmdate("D, d M Y H:i:s\G\M\T",$lastModifiedDate);
	header('Last-Modified: '.$gmtDate);
}

// Prints the three styles when necessary
remove_action('shutdown', 'st_add_custom_warning');
st_print_style('st_style_generic');
if (get_option(st_option_name(TRUE)) == '')
	st_print_style(st_option_name());
else
	st_print_style(st_option_name(TRUE));
?>