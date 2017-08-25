<?php
/**
 * Plugin Name: LearnDash LMS
 * Plugin URI: http://www.learndash.com
 * Description: LearnDash LMS Plugin - Turn your WordPress site into a learning management system.
 * Version: 2.3.0.2
 * Author: LearnDash
 * Author URI: http://www.learndash.com
 * Text Domain: learndash
 * Doman Path: /languages/
 * @since 2.1.0
 * 
 * @package LearnDash
 */


/**
 * LearnDash Version Constant
 */
define( 'LEARNDASH_VERSION', '2.3.0.2' );
define( 'LEARNDASH_SETTINGS_DB_VERSION', '2.3' );

if ( !defined('LEARNDASH_LMS_PLUGIN_DIR' ) ) {
	define( 'LEARNDASH_LMS_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if (!defined( 'LEARNDASH_LMS_PLUGIN_URL' ) ) {
	define( 'LEARNDASH_LMS_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

// If the WordPress 'SCRIPT_DEBUG' is set then we also set our 'LEARNDASH_SCRIPT_DEBUG' so we are serving non-minified scripts
if ( !defined( 'LEARNDASH_SCRIPT_DEBUG' ) ) {
	if ( ( defined( 'SCRIPT_DEBUG' ) ) && ( SCRIPT_DEBUG === true ) ) {
		define('LEARNDASH_SCRIPT_DEBUG', true);
	} else {
		define('LEARNDASH_SCRIPT_DEBUG', false);
	}
}  

if ( !defined('LEARNDASH_LMS_DEFAULT_QUESTION_POINTS' ) ) {
	define( 'LEARNDASH_LMS_DEFAULT_QUESTION_POINTS', 1 );
}

if ( !defined('LEARNDASH_LMS_DEFAULT_ANSWER_POINTS' ) ) {
	define( 'LEARNDASH_LMS_DEFAULT_ANSWER_POINTS', 0 );
}

// Define the number of items to lazy load per AJAX request.
if ( !defined('LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE' ) ) {
	define( 'LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE', 5000 );
} 

// Define what administrator cabability to check. 
if ( !defined('LEARNDASH_ADMIN_CAPABILITY_CHECK' ) ) {
	define( 'LEARNDASH_ADMIN_CAPABILITY_CHECK', 'manage_options' );
} 

if ( !defined('LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK' ) ) {
	define( 'LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK', 'group_leader' );
} 

/**
 * The module base class; handles settings, options, menus, metaboxes, etc.
 */
require_once( dirname( __FILE__ ).'/includes/class-ld-semper-fi-module.php' );

/**
 * SFWD_LMS
 */
require_once( dirname( __FILE__ ).'/includes/class-ld-lms.php' );

/**
 * Register CPT's and Taxonomies
 */
require_once( dirname( __FILE__ ).'/includes/class-ld-cpt.php' );

/**
 * Register CPT's and Taxonomies
 */
require_once( dirname( __FILE__ ).'/includes/class-ld-cpt-instance.php' );

/**
 * Registers widget for displaying a list of lessons for a course and tracks lesson progress.
 */
require_once( dirname( __FILE__ ).'/includes/class-ld-cpt-widget.php' );

/**
 * Course functions
 */
require_once( dirname( __FILE__ ).'/includes/course/ld-course-functions.php' );

/**
 * Course navigation
 */
require_once( dirname( __FILE__ ).'/includes/course/ld-course-navigation.php' );

/**
 * Course progress functions
 */
require_once( dirname( __FILE__ ).'/includes/course/ld-course-progress.php' );

/**
 * Course, lesson, topic, quiz list shortcodes and helper functions
 */
require_once( dirname( __FILE__ ).'/includes/course/ld-course-list-shortcode.php' );

/**
 * Course info and navigation widgets
 */
require_once( dirname( __FILE__ ).'/includes/course/ld-course-info-widget.php' );

/**
 * Implements WP Pro Quiz
 */
require_once( dirname( __FILE__ ).'/includes/quiz/ld-quiz-pro.php' );

/**
 * Shortcodes for displaying Quiz and Course info
 */
require_once( dirname( __FILE__ ).'/includes/quiz/ld-quiz-info-shortcode.php' );

/**
 * Quiz migration functions
 */
require_once( dirname( __FILE__ ).'/includes/quiz/ld-quiz-migration.php' );

/**
 * Quiz essay question functions
 */
require_once( dirname( __FILE__ ).'/includes/quiz/ld-quiz-essays.php' );

/**
 * Load scripts & styles
 */
require_once( dirname( __FILE__ ).'/includes/ld-scripts.php' );

/**
 * Customizations to wp editor for LearnDash
 */
require_once( dirname( __FILE__ ).'/includes/ld-wp-editor.php' );

/**
 * Handles assignment uploads and includes helper functions for assignments
 */
require_once( dirname( __FILE__ ).'/includes/ld-assignment-uploads.php' );

/**
 * Group functions
 */
require_once( dirname( __FILE__ ).'/includes/ld-groups.php' );

/**
 * User functions
 */
require_once( dirname( __FILE__ ).'/includes/ld-users.php' );

/**
 * Certificate functions
 */
require_once( dirname( __FILE__ ).'/includes/ld-certificates.php' );

/**
 * Misc functions
 */
require_once( dirname( __FILE__ ).'/includes/ld-misc-functions.php' );

/**
 * wp-admin functions
 */
require_once( dirname( __FILE__ ).'/includes/admin/ld-admin.php' );

/**
 * Custom label
 */
require_once( dirname( __FILE__ ).'/includes/class-ld-custom-label.php' );

/**
 * Binary Selector
 */
require_once( dirname( __FILE__ ).'/includes/admin/class-learndash-admin-binary-selector.php' );

/**
 * Data/System Upgrades
 */
require_once( dirname( __FILE__ ).'/includes/admin/class-learndash-admin-settings-data-upgrades.php' );

/**
 * Reports
 */
require_once( dirname( __FILE__ ).'/includes/admin/class-learndash-admin-settings-data-reports.php' );

/**
 * Reports Functions
 */
require_once( dirname( __FILE__ ).'/includes/ld-reports.php' );


/**
 * Globals that hold CPT's and Pages to be set up
 */
global $learndash_post_types, $learndash_pages;

$learndash_post_types = array( 
	'sfwd-courses', 
	'sfwd-lessons', 
	'sfwd-topic', 
	'sfwd-quiz', 
	'sfwd-transactions', 
	'sfwd-groups', 
	'sfwd-assignment',
	'sfwd-essays',
);

$learndash_pages = array( 
	'group_admin_page', 
	'learndash-lms-certificate_shortcodes', 
	'learndash-lms-course_shortcodes', 
	'learndash-lms-reports', 
	'ldAdvQuiz',
);

$learndash_course_statuses = array(
	'not_started'	=>	__( 'Not Started', 'learndash' ),
	'in_progress'	=>	__( 'In Progress', 'learndash' ),
	'complete'		=>	__( 'Completed', 'learndash' )
);

// This is a global variable which is set in any of the shortcode handler functions.
// The purpose is to let the plugin know when and if the any of the shortcodes were used. 
$learndash_shortcode_used = false;

$learndash_assets_loaded = array();
$learndash_assets_loaded['styles'] = array();
$learndash_assets_loaded['scripts'] = array();