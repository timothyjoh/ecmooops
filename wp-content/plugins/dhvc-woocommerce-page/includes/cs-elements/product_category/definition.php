<?php

class DHVC_Woo_Product_Page_Product_Category extends DHVC_Woo_Page_Cornerstone_Element_Base {

	public function data() {
		return array( 
			'name' => 'dhvc_woo_product_page_product_category', 
			'title' => __( 'Woo Product Category', DHVC_WOO_PAGE ), 
			'section' => 'WC Single Product', 
			'icon_group' => 'wc-single-product', 
			'supports' => array( 'class' ) );
	}

	public function controls() {
		$this->addControl( 'per_page', 'text', __( 'Product Per Page', DHVC_WOO_PAGE ), '', 12 );
		$this->addControl( 'columns', 'text', __( 'Columns', DHVC_WOO_PAGE ), '', 4 );
		$this->addControl( 
			'orderby', 
			'select', 
			__( "Products Ordering", DHVC_WOO_PAGE ), 
			'', 
			'', 
			array( 
				'choices' => dhvc_select2cs( 
					array( 
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date', 
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified', 
						__( 'Random', DHVC_WOO_PAGE ) => 'rand', 
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title', 
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity', 
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating', 
						__( 'Price', DHVC_WOO_PAGE ) => 'price' ) ) ) );
		$this->addControl( 
			'order', 
			'select', 
			__( "Ascending or Descending", DHVC_WOO_PAGE ), 
			'', 
			'', 
			array( 
				'choices' => dhvc_select2cs( 
					array( __( 'Ascending', DHVC_WOO_PAGE ) => 'ASC', __( 'Descending', DHVC_WOO_PAGE ) => 'DESC' ) ) ) );
		$args = array( 'orderby' => 'name', 'hide_empty' => 0 );
		$choices = array();
		$categories = get_terms( 'product_cat', $args );
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $cat ) :
				$choices[] = array( 'value' => $cat->slug, 'label' => $cat->name );
			endforeach
			;
		}
		$this->addControl( 
			'category', 
			'select', 
			__( "Categories", DHVC_WOO_PAGE ), 
			'', 
			'', 
			array( 'choices' => $choices ) );
		$this->addControl( 
			'operator', 
			'select', 
			__( "Query type", DHVC_WOO_PAGE ), 
			'', 
			'', 
			array( 
				'choices' => dhvc_select2cs( 
					array( 
						__( 'IN', DHVC_WOO_PAGE ) => 'IN', 
						__( 'AND', DHVC_WOO_PAGE ) => 'AND', 
						__( 'NOT IN', DHVC_WOO_PAGE ) => 'NOT IN' ) ) ) );
	}

	public function render( $atts ) {
		extract( $atts );
		$shortcode = "[dhvc_woo_product_page_product_category{$extra}]";
		
		return $shortcode;
	}
}
