<?php
/*
Plugin Name: CataBlog
Plugin URI: http://catablog.illproductions.com
Description: CataBlog is a comprehensive and effortless tool that allows you to create catalogs and galleries for your blog.
Version: 1.1.6
Author: Zachary Segal
Author URI: http://catablog.illproductions.com/about/

Copyright 2009  Zachary Segal  (email : zac@illproductions.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


/** CHECK PHP **/
// check if PHP is version 5
if (version_compare(phpversion(), '5.0.0', '<')) {
  die("<strong>CataBlog</strong> requires <strong>PHP 5</strong> or better running on your web server. 
		You're version of PHP is to old, please contact your hosting company or IT department for an upgrade.
		Thanks.");
}
// check if GD Library is loaded in PHP
if (!extension_loaded('gd') || !function_exists('gd_info')) {
    die("<strong>CataBlog</strong> requires that the <strong>GD Library</strong> be installed on your
		web server's version of PHP. Please contact your hosting company or IT department for
		more information. Thanks.");
}
// check if mbstring Library is loaded in PHP
if (!extension_loaded('mbstring') || !function_exists('mb_strlen')) {
    die("<strong>CataBlog</strong> requires that the <strong>MultiByte String Library</strong> be installed on your
		web server's version of PHP. Please contact your hosting company or IT department for
		more information. Thanks.");	
}



/** CHECK WORDPRESS **/
// check WordPress version
if (version_compare(get_bloginfo('version'), '3.0', '<')) {
	die("<strong>CataBlog</strong> requires <strong>WordPress 3.0</strong> or above. Please
	upgrade WordPress or contact your system administrator about upgrading.");
}
// check if uploads directory is set and writable
$upload_directory = wp_upload_dir();
if ($upload_directory['error']) {
	die("<strong>CataBlog</strong> could not detect your upload directory or it is not writable by PHP. 
	Please contact your hosting company or IT department for more information. Thanks.");
}



/** LOAD PLUGIN **/
// load necessary libraries
require('lib/CataBlog.class.php');
require('lib/CataBlogItem.class.php');
require('lib/CataBlogDirectory.class.php');

// create CataBlog class and hook into WordPress
$wp_plugin_catablog_class = new CataBlog();
$wp_plugin_catablog_class->registerWordPressHooks();

// Declare a function for use in custom wordpress templates
function catablog_show_items($category=null) {
	global $wp_plugin_catablog_class;
	$wp_plugin_catablog_class->frontend_init(true);
	echo $wp_plugin_catablog_class->frontend_content(array('category'=>$category));
}











