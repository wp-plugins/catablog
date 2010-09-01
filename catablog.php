<?php
/*
Plugin Name: CataBlog
Plugin URI: http://catablog.illproductions.net
Description: CataBlog allows you to create a catalog of items for your blog. Upload Images and then add titles, links and descriptions for each item. Now it's super easy to keep track of all your stuff.
Version: 0.8.5
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
require('lib/CataBlog.class.php');
require('lib/CataBlog_Directory.class.php');

$catablog = new CataBlog();
$catablog->registerWordPressHooks();















