<?php

/** 
 * Plugin Name: PROPEL by Scitent
 * Version: 2015-05-01 15:46:47
 * Author: Casey Patrick Driscoll
 * Author URI: http://caseypatrickdriscoll.com
 * Description: Scitent OKM WooCommerce and Learndash integration
 */

include 'propel-custom-fields-courses.php';
include 'propel-custom-fields-products.php';
include 'propel-widget.php';
include 'learndash-override-functions.php';
include 'shortcodes/propel-shortcodes.php';
include 'shortcodes/my-courses-shortcode.php';
include 'shortcodes/okg-shortcodes.php';
include 'propel-enqueue.php';
include 'propel-settings.php';
include 'propel-user-registration.php';
include 'propel-course-product.php';
include 'propel-woo-integration.php';
include 'propel-generate-keys.php';
include 'propel-activate-key.php';
include 'propel-org-admin.php';  // depends on propel-settings.php for get_org_options
include 'propel-okg.php'; // depends on propel-org-admin.php
include 'propel-db.php';
include 'propel-migrate-course-completion.php';
include 'propel-reports.php';
include 'propel-manage-administrators.php';
include 'popups/popups.php';


/**
 * The Propel_LMS class handles the integration of LearnDash and WooCommerce,
 *
 *   It creates the necessary ancillary components (like pages and roles)
 *   to make the system run fully and completely.
 *
 *   It also integrates with the proprietary Scitent 'OKM',
 *   a course enrolment key management API system, hosted on a separate server
 *
 * Customers will typically purchase a LearnDash course enrollment through WooCommerce.
 *   At the time the WooCommerce purchase is complete
 */
class Propel_LMS {


  // Who to email in check_tenant_key. Can be comma separated list
  const OKM_SUPPORT = 'ljeanmarius@scitent.com';

  // Returned from self::okm_server() if no option set.
  // Declared here for access
  const OKM_SERVER  = 'https://example.com';

  // Used as a helpful hint in Propel_Settings::okm_server_callback()
  // Declared here for access
  const OKM_STAGING = '107.21.224.181';


  function __construct() {

    register_activation_hook( __FILE__, 
      array( $this, 'create_necessary_pages' ) );

    register_activation_hook( __FILE__,
      array( $this, 'create_necessary_roles' ) );

    add_action( 'plugins_loaded',
      array( $this, 'check_db_upgrade' ) );

    add_action( 'admin_menu',
      array( $this, 'add_propel_okm_menu' ) );


    add_action( 'woocommerce_checkout_billing',
      array( $this, 'check_tenant_key' ) );

    // Auth0 supporting actions
    add_action( 'woocommerce_created_customer', array( $this, 'woo_created_customer_hook' ), 0, 3 );
    add_action( 'auth0_user_login', array( $this, 'auth0_user_logged_in'), 0,5 );

    add_shortcode( 'current_username' , array($this,'sc_get_username') );

    add_action('clear_auth_cookie', array($this,'logout_hook'));

    add_filter( 'woocommerce_return_to_shop_redirect', array($this,'override_empty_cart_button_url') );
    add_filter( 'sfwd_lms_has_access', 'override_sfwd_lms_has_access',0,3 );
  }

  function override_empty_cart_button_url () {
    $propel_settings = get_option( 'propel_settings' );
    if ( isset($propel_settings['store_url'])) {
      return $propel_settings['store_url'];
    } else {
      return get_site_url();
    }
  }

  function logout_hook() {
    setcookie('authToken', '', time()-3600);
    setcookie('a0userId', '', time()-3600);
    setcookie('userName', '', time()-3600);
  }

  function sc_get_username(){
    $name = $_COOKIE["userName"];
    if ( !$name ) {
      $user = wp_get_current_user();
      $name = $user->display_name;
      setcookie("userName", $user->display_name);
    }
    return $name;
  }

   /**
    * Woo commerce has created a new customer. Create an auth0 user and log them in
    */
  function woo_created_customer_hook($customer_id, $new_customer_data, $password_generated) {
    $settings = get_option( 'propel_settings' );
      
      // if sso is not no, don't try to create auth0 users
      if ( $settings['okm_sso_enabled'] != 'on') {
         return;
      }
      
    $api_token = $settings['auth0_token'];
    $propel_db = $settings['auth0_database'];
      $tenant_key = $settings['okm_tenant_secret_key'];
    $auth0_client_id = $settings['auth0_client_id'];
    
    $domain = WP_Auth0_Options::get( 'domain' );
    $endpoint = "https://$domain/api/v2/users";
    
    $headers = array();
    $headers['Authorization'] = "Bearer $api_token";
    $headers['Content-Type'] = "application/json";

      $req = array(
        "connection"=> $propel_db,
        "email" => $new_customer_data['user_email'],
        "username" => $new_customer_data['user_login'],
        "password" => $new_customer_data['user_pass'],
        "email_verified" => true,
        "app_metadata" => array( "role" => $new_customer_data['role'], "ext_user_id"=>$customer_id, "tenant_key"=>$tenant_key )
       );

      $post_data = array( 'headers' => $headers, 'body'=>json_encode($req) );
      $response = wp_remote_post( $endpoint, $post_data);

      // Now, log them into auth0
      $req = array(
      "client_id" => $auth0_client_id,
      "username"=> $new_customer_data['user_login'],
      "password" => $new_customer_data['user_pass'],
      "connection" =>  $propel_db,
      "grant_type" => "password",
      "scope" =>   "openid profile email"
      );
      $headers = array('Content-Type' => "application/json");
      $endpoint = "https://$domain/oauth/ro";
      $post_data = array( 'headers' => $headers, 'body'=>json_encode($req) );

      $response = wp_remote_post( $endpoint, $post_data);
  }

  function auth0_user_logged_in($user_id, $user_profile, $is_new, $id_token, $access_token) {

    // Get this users role
    $user = new WP_User( $user_id );
    $role = $user->roles[0];
      $org_id = get_user_meta( $user->ID, 'propel_org_admin', 1 );

    # IMPORTANT: this token is not the USER JWT token, Instead it is an API
    # token generated from the API v2 Explorer located here https://auth0.com/docs/api/v2
    $settings = get_option( 'propel_settings' );
    $api_token = $settings['auth0_token'];
      $tenant_key = $settings['okm_tenant_secret_key'];

    // ...update the auth0 app_meta associated with the account
    $domain = WP_Auth0_Options::get( 'domain' );
    $endpoint = "https://$domain/api/v2/users/" . $user_profile->user_id;
      $headers = array();

      $headers['Authorization'] = "Bearer $api_token";
      $headers['Content-Type'] = "application/json";
      $body = '{"app_metadata":{"role":"' . $role . '", "ext_user_id": "'.$user->ID.'", "tenant_key": "'.$tenant_key.'", "org_id": "'.$org_id.'"}}';
      $post_data = array( 'method' => 'PATCH', 'headers' => $headers, 'body'=>$body);

      $response = wp_remote_post( $endpoint, $post_data);

    // Save JWT in cookie for future calls the OKM API
    setCookie("a0userId", $user_profile->user_id);
    setcookie("authToken", $id_token);
    setcookie("userName", $user->display_name);
  }

    /**
     * Queries the DB for the given enrollment,
     *   conditionally returning the enrollment key or false if not active or non-existent
     *
     * TODO: Consider multiple enrollments
     */
    static function get_active_enrollment( $user_id, $course_id ) {
      global $wpdb;

      $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;
      $now = current_time( 'mysql' );

      $enrollment = $wpdb->get_row( "
                            SELECT *
                            FROM $propel_table 
                            WHERE user_id = $user_id
                              AND post_id = $course_id
                              AND expiration_date > $now
                          " );

      if ( $enrollment )
        return $enrollment->activation_key;
      else
        return false;

    }


    /**
     * Checks to see if database needs an upgrade then does it
     *
     * Only checks on plugins_loaded, can't check on plugin upgrade/registration
     * http://codex.wordpress.org/Creating_Tables_with_Plugins
     */
    function check_db_upgrade() {
      $settings = get_option( 'propel_settings' );
      if ( isset( $settings ) && intval( $settings['db_version'] ) < Propel_DB::VERSION )
        new Propel_DB();
    }


    /**
     * Registers admin menu page for OKM
     */
    function add_propel_okm_menu() {
      add_menu_page(
        'PROPEL OKM',
        'PROPEL OKM',
        'view_tenant_okm',
        'propel-okm',
        array( $this, 'render_propel_okm' ),
        '',
        10
      );
    }


    /**
     * Renders OKM in the admin
     */
    function render_propel_okm() {
         global $current_user;
         get_currentuserinfo();
         
         if ( ! current_user_can( 'view_tenant_okm' ) ) return;
         
         $settings = get_option( 'propel_settings' );
         $tenant_key = $settings['okm_tenant_secret_key'];
         
         $width = '100%';
         $height = '3000';
         $a0UserId = $_COOKIE["a0userId"];
         
         /*Porthole scripts added 1/20/2016*/
         function enqueue_porthole_js() {
           wp_enqueue_script('jQuery');  
           wp_enqueue_script( 'porthole.min.js',  plugins_url() . '/propel-wordpress/vendor/porthole/porthole.min.js', array(), null, true );
           wp_enqueue_script( 'okm_parent_porthole.js',  plugins_url() . '/propel-wordpress/js/okm_parent_porthole.js', array(), null, true );
         }
         enqueue_porthole_js();
        /*end Porthole scripts*/ 


         // If OKM is not setup with auth0, use the old auth and iframe
         if ( $settings['okm_sso_enabled'] == 'on' && $a0UserId) {
            error_log("Authenticate with OKM using auth0");
            $out = '<iframe id="okm-frame" name="okm-frame" src="' . Propel_LMS::okm_server() . '/accounts/sso/' . $tenant_key . '" width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="auto"></iframe>';
            echo $out;
         } else {
            error_log("Authenticate with OKM using auth tokens");
            $auth_array = array(
               'tenant_secret_key' => $tenant_key,
               'first_name'        => $current_user->user_firstname,
               'last_name'         => $current_user->user_lastname,
               'ext_user_id'       => $current_user->ID,
               'role'              => $current_user->roles[0],
               'email'             => $current_user->user_email
            );
            
            $propel_org_admin = get_user_meta( $current_user->ID, 'propel_org_admin', true );
            
            if ( ! empty( $propel_org_admin ) ) $auth_array['org_id'] = $propel_org_admin;
            
            $response = Propel_LMS::ping_api( $auth_array, 'authenticate' );
            $okm_token = $response['auth_token'];
            
            $out = '<iframe id="okm-frame" name="okm-frame" src="' . Propel_LMS::okm_server() . '/accounts/' . $okm_token . '/sign_in' . '" width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="auto"></iframe>';
            
            echo $out;
         }
      }

    /**
     * Creates WordPress pages for user registration and interaction
     *   - Terms of Service
     *   - Activate Key
     *   - My Courses
     *   - OKM
     *   - Order On-Hold
     */
    function create_necessary_pages() {
      $user_id = get_current_user_id();

      // 1. Create 'Terms of Service' page
      if ( ! isset( get_page_by_title( 'Terms of Service' )->ID ) ) { 

        $page_content = file_get_contents( plugin_dir_path( __FILE__ ) . 'terms-of-service.txt' );

        $terms_of_service_page = array(
          'post_title' => 'Terms of Service',
          'post_content' => $page_content,
          'post_status' => 'publish',
          'post_type' => 'page',
          'post_author' => $user_id
        );

        wp_insert_post( $terms_of_service_page );

      }


      // 2. Create 'Activate Key' page
      if ( ! isset( get_page_by_title( 'Activate Key' )->ID ) ) {

        $page_content = 'Access to and use of ' . get_bloginfo( 'url' ) . ' is subject to the following terms and conditions. Please read these terms and conditions carefully before activating a course key. Completing the key activation process indicates that you accept and agree to abide by these terms and conditions set forth by the ' . get_bloginfo( 'name' ) . '.';

        $page_content .= "\n\n";
        $page_content .= '[propel-key-activator]';

        $page_content .= "\n\n";
        $page_content .= '[propel-terms-of-service]';

        $page_content .= "\n\n";
        $page_content .= '[propel-key-submit]';

        $activate_key_page = array(
          'post_title' => 'Activate Key',
          'post_content' => $page_content,
          'post_status' => 'publish',
          'post_type' => 'page',
          'post_author' => $user_id 
        );

        wp_insert_post( $activate_key_page );

      }


      // 3. Create 'My Courses' page
      if ( ! isset( get_page_by_title( 'My Courses' )->ID ) ) { 

        $page_content = '[ld_course_list mycourses="true"]';

        $my_courses_page = array(
          'post_title' => 'My Courses',
          'post_content' => $page_content,
          'post_status' => 'publish',
          'post_type' => 'page',
          'post_author' => $user_id 
        );

        wp_insert_post( $my_courses_page );

      }


      $propel_settings = get_option( 'propel_settings' );

      // 4. Create 'OKM' page
      if ( ! isset( $propel_settings['okm_page_id'] ) ) {

        $org_id = get_the_author_meta( 'propel_org_admin', $user_id ); 

        $page_content = '[propel-okm]';

        $okm_page = array(
          'post_title' => 'OKM',
          'post_content' => $page_content,
          'post_status' => 'private',
          'post_type' => 'page',
          'post_author' => $user_id
        );

        $okm_page_id = wp_insert_post( $okm_page );

        if ( isset( $propel_settings ) )
          $propel_settings['okm_page_id'] = $okm_page_id;
        else
          $propel_settings = array( 'okm_page_id' => $okm_page_id );

        update_option( 'propel_settings', $propel_settings );

      }


      // 5. Create 'Order On-Hold'
      if ( ! isset( $propel_settings['order_on_hold_page_id'] ) ) {

        $page_content = 'Thank you for placing a purchase order with us. We will send you your access keys once we approve the PO.';

        $order_on_hold_page = array(
          'post_title'   => 'Order On-Hold',
          'post_content' => $page_content,
          'post_status'  => 'publish',
          'post_type'    => 'page',
          'post_author'  => $user_id
        );

        $order_on_hold_page_id = wp_insert_post( $order_on_hold_page );

        if ( isset( $propel_settings ) )
          $propel_settings['order_on_hold_page_id'] = $order_on_hold_page_id;
        else
          $propel_settings = array( 'order_on_hold_page_id' => $order_on_hold_page_id );

        update_option( 'propel_settings', $propel_settings );

      }

    }


    /**
     * Creates the 'org_admin' and 'okg_admin' roles by duplicating the subscriber role
     */
    function create_necessary_roles() {
      Propel_Org_Admin::create_role();
      Propel_OKG::create_role();
   }


    /**
     * Checks that the tenant key is installed or kills the system
     */
    function check_tenant_key( $action ) {
      $propel_settings = get_option( 'propel_settings' );

      if ( isset( $propel_settings['okm_tenant_secret_key'] ) ) return;

      echo '<p>There is an error, the OKM tenant key is missing.</p>';
      echo '<p>Please contact the Scitent administrator at <a href="mailto:' . self::OKM_SUPPORT . '">' . self::OKM_SUPPORT . '</a>.</p>';

      if ( empty( $action ) ) {

        // $action completes the phrase 'user was trying to $action.'
        switch ( current_action() ) {
          case woocommerce_checkout_billing:
            $action = 'check out';
            break;
          default:
            $action = '(action is unknown)';
            break;
        }

      }

      wp_mail(
        self::OKM_SUPPORT,
        'FAILURE: No tenant key installed at ' . get_bloginfo( 'name' ),
          'The website ' . get_home_url() . ' is currently down.

        Users are not currently able to purchase or activate keys.

        The user "' . wp_get_current_user()->user_nicename . '" was trying to ' . $action . '.

        Please enter the tenant key at ' . admin_url( 'options-general.php?page=propel-settings&tab=license' )
      );

      die();
    }


    /**
     * Sends curl to Scitent OKM server and receives response
     */
    static function ping_api( $request, $method, $action = 'POST', $content_type = 'application/json' ) {

      $url = self::okm_server() . '/api/' . $method;

      // If is it is a string (not array), add it to the end of the url
      // For example, ?tenant_secret_key=xxxxxxxxxxx
      if ( ! is_array( $request ) )
        $url .= $request;

      $ch = curl_init( $url );

      if ( $action == 'POST' )
        curl_setopt( $ch, CURLOPT_POST, TRUE );

      if ( 'application/json' === $content_type && is_array( $request ) ){
          curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $request ) );
      } else if( 'application/x-www-form-urlencoded' === $content_type ) {
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $request );        
      }


      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
      curl_setopt( $ch, CURLINFO_HEADER_OUT, TRUE);
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0'); 

      if (isset($_COOKIE["authToken"])) {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: ' . $content_type,
                'Authorization: Bearer '.$_COOKIE["authToken"]
              ) );
      } else {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: ' . $content_type
            ) );
      }
        
      // Send the request
      $response = curl_exec( $ch );

      // Check for errors
      if ( $response === FALSE ) {
        die( curl_error( $ch ) );
      }

      // Decode the response
      $responseData = json_decode( $response, TRUE );

      $responseData['http_status'] = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

      curl_close( $ch );

      return $responseData;
    }

    /**
     * Detect Pantheon environment
     * Returns:
     * (if successful) 'dev' | 'test' | 'live' | '...' other multidev value
     * (if failure) WP_Error with details
     */
    static function pantheon_env() {
      if( !isset($_ENV['PANTHEON_ENVIRONMENT']) ) {
        return new WP_Error('not_pantheon','This is not Pantheon.');
      } else {
        return $_ENV['PANTHEON_ENVIRONMENT'];
      }
    }

    /**
     * Boolean version of pantheon_env();
     */
    static function is_this_pantheon_live() {
      return self::pantheon_env() === 'live';
    }

    /**
     * Returns the name of okm server, if saved in settings
     */
    static function okm_server() {

      $settings = get_option( 'propel_settings' );
      if( isset($settings['okm_server_prod']) && !empty($settings['okm_server_prod']) && self::is_this_pantheon_live() ) {
        return $settings['okm_server_prod'];
      } else if( isset($settings['okm_server_stag']) && !empty( $settings['okm_server_stag']) && !self::is_this_pantheon_live() ) {
        return $settings['okm_server_stag'];
      } else {
        return self::OKM_SERVER;
      }

      // if ( isset( $settings['okm_server'] ) && ! empty( $settings['okm_server'] ) )
      //   return $settings['okm_server'];
      // else
      //   return self::OKM_SERVER;
    }


    /**
     * Finds enrollment by given params
     *
     * This returns a *single* enrollment, so will only return the first if multiple found
     */
    static function get_enrollment( $atts ) {

      if ( is_array( $atts ) ) extract( $atts );

      global $wpdb;
      $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;


      $query = "SELECT *
                FROM $propel_table
                ";


      if ( isset( $activation_key ) ) {
        $query .= "WHERE activation_key = '" . $activation_key . "'
                  ";
      }

      $query .= "LIMIT 1";


      $enrollment = $wpdb->get_row( $query, ARRAY_A );

      return $enrollment;
    }


    /**
     * Checks the enrollment_table for a record with the given key,
     *   returning 1 or 0 if the record exists or not
     */
    static function is_enrollment_active( $key ) {

      global $wpdb;
      $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

      // Will return 0 or 1 depending if record exists
      $active = $wpdb->get_var( "
                  SELECT COUNT(*)
                  FROM $propel_table
                  WHERE activation_key = '$key'
                    AND expiration_date > NOW()
                " );

      return $active;
    }

    static function is_enrollment_expired( $key ) {

      global $wpdb;
      $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

      // Will return 0 or 1 depending if record exists
      $active = $wpdb->get_var( "
                  SELECT COUNT(*)
                  FROM $propel_table
                  WHERE activation_key = '$key'
                    AND expiration_date < NOW()
                " );

      return $active;
    }


    /**
     * Unenrolls a key in the enrollments table
     * Unenrollment means setting expiration date to now
     */
    static function unenroll_key( $key ) {
      global $wpdb;
      $enrollments_table = $wpdb->prefix . Propel_DB::enrollments_table;
      $enrollment = $wpdb->get_row( "UPDATE $enrollments_table SET expiration_date = NOW() WHERE activation_key = '$key'" );
    }


}

new Propel_LMS();
