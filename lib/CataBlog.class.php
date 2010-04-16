<?php


/**********************************************
**  CataBlog Class
**********************************************/
class CataBlog {
	
	
	/**********************************************
	**  Variable Declaration and Construct Method
	**********************************************/
	private $wpdb         = null;
	private $items        = array();
	private $version      = "0.7.1";
	private $dir_version  = 1;
	private $db_version   = 2;
	private $db_table     = "catablog";
	private $user_level   = 8;
	private $directories  = array();
	private $urls         = array();
	
	public function __construct() {
		global $user_level;
		$user_level = $this->user_level;
		
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->db_table = $wpdb->prefix . $this->db_table;
		
		global $now; 
		$now = mysql2date('Y-m-d', current_time('mysql'));
		
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
		register_activation_hook($this->plugin_file, array(&$this, 'install'));
		register_deactivation_hook($this->plugin_file, array(&$this, 'deactivate'));
		
		add_action('admin_init', array(&$this, 'admin_head'));
		add_action('admin_menu', array(&$this, 'admin_menu'));

		add_action('wp_head', array(&$this, 'frontend_head'));
		add_shortcode('catablog', array(&$this, 'frontend_content'));
	}
	
	
	
	
	/**********************************************
	**  Admin Panel Integration Points
	**********************************************/
	public function admin_head() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('catablog-admin-js', $this->urls['javascript'] . '/catablog-admin.js');
		wp_enqueue_style('catablog-admin-css', $this->urls['css'] . '/catablog-admin.css');
	}
	
	public function admin_menu() {
		add_menu_page("CataBlog &rsaquo; View", "CataBlog", $this->user_level, $this->plugin_file, "", $this->urls['plugin']."/images/cb_icon_16.png");
		add_submenu_page($this->plugin_file, "CataBlog &rsaquo; View", 'Manage', $this->user_level, $this->plugin_file, array(&$this, 'admin_view'));
		add_submenu_page($this->plugin_file, "CataBlog &rsaquo; Edit", 'Add New', $this->user_level, 'catablog_edit', array(&$this, 'admin_edit'));
		add_submenu_page($this->plugin_file, "CataBlog &rsaquo; Options", 'Options', $this->user_level, 'catablog_options', array(&$this, 'admin_options'));
	}
	
	
	
	
	/**********************************************
	**  Admin Panel Actions
	**********************************************/	
	public function admin_view() {
		$results = $this->get_items();		
		require_once($this->directories['template'] . '/admin-view.php');
	}
	
	public function admin_edit() {
		if (isset($_POST['save'])) {
			// save record
			if ($this->save_item() == false) {
				$result = $_REQUEST;
				require($this->directories['template'] . '/admin-edit.php');
			}
			else {
				$this->admin_view();
			}
		}
		elseif (isset($_REQUEST['action'])) {
			if ($_REQUEST['action'] == 'remove') {
				$this->delete_item($_REQUEST['id']);
				$this->admin_view();
			}
			elseif ($_REQUEST['action'] == 'edit') {
				// edit record
				if (isset($_REQUEST['id'])) {
					$update = true;
					$id = $_REQUEST['id'];
					$result = $this->get_item($id);
				}
				
				require($this->directories['template'] . '/admin-edit.php');
			}
		} else {
			// view record
			$update = false;
			$result = array('id'=>'', 'image'=>'', 'title'=>'', 'description'=>'');
			require($this->directories['template'] . '/admin-edit.php');
		}	
	}

	public function admin_options() {
		if (isset($_POST['save'])) {
			if (true) {
				update_option('catablog_image_size', $_REQUEST['image_size']);
				$this->wp_message("Thumbnail Size Set To " . $_REQUEST['image_size'] . " Pixels");
			}
		}

		require($this->directories['template'] . '/admin-options.php');
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
		
		$size    = get_option('catablog_image_size');
		$ml      = ($size + 10) . 'px';

		$cb_catalog = "";
		foreach ($results as $result) {
			$link_start = "";
			$link_end   = "";
			if (strlen($result->link) > 0) {
				$link_start = "<a href='".$result->link."'>";
				$link_end   = "</a>";
			}

			$cb_catalog .= "<div class='catablog_row'>";
			$cb_catalog .= $link_start."<img src='".$this->urls['thumbnails']."/$result->image' class='catablog_image' width='$size' height='$size' />".$link_end;
			$cb_catalog .= "<h4 class='catablog_title' style='margin-left:$ml'>";
			$cb_catalog .=   $link_start;
			$cb_catalog .=   htmlspecialchars($result->title, ENT_QUOTES, 'UTF-8');
			$cb_catalog .=   $link_end;
			$cb_catalog .= "</h4>";

			$cb_catalog .= "<p class='catablog_description' style='margin-left:$ml'>".nl2br(htmlspecialchars($result->description, ENT_QUOTES, 'UTF-8'))."</p>";
			$cb_catalog .= "</div>";
		}

		return $cb_catalog;
	}
	
	
	
	
	/**********************************************
	**  Plugin Activate and Deactivate Actions
	**********************************************/
	public function install() {
		
		$table = $this->db_table;
		
		// NO CATABLOG TABLE IN THE DATABASE. CREATE NEW INSTALLATION
		if ($this->wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
			// no table in database, wipe everything and start over
			$this->uninstall();
			
			$sql = "CREATE TABLE $table (
				`id` INT(9) NOT NULL AUTO_INCREMENT,
				`order` INT(9) NOT NULL,
				`image` VARCHAR(255) NOT NULL,
				`title` VARCHAR(255) NOT NULL,
				`link` TEXT,
				`description` TEXT,
				`tags` VARCHAR(255) NOT NULL,
				UNIQUE KEY id (id)
			);";
			$this->wpdb->query($sql);
			
			// update application options
			update_option('catablog_db_version', $this->db_version);
			update_option('catablog_dir_version', $this->dir_version);
			update_option('catablog_image_size', 200);
			
			// make all directories for image storage
			$dirs = array('uploads', 'thumbnails', 'originals');
			foreach ($dirs as $dir) {
				$is_dir  = is_dir($this->directories[$dir]);
				$is_file = is_file($this->directories[$dir]);
				if (!$is_dir && !$is_file) {
					mkdir($this->directories[$dir]);
				}
			}
		}
		
		// A TABLE EXISTS, LETS UPDATE IT AND THEN UPDATE OUR OPTIONS AND DIRECTORY SCHEMA
		else {
			if ($this->db_version > get_option('catablog_db_version')) {
				
				// table is out of date, update it
				$sql = "CREATE TABLE $table (
					`id` INT(9) NOT NULL AUTO_INCREMENT,
					`order` INT(9) NOT NULL,
					`image` VARCHAR(255) NOT NULL,
					`title` VARCHAR(255) NOT NULL,
					`link` TEXT,
					`description` TEXT,
					`tags` VARCHAR(255) NOT NULL,
					UNIQUE KEY id (id)
				);";
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				
				// Transform the old image_size option into catablog_image_size
				if (get_option('catablog_image_size') === false) {
					if (get_option('image_size') === false) {
						update_option('catablog_image_size', 200);
					}
					else {
						update_option('catablog_image_size', get_option('image_size'));
						delete_option('image-size');
					}
				}
				else {
					if (get_option('image_size')) {
						delete_option('image-size');
					}
				}
				
				// update application options
				update_option('catablog_db_version', $this->db_version);
				update_option('catablog_dir_version', $this->dir_version);
				
				
				// see if the old and new image directory exists
				$old_directory_exists = false;
				if (is_dir($this->directories['old_pics'])) {
					$old_directory_exists = true;
				}
				
				$new_directory_exists = false;
				if (is_dir($this->directories['uploads'])) {
					$new_directory_exists = true;
				}
				
				if ($old_directory_exists) {
					if ($new_directory_exists) {
						$this->remove_old_pics();
					}
					else {
						$this->make_new_pic_directories();
						$this->transfer_old_pics_to_new();
						$this->remove_old_pics();
					}
				}
				else {
					if ($new_directory_exists) {
						// do nothing
					}
					else {
						$this->make_new_pic_directories();
					}
				}
				
			}
			else { /* table is there and a newer version, leave alone */ }
		}
	}
	
	public function deactivate() {
		// do nothing;
	}
	
	public function uninstall() {
		$table = $this->db_table;
		$tear_down = "DROP TABLE $table;";
		$this->wpdb->query($tear_down);
								
		delete_option('catablog_db_version');
		delete_option('catablog_dir_version');
		delete_option('catablog_image_size');
		
		$this->remove_old_pics();
		$this->remove_new_pics();

	}
	
	
	
	
	
	
	
	private function make_new_pic_directories() {
		
		// determine which directories need to be made for storing images
		$dirs = array(0=>'uploads', 1=>'thumbnails', 2=>'originals');
		if (is_dir($this->dirs['uploads'])) {
			unset($directories[0]); // so far so good, catablog directory in uploads folder
			if (is_dir($this->dirs['thumbnails'])) {
				unset($directories[1]); // still good, thumbnails directory in uploads/catablog
			}
			if (is_dir($this->dirs['originals'])) {
				unset($directories[2]); // all directories are present, originals directory in uploads/catablog
			}
		}
		
		// create the determined directories from the last step
		foreach ($dirs as $dir) {
			$is_dir  = is_dir($this->directories[$dir]);
			$is_file = is_file($this->directories[$dir]);
			if (!$is_dir && !$is_file) {
				mkdir($this->directories[$dir]);
			}
		}
	}
	
	private function transfer_old_pics_to_new() {
		// if old directory exists move files to the new thumbnails directory
		$old_thumbnail_dir = $this->directories['old_pics'];
		
		if (is_dir($old_thumbnail_dir)) {
		    $ignore = array('cgi-bin', '.', '..');
		    $dh = @opendir($old_thumbnail_dir);
		    while (false !== ($file = readdir($dh))) {
		        if (!in_array($file, $ignore)) {
					$extension = substr( $file , (strrpos('.')+1) );
					if ($extension == 'jpg') {
						rename("$old_thumbnail_dir/$file", $this->directories['thumbnails']."/$file");
					}
		        }
		    }
		    closedir($dh);
		}
	}
	
	private function remove_old_pics() {
		$mydir = $this->directories['old_pics'];
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
	
	private function remove_new_pics() {
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
	
	
	
	
	
	
	
	
	
	
	
	private function load_template($name) {
		require_once($this->directories['template'] . "/$name.php");
	}
	
	
	
	/**********************************************
	**  Private Database Methods
	**********************************************/
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
		
		$this->wp_message('Catalog Item Removed Successfully');
	}
	
	private function save_item() {
		$escaped_post = array_map('stripslashes_deep', $_POST);
		
		$new_image  = $_FILES['image']['error'] != 4;
		$image_name = sanitize_title($escaped_post['title']) . ".jpg";
		
		// PROCESS IMAGE
		if ($new_image) {
			$tmp = $_FILES['image']['tmp_name'];
			$filepath_thumb    = $this->directories['thumbnails'] . "/$image_name";
			$filepath_original = $this->directories['originals'] . "/$image_name";
			
			// if (is_file($filepath_thumb) && is_file($filepath_original)) {
			// 	$this->wp_message('Filename already exists, please rename image before trying to upload it again.');
			// 	return false;
			// }

			if (is_uploaded_file($tmp)) {
				list($width, $height) = getimagesize($tmp);
				
				$size = getimagesize($tmp);
				// print_r($size);
				
				$width  = $size[0];
				$height = $size[1];
				$format = $size['mime'];
				
				if ($width < 1 || $height < 1 || $format != 'image/jpeg') {
					$this->wp_error('Image Error: None Supported Format');
					return false;
				}
				
				$final_size = get_option('catablog_image_size');

				$canvas = imagecreatetruecolor($final_size, $final_size);
				$bg_color = imagecolorallocate($canvas, 0, 0, 0);
				imagefill($canvas, 0, 0, $bg_color);

				$upload = imagecreatefromjpeg($tmp);
				imagecopyresampled($canvas, $upload, 0, 0, 0, 0, $final_size, $final_size, $width, $height);

				imagejpeg($canvas, $filepath_thumb, 80);	
				move_uploaded_file($tmp, $filepath_original);
			}
			else {
				$this->wp_error('Image Error: None Supported Format');
				$_REQUEST['image'] = $_REQUEST['saved_image'];
				return false;
			}

		}
		
		
		
		// PROCESS DATABASE
		$table = $this->db_table;
		$title = $escaped_post['title'];
		$link  = $escaped_post['link'];
		$desc  = $escaped_post['description'];
		$tags  = $escaped_post['tags'];
		$order = $escaped_post['order'];
		$image = $image_name;
		
		if (isset($_REQUEST['id'])) {
			// old entry, need to update row
			$id = $_REQUEST['id'];
			if ($new_image) {
				$this->wpdb->update($table, array('order'=>$order, 'image'=>$image, 'title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags), array('id'=>$id), array('%d', '%s', '%s', '%s', '%s', '%s'), array('%d'));
			}
			else {
				$this->wpdb->update($table, array('order'=>$order, 'title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags), array('id'=>$id), array('%d', '%s', '%s', '%s', '%s'), array('%d'));
			}
			
			$this->wp_message('Your Changes Have Been Saved');
		} else {
			// new entry, need to insert row
			$this->wpdb->insert($table, array('order'=>$order, 'image'=>$image, 'title'=>$title, 'link'=>$link, 'description'=>$desc, 'tags'=>$tags), array('%d', '%s', '%s', '%s', '%s', '%s'));
			$this->wp_message('New Catalog Item Added');
		}
		
		return true;		
	}
	
	
	private function string2slug($string) {
		
		return sanitize_title($string);
		
		// $string = strtolower(trim($string));
		// 		$string = preg_replace('/[^a-z0-9-.]/', '-', $string);
		// 		$string = preg_replace('/-+/', "-", $string);
		// 		return $string;
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
	public function compatibility() {
		// Pre-2.6 compatibility
		if ( !defined('WP_CONTENT_URL') ) {
			define( 'WP_CONTENT_URL', get_bloginfo('wpurl') . '/wp-content');
		}
		if ( !defined('WP_CONTENT_DIR') ) {
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}
		if ( !defined('WP_PLUGIN_URL') ) {
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
		}

		// require(WP_CONTENT_DIR . '/../wp-config.php');		
	}
	
	
		
}