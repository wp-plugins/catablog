<?php


/**********************************************
**  CataBlog Class
**********************************************/
class CataBlog {
	
	// plugin component version numbers
	private $version     = "1.2.1c";
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
	private $user_level = 'edit_pages';
	
	// default image sizes
	private $default_thumbnail_size = 100;
	private $default_image_size     = 600;
	private $default_bg_color       = "#ffffff";
	
	// two private arrays for storing common file paths
	private $directories   = array();
	private $urls          = array();
	
	private $terms             = NULL;
	private $default_term      = NULL;
	private $default_term_name = "Uncategorized";
	
	
	public function __construct() {
		
		// get plugin options from wp database
		$this->options = $this->get_options();
		
		$wp_upload_dir = wp_upload_dir();
		
		// define common directories and files for the plugin
		$this->plugin_file               = WP_CONTENT_DIR . "/plugins/catablog/catablog.php";
		$this->directories['plugin']     = WP_CONTENT_DIR . "/plugins/catablog";
		$this->directories['css']        = WP_CONTENT_DIR . "/plugins/catablog/css";
		$this->directories['template']   = WP_CONTENT_DIR . "/plugins/catablog/templates";
		$this->directories['views']      = WP_CONTENT_DIR . "/plugins/catablog/templates/views";
		$this->directories['buttons']    = WP_CONTENT_DIR . "/plugins/catablog/templates/buttons";
		
		$this->directories['wp_uploads'] = $wp_upload_dir['basedir'];
		$this->directories['uploads']    = $wp_upload_dir['basedir'] . "/catablog";
		$this->directories['originals']  = $wp_upload_dir['basedir'] . "/catablog/originals";
		$this->directories['thumbnails'] = $wp_upload_dir['basedir'] . "/catablog/thumbnails";
		$this->directories['fullsize']   = $wp_upload_dir['basedir'] . "/catablog/fullsize";

		$this->urls['plugin']     = WP_CONTENT_URL . "/plugins/catablog";
		$this->urls['css']        = WP_CONTENT_URL . "/plugins/catablog/css";
		$this->urls['javascript'] = WP_CONTENT_URL . "/plugins/catablog/js";
		$this->urls['images']     = WP_CONTENT_URL . "/plugins/catablog/images";
		$this->urls['template']   = WP_CONTENT_URL . "/plugins/catablog/templates";
		$this->urls['views']      = WP_CONTENT_URL . "/plugins/catablog/templates/views";
		$this->urls['buttons']    = WP_CONTENT_URL . "/plugins/catablog/templates/buttons";
		
		$this->urls['originals']  = $wp_upload_dir['baseurl'] . "/catablog/originals";
		$this->urls['thumbnails'] = $wp_upload_dir['baseurl'] . "/catablog/thumbnails";
		$this->urls['fullsize']   = $wp_upload_dir['baseurl'] . "/catablog/fullsize";
	}
	
	
	
	
	
	
	
	/*****************************************************
	**       - WORDPRESS HOOKS
	*****************************************************/
	public function registerWordPressHooks() {
		// register custom post type and taxonomy
		add_action('init', array(&$this, 'initialize_plugin'), 0);
		
		// register activation hooks
		register_activation_hook($this->plugin_file, array(&$this, 'activate'));
		register_deactivation_hook($this->plugin_file, array(&$this, 'deactivate'));
		
		// register admin hooks
		if (is_admin()) {
			add_action('admin_menu', array(&$this, 'admin_menu'));
			if (strpos($_SERVER['QUERY_STRING'], 'page=catablog') !== false) {
				add_action('admin_init', array(&$this, 'admin_init'));
			}
			
			// register admin ajax actions
			// add_action('wp_ajax_catablog_fetch', array($this, 'ajax_fetch_items'));
			add_action('wp_ajax_catablog_reorder', array($this, 'ajax_reorder_items'));
			add_action('wp_ajax_catablog_new_category', array($this, 'ajax_new_category'));
			add_action('wp_ajax_catablog_delete_category', array($this, 'ajax_delete_category'));
			add_action('wp_ajax_catablog_flush_fullsize', array($this, 'ajax_flush_fullsize'));
			add_action('wp_ajax_catablog_render_images', array(&$this, 'ajax_render_images'));
			add_action('wp_ajax_catablog_delete_subimage', array(&$this, 'ajax_delete_subimage'));
			
		}
		
		// register frontend actions
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_init'));
		add_action('wp_print_footer_scripts', array(&$this, 'frontend_footer'));
		add_shortcode('catablog', array(&$this, 'frontend_content'));
		
		// add_filter('the_content', array(&$this, 'frontend_catalog_item_page'));
	}
	
	
	
	
	
	
	
	/*****************************************************
	**       - REGISTER CUSTOM POST TYPE 
	*****************************************************/
	public function initialize_plugin() {
		
		$params['label']              = "CataBlog Item";
		$params['public']              = false;
		$params['rewrite']             = false;
		$params['supports']            = array('title', 'editor');
		$params['description']         = "A CataBlog Plugin Catalog Item";
		$params['hierarchical']        = false;
		$params['taxonomies']          = array($this->custom_tax_name);
		$params['menu_position']       = 45;
		$params['menu_icon']           = $this->urls['plugin']."/images/catablog-icon-16.png";
		register_post_type($this->custom_post_name, $params);
		
		
		
		$params = array();
		$params['hierarchical']          = false;
		$params['label']                = "CataBlog Category";
		$params['query_var']             = true;
		$params['rewrite']               = false;
		$params['public']                = false;
		register_taxonomy($this->custom_tax_name, $this->custom_post_name, $params);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMIN MENU AND HOOKS
	*****************************************************/
	public function admin_init() {
		
		// go straigt to create new item action, no interface
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-create') !== false) {
			$this->admin_create(true);
		}

		// go straigt to save item action, no interface
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-save') !== false) {
			$this->admin_save(true);
		}

		// go straigt to delete item action, no interface
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-delete') !== false) {
			$this->admin_delete(true);
		}
		
		
		
		// if export action is being called go directly to admin_export method 
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-export') !== false) {
			$this->admin_export();
		}
		
		
		
		// go striaght to replace main image action
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-replace-image') !== false) {
			$this->admin_replace_main_image(true);
		}
		
		// go straight to add subimage action
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-add-subimage') !== false) {
			$this->admin_add_subimage(true);
		}
		
				
		// display an error message if catablog options are empty or directories are missing
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-install') === false) {
			if ($this->is_installed() === false) {
				$this->wp_error("CataBlog must be setup for this site before you may use it! <a href='admin.php?page=catablog-install'>Setup CataBlog Now</a>");
			}
		}
		
		
		// set cookie to remember view selection
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-install') === false) {
			if(isset($_REQUEST['view'])){
				if($_REQUEST['view'] == 'grid') {
					setCookie('catablog-view-cookie', 'grid', (time()+36000000));
				}
				elseif ($_REQUEST['view'] == 'list') {
					setCookie('catablog-view-cookie', 'grid', (time()-3600));
				}
			}
		}
		
		
		// load javascript libraries for admin panels
		wp_enqueue_script('jquery');		
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('farbtastic');
		wp_enqueue_style('farbtastic');
		wp_enqueue_script('catablog-admin', $this->urls['javascript'] . '/catablog-admin.js', array('jquery'), $this->version);
		
		
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
		add_submenu_page('catablog', 'About CataBlog', 'About', $this->user_level, 'catablog-about', array(&$this, 'admin_about'));
		
		// register create/edit/delete catalog item actions
		add_submenu_page('catablog-hidden', "Create CataBlog Item", "Create", $this->user_level, 'catablog-create', array(&$this, 'admin_create'));
		add_submenu_page('catablog-hidden', "Save CataBlog Item", "Save", $this->user_level, 'catablog-save', array(&$this, 'admin_save'));
		add_submenu_page('catablog-hidden', "Delete CataBlog Item", "Delete", $this->user_level, 'catablog-delete', array(&$this, 'admin_delete'));
		add_submenu_page('catablog-hidden', "Bulk Edit CataBlog Items", "Bulk", $this->user_level, 'catablog-bulkedit', array(&$this, 'admin_bulk_edit'));
		add_submenu_page('catablog-hidden', "Replace Main Image", "Replace", $this->user_level, 'catablog-replace-image', array(&$this, 'admin_replace_main_image'));
		add_submenu_page('catablog-hidden', "Add SubImage", "SubImage", $this->user_level, 'catablog-add-subimage', array(&$this, 'admin_add_subimage'));
		
		// register import/export page actions to hidden menu
		add_submenu_page('catablog-hidden', "CataBlog Import", "Import", $this->user_level, 'catablog-import', array(&$this, 'admin_import'));
		add_submenu_page('catablog-hidden', "CataBlog Export", "Export", $this->user_level, 'catablog-export', array(&$this, 'admin_export'));
		add_submenu_page('catablog-hidden', "CataBlog Unlock Folders", "Unlock Folders", $this->user_level, 'catablog-unlock-folders', array(&$this, 'admin_unlock_folders'));
		add_submenu_page('catablog-hidden', "CataBlog Lock Folders", "Lock Folders", $this->user_level, 'catablog-lock-folders', array(&$this, 'admin_lock_folders'));
		add_submenu_page('catablog-hidden', "CataBlog Regenerate Images", "Regenerate Images", $this->user_level, 'catablog-regenerate-images', array(&$this, 'admin_regenerate_images'));
		add_submenu_page('catablog-hidden', "CataBlog Rescan Images", "Rescan Images Folder", $this->user_level, 'catablog-rescan-images', array(&$this, 'admin_rescan_images'));
		add_submenu_page('catablog-hidden', "CataBlog Clear Old Data", "Clear Old Data", $this->user_level, 'catablog-clear-old-data', array(&$this, 'admin_clear_old_database'));
		
		// register about page actions to hidden menu
		add_submenu_page('catablog-hidden', "CataBlog Install", "Install", $this->user_level, 'catablog-install', array(&$this, 'admin_install'));
		add_submenu_page('catablog-hidden', "CataBlog Reset", "Reset", $this->user_level, 'catablog-reset', array(&$this, 'admin_reset_all'));
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMIN PAGES
	*****************************************************/
	
	public function admin_list() {
		
		// if id is set show edit form
		if (isset($_GET['id'])) {
			$this->admin_edit();
			return false;
		}
		
		$limit = $this->items_per_page;
		$offset = 0;
		
		$category_filter = false;
		
		$selected_term = $this->get_default_term();
		if (isset($_GET['category']) && is_numeric($_GET['category'])) {
			$selected_term = false;
			if ($_GET['category'] > 0) {
				$selected_term = get_term_by('id', $_GET['category'], $this->custom_tax_name);
			}
		}
		
		if ($selected_term) {
			$category_filter = $selected_term->slug;
		}
		
		$results    = CataBlogItem::getItems($category_filter);
		
		$view = 'list';
		if (isset($_COOKIE['catablog-view-cookie'])) {
			$view = 'grid';
		}
		if (isset($_REQUEST['view'])) {
			if ($_REQUEST['view'] == 'grid') {
				$view = 'grid';
			}
			else {
				$view = 'list';
			}
		}
		
		switch ($_GET['message']) {
			case 2:
				$this->wp_message("Catalog Item Deleted Successfully.");
				break;
			case 3:
				$this->wp_error("Could Not Delete Item Because ID was non existent.");
		}
		
		include_once($this->directories['template'] . '/admin-items.php');
	}
	
	public function admin_edit() {
		if (isset($_GET['id'])) {
			
			$result = CataBlogItem::getItem($_GET['id']);
			if (!$result) {
				include_once($this->directories['template'] . '/admin-404.php');
				return false;
			}
			
			switch ($_GET['message']) {
				case 1:
					$this->wp_message("Changes Saved Successfully");
					break;
			}
			
			include_once($this->directories['template'] . '/admin-edit.php');
		}
	}
	
	public function admin_new() {
		if (function_exists('is_upload_space_available') && is_upload_space_available() == false) {
			include_once($this->directories['template'] . '/admin-discfull.php');
		}
		else {
			include_once($this->directories['template'] . '/admin-new.php');
		}		
	}
	
	public function admin_options() {
		$recalculate = false;
		
		if (isset($_REQUEST['save'])) {
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_options_nonce'], 'catablog_options' );
			if ($nonce_verified) {
				
				// strip slashes from post values
				$post_vars = array_map('stripslashes_deep', $_POST);
				$post_vars = array_map('trim', $post_vars);
				
				// validate post data
				$post_vars['link_relationship'] = preg_replace('/[^a-z0-9_-]/', '', $post_vars['link_relationship']);
				
				
				// set default values for post message and image recalculation
				$recalculate_thumbnails = false;
				$recalculate_fullsize   = false;
				$rewrite_permalinks     = false;
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
					$fullsize_dir = new CataBlogDirectory($this->directories['fullsize']);
					if ($fullsize_different || $fullsize_dir->getCount() < 1) {
						$recalculate_fullsize = true;
					}
				}
				
				// save new plugins options to database
				$this->options['thumbnail-size']       = $post_vars['thumbnail_size'];
				$this->options['image-size']           = $post_vars['lightbox_image_size'];
				$this->options['lightbox-enabled']     = isset($post_vars['lightbox_enabled']);
				$this->options['background-color']     = $post_vars['bg_color'];
				$this->options['paypal-email']         = $post_vars['paypal_email'];
				$this->options['keep-aspect-ratio']    = isset($post_vars['keep_aspect_ratio']);
				$this->options['link-target']          = strip_tags($post_vars['link_target']);
				$this->options['link-relationship']    = strip_tags($post_vars['link_relationship']);
				$this->options['view-theme']           = $post_vars['view-code-template'];
				$this->options['view-buynow']          = $post_vars['view-code-buynow'];
				$this->options['filter-description']   = isset($post_vars['wp-filters-enabled']);
				$this->options['nl2br-description']    = isset($post_vars['nl2br-enabled']);
				
				$this->update_options();
				
				// recalculate thumbnail and fullsize images if necessary
				if ($recalculate_thumbnails || $recalculate_fullsize) {
					$save_message .= " - Please Let The Rendering Below Complete Before Navigating Away From This Page";
					
					delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
					
					$items       = CataBlogItem::getItems(false, false);
					$image_names = array();

					foreach ($items as $item) {
						$image_names[] = $item->getImage();
						foreach ($item->getSubImages() as $image) {
							$image_names[] = $image;
						}
					}					
				}
				
				$this->wp_message($save_message);
			}
			else {
				$this->wp_error('Form Validation Error. Please reload the page and try again.');
			}
		}
		
		$thumbnail_size               = $this->options['thumbnail-size'];
		$lightbox_size                = $this->options['image-size'];
		$lightbox_enabled             = $this->options['lightbox-enabled'];
		$background_color             = $this->options['background-color'];
		$paypal_email                 = $this->options['paypal-email'];
		$keep_aspect_ratio            = $this->options['keep-aspect-ratio'];
		$link_target                  = $this->options['link-target'];
		$link_relationship            = $this->options['link-relationship'];
		$wp_filters_enabled           = $this->options['filter-description'];
		$nl2br_enabled                = $this->options['nl2br-description'];
		
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
	public function admin_create($init_run=false) {
		$error = false;
		
		$new_item = new CataBlogItem();
		$nonce_verified = true; //wp_verify_nonce( $_REQUEST['_catablog_create_nonce'], 'catablog_create' );
		if ($nonce_verified) {
			
			$tmp_name = $_FILES['new_image']['tmp_name'];
			
			if (mb_strlen($tmp_name) > 0) {
				$validate = $new_item->validateImage($tmp_name);
				if ($validate === true) {
					
					$new_item_title = $_FILES['new_image']['name'];
					$new_item_title = preg_replace('/\.[^.]+$/','',$new_item_title);
					$new_item_title = str_replace(array('_','-','.'), ' ', $new_item_title);
					$new_item_order = wp_count_posts($this->custom_post_name)->publish;
					
					$new_item->setOrder($new_item_order);
					$new_item->setTitle($new_item_title);
					
					$new_item->setImage($tmp_name);
					$new_item->setSubImages(array());

					$default_term = $this->get_default_term();
					$new_item->setCategories(array($default_term->term_id=>$default_term->name));
					
					$new_item->save();

					header('Location: admin.php?page=catablog&id=' . $new_item->getId()); die;
				}
				else {
					$error = $validate;
				}
			}
			else {
				$error = "The file you selected was to large or you didn't select anything at all, please try again.";
			}
		}
		else {
			$error = "Could not validate the WordPress Nonce, reload the form and try again...";
		}
			
		if (!$init_run && $error !== false) {
			$this->wp_error($error);
			include_once($this->directories['template'] . '/admin-new.php');
		}
	}
	
	
	public function admin_save($init_run=false) {
		$error = false;
		
		if (isset($_POST['save'])) {
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_save_nonce'], 'catablog_save' );
			if ($nonce_verified) {
				$post_vars = $_POST;
				$post_vars = array_map('stripslashes_deep', $post_vars);
				
				$trim_fields = array('title', 'description', 'link', 'price', 'product_code');
				foreach ($trim_fields as $field) {
					$post_vars[$field] = trim($post_vars[$field]);
				}
				
				$post_vars['categories'] = (isset($post_vars['categories']))? $post_vars['categories'] : array();
				foreach ($post_vars['categories'] as $key => $value) {
					$post_vars['categories'][$key] = (integer) $value;
				}
				
				$result    = new CataBlogItem($post_vars);
				$validate  = $result->validate();
				if ($validate === true) {
					$write = $result->save();
					if ($write === true) {
						header('Location: admin.php?page=catablog&id=' . $result->getId() . '&message=1'); die;
					}
					else {
						$error = $write;
					}
					
				}
				else {
					$error = $validate;
				}
			}
			else {
				$error = "could not verify wordpress nonce";
			}
		}
		else {
			$error = "full form was not submitted, please try again.";
		}
		
		if (!$init_run && $error) {
			$this->wp_error($error);
			include_once($this->directories['template'] . '/admin-edit.php');
			return true;
		}
	}
	
	public function admin_replace_main_image($init_run=false) {
		$error = false;
		
		if (is_numeric($_POST['id'])) {
			$result = CataBlogItem::getItem($_POST['id']);
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_replace_image_nonce'], 'catablog_replace_image' );
			if ($nonce_verified) {
				
				$tmp_name = $_FILES['new_image']['tmp_name'];
				
				if (mb_strlen($tmp_name) > 0) {
					$validate = $result->validateImage($tmp_name);
					if ($validate === true) {
						$to_delete = array();
						$to_delete["original"]  = $this->directories['originals'] . "/" . $result->getImage();
						$to_delete["thumbnail"] = $this->directories['thumbnails'] . "/" . $result->getImage();
						$to_delete["fullsize"]  = $this->directories['fullsize'] . "/" . $result->getImage();
						
						$result->setImage($tmp_name);
						
						foreach ($to_delete as $file) {
							if (is_file($file)) {
								unlink($file);
							}
						}
						
						$result->save();
						header('Location: admin.php?page=catablog&id=' . $_POST['id']); die;
					}
					else {
						$error = $validate;
					}
				}
				else {
					$error = "You didn't select anything to upload, please try again.";
				}
			}
			else {
				$error = "WordPress Nonce Error, reload the form and try again...";
			}
		}
		else {
			$error = "No item ID posted, press back arrow and try again.";
		}
		
		if (!$init_run && $error !== false) {
			$this->wp_error($error);
			include_once($this->directories['template'] . '/admin-edit.php');
		}
	}
	
	public function admin_add_subimage($init_run=false) {
		$error = false;
		
		if (is_numeric($_POST['id'])) {
			$result = CataBlogItem::getItem($_POST['id']);
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_add_subimage_nonce'], 'catablog_add_subimage' );
			if ($nonce_verified) {
				
				$tmp_name = $_FILES['new_sub_image']['tmp_name'];
				
				if (mb_strlen($tmp_name) > 0) {
					$validate = $result->validateImage($tmp_name);
					if ($validate === true) {
						$result->addSubImage($tmp_name);
						header('Location: admin.php?page=catablog&id=' . $_POST['id']); die;
					}
					else {
						$error = $validate;
					}
				}
				else {
					$error = "You didn't select anything to upload, please try again.";
				}
			}
			else {
				$error = "WordPress Nonce Error, reload the form and try again...";
			}
		}
		else {
			$error = "No item ID posted, press back arrow and try again.";
		}
		
		if (!$init_run && $error !== false) {
			$this->wp_error($error);
			include_once($this->directories['template'] . '/admin-edit.php');
		}
	}
	
	
	public function admin_delete($init_run=false) {
		$error = false;
		
		// need to add support for nonce check
		if (isset($_REQUEST['id'])) {
			$item = CataBlogItem::getItem($_REQUEST['id']);
			if ($item) {
				$item->delete();
				$this->reorder_all_items();
				header('Location: admin.php?page=catablog&message=2'); die;
			}
			else {
				header('Location: admin.php?page=catablog&message=3'); die;
			}
		}
	}
	
	
	public function admin_bulk_edit() {
		$action = $_REQUEST['bulk-action'];
		if (mb_strlen($action) > 0) {
			
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_bulkedit_nonce'], 'catablog_bulkedit' );
			if ($nonce_verified) {
			
				if (isset($_REQUEST['bulk_selection'])) {
					$selection = $_REQUEST['bulk_selection'];
					foreach ($selection as $item) {
						$item = CataBlogItem::getItem($item);
						if ($item) {
							$item->delete();
						}
						else {
							$this->wp_error('Error during bulk delete, could not locate item by id.');
						}
					}
					
					$this->reorder_all_items();
					$this->wp_message('Bulk delete performed successfully.');
					
				} else {
					$this->wp_message('Please make your selection by checking the boxes in the list below.');
					$this->admin_list();	
				}
				
			} else {
				$this->wp_error('Could not verify bulk edit action nonce, please refresh page and try again.');
			}
			
		}
		
		$this->admin_list();
	}
	
	public function admin_import() {
		$error = false;
		
		// do appropriate actions depending on format and successful upload
		$file_error_check = $this->check_file_upload_errors();
		if ($file_error_check !== true) {
			$error = ($file_error_check);
		}
		else {
			$upload = $_FILES['catablog_data'];
			$extension = end(explode(".", strtolower($upload['name'])));
			
			if ($extension == 'xml') {
				$data = $this->xml_to_array($upload['tmp_name']);
				if ($data === false) {
					$error = ('Uploaded XML File Could Not Be Parsed, Check That The File\'s Content Is Valid XML.');
				}
			}
			else if ($extension == 'csv') {
				$data = $this->csv_to_array($upload['tmp_name']);
				if (empty($data)) {
					$error = ('Uploaded CSV File Could Not Be Parsed, Check That The File\'s Format Is Valid.');
				}
			}
			else {
				$error = ('Uploaded file was not of proper format, please make sure the filename has an xml or csv extension.');
			}
			
			
		}
		
		// if there is an error display it and stop
		if ($error !== false) {
			$this->wp_error($error);
			include_once($this->directories['template'] . '/admin-import.php');
			return false;
		}
		
		// Private DataBase Insertion Method Called in Template:  load_array_to_database($data_array)
		include_once($this->directories['template'] . '/admin-import.php');
	}
	
	public function admin_export() {
		$date = date('Y-m-d');
		
		$format = 'xml';
		if (isset($_REQUEST['format'])) {
			if ($_REQUEST['format'] == 'csv') {
				$format = 'csv';
			}
		}
		
		header('Content-type: application/'.$format);
		header('Content-Disposition: attachment; filename="catablog-backup-'.$date.'.'.$format.'"');
		header("Pragma: no-cache");
		header("Expires: 0");

		$results = CataBlogItem::getItems();
				
		if ($format == 'csv') {
			ini_set('auto_detect_line_endings', true);
			
			$outstream   = fopen("php://output", 'w');
			$field_names = array('order','image','subimages','title','link','description','categories','price','product_code', 'quantity', 'size');
			$header      = NULL;
			
			foreach ($results as $result) {
				if (!$header) {
					fputcsv($outstream, $field_names, ',', '"');
					$header = true;
				}
				fputcsv($outstream, $result->getValuesArray(), ',', '"');
			}
			
			fclose($outstream);
		}
		
		if ($format == 'xml') {
			include_once($this->directories['template'] . '/admin-export.php');
		}
		
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
		$items       = CataBlogItem::getItems(false, false);
		$image_names = array();
		
		foreach ($items as $item) {
			$image_names[] = $item->getImage();
			foreach ($item->getSubImages() as $image) {
				$image_names[] = $image;
			}
		}
		
		include_once($this->directories['template'] . '/admin-regenerate.php');
	}
	
	public function admin_rescan_images() {
		$items = CataBlogItem::getItems(false, false);
		$image_names = array();
		foreach ($items as $item) {
			$image_names[] = $item->getImage();
			foreach ($item->getSubImages() as $subimage) {
				$image_names[] = $subimage;
			}
		}
		
		$new_rows = array();
		$new_rows['image'] = array();
		$originals = new CataBlogDirectory($this->directories['originals']);
		
		if ($originals->isDirectory()) {
			
			$new_order = wp_count_posts($this->custom_post_name)->publish;
			
			$default_term = $this->get_default_term();
			$default_category = (array($default_term->term_id=>$default_term->name));
			
			foreach ($originals->getFileArray() as $file) {
				if (!in_array($file, $image_names)) {
					
					$extension = end(explode(".", strtolower($file)));
					$media_accepted = array('jpg', 'jpeg', 'gif', 'png');
					
					if (in_array($extension, $media_accepted)) {
						$title = str_replace(array('-','_'), ' ', $file);
						$title = str_ireplace('.'.$extension, '', $title);
						
						$new_item = new CataBlogItem();
						$new_item->setOrder($new_order);
						$new_item->setTitle($title);

						$new_item->setImage($file, false);
						$new_item->setSubImages(array());

						$new_item->setCategories($default_category);
						$new_item->save();
												
						$new_rows['ids'][]    = $new_item->getId();
						$new_rows['titles'][] = $new_item->getTitle();				
						$new_rows['image'][]  = $new_item->getImage();
						
					}
				}
				
				$new_order += 1;
			}
		}
		
		include_once($this->directories['template'] . '/admin-rescan.php');
	}
	
	public function admin_install() {
		$this->install_options();
		$this->install_directories();
		$this->install_default_term();
		$this->wp_message('CataBlog options and directories have been successfully installed.');
		$this->admin_list();
	}
	
	public function admin_reset_all() {
		// remove all catablog posts
		$items = CataBlogItem::getItems();
		foreach ($items as $item) {
			$item->delete(false);
		}
		
		$this->remove_options();
		$this->remove_directories();
		$this->remove_terms();
		
		$this->install_options();
		$this->install_directories();
		$this->install_default_term();
		
		delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
		
		$this->wp_message('System has been cleared of all CataBlog data and the default options reset.');
		$this->admin_options();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMIN AJAX ACTIONS
	*****************************************************/
	// public function ajax_fetch_items() {
	// 	check_ajax_referer('catablog-fetch-rows', 'security');
	// 	die();
	// 	
	// 	// DOES NOTHING CURRENTLY 
	// 	$ajax_call = true;
	// 	
	// 	$category = (mb_strlen($_REQUEST['category']) > 0)? $_REQUEST['category'] : false;
	// 	$offset   = $_REQUEST['offset'];
	// 	$limit    = 100;
	// 	
	// 	$results  = CataBlogItem::getItems();
	// 	
	// 	if ($view == 'grid') {
	// 		include_once($this->directories['template'] . '/admin-grid.php');
	// 	}
	// 	else {
	// 		include_once($this->directories['template'] . '/admin-list.php');
	// 	}
	// 	
	// 	die();
	// }
	
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
		
		$name_exists = false;
		foreach ($this->get_terms() as $term) {
			if (strtolower($category_name) == strtolower($term->name)) {
				$name_exists = true;
				break;
			}
		}
		if ($name_exists) {
			echo "({'success':false, 'error':'There already is a category with that name.'})";
			die;
		}
		
		$category_slug = $this->string2slug($category_name);
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
	
	public function ajax_render_images() {
		check_ajax_referer('catablog-render-images', 'security');
		
		$name = $_REQUEST['image'];
		$type = $_REQUEST['type'];
		
		$item = new CataBlogItem();
		
		switch ($type) {
			case 'thumbnail':
				$complete = $item->MakeThumbnail($name);
				break;
			case 'fullsize';
				$complete = $item->MakeFullsize($name);
				break;
			default:
				$complete = "unsupported image size type";
				break;
		}
		
		if ($complete !== true) {
			echo "({'success':false, 'error':'$complete'})";
		}
		else {
			echo "({'success':true, 'message':'render $type complete'})";
		}
		
		
		die();
	}
	
	public function ajax_delete_subimage() {
		check_ajax_referer('catablog-delete-subimage', 'security');
		
		$id    = $_POST['id'];
		$image = $_POST['image'];
		
		$result     = CataBlogItem::getItem($id);
		$sub_images = $result->getSubImages();		
		
		foreach ($sub_images as $key => $value) {
			if ($image == $value) {
				unset($sub_images[$key]);
			}
		}
		
		$to_delete = array();	
		$to_delete["original"]  = $this->directories['originals'] . "/$image";
		$to_delete["thumbnail"] = $this->directories['thumbnails'] . "/$image";
		$to_delete["fullsize"]  = $this->directories['fullsize'] . "/$image";
					
		foreach ($to_delete as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
		
		$result->setSubImages($sub_images);
		$result->save();
			
		delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
		
		if (false) {
			echo "({'success':false, 'error':'error'})";
		}
		else {
			echo "({'success':true, 'message':'sub image deleted successfully'})";
		}
		
		die();
	}





	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - FRONTEND ACTIONS
	*****************************************************/
	public function frontend_init($load=false) {
		global $posts;
		$pattern = get_shortcode_regex();
		
		$this->load_support_files = $load;
				
		// is catablog shortcode in the posts or pages content
		if ($this->load_support_files == false) {
			foreach ($posts as $post) {
				preg_match('/'.$pattern.'/s', $post->post_content, $matches);
				if (is_array($matches) && $matches[2] == 'catablog') {
					$this->load_support_files = true; //shortcode is being used on page
					break;
				}
			}			
		}
		
		// only load support files if catablog shortcode was found on page
		if ($this->load_support_files) {
			if ($this->options['lightbox-enabled']) {
				wp_enqueue_script('catablog-lightbox', $this->urls['javascript'] . '/catablog.lightbox.js', array('jquery'), $this->version);
			}

			wp_enqueue_style('catablog-stylesheet', $this->urls['css'] . '/catablog.css', false, $this->version);

			$path = get_stylesheet_directory().'/catablog.css';
			if (file_exists($path)) {
				wp_enqueue_style('catablog-custom-stylesheet', get_bloginfo('stylesheet_directory') . '/catablog.css', false, $this->version);
			}
		}
		
	}
	
	public function frontend_footer() {
		if (!is_admin() && $this->options['lightbox-enabled']) {
			if ($this->load_support_files) {
				echo "<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('.catablog-image').catablogLightbox(); });</script>\n";
			}
		}
	}

	public function frontend_content($atts) {
		
		extract(shortcode_atts(array('category'=>false, 'tag'=>false), $atts));
			
		// if an old tag attribute is used put it in categories instead
		if ($category === false && $tag !== false) {
			$category = $tag;
		}
		
		$slug = false;
		if (mb_strlen($category) > 0) {
			$slug = NULL;
			foreach ($this->get_terms() as $term) {
				if (strtolower($category) == strtolower($term->name)) {
					$slug = $term->slug;
				}
			}			
		}
		
		// get items and start the output buffer
		$results = CataBlogItem::getItems($slug, false);
		ob_start();
		
		foreach ($results as $result) {
			echo $this->frontend_render_catalog_row($result);
		}
		
		// give the credit where it is due
		echo "<p class='catablog-credits'><!-- Catalog Content by CataBlog $this->version - http://catablog.illproductions.com/ --></p>";
		
		return ob_get_clean();
	}
	
	// public function frontend_catalog_item_page($content) {
	// 	global $post;
	// 	if ($post->post_type == $this->custom_post_name){
	// 		$result  = CataBlogItem::getItem($post->ID);
	// 		$content = $this->frontend_render_catalog_row($result, false);
	// 	}
	// 	
	// 	return $content;
	// }
	
	public function frontend_render_catalog_row($result, $show_title=true) {
		$thumbnail_size = $this->options['thumbnail-size'];
		
		$values = array();
		
		// system wide token values
		$values['image-size']        = $thumbnail_size;
		$values['paypal-email']      = $this->options['paypal-email'];
		$values['min-height']        = "style='min-height:$thumbnail_size"."px; height:auto !important; height:$thumbnail_size"."px;'";
		$values['hover-title-size']  = ($thumbnail_size - 10) . 'px';
		$values['margin-left']       = ($thumbnail_size + 10) . 'px';		
		$values['lightbox']          = ($this->options['lightbox-enabled'])? "catablog-lightbox" : "";
		
		// check if theme is empty, if so use default theme
		$string = $this->options['view-theme'];
		if (mb_strlen($string) == 0) {
			$string = file_get_contents($this->directories['template'] . '/views/default.htm');
		}
		
		// filter description if neccessary
		$description = $result->getDescription();
		if ($this->options['filter-description']) {
			$pattern     = '/\[(catablog)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s';
			$description = preg_replace($pattern, '', $description);
			$description = apply_filters('the_content', $description);
		}
		if ($this->options['nl2br-description']) {
			$description = nl2br($description);
		}
		
		$target = htmlspecialchars($this->options['link-target'], ENT_QUOTES, 'UTF-8');
		$target = (mb_strlen($target) > 0)? "target='$target'" : "";
		$rel    = htmlspecialchars($this->options['link-relationship'], ENT_QUOTES, 'UTF-8');
		$rel    = (mb_strlen($rel) > 0)? "rel='$rel'" : "";
		$link   = $result->getLink();
		
		// set the title values into an array
		if (mb_strlen($result->getLink()) > 0) {
			$values['title'] = "<a href='$link' $target $rel>".$result->getTitle()."</a>";
		}
		else {
			$values['title'] = $result->getTitle();
		}
		
		// set the catalog item values of the item into an array
		$values['title-text']      = $result->getTitle();
		$values['image']           = $this->urls['thumbnails'] . "/". $result->getImage();

		$values['image-fullsize']  = $this->urls['fullsize'] . "/". $result->getImage();
		if (is_file($values['image-fullsize']) == false) {
			$values['image-fullsize']  = $this->urls['originals'] . "/". $result->getImage();
		}
		
		$values['link']            = (mb_strlen($link) > 0)? $link : "#empty-link";
		$values['link-target']     = $target;
		$values['link-rel']        = $rel;
		$values['description']     = $description;
		$values['price']           = number_format(((float)($result->getPrice())), 2, '.', '');
		$values['product-code']    = $result->getProductCode();		
		$values['quantity']        = $result->getQuantity();
		$values['size']            = $result->getSize();
		
		$values['main-image']      = '<img src="'.$values['image'].'" class="catablog-image" width="'.$values['image-size'].'" alt="" />';
		$values['sub-images']      = "";
		foreach ($result->getSubImages() as $image) {
			$values['sub-images'] .= '<img src="'.$this->urls['thumbnails'].'/'.$image.'" class="catablog-subimage catablog-image" />';
		}
		
		
		
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
		return $string;
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
		$this->initialize_plugin();
		$this->install_directories();
		$this->install_options();
		if ($this->get_default_term() === null) {
			$this->install_default_term();
		}
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
		$default_options['link-relationship']  = "";
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
	
	private function install_default_term() {
		// if ($this->get_default_term() == NULL) {
			
			$category_slug = $this->string2slug($this->default_term_name);
			$attr          = array('slug'=>$category_slug);
			$insert_return = wp_insert_term($this->default_term_name, $this->custom_tax_name, $attr);
			
			if ($insert_return instanceof WP_Error) {
				foreach ($insert_return->get_error_messages() as $error) {
					$this->wp_error("There was an error creating the default term: $error");
				}
			}
			
			return $insert_return;
		// }
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - REMOVAL METHODS
	*****************************************************/
	
	public function deactivate() {
		
	}
	
	private function remove() {
		$this->remove_options();
		$this->remove_directories();
	}
	
	private function remove_options() {
		delete_option($this->options_name);
		$this->options = array();
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
		
		delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
	}
	
	private function remove_terms() {
		$terms = $this->get_terms(true);
		foreach ($terms as $term) {
			wp_delete_term($term->term_id, $this->custom_tax_name);
		}
	}
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - IMPORT METHODS
	*****************************************************/
	public function xml_to_array($filename='') {
		if(!file_exists($filename) || !is_readable($filename)) {
			return FALSE;
		}
		
		libxml_use_internal_errors(true);
		$xml = simplexml_load_file($filename);
		if ($xml === false) {
			return FALSE;
		}
		
		$data = array();
		foreach ($xml->item as $item) {
			$row = array();
			
			$row['id']           = null;
			$row['order']        = (integer) ((isset($item->order))? $item->order : $item->ordinal);
			$row['image']        = (string) $item->image;
			$row['title']        = (string) $item->title;
			$row['link']         = (string) $item->link;
			$row['description']  = (string) $item->description;
			$row['price']        = (float) $item->price;
			$row['product_code'] = (string) $item->product_code;
			$row['quantity']     = (string) $item->quantity;
			$row['size']         = (string) $item->size;
			
			$subimages = array();
			foreach ($item->subimages as $images) {
				foreach ($images as $image) {
					$subimages[] = (string) $image;
				}
			}
			
			$row['subimages']    = $subimages;
			
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
			$row['categories'] = implode('|', $terms);
			
			$data[] = $row;
		}
		
		return $data;
	}
	
	public function csv_to_array($filename='', $delimiter=',') {
		if(!file_exists($filename) || !is_readable($filename)) {
			return FALSE;
		}
		
		ini_set('auto_detect_line_endings', true);
		
		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
				if(!$header) {
					$header = $row;
					if (count($header) != 11) {
						return $data;
					}
				}
				else if (count($row) == 11) {
					$data[] = array_combine($header, $row);
				}
			}
			fclose($handle);
		}

		return $data;
	}
	
	private function load_array_to_database($data) {
		
		// extract a list of every category in the import
		$import_terms = array($this->default_term_name);
		foreach ($data as $key => $row) {
			$row_terms = array();
			if (mb_strlen($row['categories']) > 0) {
				$row_terms = explode('|', $row['categories']);
			}
			
			foreach ($row_terms as $row_term) {
				$import_terms[] = $row_term;
			}
		}
		$import_terms = array_intersect_key($import_terms,array_unique(array_map(strtolower,$import_terms)));
		
		// extract a list of every category that needs is not already created
		$make_terms = $import_terms;
		if (isset($_REQUEST['catablog_clear_db']) === false) {
			$existant_terms = $this->get_terms(true);
			foreach ($existant_terms as $existant_term) {
				foreach ($make_terms as $key => $make_term) {				
					if ($make_term == $existant_term->name) {
						unset($make_terms[$key]);
						unset($import_terms[$key]);
						
						$import_terms[$existant_term->term_id] = $existant_term->name;
					}
				}
			}
		}
		
		// create the neccessary new categories from the previous export
		$new_term_made = false;
		foreach ($make_terms as $key => $make_term) {
			$category_slug = $this->string2slug($make_term);
			$attr          = array('slug'=>$category_slug);
			$insert_return = wp_insert_term($make_term, $this->custom_tax_name, $attr);
			
			if ($insert_return instanceof WP_Error) {
				foreach ($insert_return->get_error_messages() as $error) {
					echo "<li class='error'>Create Term Error - <strong>".$make_term."</strong>: $error</li>";
				}
			}
			else {
				if (isset($insert_return['term_id'])) {
					$new_term_id = $insert_return['term_id'];
					$new_term_name = $make_term;
					
					if ($new_term_id > 0) {
						unset($import_terms[$key]);
						unset($make_terms[$key]);
						
						$import_terms[$new_term_id] = $new_term_name;
						$new_term_made = true;
						echo '<li class="updated">Success: <em>' . $make_term . '</em> inserted into catalog categories.</li>';
					}
				}							
			}
		}
		
		// render complete making new categories message
		if ($new_term_made) {
			echo '<li class="updated"><strong>All Categories Created</strong></li>';
			echo '<li>&nbsp;</li>';
		}
		else {
			echo '<li class="updated"><strong>No New Categories Created</strong></li>';
			echo '<li>&nbsp;</li>';
		}
		
		// import each new catalog item
		$new_order = wp_count_posts($this->custom_post_name)->publish;
		foreach ($data as $row) {
			$error = false;
			
			$success_message = '<li class="updated">Success: <em>' . $row['title'] . '</em> inserted into the database.</li>';
			$error_message   = '<li class="error"><strong>Error:</strong> <em>' . $row['title'] . '</em> was not inserted into the database.</li>';
			
			if (mb_strlen($row['title']) < 1) {
				$error = '<li class="error"><strong>Error: Could Not Find Title</strong></li>';
			}
			if (mb_strlen($row['image']) < 1) {
				$error = '<li class="error"><strong>Error:</strong> Did Not Make Catalog Item <strong>'.$row['title'].':</strong> could not find image filename</li>';
			}
			
			if ($error) {
				echo $error;
			}
			else {
				$categories = $row['categories'];
				if (is_array($categories) === false) {
					$categories = explode('|', $categories);
					$row['categories'] = array();
				}
				foreach ($categories as $cat) {
					foreach ($import_terms as $term_id => $term_name) {
						if (strtolower($term_name) == strtolower($cat)) {
							$row['categories'][] = $term_id;
						}
					}					
				}
				
				$item = new CataBlogItem($row);
				
				$item->setOrder($new_order);
				
				$subimages = $row['subimages'];
				if (is_array($subimages) === false) {
					if (mb_strlen($subimages) > 0) {
						$subimages = explode('|', $subimages);
					}
					else {
						$subimages = array();
					}
				}
				$row['subimages'] = $subimages;
				
				foreach ($row['subimages'] as $subimage) {
					$item->setSubImage($subimage);
				}
				
				$results = $item->save();
				if ($results === true) {
					echo $success_message;
				}
				else {
					echo $results;
				}
			}
			
			$new_order += 1;
			
		}
		
		echo '<li class="updated"><strong>All Catalog Items Created</strong></li>';
		
	}
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - ADMINISTRATION METHODS
	*****************************************************/
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
	
	private function get_terms($reload=false) {
		if ($this->terms == NULL || $reload) {
			$database_fetch = get_terms($this->custom_tax_name, 'hide_empty=0');
			if ($database_fetch == NULL) {
				$this->terms = array();
			}
			else {
				$this->terms = $database_fetch;
			}
		}
		
		return $this->terms;
	}
	
	private function get_default_term() {
		if ($this->default_term == NULL) {
			$terms = $this->get_terms();
			foreach ($terms as $term) {
				if ($term->name == $this->default_term_name) {
					$this->default_term = $term;
				}
			}
		}
		return $this->default_term;
	}
	
	private function reorder_all_items($db_optimize=true) {
		if ($db_optimize) {
			global $wpdb;

			$query1 = "SET @catablogCounter = -1;";
			$wpdb->query($query1);

			$query2 = "UPDATE $wpdb->posts SET menu_order = (@catablogCounter := @catablogCounter+1) WHERE post_type='catablog-items' ORDER BY menu_order ASC";
			$wpdb->query($query2);
		}
		else {
			$items = CataBlogItem::getItems();
			$length = count($items);
			for ($i=0; $i < $length; $i++) {
				$item = $items[$i];
				if ($item->getOrder() != $i) {
					$item->setOrder($i);
					$item->save();				
				}
			}
		}
		
		return true;
	}
	
	private function string2slug($string) {
		$slug = "catablog-term-" . strtolower($string);
		return  wp_unique_term_slug($slug, null);
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