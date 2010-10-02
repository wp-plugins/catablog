<?php


/**********************************************
**  CataBlog Class
**********************************************/
class CataBlog {
	
	// plugin component version numbers
	private $version     = "0.8.9";
	private $dir_version = 3;
	private $db_version  = 5;
	private $debug       = false;
	
	// wordpress database object and options
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
		global $wpdb;
		
		$this->db_table = $wpdb->prefix . $this->db_table;
		
		// get plugin options from wp database
		$this->options = get_option($this->options_name);
		
		// define common directories and files for the plugin
		$this->plugin_file               = WP_CONTENT_DIR . "/plugins/catablog/catablog.php";
		$this->directories['plugin']     = WP_CONTENT_DIR . "/plugins/catablog";
		$this->directories['css']        = WP_CONTENT_DIR . "/plugins/catablog/css";
		$this->directories['template']   = WP_CONTENT_DIR . "/plugins/catablog/templates";
		$this->directories['wp_uploads'] = WP_CONTENT_DIR . "/uploads";
		$this->directories['uploads']    = WP_CONTENT_DIR . "/uploads/catablog";
		$this->directories['thumbnails'] = WP_CONTENT_DIR . "/uploads/catablog/thumbnails";
		$this->directories['fullsize']   = WP_CONTENT_DIR . "/uploads/catablog/fullsize";
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
			add_action('admin_init', array(&$this, 'admin_init'));
		}
		
		//ajax actions
		add_action('wp_ajax_catablog_reorder', array($this, 'ajax_reorder_items'));
		add_action('wp_ajax_catablog_reset', array($this, 'ajax_reset_all'));
		add_action('wp_ajax_catablog_flush_fullsize', array($this, 'ajax_flush_fullsize'));
		add_action('wp_ajax_catablog_render_fullsize', array($this, 'ajax_render_fullsize'));
		// add_action('wp_ajax_catablog_recalc_thumbs', array($this, 'ajax_recalc_thumbs'));
		
		// frontend actions
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_init'));
		add_shortcode('catablog', array(&$this, 'frontend_content'));
	}
	
	
	
	
	/**********************************************
	**  Admin Panel Integration Points
	**********************************************/
	public function admin_init() {
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-export') !== false) {
			$this->admin_export();
		}
		
		wp_deregister_script('jquery');
		wp_deregister_script('jquery-ui');
		wp_deregister_script('catablog-ui');
		
		wp_register_script('jquery', $this->urls['javascript'].'/jquery-1.4.2.min.js', false, '1.4.2');
		wp_enqueue_script('jquery-ui', $this->urls['javascript'] . '/jquery-ui-1.8.1.custom.min.js', array('jquery'), '1.8.1');
		wp_enqueue_script('catablog-admin', $this->urls['javascript'] . '/catablog-admin.js', array('jquery', 'jquery-ui'), $this->version);
		
		wp_enqueue_style('jquery-ui-lightness', $this->urls['css'] . '/ui-lightness/jquery-ui-1.8.1.custom.css', false, '1.8.1');
		wp_enqueue_style('catablog-admin-css', $this->urls['css'] . '/catablog-admin.css', false, $this->version);
	}
	
	public function admin_menu() {
		add_object_page("Edit CataBlog", "CataBlog", $this->user_level, 'catablog-edit', array($this, 'admin_list'), $this->urls['plugin']."/images/cb_icon_16.png");
		add_submenu_page('catablog-edit', "Edit CataBlog", 'Edit', $this->user_level, 'catablog-edit', array(&$this, 'admin_list'));
		add_submenu_page('catablog-edit', "Add New CataBlog", 'Add New', $this->user_level, 'catablog-new', array(&$this, 'admin_item'));
		add_submenu_page('catablog-edit', "CataBlog Options", 'Options', $this->user_level, 'catablog-options', array(&$this, 'admin_options'));
		add_submenu_page('catablog-edit', "CataBlog Import/Export", 'Import/Export', $this->user_level, 'catablog-import-export', array(&$this, 'admin_import_export'));
		add_submenu_page('catablog-edit', 'About CataBlog', 'About', $this->user_level, 'catablog-about', array(&$this, 'admin_about'));
		
		// hidden pages
		add_submenu_page('catablog-hidden', "Save CataBlog Item", "Save", $this->user_level, 'catablog-save', array(&$this, 'admin_save'));
		add_submenu_page('catablog-hidden', "Delete CataBlog Item", "Delete", $this->user_level, 'catablog-delete', array(&$this, 'admin_delete'));
		add_submenu_page('catablog-hidden', "CataBlog Import", "Import", $this->user_level, 'catablog-import', array(&$this, 'admin_import'));
		add_submenu_page('catablog-hidden', "CataBlog Export", "Export", $this->user_level, 'catablog-export', array(&$this, 'admin_export'));
	}
	
	
	
	
	/**********************************************
	**  Admin Panel Actions
	**********************************************/	
	public function admin_list() {
		$results = $this->get_items();
		include_once($this->directories['template'] . '/admin-list.php');
	}
	
	public function admin_item() {
		if (isset($_REQUEST['action'])) {
			if ($_REQUEST['action'] == 'edit') {
				// edit record
				if (isset($_REQUEST['id'])) {
					$update = true;
					$id = $_REQUEST['id'];
					$result = $this->get_item($id);
				}
			}
		}
		else {
			// view record
			$update = false;
			$result = array(
				'id'=>'', 
				'image'=>'', 
				'title'=>'', 
				'description'=>'', 
				'link'=>'', 
				'tags'=>'', 
				'price'=>'', 
				'product_code'=>''
			);
		}
		
		include_once($this->directories['template'] . '/admin-form.php');
	}
	
	public function admin_delete() {
		// need to add support for nonce check
		if (isset($_REQUEST['id'])) {
			$this->delete_item($_REQUEST['id']);
			$this->wp_message('Catalog Item Removed Successfully');
			$this->admin_list();
		}
	}
	
	public function admin_save() {
		// need to add support for nonce check
		if (isset($_POST['save'])) {
			if ($this->save_item() == false) {
				
				$update = false;
				if (isset($_REQUEST['id'])) {
					$update = true;
				}
				
				$result = array_map('stripslashes_deep', $_POST);
				include_once($this->directories['template'] . '/admin-form.php');
			}
			else {
				$this->admin_list();
			}
		}
	}

	public function admin_options() {
		
		if (isset($_REQUEST['save'])) {
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_options_nonce'], 'catablog_options' );
			if ($nonce_verified) {
				$recalculate_thumbnails = false;
				$recalculate_fullsize   = false;
				$save_message           = "CataBlog Options Saved";
				
				$image_size_different   = $_REQUEST['thumbnail_size'] != $this->options['thumbnail-size'];
				$bg_color_different     = $_REQUEST['bg_color'] != $this->options['background-color'];
				$keep_ratio_different   = isset($_REQUEST['keep_aspect_ratio']) != $this->options['keep-aspect-ratio'];
				$fullsize_different     = $_REQUEST['lightbox_image_size'] != $this->options['image-size'];
				
				if ($image_size_different || $bg_color_different || $keep_ratio_different) {
					$recalculate_thumbnails = true;
					$save_message          .= " - Thumbnails Regenerated";
				}
				
				if (isset($_REQUEST['lightbox_enabled'])) {
					if ($fullsize_different) {
						$recalculate_fullsize = true;
						$save_message        .= " - Full Size Images Regenerated";						
					}
				}
				
				$this->options['thumbnail-size']    = $_REQUEST['thumbnail_size'];
				$this->options['image-size']        = $_REQUEST['lightbox_image_size'];
				$this->options['lightbox-enabled']  = isset($_REQUEST['lightbox_enabled']);
				$this->options['background-color']  = $_REQUEST['bg_color'];
				$this->options['paypal-email']      = $_REQUEST['paypal_email'];
				$this->options['keep-aspect-ratio'] = isset($_REQUEST['keep_aspect_ratio']);
				$this->options['link-target']       = $_REQUEST['link_target'];
				
				update_option($this->options_name, $this->options);
				
				if ($recalculate_thumbnails) {
					$this->regenerate_all_thumbnails();
				}
				if ($recalculate_fullsize) {
					$this->regenerate_all_fullsize();
				}
				
				$this->wp_message($save_message);
			}
			else {
				$this->wp_error('Form Validation Error. Please reload the page and try again.');
			}
		}
		
		$thumbnail_size    = $this->options['thumbnail-size'];
		$lightbox_size     = $this->options['image-size'];
		$lightbox_enabled  = $this->options['lightbox-enabled'];
		$background_color  = $this->options['background-color'];
		$paypal_email      = $this->options['paypal-email'];
		$keep_aspect_ratio = $this->options['keep-aspect-ratio'];
		$link_target       = $this->options['link-target'];
		
		include_once($this->directories['template'] . '/admin-options.php');
	}
	
	public function admin_import_export() {
		include_once($this->directories['template'] . '/admin-import-export.php');
	}
	
	public function admin_import() {
		$error = false;
		$upload = $_FILES['catablog_data'];
		$type = $upload['type'];
		
		if ($type != 'text/xml') {
			$error = true;
		}
		
		$xml_object = simplexml_load_file($upload['tmp_name']);
		if ($xml_object === false) {
			$error = true;
		}
		
		if ($error !== false) {
			$this->wp_error("Upload Error: make sure you are uploading a valid xml file with a '.xml' extension");
			$this->admin_import_export();
			return false;
		}
		
		// remove all data from database if clear box is checked
		if ($_REQUEST['catablog_clear_db'] == 'true') {
			$this->remove_database();
			$this->install_database();
		}
		
		
		// Private DataBase Insertion Method Called in Template:  load_xml_to_database($xml_object)
		
		include_once($this->directories['template'] . '/admin-import.php');
	}
	
	public function admin_export() {
		$date = date('Y-m-d');
		header('Content-type: application/xml');
		header('Content-Disposition: attachment; filename="catablog-backup-'.$date.'.xml"');
		
		$results = $this->get_items();
		include_once($this->directories['template'] . '/admin-export.php');
		
		die;
	}
	
	
	
	public function admin_about() {
		global $wpdb;
		
		$thumbnail_size = 'not present';
		$fullsize_size  = 'not present';
		$original_size  = 'not present';
		
		$thumb_dir = new CataBlog_Directory($this->directories['thumbnails']);
		$fullsize_dir = new CataBlog_Directory($this->directories['fullsize']);
		$original_dir = new CataBlog_Directory($this->directories['originals']);
		
		if ($thumb_dir->isDirectory()) {
			$thumbnail_size = round(($thumb_dir->getDirectorySize() / (1024 * 1024)), 2) . " MB";
		}
		if ($fullsize_dir->isDirectory()) {
			$fullsize_size  = round(($fullsize_dir->getDirectorySize() / (1024 * 1024)), 2) . " MB";
		}
		if ($original_dir->isDirectory()) {
			$original_size  = round(($original_dir->getDirectorySize() / (1024 * 1024)), 2) . " MB";
		}
		
		$stats = array();
		$stats['CataBlog_Version'] = $this->version;
		$stats['MySQL_Version']    = $wpdb->get_var("SELECT version()");
		$stats['PHP_Version']      = phpversion();
		
		$stats['PHP_Memory_Usage'] = round((memory_get_peak_usage(true) / (1024 * 1024)), 2) . " MB";
		$stats['PHP_Memory_Limit'] = preg_replace('/[^0-9]/', '', ini_get('memory_limit')) . " MB";
		
		$stats['Max_Uploaded_File_Size']     = ini_get('upload_max_filesize');
		$stats['Max_Post_size']              = ini_get('post_max_size');
		
		$stats['Thumbnail_Disc_Usage']       = $thumbnail_size;
		$stats['Full_Size_Disc_Usage']       = $fullsize_size;
		$stats['Original_Upload_Disc_Usage'] = $original_size;
		$stats['Total_Disc_Usage']   = (round($thumbnail_size, 2) + round($fullsize_size, 2) + round($original_size, 2)) . " MB";
		
		include_once($this->directories['template'] . '/admin-about.php');
	}
	
	
	
	
	
	
	/**********************************************
	**  Admin Panel AJAX Actions
	**********************************************/
	public function ajax_reorder_items() {
		global $wpdb;
		
		check_ajax_referer('catablog-reorder', 'security');
		
		$ids    = $_POST['ids'];
		$length = count($ids);
		
		for ($i=0; $i < $length; $i++) {
			$wpdb->update($this->db_table, array('order'=>$i), array('id'=>$ids[$i]), array('%d'), array('%d'));
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
	
	public function ajax_flush_fullsize() {
		check_ajax_referer('catablog-flush-fullsize', 'security');
		
		$this->remove_directories(array('fullsize'));
		
		$dir = 'fullsize';
		$is_dir  = is_dir($this->directories[$dir]);
		$is_file = is_file($this->directories[$dir]);
		if (!$is_dir && !$is_file) {
			mkdir($this->directories[$dir]);
		}
		
		$this->options['lightbox-enabled'] = false;
		update_option($this->options_name, $this->options);
	}
	
	public function ajax_render_fullsize() {
		check_ajax_referer('catablog-render-fullsize', 'security');
		
		$this->regenerate_all_fullsize();
		
		$this->options['lightbox-enabled'] = true;
		update_option($this->options_name, $this->options);
	}
	
	
	
	
	
	/**********************************************
	** Frontend Actions
	**********************************************/
	public function frontend_init() {
		if ($this->options['lightbox-enabled']) {
			// enqueue scripts
			wp_enqueue_script('jquery');
			wp_enqueue_script('catablog-lightbox', $this->urls['javascript'] . '/catablog.lightbox.js', array('jquery'), $this->version);
			wp_enqueue_script('catablog-ui', $this->urls['javascript'] . '/catablog.ui.js', array('jquery', 'catablog-lightbox'), $this->version);
		}
		
		wp_enqueue_style('catablog-stylesheet', $this->urls['css'] . '/catablog.css', false, $this->version);
		
		$path = get_template_directory().'/catablog.css';
		if (file_exists($path)) {
			wp_enqueue_style('catablog-custom-stylesheet', get_bloginfo('template_url') . '/catablog.css', false, $this->version);
		}
	}

	public function frontend_content($atts) {
		extract(shortcode_atts(array('tag'=>false), $atts));
		
		$results = $this->get_items($tag);
		
		ob_start();
		include $this->directories['template'] . '/frontend-view.php';
		return ob_get_clean();
	}
	
	
	
	
	
	

	

	
	
	
	
	
	
	
	public function activate() {
		global $wpdb;
		
		$options  = get_option($this->options_name);
		$table = $this->db_table;
		
		$this->remove_legacy_data();
		$this->install_directories();
		$this->install_options();
		
		// if no table present, install the database
		if($wpdb->get_var("show tables like '$table'") != $table) {
			$this->install_database();
		}
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
		$options = array();
		$options['db-version']        = $this->db_version;
		$options['dir-version']       = $this->dir_version;
		$options['thumbnail-size']    = $this->default_thumbnail_size;
		$options['image-size']        = $this->default_image_size;
		$options['background-color']  = $this->default_bg_color;
		$options['paypal-email']      = "";
		$options['keep-aspect-ratio'] = false;
		$options['lightbox-enabled']  = false;
		$options['link-target']       = "_blank";
		
		if ($this->options == false) {
			update_option($this->options_name, $options);
		}
		else {
			$this->options['db-version']        = $this->db_version;
			$this->options['dir-version']       = $this->dir_version;
			
			foreach ($options as $option_name => $option) {
				if (isset($this->options[$option_name]) === false) {
					$this->options[$option_name] = $option;
				}
			}
			
			update_option($this->options_name, $this->options);
		}
	}
	
	private function install_database() {
		include_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($this->get_database_schema());
	}
	
	private function install_directories() {
		$dirs = array(0=>'wp_uploads', 1=>'uploads', 2=>'thumbnails', 3=>'originals', 4=>'fullsize');
		
		foreach ($dirs as $dir) {
			$is_dir  = is_dir($this->directories[$dir]);
			$is_file = is_file($this->directories[$dir]);
			if (!$is_dir && !$is_file) {
				if (mkdir($this->directories[$dir]) == false) {
					// couldn't write the file to disc
				}
			}
		}
	}
	
	
	private function test_php_writable() {
		$error = false;
		$dirs = array('uploads');
		
		foreach ($dirs as $dir) {
			if (mkdir($this->directories[$dir]) === false) {
				$error = true;
				break;
			}
		}
		
		return $error;
	}
	
	
	private function remove_options() {
		delete_option($this->options_name);
	}
	
	private function remove_database() {
		global $wpdb;
		
		$table = $this->db_table;
		$drop = "DROP TABLE $table";
		$wpdb->query($drop);
	}
	
	private function remove_directories($dirs=null) {
		if ($dirs === null) {
			$dirs = array('fullsize', 'thumbnails', 'originals', 'uploads');
		}
		
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
		global $wpdb;
		
		// remove legacy options
		delete_option('image_size');
		delete_option('catablog_db_version');
		delete_option('catablog_dir_version');
		delete_option('catablog_image_size');
		
		// remove legacy database tables
		$tables = array('disc_history', 'catablog');
		foreach ($tables as $table) {
			$table = $wpdb->prefix . $table;
			if ($wpdb->get_var("show tables like '$table'") == $table) {
				$drop  = "DROP TABLE $table";
				$wpdb->query($drop);
			}			
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
		global $wpdb;
		
		$table = $this->db_table;
		$tag;
		if ($tag) {
			$query = $wpdb->prepare("SELECT * FROM `$table` WHERE `tags` LIKE %s ORDER BY `order`", array('%s'=>"%$tag%"));
		}
		else {
			$query = $wpdb->prepare("SELECT * FROM $table ORDER BY `order`", array());
		}
				
		return $wpdb->get_results($query);
	}
	
	private function get_item($id) {
		global $wpdb;
		
		$table = $this->db_table;
		$query = $wpdb->prepare("SELECT * FROM $table WHERE `id`=%d", $id);
		return $wpdb->get_row($query, ARRAY_A);
	}
	
	private function delete_item($id) {
		global $wpdb;
		
		$table = $this->db_table;
		
		// delete images
		$query = $wpdb->prepare("SELECT image FROM $table WHERE `id`=%d", $id);
		$db_filename = $wpdb->get_var($query);
		
		$dirs = array('originals', 'thumbnails', 'fullsize');
		foreach ($dirs as $dir) {
			$file_path = $this->directories[$dir] . '/' . $db_filename;
			if (is_file($file_path)) {
				unlink($file_path);
			}			
		}
		
		$query = $wpdb->prepare("DELETE FROM $table WHERE `id`=%d;", $id);
		$wpdb->query($query);
		$wpdb->flush();
	}
	
	private function save_item() {
		global $wpdb;
		
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
			if ($this->options['lightbox-enabled']) {
				if ($this->generate_fullsize($image_name, $upload) === false) {
					return false;
				}				
			}
			
			move_uploaded_file($upload, $this->directories['originals'] . "/$image_name");
		}
		
		
		if (isset($_REQUEST['id'])) {
			// old entry, need to update row
			$id = $_REQUEST['id'];
			if ($new_image) {
				$wpdb->update($table, array('image'=>$image_name, 'title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags, 'price'=>$price, 'product_code'=>$code), array('id'=>$id), array('%s', '%s', '%s', '%s', '%s', '%d', '%s'), array('%d'));
			}
			else {
				$wpdb->update($table, array('title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags, 'price'=>$price, 'product_code'=>$code), array('id'=>$id), array('%s', '%s', '%s', '%s', '%d', '%s'), array('%d'));
			}
			
			$this->wp_message('Your Changes Have Been Saved');
		} else {
			// new entry, need to insert row
			$wpdb->insert($table, array('order'=>0, 'image'=>$image_name, 'title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags, 'price'=>$price, 'product_code'=>$code), array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s'));
			$this->wp_message('New Catalog Item Added');
		}
		
		return true;		
	}
	
	private function load_xml_to_database($xml) {
		global $wpdb;
		$table = $this->db_table;
		
		$data = array();
		foreach ($xml->item as $item) {
			$row   = array();
			$row['order'] = (integer) $item->order;
			$row['image'] = (string) $item->image;
			$row['title'] = (string) $item->title;
			$row['link'] = (string) $item->link;
			$row['description'] = (string) $item->description;
			$row['tags'] = (string) $item->tags;
			$row['price'] = (integer) $item->price;
			$row['product_code'] = (string) $item->product_code;
			
			$data[] = $row;
		}
		
		foreach ($data as $row) {
			$success_message = '<li class="updated">Success: <em>' . $row['title'] . '</em> inserted into the database.</li>';
			$error_message   = '<li class="error"><strong>Error:</strong> <em>' . $row['title'] . '</em> was not inserted into the database.</li>';
			
			if (mb_strlen($row['title']) < 1) {
				echo $error_message;
			}
			else {
				if (false ==! $wpdb->insert($table, $row, array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s'))) {
					echo $success_message;
				}
				else {
					echo $error_message;
				}				
			}
		}
	}
	
	
	
	
	
	
	
	
	

	
	private function generate_thumbnail($image_name, $image_path, $force_regenerate=false) {
				
		$tmp = $image_path;
		$filepath_thumb    = $this->directories['thumbnails'] . "/$image_name";
		$filepath_original = $this->directories['originals'] . "/$image_name";

		
		if (is_uploaded_file($tmp) || $force_regenerate) {
			
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
			imagejpeg($canvas, $filepath_thumb, 90);	
			
		}
		else {
			$error_link = '[<a href="http://catablog.illproductions.net/errors#max-upload" target="_blank">Explain More</a>]';
			$this->wp_error("Image Error: Image Not Uploading, Check Your PHP Max Upload Size $error_link");
			$_REQUEST['image'] = $_REQUEST['saved_image'];
			
			return false;
		}
	}
	
	
	private function generate_fullsize($image_name, $image_path, $force_regenerate=false) {
				
		$tmp = $image_path;
		$filepath_fullsize = $this->directories['fullsize'] . "/$image_name";
		$filepath_original = $this->directories['originals'] . "/$image_name";

		
		if (is_uploaded_file($tmp) || $force_regenerate) {
			
			list($width, $height, $format) = getimagesize($tmp);
			$canvas_size = $this->options['image-size'];
			
			if ($width < 1 || $height < 1) {
				$this->wp_error('Image Error: None Supported Format');
				return false;
			}
			
			if ($width < $canvas_size && $height < $canvas_size) {
				//original is smaller, do nothing....
			}
			
			
			$ratio = ($height > $width)? ($canvas_size / $height) : ($canvas_size / $width);			
			$new_height = $height * $ratio;
			$new_width  = $width * $ratio;
			
			
			// create a blank canvas of user specified size
			$bg_color = $this->html2rgb($this->options['background-color']);
			$canvas   = imagecreatetruecolor($new_width, $new_height);
			
			
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
				
			
			if ($this->debug) {
				echo "offset: $x_offset, $y_offset<br>width: $width, $new_width<br>height: $height, $new_height";
			}
			
			imagecopyresampled($canvas, $upload, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($canvas, $filepath_fullsize, 80);	
			
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
	
	private function regenerate_all_fullsize() {
		$dir = new CataBlog_Directory($this->directories['originals']);
		foreach ($dir->getFileArray() as $file) {
			$filepath = $this->directories['originals'] . "/" . $file;
			$this->generate_fullsize($file, $filepath, true);
		}
	}
	
	
	private function regenerate_css_styles() {
		
		$path   = $this->directories['css'] . '/catablog.css';
		$styles = file_get_contents($path);
		
		// extract catablog-row css class
		$catablog_row_css_start  = stripos($styles, '.catablog-row {');
		$catablog_row_css_end    = stripos($styles, '}', $catablog_row_css_start);
		$catablog_row_css_length = $catablog_row_css_end - $catablog_row_css_start;
		$catablog_row_css        = substr($styles, $catablog_row_css_start, $catablog_row_css_length);
		
		// replace old thumbnail size with new one
		$pattern                 = '/(\d+)px/i';
		$new_size                = $this->options['thumbnail-size'] . "px";
		$catablog_row_css        =  preg_replace($pattern, $new_size, $catablog_row_css);
		
		// rebuild the css stylesheet with modified catablog-row class
		$styles_start = substr($styles, 0, $catablog_row_css_start);
		$styles_end   = substr($styles, $catablog_row_css_end);
		$styles = $styles_start . $catablog_row_css . $styles_end;
		
		if (file_put_contents($path, $styles) === false) {
			$this->wp_error('Error: did not have permission to update catablog.css, please update it manually');
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