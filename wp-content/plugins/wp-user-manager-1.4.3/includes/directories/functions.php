<?php
/**
 * Handles directories functions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wpum_directory_sort_dropdown' ) ) :
	/**
	 * Display or retrieve the HTML dropdown list of sorting options.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string|array $args Optional. Override default arguments.
	 * @return string HTML.
	 */
	function wpum_directory_sort_dropdown( $args = '' ) {

		$defaults = array(
			'exclude'  => '',
			'selected' => '',
			'class'    => 'wpum-dropdown-sort',
		);

		$args = wp_parse_args( $args, $defaults );

		// Get css class
		$class = $args['class'];

		// Get options
		$sorting_methods = wpum_get_directory_sorting_methods();

		// Exclude methods if any
		if ( ! empty( $args['exclude'] ) ) {

			// Check if it's only one value that we need to exclude
			if ( is_string( $args['exclude'] ) ) :

				unset( $sorting_methods[ $args['exclude'] ] );

			// Check if there's more than one value to exclude
			elseif ( is_array( $args['exclude'] ) ) :

				foreach ( $args['exclude'] as $method_to_exclude ) {
					unset( $sorting_methods[ $method_to_exclude ] );
				}

			endif;

		}

		$sorting_methods = apply_filters( 'wpum_sort_dropdown_methods', $sorting_methods, $args );
		$selected        = isset( $_GET['sort'] ) ? $selected = $_GET['sort']: $selected = $args['selected'];

		$output = "<select name='wpum-dropdown' id='wpum-dropdown' class='$class'>\n";

		foreach ( $sorting_methods as $value => $label ) {

			$method_url = add_query_arg( array(
				'sort'        => $value,
				'search_user' => isset( $_GET['search_user'] ) ? sanitize_text_field( $_GET['search_user'] ): false,
			), get_permalink() );

			if ( $selected == $value ) {
				$output .= "\t<option value='" . esc_url( $method_url ) . "' selected='selected' >$label</option>\n";
			} else {
				$output .= "\t<option value='" . esc_url( $method_url ) . "'>$label</option>\n";
			}

		}

		$output .= "</select>\n";

		return $output;

	}
endif;

if ( ! function_exists( 'wpum_directory_results_amount_dropdown' ) ) :
	/**
	 * Display or retrieve the HTML dropdown list of results amount options.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string|array $args Optional. Override default arguments.
	 * @return string HTML content.
	 */
	function wpum_directory_results_amount_dropdown( $args = '' ) {

		$defaults = array(
			'exclude' => '',
			'class'   => 'wpum-results-dropdown-sort',
		);

		$args = wp_parse_args( $args, $defaults );

		// Get css class
		$class = $args['class'];

		// Get options
		$results_options = wpum_get_directory_amount_options();
		$selected        = isset( $_GET['amount'] ) ? $_GET['amount']: false;

		$output = "<select name='wpum-amount-dropdown' id='wpum-amount-dropdown' class='$class'>\n";

		foreach ( $results_options as $value => $label ) {

			$result_url = add_query_arg( array(
				'amount'      => $value,
				'search_user' => isset( $_GET['search_user'] ) ? sanitize_text_field( $_GET['search_user'] ): false,
			), get_permalink() );

			if ( $selected == $value ) {
				$output .= "\t<option value='" . esc_url( $result_url ) . "' selected='selected' >$label</option>\n";
			} else {
				$output .= "\t<option value='" . esc_url( $result_url ) . "'>$label</option>\n";
			}
		}

		$output .= "</select>\n";

		return $output;

	}
endif;

/**
 * Available templates for user directories.
 * Developers can use the filter wpum_get_directory_templates
 * to customize add and remove templates.
 *
 * @since 1.0.0
 * @return array $templates list of the templates - the key is the file name without extension.
 */
function wpum_get_directory_templates() {

	// Default template has empty key.
	$templates = array( '' => __( 'Default template', 'wpum' ) );

	return apply_filters( 'wpum_get_directory_templates', $templates );

}

/**
 * Checks whether a directory has a search form.
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return bool
 */
function wpum_directory_has_search_form( $directory_id = 0 ) {

	if ( get_post_meta( $directory_id, 'display_search_form', true ) )
		return true;

	return false;

}

/**
 * Checks whether a directory has a custom template.
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return bool|string Boolean if no custom template is assigned.
 *                     Returns template name excluding extension if has custom template.
 */
function wpum_directory_has_custom_template( $directory_id = 0 ) {

	$template = false;

	$custom_template = get_post_meta( $directory_id, 'directory_template', true );

	if ( ! empty( $custom_template ) ) {
		$template = $custom_template;
	}

	return $template;
}

/**
 * Grabs the amount of users to display into the directory.
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return string amount of users to display.
 */
function wpum_directory_profiles_per_page( $directory_id = 0 ) {

	$amount = get_post_meta( $directory_id, 'profiles_per_page', true );

	if ( empty( $amount ) )
		return 10;

	return $amount;

}

/**
 * Grabs user roles for the directory if any.
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return array|bool list of roles or false if no roles is set.
 */
function wpum_directory_get_roles( $directory_id = 0 ) {

	$roles = get_post_meta( $directory_id, 'directory_roles', true );

	if ( empty( $roles ) || !is_array( $roles ) )
		return false;

	return $roles;

}

/**
 * Grabs excluded users for the directory if any.
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return array|bool list of excluded users ids or false if no ids are set.
 */
function wpum_directory_get_excluded_users( $directory_id = 0 ) {

	$users = get_post_meta( $directory_id, 'excluded_ids', true );

	// Process string to array
	if ( $users ) {

		$list = explode( ',', $users );
		$users = $list;

	} else {
		$users = false;
	}

	return $users;

}

/**
 * Grabs the currently selected sorting method for the directory
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return string|bool sorting method or false if no ids are set.
 */
function wpum_directory_get_sorting_method( $directory_id = 0 ) {

	$method = get_post_meta( $directory_id, 'default_sorting_method', true );

	return $method;

}

/**
 * Produces the list of sorting methods.
 * Developers can use the filter wpum_get_directory_sorting_methods
 * to add new methods.
 *
 * @since 1.0.0
 * @return array list sorting methods.
 */
function wpum_get_directory_sorting_methods() {

	// Let's add the default sorting methods
	$methods = array(
		'user_nicename' => __( 'By nickname', 'wpum' ),
		'newest'        => __( 'Newest users first', 'wpum' ),
		'oldest'        => __( 'Oldest users first', 'wpum' ),
		'name'          => __( 'First name', 'wpum' ),
		'last_name'     => __( 'Last Name', 'wpum' )
	);

	return apply_filters( 'wpum_get_directory_sorting_methods', $methods );

}

/**
 * Produces the list of results per page options.
 * Developers can use the filter wpum_get_directory_amount_options
 * to add new options.
 *
 * @since 1.0.0
 * @return array list amount options.
 */
function wpum_get_directory_amount_options() {

	// Let's add the default results options
	$amounts = array(
		''   => '',
		'10' => '10',
		'15' => '15',
		'20' => '20',
	);

	return apply_filters( 'wpum_get_directory_amount_options', $amounts );

}

/**
 * Checks whether a directory has the sorting form enabled.
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return bool
 */
function wpum_directory_display_sorter( $directory_id = 0 ) {

	if ( get_post_meta( $directory_id, 'display_sorter', true ) )
		return true;

	return false;

}

/**
 * Checks whether a directory has the amount results form enabled.
 *
 * @since 1.0.0
 * @param int     $directory_id the ID of a directory custom post type, post.
 * @return bool
 */
function wpum_directory_display_amount_sorter( $directory_id = 0 ) {

	if ( get_post_meta( $directory_id, 'display_amount', true ) )
		return true;

	return false;

}
