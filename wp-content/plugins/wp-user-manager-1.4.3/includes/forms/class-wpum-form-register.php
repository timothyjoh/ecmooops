<?php
/**
 * WP User Manager Forms
 *
 * @package     wp-user-manager
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Form_Register Class
 *
 * @since 1.0.0
 */
class WPUM_Form_Register extends WPUM_Form {

	/**
	 * The name of the form
	 */
	public static $form_name = 'register';

	/**
	 * Password Method
	 */
	public static $random_password = true;

	/**
	 * Init the form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {

		add_action( 'wp', array( __CLASS__, 'process' ) );

		// Validate and process passwords.
		if( wpum_get_option( 'custom_passwords' ) ) {

			self::$random_password = false;

			add_filter( 'wpum/form/validate=register', array( __CLASS__, 'validate_password' ), 10, 3 );

			if( wpum_get_option('login_after_registration') ) {
				add_action( 'wpum/form/register/done', array( __CLASS__, 'do_login' ), 11, 3 );
			}

		}

		// Make sure the submitted email is valid and not in use.
		add_filter( 'wpum/form/validate=register', array( __CLASS__, 'validate_email' ), 10, 3 );

		// Add a very basic honeypot spam prevention field.
		if( wpum_get_option( 'enable_honeypot' ) ) {
			add_action( 'wpum_get_registration_fields', array( __CLASS__, 'add_honeypot' ) );
			add_filter( 'wpum/form/validate=register', array( __CLASS__, 'validate_honeypot' ), 10, 3 );
		}

		/**
		 * Adds a "terms" checkbox field to the signup form.
		 */
		if( wpum_get_option('enable_terms') ) {
			add_action( 'wpum_get_registration_fields', array( __CLASS__, 'add_terms' ) );
		}

		// Allow user to select a user role upon registration.
		if( wpum_get_option( 'allow_role_select' ) ) {
			add_action( 'wpum_get_registration_fields', array( __CLASS__, 'add_role' ) );
			add_filter( 'wpum/form/validate=register', array( __CLASS__, 'validate_role' ), 10, 3 );
			add_action( 'wpum/form/register/success', array( __CLASS__, 'save_role' ), 10, 10 );
		}

		// Prevent users from using specific usernames if enabled.
		$exclude_usernames = wpum_get_option( 'exclude_usernames' );

		if( ! empty( $exclude_usernames ) ) {
			add_filter( 'wpum/form/validate=register', array( __CLASS__, 'validate_username' ), 10, 3 );
		}

		// Store uploaded avatars into the database.
		if( wpum_get_option('custom_avatars') && WPUM()->fields->show_on_registration( 'user_avatar' ) ) {
			add_action( 'wpum/form/register/success', array( __CLASS__, 'save_avatar' ), 10, 3 );
		}

		// Redirect to a page after successfull registration.
		if( wpum_get_option('login_after_registration') && wpum_get_option( 'custom_passwords' ) && wpum_get_option( 'registration_redirect' ) ) {

			add_filter( 'wpum_redirect_after_automatic_login', array( __CLASS__, 'adjust_redirect_url' ), 10, 2 );

		} elseif( ! wpum_get_option('login_after_registration') || ! wpum_get_option( 'custom_passwords' ) ) {

			if( wpum_get_option( 'registration_redirect' ) )
				add_action( 'wpum/form/register/done', array( __CLASS__, 'redirect_on_success' ), 9999, 3 );

		}

	}

	/**
	 * Define registration fields
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function get_registration_fields() {

		if ( self::$fields ) {
			return;
		}

		self::$fields = array(
			'register' => wpum_get_registration_fields()
		);

	}

	/**
	 * Validate the password field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_password( $passed, $fields, $values ) {

		$pwd          = $values['register']['password'];
		$pwd_strenght = wpum_get_option('password_strength');

		$containsLetter  = preg_match( '/[A-Z]/', $pwd );
		$containsDigit   = preg_match( '/\d/', $pwd );
		$containsSpecial = preg_match( '/[^a-zA-Z\d]/', $pwd );

		if( $pwd_strenght == 'weak' ) {
			if( strlen( $pwd ) < 8)
				return new WP_Error( 'password-validation-error', __( 'Password must be at least 8 characters long.', 'wpum' ) );
		}
		if( $pwd_strenght == 'medium' ) {
			if( ! $containsLetter || ! $containsDigit || strlen( $pwd ) < 8 )
				return new WP_Error( 'password-validation-error', __( 'Password must be at least 8 characters long and contain at least 1 number and 1 uppercase letter.', 'wpum' ) );
		}
		if( $pwd_strenght == 'strong' ) {
			if( ! $containsLetter || ! $containsDigit || ! $containsSpecial || strlen( $pwd ) < 8 )
				return new WP_Error( 'password-validation-error', __( 'Password must be at least 8 characters long and contain at least 1 number and 1 uppercase letter and 1 special character.', 'wpum' ) );
		}

		return $passed;

	}

	/**
	 * Autologin.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function do_login( $user_id, $values ) {

		$userdata = get_userdata( $user_id );

		$data = array();
		$data['user_login']    = $userdata->user_login;
		$data['user_password'] = $values['register']['password'];
		$data['rememberme']    = true;

		$user_login = wp_signon( $data, false );

		wp_redirect( apply_filters( 'wpum_redirect_after_automatic_login', get_permalink(), $user_id ) );
		exit;

	}

	/**
	 * Adjust the redirect url of the automatic login functionality.
	 * This is triggered when a custom successfull registration page has been assigned.
	 *
	 * @param  string $permalink original url.
	 * @param  int $user_id   the id of the user.
	 * @return string            the new url.
	 */
	public static function adjust_redirect_url( $permalink, $user_id ) {

		return wpum_registration_redirect_url();

	}

	/**
	 * Validate email field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_email( $passed, $fields, $values ) {

		$mail = $values['register'][ 'user_email' ];

		if( email_exists( $mail ) )
			return new WP_Error( 'email-validation-error', __( 'Email address already exists.', 'wpum' ) );

		return $passed;

	}

	/**
	 * Add Honeypot field markup.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_honeypot( $fields ) {

		$fields[ 'comments' ] = array(
			'label'       => 'Comments',
			'type'        => 'textarea',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 9999,
			'class'       => 'wpum-honeypot-field'
		);

		return $fields;

	}

	/**
	 * Validate the honeypot field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_honeypot( $passed, $fields, $values ) {

		$fake_field = $values['register'][ 'comments' ];

		if( $fake_field )
			return new WP_Error( 'honeypot-validation-error', __( 'Failed Honeypot validation', 'wpum' ) );

		return $passed;

	}

	/**
	 * Add Terms field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_terms( $fields ) {

		$fields[ 'terms' ] = array(
			'label'       => __('Terms &amp; Conditions', 'wpum'),
			'type'        => 'checkbox',
			'description' => sprintf(__('By registering to this website you agree to the <a href="%s" target="_blank">terms &amp; conditions</a>.', 'wpum'), get_permalink( wpum_get_option('terms_page') ) ),
			'required'    => true,
			'priority'    => 9999,
		);

		return $fields;

	}

	/**
	 * Add Role field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_role( $fields ) {

		$fields[ 'role' ] = array(
			'label'       => __('Select Role', 'wpum'),
			'type'        => 'select',
			'required'    => true,
			'options'     => wpum_get_allowed_user_roles(),
			'description' => __('Select your user role', 'wpum'),
			'priority'    => 9999,
			'value'       => get_option( 'default_role' )
		);

		return $fields;

	}

	/**
	 * Validate the role field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_role( $passed, $fields, $values ) {

		$role_field     = $values['register'][ 'role' ];
		$selected_roles = array_flip( wpum_get_option( 'register_roles' ) );

		if( !array_key_exists( $role_field , $selected_roles ) )
			return new WP_Error( 'role-validation-error', __( 'Select a valid role from the list.', 'wpum' ) );

		return $passed;

	}

	/**
	 * Save the role.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function save_role( $user_id, $values ) {

		$user = new WP_User( $user_id );
		$user->set_role( $values['register'][ 'role' ] );

	}

	/**
	 * Validate username field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_username( $passed, $fields, $values ) {

		$nickname = $values['register'][ 'username' ];

		if( wpum_get_option('exclude_usernames') && array_key_exists( $nickname , wpum_get_disabled_usernames() ) )
			return new WP_Error( 'nickname-validation-error', __( 'This nickname cannot be used.', 'wpum' ) );

		// Check for nicknames if permalink structure requires unique nicknames.
		if( get_option('wpum_permalink') == 'nickname'  ) :

			if( wpum_nickname_exists( $nickname ) )
				return new WP_Error( 'username-validation-error', __( 'This nickname cannot be used.', 'wpum' ) );

		endif;

		return $passed;

	}

	/**
	 * Add avatar to user custom field.
	 * Also deletes previously selected avatar.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function save_avatar( $user_id, $values ) {

		$avatar_field = $values['register'][ 'user_avatar' ];

		if( !empty( $avatar_field ) && is_array( $avatar_field ) ) {
			update_user_meta( $user_id, "current_user_avatar", esc_url( $avatar_field['url'] ) );
			update_user_meta( $user_id, '_current_user_avatar_path', $avatar_field['path'] );
		}

	}

	/**
	 * Redirect user to a page upon successfull registration.
	 *
	 * @param  int $user_id id of the newly registered user.
	 * @param  array $values  list of values submitted into the registration form.
	 * @return void
	 */
	public static function redirect_on_success( $user_id, $values ) {

		if( wpum_registration_redirect_url() ) {

			wp_redirect( wpum_registration_redirect_url() );
			exit;

		}

	}

	/**
	 * Process the submission.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function process() {

		// Get fields
		self::get_registration_fields();

		// Get posted values
		$values = self::get_posted_fields();

		if ( empty( $_POST['wpum_submit_form'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'register' ) ) {
			return;
		}

		// Validate required
		if ( is_wp_error( ( $return = self::validate_fields( $values, self::$form_name ) ) ) ) {
			self::add_error( $return->get_error_message() );
			return;
		}

		// Let's do the registration
		self::do_registration( $values['register']['username'], $values['register']['user_email'], $values );

	}

	/**
	 * Do registration.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function do_registration( $username, $email, $values ) {

		// Try registration
		if( self::$random_password ) {

			$do_user = self::random_psw_registration( $username, $email );
			$pwd     = $do_user['pwd'];

		} else {

			$pwd     = $values['register']['password'];
			$do_user = wp_create_user( $username, $pwd, $email );

		}

		// Check for errors.
		$do_user = isset( $do_user['do_user'] ) ? $do_user['do_user'] : $do_user;

		if ( is_wp_error( $do_user ) ) {

			foreach ($do_user->errors as $error) {
				self::add_error( $error[0] );
			}
			return;

		} else {

			$user_id = $do_user;

			// Set some meta if available
			if( array_key_exists( 'first_name' , $values['register'] ) )
				update_user_meta( $user_id, 'first_name', $values['register']['first_name'] );
			if( array_key_exists( 'last_name' , $values['register'] ) )
				update_user_meta( $user_id, 'last_name', $values['register']['last_name'] );
			if( array_key_exists( 'user_url' , $values['register'] ) )
				wp_update_user( array( 'ID' => $user_id, 'user_url' => $values['register']['user_url'] ) );
			if( array_key_exists( 'description' , $values['register'] ) )
				update_user_meta( $user_id, 'description', $values['register']['description'] );

			if( self::$random_password ) :
				self::add_confirmation( apply_filters( 'wpum/form/register/success/message', __( 'Registration complete. We have sent you a confirmation email with your password.', 'wpum' ) ) );
			else :
				self::add_confirmation( apply_filters( 'wpum/form/register/success/message', __( 'Registration complete.', 'wpum' ) ) );
			endif;

			// Add ability to extend registration process.
			do_action( "wpum/form/register/success" , $user_id, $values );

			// Send notification if password is manually added by the user.
			wpum_new_user_notification( $do_user, $pwd );

			// Needed to close the registration process properly.
			do_action( "wpum/form/register/done" , $user_id, $values );

		}

	}

	/**
	 * Generate random password and register user
	 *
	 * @since 1.0.3
	 * @param  string $username username
	 * @param  string $email    email
	 * @return mixed
	 */
	public static function random_psw_registration( $username, $email ) {

		// Generate something random for a password.
		$pwd = wp_generate_password( 20, false );

		$do_user = wp_create_user( $username, $pwd, $email );

		return array( 'do_user' => $do_user, 'pwd' => $pwd );

	}

	/**
	 * Output the form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function output( $atts = array() ) {

		// Get fields
		self::get_registration_fields();

		if( isset( $_POST['submit_wpum_register'] ) ) {
			// Show errors from fields
			self::show_errors();
			// Show confirmation messages
			self::show_confirmations();
		}

		// Display template
		if( !get_option( 'users_can_register' ) ) :

			// Display error message
			$message = array(
				'id'   => 'wpum-registrations-disabled',
				'type' => 'notice',
				'text' => __( 'Registrations are currently disabled.', 'wpum' )
			);
			wpum_message( $message );

		elseif( is_user_logged_in() ) :

			get_wpum_template( 'already-logged-in.php',
				array(
					'args' => $atts
				)
			);

		// Show register form if not logged in
		else :

			get_wpum_template( 'forms/registration-form.php',
				array(
					'atts' => $atts,
					'form' => self::$form_name,
					'register_fields' => self::get_fields( 'register' ),
				)
			);

		endif;

	}

}
