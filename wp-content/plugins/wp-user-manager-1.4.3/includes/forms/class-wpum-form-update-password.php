<?php
/**
 * WP User Manager Forms: update password form
 * This form is used into the account page when a user is already logged in.
 *
 * @package     wp-user-manager
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Form_Update_Password Class
 *
 * @since 1.0.0
 */
class WPUM_Form_Update_Password extends WPUM_Form {

	public static $form_name = 'update-password';

	/**
	 * Init the form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {

		add_action( 'wp', array( __CLASS__, 'process' ) );

		add_filter( 'wpum/form/validate=update-password', array( __CLASS__, 'validate_password_field' ), 10, 3 );

	}

	/**
	 * Define password update form fields
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function get_update_password_fields() {

		self::$fields = apply_filters( 'wpum_password_update_fields', array(
			'password_update' => array(
				'password' => array(
					'label'       => __( 'Password', 'wpum' ),
					'type'        => 'password',
					'required'    => false,
					'placeholder' => '',
					'priority'    => 1
				),
				'password_repeat' => array(
					'label'       => __( 'Repeat Password', 'wpum' ),
					'type'        => 'password',
					'required'    => false,
					'placeholder' => '',
					'priority'    => 2
				),
			),
		) );

	}

	/**
	 * Validate the password field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_password_field( $passed, $fields, $values ) {

		$pwd = $values['password_update']['password'];
		$pwd_strenght = wpum_get_option('password_strength');

		if( empty( $pwd ) )
			return new WP_Error( 'password-validation-error', __( 'Enter a password.', 'wpum' ) );

		// Check strenght
		$containsLetter  = preg_match('/[A-Z]/', $pwd);
		$containsDigit   = preg_match('/\d/', $pwd);
		$containsSpecial = preg_match('/[^a-zA-Z\d]/', $pwd);

		if($pwd_strenght == 'weak') {
			if(strlen($pwd) < 8)
				return new WP_Error( 'password-validation-error', __( 'Password must be at least 8 characters long.', 'wpum' ) );
		}
		if($pwd_strenght == 'medium') {
			if( !$containsLetter || !$containsDigit || strlen($pwd) < 8 )
				return new WP_Error( 'password-validation-error', __( 'Password must be at least 8 characters long and contain at least 1 number and 1 uppercase letter.', 'wpum' ) );
		}
		if($pwd_strenght == 'strong') {
			if( !$containsLetter || !$containsDigit || !$containsSpecial || strlen($pwd) < 8 )
				return new WP_Error( 'password-validation-error', __( 'Password must be at least 8 characters long and contain at least 1 number and 1 uppercase letter and 1 special character.', 'wpum' ) );
		}

		// Check if matches repeated password
		if( $pwd !== $values['password_update']['password_repeat'] )
			return new WP_Error( 'password-validation-error', __( 'Passwords do not match.', 'wpum' ) );

		return $passed;

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
		self::get_update_password_fields();

		// Get posted values
		$values = self::get_posted_fields();

		if ( empty( $_POST['wpum_submit_form'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-password' ) ) {
			return;
		}

		// Check values
		if( empty($values) || !is_array($values) )
			return;

		// Validate required
		if ( is_wp_error( ( $return = self::validate_fields( $values, self::$form_name ) ) ) ) {
			self::add_error( $return->get_error_message() );
			return;
		}

		// Proceed to update the password
		$user_data = array(
			'ID'        => get_current_user_id(),
			'user_pass' => $values['password_update']['password']
		);

		$user_id = wp_update_user( $user_data );

		if ( is_wp_error( $user_id ) ) {

			self::add_error( $user_id->get_error_message() );

		} else {

			self::add_confirmation( __('Password successfully updated.', 'wpum') );

		}

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
		self::get_update_password_fields();

		if( isset( $_POST['submit_wpum_update_password'] ) ) {
			// Show errors from fields
			self::show_errors();
			// Show confirmation messages
			self::show_confirmations();
		}

		// Display template
		if( is_user_logged_in() ) :

			get_wpum_template( 'forms/password-update-form.php',
				array(
					'form'            => self::$form_name,
					'password_fields' => self::get_fields( 'password_update' ),
				)
			);

		else :

			echo wpum_login_form();

		endif;

	}

}
