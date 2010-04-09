<?php 

function string2slug($string) {
	$string = strtolower(trim($string));
	$string = preg_replace('/[^a-z0-9-.]/', '-', $string);
	$string = preg_replace('/-+/', "-", $string);
	
	return $string;
}

global $wpdb;

$new_image = $_FILES['image']['error'] != 4;
if ($new_image) {
	$tmp = $_FILES['image']['tmp_name'];
	$new = WP_CONTENT_DIR . '/catablog/' . string2slug($_FILES['image']['name']);
	
	if (is_file($new)) {
		echo "filename already exists, please rename uploaded image";
		exit;
	}
	
	if (is_uploaded_file($tmp)) {
		list($width, $height) = getimagesize($tmp);
		$final_size = get_option('image_size');

		$canvas = imagecreatetruecolor($final_size, $final_size);
		$bg_color = imagecolorallocate($canvas, 0, 0, 0);
		imagefill($canvas, 0, 0, $bg_color);

		$upload = imagecreatefromjpeg($tmp);
		imagecopyresampled($canvas, $upload, 0, 0, 0, 0, $final_size, $final_size, $width, $height);

		imagejpeg($canvas, $new, 80);		
	}
	
}

$table = $wpdb->prefix."catablog";
$image = string2slug($_FILES['image']['name']);
$title = $_REQUEST['title'];
$link  = $_REQUEST['link'];
$desc  = $_REQUEST['description'];
$order = $_REQUEST['order'];

if (isset($_REQUEST['id'])) {
	// old entry, need to update row
	$id = $_REQUEST['id'];
	if ($new_image) {
		$wpdb->update($table, array('order'=>$order, 'image'=>$image, 'title'=>$title, 'link'=>$link, 'description'=>$desc), array('id'=>$id), array('%d', '%s', '%s', '%s', '%s'), array('%d'));
	}
	else {
		$wpdb->update($table, array('order'=>$order, 'title'=>$title, 'link'=>$link, 'description'=>$desc), array('id'=>$id), array('%d', '%s', '%s', '%s'), array('%d'));		
	}

} else {
	// new entry, need to insert row
	$wpdb->insert($table, array('order'=>$order, 'image'=>$image, 'title'=>$title, 'link'=>$link, 'description'=>$desc), array('%d', '%s', '%s', '%s', '%s'));
}

?>

<div id='message' class='updated'>
	<strong>Changes Saved</strong>
</div>