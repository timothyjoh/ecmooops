<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/dhvc-woocommerce-page/content-single-product.php
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked wc_print_notices - 10
	 */
	 do_action( 'woocommerce_before_single_product' );

	 if ( post_password_required() ) {
	 	echo get_the_password_form();
	 	return;
	 }
	 $class = 'dhvc-woocommerce-page';
	 if(dhvc_woo_product_page_is_jupiter_theme())
	 	$class .=' mk-product style-default';
	 
	 $class = apply_filters('dhvc_woocommerce_page_class',$class);
?>

<div id="product-<?php the_ID(); ?>" <?php post_class($class); ?>>

	<?php the_product_page_content();?>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
