<?php
/**
 * Installation Functions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types,
 * flushing rewrite rules and also populates the settings fields.
 * After successful install, the user is redirected to the WPUM Welcome screen.
 *
 * @since 1.0
 * @global $wpum_options
 * @global $wp_version
 * @return void
 */
function wpum_install() {

	global $wpum_options, $wp_version;

	// Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
	if ( version_compare(PHP_VERSION, '5.3', '<') ) {
		deactivate_plugins( plugin_basename( WPUM_PLUGIN_FILE ) );
		wp_die( sprintf( __( 'This plugin requires a minimum PHP Version 5.3 to be installed on your host. <a href="%s" target="_blank">Click here to read how you can update your PHP version</a>.', 'wpum'), 'http://www.wpupdatephp.com/contact-host/' ) . '<br/><br/>' . '<small><a href="'.admin_url().'">'.__('Back to your website.', 'wpum').'</a></small>' );
	}

	// Install default pages
	wpum_generate_pages();

	// Register emails if the option doesn't exist already.
	if( ! get_option( 'wpum_emails' ) ) {
		wpum_register_emails();
	}

	// Let's set some default options
	wpum_update_option( 'enable_honeypot', true ); // enable antispam honeypot by default.
	wpum_update_option( 'email_template', 'none' ); // set no template as default.
	wpum_update_option( 'from_email', get_option( 'admin_email' ) ); // set admin email as default.
	wpum_update_option( 'from_name', get_option( 'blogname' ) ); // set blogname as default mail from.
	wpum_update_option( 'guests_can_view_profiles', true );
	wpum_update_option( 'members_can_view_profiles', true );
	update_option( 'users_can_register', true ); // Enable registrations.
	update_option( 'wpum_permalink', 'user_id' ); // Set default user permalinks

	// Clear the permalinks
	flush_rewrite_rules();

	// Create groups table and 1st group
	wpum_install_groups();

	// Create fields table and primary fields
	wpum_install_fields();

	// Store plugin installation date
    add_option( 'wpum_activation_date', strtotime( "now" ) );

	// Add Upgraded From Option
	$current_version = get_option( 'wpum_version' );
	if ( $current_version ) {
		update_option( 'wpum_version_upgraded_from', $current_version );
	}

	// Update current version
	update_option( 'wpum_version', WPUM_VERSION );
	update_option( 'wpum_did_121_update', true );

	// Add the transient to redirect
	set_transient( '_wpum_activation_redirect', true, 30 );

}
register_activation_hook( WPUM_PLUGIN_FILE, 'wpum_install' );
