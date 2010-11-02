<?php
/*
Plugin Name: CataBlog
Plugin URI: http://catablog.illproductions.com
Description: CataBlog is a comprehensive and effortless tool that allows you to create catalogs and galleries for your blog.
Version: 0.9.5
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


// check if PHP is version 5 and if the plugin can run
$phpversion = phpversion();
if (strpos($phpversion, '-') !== false) {
	$phpversion = substr($phpversion,0,strpos($phpversion, '-'));
}
if (floatval($phpversion) < 5.0) {
  die("<strong>CataBlog</strong> requires <strong>PHP 5</strong> or better running on your web server. 
		You're version of PHP is to old, please contact your hosting company or IT department for an upgrade.
		Thanks.");
}


// check if GD Library is loaded
if (!extension_loaded('gd') || !function_exists('gd_info')) {
    die("<strong>CataBlog</strong> requires the <strong>GD Library</strong> be installed on your
		web server's version of PHP. Please contact your hosting company or IT department for
		more information. Thanks.");
}


// check WordPress version
global $wp_version;
if (version_compare($wp_version, '2.6', '<=')) {
	die("<strong>CataBlog</strong> requires <strong>WordPress version 2.6</strong> or above. Please
	upgrade WordPress or contact your system administrator about upgrading.");
}


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
	echo $wp_plugin_catablog_class->frontend_content(array('category'=>$category));
}











