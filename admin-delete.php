<?php

$id = $_REQUEST['id'];
$table = $wpdb->prefix."catablog";


// delete image
$query = $wpdb->prepare("SELECT image FROM $table WHERE `id`=%d", $id);
$db_filename = $wpdb->get_var($query);
$file_path = WP_CONTENT_DIR . '/catablog/' . $db_filename;
if (is_file($file_path)) {
	unlink($file_path);
}


// delete record
$query = $wpdb->prepare("DELETE FROM $table WHERE `id`=%d;", $id);
$wpdb->query($query);
$wpdb->flush();

$query = $wpdb->prepare("SELECT * FROM $table catablog", array());
$results = $wpdb->get_results($query);



echo "removed an item from the database";