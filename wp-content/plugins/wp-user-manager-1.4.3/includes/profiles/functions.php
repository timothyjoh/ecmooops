<?php
/**
 * User profiles functions.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

if ( ! function_exists( 'wpum_get_user_by_data' ) ) :
/**
 * Returns a wp user object containg user's data.
 * The user is retrieved based on the current permalink structure.
 * This function is currently used only through the wpum_profile shortcode.
 * If no data is set, returns currently logged in user data.
 *
 * @since 1.0.0
 * @access public
 * @return object
 */
function wpum_get_user_by_data() {

	$user_data = null;
	$permalink_structure = get_option( 'wpum_permalink', 'user_id' );
	$who = ( get_query_var( 'user' ) ) ? get_query_var( 'user' ) : null;

	// Checks we are on the profile page
	if ( is_page( wpum_get_core_page_id( 'profile' ) ) ) {

		// Verify the user isset
		if ( $who ) {

			switch ( $permalink_structure ) {
			case 'user_id':
				$user_data = get_user_by( 'id', intval( get_query_var( 'user' ) ) );
				break;
			case 'username':
				$user_data = get_user_by( 'login', esc_attr( urldecode( get_query_var( 'user' ) ) ) );
				break;
			case 'nickname':

				// WP_User_Query arguments
				$args = array (
					'search'         => esc_attr( get_query_var( 'user' ) ),
					'search_columns' => array( 'user_nicename' ),
				);

				// The User Query
				$user_query = new WP_User_Query( $args );
				$user_query = $user_query->get_results();

				$user_data = $user_query[0];

				break;
			default:
				$user_data = apply_filters( "wpum_get_user_by_data", $permalink_structure, $who );
				break;
			}

		} else {

			$user_data = get_user_by( 'id', get_current_user_id() );

		}

	}

	return $user_data;

}
endif;

if ( ! function_exists( 'wpum_get_user_profile_url' ) ) :
/**
 * Returns the URL of the single user profile page.
 *
 * @since 1.0.0
 * @access public
 * @param object  $user_data WP_User Object.
 * @see https://codex.wordpress.org/Function_Reference/get_user_by
 * @return string
 */
function wpum_get_user_profile_url( $user_data ) {

	$url = null;

	$permalink_structure = get_option( 'wpum_permalink', 'user_id' );
	$base_url = wpum_get_core_page_url( 'profile' );

	if ( empty( $base_url ) || !is_object( $user_data ) )
		return;

	// Define the method needed to grab the user url.
	switch ( $permalink_structure ) {
	case 'user_id':
		$url = $base_url . $user_data->ID;
		break;
	case 'username':
		$url = $base_url . $user_data->user_login;
		break;
	case 'nickname':
		$url = $base_url . $user_data->user_nicename;
		break;
	default:
		$url = apply_filters( 'wpum_get_user_profile_url', $user_data, $permalink_structure );
		break;
	}

	return esc_url( $url );

}
endif;

if ( ! function_exists( 'wpum_get_user_profile_tabs' ) ) :
/**
 * Returns registered tabs for the user profile page.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function wpum_get_user_profile_tabs() {

	$tabs = array();

	$tabs['about'] = array(
		'id'       => 'profile_details',
		'title'    => __( 'Overview', 'wpum' ),
		'slug'     => 'about',
	);

	$tabs['posts'] = array(
		'id'       => 'profile_posts',
		'title'    => __( 'Posts', 'wpum' ),
		'slug'     => 'posts',
	);

	$tabs['comments'] = array(
		'id'       => 'profile_comments',
		'title'    => __( 'Comments', 'wpum' ),
		'slug'     => 'comments',
	);

	// Remove tabs if they're not active
	if ( !wpum_get_option( 'profile_posts' ) ) // remove posts tab
		unset( $tabs['posts'] );

	if ( !wpum_get_option( 'profile_comments' ) ) // Remove comments tab
		unset( $tabs['comments'] );

	return apply_filters( 'wpum_get_user_profile_tabs', $tabs );

}
endif;

if ( ! function_exists( 'wpum_profile_avatar' ) ) :
/**
 * Display user avatar.
 *
 * @since 1.0.0
 * @access public
 * @param int     $user_data WP_User Object
 * @param bool    $hyperlink whether to link the image to the profile's page.
 * @param int     $size      the size of the avatar.
 * @return void
 */
function wpum_profile_avatar( $user_data, $hyperlink = true, $size = 128 ) {

	$output = '';
	$avatar = get_avatar( $user_data->ID , $size );

	if ( $hyperlink ) {
		$output .= '<a href="'. wpum_get_user_profile_url( $user_data ) .'" class="wpum-profile-link">' . $avatar . '</a>';
	} else {
		$output = $avatar;
	}

	return $output;

}
endif;

if ( ! function_exists( 'wpum_profile_display_name' ) ) :
/**
 * Display user "display name".
 *
 * @since 1.0.0
 * @access public
 * @param int     $user_data WP_User Object
 * @param bool    $hyperlink whether to link the image to the profile's page.
 * @return void
 */
function wpum_profile_display_name( $user_data, $hyperlink = true ) {

	$output = $user_data->display_name;

	if ( $hyperlink ) {
		$output = '<a href="'. wpum_get_user_profile_url( $user_data ) .'" class="wpum-profile-link">' . $user_data->display_name . '</a>';
	}

	return $output;

}
endif;

if ( ! function_exists( 'wpum_current_user_overview' ) ) :
/**
 * Display overview of the current user profile.
 *
 * @since 1.0.0
 * @access public
 * @return void.
 */
function wpum_current_user_overview() {

	$current_user = wp_get_current_user();
	get_wpum_template( 'user-overview.php', array( 'current_user' => $current_user ) );

}
endif;

/**
 * Checks if guests can view profiles.
 *
 * @since 1.0.0
 * @return bool
 */
function wpum_guests_can_view_profiles() {

	$pass = false;

	if ( wpum_get_option( 'guests_can_view_profiles' ) )
		$pass = true;

	return $pass;
}

/**
 * Checks if members can view profiles.
 *
 * @since 1.0.0
 * @return bool
 */
function wpum_members_can_view_profiles() {

	$pass = false;

	if ( wpum_get_option( 'members_can_view_profiles' ) )
		$pass = true;

	return $pass;

}

/**
 * Checks if viewing single profile page.
 *
 * @since 1.0.0
 * @return bool
 */
function wpum_is_single_profile() {

	$who = ( get_query_var( 'user' ) ) ? get_query_var( 'user' ) : false;

	return $who;

}

/**
 * Checks the current active tab (if any).
 *
 * @since 1.0.0
 * @return bool|string
 */
function wpum_get_current_profile_tab() {

	$tab = ( get_query_var( 'tab' ) ) ? get_query_var( 'tab' ) : null;
	return $tab;

}

/**
 * Checks the given profile tab is registered.
 *
 * @since 1.0.0
 * @param string  $tab the key value of the array in wpum_get_user_profile_tabs() must match slug
 * @return bool
 */
function wpum_profile_tab_exists( $tab ) {

	$exists = false;

	if ( array_key_exists( $tab, wpum_get_user_profile_tabs() ) )
		$exists = true;

	return $exists;

}

/**
 * Returns the permalink of a profile tab.
 *
 * @since 1.0.0
 * @return bool|string
 */
function wpum_get_profile_tab_permalink( $user_data, $tab ) {

	$tab_slug = $tab['slug'];
	$base_link = wpum_get_user_profile_url( $user_data );

	$tab_permalink = $base_link . '/' . $tab_slug;

	return $tab_permalink;
}

/**
 * Verify whether the user is viewing it's own profile page.
 *
 * @uses wpum_is_single_profile()
 * @since 1.1.0
 * @return boolean
 */
function wpum_is_own_profile() {

	// Set access flag.
	$is_own_profile = false;
	$check = false;

	// Get permalink structure.
	$structure = get_option( 'wpum_permalink', 'user_id' );

	// Get current user.
	$user = wp_get_current_user();

	// Get current profile.
	$current_profile = wpum_is_single_profile();

	if( $current_profile ) {

		switch ( $structure ) {
		  case 'user_id':
		    $check = $current_profile == $user->ID ? true : false;
		    break;
		  case 'username':
		    $check = $current_profile == $user->user_login ? true : false;
		    break;
		  case 'nickname':
		    $check = $current_profile == $user->user_nicename ? true : false;
		    break;
		}

		if( $check ) {
			$is_own_profile = true;
		}

	}

	return $is_own_profile;

}

/**
 * Checks if profiles are available.
 *
 * @uses wpum_is_single_profile
 * @uses wpum_guests_can_view_profiles
 * @uses wpum_members_can_view_profiles
 * @since 1.0.0
 * @return bool
 */
function wpum_can_access_profile() {

	$pass = true;

	// Check if not logged in and on profile page - no given user
	if ( ! is_user_logged_in() && ! wpum_is_single_profile() ) {
		// Display error message
		$args = array(
			'id'   => 'wpum-guests-disabled',
			'type' => 'notice',
			'text' => sprintf( __( 'This content is available to members only. Please <a href="%s">login</a> or <a href="%s">register</a> to view this area.', 'wpum' ), wpum_get_core_page_url( 'login' ), wpum_get_core_page_url( 'register' )  )
		);
		wpum_message( $args );
		$pass = false;
	}

	// Block guests on single profile page if option disabled
	if ( ! is_user_logged_in() && wpum_is_single_profile() && ! wpum_guests_can_view_profiles() ) {
		// Display error message
		$args = array(
			'id'   => 'wpum-guests-disabled',
			'type' => 'notice',
			'text' => sprintf( __( 'This content is available to members only. Please <a href="%s">login</a> or <a href="%s">register</a> to view this area.', 'wpum' ), wpum_get_core_page_url( 'login' ), wpum_get_core_page_url( 'register' )  )
		);
		wpum_message( $args );
		$pass = false;
	}

	// Block members on single profile page if option disabled
	if ( is_user_logged_in() && wpum_is_single_profile() && ! wpum_members_can_view_profiles() && ! wpum_is_own_profile() ) {
		// Display error message
		$args = array(
			'id'   => 'wpum-no-access',
			'type' => 'notice',
			'text' => __( 'You are not authorized to access this area.', 'wpum' )
		);
		wpum_message( $args );
		$pass = false;

	}

	return apply_filters( 'wpum_can_access_profile', $pass );

}
