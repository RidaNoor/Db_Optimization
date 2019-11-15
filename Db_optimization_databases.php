<?php
/**
 * @package Optimize Database
 */
/*
Plugin Name: Optimize Database
Description: Optimizes the Wordpress Database after Cleaning it out
*/

//MAIN CLASS

global $database_optimization_class;
$database_optimization_class = new OptimizeDatabase();

class OptimizeDatabase {

	// PLUGIN OPTIONS
	var $database_optimization_database_optimizationoptions       = array();
	
	// EXCLUDED TABELS
	var $database_optimization_database_optimizationexcluded_tabs = array();

	// MULTISITE STRUCTURE
	var $database_optimization_ms_prefixes       = array();
	
	// DATABASE TABLES
	var $database_optimization_tables            = array();
	
	// MINIFYING?
	var $database_optimization_minify;
	
	// MAIN PLUGIN FILE
	var $database_optimization_main_file         = 'optimize-database/optimize-database.php';
	
	// LOCALIZATION
	var $database_optimization_txt_domain        = 'optimize-database';

	// CURRENT SITE DATE (yyyymmddHHiiss) AND UNIX TIMESTAMP, BASED ON TIMEZONE OF THE SITE
	
	var $database_optimization_current_date;
	var $database_optimization_timestamp;
	var $database_optimization_last_run_seconds;
	
	// PLUGIN
	var $database_optimization_plugin_url;
	var $database_optimization_plugin_path;
	
	// LOGGING
	var $database_optimization_logfile_url;
	var $database_optimization_logfile_path;
	
	// OBJECTS
	var $database_optimization_cleaner_obj;
	var $database_optimization_displayer_obj;
	var $database_optimization_logger_obj;
	var $database_optimization_multisite_obj;
	var $database_optimization_scheduler_obj;
	var $database_optimization_utilities_obj;
	
	// PAGE TIMER
	var	$database_optimization_start_time;


	/*******************************************************************************
	 * 	CONSTRUCTOR
	 *******************************************************************************/
	function __construct() {
		// INITIALIZE PLUGIN
		add_action('init', array(&$this, 'database_optimization_init'));
	} // __construct()
	
	
	/*******************************************************************************
	 * 	INITIALIZE PLUGIN
	 *******************************************************************************/	
	function database_optimization_init() {
		// LOAD CLASSES
		$this->database_optimization_classes();

		// URLS AND DIRECTORIES
		$this->database_optimization_urls_dirs();

		// GET (MULTI-SITE) NETWORK INFORMATION	
		$this->database_optimization_multisite_obj->database_optimization_ms_network_info();
						
		// LOAD OPTIONS
		$this->database_optimization_load_options();		
		
		// INITIALIZE WORDPRESS HOOKS
		$this->database_optimization_init_hooks();
		
		// GET THE DATABASE TABLES
		$this->database_optimization_tables = $this->database_optimization_utilities_obj->database_optimization_get_tables();
			
		// GET EXCLUDED TABLES FROM SETTINGS
		$this->database_optimization_database_optimizationexcluded_tabs = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimization_database_optimizationexcluded_tabs');
		
		// USE THE NON-MINIFIED VERSION OF SCRIPTS AND STYLE SHEETS WHILE DEBUGGING
		$this->database_optimization_minify = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
		
		// LOAD STYLE SHEET (ONLY ON RELEVANT PAGES)
		$this_page = '';
		if(isset($_GET['page'])) $this_page = $_GET['page'];
		// v4.0.3
		if($this->database_optimization_is_relevant_page())
		{	wp_register_style('database_optimization-style'.$this->database_optimization_version, plugins_url('css/style'.$this->database_optimization_minify.'.css', __FILE__));
			wp_enqueue_style('database_optimization-style'.$this->database_optimization_version);
		}

		if(defined('RUN_OPTIMIZE_DATABASE') && RUN_OPTIMIZE_DATABASE == 1) $this->database_optimization_start(true);
	} // database_optimization_init()


	/*******************************************************************************
	 * 	LOAD AND INITIALIZE CLASSES
	 *******************************************************************************/		
	function database_optimization_classes() {
		// LOAD CLASSES
		include_once('classes/database_optimization-cleaner.php');
		include_once('classes/database_optimization-displayer.php');
		include_once('classes/database_optimization-logger.php');
		include_once('classes/database_optimization-multisite.php');
		include_once('classes/database_optimization-scheduler.php');
		include_once('classes/database_optimization-utilities.php');
		
		// CREATE INSTANCES
		$this->database_optimization_cleaner_obj   = new database_optimization_Cleaner();
		$this->database_optimization_displayer_obj = new database_optimization_Displayer();
		$this->database_optimization_logger_obj    = new database_optimization_Logger();
		$this->database_optimization_multisite_obj = new database_optimization_MultiSite();		
		$this->database_optimization_scheduler_obj = new database_optimization_Scheduler();
		$this->database_optimization_utilities_obj = new database_optimization_Utilities();
	} // database_optimization_classes()


	// 	INITIALIZE URLS AND DIRECTORIES
	
	function database_optimization_urls_dirs() {
		$this->database_optimization_plugin_url         = plugins_url( '/', __FILE__ );
		$this->database_optimization_plugin_path        = plugin_dir_path(__FILE__);
		$this->database_optimization_logfile_url        = $this->database_optimization_plugin_url.'logs/optimize-db-log.html';
		$this->database_optimization_logfile_path       = $this->database_optimization_plugin_path.'logs/optimize-db-log.html';
		$this->database_optimization_logfile_debug_path = $this->database_optimization_plugin_path.'logs/optimize-db-log.txt';		
	} // database_optimization_urls_dirs()


	// 	LOAD OPTIONS
	 
	function database_optimization_load_options() {
		// GET OPTIONS
		$this->database_optimization_database_optimizationoptions = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimization_database_optimizationoptions');
		
		if(!isset($this->database_optimization_database_optimizationoptions['version']))
			// THIS VERSION IS FROM BEFORE 4.0: CONVERT OPTIONS
			$this->database_optimization_convert_options();
		
		if(!isset($this->database_optimization_database_optimizationoptions['adminbar']))
			$this->database_optimization_database_optimizationoptions['adminbar']         = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['adminmenu']))
			$this->database_optimization_database_optimizationoptions['adminmenu']        = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['clear_pingbacks']))
			$this->database_optimization_database_optimizationoptions['clear_pingbacks']  = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['clear_spam']))			
			$this->database_optimization_database_optimizationoptions['clear_spam']       = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['clear_tags']))
			$this->database_optimization_database_optimizationoptions['clear_tags']       = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['clear_transients']))
			$this->database_optimization_database_optimizationoptions['clear_transients'] = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['clear_trash']))
			$this->database_optimization_database_optimizationoptions['clear_trash']      = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['delete_older']))
			$this->database_optimization_database_optimizationoptions['delete_older']     = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['database_optimizationrevisions']))
			$this->database_optimization_database_optimizationoptions['database_optimizationrevisions']     = 'N';
			
		if(!isset($this->database_optimization_database_optimizationoptions['last_run']))
			$this->database_optimization_database_optimizationoptions['last_run']         = '';
		// v4.5.1
		if(!isset($this->database_optimization_database_optimizationoptions['last_run_seconds']))
			$this->database_optimization_database_optimizationoptions['last_run_seconds'] = '';			
		if(!isset($this->database_optimization_database_optimizationoptions['logging_on']))
			$this->database_optimization_database_optimizationoptions['logging_on']       = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['nr_of_revisions']))
			$this->database_optimization_database_optimizationoptions['nr_of_revisions']  = '';
		// v4.1
		if(!isset($this->database_optimization_database_optimizationoptions['older_than']))
			$this->database_optimization_database_optimizationoptions['older_than']       = '';
		if(!isset($this->database_optimization_database_optimizationoptions['optimize_inndatabase_optimization']))
			$this->database_optimization_database_optimizationoptions['optimize_inndatabase_optimization']  = 'N';
		if(!isset($this->database_optimization_database_optimizationoptions['schedule_type']))
			$this->database_optimization_database_optimizationoptions['schedule_type']    = '';
		if(!isset($this->database_optimization_database_optimizationoptions['schedule_hour']))
			$this->database_optimization_database_optimizationoptions['schedule_hour']    = '';
		if(!isset($this->database_optimization_database_optimizationoptions['total_savings']))
			$this->database_optimization_database_optimizationoptions['total_savings']    = (int)0;
		if(!isset($this->database_optimization_database_optimizationoptions['version']))
			$this->database_optimization_database_optimizationoptions['version']          = $this->database_optimization_version;
			
		// CUSTOM POST TYPES (from v4.4)
		if(!isset($this->database_optimization_database_optimizationoptions['post_types'])) {
			$this->database_optimization_database_optimizationoptions['post_types'] = array();
			$relevant_pts = $this->database_optimization_utilities_obj->database_optimization_get_relevant_post_types();
			// (CUSTOM) POST TYPES ARE PER DEFAULT ENABLED		
			foreach($relevant_pts as $posttype) {
				$this->database_optimization_database_optimizationoptions['post_types'][$posttype] = "Y";
			} // foreach($relevant_pts as $posttype)
			
			if (isset($this->database_optimization_database_optimizationoptions['rev_post_type'])) {
				// UPGRADE FROM A VERSION < 4.4
				if ($this->database_optimization_database_optimizationoptions['rev_post_type'] == 'page') {
					// PAGES ONLY: DISABLE 'post'
					$this->database_optimization_database_optimizationoptions['post_types']['post'] = "N";
				} else if ($this->database_optimization_database_optimizationoptions['rev_post_type'] == 'post') {
					// POSTS ONLY: DISABLE 'page'
					$this->database_optimization_database_optimizationoptions['post_types']['page'] = "N";
				}
				unset($this->database_optimization_database_optimizationoptions['rev_post_type']);
			} // if (isset($this->database_optimization_database_optimizationoptions['rev_post_type']))
		} // if(!isset($this->database_optimization_database_optimizationoptions['post_types']))

		// UPDATE OPTIONS
		$this->database_optimization_multisite_obj->database_optimization_ms_update_option('database_optimization_database_optimizationoptions', $this->database_optimization_database_optimizationoptions);
		
		// UPDATE SCHEDULER (IF NEEDED)
		$this->database_optimization_scheduler_obj->database_optimization_update_scheduler();
	} // database_optimization_load_options()


	/*******************************************************************************
	 * 	COPY AND DELETE OPTIONS FROM PREVIOUS VERSIONS (BEFORE 4.0)
	 *******************************************************************************/
	function database_optimization_convert_options() {
		global $wpdb;
		
		// STOP OLD SCHEDULER
		wp_clear_scheduled_hook('database_optimizationoptimize_database');
		
		$setting = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationdatabase_optimization_total_savings');
		if($setting) {
			$this->database_optimization_database_optimizationoptions['total_savings'] = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationdatabase_optimization_total_savings');
			$this->database_optimization_multisite_obj->database_optimization_ms_delete_option('database_optimizationdatabase_optimization_total_savings');
		}
		
		$setting = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_pingbacks');
		if($setting) {
			$this->database_optimization_database_optimizationoptions['clear_pingbacks'] = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_pingbacks');
			$this->database_optimization_multisite_obj->database_optimization_ms_delete_option('database_optimizationclear_pingbacks');
		}
		
		$setting = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_spam');
		if($setting) {
			$this->database_optimization_database_optimizationoptions['clear_spam'] = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_spam');
			$this->database_optimization_multisite_obj->database_optimization_ms_delete_option('database_optimizationclear_spam');
		}
		
		$setting = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_tags');
		if($setting) {
			$this->database_optimization_database_optimizationoptions['clear_tags'] = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_tags');
			$this->database_optimization_multisite_obj->database_optimization_ms_delete_option('database_optimizationclear_tags');
		}
		
		$setting = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_transients');
		if($setting) {
			$this->database_optimization_database_optimizationoptions['clear_transients'] = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_transients');
			$this->database_optimization_multisite_obj->database_optimization_ms_delete_option('database_optimizationclear_transients');					
		}

		$setting = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_trash');
		if($setting)
		{	$this->database_optimization_database_optimizationoptions['clear_trash'] = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationclear_trash');
			$this->database_optimization_multisite_obj->database_optimization_ms_delete_option('database_optimizationclear_trash');
		}
	
		$setting = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationdatabase_optimization_adminbar');
		if($setting) {
			$this->database_optimization_database_optimizationoptions['adminbar'] = $this->database_optimization_multisite_obj->database_optimization_ms_get_option('database_optimizationdatabase_optimization_adminbar');
			$this->database_optimization_multisite_obj->database_optimization_ms_delete_option('database_optimizationdatabase_optimization_adminbar');	
		}


		// COPY EXCLUDED TABLES
		for($i=0; $i<count($this->database_optimization_ms_prefixes); $i++) {
			$sql = "
			  SELECT `option_name`
				FROM ".$this->database_optimization_ms_prefixes[$i]."options
			   WHERE `option_name` LIKE 'database_optimizationex_%'		
			";

			$res = $wpdb->get_results($sql, ARRAY_A);
			for($j=0; $j<count($res); $j++) {
				$option_name = $res[$j]['option_name'];
				$option_name_new = substr($option_name, 7);
				$this->database_optimization_database_optimizationexcluded_tabs[$option_name_new] = 'Y';
				$this->database_optimization_multisite_obj->database_optimization_ms_delete_option($option_name);
			} // for($j=0; $j<count($res); $j++)
		} // for($i=0; $i<count($this->database_optimization_ms_prefixes); $i++)
		
		// UPDATE EXCLUDED TABLES
		$this->database_optimization_multisite_obj->database_optimization_ms_update_option('database_optimization_database_optimizationexcluded_tabs', $this->database_optimization_database_optimizationexcluded_tabs);		
	} // database_optimization_convert_options()
	

	/*******************************************************************************
	 * 	INITIALIZE WORDPRESS HOOKS
	 *******************************************************************************/		
	function database_optimization_init_hooks() {
		global $blog_id;
		
		// ON DE-ACTIVATION
		register_deactivation_hook(__FILE__, array('OptimizeDatabase', 'database_optimization_deactivation_handler'));
		
		// ON UN-INSTALLATION
		register_uninstall_hook(__FILE__, array('OptimizeDatabase', 'database_optimization_uninstallation_handler'));
				
		// ADD ENTRY TO ADMIN TOOLS MENU
		if (is_multisite()) {
			if ($blog_id == 1) {
				// v4.1: PLUGIN ONLY CAN BE USED ON THE MAIN SITE (NOT ON THE SUB SITES)
				add_action('admin_menu', array(&$this, 'database_optimization_admin_tools'));
				add_action('admin_menu', array(&$this, 'database_optimization_admin_settings'));
				// ADD 'SETTINGS' LINK TO THE MAIN PLUGIN PAGE
				add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'database_optimization_settings_link'));				
			} // if ($blog_id == 1)
		} else {
			add_action('admin_menu', array(&$this, 'database_optimization_admin_tools'));
			add_action('admin_menu', array(&$this, 'database_optimization_admin_settings'));
			// ADD 'SETTINGS' LINK TO THE MAIN PLUGIN PAGE
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'database_optimization_settings_link'));				
		} // if (is_multisite())
		
		// ICON MODE: ADD ICON TO ADMIN MENU
		if ($this->database_optimization_database_optimizationoptions['adminmenu'] == "Y") {
			add_action('admin_menu', array(&$this, 'database_optimization_admin_icon'));
			add_action('admin_menu', array(&$this, 'database_optimization_register_options'));
		}

		// ADD THE '1 CLICK OPTIMIZE DATABASE' ITEM TO THE ADMIN BAR (IF ACTIVATED)
		if($this->database_optimization_database_optimizationoptions['adminbar'] == 'Y')
			add_action('wp_before_admin_bar_render', array(&$this, 'database_optimization_admin_bar'));
		
		// INITIALIZE LOCALIZATION
		add_action('admin_menu', array(&$this, 'database_optimization_i18n'));	
	} // database_optimization_init_hooks()


	
	function database_optimization_admin_tools() {
		if (function_exists('add_management_page')) {
			add_management_page(
				__('Optimize Database',$this->database_optimization_txt_domain),	// page title
				__('Optimize Database',$this->database_optimization_txt_domain),	// menu title
				'manage_options',								// capability
				'optimize-database',						// menu slug
				array(&$this, 'database_optimization_start_manually'));			// function
		} // if (function_exists('add_management_page'))
	} // database_optimization_admin_tools()
	
	

	function database_optimization_admin_settings() {
		if (function_exists('add_options_page'))
			add_options_page(
				__('Optimize Database', $this->database_optimization_txt_domain),	// page title
				__('Optimize Database', $this->database_optimization_txt_domain),	// menu title
				'manage_options',								// capability
				'database_optimization_settings_page',							// menu slug
				array(&$this, 'database_optimization_settings_page')				// function
			);
	} // database_optimization_admin_settings()
	
	

	function database_optimization_settings_link($links) {
		array_unshift($links, '<a href="options-general.php?page=database_optimization_settings_page">'.__('Settings', $this->database_optimization_txt_domain).'</a>');
		return $links;
	} // database_optimization_settings_link()
	
	
	
	function database_optimization_admin_bar() {
		global $wp_admin_bar;
	
		if (!is_super_admin() || !is_admin_bar_showing()) return;
		
		$siteurl = site_url('/');
		$wp_admin_bar->add_menu(
			array(
			   'id'    => 'optimize',
			   'title' => __('Optimize DB (1 click)', $this->database_optimization_txt_domain),
			   'href'  => $siteurl.'wp-admin/tools.php?page=optimize-database&action=run' ));
	} // database_optimization_admin_bar()	


	
	function database_optimization_admin_icon() {
		if (function_exists('add_menu_page')) {
			add_menu_page(
				__('Optimize Database', $this->database_optimization_txt_domain),			// page title
				__('Optimize Database', $this->database_optimization_txt_domain), 		// menu title
				'administrator',										// capability
				'optimize-database',								// menu slug
				array(&$this, 'database_optimization_start_manually'),					// function
				$this->database_optimization_plugin_url.'images/icon.png'					// icon url
			);
		}
	} // database_optimization_admin_icon()


//'ICON MODE': REGISTER OPTION PAGE BUT HIDE IT FROM THE ADMIN MENU
	 
	function database_optimization_register_options() {
		if (function_exists('add_submenu_page')) {
			add_submenu_page(
				null,											// parent slug (NULL is hide from menu)
				__('Optimize Database', $this->database_optimization_txt_domain),	// page title
				__('Optimize Database', $this->database_optimization_txt_domain),	// menu title
				'manage_options',								// capability
				'database_optimizationdatabase_optimization_admin',								// menu slug
				array(&$this, 'database_optimization_settings_page')				// function
			);
		}
	} // database_optimization_register_options()


	
	function database_optimization_is_relevant_page() {
		$this_page = '';
		if(isset($_GET['page'])) $this_page = $_GET['page'];
		return ($this_page == 'database_optimization_settings_page' || $this_page == 'optimize-database');
	} // database_optimization_is_relevant_page()



	public static function database_optimization_deactivation_handler() {
		// STOP SCHEDULER
		wp_clear_scheduled_hook('database_optimization_scheduler');
	} // database_optimization_deactivation_handler()


	
	public static function database_optimization_uninstallation_handler() {
		// STOP SCHEDULER
		wp_clear_scheduled_hook('database_optimization_scheduler');
	} // database_optimization_uninstallation_handler()



	function database_optimization_settings_page() {
		global $wpdb, $database_optimization_database_optimizationexcluded_tabs, $database_optimization_ms_prefixes;
	
		include_once(trailingslashit(dirname(__FILE__)).'/includes/settings-page.php');
	} // database_optimization_settings_page()
	

	
	function database_optimization_start_manually() {	
		$this->database_optimization_start(false);
	} // database_optimization_start_manually()



	function database_optimization_start_scheduler() {
		$this->database_optimization_start(true);
	} // database_optimization_start_scheduler()
		


	function database_optimization_start($scheduler) {	
		// PAGE LOAD TIMER
		$time  = microtime();
		$time  = explode(' ', $time);
		$time  = $time[1] + $time[0];
		$this->database_optimization_start_time = $time;
			
		$action = '';
		if(isset($_REQUEST['action'])) {
			$action = $_REQUEST['action'];
			if($action == "delete_log") {
				// DELETE LOG FILE
				@unlink($this->database_optimization_plugin_path.'logs/optimize-db-log.html');
				
				// UPDATED MESSAGE
				// v4.1.10
				echo "<script>jQuery('#database_optimization-running').hide();</script>";
				echo "<div class='updated database_optimization-bold'><p>".
					__('Optimize Database LOG FILE HAS BEEN DELETED', $this->database_optimization_txt_domain);
				echo "</p></div>";			
			} // if($action == "delete_log")
		} // if(isset($_REQUEST['action']))
		
		if(!$scheduler) {
			// SHOW PAGE HEADER
			$this->database_optimization_displayer_obj->display_header();
			// v4.1.9: STARTING: SHOW RUNNING INDICATOR
			echo "<script>jQuery('#database_optimization-running').show();</script>";			
			// SHOW CURRENT SETTINGS
			$this->database_optimization_displayer_obj->display_current_settings();	
		} // if(!$scheduler)
				
		if ($action != 'run' && !$scheduler) {
		
			$this->database_optimization_displayer_obj->display_start_buttons($action);
		} else {
		
			$this->database_optimization_displayer_obj->display_start_buttons($action);
			 
			 // REGISTER THE LAST RUN
			$this->database_optimization_database_optimizationoptions['last_run'] = current_time('M j, Y @ H:i', 0);
			// DELETE REDUNDANT DATA
			$this->database_optimization_cleaner_obj->database_optimization_run_cleaner($scheduler);
			// OPTIMIZE DATABASE TABLES
			$this->database_optimization_cleaner_obj->database_optimization_run_optimizer($scheduler);
			// SHOW RESULTS
			$this->database_optimization_cleaner_obj->database_optimization_savings($scheduler);
			// SHOW 'DONE' PARAGRAPH
			if(!$scheduler) $this->database_optimization_cleaner_obj->database_optimization_done();
			
			$this->database_optimization_database_optimizationoptions['last_run_seconds'] = $this->database_optimization_last_run_seconds;
			
			$this->database_optimization_multisite_obj->database_optimization_ms_update_option('database_optimization_database_optimizationoptions', $this->database_optimization_database_optimizationoptions);
		}  // if($action != 'run')
		// v4.1.10: DONE: HIDE RUNNING INDICATOR
		echo "<script>jQuery('#database_optimization-running').hide();</script>";
	} // database_optimization_start()
} // OptimizeDatabase
?>