<?php


/**********************************************
**  CataBlog Class
**********************************************/
class CataBlog {
	
	// plugin component version numbers
	private $version     = "0.8";
	private $dir_version = 2;
	private $db_version  = 4;
	private $debug       = false;
	
	// wordpress database object and options
	private $wpdb         = null;
	private $options      = array();
	private $options_name = 'catablog-options';
	
	// database table names and user permission requirements
	private $db_table   = 'catablog_items';
	private $user_level = 'edit_pages';
	
	// default image sizes
	private $default_thumbnail_size = 100;
	private $default_image_size     = 600;
	private $default_bg_color       = "ffffff";
	
	// two private arrays for storing common file paths
	private $directories   = array();
	private $urls          = array();
	
	
	
	public function __construct() {
		
		// embed the wp database class into the plugin class
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->db_table = $wpdb->prefix . $this->db_table;
		
		// get plugin options from wp database
		$this->options = get_option($this->options_name);
		
		// define common directories and files for the plugin
		$this->plugin_file               = WP_CONTENT_DIR . "/plugins/catablog/catablog.php";
		$this->directories['plugin']     = WP_CONTENT_DIR . "/plugins/catablog";
		$this->directories['template']   = WP_CONTENT_DIR . "/plugins/catablog/templates";
		$this->directories['uploads']    = WP_CONTENT_DIR . "/uploads/catablog";
		$this->directories['thumbnails'] = WP_CONTENT_DIR . "/uploads/catablog/thumbnails";
		$this->directories['originals']  = WP_CONTENT_DIR . "/uploads/catablog/originals";
		$this->directories['old_pics']   = WP_CONTENT_DIR . "/catablog";
		$this->urls['plugin']     = WP_CONTENT_URL . "/plugins/catablog";
		$this->urls['css']        = WP_CONTENT_URL . "/plugins/catablog/css";
		$this->urls['javascript'] = WP_CONTENT_URL . "/plugins/catablog/js";
		$this->urls['thumbnails'] = WP_CONTENT_URL . "/uploads/catablog/thumbnails";
		$this->urls['originals']  = WP_CONTENT_URL . "/uploads/catablog/originals";
	}
	
	
	
	
	/**********************************************
	**  WordPress Application Hooks
	**********************************************/
	public function registerWordPressHooks() {
		// install-uninstall actions
		register_activation_hook($this->plugin_file, array(&$this, 'activate'));
		register_deactivation_hook($this->plugin_file, array(&$this, 'deactivate'));
		register_uninstall_hook($this->plugin_file, array(&$this, 'remove'));
		
		// admin actions
		add_action('admin_menu', array(&$this, 'admin_menu'));
		if(strpos($_SERVER['QUERY_STRING'], 'catablog') !== false) {
			add_action('admin_init', array(&$this, 'admin_head'));
		}
		
		//ajax actions
		add_action('wp_ajax_catablog_reorder', array($this, 'ajax_reorder_items'));
		add_action('wp_ajax_catablog_reset', array($this, 'ajax_reset_all'));
		// add_action('wp_ajax_catablog_recalc_thumbs', array($this, 'ajax_recalc_thumbs'));
		
		// frontend actions
		add_action('wp_head', array(&$this, 'frontend_head'));
		add_shortcode('catablog', array(&$this, 'frontend_content'));
	}
	
	
	
	
	/**********************************************
	**  Admin Panel Integration Points
	**********************************************/
	public function admin_head() {
		wp_deregister_script('jquery');
		wp_deregister_script('jquery-ui');
		
		wp_register_script('jquery', $this->urls['javascript'].'/jquery-1.4.2.min.js');
		
		// wp_enqueue_script('jquery-ui-core');
		// wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui', $this->urls['javascript'] . '/jquery-ui-1.8.1.custom.min.js');
		wp_enqueue_script('jpicker', $this->urls['javascript'] . '/jpicker-1.1.2.min.js');
		wp_enqueue_script('catablog-admin-js', $this->urls['javascript'] . '/catablog-admin.js');
		
		wp_enqueue_style('catablog-admin-css', $this->urls['css'] . '/catablog-admin.css');
		wp_enqueue_style('jquery-ui-lightness', $this->urls['css'] . '/ui-lightness/jquery-ui-1.8.1.custom.css');
	}
	
	public function admin_menu() {
		add_object_page("Edit CataBlog", "CataBlog", $this->user_level, 'catablog-edit', array($this, 'admin_list'), $this->urls['plugin']."/images/cb_icon_16.png");
		add_submenu_page('catablog-edit', "Edit CataBlog", 'Edit', $this->user_level, 'catablog-edit', array(&$this, 'admin_list'));
		add_submenu_page('catablog-edit', "Add New CataBlog", 'Add New', $this->user_level, 'catablog-new', array(&$this, 'admin_item'));
		add_submenu_page('catablog-edit', "CataBlog Options", 'Options', $this->user_level, 'catablog-options', array(&$this, 'admin_options'));
		add_submenu_page('catablog-edit', 'About CataBlog', 'About', $this->user_level, 'catablog-about', array(&$this, 'admin_about'));
	}
	
	
	
	
	/**********************************************
	**  Admin Panel Actions
	**********************************************/	
	public function admin_list() {
		$results = $this->get_items();		
		require_once($this->directories['template'] . '/admin-list.php');
	}
	
	public function admin_item() {
		if (isset($_POST['save'])) {
			// save record
			if ($this->save_item() == false) {
				$result = array_map('stripslashes_deep', $_POST);
				require($this->directories['template'] . '/admin-form.php');
			}
			else {
				$this->admin_list();
			}
		}
		elseif (isset($_REQUEST['action'])) {
			if ($_REQUEST['action'] == 'remove') {
				$this->delete_item($_REQUEST['id']);
				$this->wp_message('Catalog Item Removed Successfully');
				$this->admin_list();
			}
			elseif ($_REQUEST['action'] == 'edit') {
				// edit record
				if (isset($_REQUEST['id'])) {
					$update = true;
					$id = $_REQUEST['id'];
					$result = $this->get_item($id);
				}
				
				require($this->directories['template'] . '/admin-form.php');
			}
		} else {
			// view record
			$update = false;
			$result = array('id'=>'', 'image'=>'', 'title'=>'', 'description'=>'');
			require($this->directories['template'] . '/admin-form.php');
		}	
	}

	public function admin_options() {
		if (isset($_POST['save'])) {
			if (true) {
				$recalculate_thumbnails = false;
				$save_message           = "CataBlog Options Saved";
				
				$image_size_different   = $_REQUEST['image_size'] != $this->options['thumbnail-size'];
				$bg_color_different     = $_REQUEST['bg_color'] != $this->options['background-color'];
				$keep_ratio_different   = $_REQUEST['keep_aspect_ratio'] != $this->options['keep-aspect-ratio'];
				if ($image_size_different || $bg_color_different || $keep_ratio_different) {
					$recalculate_thumbnails = true;
					$save_message           = "CataBlog Options Saved & Thumbnails Regenerated";
				}
				
				$this->options['thumbnail-size'] = $_REQUEST['image_size'];
				$this->options['background-color'] = $_REQUEST['bg_color'];
				$this->options['paypal-email'] = $_REQUEST['paypal_email'];
				$this->options['keep-aspect-ratio'] = $_REQUEST['keep_aspect_ratio'];
				update_option($this->options_name, $this->options);
				
				if ($recalculate_thumbnails) {
					$this->regenerate_all_thumbnails();
				}
				
				$this->wp_message($save_message);
			}
		}
		
		$thumbnail_size    = $this->options['thumbnail-size'];
		$background_color  = $this->options['background-color'];
		$paypal_email      = $this->options['paypal-email'];
		$keep_aspect_ratio = $this->options['keep-aspect-ratio'];
		
		require($this->directories['template'] . '/admin-options.php');
	}
	
	public function admin_about() {
		$thumb_dir    = new CataBlog_Directory($this->directories['thumbnails']);
		$original_dir = new CataBlog_Directory($this->directories['originals']);
		$thumbnail_size = $thumb_dir->getDirectorySize() / (1024 * 1024);
		$original_size  = $original_dir->getDirectorySize() / (1024 * 1024);
		
		$stats = array();
		$stats['CataBlog Version']  = $this->version;
		$stats['System Versions']    = apache_get_version();
		$stats['PHP_Memory']        = (memory_get_peak_usage(true) / (1024 * 1024)) . " MB";
		$stats['MySQL_Version']     = $this->wpdb->get_var("SELECT version()");
		$stats['Thumbnail_Disc_Usage'] = round($thumbnail_size, 2) . " MB";
		$stats['Original_Upload_Disc_Usage'] = round($original_size, 2) . " MB";
		$stats['Total_Library_Disc_Usage'] = (round($thumbnail_size, 2) + round($original_size, 2)) . " MB";
		
		require($this->directories['template'] . '/admin-about.php');
	}
	
	
	
	/**********************************************
	**  Admin Panel AJAX Actions
	**********************************************/
	public function ajax_reorder_items() {
		check_ajax_referer('catablog-reorder', 'security');
		
		$ids    = $_POST['ids'];
		$length = count($ids);
		
		for ($i=0; $i < $length; $i++) {
			$this->wpdb->update($this->db_table, array('order'=>$i), array('id'=>$ids[$i]), array('%d'), array('%d'));
		}
		
		die();
	}
	
	public function ajax_reset_all() {
		check_ajax_referer('catablog-reset', 'security');
		
		$this->remove_legacy_data();
		$this->remove_options();
		$this->remove_database();
		$this->remove_directories();
		
		$this->install_options();
		$this->install_database();
		$this->install_directories();
		
		die();
	}
	
	public function ajax_recalc_thumbs() {
		check_ajax_referer('catablog-recalc-thumbs', 'security');
		$this->regenerate_all_thumbnails();
		die();
	}
	
	
	
	
	
	/**********************************************
	** Frontend Actions
	**********************************************/
	public function frontend_head() {
		$path = get_template_directory().'/catablog/style.css';
		if (file_exists($path)) {
			echo '	<link rel="stylesheet" type="text/css" href="'.get_bloginfo('template_url').'/catablog/style.css" media="all" />';
		}
		echo '	<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/catablog/css/catablog.css" media="all" />';
	}

	public function frontend_content($atts) {
		extract(shortcode_atts(array('tag'=>false), $atts));
		
		$results = $this->get_items($tag);
		$size    = $this->options['thumbnail-size'];
		$ml      = ($size + 10) . 'px';
		
		ob_start();
		include $this->directories['template'] . '/frontend-view.php';
		return ob_get_clean();
	}
	
	
	
	
	
	

	

	
	
	
	
	
	
	
	public function activate() {
		$options  = get_option($this->options_name);
		$table = $this->db_table;
		
		$this->remove_legacy_data();
		
		if($this->wpdb->get_var("show tables like '$table'") != $table) {
			// no table present, maybe do something
		}
		
		$this->install_options();
		$this->install_database();
		$this->install_directories();
	}

	public function deactivate() {
		
	}
	
	public function remove() {
		$this->remove_legacy_data();
		$this->remove_options();
		$this->remove_database();
		$this->remove_directories();
	}
	
	
	
	
	
	private function install_options() {
		if ($this->options == false) {
			$options = array();
			$options['db-version']        = $this->db_version;
			$options['dir-version']       = $this->dir_version;
			$options['thumbnail-size']    = $this->default_thumbnail_size;
			$options['image-size']        = $this->default_image_size;
			$options['background-color']  = $this->default_bg_color;
			$options['paypal-email']      = "";
			$options['keep-aspect-ratio'] = false;
			
			update_option($this->options_name, $options);
		}
		else {
			$this->options['db-version']        = $this->db_version;
			$this->options['dir-version']       = $this->dir_version;
			
			update_option($this->options_name, $this->options);
		}
	}
	
	private function install_database() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($this->get_database_schema());
	}
	
	private function install_directories() {
		$dirs = array(0=>'uploads', 1=>'thumbnails', 2=>'originals');
		
		foreach ($dirs as $dir) {
			$is_dir  = is_dir($this->directories[$dir]);
			$is_file = is_file($this->directories[$dir]);
			if (!$is_dir && !$is_file) {
				mkdir($this->directories[$dir]);
			}
		}
	}
	
	
	
	private function remove_options() {
		delete_option($this->options_name);
	}
	
	private function remove_database() {
		$table = $this->db_table;
		$drop = "DROP TABLE $table";
		$this->wpdb->query($drop);
	}
	
	private function remove_directories() {
		$dirs = array('thumbnails', 'originals', 'uploads');
		foreach ($dirs as $dir) {
			$mydir = $this->directories[$dir];
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
			else {
				unlink($mydir);
			}
		}
	}
	
	private function remove_legacy_data() {
		// remove legacy options
		delete_option('image_size');
		delete_option('catablog_db_version');
		delete_option('catablog_dir_version');
		delete_option('catablog_image_size');
		
		// remove legacy database tables
		$tables = array('disc_history', 'catablog');
		foreach ($tables as $table) {
			$table = $this->wpdb->prefix . $table;
			$drop  = "DROP TABLE $table";
			$this->wpdb->query($drop);
		}
	}
	
	
	
	

	
	
	
	
	
	
	
	/**********************************************
	**  Private Database Methods
	**********************************************/
	private function get_database_schema() {
		$table = $this->db_table;
		$sql   = "CREATE TABLE $table (
			`id` INT(9) NOT NULL AUTO_INCREMENT,
			`order` INT(9) NOT NULL,
			`image` TEXT NOT NULL,
			`title` TEXT NOT NULL,
			`link` TEXT,
			`description` TEXT,
			`tags` VARCHAR(255) NOT NULL,
			`price` INT(9),
			`product_code` TEXT,
			UNIQUE KEY id (id)
		);";
		
		return $sql;
	}
	
	private function get_items($tag=false) {
		$table = $this->db_table;
		$tag;
		if ($tag) {
			$query = $this->wpdb->prepare("SELECT * FROM `$table` WHERE `tags` LIKE %s ORDER BY `order`", array('%s'=>"%$tag%"));
		}
		else {
			$query = $this->wpdb->prepare("SELECT * FROM $table ORDER BY `order`", array());
		}
				
		return $this->wpdb->get_results($query);
	}
	
	private function get_item($id) {
		$table = $this->db_table;
		$query = $this->wpdb->prepare("SELECT * FROM $table WHERE `id`=%d", $id);
		return $this->wpdb->get_row($query, ARRAY_A);
	}
	
	private function delete_item($id) {
		$table = $this->db_table;
		
		// delete images
		$query = $this->wpdb->prepare("SELECT image FROM $table WHERE `id`=%d", $id);
		$db_filename = $this->wpdb->get_var($query);
		
		$dirs = array('originals', 'thumbnails');
		foreach ($dirs as $dir) {
			$file_path = $this->directories[$dir] . '/' . $db_filename;
			if (is_file($file_path)) {
				unlink($file_path);
			}			
		}
		
		$query = $this->wpdb->prepare("DELETE FROM $table WHERE `id`=%d;", $id);
		$this->wpdb->query($query);
		$this->wpdb->flush();
	}
	
	private function save_item() {
		
		// Set variables from $_POST and $_FILE
		$escaped_post = array_map('stripslashes_deep', $_POST);
		
		$table = $this->db_table;
		$title = trim($escaped_post['title']);
		$link  = trim($escaped_post['link']);
		$desc  = trim($escaped_post['description']);
		$tags  = trim($escaped_post['tags']);
		$price = trim($escaped_post['price']);
		$code  = trim($escaped_post['product_code']);
		
		$new_image  = $_FILES['image']['error'] != 4;
		$image_name = sanitize_title($title) . "-" . time() . ".jpg";
		
		
		//validate form data
		if (mb_strlen($title) < 1) {
			$this->wp_error('The catalog item must have a title of at least one character');
			return false;
		}
		if (mb_strlen($title) > 200) {
			$this->wp_error('The title can not be more then 200 characters long');
			return false;
		}
		if (mb_strlen($price) > 0) {
			if (is_numeric($price) == false || $price < 0) {
				$this->wp_error('The item price must be a positive integer');
				return false;
			}
		}
		
		
		if ($new_image) {
			$upload = $_FILES['image']['tmp_name'];
			if ($this->generate_thumbnail($image_name, $upload) === false) {
				return false;
			}
			move_uploaded_file($upload, $this->directories['originals'] . "/$image_name");
		}
		
		
		if (isset($_REQUEST['id'])) {
			// old entry, need to update row
			$id = $_REQUEST['id'];
			if ($new_image) {
				$this->wpdb->update($table, array('image'=>$image_name, 'title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags, 'price'=>$price, 'product_code'=>$code), array('id'=>$id), array('%s', '%s', '%s', '%s', '%s', '%d', '%s'), array('%d'));
			}
			else {
				$this->wpdb->update($table, array('title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags, 'price'=>$price, 'product_code'=>$code), array('id'=>$id), array('%s', '%s', '%s', '%s', '%d', '%s'), array('%d'));
			}
			
			$this->wp_message('Your Changes Have Been Saved');
		} else {
			// new entry, need to insert row
			$this->wpdb->insert($table, array('order'=>0, 'image'=>$image_name, 'title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags, 'price'=>$price, 'product_code'=>$code), array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s'));
			$this->wp_message('New Catalog Item Added');
		}
		
		return true;		
	}
	
	
	
	
	
	
	
	
	
	private function generate_thumbnail($image_name, $image_path, $bypass=false) {
				
		$tmp = $image_path;
		$filepath_thumb    = $this->directories['thumbnails'] . "/$image_name";
		$filepath_original = $this->directories['originals'] . "/$image_name";

		
		if (is_uploaded_file($tmp) || $bypass) {
			
			list($width, $height, $format) = getimagesize($tmp);
			$canvas_size = $this->options['thumbnail-size'];
			
			if ($width < 1 || $height < 1) {
				$this->wp_error('Image Error: None Supported Format');
				return false;
			}
			
			// create a blank canvas of user specified size and color
			$bg_color = $this->html2rgb($this->options['background-color']);
			$canvas   = imagecreatetruecolor($canvas_size, $canvas_size);
			$bg_color = imagecolorallocate($canvas, $bg_color[0], $bg_color[1], $bg_color[2]);
			imagefill($canvas, 0, 0, $bg_color);
			
			
			switch($format) {
				case IMAGETYPE_GIF:
					$upload = imagecreatefromgif($tmp);
					break;
				case IMAGETYPE_JPEG:
					$upload = imagecreatefromjpeg($tmp);
					break;
				case IMAGETYPE_PNG:
					$upload = imagecreatefrompng($tmp);
					break;
				default:
					$this->wp_error('Image Error: None Supported Format');
					return false;
			}
			
			
			$x_offset = 0;
			$y_offset = 0;
			if ($this->options['keep-aspect-ratio']) {
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
			
			
			if ($this->debug) {
				echo "offset: $x_offset, $y_offset<br>width: $width, $new_width<br>height: $height, $new_height";
			}
			
			imagecopyresampled($canvas, $upload, $x_offset, $y_offset, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($canvas, $filepath_thumb, 80);	
			
		}
		else {
			$this->wp_error('Image Error: Image Not Uploaded Correctly');
			$_REQUEST['image'] = $_REQUEST['saved_image'];
			
			return false;
		}
	}
	
	private function regenerate_all_thumbnails() {
		$dir = new CataBlog_Directory($this->directories['originals']);
		foreach ($dir->getFileArray() as $file) {
			$filepath = $this->directories['originals'] . "/" . $file;
			$this->generate_thumbnail($file, $filepath, true);
		}
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
	
	private function string2slug($string) {
		return sanitize_title($string);
	}
	
	private function wp_message($message) {
		echo "<div id='message' class='updated'>";
		echo "	<strong>$message</strong>";
		echo "</div>";
	}
	
	private function wp_error($message) {
		echo "<div id='error' class='error'>";
		echo "	<strong>$message</strong>";
		echo "</div>";
	}
	
	
	
	
	
	
	
	
	
	/**********************************************
	**  Plugin Compatibility and Misc Methods
	**  TO BE DELETED
	**********************************************/
	// public function compatibility() {
	// 	// Pre-2.6 compatibility
	// 	if ( !defined('WP_CONTENT_URL') ) {
	// 		define( 'WP_CONTENT_URL', get_bloginfo('wpurl') . '/wp-content');
	// 	}
	// 	if ( !defined('WP_CONTENT_DIR') ) {
	// 		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	// 	}
	// 	if ( !defined('WP_PLUGIN_URL') ) {
	// 		define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	// 	}
	// 
	// 	// require(WP_CONTENT_DIR . '/../wp-config.php');		
	// }
	// 
	// private function load_template($name) {
	// 	require_once($this->directories['template'] . "/$name.php");
	// }
	
		
}