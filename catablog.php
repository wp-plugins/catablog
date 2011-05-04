<?php
/*
Plugin Name: CataBlog
Plugin URI: http://catablog.illproductions.com
Description: CataBlog is a comprehensive and effortless tool that helps you create, organize and share catalogs, stores, galleries and portfolios on your blog.
Version: 1.2.6
Author: Zachary Segal
Author URI: http://catablog.illproductions.com/about/

Copyright 2011  Zachary Segal  (email : zac@illproductions.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}





function catablog_activate() {
	
	// check if PHP is version 5
	if (version_compare(phpversion(), '5.0.0', '<')) {
		exit(__("<strong>CataBlog</strong> requires <strong>PHP 5</strong> or better running on your web server. You're version of PHP is to old, please contact your hosting company or IT department for an upgrade. Thanks.", 'catablog'));
	}
	
	// check if GD Library is loaded in PHP
	if (!extension_loaded('gd') || !function_exists('gd_info')) {
	    exit(__("<strong>CataBlog</strong> requires that the <strong>GD Library</strong> be installed on your web server's version of PHP. Please contact your hosting company or IT department for more information. Thanks.", 'catablog'));
	}
	
	// check WordPress version
	if (version_compare(get_bloginfo('version'), '3.1', '<')) {
		exit(__("<strong>CataBlog</strong> requires <strong>WordPress 3.1</strong> or above. Please upgrade WordPress or contact your system administrator about upgrading.", 'catablog'));
	}
	
}
register_activation_hook( __FILE__, 'catablog_activate' );




function catablog_load_plugin() {
	
	/** LOAD PLUGIN **/
	// load necessary libraries
	require('lib/CataBlog.class.php');
	require('lib/CataBlogItem.class.php');
	require('lib/CataBlogDirectory.class.php');

	// create CataBlog class and hook into WordPress
	global $wp_plugin_catablog_class;
	$wp_plugin_catablog_class = new CataBlog();
	$wp_plugin_catablog_class->registerWordPressHooks();


	// Declare a function for use in custom wordpress templates
	function catablog_show_items($category=null, $template=null, $sort='menu_order', $order='asc', $operator='IN') {
		global $wp_plugin_catablog_class;
		$wp_plugin_catablog_class->frontend_init(true);
		echo $wp_plugin_catablog_class->frontend_content(array('category'=>$category, 'template'=>$template, 'sort'=>$sort, 'order'=>$order, 'operator'=>$operator));
	}

}
catablog_load_plugin();