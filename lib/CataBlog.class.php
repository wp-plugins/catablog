<?php


/**********************************************
**  CataBlog Class
**********************************************/
class CataBlog {
	
	// plugin component version numbers
	private $version     = "1.2.5";
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
		$this->directories['languages']  = WP_CONTENT_DIR . "/plugins/localization";
		
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
	
	public function getCustomPostName() {
		return $this->custom_post_name;
	}
	
	public function getCustomTaxName() {
		return $this->custom_tax_name;
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
		// load in i18n file
		load_plugin_textdomain('catablog', false, '/catablog/localization');
		$this->default_term_name = __("Uncategorized", "catablog");
		
		$params['label']              = __("CataBlog Item", 'catablog');
		$params['public']              = false;
		$params['rewrite']             = false;
		$params['supports']            = array('title', 'editor');
		$params['description']         = __("A Catalog Item, generated by CataBlog.", 'catablog');
		$params['hierarchical']        = false;
		$params['taxonomies']          = array($this->custom_tax_name);
		$params['menu_position']       = 45;
		$params['menu_icon']           = $this->urls['plugin']."/images/catablog-icon-16.png";
		register_post_type($this->custom_post_name, $params);
		
		
		
		$params = array();
		$params['hierarchical']          = false;
		$params['label']                = __("CataBlog Category", 'catablog');
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
				$message = sprintf(__("CataBlog must be setup for this site before you may use it! %s Setup CataBlog Now %s"), "<a href='admin.php?page=catablog-install'>", "</a>");
				$this->wp_error($message);
			}
		}
		
		
		// set cookie to remember the admin view settings
		if(isset($_GET['page']) && $_GET['page'] == 'catablog') {
			$options = array('sort', 'order', 'view');
			foreach ($options as $option) {
				if(isset($_GET[$option])) {
					setCookie("catablog-view-cookie[$option]", $_REQUEST[$option], (time()+36000000));
				}
				
			}
			setCookie("catablog-view-cookie", false, (time() - 36000));
		}
		
		
		// load javascript libraries for admin panels
		wp_enqueue_script('jquery');		
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('farbtastic');
		wp_enqueue_style('farbtastic');
		wp_enqueue_script('catablog-admin', $this->urls['javascript'] . '/catablog-admin.js', array('jquery'), $this->version);
		
		
		// load css stylesheets for admin panels
		// wp_enqueue_style('jquery-ui-lightness', $this->urls['css'] . '/ui-lightness/jquery-ui-1.8.1.custom.css', false, '1.8.1');
		wp_enqueue_style('catablog-admin-css', $this->urls['css'] . '/catablog-admin.css', false, $this->version);
	}
	
	
	public function admin_menu() {
		// register main plugin menu
		add_menu_page("Edit CataBlog", "CataBlog", $this->user_level, 'catablog', array($this, 'admin_list'), $this->urls['plugin']."/images/catablog-icon-16.png");
		
		// register main plugin pages
		add_submenu_page('catablog', __("Manage CataBlog", 'catablog'), __('CataBlog', 'catablog'), $this->user_level, 'catablog', array(&$this, 'admin_list'));
		add_submenu_page('catablog', __("Add New CataBlog Entry", 'catablog'), __('Add New', 'catablog'), $this->user_level, 'catablog-new', array(&$this, 'admin_new'));
		add_submenu_page('catablog', __("CataBlog Options", 'catablog'), __('Options', 'catablog'), $this->user_level, 'catablog-options', array(&$this, 'admin_options'));
		add_submenu_page('catablog', __("About CataBlog", 'catablog'), __('About', 'catablog'), $this->user_level, 'catablog-about', array(&$this, 'admin_about'));
		
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
		
		$sort   = 'date';
		$order  = 'desc';
		$category_filter = false;
		
		$selected_term = false;//$this->get_default_term();
		if (isset($_GET['category']) && is_numeric($_GET['category'])) {
			if ($_GET['category'] > 0) {
				$selected_term = get_term_by('id', $_GET['category'], $this->custom_tax_name);
			}
		}
		
		if (isset($_GET['sort'])) {
			$sort = $_GET['sort'];
		}
		
		if (isset($_GET['order'])) {
			$order = $_GET['order'];
		}
		
		
		if ($selected_term) {
			$category_filter = $selected_term->slug;
		}
		
		$sort = 'date';
		$order = 'asc';
		$view = 'list';
		if (isset($_COOKIE['catablog-view-cookie'])) {
			$cookie = $_COOKIE['catablog-view-cookie'];
			$sort  = $cookie['sort'];
			$order = $cookie['order'];
			$view  = $cookie['view'];
			
			if (is_array($cookie)) {
				foreach ($cookie as $key => $value) {
					if (isset($_GET[$key])) {
						${$key} = $_GET[$key];
					}
				}
			}
		}
		
		
		$results = CataBlogItem::getItems($category_filter, 'IN', $sort, $order);
		
		
		if (isset($_GET['message'])) {
			switch ($_GET['message']) {
				case 1:
					$this->wp_message(__("Changes Saved Successfully", 'catablog'));
					break;
				case 2:
					$this->wp_message(__("Catalog Item Deleted Successfully.", 'catablog'));
					break;
				case 3:
					$this->wp_error(__("Could Not Delete Item Because ID was non existent.", 'catablog'));
			}
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
			
			if (isset($_GET['message'])) {
				switch ($_GET['message']) {
					case 1:
						$this->wp_message(__("Changes Saved Successfully", 'catablog'));
						break;
				}
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
		$recalculate_thumbnails = false;
		$recalculate_fullsize   = false;
		
		if (isset($_REQUEST['save'])) {
			$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_options_nonce'], 'catablog_options' );
			if ($nonce_verified) {
				
				// strip slashes from post values
				$post_vars = array_map('stripslashes_deep', $_POST);
				$post_vars = array_map('trim', $post_vars);
				
				// validate post data
				$post_vars['link_relationship'] = preg_replace('/[^a-z0-9_-]/', '', $post_vars['link_relationship']);
				
				
				// set default values for post message and image recalculation
				$save_message           = __("CataBlog Options Saved", 'catablog');
				
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
				if (isset($post_vars['lightbox_render'])) {
					$fullsize_dir = new CataBlogDirectory($this->directories['fullsize']);
					if ($fullsize_different || $fullsize_dir->getCount() < 1) {
						$recalculate_fullsize = true;
					}
				}
				
				// save new plugins options to database
				$this->options['thumbnail-size']       = $post_vars['thumbnail_size'];
				$this->options['image-size']           = $post_vars['lightbox_image_size'];
				$this->options['lightbox-enabled']     = isset($post_vars['lightbox_enabled']);
				$this->options['lightbox-render']      = isset($post_vars['lightbox_render']);
				$this->options['lightbox-selector']    = $post_vars['lightbox_selector'];
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
					$save_message .= " - ";
					$save_message .= __("Please Let The Rendering Below Complete Before Navigating Away From This Page", 'catablog');
					
					delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
					
					$items       = CataBlogItem::getItems();
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
				$this->wp_error(__('Form Validation Error. Please reload the page and try again.', 'catablog'));
			}
		}
		
		$thumbnail_size               = $this->options['thumbnail-size'];
		$lightbox_size                = $this->options['image-size'];
		$lightbox_enabled             = $this->options['lightbox-enabled'];
		$lightbox_render              = $this->options['lightbox-render'];
		$lightbox_selector            = $this->options['lightbox-selector'];
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
		
		$thumbnail_size = __('not present', 'catablog');
		$fullsize_size  = __('not present', 'catablog');
		$original_size  = __('not present', 'catablog');
		
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
					$new_item_order = wp_count_posts($this->custom_post_name)->publish + 1;
					
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
				$error = __("The file you selected was to large or you didn't select anything at all, please try again.", 'catablog');
			}
		}
		else {
			$error = __("WordPress Nonce Error, please reload the form and try again.", 'catablog');
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
				
				if (!isset($post_vars['sub_images'])) {
					$post_vars['sub_images'] = array();
				}
				
				$result    = new CataBlogItem($post_vars);
				$validate  = $result->validate();
				if ($validate === true) {
					$write = $result->save();
					if ($write === true) {
						header('Location: admin.php?page=catablog&message=1'); die;
						// header('Location: admin.php?page=catablog&id=' . $result->getId() . '&message=1'); die;
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
				$error = __("WordPress Nonce Error, please reload the form and try again.", 'catablog');
			}
		}
		else {
			$error = __("full form was not submitted, please try again.", 'catablog');
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
					$error = __("You didn't select anything to upload, please try again.", 'catablog');
				}
			}
			else {
				$error = __("WordPress Nonce Error, please reload the form and try again.", 'catablog');
			}
		}
		else {
			$error = __("No item ID posted, press back arrow and try again.", 'catablog');
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
					$error = __("You didn't select anything to upload, please try again.", 'catablog');
				}
			}
			else {
				$error = __("WordPress Nonce Error, please reload the form and try again.", 'catablog');
			}
		}
		else {
			$error = __("No item ID posted, press back arrow and try again.", 'catablog');
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
							$this->wp_error(__('Error during bulk delete, could not locate item by id.', 'catablog'));
						}
					}
					
					$this->reorder_all_items();
					$this->wp_message(__('Bulk delete performed successfully.', 'catablog'));
					
				} else {
					$this->wp_message(__('Please make your selection by checking the boxes in the list below.', 'catablog'));
					$this->admin_list();	
				}
				
			} else {
				$this->wp_error(__('Could not verify bulk edit action nonce, please refresh page and try again.', 'catablog'));
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
		elseif (isset($_FILES['catablog_data']) === false) {
			$error = __('No file was selected for upload, please try again.', 'catablog');
		}
		else {
			$upload = $_FILES['catablog_data'];
			$extension = end(explode(".", strtolower($upload['name'])));
			
			if ($extension == 'xml') {
				$data = $this->xml_to_array($upload['tmp_name']);
				if ($data === false) {
					$error = __('Uploaded XML File Could Not Be Parsed, Check That The File\'s Content Is Valid XML.', 'catablog');
				}
			}
			else if ($extension == 'csv') {
				$data = $this->csv_to_array($upload['tmp_name']);
				if (empty($data)) {
					$error = __('Uploaded CSV File Could Not Be Parsed, Check That The File\'s Format Is Valid.', 'catablog');
				}
			}
			else {
				$error = __('Uploaded file was not of proper format, please make sure the filename has an xml or csv extension.', 'catablog');
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
			$field_names = array('id','image','subimages','title','description','date','order','link','price','product_code','categories');
			$header      = NULL;
			
			foreach ($results as $result) {
				if (!$header) {
					fputcsv($outstream, $field_names, ',', '"');
					$header = true;
				}
				fputcsv($outstream, $result->getCSVArray(), ',', '"');
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
			$this->wp_message(__("The CataBlog upload directories have been unlocked.", 'catablog'));
		}
		else {
			$this->wp_error(__("Could not lock/unlock the directory. Are you using a unix based server?", 'catablog'));
		}
		
		$this->admin_options();
	}

	public function admin_lock_folders() {
		if ($this->lock_directories()) {
			$this->wp_message(__("The CataBlog upload directories have been locked.", 'catablog'));
		}
		else {
			$this->wp_error(__("Could not lock/unlock the directory. Are you using a unix based server?", 'catablog'));
		}
		
		$this->admin_options();
	}
	
	public function admin_regenerate_images() {
		$items       = CataBlogItem::getItems();
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
		$items = CataBlogItem::getItems();
		$image_names = array();
		foreach ($items as $item) {
			$image_names[] = $item->getImage();
			foreach ($item->getSubImages() as $subimage) {
				$image_names[] = $subimage;
			}
		}
		
		$new_rows = array();
		$new_rows['images'] = array();
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
						$new_rows['images'][]  = $new_item->getImage();
						
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
		$this->wp_message(__('CataBlog options and directories have been successfully installed.', 'catablog'));
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
		
		$this->wp_message(__('System has been cleared of all CataBlog data and the default options reset.', 'catablog'));
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
		
		$char_check = preg_match('/[\,\<\>\&\'\"]/', $category_name);
		if ($char_check > 0) {
			echo "({'success':false, 'error':'".__('Commas and reserved HTML characters are not allowed in category names.', 'catablog')."'})";
			die;
		};
		
		$string_length = mb_strlen($category_name);
		if ($string_length < 1) {
			echo "({'success':false, 'error':'".__('Please be a little more specific with your category name.', 'catablog')."'})";
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
			echo "({'success':false, 'error':'".__('There already is a category with that name.', 'catablog')."'})";
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
			echo "({'success':true, 'message':'".__('Term Removed Successfully.', 'catablog')."'})";
		}
		else {
			echo "({'success':false, 'error':'".__('Term did not exist, please refresh page and try again.', 'catablog')."'})";
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
		
		$this->options['lightbox-render'] = false;
		$this->update_options();
		
		die();
	}
	
	public function ajax_render_images() {
		check_ajax_referer('catablog-render-images', 'security');
		
		$name  = $_REQUEST['image'];
		$type  = $_REQUEST['type'];
		$count = $_REQUEST['count'];
		$total = $_REQUEST['total'];
		
		$item = new CataBlogItem();
		
		switch ($type) {
			case 'thumbnail':
				$success = $item->MakeThumbnail($name);
				break;
			case 'fullsize';
				$success = $item->MakeFullsize($name);
				break;
			default:
				$success = __("unsupported image size type", 'catablog');
				break;
		}
		
		if ($success !== true) {
			$message = $success;
			echo "({'success':false, 'message':'$message'})";
		}
		else {
			$message = sprintf(__('Rendering... %s of %s', 'catablog'), $total - $count, $total);
			if ($count == 0) {
				$message = __('Image rendering is now complete.', 'catablog');
			}
			echo "({'success':true, 'message':'$message'})";
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
			echo "({'success':false, 'error':'".__('error', 'catablog')."'})";
		}
		else {
			echo "({'success':true, 'message':'".__('sub image deleted successfully', 'catablog')."'})";
		}
		
		die();
	}





	
	
	
	
	
	
	
	
	
	
	/*****************************************************
	**       - FRONTEND ACTIONS
	*****************************************************/
	public function frontend_init($load=false) {
		// get global posts and the shortcode regex pattern
		global $posts;
		$pattern = get_shortcode_regex();
		
		// set load supported files variable
		$this->load_support_files = true;
		
		
		
		/*
		// !!! DISABLE CACHE HERE 
		if (false) { 
			
			// find all catablog shortcodes in posts and put them in $shortcode_matches
			$shortcode_matches  = array();
			if ($this->load_support_files == false) {
				foreach ($posts as $post) {
					preg_match('/'.$pattern.'/', $post->post_content, $matches);

					if (is_array($matches) && isset($matches[2]) && is_array($matches[2])) {
						foreach ($matches[2] as $key => $match) {
							if ($match == 'catablog') {
								if (isset($matches[3]) && is_array($matches[3])) {
									$shortcode_matches[] = $matches[3][$key];
								}

							}
						}
					}
				}			
			}
			
			
			// put all the category slugs into one array
			$slugs = array();
			foreach ($shortcode_matches as $shortcode) {
				$atts = shortcode_parse_atts($shortcode);
				$atts = shortcode_atts(array('category'=>false), $atts);
				$category = $atts['category'];
			
				if ($category === false) {
					$slugs = false;
					break;
				}
			
				foreach ($this->get_terms() as $term) {
					if (strtolower($category) == strtolower($term->name)) {
						$slugs[] = $term->slug;
					}
				}
			}
		
			// if $slugs is an array remove duplicate entries
			if (is_array($slugs)) {
				$slugs = array_unique($slugs);
			}
		
			// get all necessary catalog items for the page and save in cache
			$this->results_cache = CataBlogItem::getItems($slugs, $operator, $sort, $order);
			
		}
		// END DISABLED CACHE
		*/
		
		
		
		// set load_support_file to true if a catablog shortcode was found
		if (count($shortcode_matches) > 0) {
			$this->load_support_files = true;
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
			
			$size = $this->options['thumbnail-size'];
			$size1 = $size + 10;
			$size2 = $size - 10;
			
			$inline_styles = array();
			
			$inline_styles[] = ".catablog-row {min-height:{$size}px; height:auto !important; height:{$size}px;}";
			$inline_styles[] = ".catablog-row .catablog-image{width:{$size}px;}";
			$inline_styles[] = ".catablog-row .catablog-title {margin-left:{$size1}px;}";
			$inline_styles[] = ".catablog-row .catablog-description {margin-left:{$size1}px;}";
			$inline_styles[] = ".catablog-row .catablog-images-column {width:{$size}px;} ";
			
			$inline_styles[] = ".catablog-gallery.catablog-row .catablog-image {width:{$size}px; height:{$size}px;}";
			$inline_styles[] = ".catablog-gallery.catablog-row .catablog-link {width:{$size}px; height:{$size}px;}";
			$inline_styles[] = ".catablog-gallery.catablog-row .catablog-title {width:{$size2}px;}";
			
			echo "\n<!-- ".__('CataBlog LightBox Inline Stylesheet')." -->\n";
			echo "<style>".implode("", $inline_styles)."</style>\n";
			echo "<!-- ".__('End CataBlog LightBox Inline Stylesheet')." -->\n\n";
			
		}
		
	}
	
	public function frontend_footer() {
		if (!is_admin() && $this->options['lightbox-enabled']) {
			if (isset($this->load_support_files) && $this->load_support_files) {
				$selector = '.catablog-image';
				if (isset($this->options['lightbox-selector'])) {
					$selector = $this->options['lightbox-selector'];
				}
				
				$javascript = array();
				
				$javascript[] = "var js_i18n=new Object;";
				$javascript[] = 'js_i18n.prev_tip="'.__("You may also press &quot;P&quot; or the left arrow on your keyboard", 'catablog').'";';
				$javascript[] = 'js_i18n.next_tip="'.__("You may also press &quot;N&quot; or the right arrow on your keyboard", 'catablog').'";';
				$javascript[] = "js_i18n.close_tip='".__('Close LightBox Now', 'catablog')."';";
				$javascript[] = "js_i18n.prev_label='".__('PREV', 'catablog')."';";
				$javascript[] = "js_i18n.next_label='".__('NEXT', 'catablog')."';";
				$javascript[] = "js_i18n.close_label='".__('CLOSE', 'catablog')."';";
				
				$javascript[] = "jQuery(document).ready(function(){ jQuery('$selector').catablogLightbox(); });";
				
				echo "<!-- ".__('CataBlog LightBox JavaScript')." -->\n";
				echo "<script type='text/javascript'>".implode(" ", $javascript)."</script>\n";
				echo "<!-- ".__('End CataBlog LightBox JavaScript')." -->\n\n";
			}
		}
	}

	public function frontend_content($atts) {
		$shortcode_params = array('category'=>false, 'template'=>false, 'sort'=>'menu_order', 'order'=>'asc', 'operator'=>'IN');
		
		extract(shortcode_atts($shortcode_params, $atts));
		
		// extract all category names
		$categories = explode(',', $category);
		array_walk($categories, create_function('&$val', '$val = trim($val);'));
		
		// if sort equals order, change it to menu_order
		$sort = ($sort == 'order')? 'menu_order' : $sort;
		
		// modify the operator if it is a possibly wrong format to work with WP.
		$operator = str_replace("-", " ", strtoupper($operator));
		
		// get items from cache and start the output buffer
		if (isset($this->results_cache)) {
			$results = $this->results_cache;
		}
		else {
			$slugs = array();
			foreach ($categories as $category) {
				foreach ($this->get_terms() as $term) {
					if (strtolower($category) == strtolower($term->name)) {
						$slugs[] = $term->slug;
					}
				}
			}
			
			$results = CataBlogItem::getItems($slugs, $operator, $sort, $order);
		}
		
		ob_start();
		
		foreach ($results as $result) {
			
			// render all items if the category is not set
			// if ($category === false) {
				echo $this->frontend_render_catalog_row($result, $template);
			// }
			
			// render only the items in the set category
			// else if ($result->inCategory($category)) {
				// echo $this->frontend_render_catalog_row($result, $template);
			// }
		}
		
		// give the credit where it is due
		echo "<span class='catablog-credits'><!-- ".sprintf(__('Catalog Content by CataBlog %s - http://catablog.illproductions.com/'), $this->version)." --></span>";
		
		return ob_get_clean();
	}
	
	public function frontend_render_catalog_row($result, $template_override=false) {
		
		// $timer_start = microtime();
		
		$thumbnail_size = $this->options['thumbnail-size'];
		
		$values = array();
		
		// system wide token values
		$values['image-size']   = $thumbnail_size;
		$values['paypal-email'] = $this->options['paypal-email'];
		$values['link-target']  = $target;
		$values['link-rel']     = $rel;

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
		
		
		// catalog item image paths
		$values['image-original']  = $this->urls['originals']  . "/" . $result->getImage();
		$values['image-thumbnail'] = $this->urls['thumbnails'] . "/" . $result->getImage();
		$values['image-lightbox']  = $this->urls['fullsize']   . "/" . $result->getImage();
		$values['image-path']      = $this->urls['fullsize'];
		if ($this->options['lightbox-render'] == false) {
			$values['image-path']      = $this->urls['originals'];
			$values['image-lightbox']  = $values['image-original'];
		}
		
		// catalog item title and content
		$values['title']           = $result->getTitle();
		$values['title-link']      = "<a href='".$values['link']."'>".$values['title']."</a>";
		$values['description']     = $description;
		
		// catalog item attributes
		$values['date']            = strtotime($result->getDate());
		$values['order']           = $result->getOrder();
		
		// catalog item field values
		$values['link']            = (mb_strlen($link) > 0)? $link : $values['image-lightbox'];
		$values['price']           = number_format(((float)($result->getPrice())), 2, '.', '');
		$values['product-code']    = $result->getProductCode();
		
		// catalog item images
		$values['main-image']      = '<img src="'.$values['image-thumbnail'].'" alt="" />';
		$values['main-image']      = "<a href='".$values['link']."' class='catablog-image' $target $rel>".$values['main-image']."</a>";
		$values['sub-images']      = "";
		foreach ($result->getSubImages() as $image) {
			$sub_image             = '<img src="'.$this->urls['thumbnails'].'/'.$image.'" />';
			$sub_image             = "<a href='".$this->urls['originals']."/$image' class='catablog-subimage catablog-image'>".$sub_image."</a>";
			$values['sub-images'] .= $sub_image;
		}
		
		
		
		// deprecatted values
		$values['title-text']     = $values['title'];
		$values['image']          = $values['image-thumbnail'];
		$values['image-fullsize'] = $values['image-lightbox'];
		
		
		
		
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
		
		
		
		// check if theme is empty, if so use default theme
		$template = "";
		if ($template_override !== false) {
			$template_file = $this->directories['template'] . '/views/' . $template_override . '.htm';
			
			if (is_file($template_file)) {
				$template = file_get_contents($template_file);
			}
			else {
				$template  = "<p>";
				$template .= sprintf(__("CataBlog ShortCode Parameter Error: The template attribute of this ShortCode points to a file that does not exist. Please make sure their is a file with the name '%s.htm' in the views directory."), $template_override);
				$template .= " [<a href='http://catablog.illproductions.com/documentation/making-custom-templates/' target='_blank'>".__("Learn More")."</a>]";
				$template .= "</p>";
			}
			
			if (mb_strlen($template) == 0) {
				$template = file_get_contents($this->directories['template'] . '/views/default.htm');
			}
		}
		else {
			$template = $this->options['view-theme'];
			if (mb_strlen($template) == 0) {
				$template = file_get_contents($this->directories['template'] . '/views/default.htm');
			}
		}
		
		// echo $template;
		
		// loop through each items array of values and replace tokens
		foreach($values as $key => $value) {
			$search  = "%" . strtoupper($key) . "%";
			$template  = str_replace($search, $value, $template);
		}
		
		$processed_row = $template;
		
		// write the string to the current output buffer
		return $processed_row;
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
		$this->check_system_reqs();
		$this->initialize_plugin();
		$this->install_directories();
		$this->install_options();
		if ($this->get_default_term() === null) {
			$this->install_default_term();
		}
	}
	
	private function check_system_reqs() {
		
		/** CHECK PHP **/
		// check if PHP is version 5
		if (version_compare(phpversion(), '5.0.0', '<')) {
		  die(__("<strong>CataBlog</strong> requires <strong>PHP 5</strong> or better running on your web server. You're version of PHP is to old, please contact your hosting company or IT department for an upgrade. Thanks.", 'catablog'));
		}
		// check if GD Library is loaded in PHP
		if (!extension_loaded('gd') || !function_exists('gd_info')) {
		    die(__("<strong>CataBlog</strong> requires that the <strong>GD Library</strong> be installed on your web server's version of PHP. Please contact your hosting company or IT department for more information. Thanks.", 'catablog'));
		}
		// check if mbstring Library is loaded in PHP
		if (!extension_loaded('mbstring') || !function_exists('mb_strlen')) {
		    die(__("<strong>CataBlog</strong> requires that the <strong>MultiByte String Library</strong> be installed on your web server's version of PHP. Please contact your hosting company or IT department for more information. Thanks.", 'catablog'));
		}



		/** CHECK WORDPRESS **/
		// check WordPress version
		if (version_compare(get_bloginfo('version'), '3.1', '<')) {
			die(__("<strong>CataBlog</strong> requires <strong>WordPress 3.1</strong> or above. Please upgrade WordPress or contact your system administrator about upgrading.", 'catablog'));
		}
		// check if uploads directory is set and writable
		$upload_directory = wp_upload_dir();
		if ($upload_directory['error']) {
			die(__("<strong>CataBlog</strong> could not detect your upload directory or it is not writable by PHP. Please contact your hosting company or IT department for more information. Thanks.", 'catablog'));
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
		$default_options['lightbox-render']    = false;
		$default_options['lightbox-selector']  = ".catablog-image";
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
					$this->wp_error(__("There was an error creating the default term: ", 'catablog').$error);
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
			
			$row['id']           = (integer) $item->image;
			$row['order']        = (integer) ((isset($item->order))? $item->order : $item->ordinal);
			$row['date']         = (string) $item->date;
			$row['image']        = (string) $item->image;
			$row['title']        = (string) $item->title;
			$row['link']         = (string) $item->link;
			$row['description']  = (string) $item->description;
			$row['price']        = (float) $item->price;
			$row['product_code'] = (string) $item->product_code;
			
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
			
			$row['date'] = date('Y-m-d G:i:s', strtotime($row['date']));
			
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
			while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
				if(!$header) {
					$header = $row;
				}
				else {//if (count($row) == 9) {
					$data[] = array_combine($header, $row);
				}
			}
			fclose($handle);
		}
		
		foreach ($data as $key => $row) {
			$data[$key]['date'] = date('Y-m-d G:i:s', strtotime($row['date']));
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
		$import_terms = array_intersect_key($import_terms,array_unique(array_map('strtolower',$import_terms)));
		
		// extract a list of every category that is not already created
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
					echo '<li class="error">' . __("Error:", 'catablog') . sprintf(' %s %s', "<strong>$make_term</strong>", $error) . '</li>';
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
						echo '<li class="updated">' . __("Success:", 'catablog') . sprintf(__(' %s inserted into catalog categories.', 'catablog'), "<em>$make_term</em>") . '</li>';
					}
				}							
			}
		}
		
		// render complete making new categories message
		if ($new_term_made) {
			echo '<li class="updated"><strong>'.__('All New Categories Created', 'catablog').'</strong></li>';
			echo '<li>&nbsp;</li>';
		}
		else {
			echo '<li class="updated"><strong>'.__('No New Categories Created', 'catablog').'</strong></li>';
			echo '<li>&nbsp;</li>';
		}
		
		// load all existing catalog item ids
		$existant_ids = CataBlogItem::getItemIds();
		
		// import each new catalog item
		$new_order = wp_count_posts($this->custom_post_name)->publish;
		foreach ($data as $row) {
			$error = false;
			
			$success_message = '<li class="updated">' . __('Update:', 'catablog') . sprintf(__(' %s updated in database.', 'catablog'), '<em>'.$row['title'].'</em>') . '</li>';
			$error_message   = '<li class="error">' . __('Error:', 'catablog') . sprintf(__(' %s was not inserted into the database.', 'catablog'), '<strong>'.$row['title'].'</strong>') . '</li>';
			
			if (mb_strlen($row['title']) < 1) {
				$error = '<li class="error"><strong>' . __('Error:', 'catablog') . "</strong> " . __('Item had no title and could not be made.', 'catablog') . '</li>';
			}
			if (mb_strlen($row['image']) < 1) {
				$error = '<li class="error"><strong>' . __('Error:', 'catablog') . "</strong> " . __('Item had no primary image name and could not be made.', 'catablog') . '</li>';
			}
			
			if ($error) {
				echo $error;
			}
			else {
				
				// transform categories array
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
				
				// transform subimages array
				$subimages = $row['subimages'];
				if (is_array($subimages) === false) {
					if (mb_strlen($subimages) > 0) {
						$subimages = explode('|', $subimages);
					}
					else {
						$subimages = array();
					}
				}
				$row['sub_images'] = $subimages;
				unset($row['subimages']);
				
				// unset id if it is not already in the database.
				if (!in_array($row['id'], $existant_ids)) {
					$success_message = '<li class="updated">' . __('Insert:', 'catablog') . sprintf(__(' %s inserted into the database.', 'catablog'), '<em>'.$row['title'].'</em>') . '</li>';
					unset($row['id']);
				}
				
				$item = new CataBlogItem($row);
				
				$item->setOrder($new_order);
				
				$results = $item->save();
				if ($results === true) {
					if (!isset($row['id'])) {
						$existant_ids[] = $item->getId();
					}
					echo $success_message;
				}
				else {
					echo $results;
				}
			}
			
			$new_order += 1;
			
		}
		
		echo '<li class="updated"><strong>' . __('All Catalog Items Processed', 'catablog') . '</strong></li>';
		
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
					case UPLOAD_ERR_INI_SIZE:
						return __("Uploaded File Exceeded The PHP Configurations Max File Size.", 'catablog');
						break;
					case UPLOAD_ERR_FORM_SIZE:
						return __("Upload File Exceeded The HTML Form's Max File Size.", 'catablog');
						break;
					case UPLOAD_ERR_PARTIAL:
						return __("File Only Partially Uploaded, Please Try Again.", 'catablog');
						break;
					case UPLOAD_ERR_NO_FILE:
						return __("No File Selected For Upload. Please Resubmit The Form.", 'catablog');
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						return __("Your Server's PHP Configuration Does Not Have A Temporary Folder For Uploads, Please Contact The System Admin.", 'catablog');
						break;
					case UPLOAD_ERR_CANT_WRITE:
						return __("Your Server's PHP Configuration Can Not Write To Disc, Please Contact The System Admin.", 'catablog');
						break;
					case UPLOAD_ERR_EXTENSION:
						return __("A PHP Extension Is Blocking PHP From Excepting Uploads, Please Contact The System Admin.", 'catablog');
						break;
					default:
						return __("An Unknown Upload Error Has Occurred", 'catablog');
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