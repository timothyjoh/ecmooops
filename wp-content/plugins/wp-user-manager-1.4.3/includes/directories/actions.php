<?php
/**
 * Directories Actions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a search form on top of a directory.
 *
 * @param array $directory_args directory arguments.
 * @return void
 * @since 1.2.0
 */
function wpum_directory_add_search_form( $directory_args ) {

	if( $directory_args['search_form'] ) {
		get_wpum_template( "directory/search-form.php", array(
			'directory_args'  =>  $directory_args
		) );
	}

}
add_action( 'wpum_before_user_directory', 'wpum_directory_add_search_form' );

/**
 * Adds total number of users found on top of the directory.
 *
 * @since 1.0.0
 * @param array   $directory_args directory arguments.
 * @return void
 */
function wpum_directory_topbar( $directory_args ) {

	get_wpum_template( "directory/top-bar.php", array(
		'users_found'  => $directory_args['users_found'],
		'directory_id' => $directory_args['directory_id']
	) );

}
add_action( 'wpum_before_user_directory', 'wpum_directory_topbar' );

/**
 * Adds pagination at the bottom of the user directory.
 *
 * @since 1.0.0
 * @access public
 * @param array   $directory_args directory arguments.
 * @see
 * @return void
 */
function wpum_user_directory_pagination( $directory_args ) {

	echo '<div class="wpum-directory-pagination">';

	$big          = 9999999;
	$search_for   = array( $big, '#038;' );
	$replace_with = array( '%#%', '&' );

	echo paginate_links( array(
			'base'      => str_replace( $search_for, $replace_with, esc_url( get_pagenum_link( $big ) ) ),
			'format'    => isset( $_GET['sort'] ) || isset( $_GET['amount'] ) || isset( $_GET['search_user'] ) ? '&paged=%#%' : '?paged=%#%',
			'current'   => $directory_args['paged'],
			'total'     => $directory_args['total_pages'],
			'prev_text' => __( 'Previous page', 'wpum' ),
			'next_text' => __( 'Next page', 'wpum' )
		)
	);

	echo '</div>';

}
add_action( 'wpum_after_user_directory', 'wpum_user_directory_pagination' );
