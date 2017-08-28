<?php
/**
 * Ajax Handler
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Ajax_Handler Class
 * Handles all the ajax functionalities of the plugin.
 *
 * @since 1.0.0
 */
class WPUM_Ajax_Handler {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Restore Email
		add_action( 'wp_ajax_wpum_restore_emails', array( $this, 'restore_emails' ) );

		// Avatar removal method
		add_action( 'wp_ajax_wpum_remove_file', array( $this, 'remove_user_file' ) );
		add_action( 'wp_ajax_nopriv_wpum_remove_file', array( $this, 'remove_user_file' ) );

		// Update custom fields order
		add_action( 'wp_ajax_wpum_update_fields_order', array( $this, 'update_fields_order' ) );

	}

	/**
	 * Restore email into the backend.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function restore_emails() {

		// Check our nonce and make sure it's correct.
		check_ajax_referer( 'wpum_nonce_email_reset', 'wpum_backend_security' );

		// Abort if something isn't right.
		if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
			$return = array(
				'message' => __( 'Error.', 'wpum' ),
			);

			wp_send_json_error( $return );
		}

		// Delete the option first
		delete_option( 'wpum_emails' );

		// Get all registered emails
		wpum_register_emails();

		$return = array(
			'message' => __( 'Emails successfully restored.', 'wpum' ),
		);

		wp_send_json_success( $return );

	}

	/**
	 * Remove the avatar of a user.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function remove_user_file() {

		$form = esc_attr( $_REQUEST['submitted_form'] );

		check_ajax_referer( $form, 'wpum_removal_nonce' );

		$field_id = $_REQUEST['field_id'];
		$user_id = get_current_user_id();

		// Functionality to remove avatar.
		if( $field_id == 'user_avatar' ) {

			if( $field_id && is_user_logged_in() ) {

				delete_user_meta( $user_id, "current_{$field_id}" );

				// Deletes previously selected avatar.
				$previous_avatar = get_user_meta( $user_id, "_current_{$field_id}_path", true );
				if( $previous_avatar )
					wp_delete_file( $previous_avatar );

				delete_user_meta( $user_id, "_current_{$field_id}_path" );

				$return = array(
					'valid'   => true,
					'message' => apply_filters( 'wpum_avatar_deleted_success_message', __( 'Your profile picture has been deleted.', 'wpum' ) )
				);

				wp_send_json_success( $return );

			} else {

				$return = array(
					'valid'   => false,
					'message' => __( 'Something went wrong.', 'wpum' )
				);

				wp_send_json_error( $return );

			}

		// This is needed for all the other field types.
		} else {

			if( $field_id && is_user_logged_in() ) {

				$field_files = get_user_meta( $user_id, $field_id, true );
				$field_files = maybe_unserialize( $field_files );

				if( is_array( $field_files ) ) {

					if( wpum_is_multi_array( $field_files ) ) {

						foreach ( $field_files as $key => $file ) {
							wp_delete_file( $file['path'] );
						}

					} else {

						wp_delete_file( $field_files['path'] );

					}

				}

				delete_user_meta( $user_id, $field_id );

				$return = array(
					'valid'   => true,
					'message' => apply_filters( 'wpum_files_deleted_success_message', __( 'Files successfully removed.', 'wpum' ) )
				);

				wp_send_json_success( $return );

			}

		}

	}

	/**
	 * Updates custom fields order.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function update_fields_order() {

		// Check our nonce and make sure it's correct.
		check_ajax_referer( 'wpum_fields_editor_nonce', 'wpum_editor_nonce' );

		// Abort if something isn't right.
		if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
			$return = array(
				'message' => __( 'Error.', 'wpum' ),
			);
			wp_send_json_error( $return );
		}

		// Prepare the array.
		$fields = $_POST['items'];

		if( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$args = array(
					'field_order' => (int) $field['priority'],
				);
				WPUM()->fields->update( (int) $field['field_id'], $args );
			}
		} else {
			$return = array(
				'message' => __( 'Error.', 'wpum' ),
			);
			wp_send_json_error( $return );
		}

		// Send message
		$return = array(
			'message'   => __( 'Fields order successfully updated.', 'wpum' ),
		);

		wp_send_json_success( $return );

	}

}

new WPUM_Ajax_Handler;
