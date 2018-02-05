<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('DHCS_WOO_PAGE_CS_ELEMENT_DIR', DHVC_WOO_PAGE_DIR.'/includes/cs-elements');

function dhvc_select2cs($options){
	$choices = array();
	$choices[]=array( 'value' => '',  'label' => '' );
	foreach ($options as $label=>$value){
		$choices[] = array( 'value' => $value,  'label' => $label );
	}
	return $choices;
}

function dhvc_woo_product_page_shortcode_placeholder($content){
	$args = array(
		'posts_per_page'      => 1,
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => 1,
		'no_found_rows'       => 1
	);
	$dhvc_woo_product_page_shortcode_placeholder_id = apply_filters('dhvc_woo_product_page_shortcode_placeholder_id',0);
	if ( !empty($dhvc_woo_product_page_shortcode_placeholder_id) ) {
		$args['p'] = absint( $dhvc_woo_product_page_shortcode_placeholder_id );
	}
	$single_product = new WP_Query( $args );
	if($single_product->have_posts()){
		while ($single_product->have_posts()){
			$single_product->the_post();
			do_action('dhvc_woo_product_page_shortcode_placeholder_render');
		}
	}
	wp_reset_postdata();
	return $content;
}



class DHVC_Woo_Page_Cornerstone_Element_Base extends Cornerstone_Element_Base{
	
	
	public function renderElement($atts){
		$atts = parent::injectAtts($atts);
		if(!isset($atts['extra']))
			$atts['extra']='';
		
		$atts['extra'] .= $this->_injectAtts2($atts);
		do_action('dhvc_cs_render_element');
		return dhvc_woo_product_page_shortcode_placeholder($this->render( $atts )); 
	}
	
	protected function _injectAtts2($atts=array()){
		$base_attrs='';
		$defaults = parent::get_defaults();
		if(isset($atts['_type']))
			unset( $atts['_type'] );
		
		foreach ((array)$atts as $key=>$value){
			if('extra' != $key){
				$attr='';
				if(!empty($value))
					$attr=" {$key}=\"{$value}\"";
				elseif(isset($defaults[$key]) && $defaults[$key]!='')
					$attr=" {$key}=\"{$defaults[$key]}\"";
				
				$base_attrs.= $attr;	
			}
		}	
		return $base_attrs;
	}
	
}

class DHVC_Woo_Page_Cornerstone{
	
	public function __construct(){
		add_action('cornerstone_register_elements', array(&$this,'register_elements'),20);
		add_action('dhvc_cs_render_element', array(&$this,'add_post_class'));
		add_action('cornerstone_load_preview', array(&$this,'load_preview'));
		add_action('cornerstone_generated_preview_css', array(&$this,'cornerstone_generated_preview_css'));
	}
	public function cornerstone_generated_preview_css(){
		echo '.cs-preview-element-wrapper{
		  *zoom: 1;
		}
		
		.cs-preview-element-wrapper:before,
		.cs-preview-element-wrapper:after{
		  display: table;
		  content: "";
		}
		
		.cs-preview-element-wrapper:after{
		  clear: both;
		}';
	}
	public function load_preview(){
		add_filter('the_content', array($this,'render_the_content'));
	}
	
	public function render_the_content($content){
		return '<div class="woocommerce"><div class="product">'.$content.'</div></div>';
	}
	
	public function add_post_class(){
		add_filter('post_class', array(&$this,'post_class'),50,3);
	}
	
	public function post_class($classes,$class, $post_id){
		$classes[] = get_post_type($post_id);
		return $classes;
	}
	
	public function register_elements(){
		//Single Shortcode
		cornerstone_register_element('dhvc_woo_product_page_images','dhvc_woo_product_page_images',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/images');
		cornerstone_register_element('dhvc_woo_product_page_title','dhvc_woo_product_page_title',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/title');
		cornerstone_register_element('dhvc_woo_product_page_rating','dhvc_woo_product_page_rating',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/rating');
		cornerstone_register_element('dhvc_woo_product_page_price','dhvc_woo_product_page_price',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/price');
		cornerstone_register_element('dhvc_woo_product_page_excerpt','dhvc_woo_product_page_excerpt',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/excerpt');
		cornerstone_register_element('dhvc_woo_product_page_description','dhvc_woo_product_page_description',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/description');
		cornerstone_register_element('dhvc_woo_product_page_additional_information','dhvc_woo_product_page_additional_information',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/additional_information');
		cornerstone_register_element('dhvc_woo_product_page_add_to_cart','dhvc_woo_product_page_add_to_cart',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/add_to_cart');
		cornerstone_register_element('dhvc_woo_product_page_meta','dhvc_woo_product_page_meta',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/meta');
		cornerstone_register_element('dhvc_woo_product_page_sharing','dhvc_woo_product_page_sharing',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/sharing');
		cornerstone_register_element('dhvc_woo_product_page_data_tabs','dhvc_woo_product_page_data_tabs',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/data_tabs');
		cornerstone_register_element('dhvc_woo_product_page_reviews','dhvc_woo_product_page_reviews',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/reviews');
		cornerstone_register_element('dhvc_woo_product_page_upsell_products','dhvc_woo_product_page_upsell_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/upsell_products');
		cornerstone_register_element('dhvc_woo_product_page_related_products','dhvc_woo_product_page_related_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/related_products');
		cornerstone_register_element('dhvc_woo_product_page_custom_field','dhvc_woo_product_page_custom_field',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/custom_field');
		if (defined ( 'YITH_WCWL' )) 
			cornerstone_register_element('dhvc_woo_product_page_wishlist','dhvc_woo_product_page_wishlist',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/wishlist');
		if (class_exists ( 'acf' ))
			cornerstone_register_element('dhvc_woo_product_page_acf_field','dhvc_woo_product_page_acf_field',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/acf_field');
		//Default WooCommerce Shortcode
		cornerstone_register_element('dhvc_woo_product_page_breadcrumb','dhvc_woo_product_page_breadcrumb',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/breadcrumb');
		cornerstone_register_element('dhvc_woo_product_page_cart','dhvc_woo_product_page_cart',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/cart');
		cornerstone_register_element('dhvc_woo_product_page_checkout','dhvc_woo_product_page_checkout',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/checkout');
		cornerstone_register_element('dhvc_woo_product_page_order_tracking','dhvc_woo_product_page_order_tracking',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/order_tracking');
		cornerstone_register_element('dhvc_woo_product_page_my_account','dhvc_woo_product_page_my_account',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/my_account');
		cornerstone_register_element('dhvc_woo_product_page_product_category','dhvc_woo_product_page_product_category',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/product_category');
		cornerstone_register_element('dhvc_woo_product_page_product_categories','dhvc_woo_product_page_product_categories',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/product_categories');
		cornerstone_register_element('dhvc_woo_product_page_recent_products','dhvc_woo_product_page_recent_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/recent_products');
		cornerstone_register_element('dhvc_woo_product_page_products','dhvc_woo_product_page_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/products');
		cornerstone_register_element('dhvc_woo_product_page_sale_products','dhvc_woo_product_page_sale_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/sale_products');
		cornerstone_register_element('dhvc_woo_product_page_best_selling_products','dhvc_woo_product_page_best_selling_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/best_selling_products');
		cornerstone_register_element('dhvc_woo_product_page_top_rated_products','dhvc_woo_product_page_top_rated_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/top_rated_products');
		cornerstone_register_element('dhvc_woo_product_page_featured_products','dhvc_woo_product_page_featured_products',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/featured_products');
		cornerstone_register_element('dhvc_woo_product_page_shop_messages','dhvc_woo_product_page_shop_messages',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/shop_messages');
		cornerstone_register_element('dhvc_woo_product_page_product_attribute','dhvc_woo_product_page_product_attribute',DHCS_WOO_PAGE_CS_ELEMENT_DIR.'/product_attribute');
	}
	public function icon_map($icon_map){
		$icon_map['wc-single-product'] = DHVC_WOO_PAGE_DIR . '/assets/images/icons.svg';
		return $icon_map;
	}
}
new DHVC_Woo_Page_Cornerstone();
