<?php


/**********************************************
**  CataBlog Class
**********************************************/
class CataBlog {
	
	// plugin component version numbers
	private $version     = "1.1.6";
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
	private $default_bg_color       = "#ffffff";
	
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
		add_action('wp_ajax_catablog_delete_subimage', array(&$this, 'ajax_delete_subimage'));
		
		// register frontend actions
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_init'));
		add_action('wp_print_footer_scripts', array(&$this, 'frontend_footer'));
		add_shortcode('catablog', array(&$this, 'frontend_content'));
		
		add_filter('the_content', array(&$this, 'frontend_catalog_item_page'));
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
		// $params['show-ui']             = false;

		$params['exclude_from_search'] = false == $this->options['public-catalog-items'];		
		$params['publicly_queryable']  = $this->options['public-catalog-items'];
		$params['show_in_nav_menus']   = $this->options['public-catalog-items'];
		$params['rewrite']             = array('slug'=>$this->options['public-catalog-slug']);
		
		$params['supports']            = array('title', 'editor');
		$params['description']         = "A CataBlog Item";
		$params['hierarchical']        = false;
		$params['taxonomies']          = array($this->custom_tax_name);

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
		

		
		// go straigt to save action, no interface
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-save') !== false) {
			$this->admin_save(true);
		}
		
		// go striagt to remove all subimages action
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-replace-image') !== false) {
			$this->admin_replace_main_image(true);
		}
		
		// if add sub image is being called go directly to admin_add_subimage method
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-add-subimage') !== false) {
			$this->admin_add_subimage(true);
		}
		
		// go striagt to remove all subimages action
		if(strpos($_SERVER['QUERY_STRING'], 'catablog-remove-subimages') !== false) {
			$this->admin_remove_subimages();
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
		
		wp_enqueue_script('farbtastic');
		wp_enqueue_style('farbtastic');
		
		
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
		add_submenu_page('catablog-hidden', "Bulk Edit CataBlog Items", "Bulk", $this->user_level, 'catablog-bulkedit', array(&$this, 'admin_bulk_edit'));
		add_submenu_page('catablog-hidden', "Replace Main Image", "Replace", $this->user_level, 'catablog-replace-image', array(&$this, 'admin_replace_main_image'));
		add_submenu_page('catablog-hidden', "Add Sub Image to Item", "SubImage", $this->user_level, 'catablog-add-subimage', array(&$this, 'admin_add_subimage'));
		add_submenu_page('catablog-hidden', "Delete All Sub Images", "Remove-SubImages", $this->user_level, 'catablog-remove-subimages', array(&$this, 'admin_remove_subimages'));
		
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
		
		$selected_category = (isset($_GET['category']))? $_GET['category'] : 0;
		
		if ($selected_category > 0) {
			$selected_term = get_term_by('id', $selected_category, $this->custom_tax_name);
			$results = CataBlogItem::getItems($selected_term->name);
		}
		else {
			$results = CataBlogItem::getItems();
		}
		
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
		
		include_once($this->directories['template'] . '/admin-items.php');
	}
	
	public function admin_new() {
		if (function_exists('is_upload_space_available') && is_upload_space_available() == false) {
			include_once($this->directories['template'] . '/admin-discfull.php');
		}
		else {
			$result = new CataBlogItem();
			$new_item = true;
			include_once($this->directories['template'] . '/admin-edit.php');
		}		
	}
	
	public function admin_edit() {
		$new_item = false;
		if (isset($_REQUEST['id'])) {
			
			$result = CataBlogItem::getItem($_REQUEST['id']);
			
			
			switch ($_GET['message']) {
				case 1:
					$this->wp_message("Changes Saved Successfully");
					break;
			}
			
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
					if ($fullsize_different) {
						$recalculate_fullsize = true;
					}
				}
				
				
				// test if user needs to rewrite the permalinks
				// if ($this->options['public-catalog-items'] == false) {
				// 	if (isset($_REQUEST['public-catalog-items'])) {
				// 		$rewrite_permalinks = true;
				// 	}
				// }
				// if ($_REQUEST['public-catalog-slug'] != $this->options['public-catalog-slug']) {
				// 	$rewrite_permalinks = true;
				// }
				
				
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
				// $this->options['public-catalog-items'] = isset($post_vars['public-catalog-items']);
				// $this->options['public-catalog-slug']  = $post_vars['public-catalog-slug'];
				// $this->options['permalink-default']    = isset($post_vars['permalink-default']);
				
				$this->update_options();
				
				// recalculate thumbnail and fullsize images if necessary
				if ($recalculate_thumbnails || $recalculate_fullsize) {
					$save_message .= " - Please Let The Rendering Below Complete Before Navigating Away From This Page";
					
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
				
				// if ($rewrite_permalinks) {
				// 					$save_message .= " - <a href='options-permalink.php'>Update Your Permalink Structure NOW</a>";
				// 				}
								
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
		// $public_catalog_items_enabled = $this->options['public-catalog-items'];
		// $public_catalog_slug          = $this->options['public-catalog-slug'];
		// $permalink_default            = $this->options['permalink-default'];
		
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
		// TODO: make this method
	}
	
	
	public function admin_save($init_run=false) {
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
				
				// new catalog item, set subimages array empty
				$img = $_FILES['new_image']['tmp_name'];
				if (mb_strlen($img) > 0) {
					$image_validate = $result->validateImage($img);
					if ($image_validate === true) {
						$result->setImage($img);
						$result->setSubImages(array());						
					}
					else {
						if (!$init_run) {
							$this->wp_error($image_validate);
							include_once($this->directories['template'] . '/admin-edit.php');
							return true;
						}
					}
				}
				
				$validate  = $result->validate();
				if ($validate === true) {
					$result->save();
					header('Location: admin.php?page=catablog-edit&id=' . $result->getId() . '&message=1'); die;
				}
				else {
					if (!$init_run) {
						$this->wp_error($validate);
						include_once($this->directories['template'] . '/admin-edit.php');
						return true;
					}
				}
				
				
			}
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
						header('Location: admin.php?page=catablog-edit&id=' . $_POST['id']); die;
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
						header('Location: admin.php?page=catablog-edit&id=' . $_POST['id']); die;
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
	
	public function admin_remove_subimages() {
		$nonce_verified = wp_verify_nonce( $_REQUEST['_catablog_remove_subimages_nonce'], 'catablog_remove_subimages');
		if ($nonce_verified) {
			$to_delete = array();
			$result    = CataBlogItem::getItem($_POST['id']);
			$sub_images = $result->getSubImages();
			
			foreach ($sub_images as $key => $image) {
				$to_delete["sub$key-original"]  = $this->directories['originals'] . "/$image";
				$to_delete["sub$key-thumbnail"] = $this->directories['thumbnails'] . "/$image";
				$to_delete["sub$key-fullsize"]  = $this->directories['fullsize'] . "/$image";
			}
			
			foreach ($to_delete as $file) {
				if (is_file($file)) {
					unlink($file);
				}
			}
			// echo "<pre>"; print_r($to_delete); echo "</pre>";
			$result->setSubImages(array());
			$result->save();
			
			delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
		}
		else {
			echo "the form nonce was not verified, what are you doing?";
		}
		
		header('Location: admin.php?page=catablog-edit&id=' . $_POST['id']);
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
		
		// remove all data from database if clear box is checked
		$database_cleared = false;
		if (isset($_REQUEST['catablog_clear_db'])) {
			$items = CataBlogItem::getItems();
			foreach ($items as $item) {
				$item->delete(false);
			}
			
			$terms = get_terms($this->custom_tax_name, 'hide_empty=0');
			foreach ($terms as $term) {
				wp_delete_term($term->term_id, $this->custom_tax_name);
			}
			
			$database_cleared = true;
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
		
		if ($format == 'csv') {
			ini_set('auto_detect_line_endings', true);
			
			$outstream   = fopen("php://output", 'w');
			$field_names = array('order','image','subimages','title','link','description','categories','price','product_code');
			$header      = NULL;
			
			foreach ($results as $result) {
				if (!$header) {
					fputcsv($outstream, $field_names, ',', '"');
					$header = true;
				}
				fputcsv($outstream, $result->getValuesArray(), ',', '"');
			}
			
			fclose($outstream);
			die;
		}
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
		}
		
		$new_rows = array();
		$originals = new CataBlogDirectory($this->directories['originals']);
		if ($originals->isDirectory()) {
			foreach ($originals->getFileArray() as $file) {
				if (!in_array($file, $image_names)) {
					
					$extension = preg_match('/\\.[^.\\s]{3,4}$/', $file, $matches);
					$extension = $matches[0];
					
					$media_accepted = array('.jpg', '.jpeg', '.gif', '.png');
					if (in_array($extension, $media_accepted)) {
						$title = str_replace(array('-','_'), ' ', $file);
						$title = str_replace($extension, '', $title);
						
						$params = array();
						$params['title']       = $title;
						$params['image']       = $file;

						$new_item = new CataBlogItem($params);
						$new_item->save();

						$new_rows['ids'][]    = $new_item->getId();
						$new_rows['titles'][] = $new_item->getTitle();				
					}
				}
			}
		}
		
		include_once($this->directories['template'] . '/admin-rescan.php');
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
		
		// NOT NEEDED. is a post of the catablog type
		// foreach ($posts as $post) {
		// 	if ($post->post_type == $this->custom_post_name) {
		// 		$this->load_support_files = true; //shortcode is being used on page
		// 	}
		// }
		
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
				echo "<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('.catablog-clickable').catablogLightbox(); });</script>\n";
			}
		}
	}

	public function frontend_content($atts) {
		
		extract(shortcode_atts(array('category'=>false, 'tag'=>false), $atts));
			
		// if an old tag attribute is used put it in categories instead
		if ($category === false && $tag !== false) {
			$category = $tag;
		}
		
		// get items and start the output buffer
		$results = CataBlogItem::getItems($category);
		ob_start();
		
		foreach ($results as $result) {
			echo $this->frontend_render_catalog_row($result);
		}
		
		// give the credit where it is due
		echo "<p class='catablog-credits'><!-- Catalog Content by CataBlog $this->version - http://catablog.illproductions.com/ --></p>";
		
		return ob_get_clean();
	}
	
	public function frontend_catalog_item_page($content) {
		global $post;
		if ($post->post_type == $this->custom_post_name){
			$result  = CataBlogItem::getItem($post->ID);
			$content = $this->frontend_render_catalog_row($result, false);
		}
		
		return $content;
	}
	
	public function frontend_render_catalog_row($result, $show_title=true) {
		$thumbnail_size = $this->options['thumbnail-size'];
		
		$values = array();
		
		// new set
		
		
		// compatibility set
		$values['image-size']        = $thumbnail_size;
		$values['paypal-email']      = $this->options['paypal-email'];
		$values['min-height']        = "style='min-height:$thumbnail_size"."px; height:auto !important; height:$thumbnail_size"."px;'";
		$values['hover-title-size']  = ($thumbnail_size - 10) . 'px';
		$values['margin-left']       = ($thumbnail_size + 10) . 'px';		
		$values['lightbox']          = ($this->options['lightbox-enabled'])? "catablog-clickable" : "";
		
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
		// elseif ($this->options['permalink-default'] && $this->options['public-catalog-items']) {
			// $link = $result->getPermalink();
			// $values['title'] = "<a href='$link' $target $rel>".$result->getTitle()."</a>";
		// }
		else {
			$values['title'] = $result->getTitle();
		}
		
		// set the other values of the item into an array
		$values['title-text']      = $result->getTitle();
		$values['image']           = $this->urls['thumbnails'] . "/". $result->getImage();
		$values['image-fullsize']  = $this->urls['fullsize'] . "/". $result->getImage();
		$values['link']            = $link;
		$values['link-target']     = $target;
		$values['link-rel']        = $rel;
		$values['description']     = $description;
		$values['price']           = number_format(((float)($result->getPrice())), 2, '.', '');
		$values['product-code']    = $result->getProductCode();
		
		$values['main-image']      = "<img class='catablog-image ".$values['lightbox']."' src='".$values['image']."' height='".$values['image-size']."' width='".$values['image-size']."' alt='' />";
		$values['sub-images']      = "";
		foreach ($result->getSubImages() as $image) {
			$c = ($this->options['lightbox-enabled'])? "catablog-clickable" : "";
			$values['sub-images'] .= "<img src='".$this->urls['thumbnails']."/$image' class='catablog-subimage catablog-image  $c' />";
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
		$default_options['link-relationship']  = "";
		$default_options['view-theme']         = file_get_contents($this->directories['template'] . '/views/default.htm');
		$default_options['view-buynow']        = '';
		$default_options['filter-description'] = false;
		$default_options['nl2br-description']  = true;
		// $default_options['public-catalog-items'] = false;
		// $default_options['public-catalog-slug']  = 'catablog-item';
		// $default_options['permalink-default']  = false;
		
		
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
		
		delete_transient('dirsize_cache'); // WARNING!!! transient label hard coded.
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
				print_r($row);
				if(!$header) {
					$header = $row;
					if (count($header) != 9) {
						return $data;
					}
				}
				else if (count($row) == 9) {
					$data[] = array_combine($header, $row);
				}
			}
			fclose($handle);
		}

		return $data;
	}
	
	private function load_array_to_database($data) {
		// echo "<pre>";
		foreach ($data as $row) {
			$success_message = '<li class="updated">Success: <em>' . $row['title'] . '</em> inserted into the database.</li>';
			$error_message   = '<li class="error"><strong>Error:</strong> <em>' . $row['title'] . '</em> was not inserted into the database.</li>';
			
			if (mb_strlen($row['title']) < 1 || mb_strlen($row['image']) < 1) {
				echo $error_message;
			}
			else {
				$terms = (mb_strlen($row['categories']))? explode('|', $row['categories']) : array();
				$row['categories'] = $terms;
				
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
								if (isset($insert_return['term_id'])) {
									$new_term_id = $insert_return['term_id'];
									if ($new_term_id > 0) {
										$terms[$key] = (integer) $new_term_id;
									}
								}							
							}


						}					
					} 
				}
				
				$item = new CataBlogItem($row);
				
				$subimages = (mb_strlen($row['subimages']))? explode('|', $row['subimages']) : array();
				$row['subimages'] = $subimages;
				foreach ($row['subimages'] as $subimage) {
					$item->setSubImage($subimage);
				}
				
				if ($item->save() !== false) {
					echo $success_message;
				}
				else {
					echo $error_message;
				}
			}
		}
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