<?php

class Propel_Generate_Keys {

  function __construct() {

    add_action( 'woocommerce_order_status_completed',
      array( $this, 'generate_keys_on_okm' ), 1, 3 );
    add_action( 'woocommerce_order_status_processing',
      array( $this, 'generate_keys_on_okm' ), 1, 3 );

    // add_action( 'woocommerce_thankyou', 
      // array( $this, 'redirect_after_purchase' ) );

  }

  /**
   * Requests key generation from the Scitent OKM server when order completed
   * Saves keys to Order post_meta and attaches to customer order complete email
   */
  function generate_keys_on_okm( $order_id ) {
    $order = new WC_Order( $order_id );

    $keys = get_post_meta( $order_id, '_keys', true );

    if ( ! empty( $keys ) ) return;

    $products = $order->get_items();

    $user = $order->get_user();

    $first_name = $user->first_name ? $user->first_name : $order->billing_first_name;
    $last_name  = $user->last_name ? $user->last_name : $order->billing_last_name;

    $propel_settings = get_option( 'propel_settings' );

    Propel_LMS::check_tenant_key( 'request keys' );

    $post_data = array(
      'tenant_secret_key' => $propel_settings['okm_tenant_secret_key'], 
      'ext_user_id' => $user->ID,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'email' => $user->user_email,
      'order_number' => $order->id,
      'products' => array()
    );

    /* SENDING THE ORGANIZATION_ID TO THE OKM */

    // The OKM stores 'organizations' in its database
    // When generating keys, it needs to know if there is a corresponding organization
    //
    // The top priority is for 'Org Admins' who have the OKM tied to their wp user account
    // Send this OKM org id to the OKM if it exists
    $okm_org_id = get_the_author_meta( 'propel_org_admin', $user->ID );

    // If the 'Org Admin' id doesn't exist (empty), it means the purchaser is an ordinary user/customer
    // When ordinary user/customers are created/saved, they select an 'Organization' (example: League and Team)
    // The preferred organization id is duplicated in the usermeta table as 'propel_okm_org_id'
    // 'propel_okm_org_id' is stored as the wp_posts.ID though, NOT the OKM org id
    // The OKM org id is stored on the post_meta, as '_org_id'
    //
    // So if the $okm_org_id is currently empty (user is not an org admin)
    // Try getting the '_org_id' post_meta on the given 'propel_okm_org_id' propel_org
    //    (As example, this will be the OKM organization_id of '12' as opposed to the wp_posts.ID of 3300)
    if ( empty( $okm_org_id ) ) {
      $okm_org_id = get_the_author_meta( 'propel_okm_org_id', $user->ID );
      $okm_org_id = get_post_meta( $okm_org_id, '_org_id', true );
      $okm_team = get_the_author_meta( 'propel_org_team', $user->ID );
      $okm_group_name = get_the_title( $okm_team );
    }

    // If there is an OKM org id, send it in the request
    // If not, don't send an organization_id at all
    if ( ! empty( $okm_org_id ) )
      $post_data['organization_id'] = $okm_org_id;

    if ( ! empty( $okm_group_name ) )
      $post_data['group_name'] = $okm_group_name;


    foreach( $products as $product ) {
      // Make sure it is a course product
      $is_course = get_post_meta( $product['product_id'], '_related_course', true );
      if ( empty( $is_course ) ) continue;

      $new_product = array(
        'product_sku' => get_post_meta( $product['product_id'], '_sku', true ), 
        'product_name' => $product['name'],
        'quantity' => $product->get_quantity()
      );
      array_push( $post_data['products'], $new_product );
    }

    // No courses bought
    if ( empty( $post_data['products'] ) ) return;

    // Sends generate key request to OKM server, returns keys
    $keychain = Propel_LMS::ping_api( $post_data, 'generate_keys' );

    if ( isset( $keychain['http_status'] ) ) unset( $keychain['http_status'] );

    // TODO: Validate if $keychain correct data or error, duh
    update_post_meta( $order_id, '_keys', $keychain, true );

    error_log("KeyChain after generation is " . json_encode($keychain));

    do_action( 'after_propel_generate_keys', $order_id );

    add_action( 'woocommerce_email_order_meta', 
      array( $this, 'attach_keys_to_order_email' ), 10, 2 );
  }

  function attach_keys_to_order_email( $order, $sent_to_admin ) {
    if ( $sent_to_admin ) return;

    echo '<h2>Keys</h2>  ';

    $products = $order->get_items();

    $keychain = get_post_meta( $order->id, '_keys' );
    $keychain = $keychain[0];

    foreach ( $keychain as $product ) {
      echo '<h4>SKU: ' . $product['product_sku'] . '</h4>';

      // TODO: Check styling in email for funky word wrap
      echo '<pre> ' . implode( " ", $product['keys'] ) . ' </pre>';
    }
  }

  /**
   * Redirects to conditional page after purchase
   */
  function redirect_after_purchase() {   
    global $current_user;
    global $wp;

    $propel_settings = get_option( 'propel_settings' );

    $order_post = get_post( $wp->query_vars['order-received'] );


    if ( $order_post->post_status == 'wc-on-hold' ) {
      // Redirect to the special page
      $order_on_hold_page = get_post( $propel_settings['order_on_hold_page_id'] );

      if ( ! is_null( $order_on_hold_page ) ) {
        wp_redirect( get_permalink( $order_on_hold_page->ID ) );
        exit();
      }

    }


    $session = get_user_meta( $current_user->ID, 'session' );
    $session = $session[0];
    delete_user_meta( $current_user->ID, 'session' );


    if ( array_shift( $current_user->roles ) != 'org_admin' ) {

      // If a normal user bought multiple courses, take them to course central
      if ( $session['items'] > 1 ) {
        wp_redirect( home_url() . '/my-courses/' );
        exit;
      // Else a normal user bought a single course
      } else {

        // If they auto-enrolled, take them to the course
        // Otherwise, just show them their receipt per usual
        if ( isset( $session['course_id'] ) ) {
          wp_redirect( get_permalink( $session['course_id'] ) );
          exit;
        }
      }

    } else {

      wp_redirect( get_permalink( $propel_settings['okm_page_id'] ) );
      exit;

    }

  }


}

new Propel_Generate_Keys();