<?php
if ( !class_exists( 'Learndash_Admin_Data_Upgrades_User_Activity_DB_Table' ) ) {
	class Learndash_Admin_Data_Upgrades_User_Activity_DB_Table extends Learndash_Admin_Settings_Data_Upgrades {
		
		public static $instance = null;

		function __construct() {
			self::$instance =& $this;
			
			add_action( 'init', array( $this, 'upgrade_data_settings') );
		}
		
		public static function getInstance() {
		    if ( ! isset( self::$_instance ) ) {
		        self::$_instance = new self();
		    }
		    return self::$_instance;
		}
		
		/**
		 * Update the LearnDash Settings 
		 *
		 * Checks to see if settings needs to be updated. 
		 * @since 2.3
		 * 
		 * @param  none
		 * @param  none
		 */
		function upgrade_data_settings() {
					
			if (is_admin()) {
				$db_version = $this->get_data_settings( 'db_version' );
				if ( ( defined( 'LEARNDASH_SETTINGS_DB_VERSION' ) ) && ( LEARNDASH_SETTINGS_DB_VERSION != '' ) && ( $this->data_settings['db_version'] < LEARNDASH_SETTINGS_DB_VERSION ) ) {
					
					$this->upgrade_db_tables( $this->data_settings['db_version'] );

					$this->set_data_settings( 'db_version', LEARNDASH_SETTINGS_DB_VERSION );
				}
			}
		}
		
		function upgrade_db_tables( $data_version = 0 ) {
			global $wpdb;
			
			if (!function_exists('dbDelta'))
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			$charset_collate = '';
			if ( ! empty($wpdb->charset) )
				$charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
			
			$learndash_user_activity_db_table = $wpdb->prefix ."learndash_user_activity";
			$learndash_user_activity_db_table_create_query = "CREATE TABLE ". $learndash_user_activity_db_table ." (
				activity_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL DEFAULT '0',
			  	post_id bigint(20) unsigned NOT NULL DEFAULT '0',
			  	activity_type varchar(50) DEFAULT NULL,
			  	activity_status tinyint(1) unsigned DEFAULT '0',
			  	activity_started int(11) unsigned DEFAULT NULL,
			  	activity_completed int(11) unsigned DEFAULT NULL,
			  	activity_updated int(11) unsigned DEFAULT NULL,
			  	PRIMARY KEY  (activity_id),
			  	KEY user_id (user_id),
			  	KEY post_id (post_id),
			  	KEY activity_status (activity_status),
			  	KEY activity_type (activity_type),
			  	KEY activity_started (activity_started),
			  	KEY activity_completed (activity_completed),
			  	KEY activity_updated (activity_updated)
				) ". $charset_collate .";";
			dbDelta( $learndash_user_activity_db_table_create_query );


			$learndash_user_activity_meta_db_table = $wpdb->prefix ."learndash_user_activity_meta";
			$learndash_user_activity_meta_db_table_create_query = "CREATE TABLE ". $learndash_user_activity_meta_db_table ." (
				activity_meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				activity_id bigint(20) unsigned NOT NULL DEFAULT '0',
				activity_meta_key varchar(255) DEFAULT NULL,
				activity_meta_value mediumtext,
				PRIMARY KEY  (activity_meta_id),
				KEY activity_id (activity_id),
				KEY activity_meta_key (activity_meta_key(191))
			) ". $charset_collate .";";
			dbDelta( $learndash_user_activity_meta_db_table_create_query );
		}
		
		// end of functions
	}
}
