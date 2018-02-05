<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DHVC_Woo_Page_Post_Types {
	
	public static function init()
	{
		add_action('init', array(__CLASS__, 'register_post_types'), 5);
		if(is_admin())
			add_action( 'add_meta_boxes', array( __CLASS__, 'remove_meta_boxes' ), 1000 );
	}
	
	public static function remove_meta_boxes(){ 
		remove_meta_box( 'vc_teaser', 'dhwc_template' , 'side' );
		remove_meta_box( 'commentsdiv', 'dhwc_template' , 'normal' );
		remove_meta_box( 'commentstatusdiv', 'dhwc_template' , 'normal' );
		remove_meta_box( 'slugdiv', 'dhwc_template' , 'normal' );
		remove_meta_box('mymetabox_revslider_0', 'dhwc_template', 'normal');
	}
	
	public static function register_post_types()
	{
		if (!is_blog_installed() || post_type_exists('dhwc_template')) {
			return;
		}
	
		register_post_type('dhwc_template',array(
			'labels' => array(
				'name' => __('Product Templates', DHVC_WOO_PAGE),
				'singular_name' => __('Product Template', DHVC_WOO_PAGE),
				'menu_name' => _x('Product Template', 'Admin menu name', DHVC_WOO_PAGE),
				'add_new' => __('Add Product Template', DHVC_WOO_PAGE),
				'add_new_item' => __('Add New Product Template', DHVC_WOO_PAGE),
				'edit' => __('Edit', DHVC_WOO_PAGE),
				'edit_item' => __('Edit Product Template', DHVC_WOO_PAGE),
				'new_item' => __('New Product Template', DHVC_WOO_PAGE),
				'view' => __('View Product Template', DHVC_WOO_PAGE),
				'view_item' => __('View Product Template', DHVC_WOO_PAGE),
				'search_items' => __('Search Product Templates', DHVC_WOO_PAGE),
				'not_found' => __('No Product Templates found', DHVC_WOO_PAGE),
				'not_found_in_trash' => __('No Product Templates found in trash', DHVC_WOO_PAGE),
				'parent' => __('Parent Product Template', DHVC_WOO_PAGE)
			),
			'public' => false,
			'has_archive' => false,
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => 'edit.php?post_type=product',
			'query_var' => true,
			'capability_type' => 'post',
			'map_meta_cap'=> true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title','editor')
		));
	}
}

DHVC_Woo_Page_Post_Types::init();