<?php
if (class_exists ( 'acf' )) {
	$custom_fields = array ();
	$field_groups = apply_filters ( 'acf/get_field_groups', array () );
	foreach ( $field_groups as $field_group ) {
		if (is_array ( $field_group )) {
			$fields = apply_filters ( 'acf/field_group/get_fields', array (), $field_group ['id'] );
			if (! empty ( $fields )) {
				foreach ( $fields as $field ) {
					$custom_fields [$field ['label']] = $field ['name'];
				}
			}
		}
	}
	if (! empty ( $custom_fields )) {
		vc_map ( array (
				"name" => __ ( "Woo Single Product Advanced Custom Fields", DHVC_WOO_PAGE ),
				"base" => "dhvc_woo_product_page_custom_field",
				"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
				"icon" => "icon-dhvc-woo-product-page",
				"params" => array (
						array (
								"type" => "dropdown",
								"heading" => __ ( "Field Name", DHVC_WOO_PAGE ),
								"param_name" => "field",
								"admin_label" => true,
								"value" => $custom_fields 
						),
						array (
								"type" => "textfield",
								"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
								"param_name" => "el_class",
								"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
						) 
				) 
		) );
		function dhvc_woo_product_page_custom_field_shortcode($atts, $content = null) {
			extract ( shortcode_atts ( array (
					'field' => '',
					'el_class' => '' 
			), $atts ) );
			if (empty ( $field )) {
				return '';
			}
			ob_start ();
			echo '<div class="dhvc_woo_product_page_custom_field ' . $el_class . '">';
			the_field ( $field );
			echo '</div>';
			return ob_get_clean ();
		}
		add_shortcode ( 'dhvc_woo_product_page_custom_field', 'dhvc_woo_product_page_custom_field_shortcode' );
	}
}

vc_map ( array (
		"name" => __ ( "Woo Single Product Images", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_images",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "checkbox",
						"class" => "",
						"heading" => __ ( "Hide Sale Flash", DHVC_WOO_PAGE ),
						"param_name" => "hide_sale_flash",
						"value" => array (
								__ ( 'Yes, please', DHVC_WOO_PAGE ) => '1' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Single Product Image Width", DHVC_WOO_PAGE ),
						"param_name" => "width",
						"description" => __ ( 'This is custom the image width used by the main image on the product page. If value is NULL or 0 will use default settings in Woocommerce', DHVC_WOO_PAGE ) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Single Product Image Height", DHVC_WOO_PAGE ),
						"param_name" => "height",
						"description" => __ ( 'This is custom the image height used by the main image on the product page. If value is NULL or 0 will use default settings in Woocommerce', DHVC_WOO_PAGE ) 
				),
				array (
						"type" => "checkbox",
						"class" => "",
						"heading" => __ ( "No Crop Single Product Image?", DHVC_WOO_PAGE ),
						"param_name" => "no_crop",
						"value" => array (
								__ ( 'No, please', DHVC_WOO_PAGE ) => '0' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Thumbnails Width", DHVC_WOO_PAGE ),
						"param_name" => "thumb_width",
						"description" => __ ( 'This is custom the thumbnails width used by the main image on the product page. If value is NULL or 0 will use default settings in Woocommerce', DHVC_WOO_PAGE ) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Thumbnails Height", DHVC_WOO_PAGE ),
						"param_name" => "thumb_height",
						"description" => __ ( 'This is custom the thumbnails height used by the main image on the product page. If value is NULL or 0 will use default settings in Woocommerce', DHVC_WOO_PAGE ) 
				),
				array (
						"type" => "checkbox",
						"class" => "",
						"heading" => __ ( "No Crop Product Thumbnails Image?", DHVC_WOO_PAGE ),
						"param_name" => "thumb_no_crop",
						"value" => array (
								__ ( 'No, please', DHVC_WOO_PAGE ) => '0' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Title", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_title",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Rating", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_rating",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Price", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_price",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );
vc_map ( array (
		"name" => __ ( "Woo Single Product Excerpt", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_excerpt",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Description", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_description",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );
vc_map ( array (
		"name" => __ ( "Woo Single Product Additional Information", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_additional_information",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Add to Cart", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_add_to_cart",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Meta", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_meta",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Sharing", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_sharing",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Data Tabs", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_data_tabs",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Single Product Reviews", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_reviews",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Product Upsell", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_upsell",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "posts_per_page",
						"value" => 4 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						'class' => 'dhwc-woo-product-page-dropdown',
						"value" => array (
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
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );
vc_map ( array (
		"name" => __ ( "Woo Product Related Products", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_related_products",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "posts_per_page",
						"value" => 4 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						'class' => 'dhwc-woo-product-page-dropdown',
						"value" => array (
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
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );
// New shortcode
vc_map ( array (
		"name" => __ ( "Woo Cart", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_cart",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Checkout", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_checkout",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Order Tracking", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_order_tracking",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo My Account", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_my_account",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Product Category", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_category",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "per_page",
						"value" => 12 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						'class' => 'dhwc-woo-product-page-dropdown',
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "dhvc_woo_product_page_field_categories",
						"class" => "",
						"heading" => __ ( "Categories", DHVC_WOO_PAGE ),
						"param_name" => "category" 
				),
				array (
						"type" => "dropdown",
						"class" => "",
						"heading" => __ ( "Query type", DHVC_WOO_PAGE ),
						"param_name" => "operator",
						"value" => array (
								__ ( 'IN', DHVC_WOO_PAGE ) => 'IN',
								__ ( 'AND', DHVC_WOO_PAGE ) => 'AND',
								__ ( 'NOT IN', DHVC_WOO_PAGE ) => 'NOT IN' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Product Categories", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_categories",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "dhvc_woo_product_page_field_categories",
						"class" => "",
						"heading" => __ ( "Categories", DHVC_WOO_PAGE ),
						"param_name" => "ids" 
				),
				array (
						"type" => "textfield",
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
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "dropdown",
						"class" => "",
						"heading" => __ ( "Hide Empty", DHVC_WOO_PAGE ),
						"param_name" => "hide_empty",
						"value" => array (
								__ ( 'Yes', DHVC_WOO_PAGE ) => '1',
								__ ( 'No', DHVC_WOO_PAGE ) => '0' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Parent", DHVC_WOO_PAGE ),
						"param_name" => "parent" 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Recent Products", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_recent_products",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "per_page",
						"value" => 12 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Products", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_products",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "dhvc_woo_product_page_field_products_ajax",
						"heading" => __ ( "Select products", DHVC_WOO_PAGE ),
						"param_name" => "ids" 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Sale Products", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_sale_products",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "per_page",
						"value" => 12 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Best Selling Products", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_best_selling_products",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "per_page",
						"value" => 12 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Top Rated Products", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_top_rated_products",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "per_page",
						"value" => 12 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Featured Products", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_featured_products",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "per_page",
						"value" => 12 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Shop Messages", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_shop_messages",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

vc_map ( array (
		"name" => __ ( "Woo Product Attribute", DHVC_WOO_PAGE ),
		"base" => "dhvc_woo_product_page_product_attribute",
		"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
		"icon" => "icon-dhvc-woo-product-page",
		"params" => array (
				array (
						"type" => "textfield",
						"heading" => __ ( "Product Per Page", DHVC_WOO_PAGE ),
						"param_name" => "per_page",
						"value" => 12 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Columns", DHVC_WOO_PAGE ),
						"param_name" => "columns",
						"value" => 4 
				),
				array (
						"type" => "dropdown",
						"heading" => __ ( "Products Ordering", DHVC_WOO_PAGE ),
						"param_name" => "orderby",
						"value" => array (
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
						"heading" => __ ( "Ascending or Descending", DHVC_WOO_PAGE ),
						"param_name" => "order",
						"value" => array (
								__ ( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
								__ ( 'Descending', DHVC_WOO_PAGE ) => 'DESC' 
						) 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Attribute", DHVC_WOO_PAGE ),
						"param_name" => "attribute" 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Filter", DHVC_WOO_PAGE ),
						"param_name" => "filter" 
				),
				array (
						"type" => "textfield",
						"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
						"param_name" => "el_class",
						"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
				) 
		) 
) );

if (defined ( 'YITH_WCWL' )) {
	vc_map ( array (
			"name" => __ ( "Woo Single Product Wishlist", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_wishlist",
			"category" => __ ( "Woo Shortcodes", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Extra class name", DHVC_WOO_PAGE ),
							"param_name" => "el_class",
							"description" => __ ( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE ) 
					) 
			) 
	) );
}
