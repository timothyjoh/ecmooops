<?php
/*
* Plugin Name: WooCommerce Single Product Page Builder
* Plugin URI: http://sitesao.com/
* Description: Woocommerce single product page builder for Visual Composer plugin and Cornerstone plugin
* Version: 4.0.3
* Author: SiteSao Team
* Author URI: http://sitesao.com/
* License: License GNU General Public License version 2 or later;
* Copyright 2013  SiteSao
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!defined('DHVC_WOO_PAGE'))
	define('DHVC_WOO_PAGE','dhvc-woocommerce-page');

if(!defined('DHVC_WOO_PAGE_VERSION'))
	define('DHVC_WOO_PAGE_VERSION','4.0.3');

if(!defined('DHVC_WOO_PAGE_URL'))
	define('DHVC_WOO_PAGE_URL',untrailingslashit( plugins_url( '/', __FILE__ ) ));

if(!defined('DHVC_WOO_PAGE_DIR'))
	define('DHVC_WOO_PAGE_DIR',untrailingslashit( plugin_dir_path( __FILE__ ) ));


if(!class_exists('DHVC_Woo_Page')):

class DHVC_Woo_Page{
	
	public function __construct(){
		add_action( 'plugins_loaded', array($this,'plugins_loaded'), 9 );
	}
	
	public function plugins_loaded(){
		
		if(!function_exists('is_plugin_active'))
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // Require plugin.php to use is_plugin_active() below
		
		$editor = false;
		if ( is_plugin_active( 'woocommerce/woocommerce.php' )) {
			if(is_plugin_active('cornerstone/cornerstone.php')){
				$editor = true;
				require_once DHVC_WOO_PAGE_DIR.'/includes/cornerstone.php';
			}
			if(defined('WPB_VC_VERSION')){
				$editor = true;
				require_once DHVC_WOO_PAGE_DIR.'/includes/vc.php';
			}
		}else{
			add_action('admin_notices', array(&$this,'woocommerce_notice'));
			return ;
		}
		if(!$editor){
			add_action('admin_notices', array(&$this,'notice'));
			return;
		}
		
		require_once DHVC_WOO_PAGE_DIR.'/includes/functions.php';
		require_once DHVC_WOO_PAGE_DIR.'/includes/post-types.php';
		require_once DHVC_WOO_PAGE_DIR.'/includes/shortcode.php';
		
		add_action('init',array(&$this,'init'));
	}
	
	public function init(){
		
		load_plugin_textdomain( DHVC_WOO_PAGE, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		wp_register_style('dhvc-woo-page-chosen', DHVC_WOO_PAGE_URL.'/assets/css/chosen.min.css');
		
		
		
		if(is_admin()):
			require_once DHVC_WOO_PAGE_DIR.'/includes/admin.php';
		else:
			add_action( 'template_redirect', array( &$this, 'get_register_single_product_template' ) );
			add_action( 'template_redirect', array( &$this, 'register_assets' ) );
			add_action(	'wp_enqueue_scripts', array(&$this, 'frontend_assets'));
			add_filter(	'wc_get_template_part', array(&$this,'wc_get_template_part'),50,3);
			add_filter(	'woocommerce_locate_template', array(&$this,'woo_adon_plugin_template'),1,3);
			add_action( 'admin_bar_menu', array(&$this,'adminBarEditLink'), 1000 );
			if(apply_filters('dhvc_woocommerce_page_use_custom_single_product_template',false))
				add_filter( 'template_include', array( &$this, 'template_loader' ),50 );
		endif;

	}
	
	public function register_assets(){
		wp_register_style('dhvc-woocommerce-page-awesome', DHVC_WOO_PAGE_URL.'/assets/fonts/awesome/css/font-awesome.min.css',array(),'4.0.3');
		wp_register_style('dhvc-woocommerce-page', DHVC_WOO_PAGE_URL.'/assets/css/style.css',array(),DHVC_WOO_PAGE_VERSION);
	}
	
	public function frontend_assets(){
		wp_enqueue_style('js_composer_front');
		wp_enqueue_style('js_composer_custom_css');
		wp_enqueue_style('dhvc-woocommerce-page-awesome');
		wp_enqueue_style('dhvc-woocommerce-page');
	}
	
	
	public function get_register_single_product_template(){
		global $post;
		$product_template_id = dhvc_woo_product_page_get_custom_template($post);
		do_action('dhvc_woocommerce_page_register_single',$product_template_id);
		return $product_template_id;
	}
	
	public function template_loader( $template ) {
		if ( is_singular('product')) {
			$find = array();
			$dhvc_single_product_template_id = $this->get_register_single_product_template();
			$file 	= 'single-product.php';
			$find[] = 'dhvc-woocommerce-page/'.$file;
			if($dhvc_single_product_template = get_post($dhvc_single_product_template_id)){
				$template       = locate_template( $find );
				$status_options = get_option( 'woocommerce_status_options', array() );
				if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) )
					$template = DHVC_WOO_PAGE_DIR . '/templates/' . $file;
					
				return $template;
			}
		}
		return $template;
	}
	
	public function wc_get_template_part($template, $slug, $name){
		global $post,$dhvc_single_product_template;
		$dhvc_single_product_template_id = $this->get_register_single_product_template();
		if($slug === 'content' && $name === apply_filters('dhvc_woocommerce_page_single_product_temp_name', 'single-product')){
			do_action('dhvc_woocommerce_page_before_override');
			
			$file 	= 'content-single-product.php';
			$find[] = 'dhvc-woocommerce-page/' . $file;
			if(!empty($dhvc_single_product_template_id)){
				if($wpb_custom_css = get_post_meta( $dhvc_single_product_template_id, '_wpb_post_custom_css', true )){
					echo '<style type="text/css">'.$wpb_custom_css.'</style>';
				}
				if($wpb_shortcodes_custom_css = get_post_meta( $dhvc_single_product_template_id, '_wpb_shortcodes_custom_css', true )){
					echo '<style type="text/css">'.$wpb_shortcodes_custom_css.'</style>';
				}
				$dhvc_single_product_template = get_post($dhvc_single_product_template_id);
				if(class_exists('Ultimate_VC_Addons')){
					$backup_post = $post;
					$post  = $dhvc_single_product_template;
					$Ultimate_VC_Addons = new Ultimate_VC_Addons;
					$Ultimate_VC_Addons->aio_front_scripts();
					$post = $backup_post;
				}
				if($dhvc_single_product_template){
					$template       = locate_template( $find );
					if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) )
						$template = DHVC_WOO_PAGE_DIR . '/templates/' . $file;
						
					return $template;
				}
			}
			do_action('dhvc_woocommerce_page_after_override');
		}
		return $template;
	}


	//adds override templates for Woo elements
	public function woo_adon_plugin_template( $template, $template_name, $template_path ) {
		global $woocommerce;
		$_template = $template;
		if ( ! $template_path ) 
			$template_path = $woocommerce->template_url;

			$plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/templates/woocommerce/';

			// Look within passed path within the theme - this is priority
			$template = locate_template(
				array(
					$template_path . $template_name,
					$template_name
				)
			);

			if( ! $template && file_exists( $plugin_path . $template_name ) )
			$template = $plugin_path . $template_name;

			if ( ! $template )
			$template = $_template;
			return $template;
	 }
	
	public function notice(){
		$plugin = get_plugin_data(__FILE__);
		echo '<div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431?ref=Sitesao" target="_blank">Visual Composer</a></strong> plugin or <a href="http://codecanyon.net/item/cornerstone-the-wordpress-page-builder/15518868?ref=Sitesao" target="_blank">Cornerstone</a> plugin to be installed and activated on your site.', DHVC_WOO_PAGE), $plugin['Name']) . '</p>
			  </div>';
	}
	
	public function woocommerce_notice(){
		$plugin = get_plugin_data(__FILE__);
		echo '
			  <div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a></strong> plugin to be installed and activated on your site.', DHVC_WOO_PAGE), $plugin['Name']) . '</p>
			  </div>';
	}
	
	public function adminBarEditLink($wp_admin_bar){
		global $post;
		if ( ! is_object( $wp_admin_bar ) ) {
			global $wp_admin_bar;
		}
		$product_template_id = dhvc_woo_product_page_get_custom_template($post);
		if ( is_singular('product') && !empty($product_template_id) ) {
			$wp_admin_bar->add_menu( array(
				'id' => 'dhvc_woo_product_page-admin-bar-link',
				'title' => __( 'Edit Product Template', DHVC_WOO_PAGE ),
				'href' => get_edit_post_link($product_template_id),
				'meta' => array( 'target' => '_blank' ),
			) );
		}
	}


}

new DHVC_Woo_Page();

endif;