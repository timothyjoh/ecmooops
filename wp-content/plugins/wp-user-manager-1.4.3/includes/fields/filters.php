<?php
/**
 * Handles filters to work with the fields input/output.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adjust the output of the user website field to display an html anchor tag.
 * Because this field is stored into the database as a text field,
 * the default output would be just text, we use the filter within the loop,
 * to change it's output to a link.
 *
 * @param  string $value the value of field.
 * @param  string $field  field object.
 * @return string        output of this field.
 * @since 1.2.0
 */
function wpum_adjust_website_meta_output( $value, $field ) {

	if( $field->meta == 'user_url' ) {
		$value = '<a href="'.esc_url( $value ).'" rel="nofollow">'. esc_url( $value ) .'</a>';
	}

	return $value;

}
add_filter( 'wpum_get_the_field_value', 'wpum_adjust_website_meta_output', 10, 2 );

/**
 * Adjust the output of the first name/last name field to display a full name if the option is enabled.
 * If the option is enabled into the first name field, WPUM will display the first name + last name,
 * if the option is enabled into the last name field, WPUM will display the last name + first name.
 *
 * @param  string $value the value of field.
 * @param  string $field  field object.
 * @return string        output of this field.
 * @since 1.2.0
 */
function wpum_adjust_name_meta_output( $value, $field ) {

	if( wpum_is_single_profile() ) {

		if( $field->meta == 'first_name' && wpum_get_field_option( $field->id, 'display_full_name' ) ) {
			$value = $value . ' ' . wpum_get_user_lname( wpum_get_displayed_user_id() );
		} elseif( $field->meta == 'last_name' && wpum_get_field_option( $field->id, 'display_full_name' ) ) {
			$value = $value . ' ' . wpum_get_user_fname( wpum_get_displayed_user_id() );
		}

	}

	return $value;

}
add_filter( 'wpum_get_the_field_value', 'wpum_adjust_name_meta_output', 10, 2 );
