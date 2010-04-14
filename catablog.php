<?php
/*
Plugin Name: CataBlog
Plugin URI: http://catablog.illproductions.net
Description: CataBlog allows you to catalog any item you want and show it to the world through your blog. Upload Images and then add titles, links and descriptions of the images and watch how easy it is to keep track of all your important stuff.
Version: 0.7.0
Author: Zachary Segal
Author URI: http://catablog.illproductions.net/about/

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





// load libraries
require_once('lib/CataBlog.class.php');
$catablog = new CataBlog();
$catablog->registerWordPressHooks();















