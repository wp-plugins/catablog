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
	private $version      = "0.6.5";
	private $db_version   = "0.1";
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
		register_deactivation_hook($this->plugin_file, array(&$this, 'uninstall'));
		
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
			$this->save_item();
			$this->admin_view();
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
				update_option('image_size', $_REQUEST['image_size']);
				$this->wp_message("Thumbnail Size Set To " . $_REQUEST['image_size'] . " Pixels");
			}
		}

		require($this->directories['template'] . '/admin-options.php');
	}
	
	
	
	
	/**********************************************
	** Frontend Actions
	**********************************************/
	public function frontend_head() {
		if (file_exists(get_stylesheet_directory().'/catablog.css')) {
			echo '<link rel="stylesheet" type="text/css" href="'. get_stylesheet_directory() .'/catablog.css" media="all" />';
		}
		else if (file_exists(get_template_directory().'/catablog.css')) {
			echo '<link rel="stylesheet" type="text/css" href="'. get_template_directory() .'/catablog.css" media="all" />';
		}
		else {
			echo '<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/catablog/css/catablog.css" media="all" />';
		}
	}

	public function frontend_content($atts) {
		$results = $this->get_items();
		$size    = get_option('image_size');
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
		// create the plugin's database
		$table = $this->db_table;
		echo $structure = "CREATE TABLE $table (
			`id` INT(9) NOT NULL AUTO_INCREMENT,
			`order` INT(9) NOT NULL,
			`image` VARCHAR(255) NOT NULL,
			`title` VARCHAR(255) NOT NULL,
			`link` TEXT,
			`description` TEXT,
			UNIQUE KEY id (id)
		);";
		$this->wpdb->query($structure);
				
		// reset the image size to default
		update_option('image_size', 200);
		
		// make directory for image storage
		$dirs = array('uploads', 'thumbnails', 'originals');
		foreach ($dirs as $dir) {
			mkdir($this->directories[$dir]);
		}
	}

	public function uninstall() {
		$table = $this->db_table;
		$tear_down = "DROP TABLE $table;";
		$this->wpdb->query($tear_down);
								
		delete_option('image_size');
		
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
	private function get_items() {
		$table = $this->db_table;
		$query = $this->wpdb->prepare("SELECT * FROM $table ORDER BY `order`", array());
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
		$new_image = $_FILES['image']['error'] != 4;
		
		if ($new_image) {
			$tmp = $_FILES['image']['tmp_name'];
			$filepath_thumb    = $this->directories['thumbnails'] . '/' . $this->string2slug($_FILES['image']['name']);
			$filepath_original = $this->directories['originals'] . '/' . $this->string2slug($_FILES['image']['name']);
			
			if (is_file($filepath_thumb) && is_file($filepath_original)) {
				$this->wp_message('Filename already exists, please rename image before trying to upload it again.');
				return false;
			}

			if (is_uploaded_file($tmp)) {
				list($width, $height) = getimagesize($tmp);
				$final_size = get_option('image_size');

				$canvas = imagecreatetruecolor($final_size, $final_size);
				$bg_color = imagecolorallocate($canvas, 0, 0, 0);
				imagefill($canvas, 0, 0, $bg_color);

				$upload = imagecreatefromjpeg($tmp);
				imagecopyresampled($canvas, $upload, 0, 0, 0, 0, $final_size, $final_size, $width, $height);

				imagejpeg($canvas, $filepath_thumb, 80);	
				move_uploaded_file($tmp, $filepath_original);
			}

		}

		$table = $this->db_table;
		$image = $this->string2slug($_FILES['image']['name']);
		$title = $_REQUEST['title'];
		$link  = $_REQUEST['link'];
		$desc  = $_REQUEST['description'];
		$order = $_REQUEST['order'];

		if (isset($_REQUEST['id'])) {
			// old entry, need to update row
			$id = $_REQUEST['id'];
			if ($new_image) {
				$this->wpdb->update($table, array('order'=>$order, 'image'=>$image, 'title'=>$title, 'link'=>$link, 'description'=>$desc), array('id'=>$id), array('%d', '%s', '%s', '%s', '%s'), array('%d'));
			}
			else {
				$this->wpdb->update($table, array('order'=>$order, 'title'=>$title, 'link'=>$link, 'description'=>$desc), array('id'=>$id), array('%d', '%s', '%s', '%s'), array('%d'));		
			}
			
			$this->wp_message('Your Changes Have Been Saved');
		} else {
			// new entry, need to insert row
			$this->wpdb->insert($table, array('order'=>$order, 'image'=>$image, 'title'=>$title, 'link'=>$link, 'description'=>$desc), array('%d', '%s', '%s', '%s', '%s'));
			$this->wp_message('New Catalog Item Added');
		}		
	}
	
	
	private function string2slug($string) {
		$string = strtolower(trim($string));
		$string = preg_replace('/[^a-z0-9-.]/', '-', $string);
		$string = preg_replace('/-+/', "-", $string);
		return $string;
	}
	
	private function wp_message($message) {
		echo "<div id='message' class='updated'>";
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