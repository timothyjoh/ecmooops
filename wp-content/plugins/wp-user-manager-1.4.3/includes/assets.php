<?php
/**
 * Handles loading of css and js files.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Loads the plugin admin assets files
 *
 * @since 1.0.0
 * @return void
 */
function wpum_admin_cssjs() {

	$js_dir  = WPUM_PLUGIN_URL . 'assets/js/';
	$css_dir = WPUM_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Styles & scripts
	wp_register_style( 'wpum-admin', $css_dir . 'wp_user_manager' . $suffix . '.css', WPUM_VERSION );
	wp_register_style( 'wpum-admin-general', WPUM_PLUGIN_URL . 'assets/css/wp_user_manager_admin_general.css', WPUM_VERSION );
	wp_register_style( 'wpum-select2', WPUM_PLUGIN_URL . 'assets/select2/css/select2.css', WPUM_VERSION );
	wp_register_script( 'wpum-select2', WPUM_PLUGIN_URL . 'assets/select2/js/select2.min.js', 'jQuery', WPUM_VERSION, true );
	wp_register_script( 'wpum-serializeJSON', WPUM_PLUGIN_URL . 'assets/js/vendor/jquery.serializeJSON.js', 'jQuery', WPUM_VERSION, true );
	wp_register_script( 'wpum-admin-js', $js_dir . 'wp_user_manager_admin' . $suffix . '.js', 'jQuery', WPUM_VERSION, true );

	// Enquery styles and scripts anywhere needed
	wp_enqueue_style( 'wpum-admin-general' );

	// Enqueue styles & scripts on admin page only
	$screen = get_current_screen();

	wp_enqueue_script( 'wpum-admin-js' );

	// Load styles only on required pages.
	if ( $screen->base == 'users_page_wpum-settings' || $screen->id == 'wpum_directory' || $screen->base == 'users_page_wpum-edit-field' || $screen->base == 'users_page_wpum-profile-fields' ):

		wp_enqueue_script( 'wpum-select2' );
		wp_enqueue_style( 'wpum-admin' );
		wp_enqueue_style( 'wpum-select2' );
		wp_enqueue_script( 'accordion' );
		wp_enqueue_media();

		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'default_fields' && $screen->base == 'users_page_wpum-settings' )
			wp_enqueue_script( 'jquery-ui-sortable' );

		if ( $screen->base == 'users_page_wpum-custom-fields-editor' )
			wp_enqueue_script( 'wpum-serializeJSON' );

	endif;

	// Load select 2 within menu editor too.
	if( $screen->base == 'nav-menus' ) {
		wp_enqueue_script( 'wpum-select2' );
		wp_enqueue_style( 'wpum-select2' );
	}

	// Backend JS Settings
	wp_localize_script( 'wpum-admin-js', 'wpum_admin_js', array(
		'ajax'          => admin_url( 'admin-ajax.php' ),
		'confirm'       => __( 'Are you sure you want to do this? This action cannot be reversed.', 'wpum' ),
		'use_this_file' => __( 'Use This File', 'wpum' ),
		'upload_title'  => __( 'Upload or select a file', 'wpum' ),
	) );

}
add_action( 'admin_enqueue_scripts', 'wpum_admin_cssjs' );


/**
 * Loads the plugin frontend assets files
 *
 * @since 1.0.0
 * @return void
 */
function wpum_frontend_cssjs() {

	$js_dir  = WPUM_PLUGIN_URL . 'assets/js/';
	$css_dir = WPUM_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Default URL
	$url = $css_dir . 'wp_user_manager_frontend' . $suffix . '.css';

	$file          = 'wp_user_manager_frontend' . $suffix . '.css';
	$templates_dir = 'wpum/';
	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'wp_user_manager_frontend.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'wp_user_manager_frontend.css';
	$wpum_plugin_style_sheet     = trailingslashit( wpum_get_templates_dir()    ) . $file;

	// Look in the child theme directory first, followed by the parent theme, followed by the WPUM core templates directory
	// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	// This allows users to copy just wp_user_manager_frontend.css to their theme
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'wp_user_manager_frontend.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'wp_user_manager_frontend.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $wpum_plugin_style_sheet ) || file_exists( $wpum_plugin_style_sheet ) ) {
		$url = trailingslashit( wpum_get_templates_url() ) . $file;
	}

	// Styles & scripts registration
	wp_register_script( 'wpum-frontend-js', $js_dir . 'wp_user_manager' . $suffix . '.js', array( 'jquery' ), WPUM_VERSION, true );
	wp_register_style( 'wpum-frontend-css', $url , WPUM_VERSION );

	// Enqueue everything
	wp_enqueue_script( 'jQuery' );
	wp_enqueue_script( 'wpum-frontend-js' );

	// Allows developers to disable the frontend css in case own file is needed.
	if ( ! defined( 'WPUM_DISABLE_CSS' ) )
		wp_enqueue_style( 'wpum-frontend-css' );

	// Load psw cloacking script.
	if( wpum_is_psw_cloacking_enabled() ) {
		wp_register_script( 'wpum-hideShowPassword', $js_dir . '/vendor/' . 'hideShowPassword' . $suffix . '.js', array( 'jquery' ), WPUM_VERSION, true );
		wp_enqueue_script( 'wpum-hideShowPassword' );
	}

	// Frontend jS Settings
	wp_localize_script( 'wpum-frontend-js', 'wpum_frontend_js', array(
		'ajax'                 => admin_url( 'admin-ajax.php' ),
		'checking_credentials' => __( 'Checking credentials...', 'wpum' ),
		'pwd_meter'            => false,
		'disable_ajax'         => wpum_get_option( 'disable_ajax' )
	) );

}
add_action( 'wp_enqueue_scripts', 'wpum_frontend_cssjs' );
