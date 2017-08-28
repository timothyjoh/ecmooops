<?php
/**
 * Uninstall WPUM
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load WPUM file
include_once( 'wp-user-manager.php' );

global $wpdb;

// Delete post type contents
$wpum_post_types = array( 'wpum_directory' );

foreach ( $wpum_post_types as $post_type ) {
	$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
	if ( $items ) {
		foreach ( $items as $item ) {
			wp_delete_post( $item, true);
		}
	}
}

// Delete created pages
$wpum_pages = array( 'login_page', 'password_recovery_page', 'registration_page', 'account_page', 'profile_page' );
foreach ( $wpum_pages as $p ) {
	$page = wpum_get_option( $p, false );
	if ( $page ) {
		wp_delete_post( $page, false );
	}
}

// Delete options
delete_option( 'wpum_settings' );
delete_option( 'wpum_emails' );
delete_option( 'wpum_permalink' );
delete_option( 'wpum_custom_fields' );
delete_option( 'wpum_version' );
delete_option( 'wpum_version_upgraded_from' );
delete_transient( '_wpum_activation_redirect' );
delete_option( 'wpum_activation_date' );

// Remove all database tables
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wpum_fields" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wpum_field_groups" );