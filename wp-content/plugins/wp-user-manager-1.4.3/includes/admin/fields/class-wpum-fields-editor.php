<?php
/**
 * WP User Manager: Fields Editor
 *
 * @package     wp-user-manager
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Fields_Editor Class
 *
 * @since 1.0.0
 */
class WPUM_Fields_Editor {

	/**
	 * Holds the editor page id.
	 *
	 * @since 1.0.0
	 */
	const editor_hook = 'users_page_wpum-profile-fields';

	/**
	 * Holds the signle field editor page id.
	 *
	 * @since 1.0.0
	 */
	const single_field_hook = 'users_page_wpum-edit-field';

	/**
	 * The Database Abstraction
	 *
	 * @since  1.0.0
	 */
	protected $db;

	/**
	 * Database abstraction for field.
	 *
	 * @since 1.0.0
	 */
	protected $field_db = null;

	/**
	 * Holds the group id.
	 *
	 * @since 1.0.0
	 */
	var $group_id = null;

	/**
	 * Holds the group.
	 *
	 * @since 1.0.0
	 */
	var $group = null;

	/**
	 * Holds the field.
	 *
	 * @since 1.0.0
	 */
	var $field = null;

	/**
	 * Holds the field type object.
	 *
	 * @since 1.0.0
	 */
	var $field_object = null;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->db = new WPUM_DB_Field_Groups;

		// Detect if a group is being edited
		if( isset( $_GET['group'] ) && is_numeric( $_GET['group'] ) )
			$this->groupd_id = intval( $_GET['group'] );

		// Get selected group - set it as primary if no group is selected
		if( isset( $_GET['group'] ) && is_numeric( $_GET['group'] ) ) {
			// Get primary group
			$this->group = $this->db->get_group_by( 'id', $_GET['group'] );
		} else {
			// Get primary group
			$this->group = $this->db->get_group_by( 'primary' );
		}

		// Detect if a field is being edited
		if( isset( $_GET['field'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit_field' ) {
			$this->field_db = new WPUM_DB_Fields;
			$this->field = $this->field_db->get( (int) $_GET['field'] );
			$this->field_object = wpum_get_field_type_object( $this->field->type );
		}

		// loads metaboxes functions
		add_action( 'load-'.self::editor_hook, array( $this, 'load_editor' ) );
		add_action( 'add_meta_boxes_'.self::editor_hook, array( $this, 'add_meta_box' ) );

		// Build the main section of the editor
		add_action( 'wpum/fields/editor/single', array($this, 'field_title_editor') );
		add_action( 'wpum/fields/editor/single', array($this, 'field_description_editor') );

		// Loads metaboxes functions for single field editor page
		add_action( 'load-'.self::single_field_hook, array( $this, 'single_field_load_editor' ) );
		add_action( 'add_meta_boxes_'.self::single_field_hook, array( $this, 'single_field_add_meta_box' ) );

		// Append group saving process
		add_action( 'wpum_edit_group', array( $this, 'process_group' ) );

		// Append groupfield saving process
		add_action( 'wpum_save_field', array( $this, 'process_field' ) );

		// Load WP_List_Table
		if( ! class_exists( 'WP_List_Table' ) ) {
		    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}

		// Extedn WP_List_Table
		require_once WPUM_PLUGIN_DIR . 'includes/admin/fields/class-wpum-groups-fields.php';

	}

	/**
	 * Handles the display of the editor page in the backend.
	 *
	 * @access public
	 * @return void
	 */
	public static function editor_page() {

		ob_start();

		?>

		<div class="wrap wpum-fields-editor-wrap">

			<h2 class="wpum-page-title">
				<?php _e( 'WP User Manager - Fields Editor', 'wpum' ); ?>
				<?php do_action( 'wpum/fields/editor/title' ); ?>
			</h2>

			<div class="wp-filter">
				<?php echo self::navbar(); ?>
				<?php do_action( 'wpum/fields/editor/navbar' ); ?>
			</div>

			<?php echo self::primary_message(); ?>

			<div id="nav-menus-frame">

				<!-- Sidebar -->
				<div id="menu-settings-column" class="metabox-holder">

					<div class="clear"></div>

					<?php do_accordion_sections( self::editor_hook, 'side', null ); ?>

				</div>
				<!-- End Sidebar -->

				<div id="menu-management-liquid" class="wpum-editor-container">

					<?php echo self::group_table(); ?>

					<div class="wpum-table-loader">
						<span id="wpum-spinner" class="spinner wpum-spinner"></span>
					</div>

				</div>

			</div>

		</div>

		<?php

		echo ob_get_clean();

	}

	/**
	 * Displays the groups navigation bar
	 *
	 * @access private
	 * @return void
	 */
	private static function navbar() {

		// Get all groups
		$groups = WPUM()->field_groups->get_groups( array( 'order' => 'ASC' ) );

		if( empty( $groups ) ) :

			$output = '<div class="message error"><p>';
				$output .= __('It seems you do not have any field groups. Please deactivate and re-activate the plugin.', 'wpum');
			$output .= '</p></div>';

		else:

				$output = '<form method="get" action="'. admin_url( 'users.php?page=wpum-profile-fields' ) .'">';

					$output .= '<input type="hidden" name="page" value="wpum-profile-fields">';
					$output .= '<input type="hidden" name="action" value="edit">';

					// Get all groups into an array for the dropdown menu.
					$options = array();
					foreach ( $groups as $key => $group ) {
						$options += array( $group->id => $group->name );
					}

					// Generate dropdown menu
					$args = array(
						'options'          => $options,
						'label'            => __('Select a field group to edit:', 'wpum'),
						'id'               => 'wpum-group-selector',
						'name'             => 'group',
						'selected'         => ( isset( $_GET['group'] ) && is_numeric( $_GET['group'] ) ) ? (int) $_GET['group'] : false,
						'multiple'         => false,
						'show_option_all'  => false,
						'show_option_none' => false
					);

					$output .= '<p>' . WPUM()->html->select( $args );
						$output .= '<span class="submit-btn"><input type="submit" class="button-secondary" value="'.__('Select', 'wpum').'"></span>';
					$output .= '</p>';

				$output .= '</form>';

		endif;

		return $output;

	}

	/**
	 * Displays the table to manage each single group.
	 *
	 * @access private
	 * @return void
	 */
	private static function group_table() {

		$custom_fields_table = new WPUM_Groups_Fields();
		$custom_fields_table->prepare_items();
		$custom_fields_table->display();

		wp_nonce_field( 'wpum_fields_editor_nonce', 'wpum_fields_editor_nonce' );

	}

	/**
	 * Trigger the add_meta_boxes hooks to allow meta boxes to be added.
	 *
	 * @access public
	 * @return void
	 */
	public function load_editor() {

	    do_action( 'add_meta_boxes_'.self::editor_hook, null );
	    do_action( 'add_meta_boxes', self::editor_hook, null );

	    /* Enqueue WordPress' script for handling the meta boxes */
	    wp_enqueue_script('postbox');

	    // Process group settings update
	    $this->process_group();

	}

	/**
	 * Trigger the add_meta_boxes hooks to allow meta boxes to be added
	 * on the single field editor page.
	 *
	 * @access public
	 * @return void
	 */
	public function single_field_load_editor() {

	    do_action( 'add_meta_boxes_'.self::single_field_hook, null );
	    do_action( 'add_meta_boxes', self::single_field_hook, null );

	    /* Enqueue WordPress' script for handling the meta boxes */
	    wp_enqueue_script('postbox');

	}

	/**
	 * Register metaboxes.
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box( 'wpum_fields_editor_edit_group', __( 'Group Settings', 'wpum' ), array( $this, 'group_settings' ), self::editor_hook, 'side' );
		add_meta_box( 'wpum_fields_editor_help', __( 'Fields Order', 'wpum' ), array( $this, 'help_text' ), self::editor_hook, 'side' );
	}

	/**
	 * Register metaboxes for the single field editor.
	 *
	 * @access public
	 * @return void
	 */
	public function single_field_add_meta_box() {

		// Add Field Requirement metabox
		if( $this->field_object->set_requirement && $this->field->meta !== 'user_email' )
			add_meta_box( 'wpum_field_requirement', esc_html__( 'Requirement', 'wpum' ), array( $this, 'requirement_setting' ), self::single_field_hook, 'side' );

		// Add option to display on registration form if it's in primary group.
		if( WPUM()->field_groups->is_primary( intval( $_GET['from_group'] ) ) && $this->field_object->set_registration && $this->field->meta !== 'user_email' )
			add_meta_box( 'wpum_field_on_registration', esc_html__( 'Show on registration form', 'wpum' ), array( $this, 'field_on_registration' ), self::single_field_hook, 'side' );

		// Add name adjustment option.
		if( $this->field->meta == 'first_name' || $this->field->meta == 'last_name' )
			add_meta_box( 'wpum_field_adjust_name', esc_html__( 'Display full name', 'wpum' ), array( $this, 'name_setting' ), self::single_field_hook, 'side' );

		// Add profile visibility metabox.
		if( $this->field->meta !== 'password' || $this->field->meta !== 'user_avatar' ) {
			add_meta_box( 'wpum_profile_visibility', esc_html__( 'Visibility & Editing', 'wpum' ), array( $this, 'visibility_settings' ), self::single_field_hook, 'side' );
		}

	}

	/**
	 * Content of the first metabox.
	 *
	 * @access public
	 * @return mixed content of the "how it works" metabox.
	 */
	public static function help_text( $current_menu = null ) {

		$output = '<p>';
			$output .= sprintf( __('Click and drag the %s button to change the order of the fields.', 'wpum'), '<span class="dashicons dashicons-menu"></span>');
		$output .= '</p>';

		echo $output;

	}

	/**
	 * Display a message about the primary group.
	 *
	 * @access private
	 */
	public static function primary_message() {

		if( isset( $_GET['group'] ) && !WPUM()->field_groups->is_primary( intval( $_GET['group'] ) ) )
			return;
		?>

		<p>
			<span class="dashicons dashicons-info"></span>
			<?php _e('Fields into this group can appear on the signup page.', 'wpum') ;?>
		</p>

		<?php
	}

	/**
	 * Display the interface to edit the group.
	 *
	 * @access private
	 */
	public function group_settings() {

		// Name Field Args
		$name_args = array(
			'name'         => 'name',
			'value'        => esc_html( stripslashes( $this->group->name ) ),
			'label'        => __('Group name', 'wpum'),
			'class'        => 'text',
		);

		// Description field args
		$description_args = array(
			'name'         => 'description',
			'value'        => esc_html( stripslashes( $this->group->description ) ),
			'label'        => __('Group description', 'wpum'),
			'class'        => 'textarea',
		);

		// Prepare delete url
		$delete_url = wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'group' => (int) $this->group->id ), admin_url( 'users.php?page=wpum-profile-fields' ) ), 'delete', 'nonce' );

		?>

		<form method="post" action="<?php echo admin_url( 'users.php?page=wpum-profile-fields' ); ?>" id="wpum-group-settings-edit">

			<div class="wpum-group-settings">
				<?php echo WPUM()->html->text( $name_args ); ?>
				<?php echo WPUM()->html->textarea( $description_args ); ?>
			</div>

			<div id="major-publishing-actions">
				<div id="delete-action">
					<?php if( !$this->group->is_primary && $this->group->can_delete ) : ?>
						<a class="submitdelete deletion" href="<?php echo $delete_url; ?>"><?php _e('Delete Group', 'wpum'); ?></a>
					<?php endif; ?>
				</div>
				<div id="publishing-action">
					<input type="hidden" name="wpum-action" value="edit_group"/>
					<input type="hidden" name="group" value="<?php echo ( isset( $_GET['group'] ) ) ? (int) $_GET['group'] : (int) $this->group->id; ?>"/>
					<?php wp_nonce_field( 'wpum_group_settings' ); ?>
					<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e('Save Group Settings', 'wpum'); ?>">
				</div>
				<div class="clear"></div>
			</div>

		</form>

		<?php

	}

	/**
	 * Process the update of the group settings.
	 *
	 * @access private
	 */
	public function process_group() {

		// Process the group delete action.
		if( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['group'] ) && is_numeric( $_GET['group'] ) ) {

			// nonce verification.
			if ( ! wp_verify_nonce( $_GET['nonce'], 'delete' ) ) {
				return;
			}

			if( WPUM()->field_groups->delete( (int) $_GET['group'] ) ) {

				// Get all fields of the group and delete them too.
				$args = array(
					'id'     => (int) $_GET['group'],
					'number' => -1,
				);
				$fields = WPUM()->fields->get_by_group( $args );

				foreach ( $fields as $field_to_delete ) {
					WPUM()->fields->delete( $field_to_delete->id );
				}

				// Redirect now.
				$admin_url = add_query_arg( array( 'message' => 'group_delete_success' ), admin_url( 'users.php?page=wpum-profile-fields' ) );
				wp_redirect( $admin_url );
				exit();
			}

		}

		// Check whether the group settings form has been submitted.
		if( isset( $_POST['wpum-action'] ) && $_POST['wpum-action'] == 'edit_group' ) {

			// nonce verification
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wpum_group_settings' ) ) {
				return;
			}

			// bail if something is wrong.
			if( ! is_numeric( $_POST['group'] ) && ! current_user_can( 'manage_options' ) )
				return;

			$args = array(
				'name'        => sanitize_text_field( $_POST['name'] ),
				'description' => wp_kses_post( $_POST['description'] )
			);

			WPUM()->field_groups->update( (int) $_POST['group'], $args );

			// Redirect now
			$admin_url = add_query_arg( array( 'message' => 'group_success' ), admin_url( 'users.php?page=wpum-profile-fields' ) );
			wp_redirect( $admin_url );
			exit();

		}

	}

	/**
	 * Render single field editor page.
	 *
	 * @access public
	 * @return void
	 */
	public static function edit_field_page() {

		ob_start();

		// Prevent access to this page is no field id is passed.
		if( !isset( $_GET['field'] ) || !is_numeric( $_GET['field'] ) )
			wp_die( 'To edit a field please go to Users -> Profile fields' );

			$current_group = ( isset( $_GET['from_group'] ) && is_numeric( $_GET['from_group'] ) ) ? $_GET['from_group'] : false;
			$editor_url = add_query_arg( array( 'action' => 'edit', 'group' => $current_group ), admin_url( 'users.php?page=wpum-profile-fields' ) );

		?>

		<div class="wrap wpum-fields-editor-wrap">

			<h2 class="wpum-page-title">
				<?php echo __( 'Editing field', 'wpum' ); ?>
				<a href="<?php echo esc_url( $editor_url ); ?>" class="add-new-h2"><?php _e('Back to editor', 'wpum'); ?></a>
			</h2>

			<form name="wpum-edit-field-form" action="#" method="post" id="wpum-edit-field-form" autocomplete="off">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">

						<div id="post-body-content">

							<?php do_action( 'wpum/fields/editor/single' ); ?>

							<div id="normal-metaboxes-wrapper">
								<?php do_meta_boxes( self::single_field_hook, 'normal', null ); ?>
							</div>

						</div><!-- #post body content -->

						<div id="postbox-container-1" class="postbox-container wpum-field-editor-side-metaboxes">
							<div id="save-field-container" >

								<!-- save field box -->
								<div id="save-field" class="postbox">
									<h3 class="hndle ui-sortable-handle"><span><?php _e('Save Field', 'wpum'); ?></span></h3>
									<div id="major-publishing-actions">
										<div id="publishing-action">

											<input type="hidden" name="wpum-action" value="save_field"/>
											<input type="hidden" name="from_group" value="<?php echo ( isset( $_GET['from_group'] ) ) ? (int) $_GET['from_group'] : false; ?>"/>
											<input type="hidden" name="which_field" value="<?php echo ( isset( $_GET['field'] ) ) ? (int) $_GET['field'] : false; ?>"/>
											<?php wp_nonce_field( 'wpum_save_field' ); ?>

											<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e('Save Field', 'wpum'); ?>"></div>
											<div class="clear"></div>
										</div>
									</div>
								</div>
								<!-- save field box -->

								<?php do_meta_boxes( self::single_field_hook, 'side', null ); ?>

							</div>
						</div><!-- postbox-container sidebar -->

					</div>
				</div>
			</form>

		</div>

		<?php

		echo ob_get_clean();

	}

	/**
	 * Render the title editor for the single field editor page.
	 *
	 * @access public
	 * @return void
	 */
	public function field_title_editor() {

		// Prepare configuration for fields
		$field_name_args = array(
			'name'        => 'name',
			'value'       => esc_html( stripslashes( $this->field->name ) ),
			'label'       => false,
			'placeholder' => __('Enter a name for this field', 'wpum'),
			'class'       => 'text',
		);

		?>

		<div id="titlediv">
			<div id="titlewrap">
				<?php echo WPUM()->html->text( $field_name_args ); ?>
			</div>
		</div>

		<?php

	}

	/**
	 * Render the description editor for the single field editor page.
	 *
	 * @access public
	 * @return void
	 */
	public function field_description_editor() {

		$description_settings = array(
			'media_buttons' => false,
			'teeny'         => true,
			'quicktags'     => false,
			'textarea_rows' => 3
		);

		?>

		<div class="description-editor">
			<h3><?php esc_html_e('Field Description (optional)', 'wpum'); ?></h3>
			<?php wp_editor( stripslashes( $this->field->description ), 'field_description', $description_settings ); ?>
		</div>

		<?php

	}

	/**
	 * Render the requirement settings for the field editor.
	 *
	 * @access public
	 * @return void
	 */
	public function requirement_setting() {

		$args = array(
			'name'    => 'set_as_required',
			'current' => $this->field->is_required,
			'label'   => esc_html__('Set this field as required', 'wpum'),
		);

		echo WPUM()->html->checkbox( $args );

	}

	/**
	 * Render the requirement settings for the field editor.
	 *
	 * @access public
	 * @return void
	 */
	public function field_on_registration() {

		$args = array(
			'name'    => 'show_on_registration',
			'current' => $this->field->show_on_registration,
			'label'   => esc_html__('Display this field on registration', 'wpum'),
		);

		echo WPUM()->html->checkbox( $args );

	}

	/**
	 * Render the full name setting for the field editor.
	 *
	 * @access public
	 * @return void
	 */
	public function name_setting() {

		$args = array(
			'name'    => 'display_full_name',
			'current' => wpum_get_field_option( $this->field->id, 'display_full_name' ) ? true : false,
			'label'   => esc_html__( 'Display full name', 'wpum' ),
			'desc'    => esc_html__( 'Enable to display the user full name instead.', 'wpum' ),
		);

		echo WPUM()->html->checkbox( $args );

	}

	/**
	 * Render the visibility settings metabox.
	 *
	 * @access public
	 * @return void
	 * @since 1.2.0
	 */
	public function visibility_settings() {

		$args = array(
			'name'             => 'field_visibility',
			'selected'         => $this->field->default_visibility,
			'label'            => esc_html__( 'Profile visibility', 'wpum' ),
			'desc'             => esc_html__( 'Set the visibility of this field on users profiles.', 'wpum' ),
			'show_option_all'  => false,
			'show_option_none' => false,
			'options'          => wpum_get_field_visibility_settings()
		);

		echo WPUM()->html->select( $args );

		if( $this->field_object->set_editing === true ) {

			echo "<br/><br/>";

			$args_editing = array(
				'name'             => 'field_editing',
				'selected'         => wpum_get_field_option( $this->field->id, 'can_edit' ),
				'label'            => esc_html__( 'Profile editing', 'wpum' ),
				'desc'             => esc_html__( 'Set who can edit this field.', 'wpum' ),
				'show_option_all'  => false,
				'show_option_none' => false,
				'options'          => wpum_get_field_editing_settings()
			);

			echo WPUM()->html->select( $args_editing );

		}

		if( $this->field_object->set_read_only === true ) {

			echo "<br/><br/>";

			$args_read_only = array(
				'name'    => 'read_only',
				'current' => wpum_get_field_option( $this->field->id, 'read_only' ) ? true : false,
				'label'   => esc_html__( 'Set as read only', 'wpum' ),
				'desc'    => esc_html__( 'Enable to prevent users from editing this field.', 'wpum' ),
			);

			echo WPUM()->html->checkbox( $args_read_only );

		}


	}

	/**
	 * Save the field to the database
	 *
	 * @access public
	 * @return void
	 */
	public function process_field() {

		// Check whether the form has been submitted
		if( isset( $_POST['wpum-action'] ) && $_POST['wpum-action'] == 'save_field' ) {

			// nonce verification
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wpum_save_field' ) ) {
				return;
			}

			// bail if something is wrong
			if( !isset( $_POST['from_group'] ) || !isset( $_POST['which_field'] ) || !is_admin() || !current_user_can( 'manage_options' ) ) {
				return;
			}

			// store information into variable
			$field_id = (int) $_POST['which_field'];
			$group_id = (int) $_POST['from_group'];

			// Prepare array
			$args = array(
				'name'                 => sanitize_text_field( $_POST['name'] ),
				'description'          => wp_kses_post( $_POST['field_description'] ),
				'is_required'          => isset( $_POST['set_as_required'] ) ? (bool) $_POST['set_as_required']:            false,
				'show_on_registration' => isset( $_POST['show_on_registration'] ) ? (bool) $_POST['show_on_registration']:  false,
				'default_visibility'   => isset( $_POST['field_visibility'] ) ? sanitize_key( $_POST['field_visibility'] ): 'public'
			);

			// Unset options from being saved if field type doesn't support them
			if( ! $this->field_object->set_registration )
				unset( $args['show_on_registration'] );
			if( ! $this->field_object->set_requirement || $this->field->meta == 'user_email' )
				unset( $args['is_required'] );

			// Dirty fix for always required/show on registration fields.
			if( $this->field->meta == 'user_email' ||
					$this->field->meta == 'username' ||
					$this->field->meta == 'password'
			) {
				unset( $args['is_required'] );
				unset( $args['show_on_registration'] );
			}

			if( $this->field->meta == 'nickname' || $this->field->meta == 'display_name' ) {
				unset( $args['is_required'] );
			}

			// Save the field
			if( WPUM()->fields->update( $field_id, $args ) ) {

				// Verify whether the "display full name" option has been checked or not.
				// If it's checked, then we store the value into the field options.
				if( $this->field->meta == 'first_name' || $this->field->meta == 'last_name' ) {

					$display_full_name = isset( $_POST['display_full_name'] ) ? (bool) $_POST['display_full_name'] : false;

					if( $display_full_name ) {
						wpum_update_field_option( $field_id, 'display_full_name', true );
					} elseif ( $display_full_name === false ) {
						wpum_delete_field_option( $field_id, 'display_full_name' );
					}

				}

				// Verify whether the read only option has been enabled.
				if( $this->field_object->set_read_only === true ) {

					$read_only = isset( $_POST['read_only'] ) ? (bool) $_POST['read_only'] : false;

					if( $read_only ) {
						wpum_update_field_option( $field_id, 'read_only', true );
					} else {
						wpum_delete_field_option( $field_id, 'read_only' );
					}

				}

				// Verify the editing option has been set for the appropriate fields.
				if( $this->field_object->set_editing === true ) {

					$can_edit = isset( $_POST['field_editing'] ) ? esc_attr( $_POST['field_editing'] ) : false;

					if( $can_edit && $can_edit !== 'public' ) {
						wpum_update_field_option( $field_id, 'can_edit', $can_edit );
					}

					if( $can_edit == 'public' ) {
						wpum_delete_field_option( $field_id, 'can_edit' );
					}

				}

				// Allow plugins to extend the save process
				do_action( 'wpum/fields/editor/single/before_save', $field_id, $group_id, $this->field, $this->field_object );

				// Redirect now
				$admin_url = add_query_arg( array(
					'message' => 'field_saved',
					'action'  => 'edit',
					'group' => $group_id
				), admin_url( 'users.php?page=wpum-profile-fields' ) );

				wp_redirect( $admin_url );

				exit();

			}

		}

	}

}

new WPUM_Fields_Editor;
