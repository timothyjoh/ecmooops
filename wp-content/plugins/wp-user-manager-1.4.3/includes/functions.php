<?php
/**
 * Main Functions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wpum_get_login_methods' ) ) :
/**
 * Define login methods for options panel
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_get_login_methods() {
	return apply_filters( 'wpum_get_login_methods', array(
			'username'       => __( 'Username only', 'wpum' ),
			'email'          => __( 'Email only', 'wpum' ),
			'username_email' => __( 'Username or Email', 'wpum' ),
		) );
}
endif;

if ( ! function_exists( 'wpum_get_psw_lengths' ) ) :
/**
 * Define login methods for options panel
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_get_psw_lengths() {
	return apply_filters( 'wpum_get_psw_lengths', array(
			''       => __( 'Disabled', 'wpum' ),
			'weak'   => __( 'Weak', 'wpum' ),
			'medium' => __( 'Medium', 'wpum' ),
			'strong' => __( 'Strong', 'wpum' ),
		) );
}
endif;

if ( ! function_exists( 'wpum_logout_url' ) ) :
/**
 * A simple wrapper function for the wp_logout_url function
 *
 * The function checks whether a custom url has been passed,
 * if not, looks for the settings panel option,
 * defaults to wp_logout_url
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function wpum_logout_url( $custom_redirect = null ) {

	$redirect = null;

	if ( !empty( $custom_redirect ) ) {
		$redirect = esc_url( $custom_redirect );
	} else if ( wpum_get_option( 'logout_redirect' ) ) {
			$redirect = esc_url( get_permalink( wpum_get_option( 'logout_redirect' ) ) );
	}

	return wp_logout_url( apply_filters( 'wpum_logout_url', $redirect, $custom_redirect ) );

}
endif;

if ( ! function_exists( 'wpum_get_username_label' ) ) :
/**
 * Returns the correct username label on the login form
 * based on the selected login method.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function wpum_get_username_label() {

	$label = __( 'Username', 'wpum' );

	if ( wpum_get_option( 'login_method' ) == 'email' ) {
		$label = __( 'Email', 'wpum' );
	} else if ( wpum_get_option( 'login_method' ) == 'username_email' ) {
			$label = __( 'Username or email', 'wpum' );
	}

	return $label;

}
endif;

if ( ! function_exists( 'wpum_login_form' ) ) :
/**
 * Display login form.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function wpum_login_form( $args = array() ) {

	$defaults = array(
		'echo'           => true,
		'redirect'       => wpum_get_login_redirect_url(),
		'form_id'        => null,
		'label_username' => wpum_get_username_label(),
		'label_password' => __( 'Password', 'wpum' ),
		'label_remember' => __( 'Remember Me', 'wpum' ),
		'label_log_in'   => __( 'Login', 'wpum' ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'login_link'     => 'yes',
		'psw_link'       => 'yes',
		'register_link'  => 'yes'
	);

	// Parse incoming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Show already logged in message
	if ( is_user_logged_in() ) :

		get_wpum_template( 'already-logged-in.php', array( 'args' => $args ) );

	// Show login form if not logged in
	else :

		get_wpum_template( 'forms/login-form.php', array( 'args' => $args ) );

		// Display helper links
		do_action( 'wpum_do_helper_links', $args['login_link'], $args['register_link'], $args['psw_link'] );

	endif;

}
endif;

/**
 * Get email address of a registered user.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          email address of the user or empty if not found.
 */
function wpum_get_user_email( $user_id ) {

	$email = '';

	$user = new WP_User( $user_id );
	$email = ( isset( $user->data->user_email ) ) ? $user->data->user_email : '';

	return $email;

}

/**
 * Get username of a register user.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          the username of the user or empty if not found.
 */
function wpum_get_user_username( $user_id ) {

	$username = '';

	$user = new WP_User( $user_id );
	$username = ( isset( $user->data->user_login ) ) ? $user->data->user_login : '';

	return $username;

}

/**
 * Get displayed name of a registered user.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          the displayed name of the user or empty if not found.
 */
function wpum_get_user_displayname( $user_id ) {

	$display_name = '';

	$user = new WP_User( $user_id );
	$display_name = ( isset( $user->data->display_name ) ) ? $user->data->display_name : '';

	return $display_name;

}

/**
 * Get website url of a registered user.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          the website url of the user or empty if not found.
 */
function wpum_get_user_website( $user_id ) {

	$website = '';

	$user = new WP_User( $user_id );
	$website = ( isset( $user->data->user_url ) ) ? $user->data->user_url : '';

	return $website;

}

/**
 * Get the description of a registered user.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          the description of a registered user or empty if not found.
 */
function wpum_get_user_description( $user_id ) {

	$description = get_user_meta( $user_id, 'description', $single = true );

	return $description;

}

/**
 * Get a formatted registration date of a user.
 * The date is formatted based on the admin settings.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          the formatted registration date.
 */
function wpum_get_user_registration_date( $user_id ) {

	$date = '';

	$user = new WP_User( $user_id );
	$date = ( isset( $user->data->user_registered ) ) ? $user->data->user_registered : '';

	if( ! empty( $date ) ) {
		$date = date_i18n( get_option('date_format'), strtotime( $date ) );
	}

	return $date;

}

/**
 * Get first name of a registered user.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          the first name or empty if not found.
 */
function wpum_get_user_fname( $user_id ) {

	$fname = '';

	$user = new WP_User( $user_id );
	$fname = ( isset( $user->first_name ) ) ? $user->first_name : '';

	return $fname;

}

/**
 * Get last name of a registered user.
 *
 * @since 1.2.0
 * @param  int $user_id id number of a registered user.
 * @return string          the last name or empty if not found.
 */
function wpum_get_user_lname( $user_id ) {

	$lname = '';

	$user = new WP_User( $user_id );
	$lname = ( isset( $user->last_name ) ) ? $user->last_name : '';

	return $lname;

}

/**
 * Retrieve the user id number of the currently displayed user.
 * This function works only within the profile page.
 *
 * @since 1.2.0
 * @return mixed user id if found - false if not found.
 */
function wpum_get_displayed_user_id() {

	$user_id   = false;
	$who       = wpum_is_single_profile();
	$structure = get_option( 'wpum_permalink', 'user_id' );

	// If we're on the profile but no user has been given, we return the current user id.
	if( ! $who && ! empty( $structure ) && is_page( wpum_get_core_page_id( 'profile' ) ) ) {
		return get_current_user_id();
	}

	// Process the retrieved user.
	if( $who && ! empty ( $structure ) ) {

		switch ( $structure ) {
			case 'user_id':
				$user_id = esc_attr( $who );
				break;
			case 'username':
				$retrieve = get_user_by( 'login', esc_attr( urldecode( $who ) ) );
				$user_id  = $retrieve->data->ID;
				break;
			case 'nickname':
					// WP_User_Query arguments.
					$args = array (
						'search'         => esc_attr( $who ),
						'search_columns' => array( 'user_nicename' ),
					);

					// The User Query.
					$user_query = new WP_User_Query( $args );
					$user_query = $user_query->get_results();
					$user_id    = $user_query[0]->data->ID;

				break;

		}

	}

	return $user_id;

}

/**
 * Given an url, it creates a new url with the redirect_to parameter.
 *
 * @param  string $url         the base url.
 * @param  string $redirect_to the url to redirect to.
 * @since 1.3.0
 * @return void
 */
function wpum_create_redirect_url( $url, $redirect_to ) {

	$redirect_url = add_query_arg( array( 'redirect_to' => urlencode( $redirect_to ) ), $url );
	$redirect_url = esc_url( $redirect_url );

	return $redirect_url;

}
