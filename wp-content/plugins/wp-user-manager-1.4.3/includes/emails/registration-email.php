<?php
/**
 * Registration Email
 *
 * @package     wp-user-manager
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_register_Email Class
 * This class registers a new email for the editor.
 *
 * @since 1.0.0
 */
class WPUM_register_Email extends WPUM_Emails {

	/**
	 * This function sets up a custom email.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	function __construct() {

		// Configure Email
		$this->name        = 'register';
		$this->title       = __( "Registration Email", 'wpum' );
		$this->description = __( "This is the email that is sent to the user upon successful registration.", 'wpum' );
		$this->subject     = $this->subject();
		$this->message     = $this->message();

		// do not delete!
		parent::__construct();
	}

	/**
	 * The default subject of the email.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	public static function subject() {

		$subject = sprintf( __('Your %s Account', 'wpum'), get_option( 'blogname' ) );

		return $subject;

	}

	/**
	 * The default message of the email.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	public static function message() {

		$message = __( "Hello {username}, \n\n", 'wpum' );
		$message .= __( "Welcome to {sitename}, \n\n", 'wpum' );
		$message .= __( "These are your account details \n\n", 'wpum');
		$message .= __( "Username: {username},\n", 'wpum' );
		$message .= __( "Password: {password}", 'wpum' );

		return $message;

	}

}

new WPUM_register_Email();
