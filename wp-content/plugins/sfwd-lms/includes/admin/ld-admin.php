<?php
/**
 * Functions for wp-admin
 *
 * @since 2.1.0
 *
 * @package LearnDash\Admin
 */


/**
 * Output for admin head
 *
 * Adds learndash icon next to the LearnDash LMS menu item
 *
 * @since 2.1.0
 */
function learndash_admin_head() {
	?>
		<style> #adminmenu #toplevel_page_learndash-lms div.wp-menu-image:before { content: "\f472"; } </style>
	<?php
}

add_action( 'admin_head', 'learndash_admin_head' );



/**
 * Hide top level menu when there are no submenus
 *
 * @since 2.1.0
 */
function learndash_hide_menu_when_not_required() {
	?>
		<script>
		jQuery(window).ready(function() {
		if(jQuery(".toplevel_page_learndash-lms").length && jQuery(".toplevel_page_learndash-lms").find("li").length <= 1)
			jQuery(".toplevel_page_learndash-lms").hide();
		});
		</script>
	<?php
}

add_filter( 'admin_footer', 'learndash_hide_menu_when_not_required', 99 );



/**
 * Scripts/styles for admin
 *
 * @since 2.1.0
 */
function learndash_load_admin_resources() {
	global $pagenow, $post;
	global $learndash_post_types, $learndash_pages;;
	global $learndash_assets_loaded;

	if ( in_array( @$_GET['page'], $learndash_pages ) || in_array( @$_GET['post_type'], $learndash_post_types ) || $pagenow == 'post.php' && in_array( $post->post_type, $learndash_post_types ) ) {
		wp_enqueue_style( 
			'learndash_style', 
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/style'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css',
			array(), 
			LEARNDASH_VERSION 
		);
		$learndash_assets_loaded['styles']['learndash_style'] = __FUNCTION__;
	}

	if ( $pagenow == 'post.php' && $post->post_type == 'sfwd-quiz' || $pagenow == 'post-new.php' && @$_GET['post_type'] == 'sfwd-quiz' ) {
		wp_enqueue_script( 
			'wpProQuiz_admin_javascript', 
			plugins_url('js/wpProQuiz_admin'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', WPPROQUIZ_FILE),
			array( 'jquery' ),
			LEARNDASH_VERSION,
			true
		);
		$learndash_assets_loaded['scripts']['wpProQuiz_admin_javascript'] = __FUNCTION__;
	}

	if ( $pagenow == 'post-new.php' && @$_GET['post_type'] == 'sfwd-lessons' || $pagenow == 'post.php' && @get_post( @$_GET['post'] )->post_type == 'sfwd-lessons' ) {
		wp_enqueue_style( 
			'ld-datepicker-ui-css', 
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/jquery-ui'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css',
			array(), 
			LEARNDASH_VERSION 
		);
		$learndash_assets_loaded['styles']['ld-datepicker-ui-css'] = __FUNCTION__;
	}
	
	if ( ($pagenow == 'admin.php') && (@$_GET['page'] == 'ldAdvQuiz') && (@$_GET['module'] == 'statistics') )  {
		wp_enqueue_style( 
			'ld-datepicker-ui-css', 
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/jquery-ui'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css',
			array(), 
			LEARNDASH_VERSION 
		);
		$learndash_assets_loaded['styles']['ld-datepicker-ui-css'] = __FUNCTION__;
	}
}

add_action( 'admin_enqueue_scripts', 'learndash_load_admin_resources' );



/**
 * Register admin menu pages
 *
 * @since 2.1.0
 */
function learndash_menu() {
	if ( ! is_admin() ) {
		return;
	}

	add_menu_page(
		__( 'LearnDash LMS', 'learndash' ),
		__( 'LearnDash LMS', 'learndash' ),
		'read',
		'learndash-lms',
		null,
		null,
		null
	);
	
	/*
	add_submenu_page(
		'learndash-lms-non-existant',
		__( 'LearnDash Reports', 'learndash' ),
		__( 'LearnDash Reports', 'learndash' ),
		LEARNDASH_ADMIN_CAPABILITY_CHECK,
		'learndash-lms-reports',
		'learndash_lms_reports_page'
	);
	*/
	add_submenu_page(
		'learndash-lms-non-existant',
		__( 'Certificate Shortcodes', 'learndash' ),
		__( 'Certificate Shortcodes', 'learndash' ),
		LEARNDASH_ADMIN_CAPABILITY_CHECK,
		'learndash-lms-certificate_shortcodes',
		'learndash_certificate_shortcodes_page'
	);

	add_submenu_page(
		'learndash-lms-non-existant',
		sprintf( _x( '%s Shortcodes', 'Course Shortcodes Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
		sprintf( _x( '%s Shortcodes', 'Course Shortcodes Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
		'edit_courses',
		'learndash-lms-course_shortcodes',
		'learndash_course_shortcodes_page'
	);

	$remove_from_submenu = array(
		'options-general.php?page=nss_plugin_license-sfwd_lms-settings' => __( 'LearnDash LMS License', 'learndash' ),
		'admin.php?page=learndash-lms-reports' => 'Reports',
	);

	$remove_from_menu = array(
		'edit.php?post_type=sfwd-courses',
		'edit.php?post_type=sfwd-lessons',
		'edit.php?post_type=sfwd-quiz',
		'edit.php?post_type=sfwd-topic',
		'edit.php?post_type=sfwd-certificates',
		'edit.php?post_type=sfwd-assignment',
		'edit.php?post_type=groups',
	);

	global $submenu;

	$add_submenu = array();
	
	if ( current_user_can('edit_courses') ) {
		$add_submenu['courses'] = array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'courses' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-courses',
		);

		$add_submenu['lessons'] = array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'lessons' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-lessons',
		);
		
		$add_submenu['topics'] = array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'topics' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-topic',
		);
		
		$add_submenu['quizzes'] = array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'quizzes' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-quiz',
		);
		
		$add_submenu['certificates'] = array(
			'name' 	=> _x( 'Certificates', 'Certificates Menu Label', 'learndash' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-certificates',
		);
	}
	
	if ( current_user_can('edit_assignments') ) {
		$add_submenu['assignments'] = array(
			'name' 	=> _x( 'Assignments', 'Assignments Menu Label', 'learndash' ),
			'cap'	=> 'edit_assignments',
			'link'	=> 'edit.php?post_type=sfwd-assignment',
		);
	}
	
	if ( current_user_can('edit_groups') ) {
		$add_submenu['groups'] = array(
			'name' 	=> _x( 'Groups', 'Groups Menu Label', 'learndash' ),
			'cap'	=> 'edit_groups',
			'link'	=> 'edit.php?post_type=groups',
		);
	}

	if ( learndash_is_admin_user() ) {
		$user_group_ids = learndash_get_administrators_group_ids( get_current_user_id(), true );
		if ( !empty( $user_group_ids ) ) {
			
			$add_submenu[] = array(
				'name' 	=> _x( 'Group Administration', 'Group Administration Menu Label', 'learndash' ),
				'cap'	=> LEARNDASH_ADMIN_CAPABILITY_CHECK,
				'link'	=> 'admin.php?page=group_admin_page',
			);
		}
	} else if ( learndash_is_group_leader_user() ) {
		$add_submenu[] = array(
			'name' 	=> _x( 'Group Administration', 'Group Administration Menu Label', 'learndash' ),
			'cap'	=> LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK,
			'link'	=> 'admin.php?page=group_admin_page',
		);
	}
		
	$add_submenu['reports'] = array(
		'name' 	=> _x( 'Reports', 'Reports Menu Label', 'learndash' ),
		'cap'	=> LEARNDASH_ADMIN_CAPABILITY_CHECK,
		'link'	=> 'admin.php?page=learndash-lms-reports',
	);
	
	$add_submenu['settings'] = array(
		'name' 	=> _x( 'Settings', 'Settings Menu Label', 'learndash' ),
		'cap'	=> LEARNDASH_ADMIN_CAPABILITY_CHECK,
		'link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses'
	);
	
	if ( learndash_is_group_leader_user() ) {
		$add_submenu['essays'] = array(
			'name' 	=> _x( 'Submitted Essays', 'Submitted Essays Menu Label', 'learndash' ),
			'cap'	=> 'group_leader',
			'link'	=> 'edit.php?post_type=sfwd-essays',
		);
	}
	
	 /**
	 * Filter submenu array before it is registered
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $add_submenu
	 */
	$add_submenu = apply_filters( 'learndash_submenu', $add_submenu );
	$location = 500;

	foreach ( $add_submenu as $key => $add_submenu_item ) {
		if ( current_user_can( $add_submenu_item['cap'] ) ) {
			$submenu['learndash-lms'][ $location++ ] = array( $add_submenu_item['name'], $add_submenu_item['cap'], $add_submenu_item['link'] );
		}
	}

	foreach ( $remove_from_menu as $menu ) {
		if ( isset( $submenu[ $menu ] ) ) {
			remove_menu_page( $menu );
		}
	}

	foreach ( $remove_from_submenu as $menu => $remove_submenu_items ) {
		if ( isset( $submenu[ $menu ] ) && is_array( $submenu[ $menu ] ) ) {
			foreach ( $submenu[ $menu] as $key => $item ) {
				if ( isset( $item[0] ) && in_array( $item[0], $remove_submenu_items ) ) {
					unset( $submenu[ $menu ][ $key ] );
				}
			}
		}
	}

}

add_action( 'admin_menu', 'learndash_menu', 1000 );



/**
 * Set up admin tabs for each admin menu page under LearnDash
 *
 * @since 2.1.0
 */
function learndash_admin_tabs() {
	if ( ! is_admin() ) {
		return;
	}

	$admin_tabs = array(
		0 => array(
			'link'	=> 'post-new.php?post_type=sfwd-courses',
			'name'	=> _x( 'Add New', 'Add New Course Label', 'learndash' ),
			'id'	=> 'sfwd-courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		10 => array(
			'link'	=> 'edit.php?post_type=sfwd-courses',
			'name'	=> LearnDash_Custom_Label::get_label( 'courses' ),
			'id'	=> 'edit-sfwd-courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		24 => array(
			'link'	=> 'edit-tags.php?taxonomy=category&post_type=sfwd-courses',
			'name'	=> _x( 'Categories', 'Course Categories Label', 'learndash' ),
			'id'	=> 'edit-category',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		26 => array(
			'link'	=> 'edit-tags.php?taxonomy=post_tag&post_type=sfwd-courses',
			'name'	=> _x( 'Tags', 'Course Tags Label', 'learndash' ),
			'id'	=> 'edit-post_tag',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		28 => array(
			'link'	=> 'admin.php?page=learndash-lms-course_shortcodes',
			'name'	=> sprintf( _x( '%s Shortcodes', 'Course Shortcodes Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
			'id'	=> 'admin_page_learndash-lms-course_shortcodes',
			'cap'	=> 'edit_courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		30 => array(
			'link'	=> 'post-new.php?post_type=sfwd-lessons',
			'name'	=> _x( 'Add New', 'Add New esson Label', 'learndash' ),
			'id'	=> 'sfwd-lessons',
			'menu_link'	=> 'edit.php?post_type=sfwd-lessons',
		),
		40 => array(
			'link'	=> 'edit.php?post_type=sfwd-lessons',
			'name'	=> LearnDash_Custom_Label::get_label( 'lessons' ),
			'id'	=> 'edit-sfwd-lessons',
			'menu_link'	=> 'edit.php?post_type=sfwd-lessons',
		),
		50 => array(
			'link'	=> 'edit.php?post_type=sfwd-lessons&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-lessons',
			'name'	=> sprintf( _x( '%s Options', 'Lesson Options Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
			'id'	=> 'sfwd-lessons_page_sfwd-lms_sfwd_lms_post_type_sfwd-lessons',
			'menu_link'	=> 'edit.php?post_type=sfwd-lessons',
		),
		60 => array(
			'link'	=> 'post-new.php?post_type=sfwd-topic',
			'name'	=> _x( 'Add New', 'Add New Topic Label', 'learndash' ),
			'id'	=> 'sfwd-topic',
			'menu_link'	=> 'edit.php?post_type=sfwd-topic',
		),
		70 => array(
			'link'	=> 'edit.php?post_type=sfwd-topic',
			'name'	=> LearnDash_Custom_Label::get_label( 'topics' ),
			'id'	=> 'edit-sfwd-topic',
			'menu_link'	=> 'edit.php?post_type=sfwd-topic',
		),
		80 => array(
			'link'	=> 'post-new.php?post_type=sfwd-quiz',
			'name'	=> _x( 'Add New', 'add New Topic Label', 'learndash' ),
			'id'	=> 'sfwd-quiz',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		90 => array(
			'link'	=> 'edit.php?post_type=sfwd-quiz',
			'name'	=> LearnDash_Custom_Label::get_label( 'quizzes' ),
			'id'	=> 'edit-sfwd-quiz',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		91 => array(
			'link'	=> 'edit.php?post_type=sfwd-essays',
			'name'	=> _x( 'Submitted Essays', 'Quiz Submitted Essays Tab Label', 'learndash' ),
			'id'	=> 'edit-sfwd-essays',
			'menu_link'	=> 'edit.php?post_type=sfwd-essays',
		),
		100 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=globalSettings',
			'name'	=> sprintf( _x( '%s Options', 'Quiz Options Tab Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'id'	=> 'admin_page_ldAdvQuiz_globalSettings',
			'cap'	=> 'wpProQuiz_change_settings',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		101 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz',
			'name'	=> _x( 'Import/Export', 'Quiz Import/Export Tab Label', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz',
			'cap'	=> 'wpProQuiz_export',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		95 => array(
			'link'	=> 'post.php?post=[post_id]&action=edit',
			'name'	=> sprintf( _x( 'Edit %s', 'Edit Quiz Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'id'	=> 'sfwd-quiz_edit',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		102 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=question&quiz_id=[quiz_id]&post_id=[post_id]',
			'name'	=> _x( 'Questions', 'Quiz Questions Tab Label', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz_question',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		104 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=statistics&id=[quiz_id]&post_id=[post_id]',
			'name'	=> _x( 'Statistics', 'Quiz Statistics Tab Label', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz_statistics',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		106 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=toplist&id=[quiz_id]&post_id=[post_id]',
			'name'	=> _x( 'Leaderboard', 'Quiz Leaderboard Tab Label', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz_toplist',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		110 => array(
			'link'	=> 'post-new.php?post_type=sfwd-certificates',
			'name'	=> _x( 'Add New', 'Quiz Add New Certificate Label', 'learndash' ),
			'id'	=> 'sfwd-certificates',
			'menu_link'	=> 'edit.php?post_type=sfwd-certificates',
		),
		120 => array(
			'link'	=> 'edit.php?post_type=sfwd-certificates',
			'name'	=> _x( 'Certificates', 'Quiz Certificates Tab Label', 'learndash' ),
			'id'	=> 'edit-sfwd-certificates',
			'menu_link'	=> 'edit.php?post_type=sfwd-certificates',
		),
		130 => array(
			'link'	=> 'admin.php?page=learndash-lms-certificate_shortcodes',
			'name'	=> _x( 'Certificate Shortcodes', 'Quiz Certificate Shortcodes Tab Label', 'learndash' ),
			'id'	=> 'admin_page_learndash-lms-certificate_shortcodes',
			'menu_link'	=> 'edit.php?post_type=sfwd-certificates',
		),
		135 => array(
			'link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
			'name'	=> _x( 'PayPal Settings', 'PayPal Settings Tab Label ', 'learndash' ),
			'id'	=> 'sfwd-courses_page_sfwd-lms_sfwd_lms_post_type_sfwd-courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		136 => array(
			'link'	=> 'admin.php?page=learndash_custom_label',
			'name'	=> _x( 'Custom Labels', 'Custom Labels Tab Label', 'learndash' ),
			'id'	=> 'admin_page_learndash_custom_label',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		137 => array(
			'link'	=> 'admin.php?page=learndash_data_upgrades',
			'name'	=> _x( 'Data Upgrade', 'Data Upgrade Tab Label', 'learndash' ),
			'id'	=> 'admin_page_learndash_data_upgrades',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		140 => array(
			'link'	=> 'admin.php?page=nss_plugin_license-sfwd_lms-settings',
			'name'	=> _x( 'LMS License', 'LMS License Tab Label', 'learndash' ),
			'id'	=> 'admin_page_nss_plugin_license-sfwd_lms-settings',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		150 => array(
			'link'	=> 'admin.php?page=learndash_support',
			'name'	=> _x( 'Support', 'Support Tab Label', 'learndash' ),
			'id'	=> 'admin_page_learndash_support',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		/*
		150 => array(
			'external_link'	=> 'http://support.learndash.com',
			'target' => '_blank',
			'name'	=> _x( 'Support', 'Support Tab Label', 'learndash' ),
			'id'	=> 'external_link_support_learndash',
		),
		*/
		160 => array(
			'link'	=> 'admin.php?page=learndash-lms-reports',
			'name'	=> _x( 'User Reports', 'User Reports Tab Label', 'learndash' ),
			'id'	=> 'admin_page_learndash-lms-reports',
			'menu_link'	=> 'admin.php?page=learndash-lms-reports',
		),
		22 => array(
			'link'	=> 'edit.php?post_type=sfwd-transactions',
			'name'	=> _x( 'Transactions', 'Transactions Tab Label', 'learndash' ),
			'id'	=> 'edit-sfwd-transactions',
			'menu_link'	=> 'admin.php?page=learndash-lms-reports',
		),
		170 => array(
			'link'	=> 'edit.php?post_type=groups',
			'name'	=> _x( 'LearnDash Groups', 'LearnDash Groups Menu Label', 'learndash' ),
			'id'	=> 'edit-groups',
			'menu_link'	=> 'edit.php?post_type=groups',
		),
		180 => array(
			'link'	=> 'edit.php?post_type=sfwd-assignment',
			'name'	=> _x( 'Assignments', 'Assignments Menu Label', 'learndash' ),
			'id'	=> 'edit-sfwd-assignment',
			'menu_link'	=> 'edit.php?post_type=sfwd-assignment',
		),
		'group_admin_page'	=> array(
			'id'	=> 'admin_page_group_admin_page',
			'name' 	=> _x( 'Group Administration', 'Group Administration Menu Label', 'learndash' ),
			//'cap'	=> 'group_leader', // No longer only for Group Leaders
			'menu_link'	=> 'admin.php?page=group_admin_page',
		),
	);

	/**
	 * Filter array of tabs setup for LearnDash admin pages
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $admin_tabs
	 */
	$admin_tabs = apply_filters( 'learndash_admin_tabs', $admin_tabs );

	foreach ( $admin_tabs as $key => $admin_tab ) {
		if ( ! empty( $admin_tab['cap'] ) ) {
			if ( ! current_user_can( $admin_tab['cap'] ) ) {
				unset( $admin_tabs[ $key ] );
			}
		}
	}

	$admin_tabs_on_page = array(
			'edit-sfwd-courses'	=> array( 0, 10, 28, 24, 26 ),
			'sfwd-courses' => array( 0, 10, 28,  24, 26 ),
			'admin_page_learndash-lms-course_shortcodes' => array( 0, 10, 28,  24, 26 ),

			'edit-sfwd-lessons'	=> array( 30, 40, 50 ),
			'sfwd-lessons_page_sfwd-lms_sfwd_lms_post_type_sfwd-lessons' => array( 30, 40, 50 ),
			'sfwd-lessons' => array( 30, 40, 50 ),

			'edit-sfwd-topic' => array( 60, 70 ),
			'sfwd-topic' => array( 60, 70 ),

			'edit-sfwd-essays' => array( 80, 90, 100, 91, 101 ),
			'sfwd-essays' => array( 80, 90, 100, 91, 101 ),

			'edit-sfwd-quiz' => array( 80, 90, 100, 91, 101 ),
			'sfwd-quiz' => array( 80, 90, 100, 91, 101 ),
			'sfwd-quiz_edit' => array( 80, 90, 100, 101, 95 ),
			'admin_page_ldAdvQuiz' => array( 80, 90, 100, 91, 101  ),
			'admin_page_ldAdvQuiz_globalSettings' => array( 80, 90, 100, 91, 101  ),

			'edit-sfwd-certificates' => array( 110, 120, 130 ),
			'admin_page_learndash-lms-certificate_shortcodes' => array( 110, 120, 130 ),
			'sfwd-certificates'	=> array( 110, 120, 130 ),

			'admin_page_learndash-lms-reports' => array( 160, 22 ),
			'edit-sfwd-transactions' => array( 160, 22 ),

			'sfwd-courses_page_sfwd-lms_sfwd_lms_post_type_sfwd-courses' => array( 135, 136, 137, 140, 150 ),
			'admin_page_nss_plugin_license-sfwd_lms-settings' => array( 135, 136, 137, 140, 150 ),
			'admin_page_learndash_support' => array( 135, 136, 137, 140, 150 ),			
			'admin_page_learndash_custom_label' => array( 135, 136, 137, 140, 150 ),
			'admin_page_learndash_data_upgrades' => array( 135, 136, 137, 140, 150 ),
	);

	// We unset the essay sections/tabs because group leaders only need to see the essage listing not all the tabs. 
	if ( learndash_is_group_leader_user() ) {
		unset($admin_tabs_on_page['edit-sfwd-essays']);
		unset($admin_tabs_on_page['sfwd-essays']);
	}

	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sfwd-courses' ) {
		$admin_tabs_on_page['edit-category'] = array( 0, 10, 28, 24, 26 );
		$admin_tabs_on_page['edit-post_tag'] = array( 0, 10, 28, 24, 26 );
	}

	$current_page_id = get_current_screen()->id; //echo $current_page_id;

	$post_id = ! empty( $_GET['post_id'] ) ? $_GET['post_id'] : ( empty( $_GET['post'] ) ? 0 : $_GET['post'] );

	if ( empty( $post_id ) && ! empty( $_GET['quiz_id'] ) && $current_page_id == 'admin_page_ldAdvQuiz' ) {
		$post_id = learndash_get_quiz_id_by_pro_quiz_id( $_GET['quiz_id'] );
	}

	if ( $current_page_id == 'sfwd-quiz' || $current_page_id == 'admin_page_ldAdvQuiz' ) {

		if ( ! empty( $_GET['module'] ) ) {
			$current_page_id = $current_page_id.'_'.$_GET['module'];
			if ( empty( $admin_tabs_on_page[ $current_page_id ] ) ) {
				$admin_tabs_on_page[ $current_page_id ] = $admin_tabs_on_page['admin_page_ldAdvQuiz'];
			}
		} else if ( ! empty( $_GET['post'] ) ) {
			$current_page_id = $current_page_id.'_edit';
		}

		if ( ! empty( $post_id ) ) {
			$quiz_id = learndash_get_setting( $post_id, 'quiz_pro', true );

			if ( ! empty( $quiz_id ) ) {
				$admin_tabs_on_page[ $current_page_id ] = array( 80, 90, 100, 91, 101, 95, 102, 104, 106 );
				foreach ( $admin_tabs_on_page[ $current_page_id ] as $admin_tab_id ) {
					$admin_tabs[ $admin_tab_id ]['link'] = str_replace( '[quiz_id]', $quiz_id, $admin_tabs[ $admin_tab_id ]['link'] );
				}
			}

		}
	}

	if ( ( $current_page_id == 'edit-sfwd-essays') || ( $current_page_id == 'sfwd-essays') ) {
		if ( ! empty( $post_id ) ) {
		
			if (($current_page_id == 'edit-sfwd-essays') && (isset($_GET['quiz_id']))) {
				$quiz_id = intval($_GET['quiz_id']);
			} else if ($current_page_id == 'sfwd-essays') {
				$quiz_id = get_post_meta($post_id, 'quiz_id', true);			
			}
	
			if ( ! empty( $quiz_id ) ) {
			
				$admin_tabs[91]['id'] = $current_page_id;
			
				$admin_tabs_on_page[ $current_page_id ] = array( 80, 90, 100, 91, 101 );
				
				foreach ( $admin_tabs_on_page[ $current_page_id ] as $admin_tab_id ) {
					$admin_tabs[ $admin_tab_id ]['link'] = str_replace( '[quiz_id]', $quiz_id, $admin_tabs[ $admin_tab_id ]['link'] );
				}
			}
		}
	}

	/**
	 * Filter admin tabs on page
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $admin_tabs_on_page
	 */
	$admin_tabs_on_page = apply_filters( 'learndash_admin_tabs_on_page', $admin_tabs_on_page, $admin_tabs, $current_page_id );

	if ( empty( $admin_tabs_on_page[ $current_page_id ] ) ) {
		$admin_tabs_on_page[ $current_page_id ] = array();
	}

	/**
	 * Filter current admin tabs on page
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $learndash_current_admin_tabs_on_page
	 */
	$admin_tabs_on_page[ $current_page_id ] = apply_filters( 'learndash_current_admin_tabs_on_page', $admin_tabs_on_page[ $current_page_id ], $admin_tabs, $admin_tabs_on_page, $current_page_id );

	//	echo $current_page_id;
	if ( ! empty( $post_id ) ) {
		foreach ( $admin_tabs_on_page[ $current_page_id ] as $admin_tab_id ) {
			$admin_tabs[ $admin_tab_id ]['link'] = str_replace( '[post_id]', $post_id, $admin_tabs[ $admin_tab_id ]['link'] );
		}
	}

	if ( ! empty( $admin_tabs_on_page[ $current_page_id ] ) && count( $admin_tabs_on_page[ $current_page_id ] ) ) {
		echo '<h1 class="nav-tab-wrapper">';
		$tabid = 0;
		foreach ( $admin_tabs_on_page[ $current_page_id] as $admin_tab_id ) {
			if ( ! empty( $admin_tabs[ $admin_tab_id ]['id'] ) ) {
				$class = ( $admin_tabs[ $admin_tab_id ]['id'] == $current_page_id ) ? 'nav-tab nav-tab-active' : 'nav-tab';
				$url = ! empty( $admin_tabs[ $admin_tab_id ]['external_link'] ) ? $admin_tabs[ $admin_tab_id ]['external_link'] : admin_url( $admin_tabs[ $admin_tab_id ]['link'] );
				$target = ! empty( $admin_tabs[ $admin_tab_id ]['target'] ) ? 'target="'.$admin_tabs[ $admin_tab_id ]['target'].'"':'';
				echo '<a href="'.$url.'" class="'.$class.' nav-tab-'.$admin_tabs[ $admin_tab_id ]['id'].'"  '.$target.'>'.$admin_tabs[ $admin_tab_id ]['name'].'</a>';
			}
		}
		echo '</h1>';
	}

	global $learndash_current_page_link;
	$learndash_current_page_link = '';

	if ($current_page_id == 'sfwd-assignment') {
		add_action( 'admin_footer', 'learndash_select_menu' );
	} else {

		foreach ( $admin_tabs as $admin_tab ) {
			if ( $current_page_id == trim( $admin_tab['id'] ) ) {
				if (isset($admin_tab['menu_link'])) {
					$learndash_current_page_link = trim( $admin_tab['menu_link'] );
				} 
				
				add_action( 'admin_footer', 'learndash_select_menu' );
				break;
			}
		}
	}
}

add_action( 'all_admin_notices', 'learndash_admin_tabs' );



/**
 * Change label in admin bar on single topic to 'Edit Topic'
 *
 * @todo  consider for deprecation, action is commented
 *
 * @since 2.1.0
 */
function learndash_admin_bar_link() {
	global $wp_admin_bar;
	global $post;

	if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
		return;
	}

	if ( is_single() && $post->post_type == 'sfwd-topic' ) {
		$wp_admin_bar->add_menu( array(
			'id' => 'edit_fixed',
			'parent' => false,
			'title' => sprintf( _x( 'Edit %s', 'Edit Topic Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'topic' ) ),
			'href' => get_edit_post_link( $post->id )
		) );
	}
}



/**
 * Output Reports Page
 *
 * @since 2.1.0
 */
function learndash_lms_reports_page() {
	?>
		<div  id="learndash-reports"  class="wrap">
			<h1><?php _e( 'User Reports', 'learndash' ); ?></h1>
			<br>
			<div class="sfwd_settings_left">
				<div class=" " id="sfwd-learndash-reports_metabox">
					<div class="inside">
						<a class="button-primary" href="<?php echo admin_url( 'admin.php?page=learndash-lms-reports&action=sfp_update_module&nonce-sfwd='.wp_create_nonce( 'sfwd-nonce' ).'&page_options=sfp_home_description&courses_export_submit=Export' ); ?>"><?php printf( _x( 'Export User %s Data', 'Export User Course Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></a>
						<a class="button-primary" href="<?php echo admin_url( 'admin.php?page=learndash-lms-reports&action=sfp_update_module&nonce-sfwd='.wp_create_nonce( 'sfwd-nonce' ).'&page_options=sfp_home_description&quiz_export_submit=Export' ); ?>"><?php printf( _x( 'Export %s Data', 'Export Quiz Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?></a>
						<?php
							/**
							 * Run actions after report page buttons print
							 *
							 * @since 2.1.0
							 */
							do_action( 'learndash_report_page_buttons' );
						?>
					</div>
				</div>
			</div>
		</div>
	<?php
}



/**
 * Add Javascript to admin footer
 *
 * @since 2.1.0
 */
function learndash_select_menu() {
	global $learndash_current_page_link;
	?>
		<script type="text/javascript">
		jQuery(window).load( function( $) {
			jQuery("body").removeClass("sticky-menu");
			jQuery("#toplevel_page_learndash-lms, #toplevel_page_learndash-lms > a").removeClass('wp-not-current-submenu' );
			jQuery("#toplevel_page_learndash-lms").addClass('current wp-has-current-submenu wp-menu-open' );
			<?php if ( ! empty( $learndash_current_page_link ) ) : ?>
				jQuery("#toplevel_page_learndash-lms a[href='<?php echo $learndash_current_page_link;?>']").parent().addClass("current");
			<?php endif; ?>
		});
		</script>
	<?php
};



/**
 * Shortcode columns in admin for Quizes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_shortcode_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array( 'shortcode' => __( 'Shortcode', 'learndash' ) ),
		array_slice( $cols, 3 )
	);
}



/**
 * Assigned Course columns in admin for Lessons and Quizes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_course_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array( 
			'course' => sprintf( _x( 'Assigned %s', 'Assigned Course Label', 'learndash' ) , LearnDash_Custom_Label::get_label( 'course' ) ) 
		),
		array_slice( $cols, 3 )
	);
}



/**
 * Assigned Lesson & Assigned Course columns in admin for Topics and Assignments
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_lesson_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array(
			'lesson' => sprintf( _x( 'Assigned %s', 'Assigned Lesson Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
			'course' => sprintf( _x( 'Assigned %s', 'Assigned Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
		),
		array_slice( $cols, 3 )
	);
}



/**
 * Status columns in admin for Assignments
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_assignment_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array(
			'approval_status' => __( 'Status', 'learndash' ),
			'approval_points' => __( 'Points', 'learndash' ),
		),
		array_slice( $cols, 3 )
	);
}


/**
 * Remove tags column for quizzes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function remove_tags_column( $cols ){
	unset( $cols['tags'] );
	return $cols;
}



/**
 * Remove categories column for quizzes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function remove_categories_column( $cols ){
	unset( $cols['categories'] );
	return $cols;
}



/**
 * Output approval status for assignment in admin column
 *
 * @since 2.1.0
 *
 * @param  string 	$column_name
 * @param  int 		$id
 */
function manage_asigned_assignment_columns( $column_name, $assignment_id ) {
	switch ( $column_name ) {
		case 'approval_status':
			
		//$current_screen = get_current_screen();
		//error_log('current_screen<pre>'. print_r($current_screen, true) .'</pre>');
			
			$approval_status_flag = learndash_is_assignment_approved_by_meta( $assignment_id );
			if ($approval_status_flag == 1) {
				$approval_status_label = __( 'Approved', 'learndash' );
			} else {
				$approval_status_label = __( 'Not Approved', 'learndash' );
			}
			$approval_status_url = admin_url( 'edit.php?post_type='.@$_GET['post_type'].'&approval_status='. $approval_status_flag );
			
			echo '<a " href="'. $approval_status_url .'">'. $approval_status_label .'</a>';
			
			break;
			
		case 'approval_points':
			if ( learndash_assignment_is_points_enabled( $assignment_id ) ) {
				$max_points = 0;
			
				$assignment_settings_id = intval( get_post_meta( $assignment_id, 'lesson_id', true ) );
				if (!empty( $assignment_settings_id ) ) {
					$max_points = learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_amount' );
				} 
			
				$current_points = get_post_meta( $assignment_id, 'points', true );
				if ( ( $current_points == 'Pending' ) || ( $current_points == '' ) ) {
					if ( $approval_status_flag != 1 ) {
						$current_points = '<input id="assignment_points_'. $assignment_id .'" class="small-text" type="number" value="0" max="'. $max_points .'" min="0" step="1" name="assignment_points['. $assignment_id .']" />';
					} else {
						$current_points = 0;
					}
				}
				echo sprintf( _x('%s / %s', 'placeholders: current points / maximum point for assignment', 'learndash'), $current_points, $max_points);
				
				$approval_status_flag = learndash_is_assignment_approved_by_meta( $assignment_id );
				if ($approval_status_flag != 1) {
					?> <button id="assignment_approve_<?php echo $assignment_id ?>" class="small assignment_approve_single"><?php _e('approve', 'learndash'); ?></button><?php
				}
			
			} else {
				_e('Not Enabed', 'learndash');
			}		
			break;
			
		default:	
			break;
	}
}



/**
 * Output values for Assigned Courses in admin columns
 * for lessons, quizzes, topics, assignments
 *
 * @since 2.1.0
 *
 * @param  string 	$column_name
 * @param  int 		$id
 */
function manage_asigned_course_columns( $column_name, $id ){
	switch ( $column_name ) {
		case 'shortcode':
			$quiz_pro = learndash_get_setting( $id, 'quiz_pro', true );
			if ( ! empty( $quiz_pro) ) {
				echo '[LDAdvQuiz '.$quiz_pro.']';
			} else {
				echo '-';
			}
			break;
		case 'course':
			$url = admin_url( 'edit.php?post_type='.@$_GET['post_type'].'&course_id='.learndash_get_course_id( $id ) );
			if ( learndash_get_course_id( $id ) ){
				echo '<a href="'.$url .'">'.get_the_title( learndash_get_course_id( $id ) ).'</a>';
			} else {
				echo '&#8212;';
			}
			break;

		case 'lesson':
			$parent_id = learndash_get_setting( $id, 'lesson' );
			if ( ! empty( $parent_id ) ) {
				$url = admin_url( 'edit.php?post_type='.@$_GET['post_type'].'&lesson_id='.$parent_id );
				echo '<a href="'.$url.'">'.get_the_title( $parent_id ).'</a>';
			} else {
				echo  '&#8212;';
			}
			break;
		default:
			break;
	}
}



/**
 * Output select dropdown before the filter button to filter post listing
 * by course
 *
 * @since 2.1.0
 */
function restrict_listings_by_course() {
	global $pagenow;

	$ld_post_types = array( 
		'sfwd-lessons', 
		'sfwd-topic', 
		'sfwd-quiz', 
		'sfwd-certificates',
		'groups', 
		'sfwd-assignment',
		'sfwd-essays',
	);

	if ( !is_admin() ) return;
	if ( $pagenow != 'edit.php' ) return;
 	if ( ( isset( $_GET['post_status'] ) ) && ( $_GET['post_status'] == 'trash') ) return;
	if ( ( !isset( $_GET['post_type'] ) ) || ( !in_array( $_GET['post_type'], $ld_post_types ) ) ) return;

	$cpt_filters_shown['sfwd-courses'] = array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-assignment', 'sfwd-quiz', 'sfwd-essays', /* 'sfwd-certificates', */ 'groups' );
	$cpt_filters_shown['sfwd-lessons'] = array( 'sfwd-topic', 'sfwd-assignment', 'sfwd-quiz', 'sfwd-essays' );
	$cpt_filters_shown['sfwd-topic'] = array();
	$cpt_filters_shown['sfwd-quiz'] = array( 'sfwd-essays', /* 'sfwd-certificates' */ );
		
	$cpt_filters_shown = apply_filters( 'learndash-admin-cpt-filters-display', $cpt_filters_shown );

	$course_ids = array();
	$lesson_ids = array();
	$group_ids = array();

	// Courses filter
	if ( in_array( $_GET['post_type'], $cpt_filters_shown['sfwd-courses'] ) ) {
		$query_options_course = array( 
			'post_type' 		=> 	'sfwd-courses', 
			'post_status' 		=> 	'any',  
			'posts_per_page' 	=> 	-1,
			'orderby'			=>	'title',
			'order'				=>	'ASC'	
		);
		
		if ( learndash_is_group_leader_user( get_current_user_id() ) ) {
			$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
			if ( ! empty( $group_ids ) && is_array( $group_ids ) ) {
				foreach( $group_ids as $group_id ) {
					$group_course_ids = learndash_group_enrolled_courses( $group_id );
					if ( ! empty( $group_course_ids ) && is_array( $group_course_ids ) ) {
						$course_ids = array_merge( $course_ids, $group_course_ids );
					}
				}
			}
		
			if ( ! empty( $course_ids ) && count( $course_ids ) ) {
				$query_options_course['post__in'] = $course_ids;
			}
		}

		$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
		if ( $lazy_load == true ) {
			$lazy_load = apply_filters('learndash_element_lazy_load_admin_'. $_GET['post_type'] .'_filters', true);
			if ( $lazy_load == true ) {
				$query_options_course['paged'] 			= 	1;
				$query_options_course['posts_per_page'] = 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, $_GET['post_type'] );
			}
		}

		$query_options_course = apply_filters( 'learndash_course_post_options_filter', $query_options_course, $_GET['post_type'] );
		
		$query_posts_course = new WP_Query( $query_options_course );
		
		if ( ! empty( $query_posts_course->posts ) ) {
			if ( count( $query_posts_course->posts ) >= $query_posts_course->found_posts ) {
				// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
				$lazy_load = false;
			}

			if ($lazy_load == true) {
				$lazy_load_data = array();
				$lazy_load_data['query_vars'] 	= 	$query_options_course;
				$lazy_load_data['query_type']	= 	'WP_Query';
			
				if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) )
					$lazy_load_data['value']	=	intval( $_GET['course_id'] );
				else
					$lazy_load_data['value']	=	0;
			
				$lazy_load_data = ' learndash_lazy_load_data="'. htmlspecialchars( json_encode( $lazy_load_data ) ) .'" ';
			} else {
				$lazy_load_data = '';
			}

			echo "<select ". $lazy_load_data ." name='course_id' id='course_id' class='postform'>";
			echo "<option value=''>". sprintf( _x( 'Show All %s', 'Show All Courses Option Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'courses' ) ).'</option>';

			foreach ( $query_posts_course->posts as $p ) {
				echo '<option value='. $p->ID, ( @$_GET['course_id'] == $p->ID ? ' selected="selected"' : '').'>' . $p->post_title .'</option>';
			}
			echo '</select>';
		
			$lazy_load_spinner = '<span style="display:none;" class="learndash_lazy_loading"><img class="learndash_lazy_load_spinner" alt="'. __('loading', 'learndash') .'" src="'. admin_url('/images/wpspin_light.gif') .'" /> </span>';
			echo $lazy_load_spinner;
		
		} 
	}
	
	// Lessons filter
	if ( in_array( $_GET['post_type'], $cpt_filters_shown['sfwd-lessons'] ) ) {
			
		$query_options_lesson = array( 
			'post_type' 		=> 	'sfwd-lessons', 
			'post_status' 		=> 	'any',  
			'posts_per_page' 	=> 	-1,
			'orderby'			=>	'title',
			'order'				=>	'ASC'	
		);

		$LOAD_LESSONS = true;
		if ( learndash_is_group_leader_user( get_current_user_id() ) ) {
			//error_log('in GROUP LEADER');
			//error_log('course_ids<pre>'. print_r($course_ids, true) .'</pre>');

			if ( !empty( $course_ids ) ) {

				if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
					if ( in_array( $_GET['course_id'], $course_ids ) ) {

						if (!isset( $query_options_lesson['meta_query'] ) )
							$query_options_lesson['meta_query'] = array();
			
						$query_options_lesson['meta_query'][] = array(
							'key'     => 'course_id',
							'value'   => array( intval( $_GET['course_id'] ) ),
							'compare' => 'IN',
						);
					} else {
						$LOAD_LESSONS = false;
					}
				} else {
					if (!isset( $query_options_lesson['meta_query'] ) )
						$query_options_lesson['meta_query'] = array();
				
					$query_options_lesson['meta_query'][] = array(
						'key'     => 'course_id',
						'value'   => $course_ids,
						'compare' => 'IN',
					);
				}
			} else {
				$LOAD_LESSONS = false;
			}
		} else {
			
			// If the course_id is selected we limit the lesson selector to only those related to course_id
			// @since 2.3
			if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {

				if (!isset( $query_options_lesson['meta_query'] ) )
					$query_options_lesson['meta_query'] = array();
			
				$query_options_lesson['meta_query'][] = array(
					'key'     => 'course_id',
					'value'   => array( intval( $_GET['course_id'] ) ),
					'compare' => 'IN',
				);
			
			} else {
				if ( ! empty( $course_ids ) && count( $course_ids ) ) {
				
					if (!isset( $query_options_lesson['meta_query'] ) )
						$query_options_lesson['meta_query'] = array();
				
					$query_options_lesson['meta_query'][] = array(
						'key'     => 'course_id',
						'value'   => $course_ids,
						'compare' => 'IN',
					);
				}
			}
		}
				
		
		if ( $LOAD_LESSONS ) {
			//error_log('query_options_lesson<pre>'. print_r($query_options_lesson, true) .'</pre>');

			$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
			if ( $lazy_load == true ) {
				$lazy_load = apply_filters('learndash_element_lazy_load_admin_'. $_GET['post_type'] .'_filters', true);
				if ( $lazy_load == true ) {
					$query_options_lesson['paged'] 			= 	1;
					$query_options_lesson['posts_per_page'] = 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, $_GET['post_type']);
				}
			}
		
			$query_options_lesson = apply_filters( 'learndash_lesson_post_options_filter', $query_options_lesson, $_GET['post_type'] );		
			$query_posts_lesson = new WP_Query( $query_options_lesson );
			if ( ! empty( $query_posts_lesson->posts ) ) {
				if ( count( $query_posts_lesson->posts ) >= $query_posts_lesson->found_posts ) {
					// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
					$lazy_load = false;
				}

				if ($lazy_load == true) {
					$lazy_load_data = array();
					$lazy_load_data['query_vars'] 	= 	$query_options_lesson;
					$lazy_load_data['query_type']	= 	'WP_Query';
			
					if (isset( $_GET['lesson_id'] ) )
						$lazy_load_data['value']	=	intval( $_GET['lesson_id'] );
					else
						$lazy_load_data['value']	=	0;
			
					$lazy_load_data = ' learndash_lazy_load_data="'. htmlspecialchars( json_encode( $lazy_load_data ) ) .'" ';
				} else {
					$lazy_load_data = '';
				}

				echo "<select ". $lazy_load_data ." name='lesson_id' id='lesson_id' class='postform'>";
				echo "<option value=''>".sprintf( _x( 'Show All %s', 'Show All Lessons Option Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lessons' ) ).'</option>';

				foreach ( $query_posts_lesson->posts as $p ) {
					echo '<option value='. $p->ID, ( @$_GET['lesson_id'] == $p->ID ? ' selected="selected"' : '').'>' . $p->post_title .'</option>';
				
					if ( ( $_GET['post_type'] == 'sfwd-essays' ) || ( $_GET['post_type'] == 'sfwd-quiz' ) || ( $_GET['post_type'] == 'sfwd-assignment' ) ) {
						$query_options_topic = array( 
							'post_type' 		=> 	'sfwd-topic', 
							'post_status' 		=> 	'any',
							'posts_per_page' 	=> 	-1,
							'orderby'			=>	'title',
							'order'				=>	'ASC',
							'meta_query'		=>	array(
								array(
									'key'     => 'lesson_id',
									'value'   => $p->ID,
									'compare' => '=',
								)
							)		
						);
				
						$query_posts_topic = new WP_Query( $query_options_topic );
						if ( ! empty( $query_posts_topic->posts ) ) {
							foreach ( $query_posts_topic->posts as $topic ) {
								echo '<option value='. $topic->ID, ( @$_GET['lesson_id'] == $topic->ID ? ' selected="selected"' : '').'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $topic->post_title .'</option>';
							}
						}
					}
				}
				echo '</select>';
			}
		}
	}

	// Topicss filter
	if ( in_array( $_GET['post_type'], $cpt_filters_shown['sfwd-topic'] ) ) {
			
		$query_options_topic = array( 
			'post_type' 		=> 	'sfwd-topic', 
			'post_status' 		=> 	'any',  
			'posts_per_page' 	=> 	-1,
			'orderby'			=>	'title',
			'order'				=>	'ASC'	
		);

		// If the course_id is selected we limit the lesson selector to only those related to course_id
		// @since 2.3
		if ( ( isset( $_GET['lesson_id'] ) ) && ( !empty( $_GET['lesson_id'] ) ) ) {
			$query_options_topic['meta_key'] = 'lesson_id';
			$query_options_topic['meta_value'] = intval( $_GET['lesson_id'] );
			$query_options_topic['meta_compare'] = '=';
		} else {
			if ( ! empty( $lesson_ids ) && count( $lesson_ids ) ) {
				if (!isset( $query_options_topic['meta_query'] ) )
					$query_options_topic['meta_query'] = array();
				
				$query_options_topic['meta_query'][] = array(
					'key'     => 'lesson_id',
					'value'   => $lesson_ids,
					'compare' => 'IN',
				);
			}
		}

		$lazy_load = apply_filters('learndash_element_lazy_load_admin', true);
		if ( $lazy_load == true ) {
			$lazy_load = apply_filters('learndash_element_lazy_load_admin_'. $_GET['post_type'] .'_filters', true);
			if ( $lazy_load == true ) {
				$query_options_topic['paged'] 			= 	1;
				$query_options_topic['posts_per_page'] 	= 	apply_filters('learndash_element_lazy_load_per_page', LEARNDASH_LMS_DEFAULT_LAZY_LOAD_PER_PAGE, $_GET['post_type']);
			}
		}
		
		$query_options_topic = apply_filters( 'learndash_lesson_post_options_filter', $query_options_topic, $_GET['post_type'] );		
		
		$query_posts_topic = new WP_Query( $query_options_topic );
		
		if ( ! empty( $query_posts_topic->posts ) ) {
			if ( count( $query_posts_topic->posts ) >= $query_posts_topic->found_posts ) {
				// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
				$lazy_load = false;
			}

			if ($lazy_load == true) {
				$lazy_load_data = array();
				$lazy_load_data['query_vars'] 	= 	$query_options_topic;
				$lazy_load_data['query_type']	= 	'WP_Query';
			
				if (isset( $_GET['topic_id'] ) )
					$lazy_load_data['value']	=	intval( $_GET['topic_id'] );
				else
					$lazy_load_data['value']	=	0;
			
				$lazy_load_data = ' learndash_lazy_load_data="'. htmlspecialchars( json_encode( $lazy_load_data ) ) .'" ';
			} else {
				$lazy_load_data = '';
			}

			echo "<select ". $lazy_load_data ." name='topic_id' id='topic_id' class='postform'>";
			echo "<option value=''>".sprintf( _x( 'Show All %s', 'Show All Topics Option Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'topic' ) ).'</option>';
			foreach ( $query_posts_topic->posts as $p ) {
				echo '<option value='. $p->ID, ( @$_GET['topic_id'] == $p->ID ? ' selected="selected"' : '').'>' . $p->post_title .'</option>';
			}
			echo '</select>';
		}
	}


	// Quiz Filters
	if ( in_array( $_GET['post_type'], $cpt_filters_shown['sfwd-quiz'] ) ) {	
		
		//$quiz    = new WpProQuiz_Model_QuizMapper();
		//$quizzes = $quiz->fetchAll();
		//echo "<select name='quiz_id' id='quiz_id' class='postform'>";
		//echo "<option value=''>". sprintf( _x( 'Show All %s', 'Show All Quizzes', 'learndash' ), LearnDash_Custom_Label::get_label( 'quizzes' ) ) .'</option>';
		//foreach ( $quizzes as $quiz ) {
		//	echo '<option value='. $quiz->getId(), ( @$_GET['quiz_id'] == $quiz->getId() ? ' selected="selected"' : '').'>' . $quiz->getName() .'</option>';
		//}
		//echo '</select>';
		
		$query_options_quiz = array( 
			'post_type' 		=> 	'sfwd-quiz', 
			'post_status' 		=> 	'any',  
			'posts_per_page' 	=> 	-1,
			'orderby'			=>	'title',
			'order'				=>	'ASC'	
		);

		// If the course_id is selected we limit the lesson selector to only those related to course_id
		// @since 2.3
		if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
			$query_options_quiz['meta_key'] = 'course_id';
			$query_options_quiz['meta_value'] = intval( $_GET['course_id'] );
		} else {
			if ( ! empty( $course_ids ) && count( $course_ids ) ) {
				
				if (!isset( $query_options_quiz['meta_query'] ) )
					$query_options_quiz['meta_query'] = array();
				
				$query_options_quiz['meta_query'][] = array(
					'key'     => 'course_id',
					'value'   => $course_ids,
					'compare' => 'IN',
				);
			}
		}
		$query_options_quiz = apply_filters( 'learndash_lesson_post_options_filter', $query_options_quiz, $_GET['post_type'] );
		$query_posts_quiz = new WP_Query( $query_options_quiz );

		if ( ! empty( $query_posts_quiz->posts ) ) {
			if ( count( $query_posts_quiz->posts ) >= $query_posts_quiz->found_posts ) {
				// If the number of returned posts is equal or greater then found_posts then no need to run lazy load
				$lazy_load = false;
			}

			if ($lazy_load == true) {
				$lazy_load_data = array();
				$lazy_load_data['query_vars'] 	= 	$query_options_quiz;
				$lazy_load_data['query_type']	= 	'WP_Query';
			
				if (isset( $_GET['quiz_id'] ) )
					$lazy_load_data['value']	=	intval( $_GET['quiz_id'] );
				else
					$lazy_load_data['value']	=	0;
			
				$lazy_load_data = ' learndash_lazy_load_data="'. htmlspecialchars( json_encode( $lazy_load_data ) ) .'" ';
			} else {
				$lazy_load_data = '';
			}

			echo "<select ". $lazy_load_data ." name='quiz_id' id='lesson_id' class='postform'>";
			echo "<option value=''>".sprintf( _x( 'Show All %s', 'Show All Quizzes Option Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quizzes' ) ).'</option>';
			foreach ( $query_posts_quiz->posts as $p ) {
				$quiz_pro_id = get_post_meta( $p->ID, 'quiz_pro_id', true );
				if ( !empty( $quiz_pro_id ) ) {
					echo '<option value="'. $quiz_pro_id .'" '. selected( @$_GET['quiz_id'], $quiz_pro_id, false) .'>' . $p->post_title .'</option>';
				}
			}
			echo '</select>';
		}
	}

	if ( $_GET['post_type'] == 'sfwd-assignment' ) {
		$selected_1 = '';
		$selected_0 = '';

		if ( isset( $_GET['approval_status'] ) ) {
			if ( $_GET['approval_status'] == 1 ) {
				$selected_1 = 'selected="selected"';
				$selected_0 = '';
			} if ( $_GET['approval_status'] == 0 ) {
				$selected_0 = 'selected="selected"';
				$selected_1 = '';
			}
		} else if ((isset($_GET['approval_status'])) && ( $_GET['approval_status'] == 0 )) {
			$selected_0 = 'selected="selected"';
			$selected_1 = '';
		}
		?>
			<select name='approval_status' id='approval_status' class='postform'>
				<option value='-1'><?php _e( 'Approval Status', 'learndash' ); ?></option>
				<option value='1' <?php echo $selected_1; ?>><?php _e( 'Approved', 'learndash' ); ?></option>
				<option value='0' <?php echo $selected_0; ?>><?php _e( 'Not Approved', 'learndash' ); ?></option>
			</select>
		<?php
	}
}



/**
 * Filter queries in admin post listing by what user selects
 *
 * @since 2.1.0
 *
 * @param  object $query 	WP_Query object
 * @return object $q_vars    WP_Query object
 */
function course_table_filter( $query ) {
	global $pagenow, $typenow;
	$q_vars = &$query->query_vars;

	if ( !is_admin() ) return;
	if ( $pagenow != 'edit.php' ) return;
	if ( !$query->is_main_query() ) return;
	if ( empty( $typenow ) ) return;
	
	
	//error_log('in '. __FUNCTION__ );
	//error_log('_GET<pre>'. print_r($_GET, true) .'</pre>');
	//error_log('q_vars<pre>'. print_r($q_vars, true) .'</pre>');

	/*
	if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) 
	  && ( $typenow == 'sfwd-lessons' || $typenow == 'sfwd-topic' || $typenow == 'sfwd-quiz' || $typenow == 'sfwd-assignment' || $typenow == 'sfwd-essays' ) ) {
			$q_vars['meta_query'][] = array(
				'key' => 'course_id',
				'value'	=> $_GET['course_id'],
			);
		}

		if ( ( isset($_GET['lesson_id'] ) ) && ( !empty( $_GET['lesson_id'] ) ) && ( $typenow == 'sfwd-topic' || $typenow == 'sfwd-assignment' || $typenow == 'sfwd-essays' ) ) {
			$q_vars['meta_query'][] = array(
				'key' => 'lesson_id',
				'value'	=> $_GET['lesson_id'],
			);
		}

		if ( ( isset( $_GET['quiz_id'] ) )  && ( !empty( $_GET['quiz_id'] ) ) && ( $typenow == 'sfwd-essays' ) ) {
			$q_vars['meta_query'][] = array(
				'key' 	=>	'quiz_id',
				'value'	=> 	intval( $_GET['quiz_id'] ),
			);
		}

		// set custom post status anytime we are looking at essays with no particular post status
		if ( ( isset( $_GET['post_status'] ) ) && ( !isset( $_GET['post_status'] ) ) && ( $typenow == 'sfwd-essays' ) ) {
			$q_vars['post_status'] = array( 'graded', 'not_graded' );
		}

		if ( ( isset( $_GET['approval_status'] ) ) && ( $typenow == 'sfwd-topic' || $typenow == 'sfwd-assignment' ) ) {
			if ( $_GET['approval_status'] == 1 ) {
				$q_vars['meta_query'][] = array(
					'key' 	=> 	'approval_status',
					'value'	=> 	1,
				);
			} else if ( $_GET['approval_status'] == 0 ) {
				$q_vars['meta_query'][] = array(
					'key' 		=> 	'approval_status',
					'compare' 	=> 	'NOT EXISTS',
				);
			}
		}
	}
	*/
	
	if ( $typenow == 'sfwd-lessons' ) {
		if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'course_id',
				'value'		=> 	intval( $_GET['course_id'] ),
			);
		}
			
	} else if ( $typenow == 'sfwd-topic' ) {
		if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'course_id',
				'value'		=> 	intval($_GET['course_id']),
			);
		}
		
		if ( ( isset( $_GET['lesson_id'] ) ) && ( !empty( $_GET['lesson_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'lesson_id',
				'value'		=> 	intval( $_GET['lesson_id'] ),
			);
		}
		
		$q_vars['relation'] = 'AND';
		
	} else if ( $typenow == 'sfwd-quiz' ) {
		if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();
			
			$q_vars['meta_query'][] = array(
				'key' => 'course_id',
				'value'	=> intval($_GET['course_id']),
			);
		}

		if ( ( isset( $_GET['lesson_id'] ) ) && ( !empty( $_GET['lesson_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'lesson_id',
				'value'		=> 	intval( $_GET['lesson_id'] ),
			);
		}
	} else if ( $typenow == 'sfwd-assignment' ) {
		if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'course_id',
				'value'		=> 	intval( $_GET['course_id'] ),
			);
		}

		if ( ( isset( $_GET['lesson_id'] ) ) && ( !empty( $_GET['lesson_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'lesson_id',
				'value'		=> 	intval( $_GET['lesson_id'] ),
			);
		}
	} else if ( $typenow == 'groups' ) {
		if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
			$groups = learndash_get_course_groups( intval($_GET['course_id']), true );
			if ( !empty( $groups ) ) 
				$q_vars['post__in'] = $groups;
			else 
				$q_vars['post__in'] = array(-1);
		}
	} else if ( $typenow == 'sfwd-essays' ) {
		if ( ( isset( $_GET['course_id'] ) ) && ( !empty( $_GET['course_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'course_id',
				'value'		=> 	intval( $_GET['course_id'] ),
			);
		}

		if ( ( isset( $_GET['lesson_id'] ) ) && ( !empty( $_GET['lesson_id'] ) ) ) {
			if ( !isset( $q_vars['meta_query'] ) ) $q_vars['meta_query'] = array();

			$q_vars['meta_query'][] = array(
				'key' 		=> 	'lesson_id',
				'value'		=> 	intval( $_GET['lesson_id'] ),
			);
		}
	}
//	if ( isset( $q_vars['meta_query'] ) ) {
//		error_log('meta_query<pre>'. print_r( $q_vars['meta_query'], true ) .'</pre>');
//	} else {
//		error_log('meta_query not set');
//	}	
}



/**
 * Generate lesson id's and course id's once for all existing lessons, quizzes and topics
 *
 * @since 2.1.0
 */
function learndash_generate_patent_course_and_lesson_id_onetime() {

	if ( isset( $_GET['learndash_generate_patent_course_and_lesson_ids_onetime'] ) || get_option( 'learndash_generate_patent_course_and_lesson_ids_onetime', 'yes' ) == 'yes' ) {
		$quizzes = get_posts( 'post_type=sfwd-quiz&posts_per_page=-1' );

		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {
				update_post_meta( $quiz->ID, 'course_id', learndash_get_course_id( $quiz->ID ) );
				$meta = get_post_meta( $quiz->ID, '_sfwd-quiz', true );
				if ( ! empty( $meta['sfwd-quiz_lesson'] ) ) {
					update_post_meta( $quiz->ID, 'lesson_id', $meta['sfwd-quiz_lesson'] );
				}
			}//exit;
		}

		$topics = get_posts( 'post_type=sfwd-topic&posts_per_page=-1' );

		if ( ! empty( $topics) ) {
			foreach ( $topics as $topic ) {
				update_post_meta( $topic->ID, 'course_id', learndash_get_course_id( $topic->ID ) );
				$meta = get_post_meta( $topic->ID, '_sfwd-topic', true );
				if ( ! empty( $meta['sfwd-topic_lesson'] ) ) {
					update_post_meta( $topic->ID, 'lesson_id', $meta['sfwd-topic_lesson'] );
				}
			}
		}

		$lessons = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1' );

		if ( ! empty( $lessons) ) {
			foreach ( $lessons as $lesson ) {
				update_post_meta( $lesson->ID, 'course_id', learndash_get_course_id( $lesson->ID ) );
			}
		}

		update_option( 'learndash_generate_patent_course_and_lesson_ids_onetime', 'no' );

	}
}

add_action( 'admin_init', 'learndash_generate_patent_course_and_lesson_id_onetime' );



/**
 * On post save, update post id's that maintain relationships between
 * courses, lessons, topics, and quizzes
 *
 * @since 2.1.0
 *
 * @param  int $post_id
 */
function learndash_patent_course_and_lesson_id_save( $post_id ) {

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

	if ( 'sfwd-lessons' == $_POST['post_type'] || 'sfwd-quiz' == $_POST['post_type'] || 'sfwd-topic' == $_POST['post_type'] ) {
		if ( isset( $_POST[ $_POST['post_type'].'_course'] ) ) {
			update_post_meta( $post_id, 'course_id', @$_POST[ $_POST['post_type'].'_course'] );
		}
	}

	if ( 'sfwd-topic' == $_POST['post_type'] || 'sfwd-quiz' == $_POST['post_type'] ) {
		if ( isset( $_POST[ $_POST['post_type'].'_lesson'] ) ) {
			update_post_meta( $post_id, 'lesson_id', @$_POST[ $_POST['post_type'].'_lesson'] );
		}
	}

	if ( 'sfwd-lessons' == $_POST['post_type'] || 'sfwd-topic' == $_POST['post_type'] ) {
		global $wpdb;

		if ( isset( $_POST[ $_POST['post_type'].'_course'] ) ) {
			$course_id = get_post_meta( $post_id, 'course_id', true );
		}

		if ( ! empty( $course_id ) ) {
			$posts_with_lesson = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lesson_id' AND meta_value = '%d'", $post_id ) );

			if ( ! empty( $posts_with_lesson) && ! empty( $posts_with_lesson[0] ) ) {
				foreach ( $posts_with_lesson as $post_with_lesson ) {
					$post_course_id = learndash_get_setting( $post_with_lesson, 'course' );

					if ( $post_course_id != $course_id ) {
						learndash_update_setting( $post_with_lesson, 'course', $course_id );

						$quizzes_under_lesson_topic = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lesson_id' AND meta_value = '%d'", $posts_with_lesson ) );
						if ( ! empty( $quizzes_under_lesson_topic) && ! empty( $quizzes_under_lesson_topic[0] ) ) {
							foreach ( $quizzes_under_lesson_topic as $quiz_post_id ) {
								$quiz_course_id = learndash_get_setting( $quiz_post_id, 'course' );
								if ( $course_id != $quiz_course_id ) {
									learndash_update_setting( $quiz_course_id, 'course', $course_id );
								}
							}
						}
					}
				}
			}

		}

	}
}

add_action( 'save_post', 'learndash_patent_course_and_lesson_id_save' );


/**
 * Output certificate shortcodes content on admin tab
 *
 * @since 2.1.0
 */
function learndash_certificate_shortcodes_page() {
	?>
		<div  id="certificate-shortcodes"  class="wrap">
			<h2><?php _e( 'Certificate Shortcodes', 'learndash' ); ?></h2>
			<div class='sfwd_options_wrapper sfwd_settings_left'>
				<div class='postbox ' id='sfwd-certificates_metabox'>
					<div class="inside">
					<?php
					echo __('<b>Shortcode Options</b><p>You may use shortcodes to customize the display of your certificates. Provided is a built-in shortcode for displaying user information.</p><p><b>[usermeta]</b><p>This shortcode takes a parameter named field, which is the name of the user meta data field to be displayed.</p><p>Example: <b>[usermeta field="display_name"]</b> would display the user\'s Display Name.</p><p>See <a href="http://codex.wordpress.org/Function_Reference/get_userdata#Notes">the full list of available fields here</a>.</p>', 'learndash' ).
							'<p><b>[quizinfo]</b></p><p>' . sprintf( _x( 'This shortcode displays information regarding %s attempts on the certificate. This short code can use the following parameters:', 'placeholders: quiz', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ) . '</p>

							<ul>
							<li><b>SHOW</b>: ' . sprintf( _x( 'This parameter determines the information to be shown by the shortcode. Possible values are:
								<ol class="cert_shortcode_parm_list">
									<li>score</li>
									<li>count</li>
									<li>pass</li>
									<li>timestamp</li>
									<li>points</li>
									<li>total_points</li>
									<li>percentage</li>
									<li>quiz_title</li>
									<li>course_title</li>
									<li>timespent</li>
								</ol>
								<br>Example: <b>[quizinfo show="percentage"]</b> shows the percentage score of the user in the %s.', 'placeholder: quiz', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) )  . '<br><br></li>
							<li><b>FORMAT</b>: ' . __( 'This can be used to change the timestamp format. Default: "F j, Y, g:i a" shows as <i>March 10, 2001, 5:16 pm</i>. <br>Example: <b>[quizinfo show="timestamp" format="Y-m-d H:i:s"]</b> will show as <i>2001-03-10 17:16:18</i>', 'learndash' ) . '</li>
							</ul>
							<p>' . __( 'See <a target="_blank" href="http://php.net/manual/en/function.date.php">the full list of available date formating strings  here.</a>', 	'learndash' ) . '</p>
							<p><b>[courseinfo]</b></p><p>'. __( 'This shortcode displays course related information on the certificate. This short code can use the following parameters:', 'learndash' ) . '</p>
								<ul>
									<li><b>SHOW</b>: ' . sprintf( _x( 'This parameter determines the information to be shown by the shortcode. Possible values are:
										<ol class="cert_shortcode_parm_list">
											<li>course_title</li>
											<li>completed_on</li>
											<li>cumulative_score</li>
											<li>cumulative_points</li>
											<li>cumulative_total_points</li>
											<li>cumulative_percentage</li>
											<li>cumulative_timespent</li>
											<li>aggregate_percentage</li>
											<li>aggregate_score</li>
											<li>aggregate_points</li>
											<li>aggregate_total_points</li>
											<li>aggregate_timespent</li>
										</ol>
										<i>cumulative</i> is average for all %s of the %s.<br>
										<i>aggregate</i> is sum for all %s of the %s.<br>
									<br>Example: <b>[courseinfo show="cumulative_score"]</b> shows average points scored across all quizzes on the course.', 'placeholders: quizzes, course, quizzes, course', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'quizzes' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_lower( 'quizzes' ), LearnDash_Custom_Label::label_to_lower( 'course' )) . '<br><br></li>
									<li><b>FORMAT</b>: ' . __( 'This can be used to change the date format. Default: "F j, Y, g:i a" shows as <i>March 10, 2001, 5:16 pm</i>. <br>Example: <b>[courseinfo show="completed_on" format="Y-m-d H:i:s"]</b> will show as <i>2001-03-10 17:16:18</i>', 'learndash' ) . '</li>
								</ul>
							<p>' . __( 'See <a target="_blank" href="http://php.net/manual/en/function.date.php">the full list of available date formating strings  here.</a>',      'learndash' ) . '</p>';
					?>
					</div>
				</div>
			</div>
		</div>
	<?php
}



/**
 * Output course shortcodes content on admin tab
 *
 * @since 2.1.0
 */
function learndash_course_shortcodes_page() {
	?>
	<div  id='course-shortcodes'  class='wrap'>
		<h1><?php printf( _x( '%s Shortcodes', 'Course Shortcodes Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h1>
		<div class='sfwd_options_wrapper sfwd_settings_left'>
			<div class='postbox ' id='sfwd-course_metabox'>
				<div class='inside'>
				<?php
				echo '<b>' . __( 'Shortcode Options', 'learndash' ) . '</b>
					<p>' . sprintf( _x( 'You may use shortcodes to add information to any page/%s/%s/%s. Here are built-in shortcodes for displaying relavent user information.', 'placeholders: course, lesson, quiz', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_lower( 'lesson' ), LearnDash_Custom_Label::label_to_lower( 'quiz' )) . '</p>
					<p><b>[ld_profile]</b></p><p>' . sprintf( _x( 'Displays user\'s enrolled %s, %s progress, %s scores, and achieved certificates. This short code can take following parameters:', 'placeholder: courses, course, quiz', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ) . '</p>
					<ul>
						<li><b>order</b>: ' . sprintf( _x( 'sets order of %s. Default value DESC. Possible values: <b>DESC</b>, <b>ASC</b>. Example: <b>[ld_profile order="ASC"]</b> shows %s in ascending order.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
						<li><b>orderby</b>: ' . sprintf( _x( 'sets what the list of ordered by. Default value ID. Possible values: <b>ID</b>, <b>title</b>. Example: <b>[ld_profile orderby="ID" order="ASC"]</b> shows %s in ascending order by title.', 'placeholders: courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					</ul>
					
					<br>
					<p><b>[ld_course_list]</b></p><p>' . sprintf( _x( 'This shortcode shows list of %s. You can use this short code on any page if you dont want to use the default /%s page. This short code can take following parameters:', 'placeholders: courses, courses (URL slug)', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_slug( 'courses' ) ) . '</p>
					<ul>
					<li><b>num</b>: ' . sprintf( _x( 'limits the number of %s displayed. Example: <b>[ld_course_list num="10"]</b> shows 10 %s.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>order</b>: ' . sprintf( _x( 'sets order of %s. Possible values: <b>DESC</b>, <b>ASC</b>. Example: <b>[ld_course_list order="ASC"]</b> shows %s in ascending order.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>orderby</b>: ' . sprintf( _x( 'sets what the list of ordered by. Example: <b>[ld_course_list order="ASC" orderby="title"]</b> shows %s in ascending order by title.', 'placeholders: courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>tag</b>: ' . sprintf( _x( 'shows %s with mentioned tag. Example: <b>[ld_course_list tag="math"]</b> shows %s having tag math.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>tag_id</b>: ' . sprintf( _x( 'shows %s with mentioned tag_id. Example: <b>[ld_course_list tag_id="30"]</b> shows %s having tag with tag_id 30.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>cat</b>: ' . sprintf( _x( 'shows %s with mentioned category id. Example: <b>[ld_course_list cat="10"]</b> shows %s having category with category id 10.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>category_name</b>: ' . sprintf( _x( 'shows %s with mentioned category slug. Example: <b>[ld_course_list category_name="math"]</b> shows %s having category slug math.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>mycourses</b>: ' . sprintf( _x( 'show current user\'s %s. Example: <b>[ld_course_list mycourses="true"]</b> shows %s the current user has access to.', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ) . '</li>
					<li><b>categoryselector</b>: ' . __( 'shows a category dropdown. Example: <b>[ld_course_list categoryselector="true"]</b>.', 'learndash' ) . '</li>
					<li><b>col</b>: ' . __( 'number of columns to show when using course grid addon. Example: <b>[ld_course_list col="2"]</b> shows 2 columns.', 'learndash' ) . '</li>
					</ul>
					<p>' . __( 'See <a target="_blank" href="https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">the full list of available orderby options here.</a>', 'learndash' ) . '</p>
					<br>
					<p><b>[ld_lesson_list]</b></p><p>' . sprintf( _x( 'This shortcode shows list of %s. You can use this short code on any page. This short code can take following parameters: num, order, orderby, tag, tag_id, cat, category_name. See [ld_course_list] above details on using the shortcode parameters.', 'placeholders: lessons', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'lessons' ) ) . '</p>
					<br>
					<p><b>[ld_quiz_list]</b></p><p>' . sprintf( _x( 'This shortcode shows list of %s. You can use this short code on any page. This short code can take following parameters: num, order, orderby, tag, tag_id, cat, category_name.. See [ld_course_list] above details on using the shortcode parameters.', 'placeholders: quizzes', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'quizzes' ) ) . '</p>
					<br>
					<p><b>[learndash_course_progress]</b></p><p>' . sprintf( _x( 'This shortcode displays users progress bar for the %s in any %s/%s/%s pages.', 'placeholders: course, course, lesson, quiz', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_lower( 'lesson' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ) . '</p>
					<br>
					<p><b>[visitor]</b></p><p>' . sprintf( _x( 'This shortcode shows the content if the user is not enrolled in the %s. Example usage: <strong>[visitor]</strong>Welcome Visitor!<strong>[/visitor]</strong>', 'placeholders: course', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ) . '</p>
					<br>
                    <p><b>[student]</b></p><p>' . sprintf( _x( 'This shortcode shows the content if the user is enrolled in the %s. Example usage: <strong>[student]</strong>Welcome Student!<strong>[/student]</strong> This shortcode can take following parameters:', 'placeholders: course', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ) . '</p>
					<ul>
					<li><b>course_id</b>: ' . sprintf( _x( 'Optional. Show content if the student has access to a specific course. Example: [student course_id="10"]insert any content[/student]</b>', 'placeholders: courses, courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ) . '</li>
					</ul>
					<br>
					<p><b>[course_complete]</b></p><p>' . sprintf( _x( 'This shortcode shows the content if the user has completed the %s. Example usage: <strong>[course_complete]</strong> You have completed this %s. <strong>[/course_complete]</strong>', 'placeholders: course, course', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ) . '</p>
                    <br>
					<p><b>[user_groups]</b></p><p>' . __( 'This shortcode displays the list of groups users are assigned to as users or leaders.', 'learndash' ) . '</p>
					<br>

                    <p><b>[ld_group]</b></p><p>' . __( 'This shortcode shows the content if the user is enrolled in a specific group. Example usage: <strong>[ld_group]</strong>Welcome to the Group!<strong>[/ld_group]</strong> This shortcode takes the following parameters:', 'learndash'  ) . '</p>
					<ul>
					<li><b>group_id</b>: ' . __( 'Required. Show content if the student has access to a specific group. Example: <b>[ld_group group_id="16"]insert any content[/ld_group]</b>', 'learndash' ) . '</li>
					</ul>

					<br>
					<p><b>[learndash_payment_buttons]</b></p><p>' . sprintf( _x( 'This shortcode displays can show the payment buttons on any page. Example: <strong>[learndash_payment_buttons course_id="123"]</strong> shows the payment buttons for %s with %s ID: 123', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::get_label( 'courses' ) ) . '</p>
					<br>
					<p><b>[course_content]</b></p><p>' . sprintf( __( 'This shortcode displays the %s Content table (%s, %s, and %s) when inserted on a page or post. Example: <strong>[course_content course_id="123"]</strong> shows the %s content for %s with %s ID: 123', 'placeholders: Course, lesson, topics, quizzes, course, course, Course', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ), LearnDash_Custom_Label::label_to_lower( 'lessons' ), LearnDash_Custom_Label::label_to_lower( 'topics' ), LearnDash_Custom_Label::label_to_lower( 'quizzes' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::get_label( 'course' )) . '</p>
					
					<br>

					<p><b>[ld_course_expire_status]</b></p><p>' . sprintf( _x( 'This shortcode displays the user %s access expire date. Example: <strong>[ld_course_expire_status course_id="111" user="222" label_before="%s access will expire on:" label_after="%s access expired on:" format="F j, Y g:i a"]</strong>.', 'placeholders: course, Course, Course', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::get_label( 'course' ), LearnDash_Custom_Label::get_label( 'course' )) . '</p>					
					<ul>
					<li><b>course_id</b>: ' . sprintf( _x( 'The ID of the %s to check. If not provided will attempt to user current post. Example: <b>[ld_course_expire_status course_id="111"]</b> ', 'plaeholders: course', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) . '</li>
					<li><b>user_id</b>: ' . __( 'The ID of the user to check. If not provided the current logged in user ID will be used. Example: <b>[ld_course_expire_status user_id="222"]</b>', 'learndash' ) . '</li>
					<li><b>label_before</b>: ' . sprintf( _x( 'The label prefix shown before the access expires. Default label is "%s access will expire on:" Example: <b>[ld_course_expire_status label_before="Your access to this %s will expire on:"]</b>', 'placeholders: Course, course', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ) . '</li>
					<li><b>label_after</b>: ' . sprintf( _x( 'The label prefix shown after access has expired. Default label is "%s access expired on:" Example: <b>[ld_course_expire_status label_after="Your access to this %s expired on:"]</b>', 'placeholders: Course, course', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ) . '</li>
					<li><b>format</b>: ' . __( 'The controls the format of the date/time value shown to the user. If not provided the date/time format from your WordPress sytem will be used. Example: <b>[ld_course_expire_status format="F j, Y g:i a"]</b>', 'learndash' ) . '</li>
					</ul>

					';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}



/**
 * Add action links to quizzes post listing on post hover
 * Questions, Statistics, Leaderboard
 *
 * @since 2.1.0
 *
 * @param array   $actions An array of row action links
 * @param WP_Post $post    The post object.
 * @return array  $actions An array of row action links
 */
function learndash_quizzes_inline_actions( $actions, $post ) {
	if ( $post->post_type == 'sfwd-quiz' ) {
		$pro_quiz_id = learndash_get_setting( $post, 'quiz_pro', true );

		if ( empty( $pro_quiz_id ) ) {
			return $actions;
		}

		$statistics_link = admin_url( 'admin.php?page=ldAdvQuiz&module=statistics&id='.$pro_quiz_id.'&post_id='.$post->ID );
		$questions_link = admin_url( 'admin.php?page=ldAdvQuiz&module=question&quiz_id='.$pro_quiz_id.'&post_id='.$post->ID );
		$leaderboard_link = admin_url( 'admin.php?page=ldAdvQuiz&module=toplist&id='.$pro_quiz_id.'&post_id='.$post->ID );

		$actions['questions'] = "<a href='".$questions_link."'>".__( 'Questions', 'learndash' ).'</a>';
		$actions['statistics'] = "<a href='".$statistics_link."'>".__( 'Statistics', 'learndash' ).'</a>';
		$actions['leaderboard'] = "<a href='".$leaderboard_link."'>".__( 'Leaderboard', 'learndash' ).'</a>';
	}

	return $actions;
}

add_filter( 'post_row_actions', 'learndash_quizzes_inline_actions', 10, 2 );



function learndash_element_lazy_loader() {
		
	$reply_data = array();

	if ((isset($_POST['query_data'])) && (!empty($_POST['query_data']))) {

		if ( ( isset( $_POST['query_data']['query_vars'] ) ) && ( !empty( $_POST['query_data']['query_vars'] ) ) ) {
			$reply_data['query_data'] = $_POST['query_data'];
			
			if ( isset( $_POST['query_data']['query_type'] ) ) {
				switch( $_POST['query_data']['query_type'] ) {
					case 'WP_Query':
						$query = new WP_Query( $_POST['query_data']['query_vars'] );
						if ( $query instanceof WP_Query ) {
							if ( ! empty( $query->posts ) ) {
								$reply_data['html_options'] = '';
								foreach ( $query->posts as $p ) {
									if ( intval($p->ID) == intval($_POST['query_data']['value'])) 
										$selected = ' selected="selected" ';
									else
										$selected = '';
									$reply_data['html_options'] .= '<option '. $selected .' value="'. $p->ID .'">'. $p->post_title .'</option>';
								}
							}
						} 
						break;

					case 'WP_User_Query':
						$query = new WP_User_Query( $_POST['query_data']['query_vars'] );
						break;

					default:
						break;
				}
			}
		}
	}
	
	echo json_encode($reply_data);
	
	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_learndash_element_lazy_loader', 'learndash_element_lazy_loader' );


add_filter('views_edit-sfwd-essays', 'learndash_edit_list_table_views', 10, 1 );
add_filter('views_edit-sfwd-assignment', 'learndash_edit_list_table_views', 10, 1 );
function learndash_edit_list_table_views( $views = array() ) {
	if ( ! learndash_is_admin_user() ) { 
		$views = array();
	}

	return $views;
}