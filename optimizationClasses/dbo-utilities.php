<?php
class database_optimization_Utilities {
	
    function __construct() {
	} // __construct()


	function database_optimization_get_relevant_post_types() {
		$relevant_pts = array();
		$posttypes    = get_post_types();
		foreach ($posttypes as $posttype) {
			// SKIP THE DEFAULT POST TYPES (EXCEPT FOR 'post' AND 'page')
			if ($posttype != 'attachment' &&
					$posttype != 'revision' &&
					$posttype != 'nav_menu_item' &&
					$posttype != 'custom_css' &&
					$posttype != 'customize_changeset' &&
					
					$posttype != 'oembed_cache') {
				array_push($relevant_pts, $posttype);
			}
		} // foreach ($posttypes as $posttype)
		
		return $relevant_pts;
	} // database_optimization_get_relevant_post_types()


	
	function database_optimization_format_size($size, $precision=1) {
		if($size > 1024*1024) return (round($size/(1024*1024),$precision)).' MB';
		
		return (round($size/1024,$precision)).' KB';
	} // database_optimization_format_size()
	

	
	function database_optimization_get_db_size() {
		global $wpdb;
	
		$sql = sprintf("
		  SELECT SUM(data_length + index_length) AS size
			FROM information_schema.TABLES
		   WHERE table_schema = '%s'
		GROUP BY table_schema
		", DB_NAME);	
		
		$res = $wpdb->get_results($sql);
		
		return $res[0]->size;
	} // database_optimization_get_db_size()

	
	
	function database_optimization_get_tables() {
		global $wpdb;

		$sql = sprintf("
         SHOW FULL TABLES
		 FROM `%s`
		WHERE table_type = 'BASE TABLE'		
		", DB_NAME);		
		
		// GET THE DATABASE BASE TABLES
		return $wpdb->get_results($sql, ARRAY_N);
	} // database_optimization_get_tables()
} // database_optimization_Utilities