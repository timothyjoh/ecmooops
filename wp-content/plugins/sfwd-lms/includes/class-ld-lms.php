<?php
/**
 * SFWD_LMS
 * 
 * @since 2.1.0
 * 
 * @package LearnDash
 */


if ( ! class_exists( 'SFWD_LMS' ) ) {

	class SFWD_LMS extends Semper_Fi_Module  {

		public $post_types = array();
		public $cache_key = '';
		public $quiz_json = '';
		public $count = null;


		/**
		 * Set up properties and hooks for this class 
		 */
		function __construct() {
			self::$instance =& $this;
			$this->file = __FILE__;
			$this->name = 'LMS';
			$this->plugin_name = 'SFWD LMS';
			$this->name = 'LMS Options';
			$this->prefix = 'sfwd_lms_';
			$this->parent_option = 'sfwd_lms_options';
			parent::__construct();
			register_activation_hook( $this->plugin_path['basename'], array( $this, 'activate' ) );
			add_action( 'init', array( $this, 'add_post_types' ), 1 );
			//add_action( 'plugins_loaded', array( $this, 'add_post_types' ), 1 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'parse_request', array( $this, 'parse_ipn_request' ) );
			add_action( 'generate_rewrite_rules', array( $this, 'paypal_rewrite_rules' ) );
			add_filter( 'sfwd_cpt_loop', array( $this, 'cpt_loop_filter' ) );
			add_filter( 'edit_term_count', array( $this, 'tax_term_count' ), 10, 3 );
			add_action( 'init', array( $this, 'add_tag_init' ) ); //Initialise the tagging capability here
			add_action( 'plugins_loaded', array( $this, 'i18nize') );	//Add internationalization support
			add_shortcode( 'usermeta', array( $this, 'usermeta_shortcode' ) );

			if ( is_admin() && get_transient( 'sfwd_lms_rewrite_flush' ) ) {
				add_action( 'admin_init', 'flush_rewrite_rules' );
				set_transient( 'sfwd_lms_rewrite_flush', false );
			}

			//add_action( 'init', array( $this, 'upgrade_settings') );
			add_action( 'init', array( $this, 'load_template_functions') );

			if (is_admin()) {
				require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-groups-edit.php' );
				$this->ld_admin_groups_edit = new Learndash_Admin_Groups_Edit;

				require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-settings-support-panel.php' );
				$this->ld_admin_settings_support_panel = new Learndash_Admin_Settings_Support_Panel;

				require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-groups-users-list.php' );
				$this->ld_admin_groups_users_list = new Learndash_Admin_Groups_Users_list();

				require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-settings-data-upgrades.php' );
				$this->ld_admin_settings_data_upgrades = new Learndash_Admin_Settings_Data_Upgrades();

				require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-settings-data-reports.php' );
				$this->ld_admin_settings_data_reports = new Learndash_Admin_Settings_Data_Reports();

				if ((!defined('LEARNDASH_GROUPS_LEGACY_v220') || (LEARNDASH_GROUPS_LEGACY_v220 !== true))) {

					require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-user-profile-edit.php' );
					$this->ld_admin_user_profile_edit = new Learndash_Admin_User_Profile_Edit;

					require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-course-edit.php' );
					$this->ld_admin_course_edit = new Learndash_Admin_Course_Edit;
				}
			}

			add_action( 'wp_ajax_select_a_lesson', array( $this, 'select_a_lesson_ajax' ) );
			add_action( 'wp_ajax_select_a_lesson_or_topic', array( $this, 'select_a_lesson_or_topic_ajax' ) );
		}


		/**
		 * Load functions used for templates
		 *
		 * @since 2.1.0
		 */
		function load_template_functions() {
			$this->get_template( 'learndash_template_functions', array(), true );
		}


		/**
		 * Register Courses, Lessons, Quiz CPT's and set up their admin columns on post list view
		 */
		function add_tag_init()	{
			$tag_args = array( 'taxonomies' => array( 'post_tag', 'category' ) );
			register_post_type( 'sfwd-courses', $tag_args ); //Tag arguments for $post_type='sfwd-courses'
			register_post_type( 'sfwd-lessons', $tag_args ); //Tag arguments for $post_type='sfwd-courses'
			register_post_type( 'sfwd-quiz', $tag_args ); //Tag arguments for $post_type='sfwd-courses'

			add_filter( 'manage_edit-sfwd-lessons_columns', 'add_course_data_columns' );
			add_filter( 'manage_edit-sfwd-quiz_columns', 'add_shortcode_data_columns' );
			add_filter( 'manage_edit-sfwd-quiz_columns', 'add_course_data_columns' );
			add_filter( 'manage_edit-sfwd-topic_columns', 'add_lesson_data_columns' );
			add_filter( 'manage_edit-sfwd-assignment_columns', 'add_lesson_data_columns' );
			add_filter( 'manage_edit-sfwd-assignment_columns', 'add_assignment_data_columns' );
			add_filter( 'manage_edit-sfwd-essays_columns', 'add_essays_data_columns' );

			add_filter( 'manage_edit-sfwd-quiz_columns', 'remove_tags_column' );
			add_filter( 'manage_edit-sfwd-quiz_columns', 'remove_categories_column' );

			add_action( 'manage_sfwd-lessons_posts_custom_column', 'manage_asigned_course_columns', 10, 3 );
			add_action( 'manage_sfwd-quiz_posts_custom_column', 'manage_asigned_course_columns', 10, 3 );
			add_action( 'manage_sfwd-topic_posts_custom_column', 'manage_asigned_course_columns', 10, 3 );
			add_action( 'manage_sfwd-assignment_posts_custom_column', 'manage_asigned_course_columns', 10, 3 );
			add_action( 'manage_sfwd-assignment_posts_custom_column', 'manage_asigned_assignment_columns', 10, 3 );

			add_action( 'restrict_manage_posts', 'restrict_listings_by_course' );
			add_filter( 'parse_query', 'course_table_filter' );
		}



		/**
		 * Loads the plugin's translated strings
		 *
		 * @since 2.1.0
		 */
		function i18nize() {
			
			//$locale = apply_filters( 'plugin_locale', get_locale(), 'learndash' ); 
			//load_textdomain( 'learndash', WP_LANG_DIR . '/learndash/learndash-' . $locale . '.mo' ); 
			//load_plugin_textdomain('learndash', false, dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/languages' ); 
			
			if ((defined('LD_LANG_DIR')) && (LD_LANG_DIR)) {
				load_plugin_textdomain( 'learndash', false, LD_LANG_DIR );
			} else {
				load_plugin_textdomain( 'learndash', false, dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/languages/' );
			}
		}



		/**
		 * Update count of posts with a term
		 * 
		 * Callback for add_filter 'edit_term_count'
		 * There is no apply_filters or php call to execute this function
		 *
		 * @todo  consider for deprecation, other docblock tags removed
		 *
		 * @since 2.1.0
		 */
		function tax_term_count( $columns, $id, $tax ) {
			if ( empty( $tax ) || ( $tax != 'courses' ) ) { 
				return $columns;
			}

			if ( ! empty( $_GET ) && ! empty( $_GET['post_type'] ) ) {
				$post_type = $_GET['post_type'];
				$wpq = array(		
					'tax_query' => array( 
						array( 
							'taxonomy' => $tax, 
							'field' => 'id', 
							'terms' => $id 
						)
					),
					'post_type' => $post_type,
					'post_status' => 'publish',
					'posts_per_page' => -1
				);
				$q = new WP_Query( $wpq );
				$this->count = $q->found_posts;
				add_filter( 'number_format_i18n', array( $this, 'column_term_number' ) );
			}

			return $columns;			
		}


		/**
		 * Set column term number
		 * 
		 * This function is called by the 'tax_term_count' method and is no longer being ran
		 * See tax_term_count()
		 *
		 * @todo  consider for deprecation, other docblock tags removed
		 *
		 * @since 2.1.0
		 */
		function column_term_number( $number ) {
			remove_filter( 'number_format_i18n', array( $this, 'column_term_number' ) );
			if ( $this->count !== null ) {
				$number = $this->count;
				$this->count = null;
			}
			return $number;
		}



		/**
		 * [usermeta] shortcode
		 * 
		 * This shortcode takes a parameter named field, which is the name of the user meta data field to be displayed.
		 * Example: [usermeta field="display_name"] would display the user's Display Name.
		 *
		 * @since 2.1.0
		 * 
		 * @param  array 	$attr    shortcode attributes
		 * @param  string 	$content content of shortcode
		 * @return string          	 output of shortcode
		 */
		function usermeta_shortcode( $attr, $content = null ) {
			global $learndash_shortcode_used;
			$learndash_shortcode_used = true;
			
			extract( shortcode_atts( array( 'field' => null ), $attr ) );
			global $user_info, $user_ID;
			if ( is_user_logged_in() ) {
				
				$user_id = get_current_user_id();

				/**
				 * Added logic to allow admin and group_leader to view certificate from other users. 
				 * @since 2.3
				 */
				$post_type = '';
				if ( get_query_var( 'post_type' ) ) {
					$post_type = get_query_var( 'post_type' );
				}

				if ( $post_type == 'sfwd-certificates' ) {
					if ( ( ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) && ( ( isset( $_GET['user'] ) ) && (!empty( $_GET['user'] ) ) ) ) {
						$user_id = intval( $_GET['user'] );
					}
				}
					
				$user_info = get_userdata( $user_id );
				
				return $user_info->$field;
			}
			return '';
		}



		/**
		 * Callback for add_filter 'sfwd_cpt_loop'
		 * There is no apply_filters or php call to execute this function
		 *
		 * @since 2.1.0
		 * 
		 * @todo  consider for deprecation, other docblock tags removed
		 */
		function cpt_loop_filter( $content ) {
			global $post;
			if ( $post->post_type == 'sfwd-quiz' ) {
				$meta = get_post_meta( $post->ID, '_sfwd-quiz' );
				if ( is_array( $meta ) && ! empty( $meta ) ) {
					$meta = $meta[0];
					if ( is_array( $meta ) && ( ! empty( $meta['sfwd-quiz_lesson'] ) ) ) {
						$content = '';
					}
				}
			}
			return $content;
		}



		/**
		 * Fire on plugin activation
		 * 
		 * Currently sets 'sfwd_lms_rewrite_flush' to true
		 *
		 * @todo   consider if needed, transient is not being used anywhere else in LearnDash
		 * 
		 * @since 2.1.0
		 */
		function activate() {
			set_transient( 'sfwd_lms_rewrite_flush', true );
		}



		/**
		 * Add 'sfwd-lms' to query vars
		 * Fired on filter 'query_vars'
		 * 
		 * @since 2.1.0
		 * 
		 * @param  array  	$vars  query vars
		 * @return array 	$vars  query vars
		 */
		function add_query_vars( $vars ) {
			$courses_options = learndash_get_option( 'sfwd-courses' );
			if ((isset($courses_options['paypal_email'])) && (!empty($courses_options['paypal_email']))) {
				$vars = array_merge( array( 'sfwd-lms' ), $vars );
			}
			
			return $vars;
		}



		/**
		 * Include PayPal IPN if request is for PayPal IPN
		 * Fired on action 'parse_request'
		 * 
		 * @since 2.1.0
		 * 
		 * @param  object 	$wp  wp query
		 */
		function parse_ipn_request( $wp ) {
			$courses_options = learndash_get_option( 'sfwd-courses' );
			if ((isset($courses_options['paypal_email'])) && (!empty($courses_options['paypal_email']))) {
			
				if ( array_key_exists( 'sfwd-lms', $wp->query_vars )
						&& $wp->query_vars['sfwd-lms'] == 'paypal' ) {
				
					/**
					 * include PayPal IPN
					 */
					require_once( 'vendor/paypal/ipn.php' );
				}
			}
		}



		/**
		 * Adds paypal to already generated rewrite rules
		 * Fired on action 'generate_rewrite_rules'
		 *
		 * @since 2.1.0
		 * 
		 * @param  object  $wp_rewrite
		 */
		function paypal_rewrite_rules( $wp_rewrite ) {
			
			$courses_options = learndash_get_option( 'sfwd-courses' );
			if ((isset($courses_options['paypal_email'])) && (!empty($courses_options['paypal_email']))) {
				$wp_rewrite->rules = array_merge( array( 'sfwd-lms/paypal' => 'index.php?sfwd-lms=paypal' ), $wp_rewrite->rules );
			}
		}



		/**
		 * Sets up CPT's and creates a 'new SFWD_CPT_Instance()' of each
		 * 
		 * @since 2.1.0
		 */
		function add_post_types() {
			$post = 0;

			if ( is_admin() && ! empty( $_GET ) && ( isset( $_GET['post'] ) ) ) {
				$post_id = $_GET['post'];
			}

			if ( ! empty( $post_id ) ) {
				$this->quiz_json = get_post_meta( $post_id, '_quizdata', true );
				if ( ! empty( $this->quiz_json ) ) {
					$this->quiz_json = $this->quiz_json['workingJson'];
				}
			}

			$options = get_option( 'sfwd_cpt_options' );

			$level1 = $level2 = $level3 = $level4 = $level5 = '';

			if ( ! empty( $options['modules'] ) ) {
				$options = $options['modules'];
				if ( ! empty( $options['sfwd-quiz_options'] ) ) {
					$options = $options['sfwd-quiz_options'];
					foreach ( array( 'level1', 'level2', 'level3', 'level4', 'level5' ) as $level ) {
						$$level = '';
						if ( ! empty( $options["sfwd-quiz_{$level}"] ) ) {
							$$level = $options["sfwd-quiz_{$level}"];
						}
					}
				}
			}

			if ( empty( $this->quiz_json ) ) { 
				$this->quiz_json = '{"info":{"name":"","main":"","results":"","level1":"' . $level1 . '","level2":"' . $level2 . '","level3":"' . $level3 . '","level4":"' . $level4 . '","level5":"' . $level5 . '"}}';
			}
			
			$posts_per_page = get_option( 'posts_per_page' );

			$course_capabilities = array(
				'read_post' => 'read_course',
				'publish_posts' => 'publish_courses',
				'edit_posts' => 'edit_courses',
				'edit_others_posts' => 'edit_others_courses',
				'delete_posts' => 'delete_courses',
				'delete_others_posts' => 'delete_others_courses',
				'read_private_posts' => 'read_private_courses',
				'edit_private_posts' => 'edit_private_courses',
				'delete_private_posts' => 'delete_private_courses',
				'delete_post' => 'delete_course',
				'edit_published_posts'	=> 'edit_published_courses',
				'delete_published_posts'	=> 'delete_published_courses',
			);

			if ( is_admin() ) {
				$admin_role = get_role( 'administrator' );
				if ( ( $admin_role ) && ( $admin_role instanceof WP_Role ) ) {
					if ( ! $admin_role->has_cap( 'delete_private_courses' ) ) {
						foreach ( $course_capabilities as $key => $cap ) {
							if ( ! $admin_role->has_cap( $cap ) ) {
								$admin_role->add_cap( $cap );
							}
						}
					}
					if ( ! $admin_role->has_cap( 'enroll_users' ) ) {
						$admin_role->add_cap( 'enroll_users' );
					}
				}
			}

			$lcl_topic  = LearnDash_Custom_Label::get_label( 'topic' );
			$lcl_topics = LearnDash_Custom_Label::get_label( 'topics' );

			$lesson_topic_labels = array(
				'name' 					=> 	$lcl_topics,
				'singular_name' 		=> 	$lcl_topic,
				'add_new' 				=> 	_x( 'Add New', 'Add New Topic Label', 'learndash' ),
				'add_new_item' 			=> 	sprintf( _x( 'Add New %s', 'Add New Topic Label', 'learndash' ), $lcl_topic ),
				'edit_item' 			=> 	sprintf( _x( 'Edit %s', 'Edit Topic Label', 'learndash' ), $lcl_topic ),
				'new_item' 				=> 	sprintf( _x( 'New %s', 'New Topic Label', 'learndash' ), $lcl_topic ),
				'all_items' 			=> 	$lcl_topics,
				'view_item' 			=> 	sprintf( _x( 'View %s', 'View Topic Label', 'learndash' ), $lcl_topic ),
				'search_items' 			=> 	sprintf( _x( 'Search %s', 'Search Topic Label', 'learndash' ), $lcl_topics ),
				'not_found' 			=> 	sprintf( _x( 'No %s found', 'No Topic found Label', 'learndash' ), $lcl_topics ),
				'not_found_in_trash' 	=> 	sprintf( _x( 'No %s found in Trash', 'No Topic found in Trash', 'learndash' ), $lcl_topics ),
				'parent_item_colon' 	=> 	'',
				'menu_name' 			=> 	$lcl_topics
			);

			$lcl_quiz    = LearnDash_Custom_Label::get_label( 'quiz' );
			$lcl_quizzes = LearnDash_Custom_Label::get_label( 'quizzes' );

			$quiz_labels = array(
				'name' 					=> 	$lcl_quizzes,
				'singular_name' 		=> 	$lcl_quiz,
				'add_new' 				=> 	_x( 'Add New', 'Add New Quiz Label', 'learndash' ),
				'add_new_item' 			=> 	sprintf( _x( 'Add New %s', 'Add New Quiz Label', 'learndash' ), $lcl_quiz ),
				'edit_item' 			=> 	sprintf( _x( 'Edit %s', 'Edit Quiz Label', 'learndash' ), $lcl_quiz ),
				'new_item' 				=> 	sprintf( _x( 'New %s', 'New Quiz Label', 'learndash' ), $lcl_quiz ),
				'all_items' 			=> 	$lcl_quizzes,
				'view_item' 			=> 	sprintf( _x( 'View %s', 'View Quiz Label', 'learndash' ), $lcl_quiz ),
				'search_items' 			=> 	sprintf( _x( 'Search %s', 'Search Quiz Label', 'learndash' ), $lcl_quizzes ),
				'not_found' 			=> 	sprintf( _x( 'No %s found', 'No Quiz found Label', 'learndash' ), $lcl_quizzes ),
				'not_found_in_trash' 	=> 	sprintf( _x( 'No %s found in Trash', 'No Quiz found in Trash Label', 'learndash' ), $lcl_quizzes ),
				'parent_item_colon' 	=> 	'',
			);

			$lcl_lesson  = LearnDash_Custom_Label::get_label( 'lesson' );
			$lcl_lessons = LearnDash_Custom_Label::get_label( 'lessons' );

			$lesson_labels = array(
				'name' 					=> 	$lcl_lessons,
				'singular_name' 		=> 	$lcl_lesson,
				'add_new' 				=> 	_x( 'Add New', 'Add New Lesson Label', 'learndash' ),
				'add_new_item' 			=> 	sprintf( _x( 'Add New %s', 'Add New Lesson Label', 'learndash' ), $lcl_lesson ),
				'edit_item' 			=> 	sprintf( _x( 'Edit %s', 'Edit Lesson Label', 'learndash' ), $lcl_lesson ),
				'new_item' 				=> 	sprintf( _x( 'New %s', 'New Lesson Label', 'learndash' ), $lcl_lesson ),
				'all_items' 			=> 	$lcl_lessons,
				'view_item' 			=> 	sprintf( _x( 'View %s', 'View Lesson Label', 'learndash' ), $lcl_lesson ),
				'search_items' 			=> 	sprintf( _x( 'Search %s', 'Search Lesson Label', 'learndash' ), $lcl_lessons ),
				'not_found' 			=> 	sprintf( _x( 'No %s found', 'No Lesson found Label', 'learndash' ), $lcl_lessons ),
				'not_found_in_trash' 	=> 	sprintf( _x( 'No %s found in Trash', 'No Lesson found in Trash Label', 'learndash' ), $lcl_lessons ),
				'parent_item_colon' 	=> 	'',
			);

			$lcl_course  = LearnDash_Custom_Label::get_label( 'course' );
			$lcl_courses = LearnDash_Custom_Label::get_label( 'courses' );

			$course_labels = array(
				'name' 					=> 	$lcl_courses,
				'singular_name' 		=> 	$lcl_course,
				'add_new' 				=> 	_x( 'Add New', 'Add New Course Label', 'learndash' ),
				'add_new_item' 			=> 	sprintf( _x( 'Add New %s', 'Add New Course Label', 'learndash' ), $lcl_course ),
				'edit_item' 			=> 	sprintf( _x( 'Edit %s', 'Edit Course Label', 'learndash' ), $lcl_course ),
				'new_item' 				=> 	sprintf( _x( 'New %s', 'New Course Label', 'learndash' ), $lcl_course ),
				'all_items' 			=> 	$lcl_courses,
				'view_item' 			=> 	sprintf( _x( 'View %s', 'View Course Label', 'learndash' ), $lcl_course ),
				'search_items' 			=> 	sprintf( _x( 'Search %s', 'Search Courses Label', 'learndash' ), $lcl_courses ),
				'not_found' 			=> 	sprintf( _x( 'No %s found', 'No Courses found Label', 'learndash' ), $lcl_courses ),
				'not_found_in_trash' 	=> 	sprintf( _x( 'No %s found in Trash', 'No Courses found in Trash Label', 'learndash' ), $lcl_courses ),
				'parent_item_colon' 	=> 	'',
			);

			if ( empty( $posts_per_page ) ) { 
				$posts_per_page = 5;
			}

			$post_args = array(
				'sfwd-courses' => array(
					'plugin_name' => LearnDash_Custom_Label::get_label( 'course' ),
					'slug_name' => LearnDash_Custom_Label::label_to_slug( 'courses' ),
					'post_type' => 'sfwd-courses',
					'template_redirect' => true,
					// 'taxonomies' => array( 'courses' => __( 'Manage Course Associations', 'learndash' ) ),
					'cpt_options' => array( 
						'hierarchical' => false, 
						'supports' => array( 'title', 'editor', 'thumbnail' , 'author', 'comments', 'revisions', 'page-attributes' ),
						'labels' => $course_labels,
						'capability_type' => 'course',
						'capabilities' => $course_capabilities,
						'map_meta_cap' => true
					),
					'options_page_title' => __( 'PayPal Settings', 'learndash' ),
					'fields' => array( 
						'course_materials' => array(
							'name' => sprintf( _x( '%s Materials', 'Course Materials Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
							'type' => 'textarea',
							'help_text' => sprintf( _x( 'Options for %s materials', 'Options for course materials', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
						),
						'course_price_type' => array(
							'name' => sprintf( _x( '%s Price Type', 'Course Price Type Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
							'type' => 'select',
							'initial_options' => array(	
								'open' => __( 'Open', 'learndash' ),
								'closed' => __( 'Closed', 'learndash' ),
								'free' => __( 'Free', 'learndash' ),
								'paynow' => __( 'Buy Now', 'learndash' ),
								'subscribe'	=> __( 'Recurring', 'learndash' ),
							),
							'default' => 'open',
							'help_text' => __( 'Is it open to all, free join, one time purchase, or a recurring subscription?', 'learndash' ),
						),
						'custom_button_url' => array(
							'name' => __( 'Custom Button URL', 'learndash' ),
							'type' => 'text',
							'placeholder'	=> __( 'Optional', 'learndash' ),
							'help_text' => sprintf( _x( 'Entering a URL in this field will enable the "%s" button. The button will not display if this field is left empty.', 'placeholders: "Take This Course" button label', 'learndash' ), LearnDash_Custom_Label::get_label( 'button_take_this_course' )),
						),
						'course_price' => array(
							'name' => sprintf( _x( '%s Price', 'Course Price Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
							'type' => 'text',
							'help_text' => sprintf( _x( 'Enter %s price here. Leave empty if the %s is free.', 'Enter course price here. Leave empty if the course is free.', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ), LearnDash_Custom_Label::get_label( 'course' ) ),
						),
						'course_price_billing_cycle' => array(
							'name' => __( 'Billing Cycle', 'learndash' ),
							'type' => 'html',
							'default' => $this->learndash_course_price_billing_cycle_html(),
							'help_text' => __( 'Billing Cycle for the recurring payments in case of a subscription.', 'learndash' ),
						),
						'course_access_list' => array(
							'name' => sprintf( _x( '%s Access List', 'Course Access List Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
							'type' => 'textarea',
							'help_text' => __( 'This field is auto-populated with the UserIDs of those who have access to this course.', 'learndash' ),
						),
						'course_lesson_orderby' => array(
							'name' => sprintf( _x( 'Sort %s By', 'Sort Lesson By Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
							'type' => 'select',
							'initial_options' => array(	
								''		=> __( 'Use Default', 'learndash' ),
								'title'	=> __( 'Title', 'learndash' ),
								'date'	=> __( 'Date', 'learndash' ),
								'menu_order' => __( 'Menu Order', 'learndash' ),
							),
							'default' => '',
							'help_text' => sprintf( _x( 'Choose the sort order of %s in this %s.', 'Choose the sort order of lessons in this course.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('lessons'), LearnDash_Custom_Label::label_to_lower('course') )
						),
						'course_lesson_order' => array(
							'name' => sprintf( _x( 'Sort %s Direction', 'Sort Lesson Direction Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
							'type' => 'select',
							'initial_options' => array(	
								''		=> __( 'Use Default', 'learndash' ),
								'ASC'	=> __( 'Ascending', 'learndash' ),
								'DESC'	=> __( 'Descending', 'learndash' ),
							),
							'default' => '',
							'help_text' => sprintf( _x( 'Choose the sort order of %s in this %s.', 'Choose the sort order of lessons in this course.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('lessons'), LearnDash_Custom_Label::label_to_lower('course')),
						),
						'course_prerequisite' => array( 
							'name' => sprintf( _x( '%s prerequisites', 'Course prerequisites Label', 'learndash' ),LearnDash_Custom_Label::get_label( 'course' ) ), 
							'type' => 'select', 
							'help_text' => sprintf( _x( 'Select a %s as prerequisites to view this %s', 'Select a course as prerequisites to view this course', 'learndash' ), LearnDash_Custom_Label::label_to_lower('course'), LearnDash_Custom_Label::label_to_lower('course') ), 
							'lazy_load'	=>	true,
							'initial_options' => '', 
							'default' => '',
						),
						'course_disable_lesson_progression' => array(
							'name' => sprintf( _x( 'Disable %s Progression', 'Disable Lesson Progression Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
							'type' => 'checkbox',
							'default' => 0,
							'help_text' => sprintf( _x( 'Disable the feature that allows attempting %s only in allowed order.', 'Disable the feature that allows attempting lessons only in allowed order.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('lessons') ), 
						),
						'expire_access' => array(
							'name' => __( 'Expire Access', 'learndash' ),
							'type' => 'checkbox',
							'help_text' => __( 'Leave this field unchecked if access never expires.', 'learndash' ),
						),

						'expire_access_days' => array(
							'name' => __( 'Expire Access After (days)', 'learndash' ),
							'type' => 'number',
							'min' => '0',
							'help_text' => sprintf( _x( 'Enter the number of days a user has access to this %s.', 'Enter the number of days a user has access to this course.', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
						),
						'expire_access_delete_progress' => array(
							'name' => sprintf( _x( 'Delete %s and %s Data After Expiration', 'Delete Course and Quiz Data After Expiration Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
							'type' => 'checkbox',
							'help_text' => sprintf( _x( 'Select this option if you want the user\'s %s progress to be deleted when their access expires.', 'Select this option if you want the user\'s course progress to be deleted when their access expires.', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
						),
						'course_disable_content_table' => array(
							'name' => sprintf( _x( 'Hide %s Content table', 'Hide Course Content table Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
							'type' => 'checkbox',
							'default' => 0,
							'help_text' => sprintf( _x( 'Hide %s Content table when user is not enrolled.', 'Hide Course Content table when user is not enrolled.', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ), 
						),
						
						'certificate' => array( 
							'name' => __( 'Associated Certificate', 'learndash' ), 
							'type' => 'select', 
							'help_text' => sprintf( _x( 'Select a certificate to be awarded upon %s completion (optional).', 'Select a certificate to be awarded upon course completion (optional).', 'learndash' ), LearnDash_Custom_Label::label_to_lower('course') ), 
							'default' => '' 
						),
					),
					'default_options' => array(
						'paypal_email' => array( 
							'name' => __( 'PayPal Email', 'learndash' ), 
							'help_text' => __( 'Enter your PayPal email here.', 'learndash' ), 
							'type' => 'text',
						),
						'paypal_currency' => array( 
							'name' => __( 'PayPal Currency', 'learndash' ), 
							'help_text' => __( 'Enter the currency code for transactions.', 'learndash' ), 
							'type' => 'text', 
							'default' => 'USD',
						),
						'paypal_country' => array( 
							'name' => __( 'PayPal Country', 'learndash' ), 
							'help_text' => __( 'Enter your country code here.', 'learndash' ), 
							'type' => 'text', 
							'default' => 'US',
						),
						'paypal_cancelurl' => array( 
							'name' => __( 'PayPal Cancel URL', 'learndash' ), 
							'help_text' => __( 'Enter the URL used for purchase cancellations.', 'learndash' ), 
							'type' => 'text', 
							'default' => get_home_url(),
						),
						'paypal_returnurl' => array( 
							'name' => __( 'PayPal Return URL', 'learndash' ), 
							'help_text' => __( 'Enter the URL used for completed purchases (typically a thank you page).', 'learndash' ), 
							'type' => 'text', 
							'default' => get_home_url(),
						),
						'paypal_notifyurl' => array( 
							'name' => __( 'PayPal Notify URL', 'learndash' ), 
							'help_text' => __( 'Enter the URL used for IPN notifications.', 'learndash' ), 
							'type' => 'text', 
							'default' => get_home_url() . '/sfwd-lms/paypal',
						),
						'paypal_sandbox' => array( 
							'name' => __( 'Use PayPal Sandbox', 'learndash' ), 
							'help_text' => __( 'Check to enable the PayPal sandbox.', 'learndash' ),
						),
					),
				),
				'sfwd-lessons' => array(
					'plugin_name' => LearnDash_Custom_Label::get_label( 'lesson' ),
					'slug_name' => LearnDash_Custom_Label::label_to_slug( 'lessons' ),
					'post_type' => 'sfwd-lessons',
					'template_redirect' => true,
					// 'taxonomies' => array( 'courses' => __( 'Manage Course Associations', 'learndash' ) ),
					'cpt_options' => array( 
						'has_archive' => false, 
						'supports' => array( 'title', 'thumbnail', 'editor', 'page-attributes' , 'author', 'comments', 'revisions'), 
						'labels' => $lesson_labels , 
						'capability_type' => 'course', 
						'capabilities' => $course_capabilities, 
						'map_meta_cap' => true,
					),
					'options_page_title' => sprintf( _x( '%s Options', 'Lesson Options Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
					'fields' => array(
						'course' => array( 
							'name' => sprintf( _x( 'Associated %s', 'Associated Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ), 
							'type' => 'select', 
							'lazy_load'	=> true,
							'help_text' => sprintf( _x( 'Associate with a %s.', 'Associate with a course.', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
							'default' => '' , 
							//'initial_options' => $this->select_a_course( 'sfwd-lessons' ), // Move to lesson_display_settings
						),
						'forced_lesson_time' => array( 
							'name' => sprintf( _x( 'Forced %s Timer', 'Forced Lesson Timer Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ), 
							'type' => 'text', 
							'help_text' => sprintf( _x( 'Minimum time a user has to spend on %s page before it can be marked complete. Examples: 40 (for 40 seconds), 20s, 45sec, 2m 30s, 2min 30sec, 1h 5m 10s, 1hr 5min 10sec', 'Minimum time a user has to spend on Lesson page Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ), 
							'default' => '',
						),
						'lesson_assignment_upload' => array( 
							'name' => __( 'Upload Assignment', 'learndash' ), 
							'type' => 'checkbox', 
							'help_text' => __( 'Check this if you want to make it mandatory to upload assignment', 'learndash' ), 
							'default' => 0,
						),
						'auto_approve_assignment' => array( 
							'name' => __( 'Auto Approve Assignment', 'learndash' ), 
							'type' => 'checkbox',
							'help_text' => __( 'Check this if you want to auto-approve the uploaded assignment', 'learndash' ), 
							'default' => 0,
						),
						'lesson_assignment_points_enabled' => array(
							'name' => __( 'Award Points for Assignment', 'learndash' ),
							'type' => 'checkbox',
							'help_text' => __( 'Allow this assignment to be assigned points when it is approved.', 'learndash' ),
							'default' => 0,
						),
						'lesson_assignment_points_amount' => array(
							'name' => __( 'Set Number of Points for Assignment', 'learndash' ),
							'type' => 'number',
							'min' => 0,
							'help_text' => __( 'Assign the max amount of points someone can earn for this assignment.', 'learndash' ),
							'default' => 0,
						),
						'sample_lesson' => array( 
							'name' => sprintf( _x( 'Sample %s', 'Sample Lesson Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ), 
							'type' => 'checkbox', 
							'help_text' => sprintf( _x( 'Check this if you want this %s and all its %s to be available for free.', 'Check this if you want this lesson and all its topics to be available for free.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('lesson'), LearnDash_Custom_Label::label_to_lower('topics') ),
							'default' => 0,
						),
						'visible_after' => array( 
							'name' => sprintf( _x( 'Make %s visible X days after sign-up', 'Make lesson visible X days after sign-up', 'learndash' ), LearnDash_Custom_Label::label_to_lower('lesson') ), 
							'type' => 'number', 
							'min' => '0',
							'help_text' => sprintf( _x( 'Make %s visible ____ days after sign-up', 'Make lesson visible ____ days after sign-up', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
							'default' => 0,
						),
						'visible_after_specific_date' => array( 
							'name' => sprintf( _x( 'Make %s visible on specific date', 'Make lesson visible on specific date', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
							'type' => 'text', 
							'class' => 'learndash-datepicker-field',
							'help_text' => sprintf( _x( 'Set the date that you would like this %s to become available.', 'Set the date that you would like this lesson to become available.','learndash' ), LearnDash_Custom_Label::label_to_lower('lesson') ), 
						),
					),
					'default_options' => array(
						'orderby' => array(
							'name' => __( 'Sort By', 'learndash' ),
							'type' => 'select',
							'initial_options' => array(	
								''		=> __( 'Select a choice...', 'learndash' ),
								'title'	=> __( 'Title', 'learndash' ),
								'date'	=> __( 'Date', 'learndash' ),
								'menu_order' => __( 'Menu Order', 'learndash' ),
							),
							'default' => 'date',
							'help_text' => __( 'Choose the sort order.', 'learndash' ),
						),
						'order' => array(
							'name' => __( 'Sort Direction', 'learndash' ),
							'type' => 'select',
							'initial_options' => array(	
								''		=> __( 'Select a choice...', 'learndash' ),
								'ASC'	=> __( 'Ascending', 'learndash' ),
								'DESC'	=> __( 'Descending', 'learndash' ),
							),
							'default' => 'DESC',
							'help_text' => __( 'Choose the sort order.', 'learndash' ),
						),
						'posts_per_page' => array(
							'name' => __( 'Posts Per Page', 'learndash' ),
							'type' => 'text',
							'help_text' => __( 'Enter the number of posts to display per page.', 'learndash' ),
							'default' => $posts_per_page,
						),
					)
				),
				'sfwd-quiz' => array(
					'plugin_name' => LearnDash_Custom_Label::get_label( 'quiz' ),
					'slug_name' => LearnDash_Custom_Label::label_to_slug( 'quizzes' ),
					'post_type' => 'sfwd-quiz',
					'template_redirect' => true,
					// 'taxonomies' => array( 'courses' => __( 'Manage Course Associations', 'learndash' ) ),
					'cpt_options' => array(	
						'hierarchical' => false, 
						'supports' => array( 'title', 'thumbnail', 'editor' , 'author', 'page-attributes' ,'comments', 'revisions' ), 
						'labels' => $quiz_labels, 
						'capability_type' => 'course', 
						'capabilities' => $course_capabilities, 
						'map_meta_cap' => true
					),
					'options_page_title' => sprintf( _x( '%s Settings', 'Quiz Settings Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
					'fields' => array(
						'repeats' => array( 
							'name' => __( 'Repeats', 'learndash' ), 
							'type' => 'text', 
							'help_text' => sprintf( _x( 'Number of repeats allowed for %s', 'Number of repeats allowed for quiz', 'learndash' ), LearnDash_Custom_Label::label_to_lower('quiz') ),
							'default' => '',
						),
						'threshold' => array( 
							'name' => __( 'Certificate Threshold', 'learndash' ), 
							'type' => 'text', 
							'help_text' => __( 'Minimum score required to award a certificate, between 0 and 1 where 1 = 100%.', 'learndash' ), 
							'default' => '0.8',
						),
						'passingpercentage' => array( 
							'name' => __( 'Passing Percentage', 'learndash' ), 
							'type' => 'text', 
							'help_text' => sprintf( _x( 'Passing percentage required to pass the %s (number only). e.g. 80 for 80%%.', 'Passing percentage required to pass the quiz (number only). e.g. 80 for 80%.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('quiz') ),
							'default' => '80',
						),
						'course' => array( 
							'name' => sprintf( _x( 'Associated %s', 'Associated Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ), 
							'type' => 'select', 
							'lazy_load'	=> true,
							'help_text' => sprintf( _x( 'Associate with a %s.', 'Associate with a course.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('course') ),
							'default' => '', 
							//'initial_options' => $this->select_a_course( 'sfwd-quiz' ), // Move to quiz_display_settings
						),
						'lesson' => array( 
							'name' => sprintf( _x( 'Associated %s', 'Associated Lesson Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ), 
							'type' => 'select', 
							'help_text' => sprintf( _x( 'Optionally associate a %s with a %s.', 'Optionally associate a quiz with a lesson.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('quiz'), LearnDash_Custom_Label::label_to_lower('lesson') ),
							'default' => '', 
						),
						'certificate' => array( 
							'name' => __( 'Associated Certificate', 'learndash' ), 
							'type' => 'select', 
							'help_text' => sprintf( _x( 'Optionally associate a %s with a certificate.', 'Optionally associate a quiz with a certificate.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('quiz') ),
							'default' => '',
						),
						'quiz_pro' => array( 
							'name' => __( 'Associated Settings', 'learndash' ), 
							'type' => 'select', 
							'help_text' => sprintf( _x( 'If you imported a %s, use this field to select it. Otherwise, create new settings below. After saving or publishing, you will be able to add questions.', 'If you imported a quiz, use this field to select it. Otherwise, create new settings below. After saving or publishing, you will be able to add questions.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('quiz') )  . '<a style="display:none" id="advanced_quiz_preview" class="wpProQuiz_prview" href="#">'.__( 'Preview', 'learndash' ).'</a>',
							//'initial_options' => ( array( 0 => __( '-- Select Settings --', 'learndash' ) ) + LD_QuizPro::get_quiz_list() ), // Move to quiz_display_settings
							'default' => '',
						),
						'quiz_pro_html' => array(
							'name' => sprintf( _x( '%s Options', 'Quiz Options Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
							'type' => 'html',
							'help_text' => '',
							'label' => 'none',
							'save' => false,
							'default' => LD_QuizPro::edithtml()
						),
					),
					'default_options' => array()
				),
				'sfwd-topic' => array(
					'plugin_name' => sprintf( _x( '%s %s', 'Lesson Topic Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ), LearnDash_Custom_Label::get_label( 'topic' ) ),
					'slug_name' => LearnDash_Custom_Label::label_to_slug( 'topic' ),
					'post_type' => 'sfwd-topic',
					'template_redirect' => true,
					//'taxonomies' => array( 'courses' => __( 'Manage Course Associations', 'learndash' ) ),
					'cpt_options' => array( 
						'supports' => array( 'title', 'thumbnail', 'editor', 'page-attributes' , 'author', 'comments', 'revisions'),
						'has_archive' => false, 
						'labels' => $lesson_topic_labels, 
						'capability_type' => 'course', 
						'capabilities' => $course_capabilities, 
						'map_meta_cap' => true,
						'taxonomies' => array( 'post_tag'),
					),
					'options_page_title' => sprintf( _x( '%s %s Options', 'Lesson Topic Options Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ), LearnDash_Custom_Label::get_label( 'topic' ) ),
					'fields' => array(
						'course' => array( 
							'name' => sprintf( _x( 'Associated %s', 'Associated Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ), 
							'type' => 'select', 
							'lazy_load'	=> true,
							'help_text' => sprintf( _x( 'Associate with a %s.', 'placeholders: course', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ), 
							'default' => '', 
							//'initial_options' => $this->select_a_course( 'sfwd-topic' ),	// Move to topic_display_settings
						),
						'lesson' => array( 
							'name' => sprintf( _x( 'Associated %s', 'Associated Lesson Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ), 
							'type' => 'select', 
							'lazy_load'	=> true,
							'help_text' => sprintf( _x( 'Optionally associate a %s with a %s.', 'Optionally associate a quiz with a lesson.', 'learndash' ), LearnDash_Custom_Label::label_to_lower('quiz'), LearnDash_Custom_Label::label_to_lower('lesson') ),
							'default' => '' , 
							//'initial_options' => $this->select_a_lesson(), // // Move to topic_display_settings
						),
						'forced_lesson_time' => array( 
							'name' => sprintf( _x( 'Forced %s Timer', 'Forced Topic Timer Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'topic' ) ), 
							'type' => 'text', 
							'help_text' => sprintf( _x( 'Minimum time a user has to spend on %s page before it can be marked complete. Examples: 40 (for 40 seconds), 20s, 45sec, 2m 30s, 2min 30sec, 1h 5m 10s, 1hr 5min 10sec', 'Minimum time a user has to spend on Topic page Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'topic' ) ), 
							'default' => '',
						),
						'lesson_assignment_upload' => array( 
							'name' => __( 'Upload Assignment', 'learndash' ), 
							'type' => 'checkbox', 
							'help_text' => __( 'Check this if you want to make it mandatory to upload assignment', 'learndash' ), 
							'default' => 0,
						),
						'auto_approve_assignment' => array( 
							'name' => __( 'Auto Approve Assignment', 'learndash' ), 
							'type' => 'checkbox', 
							'help_text' => __( 'Check this if you want to auto-approve the uploaded assignment', 'learndash' ), 
							'default' => 0 
						),
						'lesson_assignment_points_enabled' => array(
							'name' => __( 'Award Points for Assignment', 'learndash' ),
							'type' => 'checkbox',
							'help_text' => __( 'Allow this assignment to be assigned points when it is approved.', 'learndash' ),
							'default' => 0,
						),
						'lesson_assignment_points_amount' => array(
							'name' => __( 'Set Number of Points for Assignment', 'learndash' ),
							'type' => 'number',
							'min' => 0,
							'help_text' => __( 'Assign the max amount of points someone can earn for this assignment.', 'learndash' ),
							'default' => 0,
						),
						// 'visible_after' => array( 
						// 	'name' => __( 'Make lesson visible X days after sign-up', 'learndash' ), 
						// 	'type' => 'text', 
						// 	'help_text' => __( 'Make lesson visible ____ days after sign-up', 'learndash' ), 
						// 	'default' => 0,
						// ),
					),
					'default_options' => array(
						'orderby' => array(
							'name' => __( 'Sort By', 'learndash' ),
							'type' => 'select',
							'initial_options' => array(	''		=> __( 'Select a choice...', 'learndash' ),
								'title'	=> __( 'Title', 'learndash' ),
								'date'	=> __( 'Date', 'learndash' ),
								'menu_order' => __( 'Menu Order', 'learndash' ),
							),
							'default' => 'date',
							'help_text' => __( 'Choose the sort order.', 'learndash' ),
							),
						'order' => array(
							'name' => __( 'Sort Direction', 'learndash' ),
							'type' => 'select',
							'initial_options' => array(	''		=> __( 'Select a choice...', 'learndash' ),
									'ASC'	=> __( 'Ascending', 'learndash' ),
									'DESC'	=> __( 'Descending', 'learndash' ),
							),
							'default' => 'DESC',
							'help_text' => __( 'Choose the sort order.', 'learndash' ),
						),
					),
				),
				/*	array(
					  'plugin_name' => __( 'Assignment', 'learndash' ),
					  'slug_name' => 'assignment',
					  'post_type' => 'sfwd-assignment',
					  'template_redirect' => true,
					  'cpt_options' => array( 'supports' => array ( 'title', 'comments', 'author' ), 'exclude_from_search' => true, 'publicly_queryable' => true, 'show_in_nav_menus' => false , 'show_in_menu'	=> true, 'has_archive' => false),
					  'fields' => array(),
				),*/			
			);

			$cert_defaults = array(
				'shortcode_options' => array(
					'name' => 'Shortcode Options',
					'type' => 'html',
					'default' => '',
					'save' => false,
					'label' => 'none',
				),
			);

			$post_args['sfwd-certificates'] = array(
				'plugin_name' => __( 'Certificates', 'learndash' ),
				'slug_name' => 'certificates',
				'post_type' => 'sfwd-certificates',
				'template_redirect' => false,
				'fields' => array(),
				'options_page_title' => __( 'Certificates Options', 'learndash' ),
				'default_options' => $cert_defaults,
				'cpt_options' => array( 
					'exclude_from_search' => true, 
					'has_archive' => false, 
					'hierarchical' => false, 
					'supports' => array( 'title', 'editor', 'thumbnail' , 'author',  'revisions'), 
					'capability_type' => 'course', 
					'capabilities' => $course_capabilities, 
					'map_meta_cap' => true,
				)
			);

			if ( learndash_is_admin_user( ) ) {
				$post_args['sfwd-transactions'] = array(
					'plugin_name' => __( 'Transactions', 'learndash' ),
					'slug_name' => 'transactions',
					'post_type' => 'sfwd-transactions',
					'template_redirect' => false,
					'options_page_title' => __( 'Transactions Options', 'learndash' ),
					'cpt_options' => array( 
						'supports' => array ( 'title', 'custom-fields' ), 
						'exclude_from_search' => true, 
						'publicly_queryable' => false, 
						'show_in_nav_menus' => false , 
						'show_in_menu'	=> 'edit.php?post_type=sfwd-courses'
					),
					'fields' => array(),
					'default_options' => array( 
						null => array( 
							'type' => 'html', 
							'save' => false, 
							'default' => __( 'Click the Export button below to export the transaction list.', 'learndash' ),
						)
					)
				);

				add_action( 'admin_init', array( $this, 'trans_export_init' ) );
			}

			 /**
			 * Filter $post_args used to create the custom post types and everything
			 * associated with them.
			 * 
			 * @since 2.1.0
			 * 
			 * @param  array  $post_args       
			 */
			$post_args = apply_filters( 'learndash_post_args', $post_args );

			add_action( 'admin_init', array( $this, 'quiz_export_init' ) );
			add_action( 'admin_init', array( $this, 'course_export_init' ) );
			
			add_action( 'show_user_profile', array( $this, 'show_course_info' ) );
			add_action( 'edit_user_profile', array( $this, 'show_course_info' ) );

			foreach ( $post_args as $p ) {				
				$this->post_types[ $p['post_type'] ] = new SFWD_CPT_Instance( $p );
			}

			//add_action( 'publish_sfwd-courses', array( $this, 'add_course_tax_entry' ), 10, 2 );
			add_action( 'init', array( $this, 'tax_registration' ), 11 );
			
			$sfwd_quiz = $this->post_types['sfwd-quiz'];
			$quiz_prefix = $sfwd_quiz->get_prefix();
			add_filter( "{$quiz_prefix}display_settings", array( $this, 'quiz_display_settings' ), 10, 3 );
			
			$sfwd_courses = $this->post_types['sfwd-courses'];
			$courses_prefix = $sfwd_courses->get_prefix();
			add_filter( "{$courses_prefix}display_settings", array( $this, 'course_display_settings' ), 10, 3 );

			$sfwd_lessons = $this->post_types['sfwd-lessons'];
			$lessons_prefix = $sfwd_lessons->get_prefix();
			add_filter( "{$lessons_prefix}display_settings", array( $this, 'lesson_display_settings' ), 10, 3 );

			$sfwd_topics = $this->post_types['sfwd-topic'];
			$topics_prefix = $sfwd_topics->get_prefix();
			add_filter( "{$topics_prefix}display_settings", array( $this, 'topic_display_settings' ), 10, 3 );

		}



		/**
		 * Displays users course information at bottom of profile
		 * Fires on action 'show_user_profile'
		 * Fires on action 'edit_user_profile'
		 * 
		 * @since 2.1.0
		 * 
		 * @param  object $user  wp user object
		 */
		function show_course_info( $user ) {
			$user_id = $user->ID;
			echo '<h3>' . sprintf( _x( '%s Info', 'Course Info Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) . '</h3>';
			echo $this->get_course_info( $user_id );
		}



		/**
		 * Returns output of users course information for bottom of profile
		 *
		 * @since 2.1.0
		 * 
		 * @param  int 		$user_id 	user id
		 * @return string          		output of course information
		 */
		static function get_course_info( $user_id ) {
			$courses_registered = ld_get_mycourses( $user_id );

			$usermeta = get_user_meta( $user_id, '_sfwd-course_progress', true );
			$course_progress = empty( $usermeta ) ? array() : $usermeta;
			//error_log('course_progress<pre>'. print_r($course_progress, true) .'</pre>');

			// The course_info_shortcode.php template is driven be the $courses_registered array. 
			// We want to make sure we show ALL the courses from both the $courses_registered and 
			// the course_progress. Also we want to run through WP_Query so we can ensure they still 
			// exist as valid posts AND we want to sort these alphs by title
			$courses_registered = array_merge( $courses_registered, array_keys($course_progress));
			if ( !empty( $courses_registered ) ) {
				$course_total_query_args = array(
					'post_type'			=>	'sfwd-courses',
					'fields'			=>	'ids',
					'nopaging'			=>	true,
					'orderby'			=>	'title',
					'order'				=>	'ASC',
					'post__in'			=>	$courses_registered
				);
				
				$course_total_query = new WP_Query( $course_total_query_args );
				
				if ( ( isset( $course_total_query->posts ) ) && ( !empty( $course_total_query->posts ) ) ) {
					$courses_registered = $course_total_query->posts;
				}
			}

			$usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );
			$quizzes = empty( $usermeta ) ? false : $usermeta;

			/**
			 * Filter Courses and Quizzes is showing the Group Admin > Report page
			 * IF we are viewing the group_admin_page we want to filter the Courses and Quizzes listing
			 * to only include those items related to the Group	
			 * 
			 * @since 2.3
			 */
			global $pagenow;
			if ( ( !empty( $pagenow ) ) && ( $pagenow == 'admin.php' ) ) {
				if ( ( isset( $_GET['page'] ) ) && ( $_GET['page'] == 'group_admin_page' ) ) {
					if ( ( isset( $_GET['group_id'] ) ) && ( !empty( $_GET['group_id'] ) ) ) {

						$group_courses = learndash_group_enrolled_courses( intval( $_GET['group_id'] ) );
						if ( empty( $group_courses ) ) {
							$group_courses = array();
						}

						if ( empty( $courses_registered ) ) {
							$courses_registered = array();
						}
						$courses_registered = array_intersect( $group_courses, $courses_registered );

						if ( empty( $course_progress ) ) {
							$course_progress = array();
						}
						foreach( $course_progress as $course_id => $course_details ) {
							if ( !in_array( $course_id, $group_courses ) ) 
								unset( $course_progress[$course_id] );
						}
						
						$group_quizzes = learndash_get_group_course_quiz_ids( intval( $_GET['group_id'] ) );
						if ( empty( $group_quizzes ) ) {
							$group_quizzes = array();
						}

						if ( empty( $quizzes ) ) {
							$quizzes = array();
						}
						
						foreach( $quizzes as $quiz_idx => $quiz_details ) {
							if ( !in_array( $quiz_details['quiz'], $group_quizzes ) ) 
								unset( $quizzes[$quiz_idx] );
						}
					}
				}
			}



			return SFWD_LMS::get_template('course_info_shortcode', array(
					'user_id' => $user_id,
					'courses_registered' => $courses_registered,
					'course_progress' => $course_progress,
					'quizzes' => $quizzes
				)
			);
		}



		/**
		 * Updates course price billy cycle on save
		 * Fires on action 'save_post'
		 *
		 * @since 2.1.0
		 * 
		 * @param  int 		 $post_id 	 
		 */
		function learndash_course_price_billing_cycle_save( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( empty( $post_id ) || empty( $_POST['post_type'] ) ) {
				return '';
			}

			// Check permissions
			if ( 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			if ( isset( $_POST['course_price_billing_p3'] ) ) {
				update_post_meta( $post_id, 'course_price_billing_p3', $_POST['course_price_billing_p3'] );
			}

			if ( isset( $_POST['course_price_billing_t3'] ) ) {
				update_post_meta( $post_id, 'course_price_billing_t3', $_POST['course_price_billing_t3'] );
			}
		}



		/**
		 * Billing Cycle field html output for courses
		 * 
		 * @since 2.1.0
		 * 
		 * @return string
		 */
		function learndash_course_price_billing_cycle_html() {
			global $pagenow;
			add_action( 'save_post', array( $this, 'learndash_course_price_billing_cycle_save' ) );

			if ( $pagenow == 'post.php' && ! empty( $_GET['post'] ) ) {
				$post_id = $_GET['post'];
				$post = get_post( $post_id );

				if ( $post->post_type != 'sfwd-courses' ) {
					return;
				}

				$course_price_billing_p3 = get_post_meta( $post_id, 'course_price_billing_p3',  true );
				$course_price_billing_t3 = get_post_meta( $post_id, 'course_price_billing_t3',  true );
				$settings = learndash_get_setting( $post_id );

				if ( ! empty( $settings ) && $settings['course_price_type'] == 'paynow' && empty( $settings['course_price'] ) ) {
					if ( empty( $settings['course_join'] ) ) {
						learndash_update_setting( $post_id, 'course_price_type', 'open' );
					} else {
						learndash_update_setting( $post_id, 'course_price_type', 'free' );
					}
				}

			} else {

				if ( $pagenow == 'post-new.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == 'sfwd-courses' ) {
					$post_id = 0;
					$course_price_billing_p3 = $course_price_billing_t3 = '';
				} else {
					return;
				}

			}
			

			$selected_D = $selected_W = $selected_M = $selected_Y = '';
			${'selected_'.$course_price_billing_t3} = 'selected="selected"';
			return '<input name="course_price_billing_p3" type="text" value="'.$course_price_billing_p3.'" size="2"/> 
					<select class="select_course_price_billing_p3" name="course_price_billing_t3">
						<option value="D" '.$selected_D.'>'.__( 'day(s)', 'learndash' ).'</option>
						<option value="W" '.$selected_W.'>'.__( 'week(s)', 'learndash' ).'</option>
						<option value="M" '.$selected_M.'>'.__( 'month(s)', 'learndash' ).'</option>
						<option value="Y" '.$selected_Y.'>'.__( 'year(s)', 'learndash' ).'</option>
					</select>';
		}

		static function course_progress_data( $course_id = null ) {
			set_time_limit( 0 );
			global $wpdb;

			$current_user = wp_get_current_user();
			if ( ( !learndash_is_admin_user( $current_user->ID ) ) && ( !learndash_is_group_leader_user( $current_user->ID ) ) ) {
				return;
			}
			
			$group_id = 0;
			if ( isset( $_GET['group_id'] ) ) {
				$group_id = $_GET['group_id'];
			}

			if ( learndash_is_group_leader_user( $current_user->ID ) ) {

				$users_group_ids = learndash_get_administrators_group_ids( $current_user->ID );
				if ( ! count( $users_group_ids ) ) {
					return array();
				}
				
				if ( !empty( $group_id ) ) {
					if ( ! in_array( $group_id, $users_group_ids ) ) {
						return;
					}
					$users_group_ids = array( $group_id );
				} 

				$all_user_ids = array();
				// First get the user_ids for each group...
				foreach($users_group_ids as $users_group_id) {
					$user_ids = learndash_get_groups_user_ids( $users_group_id );
					if (!empty($user_ids)) {
						if (!empty($all_user_ids)) {
							$all_user_ids = array_merge($all_user_ids, $user_ids);
						} else {
							$all_user_ids = $user_ids;
						}
					}
				}
				
				// Then once we have all the groups user_id run a last query for the complete user ids
				if (!empty($all_user_ids)) {
					$user_query_args = array(
						'include' 	=> 	$all_user_ids,
						'orderby' 	=>	'display_name',
						'order'	 	=>	'ASC',
					);
	
					$user_query = new WP_User_Query( $user_query_args );
	
					if ( isset( $user_query->results ) ) {
						$users = $user_query->results;
					}
				}
				
			} else if ( learndash_is_admin_user( $current_user->ID ) ) {
				if ( ! empty( $group_id ) ) {
					$users = learndash_get_groups_users( $group_id );
				} else {
					$users = get_users( 'orderby=display_name&order=ASC' );
				}

			} else {
				return array();
			}
			
			if ( empty( $users ) ) return array();

			$course_access_list = array();

			$course_progress_data = array();
			set_time_limit( 0 );

			$quiz_titles = array();
			$lessons = array();

			if ( ! empty( $course_id ) ) {
				$courses = array( get_post( $course_id ) );
			} elseif ( ! empty( $group_id ) ){
				$courses = learndash_group_enrolled_courses( $group_id );
				$courses = array_map( 'intval', $courses );
				$courses = ld_course_list( array( 'post__in' => $courses, 'array' => true ) );
			} else {
				$courses = ld_course_list( array( 'array' => true ) );
			}

			if ( ! empty( $users ) ) {

				foreach ( $users as $u ) {

					$user_id = $u->ID;
					$usermeta = get_user_meta( $user_id, '_sfwd-course_progress', true );
					if ( ! empty( $usermeta ) ) {
						$usermeta = maybe_unserialize( $usermeta );
					}

					if ( ! empty( $courses[0] ) ) {

						foreach ( $courses as $course ) {
							$c = $course->ID;

							if ( empty( $course->post_title) || ! sfwd_lms_has_access( $c, $user_id ) ) {
								continue;
							}

							$cv = ! empty( $usermeta[ $c] ) ? $usermeta[ $c ] : array( 'completed' => '', 'total' => '' );

							$course_completed_meta = get_user_meta( $user_id, 'course_completed_'.$course->ID, true );
							( empty( $course_completed_meta ) ) ? $course_completed_date = '' : $course_completed_date = date_i18n( 'F j, Y H:i:s', $course_completed_meta );

							$row = array( 'user_id' => $user_id,
								'name' => $u->display_name,
								'email' => $u->user_email,
								'course_id' => $c,
								'course_title' => $course->post_title,
								'total_steps' => $cv['total'],
								'completed_steps' => $cv['completed'], 
								'course_completed' => ( ! empty( $cv['total'] ) && $cv['completed'] >= $cv['total'] ) ? 'YES' : 'NO' , 
								'course_completed_on' => $course_completed_date
							);

							$i = 1;
							if ( ! empty( $cv['lessons'] ) ) {
								foreach ( $cv['lessons'] as $lesson_id => $completed ) {
									if ( ! empty( $completed ) ) {
										if ( empty( $lessons[ $lesson_id ] ) ) {
											$lesson = $lessons[ $lesson_id ] = get_post( $lesson_id );
										}
										else {
											$lesson = $lessons[ $lesson_id ];
										}

										$row['lesson_completed_'.$i] = $lesson->post_title;
										$i++;
									}
								}
							}

							$course_progress_data[] = $row;

						} // end foreach

					} // end if 

				} // end foreach

			} else {
				$course_progress_data[] = array( 
					'user_id' => $user_id, 
					'name' => $u->display_name, 
					'email' => $u->user_email, 
					'status' => __( 'No attempts', 'learndash' ),
				);
			}

			 /**
			 * Filter course progress data to be displayed
			 * 
			 * @since 2.1.0
			 * 
			 * @param  array  $course_progress_data
			 */
			$course_progress_data = apply_filters( 'course_progress_data', $course_progress_data, $users, @$group_id );

			return $course_progress_data;
		}



		/**
		 * Exports course progress data to CSV file
		 *
		 * @since 2.1.0
		 */
		function course_export_init() {
			error_reporting( 0 );

			if ( ! empty( $_REQUEST['courses_export_submit'] ) && ! empty( $_REQUEST['nonce-sfwd'] ) ) {
				set_time_limit( 0 );
				date_default_timezone_set( get_option( 'timezone_string' ) );

				$nonce = $_REQUEST['nonce-sfwd'];

				if ( ! wp_verify_nonce( $nonce, 'sfwd-nonce' ) ) { 
					die( __( 'Security Check - If you receive this in error, log out and back in to WordPress', 'learndash' ) );
				}

				$content = SFWD_LMS::course_progress_data();

				if ( empty( $content ) ) {
					$content[] = array( 'status' => __( 'No attempts', 'learndash' ) );
				}

				/**
				 * include parseCSV to write csv file
				 */
				require_once( dirname( __FILE__ ) . '/vendor/parsecsv.lib.php' );

				$csv = new lmsParseCSV();

				 /**
				 * Filter the content will print onto the exported CSV
				 * 
				 * @since 2.1.0
				 * 
				 * @param  array  $content
				 */
				$content = apply_filters( 'course_export_data', $content );

				$csv->output( 'courses.csv', $content, array_keys( reset( $content ) ) );
				die();
			}
		}



		/**
		 * Course Export Button submit data
		 * 
		 * apply_filters ran in display_settings_page() in sfwd_module_class.php
		 *
		 * @todo  currently no add_filter using this callback
		 *        consider for deprecation or implement add_filter
		 *
		 * @since 2.1.0
		 * 
		 * @param  array $submit
		 * @return array $submit
		 */
		function courses_filter_submit( $submit ) {
			$submit['courses_export_submit'] = array( 
				'type' => 'submit',
				'class' => 'button-primary',
				'value' => sprintf( _x( 'Export User %s Data &raquo;', 'Export User Course Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) 
			);
			return $submit;
		}



		/**
		 * Export quiz data to CSV
		 * 
		 * @since 2.1.0
		 */
		function quiz_export_init() {
			error_reporting( 0 );
			
			global $wpdb;
			$current_user = wp_get_current_user();

			if ( ( !learndash_is_admin_user( $current_user->ID ) ) && ( !learndash_is_group_leader_user( $current_user->ID ) ) )  {
				return;
			}

			//error_log('in '. __FUNCTION__ );
			//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');
			//error_log('_GET<pre>'. print_r($_GET, true) .'</pre>');
			


			// Why are these 3 lines here??
			$sfwd_quiz = $this->post_types['sfwd-quiz'];
			$quiz_prefix = $sfwd_quiz->get_prefix();
			add_filter( $quiz_prefix . 'submit_options', array( $this, 'quiz_filter_submit' ) );

			if ( ! empty( $_REQUEST['quiz_export_submit'] ) && ! empty( $_REQUEST['nonce-sfwd'] ) ) {
				$timezone_string = get_option( 'timezone_string' );
				if ( !empty( $timezone_string ) )
					date_default_timezone_set( $timezone_string );

				if ( ! wp_verify_nonce( $_REQUEST['nonce-sfwd'], 'sfwd-nonce' ) ) { 
					die ( __( 'Security Check - If you receive this in error, log out and back in to WordPress', 'learndash' ) );
				}

				/**
				 * include parseCSV to write csv file
				 */
				require_once( 'vendor/parsecsv.lib.php' );

				$content = array();
				set_time_limit( 0 );
				//Need ability to export quiz results for group to CSV

				if ( isset( $_GET['group_id'] ) ) {
					$group_id = $_GET['group_id'];
				}

				if ( learndash_is_group_leader_user( $current_user->ID ) ) {

					$users_group_ids = learndash_get_administrators_group_ids( $current_user->ID );
					if ( ! count( $users_group_ids ) ) {
						return array();
					}

					if ( isset( $group_id ) ) {
						if ( ! in_array( $group_id, $users_group_ids ) ) {
							return;
						}
						$users_group_ids = array( $group_id );
					} 
					
					$all_user_ids = array();
					// First get the user_ids for each group...
					foreach($users_group_ids as $users_group_id) {
						$user_ids = learndash_get_groups_user_ids( $users_group_id );
						if (!empty($user_ids)) {
							if (!empty($all_user_ids)) {
								$all_user_ids = array_merge($all_user_ids, $user_ids);
							} else {
								$all_user_ids = $user_ids;
							}
						}
					}
				
					// Then once we have all the groups user_id run a last query for the complete user ids
					if (!empty($all_user_ids)) {
						$user_query_args = array(
							'include' => $all_user_ids,
							'orderby' => 'display_name',
							'order'	 =>	'ASC',
							'meta_query' => array(
								array(
									'key'     	=> 	'_sfwd-quizzes',
									'compare' 	=> 	'EXISTS',
								),
							)
						);
						
						$user_query = new WP_User_Query( $user_query_args );
	
						if ( isset( $user_query->results ) ) {
							$users = $user_query->results;
						} 
					}
				} else if ( learndash_is_admin_user( $current_user->ID ) ) {
					if ( ! empty( $group_id ) ) {
						$user_ids = learndash_get_groups_user_ids( $group_id );
						if (!empty($user_ids)) {
							$user_query_args = array(
								'include' => $user_ids,
								'orderby' => 'display_name',
								'order'	 =>	'ASC',
								'meta_query' => array(
									array(
										'key'     	=> 	'_sfwd-quizzes',
										'compare' 	=> 	'EXISTS',
									),
								)
							);
		
							$user_query = new WP_User_Query( $user_query_args );
							if (isset($user_query->results)) {
								$users = $user_query->results;
							} else {
								$users = array();
							}
						}
						
					}
					else {
						
						$user_query_args = array(
							'orderby' => 'display_name',
							'order'	 =>	'ASC',
							'meta_query' => array(
								array(
									'key'     	=> 	'_sfwd-quizzes',
									'compare' 	=> 	'EXISTS',
								),
							)
						);
	
						$user_query = new WP_User_Query( $user_query_args );
						if (isset($user_query->results)) {
							$users = $user_query->results;
						} else {
							$users = array();
						}
					}

				} else {
					return array();
				}
				
				$quiz_titles = array();

				if ( ! empty( $users ) ) {

					foreach ( $users as $u ) {

						$user_id = $u->ID;
						$usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );

						if ( ! empty( $usermeta ) ) {

							foreach ( $usermeta as $k => $v ) {

								if ( ! empty( $group_id ) ) {
									$course_id = learndash_get_course_id( intval( $v['quiz'] ) );
									if ( ! learndash_group_has_course( $group_id, $course_id ) ) {
										continue;
									}
								}								

								if ( empty( $quiz_titles[ $v['quiz']] ) ) {

									if ( ! empty( $v['quiz'] ) ) {
										$quiz = get_post( $v['quiz'] );

										if ( empty( $quiz) ) {
											continue;
										}

										$quiz_titles[ $v['quiz']] = $quiz->post_title;

									} else if ( ! empty( $v['pro_quizid'] ) ) {

										$quiz = get_post( $v['pro_quizid'] );

										if ( empty( $quiz) ) {
											continue;
										}

										$quiz_titles[ $v['quiz']] = $quiz->post_title;

									} else {
										$quiz_titles[ $v['quiz']] = '';
									}
								}

								// After LD v2.2.1.2 we made a changes to the quiz user meta 'count' value output. Up to that point if the quiz showed only partial 
								// questions, like 5 of 10 total then the value of $v[count] would be 10 instead of only the shown count 5. 
								// After LD v2.2.1.2 we added a new field 'question_show_count' to hold the number of questions shown to the user during 
								// the quiz. 
								// But on legacy quiz user meta we needed a way to pull that information fron the quiz...

								if ( !isset( $v['question_show_count'] ) ) {
									$v['question_show_count'] = $v['count'];

									// ...If we have the statistics ref ID then we can pull the number of questions from there. 
									if ( ( isset( $v['statistic_ref_id'] ) ) && ( !empty( $v['statistic_ref_id'] ) ) ) {
										global $wpdb;
										
										$sql_str = $wpdb->prepare(" SELECT count(*) as count FROM ". $wpdb->prefix ."wp_pro_quiz_statistic WHERE statistic_ref_id = %d",  $v['statistic_ref_id'] );
										$count = $wpdb->get_var( $sql_str );
										if ( !$count ) $count = 0;
										$v['question_show_count'] = intval( $count );
									} else {
										// .. or if the statistics is not enabled for this quiz then we get the question show count from the 
										// quiz data. Note there is a potential hole in the logic here. If this quiz setting changes then existing 
										// quiz user meta reports will also be effected. 
										$pro_quiz_id = get_post_meta( $v['quiz'], 'quiz_pro_id', true );
										if ( !empty( $pro_quiz_id ) ) {
											$quizMapper = new WpProQuiz_Model_QuizMapper();
											$quiz       = $quizMapper->fetch( $pro_quiz_id );
									
											if ( ( $quiz->isShowMaxQuestion() ) && ( $quiz->getShowMaxQuestionValue() > 0 ) ) {
												$v['question_show_count'] = $quiz->getShowMaxQuestionValue();
											}
										}
									}
								}

								$content[] = array( 
									'user_id' 		=>	$user_id,
									'name' 			=>	$u->display_name,
									'email' 		=>	$u->user_email,
									'quiz_id' 		=>	$v['quiz'],
									'quiz_title' 	=> 	$quiz_titles[ $v['quiz'] ],
									'rank' 			=> 	$v['rank'],
									'score' 		=> 	$v['score'],
									'total' 		=> 	$v['question_show_count'],
									'date' 			=> 	date_i18n( DATE_RSS, $v['time'] ) ,
								);
							}

						} else {

							//	$content[] = array( 'user_id' => $user_id, 'name' => $u->display_name, 'email' => $u->user_email, 'status' => __( 'No attempts', 'learndash' ) );
							$content[] = array( 
								'user_id' => $user_id,
								'name' => $u->display_name,
								'email' => $u->user_email,
								'quiz_id' => __( 'No attempts',
								'learndash' ),
								'quiz_title' => '',
								'rank' => '',
								'score' => '',
								'total' => '',
								'date' => '' 
							 );

						} // end if

					} // end foreach 

				} // end if

				if ( empty( $content ) ) {
					$content[] = array( 'status' => __( 'No attempts', 'learndash' ) );
				}

				 /**
				 * Filter quiz data that will print to CSV
				 * 
				 * @since 2.1.0
				 * 
				 * @param  array  $content
				 */
				$content = apply_filters( 'quiz_export_data', $content, $users, @$group_id );

				$csv = new lmsParseCSV();
				$csv->output('quizzes.csv', $content, array_keys( reset( $content ) ) );
				die();

			}
		}



		/**
		 * Quiz Export Button submit data
		 * 
		 * Filter callback for $quiz_prefix . 'submit_options'
		 * apply_filters ran in display_settings_page() in sfwd_module_class.php
		 * 
		 * @since 2.1.0
		 * 
		 * @param  array $submit
		 * @return array
		 */
		function quiz_filter_submit( $submit ) {			
			$submit['quiz_export_submit'] = array( 
				'type' => 'submit',
				'class' => 'button-primary',
				'value' => sprintf( _x( 'Export %s Data &raquo;', 'Export Quiz Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ) 
			);
			return $submit;
		}



		/**
		 * Export transcations to CSV file
		 * 
		 * Not currently being used in plugin
		 *
		 * @todo consider for deprecation or implement in plugin
		 *
		 * @since 2.1.0
		 */
		function trans_export_init() {
			$sfwd_trans = $this->post_types['sfwd-transactions'];
			$trans_prefix = $sfwd_trans->get_prefix();
			add_filter( $trans_prefix . 'submit_options', array( $this, 'trans_filter_submit' ) );

			if ( ! empty( $_REQUEST['export_submit'] ) && ! empty( $_REQUEST['nonce-sfwd'] ) ) {
				$nonce = $_REQUEST['nonce-sfwd'];

				if ( ! wp_verify_nonce( $nonce, 'sfwd-nonce' ) ) { 
					die ( __( 'Security Check - If you receive this in error, log out and back in to WordPress', 'learndash' ) );
				}

				/**
				 * Include parseCSV to write csv file
				 */
				require_once( 'vendor/parsecsv.lib.php' );

				$content = array();
				set_time_limit( 0 );

				$locations = query_posts( 
					array( 
						'post_status' => 'publish', 
						'post_type' => 'sfwd-transactions', 
						'posts_per_page' => -1 
					) 
				);

				foreach ( $locations as $key => $location ) {
					$location_data = get_post_custom( $location->ID );
					foreach ( $location_data as $k => $v ) {
						if ( $k[0] == '_' ) {
							unset( $location_data[ $k ] );
						}
						else {
							$location_data[ $k] = $v[0];
						}
					}
					$content[] = $location_data;
				}

				if ( ! empty( $content ) ) {
					$csv = new lmsParseCSV();
					$csv->output( true, 'transactions.csv', $content, array_keys( reset( $content ) ) );
				}

				die();
			}
		}



		/**
		 * Transaction Export Button submit data
		 *
		 * Filter callback for $trans_prefix . 'submit_options'
		 * apply_filters ran in display_settings_page() in sfwd_module_class.php
		 * 
		 * @since 2.1.0
		 * 
		 * @param  array $submit
		 * @return array
		 */
		function trans_filter_submit( $submit ) {
			unset( $submit['Submit'] );
			unset( $submit['Submit_Default'] );

			$submit['export_submit'] = array( 
				'type' => 'submit',
				'class' => 'button-primary',
				'value' => __( 'Export &raquo;', 'learndash' ) 
			);

			return $submit;
		}



		/**
		 * Set up quiz display settings
		 * 
		 * Filter callback for '{$quiz_prefix}display_settings'
		 * apply_filters in display_options() in swfd_module_class.php
		 *
		 * @since 2.1.0
		 * 
		 * @param  array  $settings        quiz settings
		 * @param  string $location        where these settings are being displayed
		 * @param  array  $current_options current options stored for a given location
		 * @return array                   quiz settings
		 */
		function quiz_display_settings( $settings, $location, $current_options ) {
			global $sfwd_lms;
			$sfwd_quiz = $sfwd_lms->post_types['sfwd-quiz'];
			$quiz_prefix = $sfwd_quiz->get_prefix();
			
			$prefix_len = strlen( $quiz_prefix );
			$quiz_options = $sfwd_quiz->get_current_options();

			if ( $location == null ) {

				foreach ( $quiz_options as $k => $v ) {
					if ( strpos( $k, $quiz_prefix ) === 0 ) {
						$quiz_options[ substr( $k, $prefix_len ) ] = $v;
						unset( $quiz_options[ $k ] );
					}
				}

				foreach ( array( 'level1', 'level2', 'level3', 'level4', 'level5' ) as $level ) {
					$quiz['info'][ $level ] = $quiz_options[ $level ];
				}

				$quiz['info']['name'] = $quiz['info']['main'] = $quiz['info']['results'] = '';
				$quiz_json = json_encode( $quiz );
				$settings['sfwd-quiz_quiz']['default'] = '<div class="quizFormWrapper"></div><script type="text/javascript">var quizJSON = ' . $quiz_json . ';</script>';
				
				if ( $location == null ) { 
					unset( $settings["{$quiz_prefix}quiz"] );
				}

				if ( ! empty( $settings["{$quiz_prefix}certificate_post"] ) ) {
					$posts = get_posts( array( 'post_type' => 'sfwd-certificates' , 'numberposts' => -1 ) );
					$post_array = array( '0' => __( '-- Select a Certificate --', 'learndash' ) );

					if ( ! empty( $posts ) ) {
						foreach ( $posts as $p ) {
							$post_array[ $p->ID ] = $p->post_title;
						}
					}

					$settings["{$quiz_prefix}certificate_post"]['initial_options'] = $post_array;
				}

			} else {

				global $pagenow;
				if (($pagenow == 'post.php') || ($pagenow == 'post-new.php')) {
					$current_screen = get_current_screen();
					if ($current_screen->post_type == 'sfwd-quiz') {

						if ( ! empty( $settings["{$quiz_prefix}course"] ) ) {
							//$post_array = $this->select_a_course( 'sfwd-quiz' );
							//$settings["{$quiz_prefix}course"]['initial_options'] = $post_array;
							
							$_settings = $settings["{$quiz_prefix}course"];
							
							$query_options = array( 
								'post_type' 		=> 	'sfwd-courses', 
								'post_status' 		=> 	'any',  
								'posts_per_page' 	=> 	-1,
								'exclude'			=>	get_the_id(),
								'orderby'			=>	'title',
								'order'				=>	'ASC'	
							);

							$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
							if (($lazy_load == true) && (isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
								$query_options['paged'] 			= 	1;
								$query_options['posts_per_page'] 	= 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, "{$quiz_prefix}course");
							}
							
						    /**
						    * Filter course prerequisites
						    * 
						    * @since 2.1.0
						    * 
						    * @param  array  $options 
						    */
						   $query_options = apply_filters( 'learndash_quiz_cours_post_options', $query_options, $_settings );
							
						   $query_posts = new WP_Query( $query_options );

						   $post_array = array( '0' => sprintf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) );
						   
						   if ( ! empty( $query_posts->posts ) ) {
							   if ( count( $query_posts->posts ) >= $query_posts->found_posts ) {
								   // If the number of returned posts is equal or greater then found_posts then no need to run lazy load
								   $_settings['lazy_load'] = false;
							   }
	
							   foreach ( $query_posts->posts as $p ) {
								   if ( $p->ID == get_the_id() ){
									   //Skip for current post id as current course can not be prerequities of itself
								   } else { 
									   $post_array[ $p->ID ] = $p->post_title;
								   }
							   }
						   } else {
							   // If we don't have any items then override the lazy load flag
							   $_settings['lazy_load'] = false;
						   }
						   $settings["{$quiz_prefix}course"]['initial_options'] = $post_array;
						   
						   if ((isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
							   $lazy_load_data = array();
							   $lazy_load_data['query_vars'] 	= 	$query_options;
							   $lazy_load_data['query_type']	= 	'WP_Query';
							   $lazy_load_data['value']		=	$_settings['value'];
							   $settings["{$quiz_prefix}course"]['lazy_load_data'] = $lazy_load_data;
						   }
						}
						

						if ( ! empty( $settings["{$quiz_prefix}lesson"] ) ) {
							$post_array = $this->select_a_lesson_or_topic();
							$settings["{$quiz_prefix}lesson"]['initial_options'] = $post_array;
						}

						if ( ! empty( $settings["{$quiz_prefix}certificate"] ) ) {					
							$posts = get_posts( array( 'post_type' => 'sfwd-certificates'  , 'numberposts' => -1 ) );
							$post_array = array( '0' => __( '-- Select a Certificate --', 'learndash' ) );

							if ( ! empty( $posts ) ) {
								foreach ( $posts as $p ) {
									$post_array[ $p->ID ] = $p->post_title;
								}
							}

							$settings["{$quiz_prefix}certificate"]['initial_options'] = $post_array;
						}
				
						if ( ! empty( $settings["{$quiz_prefix}quiz_pro"] ) ) {
							$settings["{$quiz_prefix}quiz_pro"]['initial_options'] = array( 0 => __( '-- Select Settings --', 'learndash' ) ) + LD_QuizPro::get_quiz_list();
						}
					}
				}
			}

			return $settings;
		}



		/**
		 * Sets up Associated Course dropdown for lessons, quizzes, and topics
		 *
		 * @since 2.1.0
		 * 
		 * @param  string $current_post_type
		 * @return array of courses
		 * 
		 */
		function select_a_course( $current_post_type = null ) {
			global $pagenow;

			if ( ! is_admin() || ( $pagenow != 'post.php' && $pagenow != 'post-new.php') ) {
				return array();
			}

			if ( $pagenow == 'post.php' && empty( $_POST['_wpnonce'] ) && ! empty( $_GET['post'] ) && ! empty( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
				$post_id = intval($_GET['post']);
				$post = get_post( $post_id );
				if ( ! empty( $post->ID ) && $current_post_type == $post->post_type ) {
					if ( in_array( $post->post_type, array( 'sfwd-lessons', 'sfwd-quiz', 'sfwd-topic') ) ) {
						$course_id = learndash_get_course_id( $post );
						learndash_update_setting( $post, 'course', $course_id );
					}
				}
			}

			$options = array( 
				'array' => true, 
				'post_status' => 'any',  
				'orderby' => 'title', 
				'order' => 'ASC' 
			);

			 /**
			 * Filter options for querying course list
			 * 
			 * @since 2.1.0
			 * 
			 * @param  array  $options
			 */
			$options = apply_filters( 'learndash_select_a_course', $options );
			$posts = ld_course_list( $options );

			$post_array = array( '0' => sprintf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $p ){
					$post_array[ $p->ID ] = $p->post_title;
				}
			}

			return $post_array;
		}



		/**
		 * Retrieves lessons or topics for a course to populate dropdown on edit screen
		 * 
		 * Ajax action callback for wp_ajax_select_a_lesson_or_topic
		 *
		 * @since 2.1.0
		 */
		function select_a_lesson_or_topic_ajax() {
			$post_array = $this->select_a_lesson_or_topic( @$_REQUEST['course_id'] );
			$i = 0;
			foreach ( $post_array as $key => $value ) {
				$opt[ $i ]['key'] = $key;
				$opt[ $i ]['value'] = $value;
				$i++;
			}
			$data['opt'] = $opt;
			echo json_encode( $data );
			exit;
		}



		/**
		 * Makes wp_query to retrieve lessons or topics for a course
		 *
		 * @since 2.1.0
		 * 
		 * @param  int 		$course_id 
		 * @return array 	array of lessons or topics
		 */
		function select_a_lesson_or_topic( $course_id = null ) {
			if ( ! is_admin() ) {
				return array();
			}

			$opt = array( 
				'post_type' => 'sfwd-lessons',
				'post_status' => 'any',  'numberposts' => -1,
				'orderby' => learndash_get_option( 'sfwd-lessons', 'orderby' ),
				'order' => learndash_get_option( 'sfwd-lessons', 'order' ),
			);

			if ( empty( $course_id ) ) {
				$course_id = learndash_get_course_id( @$_GET['post'] );
			}

			if ( ! empty( $course_id ) ) {
				$opt['meta_key'] = 'course_id';
				$opt['meta_value'] = $course_id;
			}

			$posts = get_posts( $opt );
			$topics_array = learndash_get_topic_list();

			$post_array = array( '0' => sprintf( _x( '-- Select a %s or %s --', 'Select a Lesson or Topic Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ), LearnDash_Custom_Label::get_label( 'topic' ) ) );
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $p ){
					$post_array[ $p->ID ] = $p->post_title;
					if ( ! empty( $topics_array[ $p->ID ] ) ) {
						foreach ( $topics_array[ $p->ID ] as $id => $topic ) {
							$post_array[ $topic->ID ] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $topic->post_title;
						}
					}
				}
			}
			return $post_array;
		}



		/**
		 * Retrieves lessons for a course to populate dropdown on edit screen
		 * 
		 * Ajax action callback for wp_ajax_select_a_lesson
		 *
		 * @since 2.1.0
		 */
		function select_a_lesson_ajax() {
			$post_array = $this->select_a_lesson( @$_REQUEST['course_id'] );
			echo json_encode( $post_array );
			exit;
		}



		/**
		 * Makes wp_query to retrieve lessons a course
		 *
		 * @since 2.1.0
		 * 
		 * @param  int 		$course_id 
		 * @return array 	array of lessons
		 */
		function select_a_lesson( $course_id = null ) {			
			if ( ! is_admin() ) {
				return array();
			}

			if ( ! empty( $_REQUEST['ld_action'] ) || ! empty( $_GET['post'] ) && is_array( $_GET['post'] ) ) {
				return array();
			}

			$opt = array( 
				'post_type' => 'sfwd-lessons', 
				'post_status' => 'any',  
				'numberposts' => -1 , 
				'orderby' => learndash_get_option( 'sfwd-lessons', 'orderby' ), 
				'order' => learndash_get_option( 'sfwd-lessons', 'order' ),
			);

			if ( empty( $course_id ) ) {
				if ( empty( $_GET['post'] ) ) {
					$course_id = learndash_get_course_id();
				} else {
					$course_id = learndash_get_course_id( $_GET['post'] );
				}
			}

			if ( ! empty( $course_id ) ) {
				$opt['meta_key'] = 'course_id';
				$opt['meta_value'] = $course_id;
			}

			$posts = get_posts( $opt );
			$post_array = array( '0' => sprintf( _x( '-- Select a %s --', 'Select a Lesson Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ) );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $p ) {
					$post_array[ $p->ID ] = $p->post_title;
				}
			}

			return $post_array;
		}


		
		/**
		 * Set up course display settings
		 * 
		 * Filter callback for '{$courses_prefix}display_settings'
		 * apply_filters in display_options() in swfd_module_class.php
		 *
		 * @since 2.1.0
		 * 
		 * @param  array  $settings        quiz settings
		 * @return array                   quiz settings
		 */
		function course_display_settings( $settings ) {

			global $sfwd_lms;
			$sfwd_courses = $sfwd_lms->post_types['sfwd-courses'];
			$courses_prefix = $sfwd_courses->get_prefix();

			if ( ! empty( $settings["{$courses_prefix}course_prerequisite"] ) ) {
				
				$_settings = $settings["{$courses_prefix}course_prerequisite"];
				
				$query_options = array( 
					'post_type' 		=> 	'sfwd-courses', 
					'post_status' 		=> 	'any',  
					'posts_per_page' 	=> 	-1,
					'exclude'			=>	get_the_id(),
					'orderby'			=>	'title',
					'order'				=>	'ASC'	
				);

				$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
				if (($lazy_load == true) && (isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
					$query_options['paged'] 			= 	1;
					$query_options['posts_per_page'] 	= 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, "{$courses_prefix}course_prerequisite");
				}

				 /**
				 * Filter course prerequisites
				 * 
				 * @since 2.1.0
				 * 
				 * @param  array  $options 
				 */
				$query_options = apply_filters( 'learndash_course_prerequisite_post_options', $query_options, $_settings );

				$query_posts = new WP_Query( $query_options );

				$post_array = array( '0' => sprintf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) );

				if ( ! empty( $query_posts->posts ) ) {
					if ( count( $query_posts->posts ) >= $query_posts->found_posts ) {
						// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
						$_settings['lazy_load'] = false;
					}
					
					foreach ( $query_posts->posts as $p ) {
						if ( $p->ID == get_the_id() ){
							//Skip for current post id as current course can not be prerequities of itself
						} else { 
							$post_array[ $p->ID ] = $p->post_title;
						}
					}
				} else {
					// If we don't have any items then override the lazy load flag
					$_settings['lazy_load'] = false;
				}

				$settings["{$courses_prefix}course_prerequisite"]['initial_options'] = $post_array;
				
				if ((isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
					$lazy_load_data = array();
					$lazy_load_data['query_vars'] 	= 	$query_options;
					$lazy_load_data['query_type']	= 	'WP_Query';
					$lazy_load_data['value']		=	$_settings['value'];
					$settings["{$courses_prefix}course_prerequisite"]['lazy_load_data'] = $lazy_load_data;
				}
			}

			if ( ! empty( $settings["{$courses_prefix}certificate"] ) ) {
				$posts = get_posts( array( 'post_type' => 'sfwd-certificates'  , 'numberposts' => -1) );
				$post_array = array( '0' => __( '-- Select a Certificate --', 'learndash' ) );

				if ( ! empty( $posts ) ) {
					foreach ( $posts as $p ) {
						$post_array[ $p->ID ] = $p->post_title;
					}
				}

				$settings["{$courses_prefix}certificate"]['initial_options'] = $post_array;
			}

			return $settings;

		}

		
		/**
		 * Set up lesson display settings
		 * 
		 * Filter callback for '{$lessons_prefix}display_settings'
		 * apply_filters in display_options() in swfd_module_class.php
		 *
		 * @since 2.2.0.2
		 * 
		 * @param  array  $settings        lesson settings
		 * @return array                   lesson settings
		 */
		function lesson_display_settings( $settings ) {

			global $sfwd_lms;
			$sfwd_lessons = $sfwd_lms->post_types['sfwd-lessons'];
			$lessons_prefix = $sfwd_lessons->get_prefix();

			if ( ! empty( $settings["{$lessons_prefix}course"] ) ) {
				$_settings = $settings["{$lessons_prefix}course"];
				
				$query_options = array( 
					'post_type' 		=> 	'sfwd-courses', 
					'post_status' 		=> 	'any',  
					'posts_per_page' 	=> 	-1,
					'exclude'			=>	get_the_id(),
					'orderby'			=>	'title',
					'order'				=>	'ASC'	
				);

				$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
				if (($lazy_load == true) && (isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
					$query_options['paged'] 			= 	1;
					$query_options['posts_per_page'] 	= 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, "{$lessons_prefix}course");
				}
				

				 /**
				 * Filter course prerequisites
				 * 
				 * @since 2.1.0
				 * 
				 * @param  array  $options 
				 */
				$query_options = apply_filters( 'learndash_lesson_course_post_options', $query_options, $_settings );

 				$query_posts = new WP_Query( $query_options );
				
 				$post_array = array( '0' => sprintf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) );

 				if ( ! empty( $query_posts->posts ) ) {
 					if ( count( $query_posts->posts ) >= $query_posts->found_posts ) {
 						// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
 						$_settings['lazy_load'] = false;
 					}
					
 					foreach ( $query_posts->posts as $p ) {
 						if ( $p->ID == get_the_id() ){
 							//Skip for current post id as current course can not be prerequities of itself
 						} else { 
 							$post_array[ $p->ID ] = $p->post_title;
 						}
 					}
 				} else {
 					// If we don't have any items then override the lazy load flag
 					$_settings['lazy_load'] = false;
 				}

 				if ((isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
 					$lazy_load_data = array();
 					$lazy_load_data['query_vars'] 	= 	$query_options;
 					$lazy_load_data['query_type']	= 	'WP_Query';
 					$lazy_load_data['value']		=	$_settings['value'];
 					$settings["{$lessons_prefix}course"]['lazy_load_data'] = $lazy_load_data;
 				}

				$settings["{$lessons_prefix}course"]['initial_options'] = $post_array;
			}

			return $settings;
		}


		/**
		 * Set up topic display settings
		 * 
		 * Filter callback for '{$topics_prefix}display_settings'
		 * apply_filters in display_options() in swfd_module_class.php
		 *
		 * @since 2.2.0.2
		 * 
		 * @param  array  $settings        topic settings
		 * @return array                   topic settings
		 */
		function topic_display_settings( $settings ) {
			
			global $sfwd_lms;
			$sfwd_topics = $sfwd_lms->post_types['sfwd-topic'];
			$topics_prefix = $sfwd_topics->get_prefix();

			if ( ! empty( $settings["{$topics_prefix}course"] ) ) {
				//$options = array( 
				//	'post_type' => 'sfwd-courses', 
				//	'post_status' => 'any',  
				//	'numberposts' => -1
				//);

				$_settings = $settings["{$topics_prefix}course"];
				
				$query_options = array( 
					'post_type' 		=> 	'sfwd-courses', 
					'post_status' 		=> 	'any',  
					'posts_per_page' 	=> 	-1,
					'exclude'			=>	get_the_id(),
					'orderby'			=>	'title',
					'order'				=>	'ASC'	
				);

				$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
				if (($lazy_load == true) && (isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
					$query_options['paged'] 			= 	1;
					$query_options['posts_per_page'] 	= 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, "{$topics_prefix}course");
				}

				 /**
				 * Filter course prerequisites
				 * 
				 * @since 2.2.0.2
				 * 
				 * @param  array  $options 
				 */
				$query_options = apply_filters( 'learndash_topic_course_post_options', $query_options, $_settings );

 				$query_posts = new WP_Query( $query_options );
				
 				$post_array = array( '0' => sprintf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) );

 				if ( ! empty( $query_posts->posts ) ) {
 					if ( count( $query_posts->posts ) >= $query_posts->found_posts ) {
 						// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
 						$_settings['lazy_load'] = false;
 					}
					
 					foreach ( $query_posts->posts as $p ) {
 						if ( $p->ID == get_the_id() ){
 							//Skip for current post id as current course can not be prerequities of itself
 						} else { 
 							$post_array[ $p->ID ] = $p->post_title;
 						}
 					}
 				} else {
 					// If we don't have any items then override the lazy load flag
 					$_settings['lazy_load'] = false;
 				}
 				
				if ((isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
 					$lazy_load_data = array();
 					$lazy_load_data['query_vars'] 	= 	$query_options;
 					$lazy_load_data['query_type']	= 	'WP_Query';
 					$lazy_load_data['value']		=	$_settings['value'];
 					$settings["{$topics_prefix}course"]['lazy_load_data'] = $lazy_load_data;
 				}

				$settings["{$topics_prefix}course"]['initial_options'] = $post_array;
			}

			if ( ! empty( $settings["{$topics_prefix}lesson"] ) ) {

				$_settings = $settings["{$topics_prefix}lesson"];
				
				$query_options = array( 
					'post_type' 		=> 	'sfwd-lessons', 
					'post_status' 		=> 	'any',  
					'posts_per_page' 	=> 	-1,
					'exclude'			=>	get_the_id(),
					'orderby'			=>	'title',
					'order'				=>	'ASC'	
				);

				$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
				if (($lazy_load == true) && (isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
					$query_options['paged'] 			= 	1;
					$query_options['posts_per_page'] 	= 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, "{$topics_prefix}lesson");
				}
				
				 /**
				 * Filter lesson prerequisites
				 * 
				 * @since 2.2.0.2
				 * 
				 * @param  array  $options 
				 */
				$query_options = apply_filters( 'learndash_topic_lesson_post_options', $query_options, $_settings );

 				$query_posts = new WP_Query( $query_options );
				
 				$post_array = array( '0' => sprintf( _x( '-- Select a %s --', 'Select a Lesson Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ) );

 				if ( ! empty( $query_posts->posts ) ) {
 					if ( count( $query_posts->posts ) >= $query_posts->found_posts ) {
 						// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
 						$_settings['lazy_load'] = false;
 					}
					
 					foreach ( $query_posts->posts as $p ) {
 						if ( $p->ID == get_the_id() ){
 							//Skip for current post id as current course can not be prerequities of itself
 						} else { 
 							$post_array[ $p->ID ] = $p->post_title;
 						}
 					}
 				} else {
 					// If we don't have any items then override the lazy load flag
 					$_settings['lazy_load'] = false;
 				}
 				
				if ((isset($_settings['lazy_load'])) && ($_settings['lazy_load'] == true)) {
 					$lazy_load_data = array();
 					$lazy_load_data['query_vars'] 	= 	$query_options;
 					$lazy_load_data['query_type']	= 	'WP_Query';
 					$lazy_load_data['value']		=	$_settings['value'];
 					$settings["{$topics_prefix}lesson"]['lazy_load_data'] = $lazy_load_data;
 				}

				$settings["{$topics_prefix}lesson"]['initial_options'] = $post_array;
			}

			return $settings;
		}



		/**
		 * Insert course name as a term on course publish
		 * 
		 * Action callback for 'publish_sfwd-courses' (wp core filter action)
		 *
		 * @todo  consider for deprecation, action is commented 
		 *
		 * @since 2.1.0
		 * 
		 * @param int 		$post_id
		 * @param object 	$post
		 */
		function add_course_tax_entry( $post_id, $post ) {
			$term = get_term_by( 'slug', $post->post_name, 'courses' );
			$term_id = isset( $term->term_id ) ? $term->term_id : 0;

			if ( ! $term_id ) {
				$term = wp_insert_term( $post->post_title, 'courses', array( 'slug' => $post->post_name ) );
				$term_id = $term['term_id'];
			}

			wp_set_object_terms( (int)$post_id, (int)$term_id, 'courses', true );
		}



		/**
		 * Register taxonomies for each custom post type
		 * 
		 * Action callback for 'init'
		 *
		 * @since 2.1.0
		 */
		function tax_registration() {

			/**
			 * Filter that gathers taxonomies that need to be registered
			 * add_filters are currently added during the add_post_type() method in swfd_cpt.php
			 *
			 * @since 2.1.0
			 * 
			 * @param  array
			 */
			$taxes = apply_filters( 'sfwd_cpt_register_tax', array() );

			if ( ! empty( $taxes ) ) {
				$post_types = array();
				$tax_options = null;

				foreach ( $taxes as $k => $v ) {

					if ( ! empty( $v ) ) {

						foreach ( $v as $tax ) {

							if ( ! is_array( $tax[0] ) ) { 
								$tax[0] = array( $tax[0] );
							}

							$post_types = array_merge( $post_types, $tax[0] );

							if ( empty( $tax_options ) ) {
								$tax_options = $tax[1];
							} else {
								foreach ( $tax[1] as $l => $w ) {
									$tax_options[ $l] = $w;
								}
							}

						} // end foreach

					} // endif

				}// end foreach

				register_taxonomy( $k, $post_types, $tax_options );				
			} // endif

		}



		/**
		 * Get LearnDash template and pass data to be used in template
		 *
		 * Checks to see if user has a 'learndash' directory in their current theme
		 * and uses the template if it exists.
		 *
		 * @since 2.1.0
		 * 
		 * @param  string  	$name             template name
		 * @param  array  	$args             data for template
		 * @param  boolean 	$echo             echo or return
		 * @param  boolean 	return_file_path  return just file path instead of output
		 */
		static function get_template( $name, $args, $echo = false, $return_file_path = false ){
			$filename = substr( $name, -4 ) == '.php' ? $name : $name . '.php';
			$filepath = locate_template( array( 'learndash/'.$filename) );

			if ( ! $filepath ) {
				$filepath = locate_template( $filename );
			}

			if ( ! $filepath ){
				$filepath = dirname( dirname( __FILE__ ) ) . '/templates/' . $filename;
				if ( ! file_exists( $filepath ) ) {
					return false;
				}				
			}

			/**
			 * Filter filepath for learndash template being called
			 * 
			 * @since 2.1.0
			 * 
			 * @param  string  $filepath
			 */
			$filepath = apply_filters( 'learndash_template', $filepath, $name, $args, $echo, $return_file_path );

			if ( $return_file_path ) {
				return $filepath;
			}

			// Added check to ensure external hooks don't return empty or non-accessible filenames. 
			if ( ( !empty( $filepath ) ) && ( file_exists( $filepath ) ) && ( is_file( $filepath ) ) ) {
				extract( $args );
				$level = ob_get_level();
				ob_start();
				include( $filepath );
				$contents = learndash_ob_get_clean( $level );

				if ( ! $echo ) {
					return $contents;
				}

				echo $contents;
			}
		}
		
		// End of functions
	}
}

$sfwd_lms = new SFWD_LMS();
