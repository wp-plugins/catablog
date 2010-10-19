<?php
/*
Plugin Name: CataBlog
Plugin URI: http://catablog.illproductions.com
Description: CataBlog allows you to create a catalog of items for your blog. Upload Images and then add titles, links and descriptions for each item. Now it's super easy to keep track of all your stuff.
Version: 0.9.0
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
  die("<strong>CataBlog</strong> requires <strong>PHP5</strong> or better running on your web server. 
		You're version of PHP is to old, please contact your hosting company or IT department for an upgrade.
		Thanks.");
}







// load libraries
require('lib/CataBlog.class.php');
require('lib/CataBlog_Directory.class.php');

$catablog = new CataBlog();
$catablog->registerWordPressHooks();















