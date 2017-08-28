<?php

/**
 * Abstract WPUM_Form class.
 *
 * @abstract
 * @author      Mike Jolley
 * @author      Alessandro Tesoro
 */
abstract class WPUM_Form {

	protected static $fields;
	protected static $action;
	protected static $errors = array();
	protected static $confirmations = array();

	/**
	 * Add an error.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array of errors.
	 */
	public static function add_error( $error ) {
		self::$errors[] = $error;
	}

	/**
	 * Show errors.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function show_errors() {
		foreach ( self::$errors as $error )
			echo '<div class="wpum-message error"><p class="the-message">' . $error . '</p></div>';
	}

	/**
	 * Add a confirmation message.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $array of confirmation messages
	 */
	public static function add_confirmation( $confirmation ) {
		self::$confirmations[] = $confirmation;
	}

	/**
	 * Show confirmation messages.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $void
	 */
	public static function show_confirmations() {
		foreach ( self::$confirmations as $confirmation )
			echo '<div class="wpum-message success"><p class="the-message">' . $confirmation . '</p></div>';
	}

	/**
	 * Get action
	 *
	 * @return string
	 */
	public static function get_action() {
		return self::$action;
	}

	/**
	 * Get submitted fields values.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $values array of data from the fields.
	 */
	public static function get_posted_fields() {

		$values = array();

		foreach ( self::$fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {

				// Get the type
				$field_type = str_replace( '-', '_', $field['type'] );
				// Get the type object
				$field_object = wpum_get_field_type_object( $field_type );

				if ( method_exists( $field_object->class, "sanitization" ) ) {
					$values[ $group_key ][ $key ] = call_user_func( $field_object->class . "::sanitization", $key, $field );
				} elseif ( method_exists( __CLASS__, "get_posted_{$field_type}_field" ) ) {
					$values[ $group_key ][ $key ] = call_user_func( __CLASS__ . "::get_posted_{$field_type}_field", $key, $field );
				} else {
					$values[ $group_key ][ $key ] = self::get_posted_field( $key, $field );
				}

				// Set fields value
				self::$fields[ $group_key ][ $key ]['value'] = $values[ $group_key ][ $key ];
			}
		}

		return $values;

	}

	/**
	 * Goes through fields and sanitizes them.
	 *
	 * @access public
	 * @param array|string $value The array or string to be sanitized.
	 * @since 1.0.0
	 * @return array|string $value The sanitized array (or string from the callback)
	 */
	public static function sanitize_posted_field( $value ) {
		// Decode URLs
		if ( is_string( $value ) && ( strstr( $value, 'http:' ) || strstr( $value, 'https:' ) ) ) {
			$value = urldecode( $value );
		}

		// Santize value
		$value = is_array( $value ) ? array_map( array( __CLASS__, 'sanitize_posted_field' ), $value ) : sanitize_text_field( stripslashes( trim( $value ) ) );

		return $value;
	}

	/**
	 * Get the value of submitted fields.
	 *
	 * @access protected
	 * @param  string $key
	 * @param  array $field
	 * @since 1.0.0
	 * @return array|string content of the submitted field
	 */
	protected static function get_posted_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? self::sanitize_posted_field( $_POST[ $key ] ) : '';
	}

	/**
	 * Get the value of a posted multiselect field
	 * @param  string $key
	 * @param  array $field
	 * @return array
	 */
	protected static function get_posted_multiselect_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', $_POST[ $key ] ) : array();
	}

	/**
	 * Get the value of a posted textarea field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected static function get_posted_textarea_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? wp_kses_post( trim( stripslashes( $_POST[ $key ] ) ) ) : '';
	}

	/**
	 * Get the value of a posted textarea field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected static function get_posted_wp_editor_field( $key, $field ) {
		return self::get_posted_textarea_field( $key, $field );
	}

	/**
	 * Get the value of a posted file field
	 *
	 * @since 1.0.0
	 * @param  string $key
	 * @param  array $field
	 * @return string|array
	 */
	protected static function get_posted_file_field( $key, $field ) {
		$file = wpum_trigger_upload_file( $key, $field );

		if ( ! $file ) {
			$file = '';
		}

		return $file;
	}

	/**
	 * Validate the posted fields
	 *
	 * @return bool on success, WP_ERROR on failure
	 */
	protected static function validate_fields( $values, $form ) {

		foreach ( self::$fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {

				// Validate required fields.
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) && 'file' !== $field['type'] ) {
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field', 'wpum' ), $field['label'] ) );
				}

				if ( $field['required'] && $field['type'] == 'file' && $field['meta'] == 'user_avatar' ) {

					$avatar = get_user_meta( get_current_user_id() , 'current_user_avatar', true );

					if( empty( $avatar ) && empty( $values[ $group_key ][ $key ] ) ) {
						return new WP_Error( 'validation-error', sprintf( __( '%s is a required field', 'wpum' ), $field['label'] ) );
					}

				}

				// Validate email fields.
				if ( 'email' === $field['type'] && ! is_email( $values[ $group_key ][ $key ] ) ) {
					return new WP_Error( 'email-validation-error', sprintf( __( 'Please enter a valid email address for the "%s" field.', 'wpum' ), $field['label'] ) );
				}

				// Validate file fields.
				if ( 'file' === $field['type'] ) {

					if( is_wp_error( $values[ $group_key ][ $key ] ) )
						return new WP_Error( 'validation-error', $values[ $group_key ][ $key ]->get_error_message() );

				}

			}
		}

		return apply_filters( "wpum/form/validate={$form}", true, self::$fields, $values );

	}

	/**
	 * get_fields function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param mixed $key
	 * @return array
	 */
	public static function get_fields( $key ) {
		if ( empty( self::$fields[ $key ] ) )
			return array();

		$fields = self::$fields[ $key ];

		//uasort( $fields, __CLASS__ . '::priority_cmp' );

		return $fields;
	}

	/**
	 * priority_cmp function.
	 *
	 * @access private
	 * @since 1.0.0
	 * @param mixed $a
	 * @param mixed $b
	 * @return void
	 */
	public static function priority_cmp( $a, $b ) {
	    if ( $a['priority'] == $b['priority'] )
	        return 0;
	    return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
	}
}
