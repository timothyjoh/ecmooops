<?php
/**
 * Helper functions to work with custom fields.
 *
 * @package     wpum-custom-fields
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Verify whether a field is a custom field.
 *
 * @param  int $field_id id number of a field.
 * @return boolean
 * @since 1.0.0
 */
function wpumcf_field_is_custom_field( $field_id ) {

	return (bool) wpum_get_field_option( $field_id, 'custom_field' );

}

/**
 * Insert an array into another array before/after a certain key.
 *
 * @since 1.0.0
 * @param array $array The initial array
 * @param array $pairs The array to insert
 * @param string $key The certain key
 * @param string $position Wether to insert the array before or after the key
 * @return array
 */
function wpumcf_array_insert( $array, $pairs, $key, $position = 'after' ) {

	$key_pos = array_search( $key, array_keys( $array ) );

	if ( 'after' == $position )

		$key_pos++;

	if ( false !== $key_pos ) {

		$result = array_slice( $array, 0, $key_pos );
		$result = array_merge( $result, $pairs );
		$result = array_merge( $result, array_slice( $array, $key_pos ) );

	}

	else {

		$result = array_merge( $array, $pairs );

	}

	return $result;

}

/**
 * Parse selectable field option stored into the database into a proper array that can be used into the fields input template.
 *
 * @since 1.0.0
 * @param  array $options options to parse.
 * @return array          parsed options.
 */
function wpumcf_parse_selectable_options( $options ) {

	$selectable = array();

	if( is_array( $options ) && ! empty( $options ) ) {
		foreach ( $options as $key => $option ) {
			$selectable[ $option['option-value'] ] = $option['option-title'];
		}
	}

	return $selectable;

}
