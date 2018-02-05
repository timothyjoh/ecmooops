<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!function_exists('vc_add_shortcode_param') ){
	function vc_add_shortcode_param( $name, $form_field_callback, $script_url = null ) {
		return WpbakeryShortcodeParams::addField( $name, $form_field_callback, $script_url );
	}
}

function dhvc_woo_product_page_include_editor_template($template, $variables = array(), $once = false){
	is_array($variables) && extract($variables);
	if($once) {
		require_once DHVC_WOO_PAGE_DIR.'/includes/editor-templates/'.$template;
	} else {
		require DHVC_WOO_PAGE_DIR.'/includes/editor-templates/'.$template;
	}
}

function dhvc_woo_product_page_is_page_editable(){
	return (isset($_GET['dhvc_woo_product_page_editor']) && 'frontend'===$_GET['dhvc_woo_product_page_editor'])
	|| (isset($_GET['dhvc_woo_product_page_editable']) && 'true'===$_GET['dhvc_woo_product_page_editable']);
}


function dhvc_woo_product_page_access_check_shortcode_edit( $null, $shortcode ){
	$post_id = vc_request_param('post_id');
	$shortcodes = array_keys(dhvc_woo_product_page_single_shortcodes());
	$post_type_setting =  get_option('dhvc_woo_page_template_type','dhwc_template');
	if($post_type_setting === get_post_type($post_id) || dhvc_woo_product_page_is_page_editable()){
		return $null;
	}elseif (in_array($shortcode, $shortcodes)){
		return false;
	}
	return $null;
}
add_action( 'vc_user_access_check-shortcode_edit','dhvc_woo_product_page_access_check_shortcode_edit',10,2);

function dhvc_woo_product_page_access_check_shortcode_all( $null, $shortcode ){
	$post_id = vc_request_param('post_id');
	$shortcodes = array_keys(dhvc_woo_product_page_single_shortcodes());
	$post_type_setting =  get_option('dhvc_woo_page_template_type','dhwc_template');
	if($post_type_setting === get_post_type($post_id) || dhvc_woo_product_page_is_page_editable()){
		return $null;
	}elseif (in_array($shortcode, $shortcodes)){
		return false;
	}
	return $null;
}
add_action( 'vc_user_access_check-shortcode_all', 'dhvc_woo_product_page_access_check_shortcode_all',10,2);