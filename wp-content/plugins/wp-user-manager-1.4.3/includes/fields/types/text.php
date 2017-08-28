<?php
/**
 * Registers the text type field.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Field_Type_Text Class
 *
 * @since 1.0.0
 */
class WPUM_Field_Type_Text extends WPUM_Field_Type {

	/**
	 * Constructor for the field type
	 *
	 * @since 1.0.0
 	 */
	public function __construct() {

		// DO NOT DELETE
		parent::__construct();

		// Label of this field type
		$this->name             = _x( 'Text', 'field type name', 'wpum' );
		// Field type name
		$this->type             = 'text';
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

}

new WPUM_Field_Type_Text;
