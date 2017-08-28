<?php
/**
 * Handles management of user status specific menu items.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2016, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Menu_Controller
 *
 * @since 1.4.0
 */
class WPUM_Menu_Controller {

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public function init() {

		// Change admin walker.
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_nav_menu_walker' ) );

		// Add fields via hook.
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'add_custom_fields' ), 10, 4 );

		// Save the new fields.
		add_action( 'wp_update_nav_menu_item', array( $this, 'save_custom_fields'), 10, 2 );

		// Add menu status to menu items object.
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_menu_item' ) );

		// Now exclude menu items if needed.
		if( ! is_admin() ) {
			add_filter( 'wp_get_nav_menu_items', array( $this, 'exclude_menu_items' ), 10, 3 );
		}

	}

	/**
	 * Set the name of the class for the new Walker.
	 *
	 * @param  string $walker existing walker.
	 * @return string         new walker.
	 */
	public function edit_nav_menu_walker( $walker ) {

		return 'Walker_WPUM_Nav_Menu_Roles_Controller';

	}

	/**
	 * Register all new fields for the menus.
	 *
	 * @param  string $item_id current item id.
	 * @return array          fields to display.
	 */
	private function get_custom_fields( $item_id ) {

		$item_status = get_post_meta( $item_id, '_wpum_nav_menu_role', true );

		$fields = array(

			array(
				'type'             => 'select',
				'label'            => esc_html( 'Display to:' ),
				'name'             => 'wpum_nav_menu_status[' . $item_id . ']',
				'desc'             => esc_html__( 'Set the visibility of this menu item.', 'wpum' ),
				'show_option_all'  => false,
				'show_option_none' => false,
				'selected'         => isset( $item_status[ 'status' ] ) ? $item_status[ 'status' ]: '',
				'class'            => 'wpum-menu-visibility-setter',
				'options'          => array(
					''    => esc_html__( 'Everyone', 'wpum' ),
					'in'  => esc_html__( 'Logged In Users', 'wpum' ),
					'out' => esc_html__( 'Logged Out Users', 'wpum' ),
				)
			),

			array(
				'type'             => 'select',
				'label'            => esc_html( 'Select roles:' ),
				'name'             => 'wpum_nav_menu_status_roles[' . $item_id . ']',
				'desc'             => esc_html__( 'Select the roles that should see this menu item. Leave blank for all roles.', 'wpum' ),
				'show_option_all'  => false,
				'show_option_none' => false,
				'multiple'         => true,
				'selected'         => isset( $item_status[ 'roles' ] ) ? $item_status[ 'roles' ]: '',
				'options'          => wpum_get_roles( true )
			),

		);

		return $fields;

	}

	/**
	 * Render all the fields within the menu editor.
	 * Right now they're all "select" fields, refactor will probably change this.
	 *
	 * @param string $item_id item id.
	 * @param object $item    details about the item.
	 * @param string $depth   item depth.
	 * @param array $args     settings.
	 */
	public function add_custom_fields( $item_id, $item, $depth, $args ) {

		$fields = $this->get_custom_fields( $item_id );

		echo '<p class="wpum-menu-controller">';

		echo '<input type="hidden" class="nav-menu-id" name="wpum-menu-item-'. $item_id .'" value="'. esc_attr( $item_id ) .'">';

		foreach ( $fields as $field ) {

			echo WPUM()->html->select( $field );

		}

		echo '</p>';

		echo wp_nonce_field( "wpum_nonce_menu_controller", "wpum_nonce_menu_controller" );

	}

	/**
	 * Save status of the menu.
	 *
	 * @param  string $menu_id         Menu ID.
	 * @param  string $menu_item_db_id ID saved into the database.
	 * @return void
	 */
	public function save_custom_fields( $menu_id, $menu_item_db_id ) {

		global $wp_roles;

		$allowed_roles = apply_filters( 'wpum_nav_menu_roles', $wp_roles->role_names );

		// Nonce verification.
		if ( ! isset( $_POST['wpum_nonce_menu_controller'] ) || ! wp_verify_nonce( $_POST['wpum_nonce_menu_controller'], 'wpum_nonce_menu_controller' ) ){
			return;
		}

		$data_to_save  = false;
		$submitted_menu_statuses = ( array ) $_POST['wpum_nav_menu_status'];
		$submitted_menu_roles = isset( $_POST['wpum_nav_menu_status_roles'] ) ? $_POST['wpum_nav_menu_status_roles'] : false;

		// Check if menu item has a status.
		if( array_key_exists( $menu_item_db_id , $submitted_menu_statuses ) && $submitted_menu_statuses[ $menu_item_db_id ] == 'in' || $submitted_menu_statuses[ $menu_item_db_id ] == 'out' ) {

			$menu_item_status = $submitted_menu_statuses[ $menu_item_db_id ];
			$menu_item_roles  = false;

			// Check if any role has been set.
			if( isset( $_POST['wpum_nav_menu_status_roles'] ) && array_key_exists( $menu_item_db_id , $_POST['wpum_nav_menu_status_roles'] ) ) {
				$menu_item_roles = array_slice( $_POST['wpum_nav_menu_status_roles'][ $menu_item_db_id ], 0, -1 );
			}

			$data_to_save = array( 'status' => $menu_item_status, 'roles' => $menu_item_roles );

		}

		if( $data_to_save ) {

			update_post_meta( $menu_item_db_id, '_wpum_nav_menu_role', $data_to_save );

		} else {

			delete_post_meta( $menu_item_db_id, '_wpum_nav_menu_role' );

		}

	}

	/**
	 * Add menu status to menu object.
	 *
	 * @param  object $menu_item Menu object.
	 * @return object            Menu object.
	 */
	public function setup_menu_item( $menu_item ) {

		$item_status = get_post_meta( $menu_item->ID, '_wpum_nav_menu_role', true );

		if( ! empty( $item_status ) ) {
			$menu_item->wpum_status = $item_status;
		}

		return $menu_item;

	}

	/**
	 * Exclude menu items from navigation.
	 *
	 * @param  array $items all menu items.
	 * @param  string $menu  menu ID.
	 * @param  array $args  args passed to the function.
	 * @return array
	 */
	public function exclude_menu_items( $items, $menu, $args ) {

		foreach ( $items as $key => $item ) {

			if( isset( $item->wpum_status ) ) {

				$status  = $item->wpum_status['status'];
				$roles   = $item->wpum_status['roles'];
				$visible = true;

				switch ( $status ) {
					case 'in':
						$visible = is_user_logged_in() ? true : false;

						if( is_array( $roles ) && ! empty( $roles ) ) {
							foreach ( $roles as $role ) {
								if( ! current_user_can( $role ) ) {
									$visible = false;
								}
							}
						}

						break;
					case 'out':
						$visible = ! is_user_logged_in() ? true : false;
						break;
				}

				// Now exclude item if not visible.
				if( ! $visible ) {
					unset( $items[ $key ] );
				}

			}

    }

		return $items;

	}

}

$wpum_menu_controller = new WPUM_Menu_Controller;
$wpum_menu_controller->init();
