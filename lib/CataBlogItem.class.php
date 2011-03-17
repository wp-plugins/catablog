<?php


class CataBlogItem {
	
	// item properties that directly relate to 
	// values in the WordPress Database
	private $id           = null;
	private $title        = "";
	private $description  = "";
	private $image        = "";
	private $sub_images   = array(); 
	private $order        = 0;
	private $link         = "";
	private $price        = 0;
	private $product_code = "";
	private $quantity     = "";
	private $size         = "";
	private $prices       = "";
	private $categories   = array();
	
	// object values not considered item properties
	// this will be skipped in getParameterArray() method
	private $_options            = array();
	private $_main_image_changed = false;
	private $_sub_images_changed = false;
	private $_old_images         = array();
	private $_wp_upload_dir      = "";
	private $_custom_post_name   = "catablog-items";
	private $_custom_tax_name    = "catablog-terms";
	private $_post_meta_name     = "catablog-post-meta";
	
	// construction method
	public function __construct($post_parameters=null) {
		$this->_options = get_option('catablog-options');
		
		$wp_directories = wp_upload_dir();
		$this->_wp_upload_dir = $wp_directories['basedir'];
		
		if (isset($post_parameters)) {
			foreach ($this->getParameterArray() as $param_name) {
				$this->{$param_name} = $post_parameters[$param_name];
			}
		}
	}
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - FACTORY METHODS
	*****************************************************/
	public static function getItem($id) {
		$post = get_post($id);
		
		if ($post == false) {
			return null;
		}
		
		$item = new CataBlogItem();
		
		if ($post->post_type != $item->getCustomPostName()) {
			return null;
		}
		
		$category_ids = array();
		$terms = get_the_terms($post->ID, $item->_custom_tax_name);
		if (is_array($terms)) {
			foreach ($terms as $term) {
				$category_ids[$term->term_id] = $term->name;
			}			
		}
				
		$item->id           = $post->ID;
		$item->title        = $post->post_title;
		$item->description  = $post->post_content;
		$item->categories   = $category_ids;
		$item->order        = $post->menu_order;
		
		$meta = get_post_meta($post->ID, $item->_post_meta_name, true);
		$item->processPostMeta($meta);
		
		return $item;
	}
	
	public static function getItemIds() {
		$items = array();
		
		$cata  = new CataBlogItem();
		
		$params = array(
			'post_type'=> $cata->getCustomPostName(), 
			'orderby'=>'menu_order',
			'order'=>'ASC',
			'numberposts' => -1,
		);
		
		$posts = get_posts($params);
		$ids = array();
		foreach ($posts as $post) {
			$ids[] = $post->ID;
		}
		
		return $ids;
	}
	
	public static function getItems($category=false, $load_categories=true, $offset=0, $limit=-1) {
		if ($category === NULL) {
			return array();
		}
		
		$items = array();
		
		$cata  = new CataBlogItem();
		$params = array(
			'post_type'=> $cata->getCustomPostName(), 
			'orderby'=>'menu_order',
			'order'=>'ASC',
			'offset'=>$offset,
			'numberposts' => $limit,
		);
		
		if ($category !== false) {
			$custom_name = $cata->getCustomTaxName();
			$params[$custom_name] = $category;
			
			// currently does not work :(
			// $term = $category;
			// $params['tax_query']['taxonomy'] = $cata->getCustomTaxName();
			// $params['tax_query']['field']    = 'slug';
			// $params['tax_query']['terms']    = array($term);
		}
		
		$posts = get_posts($params);
		
		
		// return an array of CataBlogItems
		foreach ($posts as $post) {
			
			$item = new CataBlogItem();
			
			$item->id           = $post->ID;
			$item->title        = $post->post_title;
			$item->description  = $post->post_content;
			$item->categories   = array();
			$item->order        = $post->menu_order;
			
			$item_cats = array();
			if ($load_categories) {
				$category_ids = array();
				$terms = get_the_terms($post->ID, $item->_custom_tax_name);
				if (is_array($terms)) {
					foreach ($terms as $term) {
						$category_ids[$term->term_id] = $term->name;
					}
				}
				$item->categories = $category_ids;
			}
			
			$meta = get_post_meta($post->ID, $item->_post_meta_name, true);
			$item->processPostMeta($meta);
			
			$items[] = $item;
		}
		
		return $items;
	}
	
	public static function getAdjacentItem($order_number) {
		if ($order_number < 0) {
			return false;
		}
		
		$item = false;		
		$cata = new CataBlogItem();
		$params = array(
			'post_type'=> $cata->getCustomPostName(),
			'orderby'=>'menu_order',
			'order'=>'ASC',
			'numberposts' => 1,
			'offset' => $order_number,
		);
		
		$posts = get_posts($params);
		foreach ($posts as $post) {
			$item = new CataBlogItem();
			$item->id           = $post->ID;
			$item->title        = $post->post_title;
		}
		
		return $item;
	}
	
	
	
	
	
	

	
	/*****************************************************
	**       - VALIDATE, SAVE & DELETE METHODS
	*****************************************************/
	public function validate() {
		
		// catablog item must have an image associated with it
		if (mb_strlen($this->image) < 1) {
			return 'An item must have an image associated with it.';
		}
		
		// check that the originals directory exists and is writable
		$originals_directory = $this->_wp_upload_dir . "/catablog/originals";
		if (!is_writable($originals_directory)) {
			return 'Can\'t write uploaded image to server, please make sure CataBlog is properly installed.';
		}
		
		// check that the title is at least one character long
		if (mb_strlen($this->title) < 1) {
			return 'An item must have a title of at least one alphanumeric character.';
		}
		
		// check that the title is less then 200 characters long
		if (mb_strlen($this->title) > 200) {
			return 'An item\'s title can not be more then 200 characters long.';
		}
		
		// check that the price is a positive integer
		if (mb_strlen($this->price) > 0) {
			if (is_numeric($this->price) == false || $this->price < 0) {
				return 'An item\'s price must be a positive integer.';
			}
		}
		
		return true;
	}
	
	public function validateImage($image) {
		list($width, $height, $format) = getimagesize($image);
		switch($format) {
			case IMAGETYPE_GIF: break;
			case IMAGETYPE_JPEG: break;
			case IMAGETYPE_PNG:	break;
			default: return "The image could not be used because it is an unsupported format. JPEG, GIF and PNG formats only, please.";
		}
		
		// check if catablog is going over the storage space limit on multisite blogs
		if (function_exists('get_upload_space_available')) {
			if ($this->_main_image_changed) {
				$space_available = get_upload_space_available();
				$image_size      = filesize($this->image);
				if ($image_size > $space_available) {

					$space_available = round(($space_available / 1024 / 1024), 2);
					$image_size      = round(($image_size / 1024 / 1024), 2);

					$error  = 'Can\'t write uploaded image to server, your storage space is exhausted.<br />';
					$error .= 'Please delete some media files to free up space and try again.<br />';
					$error .= 'You have '.$space_available.'MB of available space on your server and your image is '.$image_size.'MB.';
					return $error;
				}				
			}
		}
		
		return true;
	}

	public function save() {
				
		$params = array();
		$params['post_title']    = $this->title;
		$params['post_content']  = $this->description;
		$params['post_status']   = 'publish';
		$params['post_type']     = $this->_custom_post_name;
		$params['menu_order']    = $this->order;
		
		if ($this->id > 0) {
			$params['ID'] = $this->id;
			if (wp_update_post($params) === false) {
				return false;
			}
		}
		else {
			$this->id = wp_insert_post($params);
			if ($this->id === false) {
				return false;
			}
		}
		
		// if the image has been set after loading process image
		if ($this->_main_image_changed) {
			// store the new uploaded file in the originals directory
			$upload_path     = $this->image;
			$sanatized_title = $this->getSanitizedTitle();
			$move_path       = $this->_wp_upload_dir . "/catablog/originals/$sanatized_title";
			$moved = move_uploaded_file($this->image, $move_path);
			$this->image = $sanatized_title;

			// save the new image's title to the post meta
			$this->updatePostMeta();

			// remove the old files associated with this item
			foreach ($this->_old_images as $old_image) {
				foreach (array('originals', 'thumbnails', 'fullsize') as $folder) {
					$path = $this->_wp_upload_dir."/catablog/$folder/$old_image";
					if (is_file($path)) {
						unlink($path);
					}					
				}
			}

			// generate a thumbnail for the new image
			$this->makeThumbnail();
			if ($this->_options['lightbox-enabled']) {
				$this->makeFullsize();
			}

			delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
		}
		
		// update post meta
		$this->updatePostMeta();
		
		// update post terms
		$terms_set = wp_set_object_terms($this->id, $this->categories, $this->_custom_tax_name);
		if ($terms_set instanceof WP_Error) {
			return "Could not set categories, please try again.";
		}
		
		return true;
	}
	
	public function delete($remove_images=true) {
		if ($this->id > 0) {
			
			// $this->deletePostMeta();
			wp_delete_post($this->id, true);
			
			// remove any associated images
			if ($remove_images) {
				$to_delete = array();
				$to_delete['original']  = $this->_wp_upload_dir . "/catablog/originals/" . $this->image;
				$to_delete['thumbnail'] = $this->_wp_upload_dir . "/catablog/thumbnails/" . $this->image;
				$to_delete['fullsize']  = $this->_wp_upload_dir . "/catablog/fullsize/" . $this->image;
				
				if (is_array($this->sub_images)) {
					foreach ($this->sub_images as $key => $image) {
						$to_delete["sub$key-original"]  = $this->_wp_upload_dir . "/catablog/originals/" . $image;
						$to_delete["sub$key-thumbnail"] = $this->_wp_upload_dir . "/catablog/thumbnails/" . $image;
						$to_delete["sub$key-fullsize"]  = $this->_wp_upload_dir . "/catablog/fullsize/" . $image;
					}					
				}
				
				foreach ($to_delete as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}
				
				delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
			}
		}
		
	}
	
	
	
	public function addSubImage($tmp_path) {
		if (function_exists('get_upload_space_available')) {
			$space_available = get_upload_space_available();
			$image_size = filesize($tmp_path);
		
			if ($image_size > $space_available) {
				$space_available = round(($space_available / 1024 / 1024), 2);
				$image_size      = round(($image_size / 1024 / 1024), 2);

				$error  = 'Can\'t write uploaded image to server, your storage space is exhausted.<br />';
				$error .= 'Please delete some media files to free up space and try again.<br />';
				$error .= 'You have '.$space_available.'MB of available space on your server and your image is '.$image_size.'MB.';
				return $error;
			}
		}
		
		// check if any image is of a bad format
		list($width, $height, $format) = getimagesize($tmp_path);
		switch($format) {
			case IMAGETYPE_GIF: break;
			case IMAGETYPE_JPEG: break;
			case IMAGETYPE_PNG:	break;
			default: return "The image could not be used because it is an unsupported format. JPEG, GIF and PNG formats only, please.";
		}
		
		$sanatized_title = $this->getSanitizedTitle();
		$move_path       = $this->_wp_upload_dir . "/catablog/originals/$sanatized_title";
		$moved           = move_uploaded_file($tmp_path, $move_path);
		
		if ($moved !== true) {
			$error = 'Could not move the uploaded file on your server';
			return $error;
		}
		
		$this->sub_images[] = $sanatized_title;
		$this->updatePostMeta();
		
		// generate a thumbnail for the new image
		$this->makeThumbnail($sanatized_title);
		if ($this->_options['lightbox-enabled']) {
			$this->makeFullsize($sanatized_title);
		}
		
		delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
		
		return true;
	}
	
	
	
	
	
	
	
	/*****************************************************
	**       - IMAGE GENERATION METHODS
	*****************************************************/
	public function makeFullsize($filepath=NULL) {
		if ($filepath === NULL) {
			$filepath = $this->getImage();
		}
		
		$original = $this->_wp_upload_dir . "/catablog/originals/" . $filepath;
		$fullsize = $this->_wp_upload_dir . "/catablog/fullsize/" . $filepath;
		$quality  = 80;
		
		if (is_file($original) === false) {
			return "Original image file could not be located at $original";
		}
		
		list($width, $height, $format) = getimagesize($original);
		$canvas_size = $this->_options['image-size'];
		
		if ($width < 1 || $height < 1) {
			return "Original image dimensions are less then 1px.";
		}
		
		if ($width < $canvas_size && $height < $canvas_size) {
			//original is smaller, do nothing....
		}
		
		
		$ratio = ($height > $width)? ($canvas_size / $height) : ($canvas_size / $width);			
		$new_height = $height * $ratio;
		$new_width  = $width * $ratio;
		
		
		// create a blank canvas of user specified size
		$bg_color = $this->html2rgb($this->_options['background-color']);
		$canvas   = imagecreatetruecolor($new_width, $new_height);
		
		
		switch($format) {
			case IMAGETYPE_GIF:
				$upload = imagecreatefromgif($original);
				break;
			case IMAGETYPE_JPEG:
				$upload = imagecreatefromjpeg($original);
				break;
			case IMAGETYPE_PNG:
				$upload = imagecreatefrompng($original);
				break;
			default:
				return "Original image could not be loaded because it is an unsupported format.";
		}
		
		imagecopyresampled($canvas, $upload, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		
		// rotate the final canvas to match the original files orientation
		$orientation = 1;
		
		if (function_exists('exif_read_data')) {
			$exif = @exif_read_data($original, 'EXIF', 0);
			if ($exif) {
				if (isset($exif['Orientation'])) {
					$orientation = $exif['Orientation'];
				}
			}
		}
		
		switch ($orientation) {
			case 1:
				$orientation = 0;
				break;
			case 3:
				$orientation = 180;
				break;
			case 6:
				$orientation = -90;
				break;
			case 8:
				$orientation = 90;
				break;
		}
		$canvas = imagerotate($canvas, $orientation, 0);
		
		
		imagejpeg($canvas, $fullsize, $quality);
		
		return true;
	}
	
	public function makeThumbnail($filepath=NULL) {
		if ($filepath === NULL) {
			$filepath = $this->getImage();
		}
		
		$original = $this->_wp_upload_dir . "/catablog/originals/" . $filepath;
		$thumb    = $this->_wp_upload_dir . "/catablog/thumbnails/" . $filepath;
		$quality  = 90;
		
		if (is_file($original) === false) {
			return "Original image file missing, could not be located at $original";
		}
		
		list($width, $height, $format) = @getimagesize($original);
		$canvas_size = $this->_options['thumbnail-size'];			
		
		if ($width < 1 || $height < 1) {
			return "<strong>$this->title</strong>: Original image dimensions are less then 1px. Most likely PHP does not have permission to read the original file.";
		}
		
		
		// create a blank canvas of user specified size and color
		$bg_color = $this->html2rgb($this->_options['background-color']);
		$canvas   = imagecreatetruecolor($canvas_size, $canvas_size);
		$bg_color = imagecolorallocate($canvas, $bg_color[0], $bg_color[1], $bg_color[2]);
		imagefill($canvas, 0, 0, $bg_color);
		
		
		switch($format) {
			case IMAGETYPE_GIF:
				$upload = imagecreatefromgif($original);
				break;
			case IMAGETYPE_JPEG:
				$upload = imagecreatefromjpeg($original);
				break;
			case IMAGETYPE_PNG:
				$upload = imagecreatefrompng($original);
				break;
			default:
				return "Original image could not be loaded because it is an unsupported format.";
		}
		
		
		$x_offset = 0;
		$y_offset = 0;
		if ($this->_options['keep-aspect-ratio']) {
			if ($height > $width) {    // resize to the height
				$ratio      = $canvas_size / $height;
				$new_height = $height * $ratio;
				$new_width  = $width * $ratio;
				$x_offset   = ($canvas_size - $new_width) / 2;
			}
			else {    // resize to the width
				$ratio      = $canvas_size / $width;
				$new_height = $height * $ratio;
				$new_width  = $width * $ratio;
				$y_offset   = ($canvas_size - $new_height) / 2;
			}
		}
		else {
			if ($height > $width) {    // resize to the height
				$ratio      = $canvas_size / $width;
				$new_height = $height * $ratio;
				$new_width  = $width * $ratio;
				$y_offset   = ($canvas_size - $new_height) / 2;
			}
			else {    // resize to the width
				$ratio      = $canvas_size / $height;
				$new_height = $height * $ratio;
				$new_width  = $width * $ratio;
				$x_offset   = ($canvas_size - $new_width) / 2;
			}
		}
		
		imagecopyresampled($canvas, $upload, $x_offset, $y_offset, 0, 0, $new_width, $new_height, $width, $height);
		
		// rotate the final canvas to match the original files orientation
		$orientation = 1;
		
		if (function_exists('exif_read_data')) {
			$exif = @exif_read_data($original, 'EXIF', 0);
			if ($exif) {
				if (isset($exif['Orientation'])) {
					$orientation = $exif['Orientation'];
				}
			}
		}
		
		switch ($orientation) {
			case 1:
				$orientation = 0;
				break;
			case 3:
				$orientation = 180;
				break;
			case 6:
				$orientation = -90;
				break;
			case 8:
				$orientation = 90;
				break;
		}
		$canvas = imagerotate($canvas, $orientation, $bg_color, false);
		
		imagejpeg($canvas, $thumb, $quality);
		
		return true;
	}
	
	

	
	
	
	
	/*****************************************************
	**       - GETTER METHODS
	*****************************************************/
	public function getId() {
		return $this->id;
	}
	public function getTitle() {
		return $this->title;
	}
	public function getDescription() {
		return $this->description;
	}
	public function getImage() {
		return $this->image;
	}
	public function getSubImages() {
		return (is_array($this->sub_images))? $this->sub_images : array();
	}
	public function getOrder() {
		return $this->order;
	}
	public function getLink() {
		return $this->link;
	}
	public function getPrice() {
		return $this->price;
	}
	public function getProductCode() {
		return $this->product_code;
	}
	public function getQuantity() {
		return $this->quantity;
	}
	public function getSize() {
		return $this->size;
	}
	public function getPrices() {
		return $this->prices;
	}
	public function getCategories() {
		return $this->categories;
	}
	public function getCustomPostName() {
		return $this->_custom_post_name;
	}
	public function getCustomTaxName() {
		return $this->_custom_tax_name;
	}
	public function getValuesArray() {
		$order        = $this->getOrder();
		$image        = $this->getImage();
		$subimages    = implode('|', $this->getSubImages());
		$title        = $this->getTitle();
		$link         = $this->getLink();
		$description  = $this->getDescription();
		$categories   = implode('|', $this->getCategories());
		$price        = $this->getPrice();
		$product_code = $this->getProductCode();
		$quantity     = $this->getQuantity();
		$size         = $this->getSize();
		$prices       = $this->getPrices();
		return array($order, $image, $subimages, $title, $link, $description, $categories, $price, $product_code, $quantity, $size, $prices);
	}
	
	
	
	
	/*****************************************************
	**       - SETTER METHODS
	*****************************************************/
	public function setId($id) {
		$this->id = $id;
	}
	public function setTitle($title) {
		$this->title = $title;
	}
	public function setDescription($description) {
		$this->description = $description;
	}
	public function setImage($image, $update=true) {
		if ($update) {
			$this->_old_images[] = $this->image;
			$this->_main_image_changed = true;			
		}
		
		$this->image = $image;
	}
	public function setSubImage($image) {
		$this->sub_images[] = $image;
		$this->_sub_images_changed = true;
	}
	public function setSubImages($images) {
		$this->sub_images = $images;
		$this->_sub_images_changed = true;
	}
	public function setOrder($order) {
		$this->order = $order;
	}
	public function setLink($link) {
		$this->link = $link;
	}
	public function setPrice($price) {
		$this->price = $price;
	}
	public function setProductCode($product_code) {
		$this->product_code = $product_code;
	}
	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}
	public function setSize($size) {
		$this->size = $size;
	}
	public function setPrices($prices) {
		$this->prices = $prices;
	}
	public function setCategory($category) {
		$this->categories[] = $category;
	}
	public function setCategories($categories) {
		$this->categories = $categories;
	}
	
	
	
	
	
	
	
	/*****************************************************
	**       - HELPER METHODS
	*****************************************************/
	private function processPostMeta($meta) {
		
		// deserialize meta if necessary
		if (is_serialized($meta)) {
			$meta = unserialize($meta);
		}
		
		// loop through meta array and set properties		
		if (is_array($meta)) {
			foreach ($meta as $key => $value) {
				$this->{str_replace('-', '_', $key)} = $value;
			}
		}
	}
	
	private function updatePostMeta() {
		$meta = array();
		$meta['image']        = $this->image;
		$meta['sub-images']   = $this->sub_images;
		$meta['link']         = $this->link;
		$meta['price']        = $this->price;
		$meta['product-code'] = $this->product_code;
		$meta['quantity']     = $this->quantity;
		$meta['size']         = $this->size;
		$meta['prices']       = $this->prices;
		
		update_post_meta($this->id, $this->_post_meta_name, $meta);
	}
	
	private function deletePostMeta() {
		// remove the current post meta values from database
		delete_post_meta($this->id, $this->_post_meta_name);
	}
	
	private function deleteLegacyPostMeta() {
		delete_post_meta($this->id, 'catablog-image');
		delete_post_meta($this->id, 'catablog-link');
		delete_post_meta($this->id, 'catablog-price');
		delete_post_meta($this->id, 'catablog-product-code');		
	}
	
	private function getParameterArray() {
		$param_names = array();
		foreach ($this as $name => $value) {
			if (substr($name,0,1) != '_') {
				$param_names[] = $name;				
			}
		}
		return $param_names;
	}
	
	private function getSanitizedTitle() {
		$special_chars_removed = preg_replace("/[^a-zA-Z0-9s]/", "", $this->title);
		return sanitize_title($special_chars_removed) . "-" . time() . ".jpg";
	}
	
	private function html2rgb($color) {
		if ($color[0] == '#') {
			$color = substr($color, 1);
		}
		
		if (strlen($color) == 6) {
			list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
		}
		elseif (strlen($color) == 3) {
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		}
		else {
			return false;
		}
		
		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);
		
		return array($r, $g, $b);
	}
	
	
	
	
	
}
