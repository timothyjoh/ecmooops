<?php
/**
 * Load custom scripts and css files into the fields editor.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Loads the plugin admin assets files.
 *
 * @return void
 * @since 1.0.0
 */
function wpumcf_admin_cssjs() {

	$js_dir  = WPUMCF_PLUGIN_URL . 'assets/js/';
	$css_dir = WPUMCF_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Styles & scripts.
	wp_register_style( 'wpumcf-admin', $css_dir . 'wpum_custom_fields' . $suffix . '.css', WPUMCF_VERSION );
	wp_register_style( 'wpumcf-selectize-css', WPUMCF_PLUGIN_URL . 'assets/selectize/' . 'selectize.css', WPUMCF_VERSION );
	wp_register_script( 'wpumcf-admin-js', $js_dir . 'wpum_custom_fields' . $suffix . '.js', 'jQuery', WPUMCF_VERSION, true );
	wp_register_script( 'wpumcf-repeater-js', $js_dir . 'jquery.repeater.min.js', 'jQuery', WPUMCF_VERSION, true );
	wp_register_script( 'wpumcf-selectize-js', WPUMCF_PLUGIN_URL . 'assets/selectize/' . 'selectize.min.js', 'jQuery', WPUMCF_VERSION, true );

	$screen = get_current_screen();

	if ( $screen->base == 'users_page_wpum-profile-fields' || $screen->base == 'users_page_wpum-edit-field' ) {

		add_thickbox();
		wp_enqueue_style( 'wpumcf-admin' );
		wp_enqueue_style( 'wpumcf-selectize-css' );
		wp_enqueue_script( 'wpumcf-repeater-js' );
		wp_enqueue_script( 'wpumcf-admin-js' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'wpumcf-selectize-js' );

	}

}
add_action( 'admin_enqueue_scripts', 'wpumcf_admin_cssjs' );
