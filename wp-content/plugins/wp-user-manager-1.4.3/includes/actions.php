<?php
/**
 * Plugin Actions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Stops users from accessing wp-login.php?action=register
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_restrict_wp_register() {

	if ( wpum_get_option( 'wp_login_signup_redirect' ) ):
		$permalink = wpum_get_option( 'wp_login_signup_redirect' );
		wp_redirect( esc_url( get_permalink( $permalink ) ) );
		exit();
	endif;

}
add_action( 'login_form_register', 'wpum_restrict_wp_register' );

/**
 * Stops users from accessing wp-login.php?action=lostpassword
 *
 * @since 1.1.0
 * @access public
 * @return void
 */
function wpum_restrict_wp_lostpassword() {

	if ( wpum_get_option( 'wp_login_password_redirect' ) ):
		$permalink = wpum_get_option( 'wp_login_password_redirect' );
		wp_redirect( esc_url( get_permalink( $permalink ) ) );
		exit();
	endif;

}
add_action( 'login_form_lostpassword', 'wpum_restrict_wp_lostpassword' );

/**
 * Stops users from seeing the admin bar on the frontend.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_remove_admin_bar() {

	$excluded_roles = wpum_get_option( 'adminbar_roles' );
	$user = wp_get_current_user();

	if ( !empty( $excluded_roles ) && array_intersect( $excluded_roles, $user->roles ) && !is_admin() ) {
		if ( current_user_can( $user->roles[0] ) ) {
			show_admin_bar( false );
		}
	}

}
add_action( 'after_setup_theme', 'wpum_remove_admin_bar' );

/**
 * Stops users from seeing the profile.php page in wp-admin.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_remove_profile_wp_admin() {

	if ( !current_user_can( 'administrator' ) && IS_PROFILE_PAGE && wpum_get_option( 'backend_profile_redirect' ) ) {
		wp_redirect( esc_url( get_permalink( wpum_get_option( 'backend_profile_redirect' ) ) ) );
		exit;
	}

}
add_action( 'load-profile.php', 'wpum_remove_profile_wp_admin' );

/**
 * Show content of the User ID column in user list page
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_show_user_id_column_content( $value, $column_name, $user_id ) {
	if ( 'user_id' == $column_name )
		return $user_id;
	return $value;
}
add_action( 'manage_users_custom_column',  'wpum_show_user_id_column_content', 10, 3 );

/**
 * Register widgets
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_register_widgets() {
	register_widget( 'WPUM_Recently_Registered_Users' );
	register_widget( 'WPUM_Password_Recovery' );
	register_widget( 'WPUM_Registration_Form_Widget' );
	register_widget( 'WPUM_Login_Form_Widget' );
}
add_action( 'widgets_init', 'wpum_register_widgets', 1 );

/**
 * Add hidden field into login form to identify login
 * has been made from a wpum login form
 *
 * @since 1.0.5
 * @access public
 * @return mixed
 */
function wpum_add_field_to_login( $content, $args ) {

	$output = '';

	// Check if it's a wpum login form.
	// We add the hidden field only to forms powered by wpum,
	// to avoid conflicts with other login forms.
	// Only a wpum login form would have the login_link key.
	if( is_array( $args ) && in_array( 'login_link' , $args ) ) {
		$output .= '<input type="hidden" name="wpum_is_login_form" value="wpum">';
	}

	// Store redirect url if specified
	$redirect = ( isset( $_GET['redirect_to'] ) && $_GET['redirect_to'] !=='' ) ? urlencode( $_GET['redirect_to'] ) : false;
	$output .= '<input type="hidden" name="wpum_test" value="'.$redirect.'">';

	return $output;

}
add_action( 'login_form_middle', 'wpum_add_field_to_login', 10, 2 );

/**
 * Authenticate the user and decide which login method to use.
 *
 * @since 1.0.3
 * @param  string $user     user object
 * @param  string $username typed username
 * @param  string $password typed password
 * @return void Results of autheticating via wp_authenticate_username_password(), using the username found when looking up via email.
 */
function wpum_authenticate_login_method( $user, $username, $password ) {

	// Get default login method
	$login_method = wpum_get_option( 'login_method', 'username' );

	// Authenticate via email only
	if( $login_method == 'email'  ) {

		if ( is_a( $user, 'WP_User' ) )
			return $user;

			if( !empty( $username ) && is_email( $username ) ) {

				$user = get_user_by( 'email', $username );

				if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
					$username = $user->user_login;

				return wp_authenticate_username_password( null, $username, $password );

			}

	} else if( $login_method == 'username_email' ) {

		if ( is_a( $user, 'WP_User' ) )
			return $user;

			$username = sanitize_user( $username );

			if( !empty( $username ) && is_email( $username ) ) {

				$user = get_user_by( 'email', $username );

				if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
					$username = $user->user_login;

				return wp_authenticate_username_password( null, $username, $password );

			} else {

				return wp_authenticate_username_password( null, $username, $password );

			}

	}

}

// Run filters only when alternative methods are selected
if( ( wpum_get_option( 'login_method') == 'email' || wpum_get_option( 'login_method') == 'username_email' ) && isset( $_POST['wpum_is_login_form'] ) ) {
	remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
	add_filter( 'authenticate', 'wpum_authenticate_login_method', 20, 3 );
}

/**
 * Authenticates the login form, if failed
 * returns back to the page where it came from.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_authenticate_login_form( $user ) {

	if ( ! defined( 'DOING_AJAX' ) && isset( $_SERVER['HTTP_REFERER'] ) && isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) :

		// check what page the login attempt is coming from
		$referrer = $_SERVER['HTTP_REFERER'];

		// remove previously added query strings
		$referrer = add_query_arg( array(
			'login'       => false,
			'captcha'     => false
		), $referrer );

		$error = false;

		if ( $_POST['log'] == '' || $_POST['pwd'] == '' ) {
			$error = true;
		}

		// check that were not on the default login page.
		if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) && $error ) {

			$referrer =  add_query_arg( array(
				'login' => 'failed',
				'redirect_to' => ( isset( $_POST['redirect_to'] ) && $_POST['redirect_to'] !== '' ) ? urlencode( $_POST['redirect_to'] ): false
			), $referrer );

			wp_redirect( esc_url_raw( $referrer ) );
			exit;

		}

	endif;

}
add_action( 'authenticate', 'wpum_authenticate_login_form' );

/**
 * Redirects wp_login_form when wrong credentials.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_handle_failed_login( $user ) {

	if ( isset( $_SERVER['HTTP_REFERER'] ) && !defined( 'DOING_AJAX' ) ) :
		// check what page the login attempt is coming from
		$referrer = $_SERVER['HTTP_REFERER'];

		// remove previously added query strings
		$referrer = add_query_arg( array(
			'login'       => false,
			'captcha'     => false
		), $referrer );

		// check that were not on the default login page
		if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) && $user != null ) {

			$referrer =  add_query_arg( array(
				'login'       => 'failed',
				'redirect_to' => ( isset( $_POST['redirect_to'] ) && $_POST['redirect_to'] !== '' ) ? urlencode( $_POST['redirect_to'] ): false
			), $referrer );

			wp_redirect( esc_url_raw( $referrer ) );
			exit;

		}
	endif;

}
add_action( 'wp_login_failed', 'wpum_handle_failed_login' );

/**
 * Displays a message if php version is lower than required one.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_php_is_old() {
	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) { ?>
		<div class="error">
			<p><?php echo sprintf( __( 'This plugin requires a minimum PHP Version 5.3 to be installed on your host. <a href="%s" target="_blank">Click here to read how you can update your PHP version</a>.', 'wpum'), 'http://www.wpupdatephp.com/contact-host/' ); ?></p>
		</div>
	<?php
	}
}
add_action( 'admin_notices', 'wpum_php_is_old' );

/**
 * Add a "view profile" link to the admin user table.
 *
 * @since 1.1.0
 * @param  array $actions     list of actions
 * @param  object $user_object user details
 * @return array              list of actions
 */
function wpum_admin_user_action_link( $actions, $user_object ) {

	if( wpum_get_core_page_id( 'profile' ) ) :
		$actions['view_profile'] = '<a href="'. wpum_get_user_profile_url( $user_object ) .'" target="_blank">'. esc_html__( 'View Profile', 'wpum' ) .'</a>';
	endif;

	return $actions;

}
add_filter( 'user_row_actions', 'wpum_admin_user_action_link', 10, 2 );
