<?php


/**********************************************
**  CataBlog Class
**********************************************/
class CataBlog {
	
	// plugin component version numbers
	private $version     = "0.9.9";
	private $dir_version = 10;
	private $db_version  = 10;
	private $debug       = false;
	
	// wordpress custom post type label
	private $custom_post_name = "catablog-items";
	private $custom_tax_name  = "catablog-terms";
	
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
		$this->options = $this->get_options();
		$wp_upload_dir = wp_upload_dir();
		
		// define common directories and files for the plugin
		$this->plugin_file               = WP_CONTENT_DIR . "/plugins/catablog/catablog.php";
		$this->directories['plugin']     = WP_CONTENT_DIR . "/plugins/catablog";
		$this->directories['css']        = WP_CONTENT_DIR . "/plugins/catablog/css";
		$this->directories['template']   = WP_CONTENT_DIR . "/plugins/catablog/templates";
		
		$this->directories['wp_uploads'] = $wp_upload_dir['basedir'];
		$this->directories['uploads']    = $wp_upload_dir['basedir'] . "/catablog";
		$this->directories['originals']  = $wp_upload_dir['basedir'] . "/catablog/originals";
		$this->directories['thumbnails'] = $wp_upload_dir['basedir'] . "/catablog/thumbnails";
		$this->directories['fullsize']   = $wp_upload_dir['basedir'] . "/catablog/fullsize";

		$this->urls['plugin']     = WP_CONTENT_URL . "/plugins/catablog";
		$this->urls['css']        = WP_CONTENT_URL . "/plugins/catablog/css";
		$this->urls['javascript'] = WP_CONTENT_URL . "/plugins/catablog/js";
		$this->urls['images']     = WP_CONTENT_URL . "/plugins/catablog/images";
		$this->urls['thumbnails'] = $wp_upload_dir['baseurl'] . "/catablog/thumbnails";
		$this->urls['originals']  = $wp_upload_dir['baseurl'] . "/catablog/originals";
	}
	
	
	
	
	
	
	
	/*****************************************************
	**       - WORDPRESS HOOKS
	*****************************************************/
	public function registerWordPressHooks() {
		// register custom post type and taxonomy
		add_action('init', array(&$this, 'register_posttype'));
		
		// register activation hooks
		register_activation_hook($this->plugin_file, array(&$this, 'activate'));
		register_deactivation_hook($this->plugin_file, array(&$this, 'deactivate'));
		
		// register admin hooks
		add_action('admin_menu', array(&$this, 'admin_menu'));
		if(strpos($_SERVER['QUERY_STRING'], 'page=catablog') !== false) {
			add_action('admin_init', array(&$this, 'admin_init'));
		}
		
		// register admin ajax actions
		add_action('wp_ajax_catablog_reorder', array($this, 'ajax_reorder_items'));
		add_action('wp_ajax_catablog_new_category', array($this, 'ajax_new_category'));
		add_action('wp_ajax_catablog_delete_category', array($this, 'ajax_delete_category'));
		add_action('wp_ajax_catablog_flush_fullsize', array($this, 'ajax_flush_fullsize'));
		add_action('wp_ajax_catablog_render_fullsize', array($this, 'ajax_render_fullsize'));
		add_action('wp_ajax_catablog_render_images', array(&$this, 'ajax_render_images'));
		
		// register frontend actions
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_init'));
		add_action('wp_print_footer_scripts', array(&$this, 'frontend_footer'));
		add_shortcode('catablog', array(&$this, 'frontend_content'));
	}
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - REGISTER CUSTOM POST TYPE 
	*****************************************************/
	public function register_posttype() {
		$name = "CataBlog Category";
		$taxonomy_labels = array();
		$taxonomy_labels['name']          = __($name . 's');
		$taxonomy_labels['singular_name'] = __($name);
		$taxonomy_labels['search_items']  = __($name);
		$taxonomy_labels['popular_items'] = __('popular items');
		$taxonomy_labels['all_items']     = __($name);
		$taxonomy_labels['parent_items']  = __($name);
		$taxonomy_labels['edit_item']     = __($name);
		$taxonomy_labels['update_item']   = __($name);
		$taxonomy_labels['add_new_item']  = __($name);
		
		$params = array();
		$params['labels']                = $taxonomy_labels;
		$params['public']                = false;
		// $params['hierarchical']          = false;
		// $params['rewrite']               = false;
		// $params['query_var']             = false;
		// $params['update_count_callback'] = array(&$this, 'count_categories');
		// $params['capabilities'] = array('manage_categories', 'edit_posts');
		register_taxonomy($this->custom_tax_name, $this->custom_post_name, $params);



		$name = "CataBlog Item";
		$post_type_labels = array();
		$post_type_labels['name']               = __($name);
		$post_type_labels['singular_name']      = __($name);
		$post_type_labels['add_new']            = __('Add New');
		$post_type_labels['add_new_item']       = __('Add New '.$name);
		$post_type_labels['edit']               = __('Edit');
		$post_type_labels['edit_item']          = __('Edit '.$name);
		$post_type_labels['new_item']           = __('New '.$name);
		$post_type_labels['view']               = __('View '.$name);
		$post_type_labels['view_item']          = __('View '.$name);
		$post_type_labels['search_item']        = __('Search '.$name);
		$post_type_labels['not_found']          = __('No '.$name.' Found');
		$post_type_labels['not_found_in_trash'] = __('No '.$name.' in Trash');

		$params = array();
		$params['labels']              = $post_type_labels;
		$params['public']              = false;
		// $params['publicly_queryable']  = false;
		// $params['show-ui']             = false;
		// $params['show_in_nav_menus']   = false;
		// $params['exclude_from_search'] = true;
		$params['supports']            = array('title', 'editor');
		$params['description']         = "A CataBlog Item";
		$params['hierarchical']        = false;
		$params['taxonomies']          = array($this->custom_tax_name);
		$params['rewrite']             = false;
		$params['menu_position']       = 45;
		$params['menu_icon']           = $this->urls['plugin']."/images/catablog-icon-16.png";
		register_post_type($this->custom_post_name, $params);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMIN MENU AND HOOKS
	*****************************************************/
	public function admin_init() {
		// if export action is being called go directly to admin_export method 
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-export') !== false) {
			$this->admin_export();
		}
		
		// display a warning message if the old database table is preset
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-clear-old-data') === false) {
			global $wpdb;
			$table = $this->db_table;
			if (($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table)) {
				$read_this_link = "<a href='http://catablog.illproductions.net/2010/11/old-database-removed/' target='_blank'>Read This</a>";
				$this->wp_error("OLD CATABLOG TABLE PRESENT! $read_this_link Immediately To Fix.");
			}
		}
		
		// display an error message if catablog options are empty or directories are missing
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-install') === false) {
			if ($this->is_installed() === false) {
				$this->wp_error("CataBlog must be setup for this site before you may use it! <a href='admin.php?page=catablog-install'>Setup CataBlog Now</a>");
			}
		}
		
		
		// load javascript libraries for admin panels
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui', $this->urls['javascript'] . '/jquery-ui-1.8.1.custom.min.js', array('jquery'), '1.8.1');
		wp_enqueue_script('catablog-admin', $this->urls['javascript'] . '/catablog-admin.js', array('jquery', 'jquery-ui'), $this->version);
		
		// load css stylesheets for admin panels
		wp_enqueue_style('jquery-ui-lightness', $this->urls['css'] . '/ui-lightness/jquery-ui-1.8.1.custom.css', false, '1.8.1');
		wp_enqueue_style('catablog-admin-css', $this->urls['css'] . '/catablog-admin.css', false, $this->version);
	}
	
	public function admin_menu() {
		// register main plugin menu
		add_object_page("Edit CataBlog", "CataBlog", $this->user_level, 'catablog', array($this, 'admin_list'), $this->urls['plugin']."/images/catablog-icon-16.png");
		
		// register main plugin pages
		add_submenu_page('catablog', "Edit CataBlog", 'CataBlog', $this->user_level, 'catablog', array(&$this, 'admin_list'));
		add_submenu_page('catablog', "Add New CataBlog", 'Add New', $this->user_level, 'catablog-new', array(&$this, 'admin_new'));
		add_submenu_page('catablog', "CataBlog Options", 'Options', $this->user_level, 'catablog-options', array(&$this, 'admin_options'));
		// add_submenu_page('catablog', "CataBlog View Option", 'View', $this->user_level, 'catablog-view', array(&$this, 'admin_view'));
		// add_submenu_page('catablog', "CataBlog Import/Export", 'Import/Export', $this->user_level, 'catablog-import-export', array(&$this, 'admin_import_export'));
		add_submenu_page('catablog', 'About CataBlog', 'About', $this->user_level, 'catablog-about', array(&$this, 'admin_about'));
		
		// register create/edit/delete catalog item actions
		add_submenu_page('catablog-hidden', "Edit CataBlog Item", "Edit", $this->user_level, 'catablog-edit', array(&$this, 'admin_edit'));
		add_submenu_page('catablog-hidden', "Save CataBlog Item", "Save", $this->user_level, 'catablog-save', array(&$this, 'admin_save'));
		add_submenu_page('catablog-hidden', "Delete CataBlog Item", "Delete", $this->user_level, 'catablog-delete', array(&$this, 'admin_delete'));
		
		// register import/export page actions to hidden menu
		add_submenu_page('catablog-hidden', "CataBlog Import", "Import", $this->user_level, 'catablog-import', array(&$this, 'admin_import'));
		add_submenu_page('catablog-hidden', "CataBlog Export", "Export", $this->user_level, 'catablog-export', array(&$this, 'admin_export'));
		add_submenu_page('catablog-hidden', "CataBlog Unlock Folders", "Unlock Folders", $this->user_level, 'catablog-unlock-folders', array(&$this, 'admin_unlock_folders'));
		add_submenu_page('catablog-hidden', "CataBlog Lock Folders", "Lock Folders", $this->user_level, 'catablog-lock-folders', array(&$this, 'admin_lock_folders'));
		add_submenu_page('catablog-hidden', "CataBlog Regenerate Images", "Regenerate Images", $this->user_level, 'catablog-regenerate-images', array(&$this, 'admin_regenerate_images'));
		add_submenu_page('catablog-hidden', "CataBlog Clear Old Data", "Clear Old Data", $this->user_level, 'catablog-clear-old-data', array(&$this, 'admin_clear_old_database'));
		
		// register about page actions to hidden menu
		add_submenu_page('catablog-hidden', "CataBlog Install", "Install", $this->user_level, 'catablog-install', array(&$this, 'admin_install'));
		add_submenu_page('catablog-hidden', "CataBlog Reset", "Reset", $this->user_level, 'catablog-reset', array(&$this, 'admin_reset_all'));
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMIN PAGES
	*****************************************************/
	
	public function admin_list() {
		$results = CataBlogItem::getItems();
		include_once($this->directories['template'] . '/admin-list.php');
	}
	
	public function admin_new() {
		$result = new CataBlogItem();
		$new_item = true;
		include_once($this->directories['template'] . '/admin-edit.php');
	}
	
	public function admin_edit() {
		$new_item = false;
		if (isset($_REQUEST['id'])) {
			$result = CataBlogItem::getItem($_REQUEST['id']);
			if (!$result) {
				$result = new CataBlogItem();
				$new_item = true;				
			}
		}
		else {
			$result = new CataBlogItem();
			$new_item = true;
		}
		
		include_once($this->directories['template'] . '/admin-edit.php');
	}
	
	public function admin_options() {
		$recalculate = false;
		
		if (isset($_REQUEST['save'])) {
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_options_nonce'], 'catablog_options' );
			if ($nonce_verified) {
				
				// strip slashes from post values
				$post_vars = array_map('stripslashes_deep', $_POST);
				
				// set default values for post message and image recalculation
				$recalculate_thumbnails = false;
				$recalculate_fullsize   = false;
				$save_message           = "CataBlog Options Saved";
				
				// get image size and rendering differences
				$image_size_different   = $post_vars['thumbnail_size'] != $this->options['thumbnail-size'];
				$bg_color_different     = $post_vars['bg_color'] != $this->options['background-color'];
				$keep_ratio_different   = isset($post_vars['keep_aspect_ratio']) != $this->options['keep-aspect-ratio'];
				$fullsize_different     = $post_vars['lightbox_image_size'] != $this->options['image-size'];
				
				// set recalculation of thumbnails and update post message
				if ($image_size_different || $bg_color_different || $keep_ratio_different) {
					$recalculate_thumbnails = true;
				}
				
				// set recalculation of fullsize images and update post message
				if (isset($post_vars['lightbox_enabled'])) {
					if ($fullsize_different) {
						$recalculate_fullsize = true;
					}
				}
				
				// save new plugins options to database
				$this->options['thumbnail-size']     = $post_vars['thumbnail_size'];
				$this->options['image-size']         = $post_vars['lightbox_image_size'];
				$this->options['lightbox-enabled']   = isset($post_vars['lightbox_enabled']);
				$this->options['background-color']   = $post_vars['bg_color'];
				$this->options['paypal-email']       = $post_vars['paypal_email'];
				$this->options['keep-aspect-ratio']  = isset($post_vars['keep_aspect_ratio']);
				$this->options['link-target']        = $post_vars['link_target'];
				$this->options['view-theme']         = $post_vars['view-code-template'];
				$this->options['view-buynow']        = $post_vars['view-code-buynow'];
				$this->options['filter-description'] = isset($post_vars['wp-filters-enabled']);
				$this->options['nl2br-description']  = isset($post_vars['nl2br-enabled']);
				$this->update_options();
				
				
				// recalculate thumbnail and fullsize images if necessary
				if ($recalculate_thumbnails || $recalculate_fullsize) {
					$recalculate = true;
					$save_message .= " - Please Let The Rendering Below Complete Before Navigating Away From This Page.";
					
					$items    = CataBlogItem::getItems();
					$item_ids = array();
					foreach ($items as $item) {
						$item_ids[] = $item->getId();
					}					
				}
				
								
				$this->wp_message($save_message);
			}
			else {
				$this->wp_error('Form Validation Error. Please reload the page and try again.');
			}
		}
		
		$thumbnail_size     = $this->options['thumbnail-size'];
		$lightbox_size      = $this->options['image-size'];
		$lightbox_enabled   = $this->options['lightbox-enabled'];
		$background_color   = $this->options['background-color'];
		$paypal_email       = $this->options['paypal-email'];
		$keep_aspect_ratio  = $this->options['keep-aspect-ratio'];
		$link_target        = $this->options['link-target'];
		$wp_filters_enabled = $this->options['filter-description'];
		$nl2br_enabled      = $this->options['nl2br-description'];
		
		include_once($this->directories['template'] . '/admin-options.php');
	}
	
	public function admin_about() {
		global $wpdb;
		
		$thumbnail_size = 'not present';
		$fullsize_size  = 'not present';
		$original_size  = 'not present';
		
		$thumb_dir = new CataBlogDirectory($this->directories['thumbnails']);
		$fullsize_dir = new CataBlogDirectory($this->directories['fullsize']);
		$original_dir = new CataBlogDirectory($this->directories['originals']);
		
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMIN ACTIONS
	*****************************************************/
	
	public function admin_save() {
		if (isset($_POST['save'])) {
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_save_nonce'], 'catablog_save' );
			if ($nonce_verified) {
				
				$post_vars = $_POST;
				$post_vars = array_map('stripslashes_deep', $post_vars);
				// $post_vars = array_map('trim', $post_vars);
				
				$post_vars['categories'] = (isset($post_vars['categories']))? $post_vars['categories'] : array();
				foreach ($post_vars['categories'] as $key => $value) {
					$post_vars['categories'][$key] = (integer) $value;
				}
				
				$result    = new CataBlogItem($post_vars);
				$new_item  = (($result->getId()) < 1);
				
				// print_r($result); die;
				
				if (mb_strlen($_FILES['new_image']['tmp_name']) > 0) {
					$result->setImage($_FILES['new_image']['tmp_name']);
				}
				
				$validate  = $result->validate();
				if ($validate === true) {
					$result->save();
					$this->admin_list();
				}
				else {
					$this->wp_error($validate);
					include_once($this->directories['template'] . '/admin-edit.php');					
				}
			}
		}
	}
	
	public function admin_delete() {
		// need to add support for nonce check
		if (isset($_REQUEST['id'])) {
			$item = CataBlogItem::getItem($_REQUEST['id']);
			if ($item) {
				$item->delete();

				$this->wp_message('Catalog Item Removed Successfully');
				$this->admin_list();
			}
			else {
				$this->wp_error('Could Not Remove Catalog Item, ID was non existant');
				$this->admin_list();
			}
		}
	}
	
	public function admin_import() {
		$error = false;
		
		
		
		$file_error_check = $this->check_file_upload_errors();
		if ($file_error_check !== true) {
			$error = ($file_error_check);
		}
		else {
			
			// TODO: Possibly add a mime-type check here
			
			$upload = $_FILES['catablog_data'];
			
			libxml_use_internal_errors(true);
			
			$xml_object = simplexml_load_file($upload['tmp_name']);
			if ($xml_object === false) {
				$error = ('Uploaded XML File Could Not Be Parsed, Check That The File\'s Content Is Valid XML.');
			}							
			// }
		}
		
		
		if ($error !== false) {
			$this->wp_error($error);
			include_once($this->directories['template'] . '/admin-import.php');
			return false;
			
			
			$this->wp_error("Upload Error: make sure you are uploading a valid xml file with a '.xml' extension");
			$this->admin_import_export();
			return false;
		}
		
		// remove all data from database if clear box is checked
		if (isset($_REQUEST['catablog_clear_db'])) {
			$items = CataBlogItem::getItems();
			foreach ($items as $item) {
				$item->delete(false);
			}
			
			$terms = get_terms($this->custom_tax_name, 'hide_empty=0');
			foreach ($terms as $term) {
				wp_delete_term($term->term_id, $this->custom_tax_name);
			}
		}
		
		
		// Private DataBase Insertion Method Called in Template:  load_xml_to_database($xml_object)
		include_once($this->directories['template'] . '/admin-import.php');
	}
	
	public function admin_export() {
		$date = date('Y-m-d');
		header('Content-type: application/xml');
		header('Content-Disposition: attachment; filename="catablog-backup-'.$date.'.xml"');

		$results = CataBlogItem::getItems();
		
		// TO BE REMOVED OR REFACTORED AT A LATER DATE
		global $wpdb;
		$table   = $this->db_table;
		$old_table_present = ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table);
		if ($old_table_present) {
			$results = array();
			$query         = $wpdb->prepare("SELECT * FROM $table");
			$query_results = $wpdb->get_results($query);
			foreach ($query_results as $query_result) {
				$order = (isset($query_result->ordinal))? $query_result->ordinal : $query_result->order;
				
				$item = new CataBlogItem();
				$item->setId($query_result->id);
				$item->setOrder($order);
				$item->setImage($query_result->image);
				$item->setTitle($query_result->title);
				$item->setLink($query_result->link);
				$item->setDescription($query_result->description);
				$item->setCategories(explode(' ', $query_result->tags));
				$item->setPrice($query_result->price);
				$item->setProductCode($query_result->product_code);
				
				$results[] = $item;
			}
		}
		// END TO BE REMOVED
		
		
		include_once($this->directories['template'] . '/admin-export.php');
		die;
	}


	public function admin_unlock_folders() {
		if ($this->unlock_directories()) {
			$this->wp_message("The CataBlog upload directories have been unlocked.");
		}
		else {
			$this->wp_error("Are you using a unix based server?");
		}
		
		$this->admin_options();
	}

	public function admin_lock_folders() {
		if ($this->lock_directories()) {
			$this->wp_message("The CataBlog upload directories have been locked.");
		}
		else {
			$this->wp_error("Are you using a unix based server?");
		}
		
		$this->admin_options();
	}
	
	
	public function admin_regenerate_images() {
		
		$items    = CataBlogItem::getItems();
		$item_ids = array();
		
		foreach ($items as $item) {
			$item_ids[] = $item->getId();
		}
		
		include_once($this->directories['template'] . '/admin-regenerate.php');
	}
	
	public function admin_clear_old_database() {
		$this->remove_legacy_data();
		$this->remove_database();
		$this->wp_message("The Old CataBlog Database Table has been removed.");
		$this->admin_options();
	}
	
	public function admin_install() {
		$this->install_options();
		$this->install_directories();
		
		$this->wp_message('CataBlog options and directories have been successfully installed.');
		$this->admin_list();
	}
	
	public function admin_reset_all() {
		// remove all catablog posts
		$items = CataBlogItem::getItems();
		foreach ($items as $item) {
			$item->delete();
		}
		
		// remove all catablog categories
		$categories = get_categories(array('hide_empty'=>0, 'hierarchical'=>1, 'taxonomy'=>$this->custom_tax_name));
		foreach ($categories as $category) {
			wp_delete_term($category->cat_ID, $this->custom_tax_name);
		}
		
		$this->remove_legacy_data();
		$this->remove_options();
		$this->remove_database();
		$this->remove_directories();
		
		$this->install_options();
		$this->install_directories();
		
		$this->wp_message('System has been cleared of all CataBlog data and the default options reset.');
		$this->admin_options();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMIN AJAX ACTIONS
	*****************************************************/
	public function ajax_reorder_items() {
		check_ajax_referer('catablog-reorder', 'security');
		
		$ids    = $_POST['ids'];
		$length = count($ids);
		
		for ($i=0; $i < $length; $i++) {
			$item = CataBlogItem::getItem($ids[$i]);
			$item->setOrder($i);
			$item->save();
		}
		
		die();
	}
	
	public function ajax_new_category() {
		check_ajax_referer('catablog-new-category', 'security');
		
		$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
		
		$category_name = trim($_REQUEST['name']);
		$category_slug = $this->string2slug($category_name);
		
		$char_check = preg_match('/[^a-zA-Z0-9_ -]/', $category_name);
		if ($char_check > 0) {
			echo "({'success':false, 'error':'Please only use these characters in your category name: letters, numbers, space, dash and underscore.'})";
			die;
		};
		
		$string_length = mb_strlen($category_name);
		if ($string_length < 1) {
			echo "({'success':false, 'error':'Please be a little more specific with your category name.'})";
			die;			
		}
		
		$attr = array('slug'=>$category_slug);
		$new_category_id = wp_insert_term($category_name, $this->custom_tax_name, $attr);
		
		if (isset($new_category_id['term_id'])) {
			echo "({'success':true, 'id':".$new_category_id['term_id'].", 'name':'$category_name'})";
		}
		else {
			$error_string = "";
			foreach ($new_category_id->get_error_messages() as $error) {
				$error_string = $error . "  ";
			}
			echo "({'success':false, 'error':'".$error_string."'})";			
		}

		
		die();
	}
	
	public function ajax_delete_category() {
		check_ajax_referer('catablog-delete-category', 'security');
		
		$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
		
		$term_id = (integer) trim($_REQUEST['term_id']);
		if(wp_delete_term($term_id, $this->custom_tax_name)) {
			echo "({'success':true, 'message':'Term Removed Successfully.'})";
		}
		else {
			echo "({'success':false, 'error':'Term did not exist, please refresh page and try again.'})";
		}
		
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
		$this->update_options();
		
		die();
	}
	
	public function ajax_render_fullsize() {
		check_ajax_referer('catablog-render-fullsize', 'security');
		
		$items = CataBlogItem::getItems();
		foreach ($items as $item) {
			$item->makeFullsize();
		}
		
		$this->options['lightbox-enabled'] = true;
		$this->update_options();
		
		die();
	}
	
	public function ajax_render_images() {
		check_ajax_referer('catablog-render-images', 'security');
		
		$id   = $_REQUEST['id'];
		$item = CataBlogItem::getItem($id);
		
		$complete = $item->MakeThumbnail();
		if ($complete !== true) {
			echo "({'success':false, 'error':'$complete'})";
			die;
		}
		
		if ($this->options['lightbox-enabled']) {
			$complete = $item->MakeFullsize();
			if ($complete !== true) {
				echo "({'success':false, 'error':'$complete'})";
				die;				
			}
		}
		
		echo "({'success':true, 'message':'render complete'})";
		
		die();
	}
	






	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - FRONTEND ACTIONS
	*****************************************************/
	public function frontend_init() {
		if ($this->options['lightbox-enabled']) {
			wp_enqueue_script('catablog-lightbox', $this->urls['javascript'] . '/catablog.lightbox.js', array('jquery'), $this->version);
		}
		
		wp_enqueue_style('catablog-stylesheet', $this->urls['css'] . '/catablog.css', false, $this->version);
		
		$path = get_stylesheet_directory().'/catablog.css';
		if (file_exists($path)) {
			wp_enqueue_style('catablog-custom-stylesheet', get_bloginfo('stylesheet_directory') . '/catablog.css', false, $this->version);
		}
	}
	
	public function frontend_footer() {
		if (!is_admin()) {
			if ($this->options['lightbox-enabled']) {
				 echo "<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('.catablog-clickable').catablogLightbox(); });</script>\n";
			}			
		}
	}

	public function frontend_content($atts) {
		
		extract(shortcode_atts(array('category'=>false, 'tag'=>false), $atts));
			
		$thumbnail_size = $this->options['thumbnail-size'];
		
		$values = array();
		$values['image-size']        = $thumbnail_size;
		$values['paypal-email']      = $this->options['paypal-email'];
		$values['min-height']        = "style='min-height:$thumbnail_size"."px; height:auto !important; height:$thumbnail_size"."px;'";
		$values['hover-title-size']  = ($thumbnail_size - 10) . 'px';
		$values['margin-left']       = ($thumbnail_size + 10) . 'px';
		$values['lightbox']          = ($this->options['lightbox-enabled'])? "catablog-clickable" : "";
		
		
		// if an old tag attribute is used put it in categories instead
		if ($category === false && $tag !== false) {
			$category = $tag;
		}
		
		// get items and start the output buffer
		$results = CataBlogItem::getItems($category);
		ob_start();
		
		foreach ($results as $result) {
			
			// check if theme is empty, if so use default theme
			$string = $this->options['view-theme'];
			if (mb_strlen($string) == 0) {
				$string = file_get_contents($this->directories['template'] . '/views/default.htm');
			}
			
			// filter description if neccessary
			$description = $result->getDescription();
			if ($this->options['filter-description']) {
				$description = apply_filters('the_content', $description);				
			}
			if ($this->options['nl2br-description']) {
				$description = nl2br($description);
			}
			
			// set the values of the item into an array
			$values['image']           = $this->urls['thumbnails'] . "/". $result->getImage();
			$values['title']           = (mb_strlen($result->getLink()) > 0)? "<a href='".$result->getLink()."' target='".$this->options['link-target']."'>".$result->getTitle()."</a>" : $result->getTitle();
			$values['title-text']      = $result->getTitle();
			$values['link']            = $result->getLink();
			$values['description']     = $description;
			$values['price']           = number_format(((float)($result->getPrice())), 2, '.', '');
			$values['product-code']    = $result->getProductCode();
			
			
			// generate the buy now button if the price of the item is greater then 0
			$buy_now_button = '';
			if ($values['price'] > 0) {
				$buy_now_button = $this->options['view-buynow'];
				foreach ($values as $key => $value) {
					$search         = "%" . strtoupper($key) . "%";
					$buy_now_button = str_replace($search, $value, $buy_now_button);
				}
			}
			$values['buy-now-button']  = $buy_now_button;
			
			
			// loop through each items array of values and replace tokens
			foreach($values as $key => $value) {
				$search  = "%" . strtoupper($key) . "%";
				$string  = str_replace($search, $value, $string);
			}
			
			
			// write the string to the current output buffer
			echo $string;
		}
		
		return ob_get_clean();
	}
	
	

	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - INSTALLATION METHODS
	*****************************************************/
	
	public function is_installed() {
		
		if ($this->options == false) {
			return false;
		}		
		$dirs = array(0=>'wp_uploads', 1=>'uploads', 2=>'thumbnails', 3=>'originals', 4=>'fullsize');
		foreach ($dirs as $dir) {
			$is_dir = is_dir($this->directories[$dir]);
			if ($is_dir === false) {
				return false;
			}
		}
		
		return true;
	}
	
	public function activate() {
		$this->install_directories();
		$this->install_options();
	}
	
	private function install_options() {
		$default_options = array();
		$default_options['db-version']         = $this->db_version;
		$default_options['dir-version']        = $this->dir_version;
		$default_options['thumbnail-size']     = $this->default_thumbnail_size;
		$default_options['image-size']         = $this->default_image_size;
		$default_options['background-color']   = $this->default_bg_color;
		$default_options['paypal-email']       = "";
		$default_options['keep-aspect-ratio']  = false;
		$default_options['lightbox-enabled']   = false;
		$default_options['link-target']        = "_blank";
		$default_options['view-theme']         = file_get_contents($this->directories['template'] . '/views/default.htm');
		$default_options['view-buynow']        = '';
		$default_options['filter-description'] = false;
		$default_options['nl2br-description']  = true;
		
		if ($this->options == false) {
			$this->options = $default_options;
			$this->update_options();
		}
		else {
			foreach ($default_options as $option_name => $option) {
				if (isset($this->options[$option_name]) === false) {
					$this->options[$option_name] = $option;
				}
			}
			$this->update_options();
		}
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - REMOVAL METHODS
	*****************************************************/
	
	public function deactivate() {
		
	}
	
	private function remove() {
		$this->remove_legacy_data();
		$this->remove_options();
		$this->remove_database();
		$this->remove_directories();
	}
	
	private function remove_options() {
		delete_option($this->options_name);
		$this->options = array();
	}
	
	private function remove_database() {
		global $wpdb;
		
		$table = $this->db_table;
		
		// if table present, remove it
		if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
			$drop = "DROP TABLE $table";
			$wpdb->query($drop);	
		}
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
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMINISTRATION METHODS
	*****************************************************/
	
	private function load_xml_to_database($xml) {
		$data = array();
		foreach ($xml->item as $item) {
			$row   = array();
			
			$row['id']           = null;
			$row['order']        = (integer) ((isset($item->order))? $item->order : $item->ordinal);
			$row['image']        = (string) $item->image;
			$row['title']        = (string) $item->title;
			$row['link']         = (string) $item->link;
			$row['description']  = (string) $item->description;
			$row['price']        = (integer) $item->price;
			$row['product_code'] = (string) $item->product_code;
			
			$terms = array();
			if (isset($item->tags)) {
				$tags  = (string) $item->tags;
				$terms = explode(' ', trim($tags));
			}
			else {
				foreach($item->categories as $categories) {
					foreach ($categories as $category) {
						$terms[] = (string) $category;
					}
				}
			}
			
			foreach ($terms as $key => $term) {
				if (mb_strlen($term) > 0) {
					$term_object = get_term_by('name', $term, $this->custom_tax_name);
					if ($term_object !== false) {
						$terms[$key] = ((integer) $term_object->term_id);
					}
					else {
						$category_slug = $this->string2slug($term);
						$attr          = array('slug'=>$category_slug);
						$insert_return = wp_insert_term($term, $this->custom_tax_name, $attr);
						
						if ($insert_return instanceof WP_Error) {
							foreach ($insert_return->get_error_messages() as $error) {
								echo "<li class='error'>Create Term Error - <strong>".$row['title']."</strong>: $error</li>";
							}
						}
						else {
							if (isset($new_term_id['term_id'])) {
								$new_term_id = $new_term_id['term_id'];
								if ($new_term_id > 0) {
									$terms[$key] = (integer) $new_term_id;
								}
							}							
						}


					}					
				} 
			}
			
			$row['categories']   = $terms;
			
			
			$data[] = $row;
		}
		
		// print_r($data); die;
		
		foreach ($data as $row) {
			$success_message = '<li class="updated">Success: <em>' . $row['title'] . '</em> inserted into the database.</li>';
			$error_message   = '<li class="error"><strong>Error:</strong> <em>' . $row['title'] . '</em> was not inserted into the database.</li>';
			
			if (mb_strlen($row['title']) < 1) {
				echo $error_message;
			}
			else {
				$item = new CataBlogItem($row);
				if ($item->save() !== false) {
					echo $success_message;
				}
				else {
					echo $error_message;
				}				
			}
		}
	}
	
	private function unlock_directories() {
		$success = true;
		$dirs = array(1=>'uploads', 2=>'thumbnails', 3=>'originals', 4=>'fullsize');
		
		foreach ($dirs as $dir) {
			$is_dir  = is_dir($this->directories[$dir]);
			if ($is_dir) {
				if (chmod($this->directories[$dir], 0777) === false) {
					$success = false;
				}
			}
		}
		
		return $success;
	}
	
	private function lock_directories() {
		$success = true;
		$dirs = array(1=>'uploads', 2=>'thumbnails', 3=>'originals', 4=>'fullsize');
		
		foreach ($dirs as $dir) {
			$is_dir  = is_dir($this->directories[$dir]);
			if ($is_dir) {
				if (chmod($this->directories[$dir], 0755) === false) {
					$success = false;
				}
			}
		}
		
		return $success;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - HELPER METHODS
	*****************************************************/
	private function check_file_upload_errors() {
		foreach($_FILES as $file) {
			if ($file['error'] > 0) {
				switch($file['error']) {
					case UPLAOD_ERR_INI_SIZE:
						return "Uploaded File Exceeded The PHP Configurations Max File Size.";
						break;
					case UPLOAD_ERR_FORM_SIZE:
						return "Upload File Exceeded The HTML Form\'s Max File Size.";
						break;
					case UPLOAD_ERR_PARTIAL:
						return "File Only Partially Uploaded, Please Try Again.";
						break;
					case UPLOAD_ERR_NO_FILE:
						return "No File Selected For Upload. Please Resubmit The Form.";
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						return "Your Server\'s PHP Configuration Does Not Have A Temporary Folder For Uploads, Please Contact The System Admin.";
						break;
					case UPLOAD_ERR_CANT_WRITE:
						return "Your Server\'s PHP Configuration Can Not Write To Disc, Please Contact The System Admin.";
						break;
					case UPLOAD_ERR_EXTENSION:
						return "A PHP Extension Is Blocking PHP From Excepting Uploads, Please Contact The System Admin.";
						break;
					default:
						return "An Unknown Upload Error Has Occurred";
				}
			}
		}
		
		return true;
	}
	private function update_options() {
		update_option($this->options_name, $this->options);
	}
	
	private function get_options() {
		return get_option($this->options_name);
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
		echo "<div id='message' class='error'>";
		echo "	<strong>$message</strong>";
		echo "</div>";
	}

	
	




		
}