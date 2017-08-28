<?php
/**
 * Handles Emails Functions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets list of registered emails.
 *
 * @since 1.0.0
 * @return array $emails - list of emails.
 */
function wpum_get_emails() {
	return apply_filters( 'wpum/get_emails', array() );
}

/**
 * Run this function to reset/install registered emails.
 * This function should be used of plugin installation
 * or on addons installation if the addon adds new emails.
 *
 * @since 1.0.0
 * @return void.
 */
function wpum_register_emails() {

	$emails = wpum_get_emails();
	$default_emails = array();

	foreach ( $emails as $id => $settings ) {
		$default_emails[ $id ] = array(
			'subject' => $settings['subject'],
			'message' => $settings['message'],
		);
	}

	update_option( 'wpum_emails', $default_emails );

}

/**
 * Email login credentials to a newly registered user.
 * A new user registration notification is also sent to admin email if enabled.
 *
 * @since 1.1.0
 * @param  string $user_id        user id number of the newly registered user.
 * @param  string $plaintext_pass password of the newly registered user.
 * @return void
 */
function wpum_new_user_notification( $user_id, $plaintext_pass ) {

	$user = get_userdata( $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	// Send notification to admin if not disabled.
	if ( !wpum_get_option( 'disable_admin_register_email' ) ) {
		$message  = sprintf( esc_html__( 'New user registration on your site %s:', 'wpum' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( esc_html__( 'Username: %s', 'wpum' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( esc_html__( 'E-mail: %s', 'wpum' ), $user->user_email ) . "\r\n";
		wp_mail( get_option( 'admin_email' ), sprintf( esc_html__( '[%s] New User Registration', 'wpum' ), $blogname ), $message );
	}

	// Send notification to the user now.
	if ( empty( $plaintext_pass ) )
		return;

	// Check if email exists first.
	if ( wpum_email_exists( 'register' ) ) {

		// Retrieve the email from the database
		$register_email = wpum_get_email( 'register' );
		$message = wpautop( $register_email['message'] );
		$message = wpum_do_email_tags( $message, $user_id, $plaintext_pass );

		WPUM()->emails->__set( 'heading', esc_html__( 'Your account', 'wpum' ) );
		WPUM()->emails->send( $user->user_email, $register_email['subject'], $message );

	}

}

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * @since 1.0.0
 * @return array $templates All the registered email templates
 */
function wpum_get_email_templates() {
	$templates = new WPUM_Emails;
	return $templates->get_templates();
}

/**
 * Checks whether a given email id exists into the database.
 *
 * @since 1.0.0
 * @return bool
 */
function wpum_email_exists( $email_id ) {

	$exists = false;
	$emails = get_option( 'wpum_emails', array() );

	if ( array_key_exists( $email_id, $emails ) )
		$exists = true;

	return $exists;
}

/**
 * Get an email from the database.
 *
 * @since 1.0.0
 * @return array email details containing subject and message
 */
function wpum_get_email( $email_id ) {

	$emails = get_option( 'wpum_emails', array() );

	return $emails[ $email_id ];

}
