<?php


class Propel_Org_Type {


	function __construct() {

		add_action( 'init', array( $this, 'create_taxonomy' ), 0 );

	}

	// Register Custom Taxonomy
	function create_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Org Types', 'Taxonomy General Name', 'propel' ),
			'singular_name'              => _x( 'Org Type', 'Taxonomy Singular Name', 'propel' ),
			'menu_name'                  => __( 'Org Types', 'propel' ),
			'all_items'                  => __( 'All Org Type', 'propel' ),
			'parent_item'                => __( 'Parent Org Type', 'propel' ),
			'parent_item_colon'          => __( 'Parent Org Type:', 'propel' ),
			'new_item_name'              => __( 'New Org Type Name', 'propel' ),
			'add_new_item'               => __( 'Add New Org Type', 'propel' ),
			'edit_item'                  => __( 'Edit Org Type', 'propel' ),
			'update_item'                => __( 'Update Org Type', 'propel' ),
			'separate_items_with_commas' => __( 'Separate Org Types with commas', 'propel' ),
			'search_items'               => __( 'Search Org Types', 'propel' ),
			'add_or_remove_items'        => __( 'Add or remove Org Types', 'propel' ),
			'choose_from_most_used'      => __( 'Choose from the most used items', 'propel' ),
			'not_found'                  => __( 'Not Found', 'propel' ),
		);

		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);

		register_taxonomy( 'org_type', array( 'propel_org' ), $args );

	}

}

new Propel_Org_Type();