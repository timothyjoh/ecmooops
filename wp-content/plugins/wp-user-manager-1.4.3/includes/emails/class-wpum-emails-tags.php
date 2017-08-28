<?php
/**
 * WPUM API for creating Email template tags
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {name}
 * {sitename}
 *
 * To replace tags in content, use: wpum_do_email_tags( $content, user_id );
 *
 * To add tags, use: wpum_add_email_tag( $tag, $description, $func ). Be sure to wrap wpum_add_email_tag()
 * in a function hooked to the 'wpum_email_tags' action
 *
 * @package     wp-user-manager
 * @author   		Copyright (c) 2014, Pippin Williamson
 * @author      Barry Kooij
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WPUM_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since 1.0.0
	 */
	private $tags;

	/**
	 * User ID
	 *
	 * @since 1.0.0
	 */
	private $user_id;

	/**
	 * Object that can contain a private value.
	 *
	 * Upon registration, this holds the $plaintext_pass (as per WP Core wp_new_user_notification() function)
	 * Upon password recover, this holds the secret key as per retrieve_password() in core wp-login.php
	 *
	 * @since 1.0.0
	 */
	private $private_key;

	/**
	 * Add an email tag
	 *
	 * @since 1.0.0
	 *
	 * @param string   $tag  Email tag to be replace in email
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}

	/**
	 * Remove an email tag
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all email tags
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content Content to search for email tags
	 * @param int $user_id The user id
	 * @param string $private_key the password
	 *
	 * @since 1.0.0
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $user_id, $private_key ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->user_id = $user_id;
		$this->private_key = $private_key;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->user_id = null;
		$this->private_key = null;

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use wpum_do_email_tags instead.
	 *
	 * @since 1.0.0
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->user_id, $this->private_key, $tag );
	}

}

/**
 * Add an email tag
 *
 * @since 1.0.0
 *
 * @param string   $tag  Email tag to be replace in email
 * @param callable $func Hook to run when email tag is found
 */
function wpum_add_email_tag( $tag, $description, $func ) {
	WPUM()->email_tags->add( $tag, $description, $func );
}

/**
 * Remove an email tag
 *
 * @since 1.0.0
 *
 * @param string $tag Email tag to remove hook from
 */
function wpum_remove_email_tag( $tag ) {
	WPUM()->email_tags->remove( $tag );
}

/**
 * Check if $tag is a registered email tag
 *
 * @since 1.0.0
 *
 * @param string $tag Email tag that will be searched
 *
 * @return bool
 */
function wpum_email_tag_exists( $tag ) {
	return WPUM()->email_tags->email_tag_exists( $tag );
}

/**
 * Get all email tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function wpum_get_email_tags() {
	return WPUM()->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since 1.0.0
 *
 * @return string
 */
function wpum_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = wpum_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

		}

	}

	// Return the list
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 * @param int $user_id The user id
 * @param string $private_key the password
 *
 * @since 1.0.0
 *
 * @return string Content with email tags filtered out.
 */
function wpum_do_email_tags( $content, $user_id, $private_key ) {

	// Replace all tags
	$content = WPUM()->email_tags->do_tags( $content, $user_id, $private_key );

	// Return content
	return $content;
}

/**
 * Load email tags
 *
 * @since 1.0.0
 */
function wpum_load_email_tags() {
	do_action( 'wpum_add_email_tags' );
}
add_action( 'init', 'wpum_load_email_tags', -999 );

/**
 * Add default WPUM email template tags
 *
 * @since 1.0.0
 */
function wpum_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'wpum' ),
			'function'    => 'wpum_email_tag_sitename'
		),
		array(
			'tag'         => 'username',
			'description' => __( 'Displays the username.', 'wpum' ),
			'function'    => 'wpum_email_tag_username'
		),
		array(
			'tag'         => 'password',
			'description' => __( 'Displays the user password. If the "custom passwords" option is enabled, the password will not be displayed into the email.', 'wpum' ),
			'function'    => 'wpum_email_tag_password'
		),
		array(
			'tag'         => 'recovery_url',
			'description' => __( 'Displays the password recovery url needed for the user to reset his password.', 'wpum' ),
			'function'    => 'wpum_email_tag_recovery_url'
		),
	);

	// Apply wpum_email_tags filter
	$email_tags = apply_filters( 'wpum_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		wpum_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

}
add_action( 'wpum_add_email_tags', 'wpum_setup_email_tags' );

/**
 * Email template tag: sitename
 * Your site name
 *
 * @param int $user_id
 * @return string sitename
 */
function wpum_email_tag_sitename( $user_id ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * Email template tag: password
 *
 * The function checks whether custom passwords are enabled.
 * If enabled, the mail won't contain the password.
 *
 * Only automatically generated passwords (as per WP default)
 * will be displayed into the email.
 *
 * @param int $user_id
 * @param int $private_key
 * @return string sitename
 */
function wpum_email_tag_password( $user_id, $private_key ) {

	$pwd = $private_key;

	if( wpum_get_option('custom_passwords') )
		$pwd = __('the password you chose upon registration.', 'wpum');

	return $pwd;
}

/**
 * Email template tag: username
 *
 * @param int $user_id
 * @return string username
 */
function wpum_email_tag_username( $user_id ) {

	$username = get_userdata( $user_id );
	$username = esc_attr($username->user_login);

	return $username;
}

/**
 * Email template tag: recovery_url
 *
 * @param int $user_id
 * @param int $private_key
 * @return string url
 */
function wpum_email_tag_recovery_url( $user_id, $private_key ) {

	$username = get_userdata( $user_id );
	$username = esc_attr($username->user_login);

	$url = add_query_arg( array( 'password-reset' => true, 'key' => $private_key, 'login' => $username ), get_permalink( wpum_get_option('password_recovery_page') ) );

	return '<a href="'. esc_url( $url ) .'">'. esc_url( $url ) .'</a>';
}
