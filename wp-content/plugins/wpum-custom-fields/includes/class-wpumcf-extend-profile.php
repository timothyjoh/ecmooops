<?php
/**
 * Handles the display of the custom fields within the backend user profile.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUMCF_Extend_Profile Class.
 * Handles the display of the custom fields within the backend user profile.
 *
 * @since 1.0.0
 */
class WPUMCF_Extend_Profile {

  /**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		global $pagenow;

		if( $pagenow == 'user-edit.php' || $pagenow == 'profile.php' ) {

			if( ! class_exists( 'CMB2' ) ) {
				require_once WPUMCF_PLUGIN_DIR . '/includes/lib/cmb2/init.php';
			}

			add_action( 'cmb2_init', array( $this, 'add_fields'), 999 );

		}

  }

	/**
	 * Retrieve all the groups from the database.
	 *
	 * @access private
	 * @return object list of all custom fields.
	 * @since 1.0.0
	 */
	private function get_groups() {

		$groups = WPUM()->field_groups->get_groups( array( 'order' => 'ASC', 'array' => true ) );

		return $groups;

	}

	/**
	 * Retrieve all the custom fields for each group from the database.
	 *
	 * @access private
	 * @return object fields.
	 * @since 1.0.0
	 */
	private function get_fields() {

		$groups = $this->get_groups();

		foreach ( $groups as $key => $group ) {

			$current_group_fields = WPUM()->fields->get_by_group( array(
				'id'      => $group['id'],
				'array'   => true,
				'orderby' => 'field_order',
				'order'   => 'ASC'
			) );

			if( ! empty( $current_group_fields ) ) {

				foreach ( $current_group_fields as $field_key => $field ) {

					if( substr( $field['meta'], 0, 5 ) !== "wpum_" ) {
						unset( $current_group_fields[ $field_key ] );
					}

				}

			}

			$groups[ $key ]['fields'] = $current_group_fields;

			if( empty( $current_group_fields ) ) {
				unset( $groups[ $key ] );
			}

		}

		return $groups;

	}

	/**
	 * Format field from the database to a compatible array for the cmb2 class.
	 *
	 * @access private
	 * @param  array $field field details.
	 * @return array         formatted field details.
	 * @since 1.0.0
	 */
	private function format_field( $field ) {

		$formatted_field = array();

		$formatted_field['name'] = $field['name'];
		$formatted_field['desc'] = $field['description'];
		$formatted_field['id']   = $field['meta'];
		$formatted_field['type'] = $field['type'];

		switch ( $field['type'] ) {
			case 'select':
			case 'radio':
				$formatted_field['options'] = wpumcf_parse_selectable_options( wpum_get_serialized_field_option( $field['options'], 'selectable' ) );
				break;
			case 'checkboxes':
			case 'multiselect':
				$formatted_field['type']    = 'multicheck';
				$formatted_field['options'] = wpumcf_parse_selectable_options( wpum_get_serialized_field_option( $field['options'], 'selectable' ) );
				break;
			case 'email':
				$formatted_field['type'] = 'text_email';
				break;
			case 'number':
				$formatted_field['type'] = 'text_small';
				break;
			case 'url':
				$formatted_field['type'] = 'text_url';
				break;
			case 'file':
				$formatted_field['type'] = 'userfiles';
				break;
		}

		return $formatted_field;

	}

	/**
	 * Add the fields into the user edit page.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function add_fields() {

		foreach ( $this->get_fields() as $key => $group ) {

			$cmb_user = new_cmb2_box( array(
				'id'               => 'wpumcf_' . $group['id'],
				'title'            => $group['name'],
				'object_types'     => array( 'user' ),
				'show_names'       => true,
				'new_user_section' => 'add-new-user',
			) );

			$cmb_user->add_field( array(
				'name'     => $group['name'],
				'desc'     => $group['description'],
				'id'       => 'extra_info',
				'type'     => 'title',
				'on_front' => false,
			) );

			foreach ( $group['fields'] as $field_key => $field ) {

				$cmb_user->add_field( $this->format_field( $field ) );

			}

		}

	}

}

new WPUMCF_Extend_Profile;
