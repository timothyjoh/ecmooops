<?php
/**
 * Handles the function to work with the fields.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets list of registered field types.
 *
 * @since 1.0.0
 * @param  bool $exclude_primary whether or not to exclude fields that should not be modified.
 * @param  string $type the category to retrieve
 * @return array                  list of fields split by categories.
 */
function wpum_get_field_types( $exclude_primary = true, $type = 'basic' ) {

	$field_types = apply_filters( 'wpum/field/types', array() );
	$field_types = $field_types[ $type ];

	if( $exclude_primary ) {
		foreach ( $field_types as $type => $name ) {
			if( $type == 'avatar' || $type == 'display_name' || $type == 'nickname' || $type == 'username' )
				unset( $field_types[ $type ] );
		}
	}

	return apply_filters( 'wpum_get_field_types', $field_types );

}

/**
 * Gets list of registered field classes.
 *
 * @since 1.0.0
 * @return array $field_classes - list of field types and class names.
 */
function wpum_get_field_classes() {
	return apply_filters( 'wpum/field/types/classes', array() );
}

/**
 * Verify if a field type exists
 *
 * @since 1.0.0
 * @return bool - true | false.
 */
function wpum_field_type_exists( $type = '' ) {

	$exists = false;

	$all_types = wpum_get_field_classes();

	if( array_key_exists( $type , $all_types ) )
		$exists = true;

	return $exists;

}

/**
 * Get the class of a field type and returns the object.
 *
 * @since 1.0.0
 * @param  $type type of field
 * @return object - class.
 */
function wpum_get_field_type_object( $type = '' ) {

	$object = null;

	$field_types = wpum_get_field_classes();

	if( !empty( $type ) && wpum_field_type_exists( $type ) ) {
		$class = $field_types[ $type ];
		$object = new $class;
	}

	return $object;

}

/**
 * Get the options of a field type.
 *
 * @since 1.0.0
 * @param  $type type of field
 * @return array - list of options.
 */
function wpum_get_field_type_options( $type = '' ) {

	$options = array();
	$field_types = wpum_get_field_classes();

	if( ! empty( $type ) && wpum_field_type_exists( $type ) ) {
		$class = $field_types[ $type ];
		$options = call_user_func( "$class::options" );
	}

	return $options;

}

/**
 * Get the list of registration fields formatted into an array.
 * The format of the array is used by the forms.
 *
 * @since 1.0.0
 * @return array - list of fields.
 */
function wpum_get_registration_fields() {

	// Get fields from the database
	$primary_group = WPUM()->field_groups->get_group_by('primary');

	$args = array(
		'id'           => $primary_group->id,
		'array'        => true,
		'registration' => true,
		'number'       => -1,
		'orderby'      => 'field_order',
		'order'        => 'ASC'
	);

	$data = WPUM()->fields->get_by_group( $args );

	// Manipulate fields list into a list formatted for the forms API.
	$fields = array();

	// Loop through the found fields
	foreach ( $data as $key => $field ) {

		// Adjust field type parameter if no field type template is defined.
		switch ( $field['type'] ) {
			case 'username':
				$field['type'] = 'text';
				break;
			case 'avatar':
				$field['type'] = 'file';
				break;
			case 'url':
				$field['type'] = 'text';
				break;
		}

		$fields[ $field['meta'] ] = apply_filters( 'wpum_form_field', array(
			'priority'    => $field['field_order'],
			'label'       => stripslashes( $field['name'] ),
			'type'        => $field['type'],
			'meta'        => $field['meta'],
			'required'    => $field['is_required'],
			'description' => stripslashes( $field['description'] ),
		), $field['options'] );

	}

	// Remove password field if not enabled
	if( ! wpum_get_option('custom_passwords') )
		unset( $fields['password'] );

	// Remove the user avatar field if not enabled
	if( ! wpum_get_option('custom_avatars') )
		unset( $fields['user_avatar'] );

	return apply_filters( 'wpum_get_registration_fields', $fields );

}

/**
 * Get the list of account fields formatted into an array.
 * The format of the array is used by the forms.
 *
 * @since 1.0.0
 * @return array - list of fields.
 */
function wpum_get_account_fields() {

	// Get fields from the database
	$primary_group = WPUM()->field_groups->get_group_by('primary');

	$args = array(
		'id'           => $primary_group->id,
		'array'        => true,
		'number'       => -1,
		'orderby'      => 'field_order',
		'order'        => 'ASC'
	);

	$data = WPUM()->fields->get_by_group( $args );

	// Manipulate fields list into a list formatted for the forms API.
	$fields = array();

	// Loop through the found fields
	foreach ( $data as $key => $field ) {

		// Adjust field type parameter if no field type template is defined.
		switch ( $field['type'] ) {
			case 'username':
			case 'nickname':
			case 'url':
				$field['type'] = 'text';
				break;
			case 'display_name':
				$field['type'] = 'select';
				break;
			case 'avatar':
				$field['type'] = 'file';
				break;
		}

		$fields[ $field['meta'] ] = apply_filters( 'wpum_form_field', array(
			'priority'    => $field['field_order'],
			'label'       => stripslashes( $field['name'] ),
			'type'        => $field['type'],
			'meta'        => $field['meta'],
			'required'    => $field['is_required'],
			'description' => stripslashes( $field['description'] ),
			'read_only'   => wpum_is_field_read_only( $field ),
			'placeholder' => apply_filters( 'wpum_profile_field_placeholder', null, $field ),
			'options'     => apply_filters( 'wpum_profile_field_options', null, $field ),
			'value'       => apply_filters( 'wpum_profile_field_value', null, $field )
		), $field['options'] );

		// Remove all fields that cannot be edited.
		// This isn't the best way to do it and will be changed at a later point.
		if( wpum_get_serialized_field_option( $field['options'] , 'can_edit' ) == 'hidden' ) {
			unset( $fields[ $field['meta'] ] );
		}

	}

	// Remove password field from here.
	unset( $fields['password'] );

	// The username cannot be changed, let's remove that field since it's useless.
	unset( $fields['username'] );

	// Remove the user avatar field if not enabled.
	if( ! wpum_get_option( 'custom_avatars' ) )
		unset( $fields['user_avatar'] );

	return apply_filters( 'wpum_get_account_fields', $fields );

}

/**
 * Displays the html of a field within a form.
 *
 * @since 1.0.0
 * @return mixed
 */
function wpum_get_field_input_html( $key, $field ) {

	if( wpum_field_type_exists( $field['type'] ) ) {

		$object = wpum_get_field_type_object( $field['type'] );

		if ( method_exists( $object->class, "input_html" ) ) {
			echo call_user_func( $object->class . "::input_html", $key, $field );
		} else {
			get_wpum_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) );
		}

	} else {
		echo __( 'This field type has no output', 'wpum' );
	}

}

/**
 * Get value of a user custom field given the user id and field meta key.
 *
 * @since 1.2.0
 * @param  string $user_id    the id number of the user.
 * @param  string $field_meta the metakey of the field
 * @return mixed
 */
function wpum_get_field_value( $user_id, $field_meta ) {

	$field_data = false;

	if( empty( $user_id ) || ! is_int( $user_id ) ) {
		return false;
	}

	switch ( $field_meta ) {
		case 'user_email':
			$field_data = wpum_get_user_email( $user_id );
			break;
		case 'username':
			$field_data = wpum_get_user_username( $user_id );
			break;
		case 'display_name':
			$field_data = wpum_get_user_displayname( $user_id );
			break;
		case 'user_url':
			$field_data = wpum_get_user_website( $user_id );
			break;
		default:
			$field_data = get_user_meta( $user_id, $field_meta, $single = true );
			break;
	}

	return maybe_unserialize( $field_data );

}

/**
 * Retrieve field groups, populated with fields and associated user data.
 *
 * @since 1.2.0
 * @param  array $args arguments for the query.
 * @return array       list of groups and fields with associated data.
 */
function wpum_get_field_groups( $args = array() ) {

	if( $args['field_group_id'] && is_int( $args['field_group_id'] ) ) {
		$groups = array();
		$groups[] = WPUM()->field_groups->get_group_by( 'id', absint( $args['field_group_id'] ), true );
	} else {
		$groups = WPUM()->field_groups->get_groups( $args );
	}

	// Merge fields for each group
	if( ! empty( $groups ) ) {
		foreach ( $groups as $key => $group ) {

			$get_fields_by_group_args = array(
				'id'             => absint( $group['id'] ),
				'orderby'        => 'field_order',
				'order'          => 'ASC',
				'array'          => true,
				'number'         => array_key_exists( 'number_fields' , $args ) ? $args['number_fields']:   false,
				'exclude_fields' => array_key_exists( 'exclude_fields' , $args ) ? $args['exclude_fields']: false
			);

			$fields = WPUM()->fields->get_by_group( $get_fields_by_group_args );

			if( empty( $fields ) && $args['hide_empty_groups'] === true ) {
				unset( $groups[ $key ] );
			} else {

				foreach ( $fields as $field_key => $field ) {

					if( $field['meta'] == 'password' || $field['meta'] == 'user_avatar' ) {
						unset( $fields[ $field_key ] );
					} else {
						$fields[ $field_key ]['value'] = wpum_get_field_value( $args['user_id'], $field['meta'] );
						$fields[ $field_key ]          = wpum_array_to_object( $fields[ $field_key ] );
					}

				}

				$fields = array_values( $fields );

				$groups[ $key ][ 'fields' ] = $fields;

			}

		}
	}

	return apply_filters( 'wpum_get_field_groups', $groups, $args );

}

/**
 * Use this function to start a loop of profile fields.
 *
 * @since 1.2.0
 * @global $wpum_profile_fields
 * @param  string  $args arguments to create the loop.
 * @return boolean       whether there's any group found.
 */
function wpum_has_profile_fields( $args = '' ) {

	global $wpum_profile_fields;

	$defaults = array(
		'user_id'           => absint( wpum_get_displayed_user_id() ),
		'field_group_id'    => false,
		'number'            => false,
		'number_fields'     => false,
		'hide_empty_groups' => true,
		'exclude_groups'    => false,
		'exclude_fields'    => false
	);

	/**
	 * Filters the query arguments to retrieve fields from the database.
	 *
	 * @since 1.2.0
	 * @return array.
	 */
	$args = apply_filters( 'wpum_has_profile_fields_query', wp_parse_args( $args, $defaults ) );

	$wpum_profile_fields = new WPUM_Fields_Data_Template( $args );

	return apply_filters( 'wpum_has_profile_fields', $wpum_profile_fields->has_groups(), $wpum_profile_fields );

}

/**
 * Setup the profile fields loop.
 *
 * @since 1.2.0
 * @global $wpum_profile_fields
 * @return bool
 */
function wpum_profile_field_groups() {

	global $wpum_profile_fields;
	return $wpum_profile_fields->profile_groups();

}

/**
 * Setup the current field group within the loop.
 *
 * @since 1.2.0
 * @global $wpum_profile_fields
 * @return array the current group within the loop.
 */
function wpum_the_profile_field_group() {

	global $wpum_profile_fields;
	return $wpum_profile_fields->the_profile_group();

}

/**
 * Return the group id number of a group within the loop.
 *
 * @since 1.2.0
 * @global $wpum_fields_group
 * @return string the current group id.
 */
function wpum_get_field_group_id() {

	global $wpum_fields_group;
	return apply_filters( 'wpum_get_field_group_id', $wpum_fields_group->id );

}

/**
 * Echo the group id number of a group within the loop.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_the_field_group_id() {
	echo wpum_get_field_group_id();
}

/**
 * Return the name of a group within the loop.
 *
 * @since 1.2.0
 * @global $wpum_fields_group
 * @return string
 */
function wpum_get_field_group_name() {

	global $wpum_fields_group;
	return apply_filters( 'wpum_get_field_group_name', $wpum_fields_group->name );

}

/**
 * Echo the name of a group within the loop.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_the_field_group_name() {
	echo wpum_get_field_group_name();
}

/**
 * Return the slug of a group within the loop.
 *
 * @since 1.2.0
 * @global $wpum_fields_group
 * @return string
 */
function wpum_get_field_group_slug() {

	global $wpum_fields_group;
	return apply_filters( 'wpum_get_field_group_slug', sanitize_title( $wpum_fields_group->name ) );

}

/**
 * Echo the slug of a group within the loop.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_the_field_group_slug() {
	echo wpum_get_field_group_slug();
}

/**
 * Retrieve the description of the group within the loop.
 *
 * @since 1.2.0
 * @global $wpum_fields_group
 * @return string
 */
function wpum_get_field_group_description() {

	global $wpum_fields_group;
	return apply_filters( 'wpum_get_field_group_description', $wpum_fields_group->description );

}

/**
 * Echo the description of a field group within the loop.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_the_field_group_description() {
	echo wpum_get_field_group_description();
}

/**
 * Whether the current group within the loop has fields.
 *
 * @since 1.2.0
 * @global $wpum_profile_fields
 * @return array the current group fields within the loop.
 */
function wpum_field_group_has_fields() {

	global $wpum_profile_fields;
	return $wpum_profile_fields->has_fields();

}

/**
 * Start the fields loop for the current group.
 *
 * @since 1.2.0
 * @global $wpum_profile_fields
 * @return mixed
 */
function wpum_profile_fields() {

	global $wpum_profile_fields;
	return $wpum_profile_fields->profile_fields();

}

/**
 * Setup global variable for field within the loop.
 *
 * @since 1.2.0
 * @global $wpum_profile_fields
 * @return void
 */
function wpum_the_profile_field() {

	global $wpum_profile_fields;
	return $wpum_profile_fields->the_profile_field();

}

/**
 * Retrieve the current field id within the loop.
 *
 * @since 1.2.0
 * @global $wpum_field
 * @return int field id
 */
function wpum_get_field_id() {

	global $wpum_field;
	return $wpum_field->id;

}

/**
 * Echo the current field id within a loop.
 *
 * @since 1.2.0
 * @see wpum_get_the_field_id()
 * @return void
 */
function wpum_the_field_id() {
	echo (int) wpum_get_field_id();
}

/**
 * Retrieve the current field name within the loop.
 *
 * @since 1.2.0
 * @global $wpum_field
 * @return string field name
 */
function wpum_get_field_name() {

	global $wpum_field;
	return apply_filters( 'wpum_get_field_name', $wpum_field->name, $wpum_field->id );

}

/**
 * Echo the current field name within a loop.
 *
 * @since 1.2.0
 * @see wpum_get_field_name()
 * @return void
 */
function wpum_the_field_name() {
	echo wpum_get_field_name();
}

/**
 * Retrieve the current field description within a loop.
 *
 * @since 1.2.0
 * @global $wpum_field
 * @return string description of the field.
 */
function wpum_get_field_description() {

	global $wpum_field;
	return apply_filters( 'wpum_get_field_description', $wpum_field->description, $wpum_field->id );

}

/**
 * Echo the current field description within a loop.
 *
 * @since 1.2.0
 * @see wpum_get_field_description()
 * @return void
 */
function wpum_the_field_description() {
	echo wpum_get_field_description();
}

/**
 * Verify whether the current field within the loop is required.
 *
 * @since 1.2.0
 * @global $wpum_field
 * @return bool
 */
function wpum_is_field_required() {

	global $wpum_field;
	return apply_filters( 'wpum_is_field_required', $wpum_field->is_required, $wpum_field->id );

}

/**
 * Verify whether the current field within the loop is a registration field.
 *
 * @since 1.2.0
 * @global $wpum_field
 * @return bool
 */
function wpum_is_registration_field() {

	global $wpum_field;
	return apply_filters( 'wpum_is_registration_field', $wpum_field->show_on_registration, $wpum_field->id );

}

/**
 * Retrieve the current field type within a loop.
 *
 * @since 1.2.0
 * @global $wpum_field
 * @return string the type of the field.
 */
function wpum_get_field_type() {

	global $wpum_field;
	return $wpum_field->type;

}

/**
 * Retrieve the classes for the field element as an array.
 *
 * @param  string $class custom class to add to the field.
 * @return array        list of all classes.
 * @since 1.2.0
 */
function wpum_get_field_css_class( $class = false ) {

	global $wpum_profile_fields;

	$classes = array();

	if ( ! empty( $class ) ) {
		$classes[] = sanitize_title( esc_attr( $class ) );
	}

	// Add a class with the field id.
	$classes[] = 'field_' . $wpum_profile_fields->field->id;

	// Add a class with the field name.
	$classes[] = 'field_' . sanitize_title( $wpum_profile_fields->field->name );

	// Add a class with the field type.
	$classes[] = 'field_type_' . sanitize_title( $wpum_profile_fields->field->type );

	// Sanitize all classes.
	$classes = array_map( 'esc_attr', $classes );

	return apply_filters( 'wpum_field_css_class', $classes, $class );

}

/**
 * Display the css classes applied to a field.
 *
 * @param  string $class custom class to add to the fields.
 * @return void
 * @since 1.2.0
 */
function wpum_the_field_css_class( $class = false ) {
	echo join( ' ', wpum_get_field_css_class( $class ) );
}

/**
 * Verify if a field has user data.
 *
 * @return boolean
 * @since 1.2.0
 */
function wpum_field_has_data() {

	global $wpum_profile_fields;
	return $wpum_profile_fields->field_has_data;

}

/**
 * Retrieve the value of a field within the loop.
 *
 * @return mixed
 * @since 1.2.0
 */
function wpum_get_the_field_value() {

	global $wpum_field;

	$wpum_field->value = wpum_format_profile_field_value( $wpum_field->value, $wpum_field );

	/**
	 * Filters the profile field value.
	 *
	 * @param string|array $value Value for the profile field.
	 * @param string $type  Type for the profile field.
	 * @param string $meta  the meta of the profile field.
	 * @param int    $id    ID for the profile field.
	 * @since 1.2.0
	 */
	return apply_filters( 'wpum_get_the_field_value', $wpum_field->value, $wpum_field );

}

/**
 * Format the profile field value.
 * This function simply checks whether an output method for the field class is available.
 * If no output method is available, we return the original string.
 *
 * @param  mixed $value the value of a field.
 * @param string $type the field type string.
 * @return string        the value of a field.
 * @since 1.2.0
 */
function wpum_format_profile_field_value( $value, $field ) {

	$type_object = wpum_get_field_type_object( $field->type );

	if ( method_exists( $type_object->class, "output_html" ) ) {

		$value = call_user_func( $type_object->class . "::output_html", $value, $field );

	}

	return $value;

}

/**
 * Output the value of a field within the loop.
 *
 * @return void
 * @since 1.2.0
 */
function wpum_the_field_value() {
	echo wpum_get_the_field_value();
}

/**
 * Get all stored options of a custom field.
 *
 * @param  int $field_id the id number of the field.
 * @return array           list of the options.
 * @since 1.2.0
 */
function wpum_get_field_options( $field_id ) {

	$options = array();

	$field_options = WPUM()->fields->get_column_by( 'options', 'id', $field_id );
	$field_options = maybe_unserialize( $field_options );

	if ( is_array( $field_options ) ) {
		$options = $field_options; }

	return $options;

}

/**
 * Helper function to update an option of a field.
 *
 * @param  int    $field_id the field id number.
 * @param  string $option   the option that needs to be updated.
 * @param  string $value    the new value of the option.
 * @return mixed
 * @since 1.2.0
 */
function wpum_update_field_option( $field_id, $option, $value ) {

	$all_options = wpum_get_field_options( $field_id );
	$option      = trim( $option );

	if ( empty( $option ) ) {
		return false; }

	// Sanitize the value being saved.
	$value = is_array( $value ) ? $value : sanitize_text_field( $value );
	$value = maybe_serialize( $value );

	if ( is_array( $all_options ) ) {
		$all_options[ $option ] = $value;
		$all_options = maybe_serialize( $all_options );
		WPUM()->fields->update( $field_id, array( 'options' => $all_options ) );
	}

}

/**
 * Helper function to delete an option of a field.
 *
 * @param  int    $field_id the field id number.
 * @param  string $option   the option that needs to be removed.
 * @return mixed
 * @since 1.2.0
 */
function wpum_delete_field_option( $field_id, $option ) {

	$all_options = wpum_get_field_options( $field_id );
	$option      = trim( $option );

	if ( empty( $option ) ) {
		return false; }

	if( is_array( $all_options ) && ! empty( $all_options ) ) {

		$all_options = maybe_unserialize( $all_options );

		if( array_key_exists( $option , $all_options ) ) {
			unset( $all_options[ $option ] );

			if ( is_array( $all_options ) ) {
				WPUM()->fields->update( $field_id, array( 'options' => maybe_serialize( $all_options ) ) );
			}

		}

	}

}

/**
 * Retrieve a single field option from the database.
 *
 * @param  int    $field_id the id of the field.
 * @param  string $option   the name of the option to retrieve.
 * @return mixed           the option value.
 * @since 1.2.0
 */
function wpum_get_field_option( $field_id, $option ) {

	$option_value = false;
	$option       = trim( $option );

	if ( empty( $option ) || empty( $field_id ) ) { return false; }

	$all_options = wpum_get_field_options( $field_id );

	if ( array_key_exists( $option, $all_options ) ) {
		$option_value = maybe_unserialize( $all_options[ $option ] );
	}

	return $option_value;

}

/**
 * Retrieve a single field option from a field.
 *
 * This function assumes the field options have already been retrieved from the database,
 * therefore we do not need to make a query again.
 *
 * @param  array $field_options  serialized options from the database.
 * @param  string $option the option we need to extract.
 * @return string         value of the extracted option.
 */
function wpum_get_serialized_field_option( $field_options, $option ) {

	$option_value = false;
	$option       = trim( $option );

	if ( empty( $option ) ) { return false; }

	$retrieved_options = maybe_unserialize( $field_options );

	if( is_array( $retrieved_options ) && array_key_exists( $option, $retrieved_options ) ) {
		$option_value = maybe_unserialize( $retrieved_options[ $option ] );
	}

	return $option_value;

}

/**
 * Get the list of fields formatted into an array.
 * The format of the array is used by the forms.
 *
 * @since 1.2.0
 * @param string $group_id the id number of the group.
 * @return array - list of fields.
 */
function wpum_get_group_fields_for_form( $group_id ) {

	$args = array(
		'id'           => $group_id,
		'array'        => true,
		'number'       => -1,
		'orderby'      => 'field_order',
		'order'        => 'ASC'
	);

	$data = WPUM()->fields->get_by_group( $args );

	// Manipulate fields list into a list formatted for the forms API.
	$fields = array();

	// Loop through the found fields.
	foreach ( $data as $key => $field ) {

		switch ( $field['type'] ) {
			case 'url':
				$field['type'] = 'text';
				break;
		}

		$fields[ $field['meta'] ] = apply_filters( 'wpum_form_field', array(
			'priority'    => $field['field_order'],
			'label'       => $field['name'],
			'type'        => $field['type'],
			'meta'        => $field['meta'],
			'required'    => $field['is_required'],
			'description' => $field['description'],
			'value'       => maybe_unserialize( get_user_meta( get_current_user_id(), $field['meta'], true ) )
		), $field['options'] );

	}

	return apply_filters( 'wpum_get_group_fields_for_form', $fields, $group_id );

}

/**
 * Retrieve the available options for field visiblity.
 *
 * @since 1.2.0
 * @return array The list of options.
 */
function wpum_get_field_visibility_settings() {

	$options = array(
		'public' => esc_html__( 'Publicly visible', 'wpum' ),
		'hidden' => esc_html__( 'Hidden', 'wpum' )
	);

	return apply_filters( 'wpum_get_field_visibility_settings', $options );

}

/**
 * Verify if the field has been set as read only.
 *
 * @since 1.3.0
 * @param  array  $field field details.
 * @return boolean
 */
function wpum_is_field_read_only( $field ) {

	$read_only = false;

	if( is_array( $field ) && array_key_exists( 'options' , $field ) && array_key_exists( 'type' , $field ) ) {

		switch ( $field['type'] ) {
			case 'text':
			case 'textarea':
			case 'email':
			case 'nickname':
			case 'number':
			case 'url':
				$read_only = wpum_get_serialized_field_option( $field['options'], 'read_only' );
				break;
		}

	}

	return $read_only;

}

/**
 * Retrieve the available options for field editing.
 *
 * @since 1.3.0
 * @return array The list of options.
 */
function wpum_get_field_editing_settings() {

	$options = array(
		'public' => esc_html__( 'Publicly editable', 'wpum' ),
		'hidden' => esc_html__( 'Hidden (admins only)', 'wpum' )
	);

	return apply_filters( 'wpum_get_field_editing_settings', $options );

}

/**
 * Retrieve a list of available date formats for the datepicker field.
 *
 * @since 1.4.1
 * @return array
 */
function wpum_get_datepicker_date_formats() {

	$formats = array(
		'mm/dd/yy' => 'mm/dd/yy',
		'dd/mm/yy' => 'dd/mm/yy',
		'dd-mm-yy' => 'dd-mm-yy',
		'dd.mm.yy' => 'dd.mm.yy',
		'yy/mm/dd' => 'yy/mm/dd',
		'yy-mm-dd' => 'yy-mm-dd',
		'yy.mm.dd' => 'yy.mm.dd'
	);

	return apply_filters( 'wpum_get_datepicker_date_formats', $formats );

}
