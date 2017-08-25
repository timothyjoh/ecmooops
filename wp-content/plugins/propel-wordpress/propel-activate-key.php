<?php

class Propel_Activate_Key {

  function __construct() {

    add_action( 'wp_ajax_activate_key',
      array( $this, 'ajax_activate_key' ) );

    add_action( 'after_propel_generate_keys',
      array( $this, 'auto_enroll_user_in_courses' ) );

    add_action('woocommerce_checkout_update_user_meta', 
      array( $this, 'auto_enroll_update_user_meta'), 2, 3 );

  }

  /* ------------------------------------------- */
  /* ------------------------------------------- */
  /* ------------ WordPress Hooks -------------- */

  /**
   * The ajax POST controller for activating keys on the OKM
   */
  function ajax_activate_key() {
    $key = $_POST['key'];
    error_log("Ajax_activate_key -> ". $key);
    $response = self::okm_activate_key( $key, get_current_user_id() );

    if ( ! $response['success'] ){
      wp_send_json_error( $response );
    } else {
      wp_send_json_success( $response );
    }

  }

  /** 
   * Store auto enroll preferences in the user's meta
   */
  function auto_enroll_update_user_meta( $user_id ) {
    $autoenrolls = array_keys($_POST['enroll']);
    error_log("Saving to AutoEnroll for ". $user_id . " these courses " . json_encode($autoenrolls) );
    if ($user_id && $_POST['enroll']) {
      update_user_meta( $user_id, '_auto_enroll', $autoenrolls );
    }
  }

  /**
   * Enroll user in product's courses if selected during checkout
   */ 
  function auto_enroll_user_in_courses( $order_id ) {
    $order = new WC_Order( $order_id );
    $user_id = $order->user_id;
    $propel_settings = get_option( 'propel_settings' );

    $enrollments = get_user_meta( $user_id, '_auto_enroll', true );
    error_log("Need to AutoEnroll for ". $user_id . " these courses " . json_encode($enrollments) );
    

    $session['items'] = count( $order->get_items() );

    foreach ( $enrollments as $product ) {
      $response = $this->okm_activate_key( $this->pluck_key( $order_id, $product ), $user_id );
      if ( ! $response['success'] ){
        $session['course_id'] = $response['course_id'];
      }
    }
    update_user_meta( $user_id, 'session', $session );
  }




  /* ------------------------------------------- */
  /* ------------------------------------------- */
  /* ------------ Internal Methods ------------- */

  function okm_activate_key( $key, $user_id ) {
    global $wpdb;
    $user = get_user_by('id', $user_id);
    $propel_settings = get_option( 'propel_settings' );

    error_log("okm_activate_key -> ". $key);
    $post_data = array(
      'tenant_secret_key' => $propel_settings['okm_tenant_secret_key'], 
      'ext_user_id' => $user->ID,
      'first_name' => $user->user_firstname,
      'last_name' => $user->user_lastname,
      'email' => $user->user_email,
      'code' => $key  
    );

    $response = Propel_LMS::ping_api( $post_data, 'activate_key' );
    if ( array_key_exists( 'code', $response ) ){
      $response['success'] = true;
    } else {
      error_log(" ->  No Key matches: ". $key);
      $response['success'] = false;
      $respsonse['msg'] = 'This key does not exist or cannot be activated.';
    }

    if ( ! $response['success'] ){
      error_log("Activating Key Response: " . json_encode($response));
      return $response;
    }

    $product_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1";

    $product_id = $wpdb->get_var( 
            $wpdb->prepare( 
              $product_query,
              $response['product_sku'] 
            ) );

    if ( $product_id ) {
      $product = new WC_Product( $product_id );
    } else {
      $respsonse['success'] = false;
      $respsonse['msg'] = 'No product with that sku';
      error_log(" ->  No product with that sku");
      return $response;
    }

    $courses_id = get_post_meta( $product_id, '_related_course', true );
    $expires_in = get_post_meta( $product_id, 'course_expires_in_days', true );
    $course_id;

    if ( $courses_id && is_array( $courses_id ) ) {
      // TODO: Shouldn't we see if user exists already in the list?
      foreach ( $courses_id as $cid ) {
        $this->update_course_access( $user->ID, $cid, $key, $expires_in );
        $course_id = $cid;
      }
    }
    $response['msg'] = 'Works great!';
    $response['course_id'] = $course_id;
    $response['url'] = '/my-courses';
    return $response;
  }

  /**
   * Attaches user to Course Post through  propel_enrollments table instead of post meta
   */
  static function update_course_access( $user_id, $course_id, $key, $expires_in = '365', $remove = false ) {
    if ( empty( $user_id ) || empty( $course_id ) ){
      return;
    }

    // Set to propel_enrollments
    global $wpdb;

    $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

    $exists = $wpdb->get_var( "
                      SELECT COUNT(*) 
                      FROM $propel_table 
                      WHERE user_id = $user_id
                        AND post_id = $course_id
                        AND expiration_date > NOW()
                    " );

    if ( ! $exists ) {
      $expiration = date_format(
                      date_add(
                        date_create( current_time( 'mysql' ) ),
                        date_interval_create_from_date_string( $expires_in . ' days' )
                      ),
                      'Y-m-d H:i:s'
                    );
      $wpdb->insert( 
              $wpdb->prefix . Propel_DB::enrollments_table,
              array( 
                'post_id' => $course_id,
                'user_id' => $user_id,
                'activation_date' => current_time( 'mysql' ),
                'expiration_date' => $expiration,
                'activation_key'  => $key
              )
            );
    }
  }

  /**
   * Returns the first key for a product
   *   To be used only for auto-enrollment, when we know the first key is not used yet
   */
  function pluck_key( $order_id, $product_id ) {
    $keys = get_post_meta( $order_id, '_keys' );
    $sku = get_post_meta( $product_id, '_sku', true );
    // TODO: Save keys outside of embedded array
    foreach ( $keys[0] as $product ){
      if ( $product['product_sku'] == $sku ){
        return $product['keys'][0];
      }
    }
  }

}

new Propel_Activate_Key();