<?php
/**
 * Handles update of the new fields when the account is registered.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUMCF_Register Class.
 * Handles update of the new fields when the account is registered.
 *
 * @since 1.0.0
 */
class WPUMCF_Register {

  /**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_filter( 'wpum_form_field', array( $this, 'set_default' ), 10, 2 );
		add_action( 'wpum/form/register/success', array( $this, 'save_profile' ), 10, 2 );

	}

	/**
	 * Set default selected value within registration form.
	 *
	 * @since 1.0.0
	 * @param array $field_attr field attributes.
	 * @param array $field_args the array of the field details from the database.
	 * @return array       field settings.
	 */
	public function set_default( $field_attr, $field_options ) {

		if( ! is_page( wpum_get_core_page_id( 'register' ) ) )
			return $field_attr;

		if( $field_attr[ 'type' ] == 'checkbox' ) {

			$default = wpum_get_serialized_field_option( $field_options, 'checked' );

			if( $default )
				$field_attr['value'] = true;

		}

		if( $field_attr[ 'type' ] == 'checkboxes' || $field_attr[ 'type' ] == 'multiselect' ) {

			$selectable_options = wpum_get_serialized_field_option( $field_options, 'selectable' );
			$get_default_option = wp_list_filter( $selectable_options, array( 'set-as-default' => 1 ) );

			$default_selected = array();

			foreach ( $get_default_option as $key => $value ) {
				$default_selected[ $value['option-value'] ] = $value['option-value'];
			}

			$field_attr['value'] = $default_selected;

		} else if( $field_attr[ 'type' ] == 'radio' || $field_attr[ 'type' ] == 'select' ) {

			$selectable_options = wpum_get_serialized_field_option( $field_options, 'selectable' );
			$get_default_option = wp_list_filter( $selectable_options, array( 'set-as-default' => 1 ) );
			$get_default_option = array_values( $get_default_option );

			if( array_key_exists( 0 , $get_default_option ) )
				$field_attr['value'] = $get_default_option[0]['option-value'];

		}

		return $field_attr;

	}

	/**
	 * Save registration fields content into the user profile upon successful registration.
	 *
	 * @since 1.0.0
	 * @param  int $user_id user id number.
	 * @param  array $values  list of submitted fields.
	 * @return void
	 */
	public function save_profile( $user_id, $values ) {

		$custom_fields = array();

		// Retrieve all custom fields.
		// Custom fields will always have the prefix wpum_ when created through the addon.
		foreach ( $values['register'] as $key => $value ) {
			if ( strpos( $key, 'wpum_' ) === 0 ) {
				$custom_fields[ $key ] = $value;
			}
		}

		// At this point the fields have already been sanitized so we do not need to do it again.
		foreach ( $custom_fields as $meta_key => $custom_field_value ) {

			// Verify if custom field is a file type field.
			if ( strpos( $meta_key, 'wpum_file_' ) === 0 ) {

				// If the field is empty we skip saving this.
				if( empty( $custom_field_value ) )
					continue;

			}

			update_user_meta( $user_id, $meta_key, maybe_serialize( $custom_field_value ) );

		}

	}

}

new WPUMCF_Register;
