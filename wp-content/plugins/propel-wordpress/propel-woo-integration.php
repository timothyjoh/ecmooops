<?php

class Propel_Woo_Integration {

  function __construct() {

    add_action( 'init',
      array( $this, 'redirect_sku_slugs' ) );

    add_action( 'woocommerce_review_order_before_payment',
      array( $this, 'auto_enroll_render_form' ) );

    add_action( 'woocommerce_order_status_on-hold', 
      array( $this, 'hook_thank_you_email' ) );

    add_action( 'template_redirect',
      array( $this, 'download_csv' ) );

    add_action( 'woocommerce_view_order',
      array( $this, 'view_order_okm_keys' ) );
    
    add_action( 'admin_enqueue_scripts',
      array( $this, 'refund_script' ) );
    
    add_action( 'save_post_shop_order',
      array( $this, 'refund_orders' ) );

    add_filter( 'woocommerce_email_headers', 
      array( $this, 'send_it_to_missy_too') );

    add_filter( 'woocommerce_available_payment_gateways', 
      array( $this, 'purchase_order_disable_manager' ) );

    add_filter( 'woocommerce_email_headers',
      array( $this, 'notify_scitent_csr_order_on_hold'), 10, 3 );

  }

  function redirect_sku_slugs() {
    global $wpdb;
    $uri = explode('/', $_SERVER["REQUEST_URI"]);
    if ($uri[1] == 'sku') {
      error_log("sku slugs loaded: ".$uri[2]);
      $product_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1";
      $product_id = $wpdb->get_var( 
        $wpdb->prepare( 
          $product_query,                   
          $uri[2] 
        ) );
      error_log($product_id);
      error_log(get_permalink( $product_id ));
      if ($product_id){
        wp_redirect(get_permalink( $product_id )); 
        exit;
      }
    }
  }

  function purchase_order_disable_manager( $available_gateways ) {
    global $woocommerce;
    if ( isset( $available_gateways['woocommerce_gateway_purchase_order'] ) && !current_user_can('pay_via_purchase_order') ) {
      unset( $available_gateways['woocommerce_gateway_purchase_order'] );
    }
    return $available_gateways;
  }

  function display_sku_image() {
    global $wpdb;
    $uri = explode('/', $_SERVER["REQUEST_URI"]);
    if ($uri[1] == 'skuimage') {
      error_log("sku slugs loaded: ".$uri[2]);
      $product_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1";
      $product_id = $wpdb->get_var( 
        $wpdb->prepare( 
          $product_query,                   
          $uri[2] 
        ) );
      error_log($product_id);
      if (has_post_thumbnail($product_id)){
        wp_redirect(get_the_post_thumbnail_url($product_id));
        exit;
      }
    }    
  }
/* fix error? */
  /**
   * Renders checkboxes at the end of checkout for product (possible multiple-course) auto-enrollment
   */
  function auto_enroll_render_form() { 
    if( current_user_can('administer_okg') ) {
      return; // no auto-enrollment for okgs
    }
    global $current_user;

    if ( array_shift( $current_user->roles ) != 'org_admin' )
      $checked = true;

    ?>
    <h3>Auto Enrollment</h3>
    <p>Check the box to auto-enroll your account.</p>

    <table class="shop_table">
      <thead>
        <tr>
          <th style="width:20px;"></th>
          <th>Product</th>
        </tr> <?php
        
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) { 
          $product = $cart_item['data']->post->ID;

          $is_course = get_post_meta( $product, '_related_course', true );
          if ( empty( $is_course ) ) continue;

          ?>
          <tr>
            <td>
              <input name="enroll[<?php echo $product; ?>]"  
                     id="enroll[<?php echo $product; ?>]"  
                     type="checkbox" 
                     <?php if ( $checked ) echo 'checked'; ?> />
            </td>
            <td>
              <label for="enroll[<?php echo $product; ?>]">
                <?php echo get_the_title( $product ); ?>
              </label>
            </td>
          </tr> <?php
        } ?>

      </thead>
    </table> <?php
  }

  function send_it_to_missy_too( $headers ) {
    $headers .= 'BCC: ' . get_option( 'woocommerce_paypal_settings' )['email'] . "\r\n";
    return $headers;
  }

  /**
   * Adds instructions to the customer thank you email 
   */
  function hook_thank_you_email() {
    add_action( 'woocommerce_email_order_meta', function( $o, $sent_to_admin, $p ) {
      if ( ! $sent_to_admin ) {
        echo '<h2>Instructions</h2>';

        // TODO: Consider proper language in this email
        echo '<p>Your payment is awaiting approval, keys will be sent upon review and approval of your order.</p>';
      }
    }, 10, 3);


  }

  /**
   * Renders a table of keys for each product on the view-order page in WooCommerce
   */
  function view_order_okm_keys( $order_id ) {

    global $wpdb;
    $post_7 = get_post($order_id); 
    $order_status = $post_7->post_status;
    if ($order_status == 'wc-refunded') {
      return false;
    }

    $product_keys = get_post_meta( $order_id, '_keys' );

    $product_keys = $product_keys[0];

    echo '<h2>Product Keys</h2>';


    foreach ( $product_keys as $product ) {

      if ( count( $product['keys'] ) >= 50 ||
           current_user_can('administer_okg') ) continue;

      $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product['product_sku'] ) );

      if ( $product_id ) $woo_product = new WC_Product( $product_id );
      else $woo_product = null;

      echo '<table class="shop_table">
              <thead>
                <tr>
                  <th colspan="3">' . $woo_product->post->post_title .' [SKU: ' . $product['product_sku'] . ']</th>
                  <th><i>' . count( $product['keys'] ) . ' Keys</i></th>
                </tr>
                <tr>
                  <th class="product-key-code">Key Code</th>
                  <th class="product-status">Status</th>
                  <th class="product-user-enrolled">User Enrolled</th>
                  <th class="product-date-enrolled">Date Enrolled</th>
                </tr>
              </thead>
              <tbody>';

      foreach ( $product['keys'] as $key ) {

        $status = Propel_LMS::is_enrollment_active( $key ) == 0 ? 'Available' : 'Enrolled';
        $status = Propel_LMS::is_enrollment_expired( $key ) == 0 ? $status : 'Expired';

        if ( $status == 'Enrolled' ) {
          $enrollment = Propel_LMS::get_enrollment( array( 'activation_key' => $key ) );
          $user = get_user_by( 'id', $enrollment['user_id'] );
          $user_out = $user->user_nicename;
        } else {
          $enrollment = null;
          $user = null;
          $user_out = null;
        }

        echo '<tr>';
        echo   '<td>' . $key . '</td>';
        echo   '<td>' . $status . '</td>';
        echo   '<td>' . $user_out . '</td>';
        echo   '<td>' . $enrollment['activation_date'] . '</td>';
        echo '</tr>';
      }

      echo '</tbody></table>';
    }

    echo '<p><a class="button button-default propel-download-csv" href="/my-account/view-order/' . $order_id . '.csv" target="_blank">Download CSV</a></p>';
  }




  /**
   * Initiates a csv download of the given order_id
   */
  function download_csv() {

    $request_uri = explode( '/', $_SERVER['REQUEST_URI'] );

    $end = end( $request_uri );

    if ( substr( $end, -4 ) != '.csv' ) return;

    if ( ! is_user_logged_in() ) {
      auth_redirect();
      exit();
    }

    $order_id = array_shift( explode( '.', $end ) );

    $order = new WC_Order( $order_id );
    $user_id = get_current_user_id();

    if ( $order->user_id != $user_id ) {
      global $wp_query;
      $wp_query->set_404();
      status_header( 404 );
      get_template_part( 404 ); exit();
    }

    global $wpdb;

    $product_keys = get_post_meta( $order_id, '_keys' );

    $product_keys = $product_keys[0];

    $fh = fopen( 'php://output', 'w' );

    ob_start();

    $headers = array( 'Product', 'Key', 'Status', 'User', 'Enrollment Date' );

      fputcsv( $fh, $headers );

    foreach ( $product_keys as $product ) {

      $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product['product_sku'] ) );

      if ( $product_id ) $woo_product = new WC_Product( $product_id );
      else $woo_product = null;

      foreach ( $product['keys'] as $key ) {

        $status = Propel_LMS::is_enrollment_active( $key ) == 0 ? 'Available' : 'Enrolled';
        $status = Propel_LMS::is_enrollment_expired( $key ) == 0 ? $status : 'Expired';

        if ( $status == 'Enrolled' ) {
          $enrollment = Propel_LMS::get_enrollment( array( 'activation_key' => $key ) );
          $user = get_user_by( 'id', $enrollment['user_id'] );
          $user_out = $user->user_nicename;
        } else {
          $enrollment = null;
          $user = null;
          $user_out = null;
        }

        $line = array();
        array_push( $line, $woo_product->post->post_title . ' [SKU: ' . $product['product_sku'] . ']' );
        array_push( $line, $key );
        array_push( $line, $status );
        array_push( $line, $user_out );
        array_push( $line, $enrollment['activation_date'] );

        fputcsv( $fh, $line );
      }

    }

    header( 'Pragma: public' );
    header( 'HTTP/1.0 200 OK' );
    header( 'Expires: 0' );
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Cache-Control: private', false );
    header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment; filename="Order_' . $order_id . '.csv";' );
    header( 'Content-Transfer-Encoding: binary' );
    $string = ob_get_clean();
    exit( $string );

  }


  /**
   * Loads refund script for refunded orders
   * Script add 'deactivate-keys' checkbox on 'refunded' status change
   */
  function refund_script() {
    global $current_screen;

    if ( $_GET['action'] != 'edit' || $current_screen->post_type != 'shop_order' ) return;

    wp_enqueue_script( 'propel-refund', plugin_dir_url( __FILE__ ) . 'js/refunds.js' );
  }


  /**
   * When a course order is refunded this
   *   Deactivates the keys through the OKM
   *   Unenrolls the any activated keys
   */
  function refund_orders( $post_id ) {

    error_log( 'Shall I refund?  ' . print_r($post_id, 1) );

    global $wp_current_filter;

    if ( $wp_current_filter[0] == 'save_post' ) return;


    // STANDARD CHECKS

    // Autosave, do nothing
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;


    // Check user permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;


    // Return if it's a post revision
    if ( false !== wp_is_post_revision( $post_id ) ) return;



    // END STANDARD CHECKS
    error_log(print_r($_POST, true));

    if ( ! $_POST['restock_refunded_items'] ) return;

    error_log( 'After refunded and deactivate is true  ' . print_r($post_id, 1));

    $user = wp_get_current_user();
    $propel_settings = get_option( 'propel_settings' );

    // Deactivate keys through OKM
    $post_data = array(
      'tenant_secret_key' => $propel_settings['okm_tenant_secret_key'],
      'order_number' => "$post_id",
      'ext_user_id' => "$user->ID",
    );

    $response = Propel_LMS::ping_api( $post_data, 'deactivate_keys' );

    // Unenroll each of the keys
    if ( $response['http_status'] == 200 ) {
        foreach ( $response['api'] as $key ) {
          Propel_LMS::unenroll_key( $key['code'] );
        }
    }


  }  

  /**
   *  Send Customer Service an email when OKM Admin buys via P.O.
   */
  function notify_scitent_csr_order_on_hold( $headers, $email_id, $order ) {
    if( !defined( 'PROPEL_CSR_ADMIN_EMAIL' ) ) {
      return $headers; // Please define this, per-client, in the theme's functions.php
    }
    if( 'customer_on_hold_order' == $email_id ){
        error_log( '=====> P.O. Purchase Notification :: Customer Order is on Hold.' );
        $headers .= 'Bcc: ' . PROPEL_CSR_ADMIN_EMAIL;
    }
    return $headers;
  }


}

new Propel_Woo_Integration();