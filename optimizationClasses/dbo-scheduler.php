<?php
class database_optimization_Scheduler {
	
	
    function __construct() {
		global $database_optimization_class;
		
		// ADD EXTRA CRON SCHEDULES
		add_filter('cron_schedules', array(&$this, 'database_optimization_extra_cron_schedules'));
		
		// ADD SCHEDULER
		add_action('database_optimization_scheduler', array(&$database_optimization_class, 'database_optimization_start_scheduler'));
	} // __construct()
	
	
	
	function database_optimization_extra_cron_schedules($schedules) {
		global $database_optimization_class;
		
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __('Once Weekly', $database_optimization_class->database_optimization_txt_domain)
		);
		$schedules['monthly'] = array(
			'interval' => 2628000, // average amount of seconds in a month
			'display'  => __('Once Monthly', $database_optimization_class->database_optimization_txt_domain)
		);		
		// FOR DEBUGGING
		$schedules['fiveminutes'] = array(
			'interval' => 300,
			'display'  => __('Every Five Minutes', $database_optimization_class->database_optimization_txt_domain)
		);		
		return $schedules;
	} // database_optimization_extra_cron_schedules()
	
	
	/*******************************************************************************
	 * 	UPDATE SCHEDULER (IF NEEDED)
	 *******************************************************************************/
	function database_optimization_update_scheduler() {
		global $database_optimization_class;

		if($database_optimization_class->database_optimization_options['schedule_type'] == '') {
			// SHOULDN'T BE SCHEDULED
			wp_clear_scheduled_hook('database_optimization_scheduler');
			$database_optimization_class->database_optimization_options['schedule_hour'] = '';
			$database_optimization_class->database_optimization_multisite_obj->database_optimization_ms_update_option('database_optimization_options', $database_optimization_class->database_optimization_options);		
		} else {
			// JOB SHOULD BE SCHEDULED: SCHEDULE IT
			if($database_optimization_class->database_optimization_options['schedule_type'] != 'daily' &&
				$database_optimization_class->database_optimization_options['schedule_type'] != 'weekly' &&
				$database_optimization_class->database_optimization_options['schedule_type'] != 'monthly'
				) {
				$database_optimization_class->database_optimization_options['schedule_hour'] = '';
				$database_optimization_class->database_optimization_multisite_obj->database_optimization_ms_update_option('database_optimization_options', $database_optimization_class->database_optimization_options);
			}
		
			if (!wp_next_scheduled('database_optimization_scheduler'))
				wp_schedule_event($this->database_optimization_calculate_time(), $database_optimization_class->database_optimization_options['schedule_type'], 'database_optimization_scheduler');
		} // if($database_optimization_class->database_optimization_options['schedule_type'] == '')
	} // database_optimization_update_scheduler()
	

	/*******************************************************************************
	 * 	SCHEDULE CHANGED ON SETTINGS PAGE: RESCHEDULE
	 *******************************************************************************/		
	function database_optimization_reschedule() {
		global $database_optimization_class;
		
		wp_clear_scheduled_hook('database_optimization_scheduler');
		wp_schedule_event($this->database_optimization_calculate_time(), $database_optimization_class->database_optimization_options['schedule_type'], 'database_optimization_scheduler');
	} // database_optimization_reschedule()


	/*******************************************************************************
	 * 	CALCULATE SCHEDULE TIME, BASED ON THE SCHEDULE TYPE
	 
	 *******************************************************************************/	
	function database_optimization_calculate_time() {
		global $database_optimization_class;

		// CURRENT TIME (WITH TIMEZONE)
		$timestamp = current_time('timestamp', 1);
		// YYYYMMDDHHMMSS
		$ymdhis    = date('YmdHis', $timestamp);

		// CHOP TIME, YYYYMMDD
		$current_date = substr($ymdhis, 0, 8);
		$current_hour = substr($ymdhis, 8, 2);
	
		if ($database_optimization_class->database_optimization_options['schedule_type'] == 'daily' ||
				$database_optimization_class->database_optimization_options['schedule_type'] == 'weekly' ||
				$database_optimization_class->database_optimization_options['schedule_type'] == 'monthly'
				) {
			// 'daily', 'weekly' OR 'monthly'
			if($database_optimization_class->database_optimization_options['schedule_hour'] <= $current_hour) {
				// NEXT RUN WILL BE TOMORROW
				$date = date('YmdHis', strtotime($current_date.$database_optimization_class->database_optimization_options['schedule_hour'].'0000'.' + 1 day'));
			} else {
				// NEXT RUN WILL BE TODAY
				$date = $current_date.$database_optimization_class->database_optimization_options['schedule_hour'].'0000';
			} // if($database_optimization_class->database_optimization_options['schedule_hour'] <= $current_hour)
		} else {
			// 'hourly' OR 'twicedaily'
			
			// ADD ONE HOUR TO THE CURRENT TIME: IT WILL RUN THE NEXT FULL HOUR (16:00 FOR INSTANCE)
			$ts   = $timestamp + 3600;
			$date = date('YmdH0000', $ts);
		} // if ($database_optimization_class->database_optimization_options['schedule_type'] == 'daily' ...
		
		// CONVERT TO TIMESTAMP
		return strtotime($date);
	} // database_optimization_calculate_time()
} // database_optimization_Scheduler
?>