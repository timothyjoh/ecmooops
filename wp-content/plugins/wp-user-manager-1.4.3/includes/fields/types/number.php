<?php
/**
 * Registers the number type field.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Field_Type_Number Class
 *
 * @since 1.0.0
 */
class WPUM_Field_Type_Number extends WPUM_Field_Type {

	/**
	 * Constructor for the field type
	 *
	 * @since 1.0.0
	*/
	public function __construct() {

		// DO NOT DELETE.
		parent::__construct();

		// Label of this field type.
		$this->name             = _x( 'Number', 'field type name', 'wpum' );
		// Field type name
		$this->type             = 'number';
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
	 * Method to register options for fields.
	 *
	 * @since 1.2.0
	 * @access public
	 * @return array list of options.
	 */
	public static function options() {

		$options = array();

		$options[] = array(
			'name'     => 'min',
			'label'    => esc_html__( 'Minimum value', 'wpum' ),
			'desc'     => esc_html__( 'Specifies the minimum value allowed, leave blank if not needed.', 'wpum' ),
			'type'     => 'text',
		);
		$options[] = array(
			'name'     => 'max',
			'label'    => esc_html__( 'Maximum value', 'wpum' ),
			'desc'     => esc_html__( 'Specifies the maximum value allowed, leave blank if not needed.', 'wpum' ),
			'type'     => 'text',
		);

		return $options;

	}

}

new WPUM_Field_Type_Number;
