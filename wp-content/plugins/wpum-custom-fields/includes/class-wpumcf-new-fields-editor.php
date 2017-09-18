<?php
/**
 * Handles creation and editing of new fields.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUMCF_New_Fields_Editor Class
 *
 * @since 1.0.0
 */
class WPUMCF_New_Fields_Editor {

	/**
	 * Basic fields.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $basic_fields = array();

	/**
	 * Advanced fields.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $advanced_fields = array();

	/**
	 * Store the current group id where we will add the new field.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $current_group;

	/**
	 * Store the current field details being edited.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public $current_field;

	/**
	 * Store the current field type class object being edited.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public $current_field_object;

	/**
	 * Field types which should display the placeholder option.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $placeholder_allowed = array();

	/**
	 * Field type settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $field_settings = array();

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Retrieve field types.
		$this->basic_fields = wpum_get_field_types();

		// Retrieve field types.
		$this->advanced_fields = wpum_get_field_types( true, 'advanced' );

		// Retrieve current group.
		$this->current_group = $this->get_current_group();

		// Retrieve current field.
		$this->current_field = $this->get_current_field();

		// Retrieve current field.
		$this->current_field_object = $this->get_current_field_object();

		// Setup placeholder field requirement.
		$this->placeholder_allowed = $this->set_placeholder();

		// Setup the field type settings.
		$this->field_settings = $this->set_field_settings();

		// Load metaboxes into fields editor.
		add_action( 'add_meta_boxes_'.WPUM_Fields_Editor::editor_hook, array( $this, 'add_meta_box' ) );

		// Load field settings for fields that allow it.
		add_action( 'add_meta_boxes_'.WPUM_Fields_Editor::single_field_hook, array( $this, 'single_field_metabox' ) );

		add_action( 'admin_init', array( $this, 'create' ) );
		add_action( 'admin_init', array( $this, 'delete' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Update the field.
		add_action( 'wpum/fields/editor/single/before_save', array( $this, 'save_field' ), 10, 4 );

	}

	/**
	 * Hook into the editor metaboxes and add new ones.
	 * We remove then re-add the "Fields order" metabox so we can change it's order.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function add_meta_box() {

		add_meta_box( 'wpum_basic_fields', esc_html__( 'Basic Fields', 'wpum-custom-fields' ), array( $this, 'basic_fields_metabox' ), WPUM_Fields_Editor::editor_hook, 'side' );
		add_meta_box( 'wpum_advanced_fields', esc_html__( 'Advanced Fields', 'wpum-custom-fields' ), array( $this, 'advanced_fields_metabox' ), WPUM_Fields_Editor::editor_hook, 'side' );

		remove_meta_box( 'wpum_fields_editor_help', WPUM_Fields_Editor::editor_hook, 'side' );
		add_meta_box( 'wpum_fields_editor_help_new', esc_html__( 'Fields Order', 'wpum-custom-fields' ), 'WPUM_Fields_Editor::help_text', WPUM_Fields_Editor::editor_hook, 'side' );

	}

	/**
	 * Add new metaboxes to the single field editing page.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function single_field_metabox() {

		if( wpumcf_field_is_custom_field( $this->current_field->id ) )
			add_meta_box( 'wpumcf_field_options', esc_html__( 'Field Settings', 'wpum-custom-fields' ), array( $this, 'single_field_options' ), WPUM_Fields_Editor::single_field_hook, 'normal' );

	}

	/**
	 * Retrieve the current group id.
	 *
	 * @access private
	 * @return int the group id.
	 * @since 1.0.0
	 */
	private function get_current_group() {

		$group_id = isset( $_GET['group'] ) ? absint( $_GET['group'] ) : $this->get_primary_group_id();

		return $group_id;

	}

	/**
	 * Get current field from database.
	 *
	 * @access private
	 * @return mixed
	 * @since 1.0.0
	 */
	private function get_current_field() {

		$field = false;

		if( ! isset( $_GET['page'] ) || isset( $_GET['page'] ) && $_GET['page'] !== 'wpum-edit-field' )
			return;

		if( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && $_GET['action'] !== 'edit_field' )
			return;

		if( isset( $_GET['field'] ) && is_numeric( $_GET['field'] ) ) {
			$field = WPUM()->fields->get( $_GET['field'] );
		}

		return $field;

	}

	/**
	 * Retrieve current field type class.
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	private function get_current_field_object() {

		$field_object = ( ! empty( $this->current_field ) && isset( $this->current_field->type ) ) ? wpum_get_field_type_object( $this->current_field->type ) : false;

		return $field_object;

	}

	/**
	 * Retrieve the primary group id number.
	 *
	 * @access private
	 * @return int the group id number.
	 * @since 1.0.0
	 */
	private function get_primary_group_id() {

		$group    = WPUM()->field_groups->get_group_by('primary');
		$group_id = $group->id;

		return $group_id;

	}

	/**
	 * Create new field and save it into the database.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function create() {

		if( isset( $_GET['action'] ) && $_GET['action'] == 'create-field' ) {

			if( ! current_user_can( 'manage_options' ) )
				return;

			if( ! is_admin() )
				return;

			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wpumcf-create-field' ) )
				return;

			// Verify field group id is set.
			if( empty( $this->current_group ) )
				return;

			// Get the field type we're creating.
			$field_type = isset( $_GET['field_type'] ) ? esc_attr( $_GET['field_type'] ) : false;

			// Verify it's set.
			if( ! $field_type )
				return;

			$new_field = array(
				'group_id' => $this->current_group,
				'type'     => $field_type,
				'name'     => sprintf( esc_html__( 'New %s field', 'wpum-custom-fields' ), $field_type ),
			);

			$created = WPUM()->fields->add( $new_field );

			if( $created ) {

				// Store a flag into the field options that determines whether this is a custom field.
				// This is needed to hide/show certain editing options that default fields shouldn't display.
				wpum_update_field_option( (int) $created, 'custom_field', true );

				// Now we append a random meta to the field.
				if( $field_type == 'file' ) {
					WPUM()->fields->update( (int) $created, array( 'meta' => 'wpum_file_field_' . (int) $created ) );
				} else {
					WPUM()->fields->update( (int) $created, array( 'meta' => 'wpum_field_' . (int) $created ) );
				}

				/**
				 * Fires after the field has been stored into the database,
				 * and just before the user is redirected to editing screen.
				 *
				 * @param $field_id the id of the newly created field.
				 * @since 1.0.0
				 */
				do_action( 'wpumcf_create_field', $created );

				// Redirect user.
				$admin_url = add_query_arg( array(
					'message'    => 'wpumcf_new_field_success',
					'action'     => 'edit_field',
					'field'      => $created,
					'from_group' => $this->current_group
				), admin_url( 'users.php?page=wpum-edit-field' ) );
				wp_redirect( $admin_url );
				exit;

			} else {

				$admin_url = add_query_arg( array( 'message' => 'wpumcf_new_field_error' ), admin_url( 'users.php?page=wpum-profile-fields' ) );
				wp_redirect( $admin_url );
				exit;

			}

		}

	}

	/**
	 * Delete a field from the database.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function delete() {

		if( isset( $_GET['action'] ) && $_GET['action'] == 'delete_field' ) {

			if( ! current_user_can( 'manage_options' ) )
				return;

			if( ! is_admin() )
				return;

			$field_id = ( isset( $_GET['field'] ) && is_numeric( $_GET['field'] ) ) ? $_GET['field'] : false;

			if( ! $field_id )
				return;

			if ( ! wp_verify_nonce( $_GET['_wpnonce'], "delete_field_{$field_id}" ) )
				return;

			if( WPUM()->fields->delete( (int) $field_id ) ) {

				do_action( 'wpumcf_delete_field', $field_id );

				// Redirect to current group page.
				$admin_url = admin_url( 'users.php?page=wpum-profile-fields' );
				$admin_url = add_query_arg( array(
					'message' => 'wpumcf_field_deleted',
					'action ' => 'edit',
					'group'   => $this->current_group
				), $admin_url );

				wp_redirect( $admin_url );
				exit;

			}

		}

	}

	/**
	 * Set the fields who should display the placeholder option.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function set_placeholder() {

		$placeholders = array( 'text', 'textarea', 'number', 'url', 'email' );

		return apply_filters( 'wpumcf_placeholder_setting', $placeholders );

	}

	/**
	 * Setup the display of the field settings within the editor.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function set_field_settings() {

		$settings = array();

		if( ! isset( $_GET['page'] ) || isset( $_GET['page'] ) && $_GET['page'] !== 'wpum-edit-field' )
			return;

		if( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && $_GET['action'] !== 'edit_field' )
			return;

		// Retrieve settings from each field type.
		$type_options = wpum_get_field_type_options( $this->current_field->type );

		if( $this->current_field ) {

			if ( strpos( $this->current_field->meta, 'wpum_file_' ) === 0 ) {
				$meta_key_field_value = str_replace( 'wpum_file_', '', $this->current_field->meta );
			} else {
				$meta_key_field_value = str_replace( 'wpum_', '', $this->current_field->meta );
			}

			$settings[] = array(
				'name'     => 'wpumcf_metakey',
				'value'    => $meta_key_field_value,
				'label'    => esc_html__( 'Unique meta key', 'wpum-custom-fields' ),
				'desc'     => esc_html__( 'The key must be unique for each field and written in lowercase with an underscore ( _ ) separating words e.g country_list or job_title. This will be used to store information about your users into the database of your website.', 'wpum-custom-fields' ),
				'type'     => 'text',
				'required' => true
			);

			// Checkboxes fields do not need a placeholder option.
			if( in_array( $this->current_field->type , $this->placeholder_allowed ) ) {
				$settings[] = array(
					'name'     => 'placeholder',
					'value'    => wpum_get_serialized_field_option( $this->current_field->options, 'placeholder' ),
					'label'    => esc_html__( 'Placeholder', 'wpum-custom-fields' ),
					'desc'     => esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.', 'wpum-custom-fields' ),
					'type'     => 'text',
					'required' => false
				);
			}

			// Merge additional settings from field type.
			if( is_array( $type_options ) && ! empty( $type_options ) ) {
				foreach ( $type_options as $setting ) {
					if( $setting['type'] == 'checkbox' ) {
						$setting['current'] = ( wpum_get_serialized_field_option( $this->current_field->options, $setting['name'] ) == 'on' ) ? true : false;
					} elseif( $setting['type'] == 'select' ) {
						$setting['selected'] = wpum_get_serialized_field_option( $this->current_field->options, $setting['name'] );
					} else {
						$setting['value'] = wpum_get_serialized_field_option( $this->current_field->options, $setting['name'] );
					}
					$settings[] = $setting;
				}
			}

		}

		return $settings;

	}

	/**
	 * Hook into the saving process of the wpum plugin and store the custom field settings.
	 *
	 * @param  int $field_id     the field id.
	 * @param  int $group_id     the group id.
	 * @param  object $field        the field details from the database.
	 * @param  object $field_object field type class object.
	 * @return void
	 * @since 1.0.0
	 */
	public function save_field( $field_id, $group_id, $field, $field_object ) {

		if( ! current_user_can( 'manage_options' ) )
			return;

		if( ! is_admin() )
			return;

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wpum_save_field' ) )
			return;

		// Get registered field settings.
		$settings = $this->field_settings;

		// Loop registered field settings.
		foreach ( $settings as $submitted_setting ) {

			if( isset( $_POST[ $submitted_setting['name'] ] ) && ! empty( $_POST[ $submitted_setting['name'] ] ) ) {

				$submitted_value = $_POST[ $submitted_setting['name'] ];

				// Sanitize submitted value.
				if( is_array( $submitted_value ) ) {
					$submitted_value = array_map( 'sanitize_text_field', $submitted_value );
				} else {
					$submitted_value = sanitize_text_field( $submitted_value );
				}

				// Field metakey is not saved into the options column of the field table,
				// so we're using a separate function to store it.
				if( $submitted_setting['name'] == 'wpumcf_metakey' ) {

					$meta_name = $submitted_value;

					if( $this->current_field->type == 'file' ) {
						$meta_name = 'file_' . $submitted_value;
					}

					$args = array(
						'meta' => 'wpum_' . $meta_name
					);
					WPUM()->fields->update( $field_id, $args );

				} else {

					wpum_update_field_option( $field_id, sanitize_key( $submitted_setting['name'] ), $submitted_value );

				}

			// Checkbox fields cannot be detected through $_POST when empty so we set them as false automatically.
			} elseif( $submitted_setting['type'] == 'checkbox' && ! isset( $_POST[ $submitted_setting['name'] ] ) ) {
				wpum_update_field_option( $field_id, sanitize_key( $submitted_setting['name'] ), false );
			}

		}

		// Now detect whether there should be repeated options for select type fields.
		if( $this->current_field_object->has_repeater ) {

			if( isset( $_POST['field-options'] ) && is_array( $_POST['field-options'] ) && ! empty( $_POST['field-options'] ) ) {

				// Store options
				$selectable_options = $_POST['field-options'];
				$options_to_save = array();

				// Loop and sanitize options.
				foreach ( $selectable_options as $key => $option ) {
					if( ! empty( $option ) && !empty( $option['option-title'] ) && !empty( $option['option-value'] ) ) {
						if( array_key_exists( 'set-as-default' , $option ) ) {
							$option['set-as-default'] = true;
						}
						$options_to_save[] = array_map( 'sanitize_text_field', $option );
					}
				}

				// Store the options.
				if( ! empty( $options_to_save ) )
					wpum_update_field_option( $field_id, 'selectable', $options_to_save );

			}

		}

	}

	/**
	 * Build the content of the basic fields metabox.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function basic_fields_metabox() {

		$create_url = wp_nonce_url( admin_url( 'users.php?page=wpum-profile-fields' ), 'wpumcf-create-field' );
		$create_url = add_query_arg( array( 'action' => 'create-field', 'group' => $this->current_group ), $create_url );

		foreach ( $this->basic_fields as $type => $name ) :

			// Remove password field from being displayed.
			if( $type == 'password' )
				continue;

		?>

			<a href="<?php echo esc_url( add_query_arg( array( 'field_type' => esc_attr( $type ) ), $create_url ) ); ?>" class="button add-field-btn"><?php echo esc_html( $name ); ?></a>

		<?php endforeach;

	}

	/**
	 * Build the content of the advanced fields metabox.
	 *
	 * @access public
	 * @return boid
	 * @since 1.0.0
	 */
	public function advanced_fields_metabox() {

		$create_url = wp_nonce_url( admin_url( 'users.php?page=wpum-profile-fields' ), 'wpumcf-create-field' );
		$create_url = add_query_arg( array( 'action' => 'create-field', 'group' => $this->current_group ), $create_url );

		foreach ( $this->advanced_fields as $type => $name ) :

		?>

			<a href="<?php echo esc_url( add_query_arg( array( 'field_type' => esc_attr( $type ) ), $create_url ) ); ?>" class="button add-field-btn"><?php echo esc_html( $name ); ?></a>

		<?php endforeach;

	}

	/**
	 * Generate the customization options for the current field being edited.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function single_field_options() {

		?>
		<div class="wpumcf-field-settings-wrapper">
		<?php

			foreach ( $this->field_settings as $setting ) {

				echo '<div class="setting">';
				echo WPUM()->html->{$setting['type']}( $setting );
				echo '</div>';

			}

			if( $this->current_field_object->has_repeater ) {
				echo '<div class="setting">';
				echo $this->options_composer( $this->current_field->id, $this->current_field->type );
				echo '</div>';
			}

		?>
		<div class="clear"></div>
		</div>
		<?php

	}

	/**
	 * Builds the interface for the options of specific fields such as dropdown, multiselect and multiple checkboxes.
	 *
	 * @access public
	 * @param  int $field_id   the field id.
	 * @param  string $field_type the type of field.
	 * @return void
	 * @since 1.0.0
	 */
	public function options_composer( $field_id, $field_type ) {

		$show_values_field = array(
			'name'  => 'show_values',
			'label' => esc_html__( 'Show values', 'wpum-custom-fields' ),
		);

		// Current field options.
		$current_options = wpum_get_field_option( $field_id, 'selectable' );

		?>

		<h4 class="fake-label"><?php esc_html_e( 'Field choices', 'wpum-custom-fields' ); ?></h4>
		<p class="wpum-description"><?php esc_html_e( 'Add choices to this field. You can mark each choice as checked by default by using the radio/checkbox fields on the left.', 'wpum-custom-fields' ); ?></p>

		<div class="show-values-wrap">
			<?php echo WPUM()->html->checkbox( $show_values_field ); ?>
		</div>

		<div class="wpumcf-field-repeater-options">

			<div class="repeater-wrapper">

				<div data-repeater-list="field-options" class="repeater-table hide-values">

					<?php if( $current_options && ! empty( $current_options ) ) : ?>

							<?php foreach ( $current_options as $key => $option ) : ?>

								<div class="repeater-row" data-repeater-item>

								  <div class="repeater-element">
								    <a href="#" class="sort-option"><span class="dashicons dashicons-menu"></span></a>
								  </div>

								  <div class="repeater-element">
								    <input type="checkbox" name="field-options[<?php echo (int)$key; ?>][set-as-default][]" <?php if( array_key_exists( 'set-as-default' , $option ) ) : ?>checked="checked"<?php endif; ?> class="wpum-checkbox set_as_default <?php if( $field_type == 'multiselect' || $field_type == 'checkboxes' ): ?>allow-multiple<?php endif; ?>"/>
								  </div>

								  <div class="repeater-element">
								    <input type="text" name="field-options[<?php echo (int)$key; ?>][option-title]" value="<?php echo esc_attr( $option['option-title'] ); ?>" placeholder="<?php esc_html_e('Enter a title for this option', 'wpum-custom-fields'); ?>"/>
								  </div>

								  <div class="repeater-element">
								    <input type="text" name="field-options[<?php echo (int)$key; ?>][option-value]" value="<?php echo esc_attr( $option['option-value'] ); ?>" placeholder="<?php esc_html_e('Enter a value for this option', 'wpum-custom-fields'); ?>"/>
								  </div>

								  <div class="repeater-element">
								    <input data-repeater-delete type="button" class="button" value="<?php esc_html_e( 'Delete', 'wpum-custom-fields' ); ?>"/>
								  </div>

								</div>

							<?php endforeach; ?>

					<?php else : ?>

						<div class="repeater-row" data-repeater-item>

						  <div class="repeater-element">
						    <a href="#" class="sort-option"><span class="dashicons dashicons-menu"></span></a>
						  </div>

						  <div class="repeater-element">
						    <input type="checkbox" name="set-as-default" class="wpum-checkbox set_as_default <?php if( $field_type == 'multiselect' || $field_type == 'checkboxes' ): ?>allow-multiple<?php endif; ?>"/>
						  </div>

						  <div class="repeater-element">
						    <input type="text" name="option-title" value="" placeholder="<?php esc_html_e('Enter a title for this option', 'wpum-custom-fields'); ?>"/>
						  </div>

						  <div class="repeater-element">
						    <input type="text" name="option-value" value="" placeholder="<?php esc_html_e('Enter a value for this option', 'wpum-custom-fields'); ?>"/>
						  </div>

						  <div class="repeater-element">
						    <input data-repeater-delete type="button" class="button" value="<?php esc_html_e( 'Delete', 'wpum-custom-fields' ); ?>"/>
						  </div>

						</div>

					<?php endif; ?>

				</div>

			</div>

			<input data-repeater-create type="button" class="button add-new-option" value="<?php esc_html_e( 'Add new option', 'wpum-custom-fields' ); ?>"/>

		</div>
		<div class="clear"></div>
		<?php

		$output = ob_get_clean();
		return $output;

	}

	/**
	 * Display error or success message when creating a group.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function admin_notices() {

		if( isset( $_GET['message'] ) && $_GET['message'] == 'wpumcf_new_field_error' ) {
			?>
			<div class="error">
				<p><strong><?php esc_html_e( 'Something went wrong: please try again.', 'wpum-custom-fields' ); ?></strong></p>
			</div>
			<?php
		}

		if( isset( $_GET['message'] ) && $_GET['message'] == 'wpumcf_field_deleted' ) {
			?>
			<div class="updated">
				<p><strong><?php esc_html_e( 'Field succesfully deleted.', 'wpum-custom-fields' ); ?></strong></p>
			</div>
			<?php
		}

		if( isset( $_GET['message'] ) && $_GET['message'] == 'wpumcf_new_field_success' ) {
			?>
			<div class="updated">
				<p><strong><?php esc_html_e( 'Field succesfully created.', 'wpum-custom-fields' ); ?></strong></p>
			</div>
			<?php
		}

	}

}

new WPUMCF_New_Fields_Editor;
