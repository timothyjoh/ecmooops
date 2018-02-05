<?php

class DHVC_Woo_Product_Page_Sharing extends DHVC_Woo_Page_Cornerstone_Element_Base {

	public function data() {
		return array( 
			'name' => 'dhvc_woo_product_page_sharing', 
			'title' => __( 'WC Single Product Sharing', DHVC_WOO_PAGE ), 
			'section' => 'WC Single Product', 
			'icon_group' => 'wc-single-product',
			'supports' => array( 'class' ) );
	}

	public function render( $atts ) {
		extract( $atts );
		$shortcode = "[dhvc_woo_product_page_sharing{$extra}]";
		
		return $shortcode;
	}
}