<?php
/**
 * Registers the checkboxes type field.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Field_Type_Checkboxes Class
 *
 * @since 1.2.0
 */
class WPUM_Field_Type_Checkboxes extends WPUM_Field_Type {

	/**
	 * Constructor for the field type
	 *
	 * @since 1.2.0
	*/
	public function __construct() {

		// DO NOT DELETE
		parent::__construct();

		// Label of this field type
		$this->name             = _x( 'Checkboxes', 'field type name', 'wpum' );
		// Field type name
		$this->type             = 'checkboxes';
		// Class of this field
		$this->class            = __CLASS__;
		// Set registration
		$this->set_registration = true;
		// Set requirement
		$this->set_requirement  = true;
    // Add repeater to this field type.
		$this->has_repeater     = true;
		// Set editing ability.
		$this->set_editing      = true;

	}

	/**
	 * Modify the output of the field on the fronted profile.
	 *
	 * @since 1.2.0
	 * @param  string $value the value of the field.
	 * @param  object $field field details.
	 * @return string        the formatted field value.
	 */
	public static function output_html( $value, $field ) {

		if( is_array( $value ) ) {

			$field_value = maybe_unserialize( $value );
			$field_value = implode( ', ', $field_value );
			return $field_value;

		}

		return $value;

	}

}

new WPUM_Field_Type_Checkboxes;
