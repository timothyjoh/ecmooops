<?php
/**
 * Registers the file type field.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Field_Type_Avatar Class
 *
 * @since 1.0.0
 */
class WPUM_Field_Type_File extends WPUM_Field_Type {

	/**
	 * Constructor for the field type
	 *
	 * @since 1.0.0
 	 */
	public function __construct() {

		// DO NOT DELETE
		parent::__construct();

		// Label of this field type.
		$this->name             = _x( 'File', 'field type name', 'wpum' );
		// Field type name.
		$this->type             = 'file';
		// Field category.
		$this->category         = 'advanced';
		// Class of this field.
		$this->class            = __CLASS__;
		// Set registration.
		$this->set_registration = true;
		// Set requirement.
		$this->set_requirement  = true;

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
			'name'  => 'extensions',
			'label' => esc_html__( 'Allowed file types', 'wpum' ),
			'desc'  => esc_html__( 'Enter the extension of the files that can be uploaded through this field, separated with a comma. Example: jpg, png, gif', 'wpum' ),
			'type'  => 'text',
 		);

		$options[] = array(
			'name'  => 'multiple',
			'label' => esc_html__( 'Allow multiple files', 'wpum' ),
			'desc'  => esc_html__( 'Enable this option to allow users to upload multiple files through this field.', 'wpum' ),
			'type'  => 'checkbox',
		);

		$options[] = array(
			'name'  => 'max_file_size',
			'label' => esc_html__( 'Maximum file size', 'wpum' ),
			'desc'  => esc_html__( 'Enter the maximum file size users can upload through this field. The amount must be in bytes.', 'wpum' ),
			'type'  => 'text',
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

		$files = $value;

		$output = '';

		// Display files if they're images.
		if( wpum_is_multi_array( $files ) ) {

			foreach ( $files as $key => $file ) {

				$extension = ! empty( $extension ) ? $extension : substr( strrchr( $file['url'], '.' ), 1 );

				if ( 3 !== strlen( $extension ) || in_array( $extension, array( 'jpg', 'gif', 'png', 'jpeg', 'jpe' ) ) ) :

					$output .= '<span class="wpum-uploaded-file-preview"><img src="' . esc_url( $file['url'] ) . '" /></span>';

				elseif( 3 !== strlen( $extension ) || in_array( $extension, array( 'pdf' ) ) ) :

					$output .= '<span class="wpum-uploaded-file-preview"><a href="' . esc_url( $file['url'] ) . '">'. esc_html( basename( $file['url'] ) ) .'</a></span>';

				else :

					$output .= '<span class="wpum-uploaded-file-name"><code>' . esc_html( basename( $file['url'] ) ) . '</code></span>';

				endif;

			}

		// We have a single file.
		} else {

			$extension = ! empty( $extension ) ? $extension : substr( strrchr( $files['url'], '.' ), 1 );

			if ( 3 !== strlen( $extension ) || in_array( $extension, array( 'jpg', 'gif', 'png', 'jpeg', 'jpe' ) ) ) :

				$output .= '<span class="wpum-uploaded-file-preview"><img src="' . esc_url( $files['url'] ) . '" /></span>';

			elseif( 3 !== strlen( $extension ) || in_array( $extension, array( 'pdf' ) ) ) :

				$output .= '<span class="wpum-uploaded-file-preview"><a href="' . esc_url( $files['url'] ) . '" download>'. esc_html( basename( $files['url'] ) ) .'</a></span>';

			else :

				$output .= '	<span class="wpum-uploaded-file-name"><code>' . esc_html( basename( $files['url'] ) ) . '</code></span>';

			endif;

		}

		return $output;

	}

}

new WPUM_Field_Type_File;
