<?php

class DHVC_Woo_Product_Page_Custom_Field extends DHVC_Woo_Page_Cornerstone_Element_Base {

	public function data() {
		return array( 
			'name' => 'dhvc_woo_product_page_custom_field', 
			'title' => __( 'WC Single Product Custom Field', DHVC_WOO_PAGE ), 
			'section' => 'WC Single Product', 
			'icon_group' => 'wc-single-product',
			'supports' => array( 'class' ) );
	}

	public function controls() {
		$this->addControl( 
			'key', 
			'text', 
			__( 'Field key name', DHVC_WOO_PAGE ), 
			__( 'Enter custom field name to retrieve meta data value.', DHVC_WOO_PAGE ), 
			'' );
		$this->addControl( 
			'label', 
			'text', 
			__( 'Label', DHVC_WOO_PAGE ), 
			__( 'Enter label to display before key value.', DHVC_WOO_PAGE ), 
			'' );
	}

	public function render( $atts ) {
		extract( $atts );
		$shortcode = "[dhvc_woo_product_page_custom_field{$extra}]";
		
		return $shortcode;
	}
}