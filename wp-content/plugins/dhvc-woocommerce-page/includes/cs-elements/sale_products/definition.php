<?php

class DHVC_Woo_Product_Page_Sale_Products extends DHVC_Woo_Page_Cornerstone_Element_Base {

	public function data() {
		return array( 
			'name' => 'dhvc_woo_product_page_sale_products', 
			'title' => __( 'WC Sale Products', DHVC_WOO_PAGE ), 
			'section' => 'WC Single Product', 
			'icon_group' => 'wc-single-product',
			'supports' => array( 'class' ) );
	}

	public function controls() {
		$this->addControl( 'per_page', 'text', __( 'Product Per Page', DHVC_WOO_PAGE ), '', 4 );
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
	}

	public function render( $atts ) {
		extract( $atts );
		$shortcode = "[dhvc_woo_product_page_sale_products{$extra}]";
		
		return $shortcode;
	}
}