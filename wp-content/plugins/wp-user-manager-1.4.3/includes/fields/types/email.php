<?php
/**
 * Registers the email type field.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Field_Type_Email Class
 *
 * @since 1.0.0
 */
class WPUM_Field_Type_Email extends WPUM_Field_Type {

	/**
	 * Constructor for the field type
	 *
	 * @since 1.0.0
 	 */
	public function __construct() {

		// DO NOT DELETE
		parent::__construct();

		// Label of this field type
		$this->name             = _x( 'Email', 'field type name', 'wpum' );
		// Field type name
		$this->type             = 'email';
		// Class of this field
		$this->class            = __CLASS__;
		// Set registration
		$this->set_registration = true;
		// Set requirement
		$this->set_requirement  = true;
		// Set read only state.
		$this->set_read_only    = true;
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

		$mail_output = '<a href="mailto:' . antispambot( $value ) .'">' . antispambot( $value ) . '</a>';

		return $mail_output;

	}

}

new WPUM_Field_Type_Email;
