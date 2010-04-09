<?php
/**
 * @package Disc History
 * @author Zachary Segal
 * @version 0.65
 */
/*
Plugin Name: CataBlog
Plugin URI: http://www.illproductions.net
Description: Lets you create a discography of albums or any other catalog you want and display it easily on any page you want.
Author: Zachary Segal
Version: 0.65
Author URI: http://www.illproductions.net
*/

/*  Copyright 2009  Zachary Segal  (email : zac@illproductions.net)

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



// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') ) {
	define( 'WP_CONTENT_URL', get_bloginfo('wpurl') . '/wp-content');
}
if ( !defined('WP_CONTENT_DIR') ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( !defined('WP_PLUGIN_URL') ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
}


require(WP_CONTENT_DIR . '/../wp-config.php');



// Register globals
global $wpdb;
global $now; $now = mysql2date('Y-m-d', current_time('mysql'));
global $user_level; $user_level = 8;

global $disc_history; $disc_history = array();
	$disc_history['db_version']   = "0.1";
	$disc_history['db_tables'] = $wpdb->prefix . "disc_history";
	$disc_history['version']   = "0.1";







function catablog_admin_head() {
	echo '<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/catablog/css/admin.css" />';
	echo '<script type="text/javascript" src="' . WP_PLUGIN_URL .'/catablog/scripts/admin.js' . '"></script>';
	echo '<script type="text/javascript" src="' . WP_PLUGIN_URL .'/catablog/scripts/jquery-ui.js' . '"></script>';
}

function catablog_admin_menu() {
	add_menu_page("CataBlog &rsaquo; View", "CataBlog", 8, __FILE__, "", WP_PLUGIN_URL . "/catablog/images/cb_icon_16.png");
	add_submenu_page(__FILE__, "CataBlog &rsaquo; View", 'Manage', 8, __FILE__, 'catablog_view');
	add_submenu_page(__FILE__, "CataBlog &rsaquo; Edit", 'Add New', 8, 'catablog_edit', 'catablog_edit');
	add_submenu_page(__FILE__, "CataBlog &rsaquo; Options", 'Options', 8, 'catablog_options', 'catablog_options');
}

function catablog_view() {
	global $wpdb;
	$table = $wpdb->prefix."catablog";
	$query = $wpdb->prepare("SELECT * FROM $table `catablog` ORDER BY `order`", array());
	$results = $wpdb->get_results($query);
	require('admin-view.php');
}

function catablog_edit() {
	global $wpdb;
	
	if (isset($_POST['save'])) {
		// save record
		require('admin-save.php');
		
		$query = $wpdb->prepare("SELECT * FROM $table `catablog` ORDER BY `order`", array());
		$results = $wpdb->get_results($query);
		require('admin-view.php');
	}
	elseif (isset($_REQUEST['action'])) {
		if ($_REQUEST['action'] == 'remove') {
			require('admin-delete.php');
			require('admin-view.php');
		}
		elseif ($_REQUEST['action'] == 'edit') {
			// edit record
			if (isset($_REQUEST['id'])) {
				$update = true;
				$id = $_REQUEST['id'];

				$table = $wpdb->prefix."catablog";
				$query = $wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id);
				$result = $wpdb->get_row($query, ARRAY_A);
			}
			require('admin-edit.php');
		}
	} else {
		// view record
		$update = false;
		$result = array('id'=>'', 'image'=>'', 'title'=>'', 'description'=>'');
		require('admin-edit.php');
	}	
}

function catablog_options() {
	if (isset($_POST['save'])) {
		if (true) {
			update_option('image_size', $_REQUEST['image_size']);
			echo "<div id='message' class='updated'>";
			echo "	<strong>Changes Saved</strong>";
			echo "</div>";
		}
	}
	
	require('admin-options.php');
}






function catablog_install() {
	global $wpdb;
	
	// create the plugin's database
	$table = $wpdb->prefix."catablog";
	$structure = "CREATE TABLE $table (
		`id` INT(9) NOT NULL AUTO_INCREMENT,
		`order` INT(9) NOT NULL,
		`image` VARCHAR(255) NOT NULL,
		`title` VARCHAR(255) NOT NULL,
		`link` TEXT,
		`description` TEXT,
		UNIQUE KEY id (id)
	);";
	
	$wpdb->query($structure);
	// $wpdb->query("INSERT INTO $table(`order`, `image`, `title`, `description`) VALUES (1, 'test.jpg', 'test', 'wowzers!')");
	
	
	// add initial option values
	update_option('image_size', 200);
	
	// make directory for image storage
	mkdir(WP_CONTENT_DIR . "/catablog");
}

function catablog_uninstall() {
	global $wpdb;
	
	$table = $wpdb->prefix."catablog";
	$tear_down = "DROP TABLE $table;";
	
	$wpdb->query($tear_down);
	
	delete_option('image_size');
	
	$mydir = WP_CONTENT_DIR . "/catablog"; 
	if (is_dir($mydir)) {
		$d = dir($mydir);
		while ($entry = $d->read()) { 
			if ($entry != "." && $entry != "..") {
				unlink($mydir . '/' . $entry); 
			} 
		} 
		$d->close(); 

		rmdir($mydir);		
	}

}






function catablog_head() {
	echo '<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/catablog/css/catablog.css" />';
}

function catablog_place($content) {
	global $wpdb;
	
	$table   = $wpdb->prefix."catablog";
	$query   = "SELECT * FROM $table ORDER BY `order`";
	$results = $wpdb->get_results($query);
	
	$size = get_option('image_size');
	$ml = ($size + 10) . 'px';
	
	$cb_catalog = "";
	foreach ($results as $result) {
		$link_start = "";
		$link_end   = "";
		if (strlen($result->link) > 0) {
			$link_start = "<a href='".$result->link."'>";
			$link_end   = "</a>";
		}
		
		$cb_catalog .= "<div class='catablog_row'>";
		$cb_catalog .= $link_start."<img src='".get_bloginfo('wpurl').'/wp-content/catablog/'.$result->image."' class='catablog_image' width='$size' height='$size' />".$link_end;
		$cb_catalog .= "<h4 class='catablog_title' style='margin-left:$ml'>";
		$cb_catalog .=   $link_start;
		$cb_catalog .=   htmlspecialchars($result->title, ENT_QUOTES, 'UTF-8');
		$cb_catalog .=   $link_end;
		$cb_catalog .= "</h4>";
		
		$cb_catalog .= "<p class='catablog_description' style='margin-left:$ml'>".nl2br(htmlspecialchars($result->description, ENT_QUOTES, 'UTF-8'))."</p>";
		$cb_catalog .= "</div>";
	}
	
	echo str_replace('[catablog]', $cb_catalog, $content);
}



// Admin Hooks
register_activation_hook(__FILE__, 'catablog_install');
register_deactivation_hook(__FILE__, 'catablog_uninstall');
add_action('admin_head', 'catablog_admin_head');
add_action('admin_menu', 'catablog_admin_menu');


// Frontend Hooks
add_action('wp_head', 'catablog_head');
add_filter('the_content', 'catablog_place', 15);






// 
// 
// // MISC Functions
// function gigpress_load_jquery()	{
// 	// If we're in the admin, and on a GigPress page, load up jQuery
// 	if ( is_admin() && strpos($_SERVER['QUERY_STRING'], 'gigpress') !== false ) {
// 		wp_enqueue_script('jquery');
// 	}
// }