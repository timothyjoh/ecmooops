<?php
/**
 * Handles creation and editing of field groups.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUMCF_New_Group_Editor Class
 *
 * @since 1.0.0
 */
class WPUMCF_New_Group_Editor {

	/**
	 * Name field configuration.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $name_field = array();

	/**
	 * Description field configuration.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $description_field = array();

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Setup the name field arguments
		$this->name_field = array(
			'name'        => 'wpumcf_new_group_name',
			'value'       => '',
			'label'       => esc_html__( 'Group name', 'wpum-custom-fields' ),
			'placeholder' => esc_html__( 'Enter a name for this group', 'wpum-custom-fields' ),
			'class'       => 'text',
			'required'    => true
		);

		// Setup the name field arguments
		$this->description_field = array(
			'name'  => 'wpumcf_new_group_description',
			'value' => '',
			'label' => __( 'Group description (optional - you can change this later)', 'wpum-custom-fields' ),
			'class' => 'text'
		);

		add_action( 'wpum/fields/editor/navbar', array( $this, 'new_group_button' ) );
		add_action( 'wpum/fields/editor/navbar', array( $this, 'new_group_modal' ) );

		add_action( 'wpum_wpumcf_create_new_group', array( $this, 'create_group' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

	}

	/**
	 * Adds the "create group" button next to the editor page title.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function new_group_button() {
		$output = '<a href="#TB_inline?width=600&height=250&inlineId=wpum_new_group_modal" class="button wpum-add-new-group thickbox" title="'. esc_html__( 'Create new fields group', 'wpum-custom-fields' ) .'"><span class="dashicons dashicons-plus-alt"></span> '. esc_html__( 'Create new fields group', 'wpum-custom-fields' ) .'</a>';
		echo $output;
	}

	/**
	 * Displays the content of the new group modal.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function new_group_modal() {

		$output = '<div id="wpum_new_group_modal">';
			$output .= '<form method="post" action="'.admin_url( 'users.php?page=wpum-profile-fields' ).'" id="wpumcf-add-new-group">';

				$output .= WPUM()->html->text( $this->name_field );
				$output .= WPUM()->html->textarea( $this->description_field );

				$output .= '<div class="wpumcf-publish-action">';
					$output .= '<input type="hidden" name="wpum-action" value="wpumcf_create_new_group">';
					$output .= wp_nonce_field( 'wpumcf_create_group', 'wpumcf_create_group' );
					$output .= '<input type="submit" name="wpumcf-create-group" id="wpumcf-create-group" class="button button-primary button-large" value="' . esc_html__( 'Create Group', 'wpum-custom-fields' ) . '">';
				$output .= '</div>';

			$output .= '</form>';
		$output .= '</div>';

		echo $output;

	}

	/**
	 * Store the newly created group into the database.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function create_group() {

		if( isset( $_POST['wpum-action'] ) && $_POST['wpum-action'] == 'wpumcf_create_new_group' ) {

			if( ! current_user_can( 'manage_options' ) )
				return;

			if( ! is_admin() )
				return;

			if ( ! wp_verify_nonce( $_POST['wpumcf_create_group'], 'wpumcf_create_group' ) )
				return;

			$group_settings = array();

			if( isset( $_POST['wpumcf_new_group_name'] ) && $_POST['wpumcf_new_group_name'] !== '' ) {
				$group_settings['name'] = sanitize_text_field( $_POST['wpumcf_new_group_name'] );
			} else {
				$group_settings = new WP_Error( 'missing_title', '' );
			}

			if( isset( $_POST['wpumcf_new_group_description'] ) && $_POST['wpumcf_new_group_description'] !== '' ) {
				$group_settings['description'] = wp_kses_post( $_POST['wpumcf_new_group_description'] );
			}

			// Verify if we have an error and display error message.
			// If not save to database and dislay success message.
			if( is_wp_error( $group_settings ) ) {

				$admin_url = add_query_arg( array( 'message' => 'wpumcf_group_error' ), admin_url( 'users.php?page=wpum-profile-fields' ) );
				wp_redirect( $admin_url );
				exit;

			} else {

				if( ! is_wp_error( $group_settings ) && is_array( $group_settings ) && array_key_exists( 'name', $group_settings ) ) {

					$new_group_id = WPUM()->field_groups->add( $group_settings );

					$admin_url = add_query_arg( array(
						'message' => 'wpumcf_group_created',
						'group_title' => urlencode( $group_settings['name'] ),
						'action' => 'edit',
						'group' => absint( $new_group_id )
					), admin_url( 'users.php?page=wpum-profile-fields' ) );
					wp_redirect( $admin_url );
					exit;

				}

			}

		}

	}

	/**
	 * Display error or success message when creating a group.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function admin_notices() {

		if( isset( $_GET['message'] ) && $_GET['message'] == 'wpumcf_group_error' ) {
			?>
			<div class="error">
				<p><strong><?php esc_html_e( 'Something went wrong: please make sure you add a title when creating a group.', 'wpum-custom-fields' ); ?></strong></p>
			</div>
			<?php
		}

		if( isset( $_GET['message'] ) && $_GET['message'] == 'wpumcf_group_created' && isset( $_GET['group_title'] ) && $_GET['group_title'] !== '' ) {
			?>
			<div class="updated">
				<p><strong><?php printf( esc_html__( '"%s" Group successfully created.', 'wpum-custom-fields' ), urldecode( esc_html( $_GET['group_title'] ) ) ) ?></strong></p>
			</div>
			<?php
		}

	}

}

new WPUMCF_New_Group_Editor;
