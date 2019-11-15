<?php
class database_optimization_Cleaner {
	var $start_size;
	var $nr_of_optimized_tables;


	
	//	CONSTRUCTOR

    function __construct() {
	} // __construct()


		//RUN CLEANER
	 
	function database_optimization_run_cleaner($scheduler) {
		global $database_optimization_class;

		if(!$scheduler) {
			echo '
	  <div id="database_optimization-cleaner" class="database_optimization-padding-left">
		<div class="database_optimization-title-bar">
		  <h2>'.__('Cleaning Database', $database_optimization_class->database_optimization_txt_domain).'</h2>
		</div>
		<br>
		<br>
			';
		} // if(!$scheduler)
		
		// GET THE SIZE OF THE DATABASE BEFORE OPTIMIZATION
		$this->start_size = $database_optimization_class->database_optimization_utilities_obj->database_optimization_get_db_size();
	
		
	
		
	
		//DELETE TRASHED ITEMS
		 
		if($database_optimization_class->database_optimization_options['clear_trash'] == 'Y') {
			// GET TRASHED POSTS / PAGES AND COMMENTS
			$results = $this->database_optimization_get_trash();
	
			$total_deleted = 0;		
			if(count($results)>0) {
				// WE HAVE TRASH TO DELETE!
				if(!$scheduler) {
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="database_optimization-result-table">
	  <tr>
		<td colspan="4"><div class="database_optimization-found">
			<?php _e('DELETED TRASHED ITEMS', $database_optimization_class->database_optimization_txt_domain);?>
		  </div></td>
	  </tr>
	  <tr>
		<th align="right" class="database_optimization-border-bottom">#</th>
		<th align="left" class="database_optimization-border-bottom"><?php _e('prefix', $database_optimization_class->database_optimization_txt_domain);?></th>
		<th align="left" class="database_optimization-border-bottom"><?php _e('type', $database_optimization_class->database_optimization_txt_domain);?></th>
		<th align="left" class="database_optimization-border-bottom"><?php _e('IP address / title', $database_optimization_class->database_optimization_txt_domain);?></th>
		<th align="left" nowrap="nowrap" class="database_optimization-border-bottom"><?php _e('date', $database_optimization_class->database_optimization_txt_domain);?></th>
	  </tr>
	  <?php
				} // if(!$scheduler)
	  
				// LOOP THROUGH THE TRASHED ITEMS AND DELETE THEM
				$total_deleted = $this->database_optimization_delete_trash($results, $scheduler);
				
				if(!$scheduler) {
	?>
	  <tr>
		<td colspan="4" align="right" class="database_optimization-border-top database_optimization-bold"><?php _e('total number of trashed items deleted', $database_optimization_class->database_optimization_txt_domain);?></td>
		<td align="right" class="database_optimization-border-top database_optimization-bold"><?php echo $total_deleted?></td>
	  </tr>
	</table>
	<?php
				} // if(!$scheduler)
			} else {
				if(!$scheduler) {
	?>
	<div class="database_optimization-not-found">
	  <?php _e('No TRASHED ITEMS found to delete', $database_optimization_class->database_optimization_txt_domain);?>
	</div>
	<?php
				} // if(!$scheduler)
			} // if(count($results)>0)
			
			// NUMBER OF DELETED TRASH FOR LOG FILE
			$database_optimization_class->log_arr["trash"] = $total_deleted;
		} // if($database_optimization_class->database_optimization_options['clear_trash'] == 'Y')
	
	//	DELETE SPAMMED ITEMS
		
		if($database_optimization_class->database_optimization_options['clear_spam'] == 'Y') {
			// GET SPAMMED COMMENTS
			$results = $this->database_optimization_get_spam();
	
			$total_deleted = 0;		
			if(count($results)>0) {
				// WE HAVE SPAM TO DELETE!
				if (!$scheduler) {
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="database_optimization-result-table">
	  <tr>
		<td colspan="4"><div class="database_optimization-found">
			<?php _e('DELETED SPAMMED ITEMS', $database_optimization_class->database_optimization_txt_domain);?>
		  </div></td>
	  </tr>
	  <tr>
		<th align="right" class="database_optimization-border-bottom">#</th>
		<th align="left" class="database_optimization-border-bottom"><?php _e('prefix', $database_optimization_class->database_optimization_txt_domain);?></th>
		<th align="left" class="database_optimization-border-bottom"><?php _e('comment author', $database_optimization_class->database_optimization_txt_domain);?></th>
		<th align="left" class="database_optimization-border-bottom"><?php _e('comment author email', $database_optimization_class->database_optimization_txt_domain);?></th>
		<th align="left" nowrap="nowrap" class="database_optimization-border-bottom"><?php _e('comment date', $database_optimization_class->database_optimization_txt_domain);?></th>
	  </tr>
	  <?php
				} // if (!$scheduler)
	  
				// LOOP THROUGH SPAMMED ITEMS AND DELETE THEM
				$total_deleted = $this->database_optimization_delete_spam($results, $scheduler);
				
				if (!$scheduler) {
	?>
	  <tr>
		<td colspan="4" align="right" class="database_optimization-border-top database_optimization-bold"><?php _e('total number of spammed items deleted', $database_optimization_class->database_optimization_txt_domain);?></td>
		<td align="right" class="database_optimization-border-top database_optimization-bold"><?php echo $total_deleted?></td>
	  </tr>
	</table>
	<?php
				} // if (!$scheduler)
			} else{
				if (!$scheduler) {
	?>
	<div class="database_optimization-not-found">
	  <?php _e('No SPAMMED ITEMS found to delete', $database_optimization_class->database_optimization_txt_domain);?>
	</div>
	<?php
				} // if (!$scheduler)
			} // if(count($results)>0)

			// NUMBER OF SPAM DELETED FOR LOG FILE
			$database_optimization_class->log_arr["spam"] = $total_deleted;			
		} // if($database_optimization_class->database_optimization_options['clear_spam'] == 'Y')
	
	
		//	DELETE UNUSED TAGS
		
		if($database_optimization_class->database_optimization_options['clear_tags'] == 'Y') {
			// DELETE UNUSED TAGS
			$total_deleted = $this->database_optimization_delete_tags();
			if($total_deleted > 0) {
				// TAGS DELETED
				if (!$scheduler) {
	?>
	<div class="database_optimization-found-number">
	  <?php _e('NUMBER OF UNUSED TAGS DELETED', $database_optimization_class->database_optimization_txt_domain);?>: <span class="database_optimization-blue"><?php echo $total_deleted;?></span> </div>
	<?php
				} // if (!$scheduler)
			} else {
				if (!$scheduler) {
	?>
	<div class="database_optimization-not-found">
	  <?php _e('No UNUSED TAGS found to delete', $database_optimization_class->database_optimization_txt_domain);?>
	</div>
	<?php
				} // if (!$scheduler)
			} // if(count($results)>0)
			
			// NUMBER OF tags DELETED FOR LOG FILE
			$database_optimization_class->log_arr["tags"] = $total_deleted;
		} // if($database_optimization_class->database_optimization_options['clear_tags'] == 'Y')
	
	
		
	
	
		/****************************************************************************************
		 *	DELETE PINGBACKS AND TRACKBACKS
		 ****************************************************************************************/	
		if($database_optimization_class->database_optimization_options['clear_pingbacks'] == 'Y') {
			// DELETE UNUSED TAGS
			$total_deleted = $this->database_optimization_delete_pingbacks();
			if($total_deleted > 0) {
				// PINGBACKS / TRACKBACKS DELETED\
				if (!$scheduler) {
	?>
	<div class="database_optimization-found-number">
	  <?php _e('NUMBER OF PINGBACKS AND TRACKBACKS DELETED', $database_optimization_class->database_optimization_txt_domain);?>: <span class="database_optimization-blue"><?php echo $total_deleted;?></span> </div>
	<?php
				} // if (!$scheduler)
			} else {
				if (!$scheduler) {
	?>
	<div class="database_optimization-not-found">
	  <?php _e('No PINGBACKS nor TRACKBACKS found to delete', $database_optimization_class->database_optimization_txt_domain);?>
	</div>
	<?php
				} // if (!$scheduler)
			} // if(count($results)>0)
		
			// NUMBER OF pingbacks / trackbacks DELETED (FOR LOG FILE)
			$database_optimization_class->log_arr["pingbacks"] = $total_deleted;	
		} // if($database_optimization_class->database_optimization_options['clear_pingbacks'] == 'Y')

	

	/********************************************************************************************
	 *	RUN OPTIMIZER
	 ********************************************************************************************/	
	function database_optimization_run_optimizer($scheduler) {
		global $database_optimization_class;
	
		if(!$scheduler) {
?>
	<div class="database_optimization-optimizing-table" class="database_optimization-padding-left">
	  <div class="database_optimization-title-bar">
		<h2><?php _e('Optimizing Database Tables', $database_optimization_class->database_optimization_txt_domain);?></h2>
	  </div>
	  <br>
	  <br>
	  <table border="0" cellspacing="8" cellpadding="2">
		<tr>
		  <th class="database_optimization-border-bottom" align="right">#</th>
		  <th class="database_optimization-border-bottom" align="left"><?php _e('table name', $database_optimization_class->database_optimization_txt_domain);?></th>
		  <th class="database_optimization-border-bottom" align="left"><?php _e('optimization result', $database_optimization_class->database_optimization_txt_domain);?></th>
		  <th class="database_optimization-border-bottom" align="left"><?php _e('engine', $database_optimization_class->database_optimization_txt_domain);?></th>
		  <th class="database_optimization-border-bottom" align="right"><?php _e('table rows', $database_optimization_class->database_optimization_txt_domain);?></th>
		  <th class="database_optimization-border-bottom" align="right"><?php _e('table size', $database_optimization_class->database_optimization_txt_domain);?></th>
		</tr>
		<?php
		} // if(!$scheduler)
		
		# OPTIMIZE THE DATABASE TABLES
		$this->nr_of_optimized_tables = $this->database_optimization_optimize_tables($scheduler);
		
		if(!$scheduler) {
	?>
	  </table>
	</div><!-- /database_optimization-optimizing-table -->	
<?php
		} // if(!$scheduler)
	} // database_optimization_run_optimizer()


	/********************************************************************************************
	 *	CALCULATE AND DISPLAY SAVINGS
	 ********************************************************************************************/		
	function database_optimization_savings($scheduler) {
		global $database_optimization_class;
		global $database_optimization_logger_obj;

		// NUMBER OF TABLES
		$database_optimization_class->log_arr["tables"] = $this->nr_of_optimized_tables;
		// DATABASE SIZE BEFORE OPTIMIZATION
		$database_optimization_class->log_arr["before"] = $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size($this->start_size,3);
		// DATABASE SIZE AFTER OPTIMIZATION
		$end_size = $database_optimization_class->database_optimization_utilities_obj->database_optimization_get_db_size();
		$database_optimization_class->log_arr["after"] = $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size($end_size,3);
		// TOTAL SAVING
		$database_optimization_class->log_arr["savings"] = $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size(($this->start_size - $end_size),3);
		// WRITE RESULTS TO LOG FILE
		$database_optimization_class->database_optimization_logger_obj->write_log($database_optimization_class->log_arr);
	
		$total_savings = $database_optimization_class->database_optimization_options['total_savings'];
		$total_savings += ($this->start_size - $end_size);
		$database_optimization_class->database_optimization_options['total_savings'] = $total_savings;		
		
		$database_optimization_class->database_optimization_multisite_obj->database_optimization_ms_update_option('database_optimization_options', $database_optimization_class->database_optimization_options);
		
		if(!$scheduler) {	
	?>
    <div id="database_optimization-savings" class="database_optimization-padding-left">
	  <div class="database_optimization-title-bar">
		<h2><?php _e('Savings', $database_optimization_class->database_optimization_txt_domain);?></h2>
	  </div>
	  <br>
	  <br>
	  <table border="0" cellspacing="8" cellpadding="2">
		<tr>
		  <th>&nbsp;</th>
		  <th class="database_optimization-border-bottom"><?php _e('size of the database', $database_optimization_class->database_optimization_txt_domain);?></th>
		</tr>
		<tr>
		  <td align="right"><?php _e('BEFORE optimization', $database_optimization_class->database_optimization_txt_domain);?></td>
		  <td align="right" class="database_optimization-bold"><?php echo $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size($this->start_size,3); ?></td>
		</tr>
		<tr>
		  <td align="right"><?php _e('AFTER optimization', $database_optimization_class->database_optimization_txt_domain);?></td>
		  <td align="right" class="database_optimization-bold"><?php echo $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size($end_size,3); ?></td>
		</tr>
		<tr>
		  <td align="right" class="database_optimization-bold"><?php _e('SAVINGS THIS TIME', $database_optimization_class->database_optimization_txt_domain);?></td>
		  <td align="right" class="database_optimization-border-top database_optimization-bold"><?php echo $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size(($this->start_size - $end_size),3); ?></td>
		</tr>
		<tr>
		  <td align="right" class="database_optimization-bold"><?php _e('TOTAL SAVINGS SINCE THE FIRST RUN', $database_optimization_class->database_optimization_txt_domain);?></td>
		  <td align="right" class="database_optimization-border-top database_optimization-bold"><?php echo $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size($total_savings,3); ?></td>
		</tr>
	  </table>      
    </div><!-- /database_optimization-savings -->
<?php
		} // if(!$scheduler)
	} // database_optimization_savings()
	

	/********************************************************************************************
	 *	SHOW LOADING TIME
	 ********************************************************************************************/	
	function database_optimization_done() {
		global $database_optimization_class;
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		
		$total_time = round(($finish - $database_optimization_class->database_optimization_start_time), 4);
		?>
      <div id="database_optimization-done" class="database_optimization-padding-left">
		<div class="database_optimization-title-bar">
		  <h2>
			<?php _e('DONE!', $database_optimization_class->database_optimization_txt_domain);?>
		  </h2>
		</div>
		<br />
		<br />
		<span class="database_optimization-padding-left"><?php _e('Optimization took', $database_optimization_class->database_optimization_txt_domain)?>&nbsp;<strong><?php echo $total_time;?></strong>&nbsp;<?php _e('seconds', $database_optimization_class->database_optimization_txt_domain)?>.</span>
		<?php
		
		$database_optimization_class->database_optimization_last_run_seconds = $total_time;
		if(file_exists($database_optimization_class->database_optimization_plugin_path.'logs/optimize-db-log.html'))
		{
		?>
		<br />
		<br />
		&nbsp;
		<input class="button database_optimization-normal" type="button" name="view_log" value="<?php _e('View Log File', $database_optimization_class->database_optimization_txt_domain);?>" onclick="window.open('<?php echo $database_optimization_class->database_optimization_logfile_url?>')" />
		&nbsp;
		<input class="button database_optimization-normal" type="button" name="delete_log" value="<?php _e('Delete Log File', $database_optimization_class->database_optimization_txt_domain);?>" onclick="self.location='tools.php?page=optimize-database&action=delete_log'" />
		<?php	
		}
?>
      </div><!-- /database_optimization-done -->		
<?php	
	} // database_optimization_done()


	/********************************************************************************************
	 *	GET REVISIONS (OLDER THAN x DAYS)
	 ********************************************************************************************/
	function database_optimization_get_revisions_older_than() {
		global $database_optimization_class, $wpdb;
		
		$res_arr = array();
		
		// CUSTOM POST TYPES
		$rel_posttypes = $database_optimization_class->database_optimization_options['post_types'];
		$in = '';
		foreach ($rel_posttypes as $posttype => $value) {
			if ($value == 'Y') {
				if ($in != '') $in .= ',';
				$in .= "'" . $posttype . "'";
			} // if ($value == 'Y')
		} // foreach($rel_posttypes as $posttypes)
		
		$where = '';
		if($in != '') {
			$where = " AND p2.`post_type` IN ($in)";
		} else {
			// NO POST TYPES TO DELETE REVISIONS FOR... SKIP!
			return $res_arr;			
		} // if($in != '')

		$older_than = $database_optimization_class->database_optimization_options['older_than'];

		$index = 0;

		// LOOP THROUGH THE SITES (IF MULTI SITE)
		for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++) {
			
			$sql = sprintf("
			  SELECT p1.`ID`, p1.`post_parent`, p1.`post_title`, p1.`post_modified`
				FROM %sposts p1, %sposts p2
			   WHERE p1.`post_type`   = 'revision'
                 AND p1.`post_parent` = p2.ID			   
			         %s
				 AND p1.`post_modified` < date_sub(now(), INTERVAL %d DAY)
			ORDER BY UCASE(p1.`post_title`)	
			",
			$database_optimization_class->database_optimization_ms_prefixes[$i],
			$database_optimization_class->database_optimization_ms_prefixes[$i],
			$where,
			$older_than);
			
			//echo 'OLDER: '.$sql.'<br>';
	
			$res = $wpdb->get_results($sql, ARRAY_A);
			
			for($j=0; $j<count($res); $j++) {
				if(isset($res[$j]) && !$this->database_optimization_post_is_excluded($res[$j]['post_parent'])) {
					$res_arr[$index] = $res[$j];
					$res_arr[$index]['site'] = $database_optimization_class->database_optimization_ms_prefixes[$i];				
					$index++;
				} // if(isset($res[$j]) && !$this->database_optimization_post_is_excluded($res[$j]['post_parent']))
			} // for($j=0; $j<count($res); $j++)
			
		} // for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++)
		
		return $res_arr;
	} // database_optimization_get_revisions_older_than()


	/********************************************************************************************
	 *	GET REVISIONS (KEEP MAX NUMBER OF REVISIONS)
	 ********************************************************************************************/	
	function database_optimization_get_revisions_keep_revisions() {
		global $database_optimization_class, $wpdb;
		
		$res_arr = array();
		
		// CUSTOM POST TYPES (from v4.4)
		$rel_posttypes = $database_optimization_class->database_optimization_options['post_types'];
		$in = '';
		foreach ($rel_posttypes as $posttype => $value) {
			if ($value == 'Y') {
				if ($in != '') $in .= ',';
				$in .= "'" . $posttype . "'";
			} // if ($value == 'Y')
		} // foreach($rel_posttypes as $posttypes)
		
		$where1 = '';
		if($in != '') {
			$where1 = " AND p2.`post_type` IN ($in)";
		} else {
			// NO POST TYPES TO DELETE REVISIONS FOR... SKIP!
			return $res_arr;
		} // if($in != '')
				
		// MAX NUMBER OF REVISIONS TO KEEP
		$max_revisions = $database_optimization_class->database_optimization_options['nr_of_revisions'];
		
		$index = 0;

		// SKIP REVISIONS THAT WILL BE DELETED BY THE 'OLDER THAN' OPTION
		$where2 = '';
		if($database_optimization_class->database_optimization_options['delete_older'] == 'Y') {
			$older_than = $database_optimization_class->database_optimization_options['older_than'];
			$where2 = 'AND p1.`post_modified` >= date_sub(now(), INTERVAL '.$older_than.' DAY)';
		}
		
		for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++) {
			
			$sql = sprintf ("
			  SELECT p1.`ID`, p1.`post_parent`, p1.`post_title`, COUNT(*) cnt
				FROM %sposts p1, %sposts p2
			   WHERE p1.`post_type` = 'revision'
                 AND p1.`post_parent` = p2.ID			   
                     %s
					 %s
			GROUP BY p1.`post_parent`
			  HAVING COUNT(*) > %d
			ORDER BY UCASE(p1.`post_title`)	
			",
			$database_optimization_class->database_optimization_ms_prefixes[$i],
			$database_optimization_class->database_optimization_ms_prefixes[$i],
			$where1,
			$where2,
			$max_revisions);
			
			//echo 'KEEP: '.$sql.'<br>';
			
			$res = $wpdb->get_results($sql, ARRAY_A);
			for($j=0; $j<count($res); $j++) {
				if(isset($res[$j]) && !$this->database_optimization_post_is_excluded($res[$j]['post_parent'])) {
					$res_arr[$index] = $res[$j];
					$res_arr[$index]['site'] = $database_optimization_class->database_optimization_ms_prefixes[$i];				
					$index++;
				}
			} // for($j=0; $j<count($res); $j++)
		} // for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++)
		
		return $res_arr;	
	} // database_optimization_get_revisions_keep_revisions()


	/********************************************************************************************
	 *	DELETE THE REVISIONS
	 ********************************************************************************************/
	function database_optimization_delete_revisions($scheduler) {
		global $database_optimization_class, $wpdb;

		$total_deleted = 0;
		$nr = 1;

		if($database_optimization_class->database_optimization_options['delete_older'] == 'Y') {
			// DELETE REVISIONS OLDER THAN x DAYS
			$results    = $this->database_optimization_get_revisions_older_than();
			$older_than = $database_optimization_class->database_optimization_options['older_than'];
			$total_deleted += count($results);
			
			for($i=0; $i<count($results); $i++) {
				if (!$scheduler) {
			?>
		<tr>
		  <td align="right" valign="top"><?php echo $nr?>.</td>
		  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
		  <td valign="top" class="database_optimization-bold"><?php echo $results[$i]['post_title']?></td>
		  <td valign="top" class="database_optimization-bold"><?php echo $results[$i]['post_modified']?></td><?php
				} // if (!$scheduler)

				$sql_delete = sprintf ("
				DELETE FROM %sposts
				 WHERE `ID` = %d
				", $results[$i]['site'], $results[$i]['ID']);
				
				$wpdb->get_results($sql_delete);
				
				$nr++;
				if(!$scheduler) {
		?>
		  <td align="right" valign="top" class="database_optimization-bold">1</td>
		</tr>
		<?php
				} // if(!$scheduler)				
			} // for($i=0; $i<count($results); $i++)			
		} // if($database_optimization_class->database_optimization_options['delete_older'] == 'Y')
		
		if($database_optimization_class->database_optimization_options['revisions'] == 'Y') {
			// KEEP MAX NUMBER OF REVISIONS
			$results       = $this->database_optimization_get_revisions_keep_revisions();
			$max_revisions = $database_optimization_class->database_optimization_options['nr_of_revisions'];
			
			for($i=0; $i<count($results); $i++) {
				$nr_to_delete  = $results[$i]['cnt'] - $max_revisions;
				$total_deleted += $nr_to_delete;
					
				if (!$scheduler) {
			?>
		<tr>
		  <td align="right" valign="top"><?php echo $nr?>.</td>
		  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
		  <td valign="top" class="database_optimization-bold"><?php echo $results[$i]['post_title']?></td>
		  <td valign="top"><?php
				} // if (!$scheduler)
				
				$sql_get_posts = sprintf( "
				  SELECT `ID`, `post_modified`
					FROM %sposts
				   WHERE `post_parent` = %d
					 AND `post_type`   = 'revision'
				ORDER BY `post_modified` ASC		
				", $results[$i]['site'], $results[$i]['post_parent']);
	
				$results_get_posts = $wpdb->get_results($sql_get_posts);
				
				for($j=0; $j<$nr_to_delete; $j++) {
					if(!$scheduler) echo $results_get_posts[$j]->post_modified.'<br />';
										
					$sql_delete = sprintf ("
					DELETE FROM %sposts
					 WHERE `ID` = %d
					", $results[$i]['site'], $results_get_posts[$j]->ID);
					
					$wpdb->get_results($sql_delete);
				} // for($j=0; $j<$nr_to_delete; $j++)
				
				$nr++;
				if(!$scheduler) {
		?></td>
		  <td align="right" valign="top" class="database_optimization-bold"><?php echo $nr_to_delete?> <?php _e('of', $database_optimization_class->database_optimization_txt_domain)?> <?php echo $results[$i]['cnt'];?></td>
		</tr>
		<?php
				} // if(!$scheduler)
			} // for($i=0; $i<count($results); $i++)
		} // if($database_optimization_class->database_optimization_options['revisions'] == 'Y')
		
		return $total_deleted;		
	} // function database_optimization_delete_revisions()


	/********************************************************************************************
	 *	CHECK IF POST IS EXCLUDED BY A CUSTOM FIELD ('keep_revisions')
	 ********************************************************************************************/
	function database_optimization_post_is_excluded($parent_id) {
		$keep_revisions = get_post_meta($parent_id, 'keep_revisions', true);
		return ($keep_revisions === 'Y');
	} // database_optimization_post_is_exclude()


	/********************************************************************************************
	 *	GET TRASHED POSTS / PAGES AND COMMENTS
	 ********************************************************************************************/
	function database_optimization_get_trash() {
		global $wpdb, $database_optimization_class;
		
		$res_arr = array();

		$index = 0;
		// LOOP TROUGH SITES
		for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++) {
			$sql = sprintf ("
			   SELECT `ID` AS id, 'post' AS post_type, `post_title` AS title, `post_modified` AS modified
				 FROM %sposts
				WHERE `post_status` = 'trash'
			UNION ALL
			   SELECT `comment_ID` AS id, 'comment' AS post_type, `comment_author_IP` AS title, `comment_date` AS modified
				 FROM %scomments
				WHERE `comment_approved` = 'trash'
			 ORDER BY post_type, UCASE(title)		
			", $database_optimization_class->database_optimization_ms_prefixes[$i], $database_optimization_class->database_optimization_ms_prefixes[$i]);
			$res = $wpdb->get_results($sql, ARRAY_A);

			if($res != null) {
				$res_arr[$index] = $res[0];
				$res_arr[$index]['site'] = $database_optimization_class->database_optimization_ms_prefixes[$i];				
				$index++;
			} // if($res != null)	
		} // for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++)
		
		return $res_arr;
	} // database_optimization_get_trash()


	/********************************************************************************************
	 *	DELETE TRASHED POSTS AND PAGES
	 ********************************************************************************************/
	function database_optimization_delete_trash($results, $scheduler) {
		global $wpdb;
	
		$nr = 1;
		$total_deleted = count($results);
		
		for($i=0; $i<$total_deleted; $i++) {
			if(!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['post_type']; ?></td>
	  <td valign="top"><?php echo $results[$i]['title']; ?></td>
	  <td valign="top" nowrap="nowrap"><?php echo $results[$i]['modified']; ?></td>
	</tr>
	<?php
			} // if(!$scheduler)
			
			if($results[$i]['post_type'] == 'comment') {
				// DELETE META DATA (IF ANY...)
				$sql_delete = sprintf ("
				DELETE FROM %scommentmeta
				 WHERE `comment_id` = %d
				", $results[$i]['site'], $results[$i]['id']);
				$wpdb->get_results($sql_delete);  
			} // if($results[$i]['post_type'] == 'comment')
			
			// DELETE TRASHED POSTS / PAGES
			$sql_delete = sprintf ("
			DELETE FROM %sposts
			 WHERE `post_status` = 'trash'			
			", $results[$i]['site']);
			$wpdb->get_results($sql_delete);		
	
			// DELETE TRASHED COMMENTS
			$sql_delete = sprintf ("
			DELETE FROM %scomments
			 WHERE `comment_approved` = 'trash'
			", $results[$i]['site']);
			$wpdb->get_results($sql_delete);	
			
			$nr++;
		} // for($i=0; $i<count($results); $i++)
	
		return $total_deleted;
	} // database_optimization_delete_trash()


	/********************************************************************************************
	 *	GET SPAMMED COMMENTS
	 ********************************************************************************************/
	function database_optimization_get_spam() {
		global $wpdb, $database_optimization_class;
	
		$res_arr = array();
	
		$index = 0;
		// LOOP THROUGH SITES
		for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++) {
			$sql = sprintf ("
			  SELECT `comment_ID`, `comment_author`, `comment_author_email`, `comment_date`
				FROM %scomments
			   WHERE `comment_approved` = 'spam'
			ORDER BY UCASE(`comment_author`)
			", $database_optimization_class->database_optimization_ms_prefixes[$i]);
			$res = $wpdb->get_results($sql, ARRAY_A);
	
			if($res != null) {
				$res_arr[$index] = $res[0];
				$res_arr[$index]['site'] = $database_optimization_class->database_optimization_ms_prefixes[$i];				
				$index++;
			} // if($res != null)		
		} // for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++)
		
		return $res_arr;
	} // database_optimization_get_spam()


	/********************************************************************************************
	 *	DELETE SPAMMED ITEMS
	 ********************************************************************************************/
	function database_optimization_delete_spam($results, $scheduler)
	{
		global $wpdb;
	
		$nr = 1;
		$total_deleted = count($results);
		for($i=0; $i<count($results); $i++) {
			if (!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['comment_author']; ?></td>
	  <td valign="top"><?php echo $results[$i]['comment_author_email']; ?></td>
	  <td valign="top" nowrap="nowrap"><?php echo $results[$i]['comment_date']; ?></td>
	</tr>
	<?php
			} // if (!$scheduler)
			
			$sql_delete = sprintf ("
			DELETE FROM %scommentmeta
			 WHERE `comment_id` = %d
			", $results[$i]['site'], $results[$i]['comment_ID']);
			$wpdb->get_results($sql_delete);
			
			$sql_delete = sprintf ("
			DELETE FROM %scomments
			 WHERE `comment_approved` = 'spam'
			", $results[$i]['site']);
			$wpdb->get_results($sql_delete);
	
			$nr++;				
		} // for($i=0; $i<count($results); $i++)
		
		return $total_deleted;
	} // database_optimization_delete_spam()


	/********************************************************************************************
	 *	DELETE UNUSED TAGS
	 ********************************************************************************************/
	function database_optimization_delete_tags() {
		global	$wpdb, $database_optimization_class;
			
		$total_deleted = 0;

		// LOOP THROUGH THE NETWORK
		for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++) {
			$sql = sprintf ("
			SELECT a.term_id AS term_id, a.name AS name
			  FROM `%sterms` a, `%sterm_taxonomy` b
			 WHERE a.term_id = b.term_id
			   AND b.taxonomy = 'post_tag'
			   AND b.term_taxonomy_id NOT IN (
				SELECT term_taxonomy_id
				  FROM %sterm_relationships
			    )
			", $database_optimization_class->database_optimization_ms_prefixes[$i], $database_optimization_class->database_optimization_ms_prefixes[$i], $database_optimization_class->database_optimization_ms_prefixes[$i]);
			
			$res = $wpdb->get_results($sql);
			for($j=0; $j<count($res); $j++) {
				if(!$this->database_optimization_delete_tags_is_scheduled($res[$j]->term_id, $database_optimization_class->database_optimization_ms_prefixes[$i])) {
					// TAG NOT USED IN SCHEDULED POSTS: CAN BE DELETED
					$total_deleted++;
					
					$sql_del = sprintf ("
					DELETE FROM %sterm_taxonomy
					 WHERE term_id = %d
					", $database_optimization_class->database_optimization_ms_prefixes[$i], $res[$j]->term_id);
					$wpdb->get_results($sql_del);
					
					$sql_del = sprintf ("
					DELETE FROM %sterms
					 WHERE term_id = %d
					", $database_optimization_class->database_optimization_ms_prefixes[$i], $res[$j]->term_id);
					$wpdb->get_results($sql_del);
				}				
			} // for($j=0; $j<count($res); $j++)
		} // for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++)
		
		return $total_deleted;
	} // database_optimization_delete_tags()


	/********************************************************************************************
	 *	IS THE UNUSED TAG USED IN ONE OR MORE SCHEDULED POSTS?
	 ********************************************************************************************/
	function database_optimization_delete_tags_is_scheduled($term_id, $database_optimization_prefix) {
		global $wpdb;
	
		$sql_get_posts = sprintf ("
		SELECT p.post_status
		  FROM %sterm_relationships t, %sposts p
		 WHERE t.term_taxonomy_id = '%s'
		   AND t.object_id        = p.ID
		", $database_optimization_prefix, $database_optimization_prefix, $term_id);
	
		$results_get_posts = $wpdb->get_results($sql_get_posts);
		for($i=0; $i<count($results_get_posts); $i++)
			if($results_get_posts[$i]->post_status == 'future') return true;
	
		return false;	
	} // database_optimization_delete_tags_is_scheduled()


	/********************************************************************************************
	 *	DELETE TRANSIENTS (v4.3.1)
	 ********************************************************************************************/
	function database_optimization_delete_transients() {
		global $wpdb, $database_optimization_class;
	
		$total_deleted = 0;

		if ($database_optimization_class->database_optimization_options['clear_transients'] == 'Y') {
			// ONLY DELETE EXPIRED TRANSIENTS
			
			$delay = time() - 60;	// ONE MINUTE DELAY
			
			// LOOP THROUGH THE NETWORK
			for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++) {
				// FIND EXPIRED TRANSIENTS
				$sql = "
				SELECT `option_name`
				FROM ".$database_optimization_class->database_optimization_ms_prefixes[$i]."options
				WHERE (
					option_name LIKE '_transient_timeout_%'
					OR option_name LIKE '_site_transient_timeout_%'
				)
				AND option_value < '".$delay."'
				";
				
				$results = $wpdb->get_results($sql);
				$total_deleted += count($results);
				
				// LOOP THROUGH THE RESULTS
				for($j=0; $j<count($results); $j++) {
					if(substr($results[$j]->option_name, 0, 19) == '_transient_timeout_') {
						// _transient_timeout_%
						$transient = substr($results[$j]->option_name, 19);
						// DELETE THE TRANSIENT
						delete_transient($transient);					
					} else {
						// _site_transient_timeout_%
						$transient = substr($results[$j]->option_name, 24);
						// DELETE THE TRANSIENT
						delete_site_transient($transient);				
					} // if(substr($results[$j]->option_name, 0, 19) == '_transient_timeout_')
				} // for($j=0; $j<count($results); $j++)
			} // for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++)
		} else {
			// DELETE ALL TRANSIENTS
			
			// LOOP THROUGH THE NETWORK
			for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++) {
				// FIND EXPIRED TRANSIENTS
				$sql = "
				SELECT `option_name`
				FROM ".$database_optimization_class->database_optimization_ms_prefixes[$i]."options
				WHERE option_name LIKE '%_transient_%'
				";
				
				$results = $wpdb->get_results($sql);
				$total_deleted += count($results);
				
				// LOOP THROUGH THE RESULTS
				for($j=0; $j<count($results); $j++) {
					delete_option($results[$j]->option_name);	
				} // for($j=0; $j<count($results); $j++)
			} // for($i=0; $i<count($database_optimization_class->database_optimization_ms_prefixes); $i++)		
		} // if ($database_optimization_class->database_optimization_options['clear_transients'] == 'Y')
		
		return $total_deleted;

	} // database_optimization_delete_transients()


	
	/********************************************************************************************
	 *	OPTIMIZE DATABASE TABLES
	 ********************************************************************************************/
	function database_optimization_optimize_tables($scheduler) {
		global $database_optimization_class, $wpdb;

		$cnt = 0;
		for ($i=0; $i<count($database_optimization_class->database_optimization_tables); $i++) {
			if(!isset($database_optimization_class->database_optimization_excluded_tabs[$database_optimization_class->database_optimization_tables[$i][0]])) {
				# TABLE NOT EXCLUDED
				$cnt++;

				$sql = sprintf ("
				SELECT engine, (data_length + index_length) AS size, table_rows
				  FROM information_schema.TABLES
				 WHERE table_schema = '%s'
				   AND table_name   = '%s'
				", DB_NAME, $database_optimization_class->database_optimization_tables[$i][0]);
				$table_info = $wpdb->get_results($sql);

				if($database_optimization_class->database_optimization_options["optimize_inndatabase_optimization"] == 'N' && strtolower($table_info[0]->engine) == 'inndatabase_optimization') {
					// SKIP InnoDB tables
					$msg = __('InnoDB table: skipped...', 'optimize-database');
				} else {
					$query  = "OPTIMIZE TABLE ".$database_optimization_class->database_optimization_tables[$i][0];
					$result = $wpdb->get_results($query);
					$msg    = $result[0]->Msg_text;
					$msg    = str_replace('OK', __('<span class="database_optimization-optimized">TABLE OPTIMIZED</span>', 'optimize-database'), $msg);
					$msg    = str_replace('Table is already up to date', __('Table is already up to date', 'optimize-database'), $msg);
					$msg    = str_replace('Table does not support optimize, doing recreate + analyze instead', __('<span class="database_optimization-optimized">TABLE OPTIMIZED</span>', 'optimize-database'), $msg);
				}
				
				if (!$scheduler)
				{	// NOT FROM THE SCEDULER
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $cnt?>.</td>
	  <td valign="top" class="database_optimization-bold"><?php echo $database_optimization_class->database_optimization_tables[$i][0] ?></td>
	  <td valign="top"><?php echo $msg ?></td>
	  <td valign="top"><?php echo $table_info[0]->engine ?></td>
	  <td align="right" valign="top"><?php echo $table_info[0]->table_rows ?></td>
	  <td align="right" valign="top"><?php echo $database_optimization_class->database_optimization_utilities_obj->database_optimization_format_size($table_info[0]->size) ?></td>
	</tr>
	<?php
				} // if (!$scheduler)
			} // if(!$excluded)
		} // for ($i=0; $i<count($tables); $i++)
		return $cnt;
		
	} // database_optimization_optimize_tables()	
	
} // database_optimization_Cleaner
?>