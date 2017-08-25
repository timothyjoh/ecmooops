<?php
// This class handles the data upgrade from the user meta arrays into a DB structure to allow on the floy reporting. Plus to not bloat the 
// user meta table. 

if ( !class_exists( 'Learndash_Admin_Settings_Data_Upgrades' ) ) {
	class Learndash_Admin_Settings_Data_Upgrades {

		private static $instance;
		
		protected $process_times = array();
		protected $data_slug;
		protected $meta_ke;
		
		protected $data_settings_loaded = false;
		protected $data_settings = array();

		protected $upgrade_actions = array();
		
		function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			
			if ( !defined( 'LEARNDASH_PROCESS_TIME_PERCENT' ) )
				define( 'LEARNDASH_PROCESS_TIME_PERCENT', apply_filters('learndash_process_time_percent', 80 ) );

			if ( !defined( 'LEARNDASH_PROCESS_TIME_SECONDS' ) )
				define( 'LEARNDASH_PROCESS_TIME_SECONDS', apply_filters('learndash_process_time_seconds', 10 ) );
		}
		
		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}
		
		/**
		 * Initialize the LearnDash Settings array
		 *
		 * @since 2.3
		 * 
		 * @param  bool $force_reload optional to force reload from database
		 * @param  none
		 */
		function init_data_settings( $force_reload = false ) {
			
			if ( ( $this->data_settings_loaded != true ) || ( $force_reload == true ) ) {
				$this->data_settings_loaded = true;
				$this->data_settings = get_option('learndash_data_settings', array());

				if ( !isset( $this->data_settings['db_version'] ) )
					$this->data_settings['db_version'] = 0;
			}
		}
				
		/**
		 * Get the LearnDash Settings array
		 *
		 * @since 2.3
		 * 
		 * @param  string $key optional to return only specifc key value. 
		 * @return  mixed 
		 */
		function get_data_settings( $key = '' ) {
			$this->init_data_settings(true);
			
			if ( !empty( $key ) ) {
				if ( isset( $this->data_settings[$key] ) ) {
					return $this->data_settings[$key];
				}
			} else {
				return $this->data_settings;
			}
		}
		
		function set_data_settings( $key = '', $value = '' ) {
			if ( empty( $key ) ) return;
			
			$this->init_data_settings(true);
			$this->data_settings[$key] = $value;
			
			return update_option('learndash_data_settings', $this->data_settings);
		}		
		
		/**
		 * Register settings menu page
		 */
		public function admin_menu() {
			$page_hook = add_submenu_page(
				'learndash-lms-non-existant',
				__( 'Data Upgrades', 'learndash' ),
				__( 'Data Upgrades', 'learndash' ),
				LEARNDASH_ADMIN_CAPABILITY_CHECK,
				'learndash_data_upgrades',
				array( $this, 'admin_page' )
			);
			add_action( 'load-'. $page_hook, array( $this, 'on_load_panel' ) );
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
				LEARNDASH_LMS_PLUGIN_URL . 'assets/css/sfwd_module'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', 
				array(), 
				LEARNDASH_VERSION 
			);
			$learndash_assets_loaded['styles']['sfwd-module-style'] = __FUNCTION__;
			
			
			wp_enqueue_script( 
				'learndash-admin-settings-data-upgrades-script', 
				LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-settings-data-upgrades'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', 
				array( 'jquery' ), 
				LEARNDASH_VERSION,
				true 
			);
			$learndash_assets_loaded['scripts']['learndash-admin-settings-data-upgrades-script'] = __FUNCTION__;
			
			$this->init_upgrade_actions();
		}

		function init_upgrade_actions() {
			
			$this->upgrade_actions = apply_filters('learndash_admin_settings_upgrades_register_actions', $this->upgrade_actions);			
		}

		public function admin_page() {
			?>
			<div id="learndash-settings" class="wrap">
				<h1><?php _e( 'Data Upgrades', 'learndash' ); ?></h1>
				<form method="post" action="options.php">
					<div class="sfwd_options_wrapper sfwd_settings_left">
						<div id="advanced-sortables" class="meta-box-sortables">
							<div id="sfwd-courses_metabox" class="postbox learndash-settings-postbox">
								<div class="handlediv" title="<?php _e( 'Click to toggle', 'learndash' ); ?>"><br></div>
								<h3 class="hndle"><span><?php _e( 'Data Upgrades', 'learndash' ); ?></span></h3>
								<div class="inside">
									<div class="sfwd sfwd_options sfwd-courses_settings">

										<table id="learndash-data-upgrades" class="wc_status_table widefat" cellspacing="0">
										<?php
											foreach( $this->upgrade_actions as $upgrade_action_slug => $upgrade_action ) {
												$upgrade_action['instance']->show_upgrade_action();
											}
										?>

<?php /* ?>
										<tr class="learndash-data-postmeta">
											<td><button id="learndash-data-upgrade-postmeta" class="button"><?php _e('Courses Post meta', 'learndash') ?></button></td>
											<td>
												<p><?php _e('Courses Post meta', 'learndash') ?><span class="description"> <?php _e('This upgrade will reconfigure the post meta information storage used for Courses, Lessons, Topics and Quizzes. (Required)', 'learndash')?></span>
												</p>
											</td>
										</tr>
										<tr id="learndash-data-upgrade-postmeta-status" style="display:none;">
											<td colspan="2" style="width: 100%">
												<div class="meter" style="width: 100%;">
													<div class="progress-meter" style="width:100%; height: 20px; border: 1px solid black;">
														<span class="progress-meter-image" style="background-color: red; float: left; height: 16px; width: 0; margin: 2px"></span>
													</div>
													<div class="progress-label" style="width: 100%"></div>
												</div>
											</td>
										</tr>
<?php */ ?>

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

		function set_last_run_info( $data = array() ) {
			$data_settings = array(
				'last_run' 	=>	 time(),
				'user_id'	=> 	get_current_user_id()
			);
			
			if ( isset( $data['total_count'] ) ) {
				$data_settings['total_count'] =	$data['total_count'];
			}
			
			$this->set_data_settings( $this->data_slug, $data_settings );
		}
		
		function get_last_run_info() {
			$last_run_info = '';
			
			$data_settings = $this->get_data_settings( $this->data_slug );

			if ( !empty( $data_settings ) ) {
				$user = get_user_by( 'id', $data_settings['user_id'] );
			
				 $last_run_info = sprintf(_x('Last run: %s by %s', 'placeholders: date/time, user name', 'learndash'), 			
					learndash_adjust_date_time_display($data_settings['last_run']),
					 $user->display_name); 
			} else {
			 	$last_run_info = __('Last run: none', 'learndash'); 
			}

			return $last_run_info;
		}
		
		function clear_previous_run_meta( $data = array() ) {
			global $wpdb;
			
			$wpdb->delete( 
				$wpdb->prefix .'usermeta', 
				array( 'meta_key' => 'ld-upgraded-'. $this->data_slug ), 
				array( '%s' ) 
			);
		}		
		
		function do_data_upgrades( $post_data = array(), $reply_data = array() ) {
			
			$this->init_upgrade_actions();
			
			if ( ( isset( $post_data['slug'] ) ) && ( !empty( $post_data['slug'] ) ) ) {
				$post_data_slug = esc_attr( $post_data['slug'] );
				
				if ( isset( $this->upgrade_actions[$post_data_slug] ) ) {
					if ( isset( $post_data['data'] ) )
						$data = $post_data['data'];
					else
						$data = array();
					
					$reply_data = $this->upgrade_actions[$post_data_slug]['instance']->process_upgrade_action( $post_data );
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

require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/classes-data-uprades-actions/class-learndash-admin-data-upgrades-user-activity-db-table.php' );
require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/classes-data-uprades-actions/class-learndash-admin-data-upgrades-user-meta-courses.php' );
require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/classes-data-uprades-actions/class-learndash-admin-data-upgrades-user-meta-quizzes.php' );
add_action('plugins_loaded', function() {
	new Learndash_Admin_Data_Upgrades_User_Activity_DB_Table();
	new Learndash_Admin_Data_Upgrades_User_Meta_Courses();
	new Learndash_Admin_Settings_Upgrades_User_Meta_Quizzes();
});


function learndash_data_upgrades_ajax() {
	//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');

	$reply_data = array( 'status' => false);


	if ( isset( $_POST['data'] ) )
		$post_data = $_POST['data'];
	else
		$post_data = array();
		
	$ld_admin_settings_data_upgrades = new Learndash_Admin_Settings_Data_Upgrades;
	$reply_data['data'] = $ld_admin_settings_data_upgrades->do_data_upgrades( $post_data, $reply_data );
	
	if ( !empty( $reply_data ) )
		echo json_encode($reply_data);

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_learndash-data-upgrades', 'learndash_data_upgrades_ajax' );


