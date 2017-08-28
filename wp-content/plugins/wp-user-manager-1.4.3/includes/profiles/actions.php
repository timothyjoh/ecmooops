<?php
/**
 * User profiles actions.
 * Holds templating actions to display various components of the layout.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

/**
 * Force 404 error if user or tabs do not exist.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_profile_force_404_error() {

	// Bail if not on the profile page
	if( !is_page( wpum_get_core_page_id('profile') ) )
		return;

	// Bail if viewing single profile only and not another user profile
	if( !wpum_is_single_profile() )
		return;

	// Trigger if tab is set and does not exist
	if( wpum_get_current_profile_tab() !== null && !wpum_profile_tab_exists( wpum_get_current_profile_tab() ) )
		wpum_trigger_404();

	// Trigger if profile is set and does not exist
	if( wpum_is_single_profile() && !wpum_user_exists( wpum_is_single_profile(), get_option( 'wpum_permalink' ) ) )
		wpum_trigger_404();

}
add_action( 'template_redirect', 'wpum_profile_force_404_error' );

/**
 * Display user name in profile.php template.
 *
 * @since 1.0.0
 * @param object $user_data holds WP_User object
 * @access public
 * @return void
 */
function wpum_profile_show_user_name( $user_data ) {

	$output = '<div class="wpum-user-display-name">';
		$output .= '<a href="'. wpum_get_user_profile_url( $user_data ) .'">'. esc_attr( $user_data->display_name ) .'</a>';

		// Show edit account only when viewing own profile
		if( $user_data->ID == get_current_user_id() )
			$output .= '<small><a href="'. wpum_get_core_page_url('account') .'" class="wpum-profile-account-edit">'. __(' (Edit Account)', 'wpum') .'</a></small>';

	$output .= '</div>';

	echo $output;

}
add_action( 'wpum_main_profile_details', 'wpum_profile_show_user_name', 10 );

/**
 * Display user description in profile.php template.
 *
 * @since 1.0.0
 * @param object $user_data holds WP_User object
 * @access public
 * @return void
 */
function wpum_profile_show_user_description( $user_data ) {

	$output = '<div class="wpum-user-description">';
		$output .= wpautop( esc_attr( get_user_meta( $user_data->ID, 'description', true) ), true );
	$output .= '</div>';

	echo $output;

}
add_action( 'wpum_main_profile_details', 'wpum_profile_show_user_description', 10 );

/**
 * Display user name in profile.php template.
 *
 * @since 1.0.0
 * @param object $user_data holds WP_User object
 * @access public
 * @return void
 */
function wpum_profile_show_user_links( $user_data ) {

	$output = get_wpum_template( 'profile/profile-links.php', array( 'user_data' => $user_data ) );

	echo $output;

}
add_action( 'wpum_secondary_profile_details', 'wpum_profile_show_user_links', 10 );
