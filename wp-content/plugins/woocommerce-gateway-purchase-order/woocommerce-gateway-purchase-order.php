<?php
/**
 * Plugin Name: WooCommerce Purchase Order Payment Gateway
 * Plugin URI: http://woothemes.com/products/woocommerce-gateway-purchase-order/
 * Description: Receive payments via purchase order with Woocommerce.
 * Version: 1.1.3
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Requires at least: 4.1.0
 * Tested up to: 4.1.0
 *
 * Text Domain: woocommerce-gateway-purchase-order
 * Domain Path: /languages/
 *
 * Originally developed, and sold to WooThemes in it's original state, by Viren Bohra ( http://enticesolution.com/ ).
 *
 * @package WooCommerce_Gateway_Purchase_Order
 * @category Core
 * @author Matty Cohen
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '573a92318244ece5facb449d63e74874', '478542' );

/**
 * Initialise the payment gateway.
 * @since  1.0.0
 * @return void
 */
function woocommerce_gateway_purchase_order_init () {
	// If we don't have access to the WC_Payment_Gateway class, get out.
	if( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	add_filter('woocommerce_payment_gateways', 'woocommerce_gateway_purchase_order_register_gateway' );

	// Localisation
	load_plugin_textdomain( 'woocommerce-gateway-purchase-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Additional admin screen logic.
	require_once( 'classes/class-woocommerce-gateway-purchase-order-admin.php' );
	Woocommerce_Gateway_Purchase_Order_Admin();
} // End woocommerce_gateway_purchase_order_init()
add_action( 'plugins_loaded', 'woocommerce_gateway_purchase_order_init', 0 );

/**
 * Register this payment gateway within WooCommerce.
 * @access public
 * @since  1.0.0
 * @param  array $methods The array of registered payment gateways.
 * @return array          The modified array of registered payment gateways.
 */
function woocommerce_gateway_purchase_order_register_gateway ( $methods ) {
	require_once( 'classes/class-woocommerce-gateway-purchase-order.php' );

	$methods[] = 'Woocommerce_Gateway_Purchase_Order';
	return $methods;
} // End woocommerce_gateway_purchase_order_register_gateway()
?>
