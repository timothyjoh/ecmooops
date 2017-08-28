<?php
/**
 * Admin Pages handler
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Users menu and assigns their
 * links to global variables
 *
 * @since 1.0.0
 * @global $wpum_settings_page
 * @return void
 */
function wpum_add_options_link() {

	global $wpum_settings_page;

	$wpum_settings_page = add_users_page( __( 'WPUM Profile Fields Editor', 'wpum' ), __( 'Profile Fields', 'wpum' ), 'manage_options', 'wpum-profile-fields', 'WPUM_Fields_Editor::editor_page' );
	$wpum_settings_page = add_users_page( __( 'WPUM Edit Field', 'wpum' ), __( 'Edit Field', 'wpum' ), 'manage_options', 'wpum-edit-field', 'WPUM_Fields_Editor::edit_field_page' );
	$wpum_settings_page = add_users_page( __('WP User Manager Settings', 'wpum'), __('WPUM Settings', 'wpum'), 'manage_options', 'wpum-settings', 'wpum_options_page' );
	$wpum_settings_page = add_users_page( __('WP User Manager Email Editor', 'wpum'), __('WPUM Email Editor', 'wpum'), 'manage_options', 'wpum-edit-email', 'WPUM_Emails_Editor::get_emails_editor_page' );
	$wpum_settings_page = add_users_page( __('WP User Manager Tools', 'wpum'), __('WPUM Tools', 'wpum'), 'manage_options', 'wpum-tools', 'wpum_tools_page' );
	$wpum_settings_page = add_users_page( __('WP User Manager Addons', 'wpum'), __('WPUM Addons', 'wpum'), 'manage_options', 'plugin-install.php?tab=wpum_addons');

	add_action( 'admin_head', 'wpum_hide_admin_pages' );

}
add_action( 'admin_menu', 'wpum_add_options_link', 10 );

/**
 * Removes admin menu links that are masked.
 * @return      void
 */
function wpum_hide_admin_pages() {
	remove_submenu_page( 'users.php', 'wpum-edit-email' );
	remove_submenu_page( 'users.php', 'wpum-edit-field' );
}
