<?php
/**
 * Directories Filters
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Modify user query to set roles and trigger search form results.
 *
 * @since 1.3.1
 * @param array $args WP_User_Query args.
 * @param string $directory_id id number of the directory.
 * @return array
 */
function wpum_directory_pre_set_roles_search( $args, $directory_id ) {

	if( isset( $_GET['search_user'] ) ) {

		$search_term    = sanitize_text_field( $_GET['search_user'] );
		$args['offset'] = null;

		$roles = wpum_directory_get_roles( $directory_id );
		if( $roles )
			$args['role'] = implode( ",", $roles );

		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => 'first_name',
				'value'   => $search_term,
				'compare' => 'LIKE'
			),
			array(
				'key'     => 'last_name',
				'value'   => $search_term,
				'compare' => 'LIKE'
			)
		);

	} else {

		$roles = wpum_directory_get_roles( $directory_id );

		// Execute only if there are roles.
		if( $roles ) {

		  global $wpdb;
		  $blog_id = get_current_blog_id();

		  $meta_query = array(
		    'key' => $wpdb->get_blog_prefix( $blog_id ) . 'capabilities',
		    'value' => '"(' . implode( '|', array_map( 'preg_quote', $roles ) ) . ')"',
		    'compare' => 'REGEXP'
		  );

		  $args['meta_query'] = array( $meta_query );

		}

	}

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_roles_search', 10, 2 );

/**
 * Modify the WP_User_Query on the directory page.
 * Check whether the directory should be excluding
 * specific users by id.
 *
 * @since 1.0.0
 * @param array $args WP_User_Query args.
 * @param string $directory_id id number of the directory.
 * @return array
 */
function wpum_directory_pre_set_exclude_users( $args, $directory_id ) {

	$users = wpum_directory_get_excluded_users( $directory_id );

	if( is_array( $users ) )
		$args['exclude'] = $users;

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_exclude_users', 11, 2 );

/**
 * Modify the WP_User_Query on the directory page.
 * Specify a custom sorting order.
 *
 * @since 1.0.0
 * @param array $args WP_User_Query args.
 * @param string $directory_id id number of the directory.
 * @return array
 */
function wpum_directory_pre_set_order( $args, $directory_id ) {

	// Get selected sorting method
	$sorting_method = get_post_meta( $directory_id, 'default_sorting_method', true );

	// Check whether a sorting method is set from frontend
	if( isset( $_GET['sort'] ) && array_key_exists( $_GET['sort'] , wpum_get_directory_sorting_methods() ) )
		$sorting_method = sanitize_key( $_GET['sort'] );

	switch ( $sorting_method ) {
		case 'user_nicename':
			$args['orderby'] = 'user_nicename';
			break;
		case 'newest':
			$args['orderby'] = 'registered';
			$args['order'] = 'DESC';
			break;
		case 'oldest':
			$args['orderby'] = 'registered';
			break;
		case 'name':
			$args['meta_key'] = 'first_name';
			$args['orderby'] = 'meta_value';
			$args['order'] = 'ASC';
			break;
		case 'last_name':
			$args['meta_key'] = 'last_name';
			$args['orderby'] = 'meta_value';
			$args['order'] = 'ASC';
			break;
	}

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_order', 12, 2 );

/**
 * Modify the search query to include some custom fields.
 *
 * @since 1.3.0
 * @param  object $query the original query.
 * @return void
 */
function wpum_directory_search_query( $query ) {

	global $wpdb;

	$display_name = sanitize_text_field( $_GET['search_user'] );

	// Search by users first name.
	$query->query_from .= " JOIN {$wpdb->usermeta} fname ON fname.user_id = {$wpdb->users}.ID AND fname.meta_key = 'first_name'";
	// Search by users last name.
	$query->query_from .= " JOIN {$wpdb->usermeta} lname ON lname.user_id = {$wpdb->users}.ID AND lname.meta_key = 'last_name'";

	// The fields to include in the search.
	$search_by = array( 'user_login', 'user_email', 'fname.meta_value', 'lname.meta_value' );

	$query->query_where = 'WHERE 1=1' . $query->get_search_sql( $display_name, $search_by, 'both' );

}
