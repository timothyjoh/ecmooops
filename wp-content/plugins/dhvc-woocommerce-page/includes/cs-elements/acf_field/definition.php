<?php

class DHVC_Woo_Product_Page_ACF_Field extends DHVC_Woo_Page_Cornerstone_Element_Base {

	public function data() {
		return array( 
			'name' => 'dhvc_woo_product_page_acf_field', 
			'title' => __( 'WC Single Product ACF Custom Fields', DHVC_WOO_PAGE ), 
			'section' => 'WC Single Product', 
			'icon_group' => 'wc-single-product',
			'supports' => array( 'class' ) );
	}

	public function controls() {
		$custom_fields = array();
		$custom_fields[] = '';
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups = acf_get_field_groups();
		} else {
			$field_groups = apply_filters( 'acf/get_field_groups', array() );
		}
		
		foreach ( $field_groups as $field_group ) {
			if ( is_array( $field_group ) ) {
				if ( function_exists( 'acf_get_fields' ) ) {
					$fields = acf_get_fields( $field_group );
					if ( ! empty( $fields ) ) {
						foreach ( $fields as $field ) {
							$custom_fields[$field['label']] = $field['name'];
						}
					}
				} else {
					$fields = apply_filters( 'acf/field_group/get_fields', array(), $field_group['id'] );
					if ( ! empty( $fields ) ) {
						foreach ( $fields as $field ) {
							$custom_fields[$field['label']] = $field['name'];
						}
					}
				}
			}
		}
		if ( ! empty( $custom_fields ) ) {
			$this->addControl( 
				'field', 
				'select', 
				__( "Field Name", DHVC_WOO_PAGE ), 
				'', 
				'', 
				array( 'choices' => dhvc_select2cs($custom_fields) ) );
		}
	}

	public function render( $atts ) {
		extract( $atts );
		$shortcode = "[dhvc_woo_product_page_acf_field{$extra}]";
		
		return $shortcode;
	}
}