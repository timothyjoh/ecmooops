<?php
/**
 * Emails Editor
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Emails_Editor Class
 *
 * @since 1.0.0
 */
class WPUM_Emails_Editor {

	public $allowed_email_tags;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->allowed_email_tags = array(
		  'a'         => array(
		      'href'  => array(),
		      'title' => array()
		  ),
			'p'          => array(),
			'br'         => array(),
			'em'         => array(),
			'strong'     => array(),
		);

		if( ! class_exists( 'WP_List_Table' ) ) {
		    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		add_action( 'wpum_emails_editor', array( $this, 'wpum_option_emails_list' ) );
		add_action( 'wpum_edit_email', array( $this, 'save_email_notifications' ) );

	}

	/**
	 * Display Table with list of emails.
	 *
	 * @since 1.0.0
	 * @return void
	*/
	public function wpum_option_emails_list() {

		ob_start();

		// Prepare the table for display
		$wpum_emails_table = new WPUM_Emails_List();
	    $wpum_emails_table->prepare_items();
	    $wpum_emails_table->display();

	    echo '<p class="description">' . __('Click the "Edit Email" button to customize notifications.', 'wpum') . '<br/>' . __('Only the emails into the list above, will use the "From Name" and "From Email" options above.', 'wpum') . '</p>';

		echo ob_get_clean();

	}

	/**
	 * Displays the page that handles the email editor output.
	 *
	 * @since 1.0.0
	 * @return void
	*/
	public static function get_emails_editor_page() {

		// Abort if not correctly loaded
		if ( !isset( $_GET['wpum_action'] ) || isset( $_GET['wpum_action'] ) && $_GET['wpum_action'] !== 'edit' || !current_user_can( 'manage_options' ) || !isset( $_GET['email-id'] ) || !isset( $_GET['email-title'] ) ) {
			_doing_it_wrong( __FUNCTION__ , _x( 'You have no rights to access this page', '_doing_it_wrong error message', 'wpum' ), '1.0.0' );
			return;
		}

		include WPUM_PLUGIN_DIR . 'includes/admin/edit-email-page.php';

	}

	/**
	 * Processes the update of an email notification
	 *
	 * @since 1.0.0
	 * @param array $data The post data
	 * @return void
	 */
	public function save_email_notifications( $data ) {

		// Check everything is correct.
		if( ! is_admin() ) {
			return;
		}

		if( ! current_user_can( 'manage_options' ) ) {
			_doing_it_wrong( __FUNCTION__ , _x( 'You have no rights to access this page', '_doing_it_wrong error message', 'wpum' ), '1.0.0' );
			return;
		}

		if( ! wp_verify_nonce( $data['wpum-email-nonce'], 'wpum_email_nonce' ) ) {
			_doing_it_wrong( __FUNCTION__ , _x( 'Nonce verification failed', '_doing_it_wrong error message', 'wpum' ), '1.0.0' );
			return;
		}

		if( ! isset( $data['email_id'] ) ) {
			_doing_it_wrong( __FUNCTION__ , _x( 'No email ID was provided', '_doing_it_wrong error message', 'wpum' ), '1.0.0' );
			return;
		}

		// Store the data
		$subject = isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : sprintf( __('%s email', 'wpum'), $data['email_id'] );
		$message = isset( $data['message'] ) ? wp_kses( $data['message'], $this->allowed_email_tags ) : false;

		$emails[ esc_attr( $data['email_id'] ) ] = array(
			'subject'     => $subject,
			'message'     => $message,
		);

		// Get current emails
		$current_emails = get_option( 'wpum_emails' );

		// Merge new and old
		$new_emails = array_merge( $current_emails, $emails );

		// Update database option
		update_option( 'wpum_emails', $new_emails );

		wp_redirect( admin_url( 'users.php?page=wpum-settings&tab=emails&emails-updated=true' ) ); exit;

	}

}
new WPUM_Emails_Editor;
