<?php
/**
 * Handles update of the new fields when the account is updated.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUMCF_Update_Account Class.
 * Handles update of the new fields when the account is updated.
 *
 * @since 1.0.0
 */
class WPUMCF_Update_Account {

	/**
	 * Field groups.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $groups = array();

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_action( 'wp_loaded', array( $this, 'init_group_tabs' ), 10 );

		add_action( 'wpum_get_account_page_tabs', array( $this, 'add_group_sections' ), 10 );

		// Store and update user meta fields within the account page.
		add_action( 'wpum_after_user_update', array( $this, 'update_account' ), 10, 3 );

		// Show new field values.
		add_filter( 'wpum_edit_account_field_value', array( $this, 'show_saved_values' ), 10, 3 );

		// Add additional parameters to the field form loading query.
		add_filter( 'wpum_form_field', array( $this, 'add_parameters' ), 10, 2 );

	}

	/**
	 * Initialize this class actions.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {

		if( ! is_page( wpum_get_core_page_id( 'account' ) ) )
			return;

		$this->groups = $this->get_groups();

		// Load new field group tabs within the account form.
		add_action( 'wpum_get_account_page_tabs', array( $this, 'add_group_sections' ), 10 );

	}

	/**
	 * Retrieve groups from the database.
	 *
	 * @access private
	 * @return array list of groups from the database.
	 * @since 1.0.0
	 */
	private function get_groups() {

		$groups = array();

		$args = array(
			'number'         => -1,
			'order'          => 'ASC',
			'exclude_groups' => '1'
		);

		/**
		 * Filters the query arguments used to retrieve groups from the database.
		 * The result of the query is then appended as "new tabs" into the edit account page.
		 *
		 * @since 1.0.0
		 * @param array $args @see get_groups method of the WPUM_DB_Field_Groups class.
		 */
		$args = apply_filters( 'wpumcf_get_groups_query', $args );

		// Retrieve field groups.
		$groups = WPUM()->field_groups->get_groups( $args );

		return $groups;

	}

	/**
	 * Load new field group tabs within the account form.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function add_group_sections( $tabs ) {

		$new_tabs = array();

		$groups = $this->get_groups();

		if( ! empty( $groups ) && is_array( $groups ) ) :

			foreach ( $groups as $key => $tab ) {

				$tab_id = sanitize_key( $tab->name );

				$new_tabs[ $tab_id ] = array(
					'id'    => $tab_id,
					'title' => esc_html( $tab->name ),
				);

			}

			// Now insert new tabs.
			$tabs = wpumcf_array_insert( $tabs, $new_tabs, 'change-password', 'before' );

		endif;

		return $tabs;

	}

	/**
	 * Load tabs content of the newly added tabs.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function init_group_tabs() {

		$groups = $this->get_groups();

		if( ! empty( $groups ) && is_array( $groups ) ) :

			foreach ( $groups as $key => $tab ) {

				$tab_id = sanitize_key( $tab->name );

				add_action( "wpum_account_tab_{$tab_id}", function() use ( $tab ) {

						global $wpum_fields_group_id;

						$wpum_fields_group_id = $tab->id;

						echo WPUM()->forms->get_form( 'custom-group' );

				});

			}

		endif;

	}

	/**
	 * Store and update fields within the edit account page.
	 *
	 * @param  array $user_data array of data passed to wp_update_user()
	 * @param  array $values    all submitted fields.
	 * @param  int $user_id   the id of the user to save.
	 * @return void
	 * @since 1.0.0
	 */
	public function update_account( $user_data, $values, $user_id ) {

		$custom_fields = array();

		// Retrieve all custom fields.
		// Custom fields will always have the prefix wpum_ when created through the addon.
		foreach ( $values['profile'] as $key => $value ) {
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

	/**
	 * Show saved values of custom fields.
	 *
	 * @param  string $default always null
	 * @param  array $field   contains details of the field for the output into the form.
	 * @param  int $user_id the user id for which we're editing the account.
	 * @return void
	 * @since 1.0.0
	 */
	public function show_saved_values( $default, $field, $user_id ) {

		if ( strpos( $field['meta'], 'wpum_' ) === 0 ) {
			return maybe_unserialize( get_user_meta( $user_id, $field['meta'], true ) );
		}

	}

	/**
	 * Add additional parameters when loading the field into the form fields array.
	 *
	 * @since 1.0.0
	 * @param array $field_attr field attributes.
	 * @param array $field_args the array of the field details from the database.
	 * @return array       field settings.
	 */
	public function add_parameters( $field_attr, $field_options ) {

		// Additional parameters for the number field type.
		if( $field_attr['type'] == 'number' ) {

			$max = wpum_get_serialized_field_option( $field_options, 'max' );
			if( $max )
				$field_attr['max'] = $max;

			$min = wpum_get_serialized_field_option( $field_options, 'min' );
			if( $min ) {
				$field_attr['min'] = $min;
			} else {
				$field_attr['min'] = 1;
			}

		} elseif( $field_attr['type'] == 'text' || $field_attr['type'] == 'textarea' || $field_attr['type'] == 'email' ) {

			$placeholder = wpum_get_serialized_field_option( $field_options, 'placeholder' );

			if( $placeholder )
				$field_attr['placeholder'] = $placeholder;

		} elseif( $field_attr['type'] == 'file' && $field_attr['meta'] !== 'user_avatar' ) {

			// Inject supported extensions.
			$field_extensions = wpum_get_serialized_field_option( $field_options, 'extensions' );
			$field_extensions = explode(',', $field_extensions );

			$field_attr['allowed_extensions'] = $field_extensions;

			// Inject multiple support.
			$multiple = wpum_get_serialized_field_option( $field_options, 'multiple' );

			if( $multiple ) {
				$field_attr['multiple'] = true;
			}

			// Inject maximum file size.
			$max_size = wpum_get_serialized_field_option( $field_options, 'max_file_size' );

			if( $max_size ) {
				$field_attr['max_file_size'] = $max_size;
			}

		} elseif( $field_attr[ 'type' ] == 'checkboxes' || $field_attr[ 'type' ] == 'multiselect' || $field_attr[ 'type' ] == 'radio' || ( $field_attr[ 'type' ] == 'select' && $field_attr['meta'] !== 'display_name' ) ) {

			$field_attr['options'] = wpumcf_parse_selectable_options( wpum_get_serialized_field_option( $field_options, 'selectable' ) );

		}

		return $field_attr;

	}

}

new WPUMCF_Update_Account;
