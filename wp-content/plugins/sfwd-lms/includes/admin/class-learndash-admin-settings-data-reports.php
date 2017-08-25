<?php

if ( !class_exists( 'Learndash_Admin_Settings_Data_Reports' ) ) {
	class Learndash_Admin_Settings_Data_Reports {
		
		protected $process_times = array();

		private $report_actions = array();
		
		function __construct() {
			add_action( 'init', array( $this, 'init_check_for_download_request' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			
			if ( !defined( 'LEARNDASH_PROCESS_TIME_PERCENT' ) )
				define( 'LEARNDASH_PROCESS_TIME_PERCENT', apply_filters('learndash_process_time_percent', 80 ) );

			if ( !defined( 'LEARNDASH_PROCESS_TIME_SECONDS' ) )
				define( 'LEARNDASH_PROCESS_TIME_SECONDS', apply_filters('learndash_process_time_seconds', 10 ) );
			
		}
		
		function init_check_for_download_request() {
			if (isset( $_GET['report-download'] ) ) {
			
				error_log('_GET<pre>'. print_r($_GET, true) .'</pre>');

				if ( ( isset( $_GET['data-nonce'] ) ) && ( !empty( $_GET['data-nonce'] ) ) && ( isset( $_GET['data-slug'] ) ) && ( !empty( $_GET['data-slug'] ) ) ) {
					
					if ( wp_verify_nonce( esc_attr( $_GET['data-nonce'] ), 'learndash-data-reports-'. esc_attr( $_GET['data-slug'] ) .'-'. get_current_user_id() ) ) {
						$transient_key = esc_attr( $_GET['data-slug'] ) .'_'. esc_attr( $_GET['data-nonce'] );
						error_log("transient_key[". $transient_key ."]");
						
						$transient_data = get_transient( $transient_key );
						error_log('transient_data<pre>'. print_r($transient_data, true) .'</pre>');
						
						if ( ( isset( $transient_data['report_filename'] ) ) && ( !empty( $transient_data['report_filename'] ) ) ) {
							$report_filename = ABSPATH . $transient_data['report_filename'];
							if ( ( file_exists( $report_filename ) ) && (is_readable( $report_filename ) ) ) {
								header('Content-Type: text/csv');
								header('Content-Disposition: attachment; filename='. basename( $report_filename ) );
								header('Pragma: no-cache');
								header("Expires: 0");

								echo file_get_contents($report_filename);
							}
						}
						die();
					} else {
						echo "wp_verify_nonce failed<br />";
					}
				}
			}
		}
		
		/**
		 * Register settings page
		 */
		public function admin_menu() {
			
			$element = Learndash_Admin_Settings_Data_Upgrades::get_instance();
			$data_settings_courses = $element->get_data_settings('user-meta-courses');
			$data_settings_quizzes = $element->get_data_settings('user-meta-quizzes');

			if ( ( !empty( $data_settings_courses ) ) && ( !empty( $data_settings_quizzes ) ) ) {
				$page_hook = add_submenu_page(
					'learndash-lms-non-existant',
					_x( 'Reports', 'Learndash Report Menu Label', 'learndash' ),
					_x( 'Reports', 'Learndash Report Menu Label', 'learndash' ),
					LEARNDASH_ADMIN_CAPABILITY_CHECK,
					'learndash-lms-reports',
					array( $this, 'admin_page' )
				);
				add_action( 'load-'. $page_hook, array( $this, 'on_load_panel' ) );
				
			} else {
				
				add_submenu_page(
					'learndash-lms-non-existant',
					__( 'LearnDash Reports', 'learndash' ),
					__( 'LearnDash Reports', 'learndash' ),
					LEARNDASH_ADMIN_CAPABILITY_CHECK,
					'learndash-lms-reports',
					'learndash_lms_reports_page'
				);
			}
		}

		function on_load_panel() {

			wp_enqueue_style( 
				'learndash_style', 
				LEARNDASH_LMS_PLUGIN_URL . 'assets/css/style'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css',
				array(), 
				LEARNDASH_VERSION 
			);
			$learndash_assets_loaded['styles']['learndash_style'] = __FUNCTION__;

			wp_enqueue_style( 
				'sfwd-module-style', 
				LEARNDASH_LMS_PLUGIN_URL . '/assets/css/sfwd_module'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', 
				array(), 
				LEARNDASH_VERSION 
			);
			$learndash_assets_loaded['styles']['sfwd-module-style'] = __FUNCTION__;
			
			
			wp_enqueue_script( 
				'learndash-admin-settings-data-reports-script', 
				LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-settings-data-reports'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', 
				array( 'jquery' ), 
				LEARNDASH_VERSION,
				true 
			);
			$learndash_assets_loaded['scripts']['learndash-admin-settings-data-reports-script'] = __FUNCTION__;
			
			$this->init_report_actions();
			
		}

		function init_report_actions() {
			
			$this->report_actions = apply_filters('learndash_admin_report_register_actions', $this->report_actions );
		}

		public function admin_page() {

			?>
			<div id="learndash-settings" class="wrap">
				<h1><?php _e( 'User Reports', 'learndash' ); ?></h1>
				<form method="post" action="options.php">
					<div class="sfwd_options_wrapper sfwd_settings_left">
						<div id="advanced-sortables" class="meta-box-sortables">
							<div id="sfwd-courses_metabox" class="postbox learndash-settings-postbox">
								<div class="handlediv" title="<?php _e( 'Click to toggle', 'learndash' ); ?>"><br></div>
								<h3 class="hndle"><span><?php _e( 'User Reports', 'learndash' ); ?></span></h3>
								<div class="inside">
									<div class="sfwd sfwd_options sfwd-courses_settings">

										<table id="learndash-data-reports" class="wc_status_table widefat" cellspacing="0">
										<?php
											//error_log('report_actions<pre>'. print_r($this->report_actions, true) .'</pre>');	 
											foreach( $this->report_actions as $report_action_slug => $report_action ) {
												$report_action['instance']->show_report_action();
											}
										?>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<?php
		}
		
		function do_data_reports( $post_data = array(), $reply_data = array() ) {
			
			$this->init_report_actions();
			
			if ( ( isset( $post_data['slug'] ) ) && ( !empty( $post_data['slug'] ) ) ) {
				$post_data_slug = esc_attr( $post_data['slug'] );
				
				if ( isset( $this->report_actions[$post_data_slug] ) ) {
					$reply_data = $this->report_actions[$post_data_slug]['instance']->process_report_action( $post_data );
				} 
			}
			return $reply_data;
		}
		
					
		function init_process_times() {
			$this->process_times['started'] = time();
			$this->process_times['limit'] = ini_get('max_execution_time');
		}

		function out_of_timer() {
			$this->process_times['current_time'] = time();			
			
			$this->process_times['ticks'] = $this->process_times['current_time'] - $this->process_times['started'];
			$this->process_times['percent'] = ($this->process_times['ticks'] / $this->process_times['limit']) * 100;

			// If we are over 80% of the allowed processing time or over 10 seconds then finish up and return
			if (( $this->process_times['percent'] >= LEARNDASH_PROCESS_TIME_PERCENT) || ($this->process_times['ticks'] > LEARNDASH_PROCESS_TIME_SECONDS))
				return true;
		
			return false;
		}

		// End of functions
	}
}

// Go ahead and inlcude out User Meta Courses upgrade class
require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/classes-data-reports-actions/class-learndash-admin-data-reports-user-courses.php' );
require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/classes-data-reports-actions/class-learndash-admin-data-reports-user-quizzes.php' );

add_action('plugins_loaded', function() {
	new Learndash_Admin_Data_Reports_Courses();
	new Learndash_Admin_Data_Reports_Quizzes();
});


function learndash_data_reports_ajax() {
	//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');

	$reply_data = array( 'status' => false);

	if ( isset( $_POST['data'] ) )
		$post_data = $_POST['data'];
	else
		$post_data = array();
		
	$ld_admin_settings_data_reports = new Learndash_Admin_Settings_Data_Reports;
	$reply_data['data'] = $ld_admin_settings_data_reports->do_data_reports( $post_data, $reply_data );
	
	if ( !empty( $reply_data ) )
		echo json_encode($reply_data);

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_learndash-data-reports', 'learndash_data_reports_ajax' );
