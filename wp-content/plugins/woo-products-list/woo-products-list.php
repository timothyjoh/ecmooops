<?php
/**
  * Plugin Name: Woocommerce Products List
* Plugin URI: https://codecanyon.net/item/woocommerce-products-list-pro/17893660
* Description: Plugin to list all your Woocommerce products
* Version: 1.1.1
* Author: Spyros Vlachopoulos
* Author URI: http://www.nitroweb.gr
* License: GPL2
* Text Domain: wcplpro
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'WCPLPRO_DIR',             dirname( __FILE__ ) );
define( 'WCPLPRO_URI',             rtrim(plugin_dir_url( __FILE__ ), '/') );

add_action('init', 'wcplpro_StartSession');
function wcplpro_StartSession() {
  if ( wcplpro_is_session_started() === FALSE ) { session_start(); }
}

add_action('wp_logout', 'wcplpro_EndSession');
add_action('wp_login', 'wcplpro_EndSession');

function wcplpro_EndSession() {
  if ( wcplpro_is_session_started() === TRUE ) {
    session_destroy ();
  }
}


// Load plugin textdomain
add_action( 'plugins_loaded', 'wcplpro_load_textdomain' );
function wcplpro_load_textdomain() {
  load_plugin_textdomain( 'wcplpro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}



function wcplpro_activate() {
  
  $wcplpro_order = array (
    'wcplpro_thumb' => __('Thumbnail', 'wcplpro'),
    'wcplpro_sku' => __('SKU', 'wcplpro'),
    'wcplpro_title' => __('Product Title', 'wcplpro'),
    'wcplpro_offer' => __('Offer Image', 'wcplpro'),
    'wcplpro_categories' => __('Categories', 'wcplpro'),
    'wcplpro_tags' => __('Tags', 'wcplpro'),
    'wcplpro_stock' => __('Stock', 'wcplpro'),
    'wcplpro_gift' => __('Gift Wrap', 'wcplpro'),
    'wcplpro_wishlist' => __('Wishlist', 'wcplpro'),
    'wcplpro_qty' => __('Quantity', 'wcplpro'),
    'wcplpro_price' => __('Price', 'wcplpro'),
    'wcplpro_total' => __('Total', 'wcplpro'),
    'wcplpro_cart' => __('Add to Cart Button', 'wcplpro')
  );
  
  
  // set options only if they do not exist
  if (get_option('wcplpro_title') === false) {
    update_option( 'wcplpro_title', 1 );
  }
  if (get_option('wcplpro_thumb') === false) {
    update_option( 'wcplpro_thumb', 1 );
  }
  if (get_option('wcplpro_thumb_size') === false) {
    update_option( 'wcplpro_thumb_size', 80 );
  }
  if (get_option('wcplpro_price') === false) {
    update_option( 'wcplpro_price', 1 );
  }
  if (get_option('wcplpro_total') === false) {
    update_option( 'wcplpro_total', 0 );
  }
  if (get_option('wcplpro_cart') === false) {
    update_option( 'wcplpro_cart', 1 );
  }
  if (get_option('wcplpro_qty') === false) {
    update_option( 'wcplpro_qty', 1 );
  }
  if (get_option('wcplpro_order') === false) {
    update_option( 'wcplpro_order', $wcplpro_order );
  }
  if (get_option('wcplpro_head') === false) {
    update_option( 'wcplpro_head', 1 );
  }
  if (get_option('wcplpro_sorting') === false) {
    update_option( 'wcplpro_sorting', 1 );
  }
  if (get_option('wcplpro_default_qty') === false) { 
    update_option('wcplpro_default_qty', 1); 
  }
  if (get_option('wcplpro_qty_control') === false) { 
    update_option('wcplpro_qty_control', 0); 
  }
  if (get_option('wcplpro_globalposition') === false) { 
    update_option('wcplpro_globalposition', 'bottom'); 
  }
  if (get_option('wcplpro_globalcart') === false) { 
    update_option('wcplpro_globalcart', 0); 
  }
  if (get_option('wcplpro_desc_inline') === false) { 
    update_option('wcplpro_desc_inline', '0'); 
  }
  if (get_option('wcplpro_weight') === false) { 
    update_option('wcplpro_weight', '0'); 
  }
  if (get_option('wcplpro_dimensions') === false) { 
    update_option('wcplpro_dimensions', '0'); 
  }
   
}
register_activation_hook( __FILE__, 'wcplpro_activate' );


include ('grid_options_page.php');
include ('product_options_meta.php');
include ('editor_plugins.php');


function wcplpro_sc_attr() {
  
  $sc_attr = array(
    'keyword'               => '',
    'categories_exc'        => '',
    'categories_inc'        => '',
    'tag_exc'               => '',
    'tag_inc'               => '',
    'posts_inc'             => '',
    'posts_exc'             => '',
    'categories'            => '',
    'tags'                  => '',
    'sku'                   => '',
    'title'                 => '',
    'thumb'                 => '',
    'thumb_size'            => '',
    'stock'                 => '',
    'hide_zero'             => '',
    'hide_outofstock'       => '',
    'zero_to_out'           => '',
    'price'                 => '',
    'total'                 => '',
    'offer'                 => '',
    'image'                 => '',
    'qty'                   => '',
    'default_qty'           => '',
    'qty_control'           => '',
    'cart'                  => '',
    'globalcart'            => '',
    'globalposition'        => '',
    'global_status'         => '',
    'custommeta'            => '',
    'metafield'             => '',
    'attributes'            => '',
    'wishlist'              => '',
    'gift'                  => '',
    'ajax'                  => '',
    'desc'                  => '',
    'weight'                => '',
    'dimensions'            => '',
    'desc_inline'           => '',
    'head'                  => '',
    'sorting'               => '',
    'order'                 => '',
    'orderby'               => '',
    'order_direction'       => '',
    'date'                  => '',
    'echo'                  => 0,
    'category_title'        => 0,
    'limit'                 => 0,
    'wcplid'                => '',
    'quickview'             => '',
    'pagination'            => '',
    'posts_per_page'        => '',
    'filter_cat'            => '',
    'filter_tag'            => '',
    'filters_position'      => ''
  );
  
  
  return apply_filters('wcplpro_sc_attr', $sc_attr);
  
}


// create the shortcode
function wcplpro_func( $atts ) {
    $a = shortcode_atts(wcplpro_sc_attr(), $atts );

    // disable echo for shortcode
    $a['echo'] = 0;
    
    return (wc_products_list_pro($a));
}
add_shortcode( 'wcplpro', 'wcplpro_func' );


function wc_products_list_pro($allsets) {
  global $product, $post, $woocommerce, $wpdb;
  
  $current_post = $post;
  
  $out = '';
  
  $sc_attr = wcplpro_sc_attr();
  
  
  // get values from shortcode
  if ($allsets) {
    foreach($sc_attr as $key => $attr) {
      ${'wcplpro_'.$key} = $allsets[$key];
    }
  } else{
    foreach($sc_attr as $key => $attr) {
      ${'wcplpro_'.$key} = null;
    }
  }
  

  // get default value if attribute is not set
  foreach($sc_attr as $key => $attr) {
    ${'wcplpro_'.$key} = (${'wcplpro_'.$key} == null         ? get_option('wcplpro_'.$key) : ${'wcplpro_'.$key});
  }
    
  // set default page to 1
  $wcplpro_paged = 1;
  
  // gift wrap option
  $default_message            = '{checkbox} '. sprintf( __( 'Gift wrap this item for %s?', 'woocommerce-product-gift-wrap' ), '{price}' );
  $gift_wrap_enabled          = get_option( 'product_gift_wrap_enabled' ) == 'yes' ? true : false;
  $gift_wrap_cost             = get_option( 'product_gift_wrap_cost', 0 );
  $product_gift_wrap_message  = get_option( 'product_gift_wrap_message');
  
  if ( ! $product_gift_wrap_message ) {
    $product_gift_wrap_message = $default_message;
  }
  
  
  // set list id
  $vtrand = $wcplpro_wcplid;
  $useruniq = isset($_COOKIE['PHPSESSID']) ? substr($_COOKIE['PHPSESSID'], 0, 10) : uniqid();
  
  // load quickview
  if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview)) {
    $YITH_WCQV_Frontend = YITH_WCQV_Frontend();
  }
  
  
  $query_args = array(
    'post_type'       => 'product',
    'nopaging'        => true,
    'posts_per_page'  => -1 
  );
  
  // add search term
  if (isset($wcplpro_keyword) && $wcplpro_keyword != null) {
    $query_args['s'] = $wcplpro_keyword;
  }
  // include specific posts only
  if (isset($wcplpro_posts_inc) && $wcplpro_posts_inc != null) {
    $query_args['post__in'] = explode(',', str_replace(' ', '', $wcplpro_posts_inc));
  }
  // exclude posts by ID
  if (isset($wcplpro_posts_exc) && $wcplpro_posts_exc != null) {
    $query_args['post__not_in'] = explode(',', str_replace(' ', '', $wcplpro_posts_exc));
  }
  // add order_direction
  if (isset($wcplpro_order_direction) && $wcplpro_order_direction != null) {
    $query_args['order'] = $wcplpro_order_direction;
  }
  if (isset($wcplpro_orderby) && $wcplpro_orderby != null) {
    
    if ($wcplpro_orderby == 'title' || $wcplpro_orderby == 'date') {
      $query_args['orderby'] = $wcplpro_orderby;
    } else {
      $query_args['orderby'] = 'meta_value_num';
      $query_args['meta_key'] = $wcplpro_orderby;
    }
  }
  
  // add date filter
  if (isset($wcplpro_date) && $wcplpro_date != null) {
    
    $date_array = $date_query = array();
    $date_array = explode('/', $wcplpro_date);
    
    if (isset($date_array[0])) { $date_query['year'] = $date_array[0]; }
    if (isset($date_array[1])) { $date_query['month'] = $date_array[1]; }
    if (isset($date_array[2])) { $date_query['day'] = $date_array[2]; }
    
    $query_args['date_query'] = array($date_query);
  }
    
  // add list filters via request
  if (
    $wcplpro_wcplid != '' 
    && $wcplpro_wcplid !== null 
    && !is_admin() 
    && isset($_REQUEST['wcpl']) 
    && $_REQUEST['wcpl'] == 1 
    && isset($_REQUEST['wcplid']) 
    && $_REQUEST['wcplid'] != ''
    && $_REQUEST['wcplid'] == $wcplpro_wcplid
    ) 
  {
    // set new request if exists
    if (isset($_REQUEST['wcpl_product_cat']) && $_REQUEST['wcpl_product_cat'] != '') {
      $wcplpro_categories_inc = esc_sql($_REQUEST['wcpl_product_cat']);
      set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat', $wcplpro_categories_inc, 3600);
    }
    // unset the cookie if the request exists but empty
    if (isset($_REQUEST['wcpl_product_cat']) && $_REQUEST['wcpl_product_cat'] == '') {
      delete_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat');
    }
    
    
    // set new request if exists
    if (isset($_REQUEST['wcpl_product_tag']) && $_REQUEST['wcpl_product_tag'] != '') {
      $wcplpro_tag_inc = esc_sql($_REQUEST['wcpl_product_tag']);
      set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag', $wcplpro_tag_inc, 3600);
    }
    // unset the cookie if the request exists but empty
    if (isset($_REQUEST['wcpl_product_tag']) && $_REQUEST['wcpl_product_tag'] == '') {
      delete_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag');
    }
    
    // set new request if exists
    if (isset($_REQUEST['paged']) && $_REQUEST['paged'] != '') {
      $wcplpro_paged = esc_sql($_REQUEST['paged']);
    }
    if (is_front_page() && get_query_var( 'page') > 1) {
      $wcplpro_paged = get_query_var( 'page');
    }
    
    if ($wcplpro_paged > 1) {
      set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag', $wcplpro_paged, 3600);
    } else {
    // unset the cookie if the request exists but empty
      delete_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag');
    }
  }
  
  // preserve the request and set it only for the specific list
  if (get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat')) {
    $wcplpro_categories_inc = get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat');
  }
  
  
  // preserve the request and set it only for the specific list
  if (get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag')) {
    $wcplpro_tag_inc = get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag');
  }
  
  // preserve the page number for this specific list
  if (get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag')) {
    $wcplpro_paged = get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag');
  }
  
  
  // add categories filters
  if ($wcplpro_categories_exc != null || $wcplpro_categories_inc != null || $wcplpro_tag_exc != null || $wcplpro_tag_inc != null) {
    $query_args['tax_query'] = array('relation' => 'AND');
  }
  if ($wcplpro_categories_exc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_categories_exc) ? $wcplpro_categories_exc : explode(',', $wcplpro_categories_exc )),
			'operator' => 'NOT IN',
    );
  }
  
  if ($wcplpro_categories_inc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_categories_inc) ? $wcplpro_categories_inc : explode(',', $wcplpro_categories_inc )),
			'operator' => 'IN',
    );
  }
  if ($wcplpro_tag_exc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_tag',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_tag_exc) ? $wcplpro_tag_exc : explode(',', $wcplpro_tag_exc )),
			'operator' => 'NOT IN',
    );
  }
  if ($wcplpro_tag_inc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_tag',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_tag_inc) ? $wcplpro_tag_inc : explode(',', $wcplpro_tag_inc )),
			'operator' => 'IN',
    );
  }
  
  
  
  
  $query_args = apply_filters('wcplpro_query_args', $query_args);
  
  $query = new WP_Query( $query_args );
  
  
  $pagination_posts_ids = array();
  if ($wcplpro_pagination != 'no' && $wcplpro_pagination !== '' ) {
    if ( $query->have_posts()) {
      
      // loop the products
      while ( $query->have_posts() ) {
        $query->the_post();
        
        $product = new WC_Product(get_the_ID());
        $product_meta = get_post_meta(get_the_ID());
        
        $terms = get_the_terms($product->id, 'product_type');
        $product_type = !empty($terms) ? sanitize_title(current($terms)->name) : 'simple';

        $product_stock = $product->get_stock_quantity();
        $product_avail = $product->get_availability();
        
        if (get_post_meta($product->id, 'wcplpro_remove_product', true) == 1) { continue; }
        if (!($product_stock > 0) && $wcplpro_hide_outofstock == 1 && !$product->is_in_stock()) { continue; }
        if (wc_format_decimal($product->get_display_price(), 2) == '0.00' && $wcplpro_hide_zero == 1) { continue; }
        
        
        $pagination_posts_ids[] = get_the_ID();
        
      }
      
    }
    
    if (!empty($pagination_posts_ids)) {
      
      global $wp_query;
      
      $query_args = array(
        'post_type'       => 'product',
        'post__in'        => $pagination_posts_ids,
        'posts_per_page'  => (absint($wcplpro_posts_per_page) > 0 ? $wcplpro_posts_per_page : get_option( 'posts_per_page' ) ),
        'paged'           => (get_query_var('paged')) ? get_query_var('paged') : 1,
        'nopaging'        => false,
        'orderby'         => 'post__in' 
      );
      
      if (is_front_page()) {
        $query_args['paged'] = (get_query_var('page')) ? get_query_var('page') : 1;
      }
      
      
      $query = new WP_Query( $query_args );

    }
    
    
  }
  
  
  // The Loop
  if ( $query->have_posts()) {
    
    // get header names
    $headenames = wcplpro_fields_func();
    $custom_meta_header = array(); // array to hole the header names of custom meta
    
    $anyextraimg = 0;
    $anydescription = 0;
    $anydimension = 0;
    $anyweight = 0;
    $head = '';
    
    
    
    
    ob_start();
    do_action('woocommerce_before_add_to_cart_form', $current_post);
    $woocommerce_before_add_to_cart_form = ob_get_clean();
    $out .= $woocommerce_before_add_to_cart_form;
    
    ob_start();
    do_action('wcplpro_before_table', $current_post);
    $wcplpro_before_table = ob_get_clean();
    $out .= $wcplpro_before_table;
    
    $wcplpro_table_class = '';
    ob_start();
    do_action('wcplpro_table_class', $current_post);
    $wcplpro_table_class = ob_get_clean();
    
    
    ob_start();
    do_action('wcplpro_after_filters_top', $current_post);
    $wcplpro_after_filters_top = ob_get_clean();
    
    $sorting_js = apply_filters( 'wcplpro_sorting_js', $wcplpro_sorting, $current_post);
    
    
    $cartredirect = get_option('woocommerce_cart_redirect_after_add');
    
    
    ob_start();
    do_action('wcplpro_before_filters_top', $current_post);
    $wcplpro_before_filters_top = ob_get_clean();
    
    $out .= $wcplpro_before_filters_top;
    
    $out .= '<div class="wcpl_group top">';
    
    // add drops down filters
    if (($wcplpro_filters_position == 'before' || $wcplpro_filters_position == 'both') || ($wcplpro_pagination == 'before' || $wcplpro_pagination == 'both')) {
      
      $out .= wcplpro_filters_form($wcplpro_filter_cat, $wcplpro_filter_tag, get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag'), $wcplpro_wcplid);
      
    }
    
    if ($wcplpro_pagination == 'before' || $wcplpro_pagination == 'both') {
      $out .= wcplpro_pagination($wcplpro_posts_per_page, $query);
    }
    
    $out .= '</div>';
    
    $out .= $wcplpro_after_filters_top;
    
    if (($wcplpro_globalcart == 1 || $wcplpro_globalcart == 2) && ($wcplpro_globalposition == 'top' || $wcplpro_globalposition == 'both')) {
      
      $out .= apply_filters('wcplpro_global_btn', '
        <div class="gc_wrap">
          <a data-position="top" href="#globalcart" class="globalcartbtn submit btn single_add_to_cart_button button alt" data-post_id="gc_'.$current_post->ID .'" id="gc_'. $vtrand .'_top" class="btn button alt">'. __('Add selected to cart', 'wcplpro') .'<span class="vt_products_count"></span></a>
          <span class="added2cartglobal added2cartglobal_'. $vtrand .'">&#10003;</span>
          <span class="vtspinner vtspinner_top vtspinner_'. $vtrand .'"><img src="'. plugins_url('images/spinner.png', __FILE__) .'" width="16" height="16" alt="spinner" /></span>
        </div>
      ', $current_post, 'top', $vtrand);
      
    }
    
    
    // open table
    $out .= '
    <div class="woocommerce wcplprotable_wrap">
    <table 
      id="tb_'. $vtrand .'" 
      class="table wcplprotable shop_table shop_table_responsive '. ($wcplpro_head == 0 ? 'nohead' : 'withhead') .' '. ($sorting_js == 1 ? 'is_sortable' : '') .' '. $wcplpro_table_class .'" 
      data-random="'. $vtrand .'" 
      '. ($sorting_js == 1 ? 'data-sort="yes"' : 'data-sort="no"') .' 
      '. ($wcplpro_ajax == 1 ? 'data-wcplprotable_ajax="1"' : 'data-wcplprotable_ajax="0"').' 
      '. ($cartredirect == 'yes' ? 'data-cartredirect="yes"' : 'data-cartredirect="no"') .' 
      data-globalcart="'. $wcplpro_globalcart .'"
      >
    
      %headplaceholder%
    ';    
    
    $out .= '<tbody>
      ';
    
    
    // loop the products
    while ( $query->have_posts() ) {
      $query->the_post();
      
      $product = new WC_Product(get_the_ID());
      $product_meta = get_post_meta(get_the_ID());
      
      $terms = get_the_terms($product->id, 'product_type');
      $product_type = !empty($terms) ? sanitize_title(current($terms)->name) : 'simple';

      $product_stock = $product->get_stock_quantity();
      $product_avail = $product->get_availability();
      
      if (get_post_meta($product->id, 'wcplpro_remove_product', true) == 1) { continue; }
      
      if (!($product_stock > 0) && $wcplpro_hide_outofstock == 1 && !$product->is_in_stock()) { continue; }
      if (wc_format_decimal($product->get_display_price(), 2) == '0.00' && $wcplpro_hide_zero == 1) { continue; }
      
      $form = '';
  
      $is_wrappable = get_post_meta( $product->id, '_is_gift_wrappable', true );
      
      if ( $is_wrappable == '' && $gift_wrap_enabled ) {
        $is_wrappable = 'yes';
      }
      
      
      ob_start();
      do_action('wcplpro_before_single_row', $product->id, $product);
      $wcplpro_before_single_row = ob_get_clean();
      
      ob_start();
      do_action('wcplpro_inside_add_to_cart_form', $product->id, $product);
      $wcplpro_inside_add_to_cart_form = ob_get_clean();
      
      
      $form = '
		<form action="'. esc_url( $product->add_to_cart_url() ) .'" method="POST" data-product_id="'.  $product->ID .'" id="wcplpro_product_'. $product->ID .'" class="vtajaxform" enctype="multipart/form-data">
			<input type="hidden" name="product_id" value="'. esc_attr( $product->id ) .'" />
			<input type="hidden" name="add-to-cart" value="'. esc_attr( $product->id ) .'" />
      '. $wcplpro_inside_add_to_cart_form .'
      ';
            
      if ($product->is_in_stock() == 1 || $product->backorders_allowed()) {
        $form .= '<input type="hidden" class="hidden_quantity" name="quantity" value="'. ($wcplpro_default_qty != '' ? apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product) : 1) .'" />';
      }
      
      $form .= '<input type="hidden" class="gift_wrap" name="gift_wrap" value="" />';
      

      $out .= $wcplpro_before_single_row .'
          <tr class="product_id_'. $product->ID .' '.$product_avail['class'].'" 
              data-price="'.wc_format_decimal($product->get_display_price(), 2) .'">';
              
      $col_checker = array(); // checks if column has any data
      
      // loop ordered columns
      foreach ($wcplpro_order as $colkey => $col_title) {
        
        $allcolumns = array();
        
        /****************************/
        //categories
        if ($colkey == 'wcplpro_categories' && $wcplpro_categories == 1) {
          
          $col_checker[$colkey] = true;
          
          $allcolumns[$colkey] = '
            <td class="categoriescol"  data-title="'. apply_filters('wcplpro_dl_categories', $headenames[$colkey], $product) .'">
              '. $product->get_categories() .'
            </td>';
        }
        
        
        /****************************/
        //tags
        if ($colkey == 'wcplpro_tags' && $wcplpro_tags == 1) {
          
          $col_checker[$colkey] = true;
          
          $allcolumns[$colkey] = '
            <td class="tagscol"  data-title="'. apply_filters('wcplpro_dl_tags', $headenames[$colkey], $product) .'">
              '. $product->get_tags() .'
            </td>';
        }
        
        
        /****************************/
        //title
        if ($colkey == 'wcplpro_title' && $wcplpro_title == 1) {
          
          $col_checker[$colkey] = true;
          
          ob_start();
          do_action('wcplpro_after_title', $product->id, $product);
          $wcplpro_after_title = ob_get_clean();
          
          ob_start();
          do_action('wcplpro_before_attributes', $product->id, $product);
          $wcplpro_before_attributes = ob_get_clean();
          
          $custom_attributes = $attributes_out = '';
          $custom_attributes = $product->get_attributes();
          
          if ($wcplpro_attributes == 1 && !empty($custom_attributes)){

            foreach ($custom_attributes as $cattr) {
              if ($cattr['is_visible'] == 1 || $cattr['is_variation'] == 1) {
                $attributes_out .= '<div class="wcplpro_attributes"> <span>'.wc_attribute_label($cattr['name'], $product).':</span> '. str_replace(' | ', ', ', $product->get_attribute($cattr['name']).'</div>');
              }
            }
            
            $attributes_out = apply_filters('wcplpro_attributes', $attributes_out, $product);
          }
          
          $allcolumns[$colkey] = '
            <td class="titlecol"  data-title="'. apply_filters('wcplpro_dl_title', $headenames[$colkey], $product) .'">
              <a href="'. get_permalink($product->id)  .'" title="'. $product->get_title() .'">'. $product->get_title() .'</a>
              '. $wcplpro_before_attributes .'
              '. $attributes_out .'
              '. $wcplpro_after_title .'
            </td>';
            
          $allcolumns[$colkey] = apply_filters('wcplpro_colum_title', $allcolumns[$colkey], $product);
        }
        
        /****************************/
        //sku
        if ($colkey == 'wcplpro_sku' && $wcplpro_sku == 1) {
          $col_checker[$colkey] = true;
          $allcolumns[$colkey] = '<td class="skucol" data-title="'. apply_filters('wcplpro_dl_sku', $headenames[$colkey], $product) .'" >'. ($product->sku != '' ? $product->sku : '&nbsp;') .'</td>';
        }
        
        
        
        
        /****************************/
        //custom meta
        if ($colkey == 'wcplpro_custommeta' && $wcplpro_custommeta == 1 && trim($wcplpro_metafield) != '') {
          $col_checker[$colkey] = true;
          
          $wcplpro_metafields = explode(',', $wcplpro_metafield);
          
          $all_meta_columns = array();
          if (!empty($wcplpro_metafields)) {
            foreach ($wcplpro_metafields as $metafield) {
              
              $metafield_array = explode('|', $metafield);
              $metafield_key = $metafield_array[0];
              $metafield_label = isset($metafield_array[1]) ? $metafield_array[1] : $metafield_array[0];
              
              $headenames['wcplpro_meta_'. $metafield_key] = $metafield_label;
              $custom_meta_header['wcplpro_meta_'. $metafield_key] = $metafield_label;
              
              $get_post_meta = '';
              $get_post_meta = get_post_meta( $product->id, $metafield_key, true );
              if (is_array($get_post_meta)) {
                $get_post_meta = implode(', ', $get_post_meta);
              }
              
              $all_meta_columns[] = '
                <td class="metacol meta_'. $metafield_key .'" data-title="'. apply_filters('wcplpro_dl_'. $metafield_key, $headenames['wcplpro_meta_'. $metafield_key], $product) .'" >
                '. apply_filters('wcplpro_post_meta', $get_post_meta, $metafield_key, $product) .'
                </td>';
            }
            
            $all_meta_columns = apply_filters('wcplpro_all_meta_columns', $all_meta_columns, $product);
            $allcolumns[$colkey] = apply_filters('wcplpro_custommeta', implode("\n", $all_meta_columns), $product);
          }
          
        }
        
        
        /****************************/
        //thumb
        if ($colkey == 'wcplpro_thumb' && $wcplpro_thumb == 1) {
          $col_checker[$colkey] = true;
          
          $rowimg = '';
          $var_feat_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->id), array($wcplpro_thumb_size, $wcplpro_thumb_size));
          $rowimgfull = wp_get_attachment_image_src(get_post_thumbnail_id($product->id), 'full');
          if (!empty($var_feat_image)) { 
            $rowimg = $var_feat_image; 
          }
        
          if (isset($rowimg[0])) {
            $allcolumns[$colkey] = '<td class="thumbcol"  data-title="'. apply_filters('wcplpro_dl_thumb', $headenames[$colkey], $product) .'">
              <a href="'. $rowimgfull[0] .'" itemprop="image" class="wcplproimg zoom '. apply_filters( 'wcplpro_thumb_class_filter', 'thumb', $product) .'" title="'. $product->post->post_title .'"  data-rel="prettyPhoto">
                <img src="'. $rowimg[0] .'" alt="'. $product->post->post_title .'" width="'. $rowimg[1] .'" height="'. $rowimg[2] .'" style="max-width: '. $wcplpro_thumb_size.'px; height: auto;" />
              </a>
            </td>';
          } else {
            $allcolumns[$colkey] = '<td class="thumbcol" data-title="'. apply_filters('wcplpro_dl_thumb', $headenames[$colkey], $product) .'">
              '. apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" style="width: '. $wcplpro_thumb_size.'px; height: auto;" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $product->ID ).'
              </td>';
          }

        }
        
        
        /****************************/
        //stock
        if ($colkey == 'wcplpro_stock' && $wcplpro_stock == 1) {
          $col_checker[$colkey] = true;
          $allcolumns[$colkey] = '<td class="stockcol" data-title="'. apply_filters('wcplpro_dl_stock', $headenames[$colkey], $product) .'"><span class="'. $product_avail['class'] .'">'. ($product_avail['availability'] != '' ? $product_avail['availability'] : '&nbsp;') .'</span></td>';
        }
        
        
        /****************************/
        //weight
        if ($colkey == 'wcplpro_weight' && $wcplpro_weight == 1) {
          $col_checker[$colkey] = true;
          $anyweight = 1;
          if ($product->has_weight()) {
            $allcolumns[$colkey] = '
              <td class="weight_col" data-sort-value="'. $product->get_weight() .'" data-title="'. apply_filters('wcplpro_dl_weight', $headenames[$colkey], $product) .'">'. $product->get_weight().($product->has_weight() ? ' '.get_option('woocommerce_weight_unit') : '') .'</td>';
              $col_checker[$colkey] = true;
            } else {
              $allcolumns[$colkey] = '
              <td class="weight_col" data-title="'. apply_filters('wcplpro_dl_weight', $headenames[$colkey], $product) .'">&nbsp;</td>';
            }
        }
        
        
        /****************************/
        //dimensions
        if ($colkey == 'wcplpro_dimensions' && $wcplpro_dimensions == 1) {
          $col_checker[$colkey] = true;
          $wcplpro_dimensions_str = '&nbsp;';
          if ($product->get_dimensions()) {
            $wcplpro_dimensions_str = $product->get_dimensions();
          }
          
          if ($product->has_dimensions()) {
            $anydimension = 1;
          }

          $allcolumns[$colkey] = '
            <td class="dimensions_col" data-title="'. apply_filters('wcplpro_dl_dimensions', $headenames[$colkey], $product) .'">'. $wcplpro_dimensions_str .'</td>';
        }
        
        
        /****************************/
        //offer image
        if ($colkey == 'wcplpro_offer' && $wcplpro_offer == 1 && $wcplpro_image != '' && get_post_meta( $product->id, 'wcplpro_offer_status', true ) != 'disable') {
          $col_checker[$colkey] = true;
          $override_extra_image = (isset($product_meta['wcplpro_override_extra_image']) ? $product_meta['wcplpro_override_extra_image'][0] : null);
          
          if (!empty($override_extra_image)) {
            $allcolumns[$colkey] = '
              <td class="offercol"  data-title="'. apply_filters('wcplpro_dl_offer', $headenames[$colkey], $product) .'">
                <img src="'. $override_extra_image .'" alt="'.  __('offer', 'wcplpro') .'" style="max-width: '. $wcplpro_thumb_size.'px; height: auto;"  />
              </td>';
            $anyextraimg = 1;
          } 
          if ($wcplpro_image !='' && empty($override_extra_image)) {
            $allcolumns[$colkey] = '
              <td class="offercol"  data-title="'. apply_filters('wcplpro_dl_offer', $headenames[$colkey], $product) .'">
                <img src="'. $wcplpro_image .'" alt="'.  __('offer', 'wcplpro') .'" style="max-width: '. $wcplpro_thumb_size.'px; height: auto;" />
              </td>';
            $anyextraimg = 1;
          }        
        }
        
        
        /****************************/
        //quantity
        if ($colkey == 'wcplpro_qty' && $wcplpro_qty == 1) {
          $col_checker[$colkey] = true;
          $wcplpro_qty_step = 1;
          // $wcplpro_qty_step = (isset($product_meta['wcplpro_qty_step']) ? $product_meta['wcplpro_qty_step'][0] : 1);
          // $wcplpro_default_qty = (isset($product_meta['wcplpro_default_qty']) ? $product_meta['wcplpro_default_qty'][0] : 1);
          
          $allcolumns[$colkey] = '
            <td class="qtycol" data-title="'. apply_filters('wcplpro_dl_qty', $headenames[$colkey], $product) .'">';
            
          if ($product->is_in_stock() || $product->backorders_allowed()) {
            
            if ($wcplpro_qty_control == 1) {
            
              $allcolumns[$colkey] .= '
              <div class="qtywrap">
              ';
              
              $allcolumns[$colkey] .= '
              <div class="minusqty qtycontrol">-</div>
              ';
            
            }
            
            $allcolumns[$colkey] .= '
              <input type="number" step="'. apply_filters('wcplpro_qty_step', $wcplpro_qty_step, $product) .'" name="wcplpro_quantity" value="'. ($wcplpro_default_qty != '' ? apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product) : 1) .'" title="Qty" class="input-text qty text" size="4" min="0" '. (intval($product->get_total_stock()) > 0 ? 'max="'. $product->get_total_stock() .'"': '') .'>
            ';
          
            if ($wcplpro_qty_control == 1) {
          
              $allcolumns[$colkey] .= '
                <div class="plusqty">+</div>
              ';
              
              $allcolumns[$colkey] .= '
                </div>
              ';
              
            }
          }
          $allcolumns[$colkey] .= '</td>';
          
        
        }
        
        
        /****************************/
        //gift wrap
        if ($colkey == 'wcplpro_gift' && $wcplpro_gift == 1 && $is_wrappable == 'yes') {
          $col_checker[$colkey] = true;
          $current_value = ! empty( $_REQUEST['gift_wrap'] ) ? 1 : 0;

          $cost = (isset($product_meta['_gift_wrap_cost']) ? $product_meta['_gift_wrap_cost'][0] : $gift_wrap_cost);

          $price_text = $cost > 0 ? woocommerce_price( $cost ) : __( 'free', 'woocommerce-product-gift-wrap' );
          $checkbox   = '<input type="checkbox" class="wcplpro_gift_wrap" name="wcplpro_gift_wrap" value="yes" ' . checked( $current_value, 1, false ) . ' />';
          
          
          $allcolumns[$colkey] = '
          <td class="giftcol" data-title="'. apply_filters('wcplpro_dl_gift', $headenames[$colkey], $value) .'">
            <label>'.  str_replace(array('{price}', '{checkbox}',), array($price_text, $checkbox), $product_gift_wrap_message) .'</label>
          </td>';
        
        }
        
        
        /****************************/
        //yith wishlist
        if ($colkey == 'wcplpro_wishlist' && $wcplpro_wishlist == 1 && defined( 'YITH_WCWL' )) {
          $col_checker[$colkey] = true;
          $url=strtok($_SERVER["REQUEST_URI"],'?');
          parse_str($_SERVER['QUERY_STRING'], $query_string);
          $query_string['add_to_wishlist'] = basename($product->id);
          $rdr_str = http_build_query($query_string);
          
          $wishlist = do_shortcode('[yith_wcwl_add_to_wishlist product_id='. $product->id .' icon="'. (get_option('yith_wcwl_add_to_wishlist_icon') != '' && get_option('yith_wcwl_use_button') == 'yes' ? get_option('yith_wcwl_add_to_wishlist_icon') : 'fa-heart') .'"]');
        
          $allcolumns[$colkey] = '
            <td class="wishcol" data-title="'. apply_filters('wcplpro_dl_wishlist', $headenames[$colkey], $product) .'">
              '. wcplpro_delete_all_between('</i>', '</a>', $wishlist) .'
            </td>';
        
        }
        
        
        /****************************/
        //price
        if ($colkey == 'wcplpro_price' && $wcplpro_price == 1) {
          $col_checker[$colkey] = true;
          $allcolumns[$colkey] = '
            <td class="pricecol" 
              data-title="'. apply_filters('wcplpro_dl_price', $headenames[$colkey], $product) .'" 
              data-price="'.wc_format_decimal($product->get_display_price(), 2) .'" 
              data-sort-value="'. wc_format_decimal($product->get_display_price(), 2) .'">
              '. $product->get_price_html() .'
            </td>';
        
        }
        
        
        /****************************/
        //total
        if ($colkey == 'wcplpro_total' && $wcplpro_total == 1) {
          $col_checker[$colkey] = true;
          $allcolumns[$colkey] = '
            <td class="totalcol" data-title="'. apply_filters('wcplpro_dl_total', $headenames[$colkey], $product) .'" data-sort-value="'. wc_format_decimal($product->get_display_price() * ($wcplpro_default_qty > 0 ? $wcplpro_default_qty : apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product)), 2) .'">
              '. wc_price($product->get_display_price() * ($wcplpro_default_qty > 0 ? $wcplpro_default_qty : apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product))) .'
              '. (get_option('woocommerce_price_display_suffix') != '' ? ' '.get_option('woocommerce_price_display_suffix') : '') .'
            </td>';
        
        }
        
        //add to cart button
        if ($colkey == 'wcplpro_cart') { // && $wcplpro_cart == 1
          $col_checker[$colkey] = true;
          ob_start();
          do_action('woocommerce_add_to_cart_class', $product->id, $product);
          $woocommerce_add_to_cart_class = ob_get_clean();
          
          ob_start();
          do_action('woocommerce_before_add_to_cart_button', $product->id, $product);
          $woocommerce_before_add_to_cart_button = ob_get_clean();
          
          $allcolumns['wcplpro_cart'] = '<td class="cartcol '. ($wcplpro_cart == 0 ? 'wcplprohide' : '') .' '. $woocommerce_add_to_cart_class .'" data-title="">'.$woocommerce_before_add_to_cart_button;
          // if is in stock or backorders are allowed
          if (isset($product_meta['_stock_status']) || $product->backorders_allowed()) {
            
            // if is out of stock and backorder are allowed
            if (
              (isset($product_meta['_stock_status']) && $product_meta['_stock_status'][0] != 'instock' && $product->backorders_allowed()) 
              ||
              ($wcplpro_zero_to_out == 1 && $product->get_stock_quantity() == 0 && $product->managing_stock() == true)
            ) { 
              $carttext = __( 'Backorder', 'wcplpro' ); 
            } else { 
              $carttext = __('Add to cart', 'wcplpro' ); 
            }
            
            $wcplpro_button_classes = apply_filters('wcplpro_single_button_classes', array(
                  'single_add_to_cart_button',
                  'button',
                  'button_theme',
                  'ajax',
                  'add_to_cart',
                  'avia-button',
                  'fusion-button',
                  'button-flat',
                  'button-round'
                )
              );
            
            if (class_exists( 'YITH_WCQV_Frontend' )) {
              ob_start();
              $YITH_WCQV_Frontend->yith_add_quick_view_button();
              $yith_quickview = ob_get_clean();
            }
            
            if ($product_type == 'variable' || (!(wc_format_decimal($product->get_display_price(), 2) > 0) && $wcplpro_zero_to_out == 1) || $product_meta['_stock_status'][0] == 'outofstock') {
              $allcolumns['wcplpro_cart'] .= '<a href="'. get_the_permalink() .'" id="add2cartbtn_'. $product->id .'" data-product_id="'. $product->id .'" class="'. implode(' ', $wcplpro_button_classes) .' alt">'. apply_filters('single_add_to_cart_text', $product->add_to_cart_text(), $product->product_type, $product) .'</a>';
                            
              if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview) && ($wcplpro_quickview == 'all' || $wcplpro_quickview == 'variable')) {
                $allcolumns['wcplpro_cart'] .= $yith_quickview;
              }
              
            } else {
              
              $allcolumns['wcplpro_cart'] .= $form.'
                <button id="add2cartbtn_'. $product->id .'" type="submit" data-product_id="'. $product->id .'" class="'. implode(' ', $wcplpro_button_classes) .'">'. apply_filters('single_add_to_cart_text', $carttext, $product->product_type, $product) .'</button>';
              if ($wcplpro_ajax == 1 || $wcplpro_globalcart == 1 || $wcplpro_globalcart == 2) {
                $allcolumns['wcplpro_cart'] .= '
                  <div class="added2cartwrap" id="added2cart_'.$product->id.'"><span class="added2cart" >&#10003;</span></div>
                  <span class="vtspinner singlebtn vtspinner_'. $product->id .'">
                    <img src="'. plugins_url('images/spinner.png', __FILE__) .'" width="16" height="16" alt="spinner" />
                  </span>
                  ';
              } else {
                $allcolumns['wcplpro_cart'] .= '
                  <div class="added2cartwrap notvisible" id="added2cart_'.$product->id.'"></div>
                  <span class="vtspinner vtspinner_'. $product->id .' notvisible"></span>
                  ';
              }
              
              if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview) && ($wcplpro_quickview == 'all' || $wcplpro_quickview == 'simple')) {
                $allcolumns['wcplpro_cart'] .= $yith_quickview;
              }
              
              
            }
          } else {
            
            $allcolumns['wcplpro_cart'] .= '
              <a href="'. get_the_permalink() .'" id="add2cartbtn_'. $product->id .'" data-product_id="'. $product->id .'" class="single_add_to_cart_button button alt ajax add_to_cart">'. apply_filters('single_add_to_cart_text', $product->add_to_cart_text(), $product->product_type, $product) .'</a>
              <div class="added2cartwrap notvisible" id="added2cart_'.$product->id.'"></div>
              <span class="vtspinner vtspinner_'. $product->id .' notvisible"></span>
            ';
            
            if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview) && ($wcplpro_quickview == 'all' || $wcplpro_quickview == 'simple')) {
              $allcolumns['wcplpro_cart'] .= $yith_quickview;
            }
            
          }
          
          ob_start();
          do_action('woocommerce_after_add_to_cart_button', $product->id, $product);
          $woocommerce_after_add_to_cart_button = ob_get_clean();
          
          $allcolumns['wcplpro_cart'] .= $woocommerce_after_add_to_cart_button .'</form></td>';
          
          
        }
        
        //global cart checkbox
        if ($colkey == 'wcplpro_globalcart' && ($wcplpro_globalcart == 1 || $wcplpro_globalcart == 2)) {
          $col_checker[$colkey] = true;
          $allcolumns['wcplpro_globalcart'] = '<td class="globalcartcol '. ($wcplpro_globalcart == 2 ? 'vartablehide' : '') .'" data-title="">';
          if ((get_post_meta($product->id, '_stock_status', true) != 'outofstock' || !empty($value['backorders_allowed'])) && $product_type != 'variable' && !(wc_format_decimal($product->get_display_price(), 2) <= 0 && $wcplpro_zero_to_out == 1) )   {   
            $allcolumns['wcplpro_globalcart'] .= '  <input type="checkbox" class="globalcheck" name="check_'. $product->id .'" value="1" '. ($wcplpro_globalcart == 2 || $wcplpro_global_status == 1 ? 'checked="checked"' : '') .'>';
          }
          $allcolumns['wcplpro_globalcart'] .= '</td>';
        }
        
        // prepare the excerpt
        $excerpt = '';
        
            
        ob_start();
        do_action('wcplpro_before_excerpt', $current_post, $product);
        $wcplpro_before_excerpt = ob_get_clean();
        $wcplpro_before_excerpt_out = $wcplpro_before_excerpt;
        
        ob_start();
        do_action('wcplpro_after_excerpt', $current_post, $product);
        $wcplpro_after_excerpt = ob_get_clean();
        $wcplpro_after_excerpt_out = $wcplpro_after_excerpt;
        
        if (absint(get_option('wcplpro_excerpt_length')) > 0) {
          $excerpt = wcplpro_excerpt_max_length(absint(get_option('wcplpro_excerpt_length')), get_the_excerpt());
        } else {
          $excerpt = get_the_excerpt();
        }
        
        // add actions to excerpt
        $excerpt = $wcplpro_before_excerpt.$excerpt.$wcplpro_after_excerpt;
        
        //description
        if ($colkey == 'wcplpro_desc' && $wcplpro_desc == 1 && $wcplpro_desc_inline == 1) {
          $col_checker[$colkey] = true;
          if (get_the_excerpt() != '') {
            $anydescription = 1;
          }
                   
          $allcolumns[$colkey] = '
            <td class="desccol" data-title="'. apply_filters('wcplpro_dl_desc', $headenames[$colkey], $product) .'">'. $excerpt .'</td>';
            
        }
        
        
        // implode all columns
        $out .= implode("\n", apply_filters('wcplpro_allcolumns', $allcolumns, $product));
        
        // $out .= '<a href="'. get_the_permalink() .'">'. get_the_title().'</a><br />';
      }
      
      $out .= '</tr>';
      
      if ($wcplpro_desc == 1 && get_the_excerpt() != '' && $wcplpro_desc_inline != 1) {
        $out .= '
        <tr class="descrow desc_'.$product->id .'">
          <td class="desccol" colspan="'. (count($headenames) - 1) .'" data-title="'. apply_filters('wcplpro_dl_desc', $headenames['wcplpro_desc'], $product) .'">'. $excerpt .'</td>
        </tr>';
      }
      
      
      
    } // The Loop END
    
    
    $out .= '</table>
    </div>
    ';
    
    if (($wcplpro_globalcart == 1 || $wcplpro_globalcart == 2) && ($wcplpro_globalposition == 'bottom' || $wcplpro_globalposition == 'both')) {
      
      ob_start();
      do_action('wcplpro_add_gc_button', $product->id);
      $wcplpro_add_gc_button = ob_get_clean();
 
      $out .= apply_filters('wcplpro_global_btn', '
        <div class="gc_wrap">
          <a data-position="bottom" href="#globalcart" class="globalcartbtn submit btn single_add_to_cart_button button alt" data-product_id="gc_'.$product->id .'" id="gc_'. $vtrand .'_bottom" class="btn button alt">'. __('Add selected to cart', 'wcplpro') .'<span class="vt_products_count"></span></a>
          <span class="added2cartglobal added2cartglobal_'. $vtrand .'">&#10003;</span>
          <span class="vtspinner vtspinner_bottom vtspinner_'. $vtrand .'"><img src="'. plugins_url('images/spinner.png', __FILE__) .'" width="16" height="16" alt="spinner" /></span>
        </div>
      ', $product, 'bottom', $vtrand );
    }
    
    
    ob_start();
    do_action('wcplpro_before_filters_bottom', $current_post);
    $wcplpro_before_filters_bottom = ob_get_clean();
    
    $out .= $wcplpro_before_filters_bottom;
    
    
    $out .= '<div class="wcpl_group bottom">';
    
    // add drops down filters
    if ($wcplpro_filters_position == 'before' || $wcplpro_filters_position == 'both') {
      
      $out .= wcplpro_filters_form($wcplpro_filter_cat, $wcplpro_filter_tag, get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag'), $wcplpro_wcplid);
      
    }
    
    if ($wcplpro_pagination == 'after' || $wcplpro_pagination == 'both') {
      $out .= wcplpro_pagination($wcplpro_posts_per_page, $query);
    }
    
    $out .= '</div>';
    
    ob_start();
    do_action('wcplpro_after_filters_bottom', $current_post);
    $wcplpro_after_filters_bottom = ob_get_clean();
    
    $out .= $wcplpro_after_filters_bottom;
    
    
    ob_start();
    do_action('wcplpro_after_table', $current_post);
    $wcplpro_after_table = ob_get_clean();
    $out .= $wcplpro_after_table;
    
    
    
    // create header
    
    $remove_header_text = apply_filters('wcplpro_remove_header_text', array('wcplpro_thumb', 'wcplpro_offer', 'wcplpro_cart', 'wcplpro_globalcart'), $product);
    
    $head_array = array();
    foreach($wcplpro_order as $colkey => $colname) {
      if(${$colkey} == 1 && isset($col_checker[$colkey]) && $col_checker[$colkey] == true) {
        
        if (in_array($colkey, $remove_header_text)) { $colname = ''; }
        if ($colkey == 'wcplpro_globalcart') { $colname = '<input type="checkbox" class="checkall wcplprotable_selectall_check" name="checkall_'. $vtrand .'" id="checkall_'. $vtrand .'" value="1" />'; }
        
        ob_start();
        do_action('wcplpro_th_class', $product);
        $wcplpro_th_class = ob_get_clean();
        
        
        $skip_sorting = apply_filters('wcplpro_skip_columns', array(
          'wcplpro_globalcart',
          'wcplpro_cart',
          'wcplpro_thumb',
          'wcplpro_qty',
          'wcplpro_offer'
        ));
        
        $sort_as_string = apply_filters('wcplpro_sort_as_string', array(
          'wcplpro_title',
          'wcplpro_sku'
        ));
        
        if ($colkey == 'wcplpro_custommeta' && !empty($custom_meta_header)) {
          
          foreach ($custom_meta_header as $meta_array_key => $meta_post_key) {
            $head_array[$meta_array_key] = '<th class="'. $colkey .' '. $meta_array_key .' '. (!in_array($meta_array_key, $skip_sorting) ? 'sortable_th' : '') .' '. $wcplpro_th_class .'" '. (!in_array($meta_array_key, $skip_sorting) ? 'data-sort="'. (in_array($meta_array_key, $sort_as_string) ? 'string' : 'float') .'"' : '') .'>'. $meta_post_key .'</th>';
          }
          
        } else {
          
          $head_array[$colkey] = '<th class="'. $colkey .' '. (!in_array($colkey, $skip_sorting) ? 'sortable_th' : '') .' '. $wcplpro_th_class .'" '. (!in_array($colkey, $skip_sorting) ? 'data-sort="'. (in_array($colkey, $sort_as_string) ? 'string' : 'float') .'"' : '') .'>'. $headenames[$colkey] .'</th>';
          
        }
      }
    }
    
    if ($wcplpro_head != 0) {
      $head = '
        <thead>
          <tr>
            '.
            implode("\n", apply_filters( 'wcplpro_header_th', $head_array, $product->id))
            .' 
          </tr>
        </thead>
      ';
    } else {
      $head = '';
    }
    
    
    
    $out = str_replace('%headplaceholder%', $head, $out);

    wp_reset_postdata();
    
  } // IF Products END
  else {
    $out = __('No products found', 'wcplpro');
  }


  if($wcplpro_echo == 1) {
    echo $out;
  } else {
    return $out;
  }
}

add_action( 'admin_enqueue_scripts', 'wcplpro_wp_admin_scripts' );
function wcplpro_wp_admin_scripts($hook) {
    
  global $post;
  
  if ( (isset($_GET['page']) && $_GET['page'] == 'productslistpro') || ($hook == 'post-new.php' || $hook == 'post.php') ) {
    wp_register_style( 'wcplpro_select2_css', plugins_url('select2/select2.css', __FILE__) );
    wp_enqueue_style('wcplpro_select2_css');
    wp_register_style( 'wcplpro_admin_css', plugins_url('assets/css/wcplpro-admin.css', __FILE__) );
    wp_enqueue_style('wcplpro_admin_css');
    
    wp_register_script( 'wcplpro_select2_js', plugins_url('select2/select2.min.js',__FILE__ ), array( 'jquery' ));
    wp_enqueue_script('wcplpro_select2_js');
  }
    
}


add_action("wp_enqueue_scripts", "wcplpro_scripts", 20); 
function wcplpro_scripts() {
  
  global $woocommerce, $post;
  
  // get array of all woo pages
  $woo_pages = wcplpro_get_woo_page_ids();
  
  if (isset($post)) { $post_id = $post->ID; } else { $post_id = 0; }
  
  if (!in_array($post_id, $woo_pages)) {
    wp_register_style( 'wcplpro_select2_css', plugins_url('select2/select2.css', __FILE__) );
    wp_enqueue_style('wcplpro_select2_css');
  }
  
  wp_register_style( 'wcplpro_css', plugins_url('assets/css/wcplpro.css', __FILE__) );
  wp_enqueue_style('wcplpro_css');
  

  if (!in_array($post_id, $woo_pages)) {
    wp_register_script( 'wcplpro_select2_js', plugins_url('select2/select2.min.js',__FILE__ ), array( 'jquery' ));
    wp_enqueue_script('wcplpro_select2_js');
  }
  
  
  if (get_option('wcplpro_sorting') == 1) {
    wp_register_script( 'wcplpro_table_sort', plugins_url('assets/js/stupidtable.js', __FILE__), array('jquery') );
    wp_enqueue_script("wcplpro_table_sort");    
  }
  
  wp_register_script( 'wcplpro_js', plugins_url('assets/js/wcplpro.js',__FILE__ ), array( 'jquery' ));
  wp_enqueue_script('wcplpro_js');
   
  $vars = array( 
    'ajax_url' => admin_url( 'admin-ajax.php' ), 
    'cart_url' => $woocommerce->cart->get_cart_url(),
    'currency_symbol' => get_woocommerce_currency_symbol(),
    'thousand_separator' => wc_get_price_thousand_separator(),
    'decimal_separator' => wc_get_price_decimal_separator(),
    'decimal_decimals' => wc_get_price_decimals(),
    'currency_pos' => get_option( 'woocommerce_currency_pos' ),
    'price_display_suffix' => get_option( 'woocommerce_price_display_suffix' ),
    'wcplpro_ajax' => get_option('wcplpro_ajax'),
  );
    
  wp_localize_script( 'wcplpro_js', 'wcplprovars', $vars );
  
}


add_action( 'wp_ajax_add_product_to_cart', 'wcplpro_ajax_add_product_to_cart' );
add_action( 'wp_ajax_nopriv_add_product_to_cart', 'wcplpro_ajax_add_product_to_cart' );

function wcplpro_ajax_add_product_to_cart() {

    ob_start();
    
    $productids  = json_decode(stripslashes($_POST['product_id']), true);
    $quantities   = json_decode(stripslashes($_POST['quantity']), true);
    
    foreach($productids as $index => $product_id) {

      $product_id   = apply_filters( 'wcplpro_add_to_cart_product_id', absint( $product_id ) );
      $quantity     = empty( $quantities[$index] ) ? 1 : wc_stock_amount( $quantities[$index] );

      // todo variation support
      // $variation_id      = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
      // $variations         = vartable_get_variation_data_from_variation_id($variation_id);

      $passed_validation = apply_filters( 'wcplpro_add_to_cart_validation', true, $product_id, $quantity);

      if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity ) ) {
          
          do_action( 'woocommerce_set_cart_cookies', TRUE );
          do_action( 'wcplpro_ajax_added_to_cart', $product_id );

          if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' && get_option('wcplpro_ajax') != 1) {

            wc_add_to_cart_message( array( $product_id => $quantity ), true );

          }


      } else {

          // If there was an error adding to the cart, redirect to the product page to show any errors
          $data = array(
              'error' => true,
              'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
          );

          wp_send_json( $data );

      }
      
    }
    
    //clear notices if any
    if (get_option('wcplpro_ajax') == 1 || get_option( 'woocommerce_cart_redirect_after_add' ) != 'yes') {
      wc_clear_notices();
    }
    // Return fragments
    if (get_option('wcplpro_ajax') == 1) {
      WC_AJAX::get_refreshed_fragments();
    }


    die();
}


function wcplpro_footer_code() {
  global $woocommerce;
  ?>
  <div id="wcplpro_added_to_cart_notification" class="<?php echo (get_option('wcplpro_panel_manualclose') == 1 ? '': 'autoclose'); ?>" style="display: none;">
    <a href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php echo __('Go to cart', 'wcplpro'); ?>"><span></span> <?php echo __('&times; product(s) added to cart', 'wcplpro'); ?> &rarr;</a> <a href="#" class="slideup_panel">&times;</a>
  </div>
  <?php
}
add_action('wp_footer', 'wcplpro_footer_code');






// Add settings link on plugin page
function wcplpro_plugin_settings_link($links) { 
  $settings_link = '<a href="admin.php?page=productslistpro">'. __('Settings', 'wcplpro') .'</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$wcplpro_plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$wcplpro_plugin", 'wcplpro_plugin_settings_link' );


// remove gift wrap frontend hook
function wcplpro_gifthook_the_remove() {
  require_once 'wp-filters-extra.php';
  if (class_exists('WC_Product_Gift_Wrap')) {
    wcplpro_remove_filters_for_anonymous_class( 'woocommerce_after_add_to_cart_button', 'WC_Product_Gift_Wrap', 'gift_option_html', 10 );
  }
}

add_action( 'plugins_loaded', 'wcplpro_gifthook_the_remove', 1) ;



/**
* @return bool
*/
function wcplpro_is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}


function wcplpro_media_upload($fname, $value = '', $ai='') {
 
// This will enqueue the Media Uploader script
wp_enqueue_media();
?>

    <input type="text" name="<?php echo $fname; ?>" id="<?php echo $fname; ?>" value="<?php echo $value; ?>" class="regular-text">
    <input type="button" name="upload-btn<?php echo $ai; ?>" id="upload-btn<?php echo $ai; ?>" class="button-secondary button button-action" value="<?php echo __('Open Media Manager', 'wcplpro'); ?>"><br />
    <img class="img_<?php echo $ai; ?>" src="<?php echo $value; ?>" />


<script type="text/javascript">
jQuery(document).ready(function($){
    jQuery('#upload-btn<?php echo $ai; ?>').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            // console.log(uploaded_image);
            var image_url = uploaded_image.toJSON().url;
            // console.log(image_url);
            // Let's assign the url value to the input field
            jQuery('input[name="<?php echo $fname; ?>"]').val(image_url);
            jQuery('img.img_<?php echo $ai; ?>').attr('src', image_url);
        });
    });
});
</script>
  <?php
}

function wcplpro_delete_all_between($beginning, $end, $string) {
  $beginningPos = strpos($string, $beginning);
  $endPos = strpos($string, $end);
  if ($beginningPos === false || $endPos === false) {
    return $string;
  }

  $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

  return str_replace($textToDelete, $beginning.$end, $string);
}


if (!function_exists('wcplpro_excerpt_max_length')) {
  function wcplpro_excerpt_max_length($charlength, $excerpt) {
    
    if ($excerpt == '') { return; }
    
    $out = '';

    if ( mb_strlen( $excerpt ) > absint($charlength) ) {
      $out = mb_substr( $excerpt, 0, $charlength ).'&hellip;';
    } else {
      $out = $excerpt;
    }
    
    return apply_filters('wcplpro_excerpt', $out);
  }
}


function wcplpro_filters_form($wcplpro_filter_cat, $wcplpro_filter_tag, $cat_transient, $tag_transient, $wcplpro_wcplid){
  
  global $wp_query;
  
  $out = '';
  
  if ($wcplpro_filter_cat == 'yes') {
    
    $cat_transient_arr = array();
    if (!empty($cat_transient)) {
      $cat_transient_arr = explode(',', $cat_transient);
    }
    
    // get woo categories
    $terms = get_categories( array(
      'taxonomy' => 'product_cat',
      'hide_empty' => true
    ) );
    
    
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
      $out .= '
      <div class="wcpl_span wcpl_span4">
        <select data-bindto="wcpl_product_cat" class="wcplpro_filter select2" id="wcplpro_filter_cat" multiple="multiple" placeholder="'. __('Filter by category', 'wcplpro') .'">';
      foreach ( $terms as $term ) {
        $out .= '<option value="'. $term->term_id .'" '. (in_array($term->term_id, $cat_transient_arr) ? 'selected="selected"' : '') .'>'. $term->name .'</option>';
      }
      $out .= '
        </select>
        <input type="hidden" name="wcpl_product_cat" value="'. $cat_transient .'" />
      </div>
      ';
    }

    
  }
  
  
  if ($wcplpro_filter_tag == 'yes') {
    
    $tag_transient_arr = array();
    if (!empty($tag_transient)) {
      $tag_transient_arr = explode(',', $tag_transient);
    }
    
    // get woo tags
    $tags = get_categories( array(
      'taxonomy' => 'product_tag',
      'hide_empty' => true
    ) );
    
    
    if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
      $out .= '
      <div class="wcpl_span wcpl_span4 last">
        <select data-bindto="wcpl_product_tag" class="wcplpro_filter select2" id="wcplpro_filter_tag" multiple="multiple" placeholder="'. __('Filter by tag', 'wcplpro') .'">';
      foreach ( $tags as $tag ) {
        $out .= '<option value="'. $tag->term_id .'" '. (in_array($tag->term_id, $tag_transient_arr) ? 'selected="selected"' : '') .'>'. $tag->name .'</option>';
      }
      $out .= '
        </select>
        <input type="hidden" name="wcpl_product_tag" value="'. $tag_transient .'" />
      </div>  
      ';
    }
    
  }
  
  if ($out != '') {
    
    $wcplpro_button_classes = apply_filters('wcplpro_single_button_classes', array(
        'single_add_to_cart_button',
        'button',
        'button_theme',
        'ajax',
        'add_to_cart',
        'avia-button',
        'fusion-button',
        'button-flat',
        'button-round'
      )
    );
    

    $out = '
      <div class="wcplpro_filters_wrap">
        <div class="wcpl_span wcpl_span6">
          <form class="wcplpro_filters_form"  action="'. get_the_permalink($wp_query->post->ID) .'" method="post" >
            <input type="hidden" name="wcpl" value="1" />
            <input type="hidden" name="wcplid" value="'. $wcplpro_wcplid .'" />
            <input type="hidden" name="wcpl_filters" value="1" />
            '. $out .'
            <div class="wcpl_span wcpl_span2">
              <input type="submit" class="wcplpro_submit" value="'. __('Filter', 'wcplpro') .'">
            </div>
            <div class="wcpl_span wcpl_span2 last">
              <a href="'. get_the_permalink($wp_query->post->ID) .'" class="'. implode(' ', $wcplpro_button_classes) .' alt wcplpro_reset" title="'. __('Reset Filters', 'wcplpro') .'" >'. __('Reset', 'wcplpro') .'</a>
            </div>

          </form>
        </div>
      </div>
    ';
    
  } else {
    
    if (get_option('wcplpro_pagination') == 'both' || get_option('wcplpro_pagination') == 'before' || get_option('wcplpro_pagination') == 'after') {
      $out = '
      <div class="wcplpro_filters_wrap">
        <div class="wcpl_span wcpl_span6">
        &nbsp;
        </div>
      </div>
      ';
    }
    
  }
  
  return $out;
  
}

/* ------------------------------------------------------------------*/
/* PAGINATION */
/* ------------------------------------------------------------------*/

function wcplpro_pagination($wcplpro_posts_per_page, $wcplpro_query) {

  $out = '';
  $total = $wcplpro_query->max_num_pages;
  // only bother with the rest if we have more than 1 page!
  if ( $total > 1 )  {
    // get the current page
    if ( !$current_page = get_query_var('paged') ) {
      $current_page = 1;
    }
    if ( is_front_page() && !$current_page = get_query_var('page') ) {
      $current_page = 1;
    }
    // structure of "format" depends on whether we're using pretty permalinks
    if( get_option('permalink_structure') ) {
     $format = 'page/%#%/';
    } else {
     $format = '&paged=%#%';
    }
    
    $out = 
    '<div class="wcplpro_pagination_wrap">
        <div class="wcpl_span wcpl_span6">
    '. paginate_links(array(
        'base'     => get_pagenum_link(1) . '%_%',
        'format'   => $format,
        'current'  => $current_page,
        'total'    => $total,
        'mid_size' => 3,
        'type'     => 'list'
    )).'
        </div>
      </div>
    ';
  }
  
  return $out;
}



// get Woocommerce pages IDs
function wcplpro_get_woo_page_ids() {
  
  $pages = array(
    'woocommerce_shop_page_id'          => get_option( 'woocommerce_shop_page_id' ),
    'woocommerce_cart_page_id'          => get_option( 'woocommerce_cart_page_id' ), 
    'woocommerce_checkout_page_id'      => get_option( 'woocommerce_checkout_page_id' ),
    'woocommerce_pay_page_id'           => get_option( 'woocommerce_pay_page_id' ),
    'woocommerce_thanks_page_id'        => get_option( 'woocommerce_thanks_page_id' ),
    'woocommerce_myaccount_page_id'     => get_option( 'woocommerce_myaccount_page_id' ),
    'woocommerce_edit_address_page_id'  => get_option( 'woocommerce_edit_address_page_id' ),
    'woocommerce_view_order_page_id'    => get_option( 'woocommerce_view_order_page_id' ),
    'woocommerce_terms_page_id'         => get_option( 'woocommerce_terms_page_id' )
  );
  
  return $pages;
  
}