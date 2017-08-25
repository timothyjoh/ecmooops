<?php


class Propel_Org {


	function __construct() {


		add_action( 'init', array( $this, 'create_post_type' ) );


		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );


		add_action( 'save_post', array( $this, 'save_meta_box_data' ) );


		add_filter( 'gettext', array( $this, 'custom_enter_title' ) );


		add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'dropdown_pages_args' ), 1, 1 );


	}


	/**
	 * Registers the 'PROPEL Org' custom post type
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @edited 2015-05-01 13:33:33 - Refactors to only show menu on cap conditional
	 *
	 */
	function create_post_type() {
		$labels = array(
			'name'                => _x( 'PROPEL Orgs', 'Post Type General Name', 'propel' ),
			'singular_name'       => _x( 'PROPEL Org', 'Post Type Singular Name', 'propel' ),
			'menu_name'           => __( 'PROPEL Orgs', 'propel' ),
			'parent_item_colon'   => __( 'Parent PROPEL Org:', 'propel' ),
			'all_items'           => __( 'All PROPEL Orgs', 'propel' ),
			'view_item'           => __( 'View PROPEL Org', 'propel' ),
			'add_new_item'        => __( 'Add New PROPEL Org', 'propel' ),
			'add_new'             => __( 'Add New', 'propel' ),
			'edit_item'           => __( 'Edit PROPEL Org', 'propel' ),
			'update_item'         => __( 'Update PROPEL Org', 'propel' ),
			'search_items'        => __( 'Search PROPEL Orgs', 'propel' ),
			'not_found'           => __( 'Not found', 'propel' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'propel' ),
		);
		$args = array(
			'label'               => __( 'propel_org', 'propel' ),
			'description'         => __( 'An organization in the OKM', 'propel' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'page-attributes' ),
			'taxonomies'          => array( 'org_type' ),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-networking',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);

		if ( current_user_can( 'edit_propel_orgs' ) ) {
			$args['show_in_menu'] = true;
			$args['show_in_nav_menus'] = true;
			$args['show_in_admin_bar'] = true;
		}

		register_post_type( 'propel_org', $args );
	}


	/**
	 * Registers the meta boxes needed for the propel_org cpt
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:11:29
	 *
	 * @return void
	 */
	function add_meta_boxes() {

		add_meta_box(
			'propel_org_org_id',
			__( 'Org ID', 'propel' ),
			array( $this, 'render_org_id_meta_box' ),
			'propel_org',
			'side'
		);

	}


	/**
	 * Renders the org_id meta box
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:12:15
	 *
	 * @param  WP_Post   $post   The post object
	 *
	 * @return void
	 */
	function render_org_id_meta_box( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'propel_org_org_id', 'propel_org_org_id_nonce' );

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$value = get_post_meta( $post->ID, '_org_id', true );

		echo '<input type="text" id="propel_org_org_id" name="propel_org_org_id" value="' . esc_attr( $value ) . '" size="10" />';
	}


	/**
	 * When the post is saved, saves the meta data.
	 *  - Thanks, http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:14:14
	 *
	 * @param   int   $post_id    The ID of the post being saved.
	 *
	 * @return  void
	 */
	function save_meta_box_data( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['propel_org_org_id_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['propel_org_org_id_nonce'], 'propel_org_org_id' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'propel_org' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		}

		/* OK, it's safe for us to save the data now. */

		// Make sure that it is set.
		if ( ! isset( $_POST['propel_org_org_id'] ) ) {
			return;
		}

		// Sanitize user input.
		$org_id = sanitize_text_field( $_POST['propel_org_org_id'] );

		// Update the meta field in the database.
		update_post_meta( $post_id, '_org_id', $org_id );
	}


	/**
	 * Filters for a different title entry text
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:28:48
	 *
	 * @param  string   $input   The given title text
	 *
	 * @return string   $input   The new title text
	 */
	function custom_enter_title( $input ) {

		global $post_type;

		if( is_admin() && 'Enter title here' == $input && 'propel_org' == $post_type )
			return 'Enter org name';

		return $input;
	}


	/**
	 * Some propel_orgs are in 'draft' status because they were created by the public
	 * The don't show in the dropdown 'Attributes' list on the post edit page, so it looks like that propel_org has no parent
	 *
	 * This changes the query for the 'Attributes' dropdown so all propel_orgs, including drafts, show appropriately.
	 *
	 * Taken from http://wordpress.stackexchange.com/questions/3346/how-can-i-set-a-draft-page-as-parent-without-publishing
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-27 10:03:40
	 *
	 * @filter page_attributes_dropdown_pages_args
	 *
	 * @param  array   $dropdown_args   The arguments for the WP_Query
	 *
	 * @return array   $dropdown_args   The arguments for the WP_Query
	 */
	function dropdown_pages_args( $dropdown_args ) {

		$dropdown_args['post_status'] = array( 'publish', 'draft' );

		return $dropdown_args;
	}

}

new Propel_Org();