<?php
/**
 * Registers the url type field.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Field_Type_Url Class
 *
 * @since 1.0.0
 */
class WPUM_Field_Type_Url extends WPUM_Field_Type {

	/**
	 * Constructor for the field type
	 *
	 * @since 1.0.0
	*/
	public function __construct() {

		// DO NOT DELETE.
		parent::__construct();

		// Label of this field type.
		$this->name             = _x( 'Url', 'field type name', 'wpum' );
		// Field type name
		$this->type             = 'url';
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
			'name'     => 'rel',
			'label'    => esc_html__( 'Nofollow', 'wpum' ),
			'desc'     => esc_html__( 'Enable this option to specify that the search spiders should not follow this link.', 'wpum' ),
			'type'     => 'checkbox',
		);

		return $options;

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

		$nofollow = wpum_get_serialized_field_option( $field->options, 'rel' );

		if( ! empty( $nofollow ) ) :

			$output = '<a href="' . esc_url( $value ) .'" rel="nofollow">' . esc_url( $value ) . '</a>';

		else :

			$output = '<a href="' . esc_url( $value ) .'">' . esc_url( $value ) . '</a>';

		endif;

		return $output;

	}

}

new WPUM_Field_Type_Url;
