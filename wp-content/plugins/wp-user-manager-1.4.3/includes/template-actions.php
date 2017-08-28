<?php
/**
 * Plugin Template Actions
 * This file holds all the template actions
 * that have effects on the templating system of the plugin.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Action to display helper links.
 *
 * @since 1.0.0
 * @access public
 * @param string  $login    yes/no
 * @param string  $register yes/no
 * @param string  $password yes/no
 * @return void
 */
function wpum_add_links_to_forms( $login, $register, $password ) {

	get_wpum_template( 'helper-links.php',
		array(
			'login'    => esc_attr( $login ),
			'register' => esc_attr( $register ),
			'password' => esc_attr( $password )
		)
	);

}
add_action( 'wpum_do_helper_links', 'wpum_add_links_to_forms', 10, 3 );

/**
 * Add helper links to the password form.
 *
 * @since 1.0.0
 * @access public
 * @param array   $atts Settings of the shortcode.
 * @return void
 */
function wpum_add_helper_links( $atts ) {

	$defaults = array(
		'login_link'    => true,
		'psw_link'      => true,
		'register_link' => true,
	);

	$atts = wp_parse_args( $atts, $defaults );

	$login_link    = $atts['login_link'];
	$psw_link      = $atts['psw_link'];
	$register_link = $atts['register_link'];

	// Display helper links
	do_action( 'wpum_do_helper_links', $login_link, $register_link, $psw_link );

}
add_action( 'wpum_after_password_form_template', 'wpum_add_helper_links', 10, 1 );
add_action( 'wpum_after_register_form_template', 'wpum_add_helper_links', 10, 1 );

/**
 * Adds tabs navigation on top of the account edit page.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_add_account_tabs( $current_tab, $all_tabs, $form, $fields, $user_id, $atts ) {

	get_wpum_template( "account-tabs.php", array( 'tabs'  => wpum_get_account_page_tabs(), 'current_tab' => $current_tab, 'all_tabs' => $all_tabs ) );

}
add_action( 'wpum_before_account', 'wpum_add_account_tabs', 10, 6 );

/**
 * Display content of the first tab into the account page.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_show_account_edit_form( $current_tab, $all_tabs, $form, $fields, $user_id, $atts ) {

	get_wpum_template( 'forms/account-form.php',
		array(
			'atts'    => $atts,
			'form'    => $form,
			'fields'  => $fields,
			'user_id' => $user_id
		)
	);

}
add_action( 'wpum_account_tab_details', 'wpum_show_account_edit_form', 10, 6 );

/**
 * Display content of the first tab into the account page.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_show_psw_update_form( $current_tab, $all_tabs, $form, $fields, $user_id, $atts ) {

	echo WPUM()->forms->get_form( 'update-password' );

}
add_action( 'wpum_account_tab_change-password', 'wpum_show_psw_update_form', 10, 6 );

/**
 * Display content of the first tab into the account page.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_show_failed_login_message() {

	if( isset( $_GET['login'] ) && $_GET['login'] == 'failed' ) {
		$args = array(
				'id'   => 'wpum-login-failed',
				'type' => 'error',
				'text' => __( 'Login failed: You have entered incorrect login details, please try again.', 'wpum' )
		);
		$warning = wpum_message( $args, true );
	}

}
add_action( 'wpum_before_login_form', 'wpum_show_failed_login_message' );

/**
 * Display update messages on the account page.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_profile_update_messages() {

	if ( isset( $_GET['updated'] ) && $_GET['updated'] == 'success' ) :
		$args = array(
			'id'   => 'wpum-profile-updated',
			'type' => 'success',
			'text' => apply_filters( 'wpum_account_update_success_message', __( 'Profile successfully updated.', 'wpum' ) )
		);
		wpum_message( $args );
	endif;
	if ( isset( $_GET['updated'] ) && $_GET['updated'] == 'error' ) :
		$args = array(
			'id'   => 'wpum-profile-error',
			'type' => 'error',
			'text' => apply_filters( 'wpum_account_update_error_message', __( 'Something went wrong.', 'wpum' ) )
		);
		wpum_message( $args );
	endif;
}
add_action( 'wpum_before_account_form', 'wpum_profile_update_messages' );
