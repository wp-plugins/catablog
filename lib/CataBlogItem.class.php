<?php


class CataBlogItem {
	
	// item properties that directly relate to 
	// values in the WordPress Database
	private $id           = null;
	private $title        = "";
	private $description  = "";
	private $image        = "";
	private $order        = 0;
	private $link         = "";
	private $price        = 0;
	private $product_code = "";
	private $categories   = array();
	
	// object values not considered item properties
	// this will be skipped in getParameterArray() method
	private $_options       = array();
	private $_image_changed = false;
	private $_old_images    = array();
	private $_wp_upload_dir = "";
	private $_custom_post_name = "catablog-items";
	private $_custom_tax_name  = "catablog-terms";
	
	
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
		$terms = wp_get_object_terms($post->ID, array($item->getCustomTaxName()), array());
		foreach ($terms as $term) {
			$category_ids[$term->term_id] = $term->name;
		}
		
		$item->id           = $post->ID;
		$item->title        = $post->post_title;
		$item->description  = $post->post_content;
		$item->categories   = $category_ids;
		$item->order        = $post->menu_order;
		
		$item->image        = get_post_meta($post->ID, "catablog-image", true);
		$item->link         = get_post_meta($post->ID, "catablog-link", true);
		$item->price        = get_post_meta($post->ID, "catablog-price", true);
		$item->product_code = get_post_meta($post->ID, "catablog-product-code", true);
		// print_r($item);
		return $item;
	}
	
	public static function getItems($category=false) {
		$items = array();
		$cata  = new CataBlogItem();
		
		$params = array(
			'post_type'=> $cata->getCustomPostName(), 
			'orderby'=>'menu_order',
			'order'=>'ASC',
			'numberposts' => -1,
		);
		
		if ($category !== false) {
			$params[$cata->getCustomTaxName()] = $category;
		}
		
		$posts = get_posts($params);
		
		foreach ($posts as $post) {
			$item = new CataBlogItem();
			
			$category_ids = array();
			$terms = wp_get_object_terms($post->ID, array($item->getCustomTaxName()), array());
			foreach ($terms as $term) {
				$category_ids[$term->term_id] = $term->name;
			}
			
			$item->id           = $post->ID;
			$item->title        = $post->post_title;
			$item->description  = $post->post_content;
			$item->categories   = $category_ids;
			$item->order        = $post->menu_order;

			$item->image        = get_post_meta($post->ID, "catablog-image", true);
			$item->link         = get_post_meta($post->ID, "catablog-link", true);
			$item->price        = get_post_meta($post->ID, "catablog-price", true);
			$item->product_code = get_post_meta($post->ID, "catablog-product-code", true);
			
			$items[] = $item;
		}
		
		return $items;
	}
	
	
	
	
	
	
	

	
	/*****************************************************
	**       - VALIDATE, SAVE & DELETE METHODS
	*****************************************************/
	public function validate() {
		if (mb_strlen($this->image) < 1) {
			return 'An item must have an image associated with it.';
		}
		if (mb_strlen($this->title) < 1) {
			return 'An item must have a title of at least one alphanumeric character.';
		}
		if (mb_strlen($this->title) > 200) {
			return 'An item\'s title can not be more then 200 characters long.';
		}
		if (mb_strlen($this->price) > 0) {
			if (is_numeric($this->price) == false || $this->price < 0) {
				return 'An item\'s price must be a positive integer.';
			}
		}
		
		return true;
	}

	public function save() {
				
		$params = array();
		$params['post_title']    = $this->title;
		$params['post_content']  = $this->description;
		$params['post_status']   = 'publish';
		// $params['post_category'] = $this->categories;
		$params['post_type']     = $this->_custom_post_name;
		$params['menu_order']    = $this->order;
		
		// print_r($params); die;
		
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
		
		// update_post_meta($this->id, "catablog-image", $this->image);
		update_post_meta($this->id, "catablog-link", $this->link);
		update_post_meta($this->id, "catablog-image", $this->image);
		update_post_meta($this->id, "catablog-price", $this->price);
		update_post_meta($this->id, "catablog-product-code", $this->product_code);
		
		// update set catagories
		 // print_r($this->categories); print_r(array(70)); die;
		wp_set_object_terms($this->id, $this->categories, $this->_custom_tax_name, false);
		
		
		if ($this->_image_changed) {
			
			// store the new uploaded file in the originals directory
			$upload_path     = $this->image;
			$sanatized_title = $this->getSanitizedTitle();
			$move_path       = $this->_wp_upload_dir . "/catablog/originals/$sanatized_title";
			$moved = move_uploaded_file($this->image, $move_path);
			$this->image = $sanatized_title;
			
			// save the new image's title to the post meta
			update_post_meta($this->id, "catablog-image", $this->image);
			
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
		}
		return true;
	}
	
	public function delete($remove_images=true) {
		if ($this->id > 0) {
			wp_delete_post($this->id, true);
			
			// remove any associated images
			if ($remove_images) {
				$to_delete = array();
				$to_delete['original']  = $this->_wp_upload_dir . "/catablog/originals/" . $this->image;
				$to_delete['thumbnail'] = $this->_wp_upload_dir . "/catablog/thumbnails/" . $this->image;
				$to_delete['fullsize']  = $this->_wp_upload_dir . "/catablog/fullsize/" . $this->image;

				foreach ($to_delete as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}				
			}
		}
		
	}
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - IMAGE GENERATION METHODS
	*****************************************************/
	public function makeFullsize() {
		$original = $this->_wp_upload_dir . "/catablog/originals/" . $this->getImage();
		$fullsize = $this->_wp_upload_dir . "/catablog/fullsize/" . $this->getImage();
		$quality  = 80;
		
		list($width, $height, $format) = getimagesize($original);
		$canvas_size = $this->_options['image-size'];
		
		if ($width < 1 || $height < 1) {
			return false;
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
				return false;
		}
		
		imagecopyresampled($canvas, $upload, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		imagejpeg($canvas, $fullsize, $quality);	
	}
	
	public function makeThumbnail() {
		$original = $this->_wp_upload_dir . "/catablog/originals/" . $this->getImage();
		$thumb    = $this->_wp_upload_dir . "/catablog/thumbnails/" . $this->getImage();
		$quality  = 90;
		
		if (is_file($original) === false) {
			return false;
		}
		
		list($width, $height, $format) = getimagesize($original);
		$canvas_size = $this->_options['thumbnail-size'];			
		
		if ($width < 1 || $height < 1) {
			return false;
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
				return false;
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
		imagejpeg($canvas, $thumb, $quality);
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
	public function getCategories() {
		return $this->categories;
	}
	public function getCustomPostName() {
		return $this->_custom_post_name;
	}
	public function getCustomTaxName() {
		return $this->_custom_tax_name;
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
	public function setImage($image) {
		$this->_old_images[] = $this->image;
		$this->image = $image;
		$this->_image_changed = true;
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
	public function setCategories($categories) {
		$this->categories = $categories;
	}
	
	
	
	
	
	
	
	/*****************************************************
	**       - HELPER METHODS
	*****************************************************/
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
		return sanitize_title($this->title) . "-" . time() . ".jpg";
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
