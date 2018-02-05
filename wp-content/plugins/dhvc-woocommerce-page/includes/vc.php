<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class DHVC_Woo_Page_VisualComposer{
	public function __construct(){
		add_action( 'vc_before_init', array(&$this,'init') );
		
		add_action( 'vc_after_set_mode', array(&$this,'disableinline') );
		
		add_action( 'vc_after_init', array(&$this,'editor_init') );
		add_action( 'vc_after_set_mode', array(&$this,'map'));
	}
	
	public function init(){
		require_once DHVC_WOO_PAGE_DIR.'/includes/vc-functions.php';
	}
	
	public function disableinline(){
		if(dhvc_woo_product_page_is_page_editable())
			vc_frontend_editor()->disableInline();
	}
	
	public function editor_init(){
		require_once DHVC_WOO_PAGE_DIR.'/includes/vc-backend-editor.php';
		$backend_editor = new DHVC_Woo_Page_Vc_Backend_Editor();
		$backend_editor->addHooksSettings();
		if(dhvc_woo_product_page_is_page_editable()){
			require_once DHVC_WOO_PAGE_DIR.'/includes/vc-frontend-editor.php';
			$dhvc_woo_page_vc_frontend_editor = new DHVC_Woo_Page_Vc_Frontend_Editor();
			$dhvc_woo_page_vc_frontend_editor->init();
		}
	}
	
	public function map(){
		$params_script = DHVC_WOO_PAGE_URL.'/assets/js/params.js';
		vc_add_shortcode_param ( 'dhvc_woo_product_page_field_categories', 'dhvc_woo_product_page_setting_field_categories',$params_script);
		vc_add_shortcode_param ( 'dhvc_woo_product_page_field_products_ajax', 'dhvc_woo_product_page_setting_field_products_ajax',$params_script);
			
		vc_map ( array (
			"name" => __ ( "WC Single Product Images", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_images",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Title", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_title",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Rating", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_rating",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Price", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_price",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		vc_map ( array (
			"name" => __ ( "WC Single Product Excerpt", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_excerpt",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Description", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_description",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		vc_map ( array (
			"name" => __ ( "WC Single Product Additional Information", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_additional_information",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Add to Cart", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_add_to_cart",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Meta", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_meta",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Sharing", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_sharing",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Data Tabs", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_data_tabs",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Reviews", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_reviews",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "WC Single Product Upsell", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_upsell_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "posts_per_page",
					"value" => 4
				),
				array (
					"type" => "textfield",
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					'save_always'=>true,
					"value" => 4
				),
				array (
					"type" => "dropdown",
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					'save_always'=>true,
					'class' => 'dhwc-woo-product-page-dropdown',
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		vc_map ( array (
			"name" => __ ( "WC Single Product Related", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_related_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "posts_per_page",
					"value" => 4
				),
				array (
					"type" => "textfield",
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					'save_always'=>true,
					"value" => 4
				),
				array (
					"type" => "dropdown",
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					'save_always'=>true,
					'class' => 'dhwc-woo-product-page-dropdown',
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		vc_map ( array (
			"name" => __ ( "WC Single Product Custom Field", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_custom_field",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			'description' => __( 'Custom fields data from meta values of the product.', DHVC_WOO_PAGE ),
			"params" => array (
				array(
					'type' => 'textfield',
					'heading' => __( 'Field key name', DHVC_WOO_PAGE ),
					'param_name' => 'key',
					'save_always'=>true,
					'admin_label'=>true,
					'description' => __( 'Enter custom field name to retrieve meta data value.', DHVC_WOO_PAGE ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Label', DHVC_WOO_PAGE ),
					'param_name' => 'label',
					'save_always'=>true,
					'admin_label'=>true,
					'description' => __( 'Enter label to display before key value.', DHVC_WOO_PAGE ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', DHVC_WOO_PAGE ),
					'param_name' => 'el_class',
					'save_always'=>true,
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', DHVC_WOO_PAGE ),
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		if(function_exists('fpd_get_option')){
			vc_map ( array (
				"name" => __ ( "WC Single FPD Designer", DHVC_WOO_PAGE ),
				"base" => "dhvc_woo_product_page_fpd",
				"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
				"icon" => "icon-dhvc-woo-product-page",
				"params" => array (
					array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						'value'=>'',
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
					),
					array(
						'type' => 'css_editor',
						'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
						'param_name' => 'css',
						'group' => __( 'Design Options', DHVC_WOO_PAGE ),
					),
				)
			) );
		}
		if (class_exists ( 'acf' )) {
			$custom_fields = array ();
			$custom_fields[] = '';
			if(function_exists('acf_get_field_groups')){
				$field_groups = acf_get_field_groups();
			}else{
				$field_groups = apply_filters ( 'acf/get_field_groups', array () );
			}
		
			foreach ( $field_groups as $field_group ) {
				if (is_array ( $field_group )) {
					if(function_exists('acf_get_fields')){
						$fields = acf_get_fields($field_group);
						if (! empty ( $fields )) {
							foreach ( $fields as $field ) {
								$custom_fields [$field ['label']] = $field ['name'];
							}
						}
		
					}else{
						$fields = apply_filters ( 'acf/field_group/get_fields', array (), $field_group ['id'] );
						if (! empty ( $fields )) {
							foreach ( $fields as $field ) {
								$custom_fields [$field ['label']] = $field ['name'];
							}
						}
					}
				}
			}
			if (! empty ( $custom_fields )) {
				vc_map ( array (
					"name" => __ ( "WC ACF Custom Fields", DHVC_WOO_PAGE ),
					"base" => "dhvc_woo_product_page_acf_field",
					"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
					'description' => __( 'Advanced Custom Fields (ACF Plugin).', DHVC_WOO_PAGE ),
					"icon" => "icon-dhvc-woo-product-page",
					"params" => array (
						array (
							"type" => "dropdown",
							"heading" => __ ( "Field Name", DHVC_WOO_PAGE ),
							"param_name" => "field",
							"admin_label" => true,
							'save_always'=>true,
							"value" => $custom_fields
						),
						array (
							"type" => "textfield",
							"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
							"param_name" => "el_class",
							'save_always'=>true,
							'value'=>'',
							"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
						),
						array(
							'type' => 'css_editor',
							'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
							'param_name' => 'css',
							'group' => __( 'Design Options', DHVC_WOO_PAGE ),
						),
					)
				) );
			}
		}
		
		if (defined ( 'YITH_WCWL' )) {
			vc_map ( array (
				"name" => __ ( "WC Single Product Wishlist", DHVC_WOO_PAGE ),
				"base" => "dhvc_woo_product_page_wishlist",
				"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
				"icon" => "icon-dhvc-woo-product-page",
				"params" => array (
					array (
						"type" => "textfield",
						'save_always'=>true,
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						'value'=>'',
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
					),
					array(
						'type' => 'css_editor',
						'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
						'param_name' => 'css',
						'group' => __( 'Design Options', DHVC_WOO_PAGE ),
					),
				)
			) );
		}
		// New shortcode
		vc_map ( array (
			"name" => __ ( "Woo Breadcrumb", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_breadcrumb",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Cart", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_cart",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Checkout", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_checkout",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Order Tracking", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_order_tracking",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo My Account", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_my_account",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Product Category", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_product_category",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "dhvc_woo_product_page_field_categories",
					"class" => "",
					'save_always'=>true,
					"heading" => __ ( "Categories", DHVC_WOO_PAGE ),
					"param_name" => "category"
				),
				array (
					"type" => "textfield",
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					'save_always'=>true,
					"value" => 12
				),
				array (
					"type" => "textfield",
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					'save_always'=>true,
					"value" => 4
				),
				array (
					"type" => "dropdown",
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					'class' => 'dhwc-woo-product-page-dropdown',
					'save_always'=>true,
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					"heading" => __ ( "Query type", DHVC_WOO_PAGE ),
					"param_name" => "operator",
					'save_always'=>true,
					"value" => array (
						""=>"",
						__ ( 'IN', DHVC_WOO_PAGE ) => 'IN',
						__ ( 'AND', DHVC_WOO_PAGE ) => 'AND',
						__ ( 'NOT IN', DHVC_WOO_PAGE ) => 'NOT IN'
					)
				),
				array (
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Product Categories", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_product_categories",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "dhvc_woo_product_page_field_categories",
					"class" => "",
					'save_always'=>true,
					'select_field'=>'id',
					"heading" => __ ( "Categories", DHVC_WOO_PAGE ),
					"param_name" => "ids"
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Number", DHVC_WOO_PAGE ),
					"param_name" => "number"
				),
				array (
					"type" => "textfield",
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __ ( "Hide Empty", DHVC_WOO_PAGE ),
					"param_name" => "hide_empty",
					"value" => array (
						""=>"",
						__ ( 'Yes', DHVC_WOO_PAGE ) => '1',
						__ ( 'No', DHVC_WOO_PAGE ) => '0'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Parent", DHVC_WOO_PAGE ),
					"param_name" => "parent"
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Recent Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_recent_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC',
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "dhvc_woo_product_page_field_products_ajax",
					"heading" => __ ( "Select products", DHVC_WOO_PAGE ),
					"param_name" => "ids",
					'save_always'=>true,
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Sale Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_sale_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Best Selling Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_best_selling_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Top Rated Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_top_rated_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Featured Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_featured_products",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Shop Messages", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_shop_messages",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __ ( "Woo Product Attribute", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_product_attribute",
			"category" => __ ( "WC Single Product", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__ ( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__ ( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__ ( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__ ( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__ ( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__ ( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__ ( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __ ( "Attribute", DHVC_WOO_PAGE ),
					"param_name" => "attribute"
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __ ( "Filter", DHVC_WOO_PAGE ),
					"param_name" => "filter"
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
	}
}

new DHVC_Woo_Page_VisualComposer;