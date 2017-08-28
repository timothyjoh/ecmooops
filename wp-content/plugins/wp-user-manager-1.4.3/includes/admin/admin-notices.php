<?php
/**
 * Admin Messages
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @since 1.0
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_admin_messages() {

	global $wpum_options;
	$screen = get_current_screen();

	if (  isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true && !wpum_get_option('custom_passwords') && wpum_get_option('password_strength') ) {
		add_settings_error( 'wpum-notices', 'custom-passwords-disabled', __( 'You have enabled the "Minimum Password Strength" option, the "Users custom passwords" is currently disabled and must be enabled for custom passwords to work.', 'wpum' ), 'error' );
	}

	if (  isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true && !wpum_get_option('custom_passwords') && wpum_get_option('login_after_registration') ) {
		add_settings_error( 'wpum-notices', 'custom-passwords-disabled', __( 'Error: the option "Login after registration" can only work when the option "Users custom passwords" is enabled too.', 'wpum' ), 'error' );
	}

	if (  isset( $_GET['emails-updated'] ) && $_GET['emails-updated'] == true ) {
		add_settings_error( 'wpum-notices', 'emails-updated', __( 'Email successfully updated.', 'wpum' ), 'updated' );
	}

	// Display Errors in plugin settings page
	if ( $screen->base == 'users_page_wpum-settings' ) {

		// Display error if no core page is setup
		if ( !wpum_get_option('login_page') || !wpum_get_option('password_recovery_page') || !wpum_get_option('registration_page') || !wpum_get_option('account_page') || !wpum_get_option('profile_page') ) {
			add_settings_error( 'wpum-notices', 'page-missing', __('One or more WPUM pages are not configured.', 'wpum') . ' ' . sprintf( __('<a href="%s" class="button-primary">Click here to setup your pages</a>', 'wpum'), admin_url( 'users.php?page=wpum-settings&tab=general&wpum_action=install_pages' ) ), 'error' );
		}

		// Display error if wrong permalinks
		if( get_option('permalink_structure' ) == '' ) {
			add_settings_error( 'wpum-notices', 'permalink-wrong', sprintf(__( 'You must <a href="%s">change your permalinks</a> to anything else other than "default" for profiles to work.', 'wpum' ), admin_url( 'options-permalink.php' ) ), 'error' );
		}

		if( isset( $_GET['setup_done'] ) && $_GET['setup_done'] == 'true' ) {
			add_settings_error( 'wpum-notices', 'pages-updated', __( 'Pages setup completed.', 'wpum' ), 'updated' );
		}

		if( isset( $_GET['message'] ) && $_GET['message'] == 'fields_fixed' ) :
			add_settings_error( 'wpum-notices', 'fields_fixed', __( 'Fields successfully fixed.', 'wpum' ), 'updated' );
		endif;

	}

	// Verify if upload folder is writable
	if( isset( $_GET['wpum_action'] ) && $_GET['wpum_action'] == 'check_folder_permission' ) {

		$upload_dir = wp_upload_dir();
		if( ! wp_is_writable( $upload_dir['path'] ) ) :
			add_settings_error( 'wpum-notices', 'permission-error', sprintf( __( 'Your uploads folder in "%s" is not writable. <br/>Avatar uploads will not work, please adjust folder permission.<br/><br/> <a href="%s" class="button" target="_blank">Read More</a>', 'wpum' ), $upload_dir['basedir'], 'http://www.wpbeginner.com/wp-tutorials/how-to-fix-image-upload-issue-in-wordpress/' ), 'error' );
		else :
			add_settings_error( 'wpum-notices', 'permission-success', sprintf( __( 'No issues detected.', 'wpum' ), admin_url( 'users.php?page=wpum-settings&tab=profile' ) ), 'updated notice is-dismissible' );
		endif;
	}

	// messages for the groups and fields pages
	if( $screen->base == 'users_page_wpum-profile-fields' ) {

		if( isset( $_GET['message'] ) && $_GET['message'] == 'group_success' ) :
			add_settings_error( 'wpum-notices', 'group-updated', __( 'Field group successfully updated.', 'wpum' ), 'updated' );
		endif;

		if( isset( $_GET['message'] ) && $_GET['message'] == 'group_delete_success' ) :
			add_settings_error( 'wpum-notices', 'group-deleted', __( 'Field group successfully deleted.', 'wpum' ), 'updated' );
		endif;

		if( isset( $_GET['message'] ) && $_GET['message'] == 'field_saved' ) :
			add_settings_error( 'wpum-notices', 'field-saved', __( 'Field successfully updated.', 'wpum' ), 'updated' );
		endif;

	}

	// messages for tools page
	if( $screen->base == 'users_page_wpum-tools' ) {

		if( isset( $_GET['message'] ) && $_GET['message'] == 'settings_imported' ) :
			add_settings_error( 'wpum-notices', 'settings-imported', __( 'Settings successfully imported.', 'wpum' ), 'updated' );
		endif;

	}

	// Message for successful test mail.
	if (  isset( $_GET['message'] ) && $_GET['message'] == 'test_mail' ) {
		add_settings_error( 'wpum-notices', 'test-mail-success', esc_html__( 'Test email successfully delivered.', 'wpum' ), 'updated' );
	}

	settings_errors( 'wpum-notices' );

}
add_action( 'admin_notices', 'wpum_admin_messages' );
