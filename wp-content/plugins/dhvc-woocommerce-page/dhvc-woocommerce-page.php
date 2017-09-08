<?php
/*
* Plugin Name: WOO Product Page for Visual Composer
* Plugin URI: http://getextension.net/
* Description: WOO single product page builder for Visual Composer
* Version: 1.8.4
* Author: DHZoanku
* Author URI: http://getextension.net/
* License: License GNU General Public License version 2 or later;
* Copyright 2013  DH Zoanku
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!defined('DHVC_WOO_PAGE'))
	define('DHVC_WOO_PAGE','dhvc-woocommerce-page');

if(!defined('DHVC_WOO_PAGE_VERSION'))
	define('DHVC_WOO_PAGE_VERSION','1.8.4');

if(!defined('DHVC_WOO_PAGE_URL'))
	define('DHVC_WOO_PAGE_URL',untrailingslashit( plugins_url( '/', __FILE__ ) ));

if(!defined('DHVC_WOO_PAGE_DIR'))
	define('DHVC_WOO_PAGE_DIR',untrailingslashit( plugin_dir_path( __FILE__ ) ));

if (!function_exists('dhwc_is_active')){
	/**
	 * Check woocommerce plugin is active
	 *
	 * @return boolean .TRUE is active
	 */
	function dhwc_is_active(){
		
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() )
			$active_plugins = array_merge($active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

if(!class_exists('DHVC_Woo_Page')):


global $product_page;

class DHVC_Woo_Page{
	
	public function __construct(){
		add_action('init',array(&$this,'init'));
		
	}
	
	public function init(){
		load_plugin_textdomain( DHVC_WOO_PAGE, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		wp_register_style('dhvc-woo-page-chosen', DHVC_WOO_PAGE_URL.'/assets/css/chosen.min.css');
		
		if ( ! defined( 'WPB_VC_VERSION' ) ) {
			add_action('admin_notices', array(&$this,'notice'));
			return;
		}
		if(!dhwc_is_active()){
			add_action('admin_notices', array(&$this,'woocommerce_notice'));
			return;
		}
		

		require_once DHVC_WOO_PAGE_DIR.'/includes/shortcode.php';
		
		$params_script = DHVC_WOO_PAGE_URL.'/assets/js/params.js';
		add_shortcode_param ( 'dhvc_woo_product_page_field_categories', 'dhvc_woo_product_page_setting_field_categories',$params_script);
		add_shortcode_param ( 'dhvc_woo_product_page_field_products_ajax', 'dhvc_woo_product_page_setting_field_products_ajax');
		
		require_once DHVC_WOO_PAGE_DIR.'/includes/functions.php';
		require_once DHVC_WOO_PAGE_DIR.'/includes/map.php';
		
		if(is_admin()):
			require_once DHVC_WOO_PAGE_DIR.'/includes/admin.php';
		else:
			add_action('wp_enqueue_scripts', array(&$this, 'frontend_assets'));
			add_filter('wc_get_template_part', array(&$this,'wc_get_template_part'),50,3);
		endif;

	}
	
	public function frontend_assets(){
		wp_enqueue_style('js_composer_front');
		wp_enqueue_style('js_composer_custom_css');
		wp_register_style('dhvc-woocommerce-page-awesome', DHVC_WOO_PAGE_URL.'/assets/fonts/awesome/css/font-awesome.min.css',array(),'4.0.3');
		wp_register_style('dhvc-woocommerce-page', DHVC_WOO_PAGE_URL.'/assets/css/style.css',array(),DHVC_WOO_PAGE_VERSION);
		wp_enqueue_style('dhvc-woocommerce-page-awesome');
		wp_enqueue_style('dhvc-woocommerce-page');
	}
	
	public function wc_get_template_part($template, $slug, $name){
		global $post,$product_page;
		if($slug === 'content' && $name === 'single-product'){
			
			$product_template_id = 0;
			if($dhvc_woo_page_product = get_post_meta($post->ID,'dhvc_woo_page_product',true)):
				$product_template_id = $dhvc_woo_page_product;
			else:
				$terms = wp_get_post_terms( $post->ID, 'product_cat' );
				foreach ( $terms as $term ):
					if($dhvc_woo_page_cat_product = get_woocommerce_term_meta($term->term_id,'dhvc_woo_page_cat_product',true)):
						$product_template_id = $dhvc_woo_page_cat_product;
					endif;
				endforeach;
			endif;
			
			
			$file 	= 'content-single-product.php';
			$find[] = 'dhvc-woocommerce-page/' . $file;
			if(!empty($product_template_id)){
				if($wpb_custom_css = get_post_meta( $product_template_id, '_wpb_post_custom_css', true )){
					echo '<style type="text/css">'.$wpb_custom_css.'</style>';
				}
				$product_page = get_post($product_template_id);
				if($product_page){
					$template       = locate_template( $find );
					if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) )
						$template = $this->get_plugin_dir() . '/templates/' . $file;
						
					return $template;
				}
			}
		}
		return $template;
	}
	
	public function notice(){
		$plugin = get_plugin_data(__FILE__);
		echo '
			  <div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/1gKaeh5" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', DHVC_WOO), $plugin['Name']) . '</p>
			  </div>';
	}
	
	public function woocommerce_notice(){
		$plugin = get_plugin_data(__FILE__);
		echo '
			  <div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a></strong> plugin to be installed and activated on your site.', DHVC_WOO), $plugin['Name']) . '</p>
			  </div>';
	}
	
	public function get_plugin_url(){
		return DHVC_WOO_PAGE_URL;
	}
	
	public function get_plugin_dir(){
		return DHVC_WOO_PAGE_DIR;
	}
}

new DHVC_Woo_Page();

endif;