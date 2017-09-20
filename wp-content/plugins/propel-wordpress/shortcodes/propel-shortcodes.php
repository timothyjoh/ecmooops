<?php

class propel_shortcodes {

   function __construct(){
      add_shortcode( 'propel-key-widget', array( $this, 'key_widget' ) );
      add_shortcode( 'propel-key-activator', array( $this, 'key_activator' ) );
      add_shortcode( 'propel-key-submit', array( $this, 'key_submit' ) );

      add_shortcode( 'propel-tos', array( $this, 'terms_of_service' ) );
      add_shortcode( 'propel-terms-of-service', array( $this, 'terms_of_service' ) );

      add_shortcode( 'propel-okm', array( $this, 'okm' ) );
      add_shortcode( 'current_user_email_in_js', array( $this, 'current_user_email_in_js' ) );

      add_shortcode( 'propel-certificate', array( $this, 'propel_certificate' ) );

      add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
   }

   function current_user_email_in_js( $atts ) {
      global $current_user;
      get_currentuserinfo();
      $email = $current_user->user_email;
      $out = '<script type="text/javascript"> window.current_user_email = "'. $email .'"; </script>';
      $out .= '<p style="color: #bbb; font-size: 60%; text-align: right;">Logged in as: ' . $email . '</p>';
      return $out;
   }


   function key_widget( $atts ) {
      the_widget( 'Propel_LMS_Widget', $atts );
   }


   function key_activator( $atts ) {

      Propel_LMS::check_tenant_key( 'activate key' );

      wp_enqueue_script( 'key-activator' );
      wp_enqueue_style( 'key-activator' );
      wp_enqueue_style( 'dashicons' );

      $out = '';

      $out .= '
         <div class="okm-key-activation" >
            <label>Enter Key: <input type="text" id="okm-key" /></label>

            <ul class="confirmations">
               <li class="validation">
                  <span class="dashicons dashicons-yes" style="display: none;"></span>
                  <span class="dashicons dashicons-no" style="display: none;"></span>
                  <span class="message"></span>
               </li>
               <li class="activation">
                  <span class="dashicons dashicons-yes" style="display: none;"></span>
                  <span class="dashicons dashicons-no" style="display: none;"></span>
                  <span class="message"></span>
               </li>
            </ul>
            <img class="load" src="/wp-includes/js/thickbox/loadingAnimation.gif" />
         </div>
      ';

      return $out;
   }

   function key_submit( $atts ) {
      wp_enqueue_style( 'key-submit' );

      if ( isset( $atts ) && is_array( $atts ) ) extract( $atts );

      if ( ! isset( $button_text ) ) $button_text = 'Activate Key';
      if ( ! isset( $cancel_text ) ) $cancel_text = 'I Decline';
      if ( ! isset( $disabled ) )    $disabled = 'disabled';

      $out  = '<input type="button" id="activate_key" value="' . $button_text . '"' . $disabled . ' />';
      $out .= '<a href="' . get_bloginfo( 'url' ) . '">' . $cancel_text . '</a>';
      $out .= '<br />';

      return $out;
   }

   function terms_of_service( $atts ) {
      wp_enqueue_style( 'terms-of-service' );

      $tos = get_page_by_title( 'Terms of Use' );

      $out  = '<h3>Terms of Use</h3>';
      $out .= '<div class="terms-of-service">';
      $out .=   $tos->post_content;
      $out .= '</div>';

      return $out;
   }


   /**
    * Renders the [propel-okm] shortcode for the 'OKM' admin page
    *   Generally includes an iframe contacting the Scitent OKM server
    *   Shortcode attributes include:
    *     - $org_id
    *     - $width
    *     - $height
    *
    * @author  caseypatrickdriscoll
    *
    * @created 2015-01-19 16:05:05
    *
    * @edited  2015-04-07 11:41:51 - Refactors to require http/https in the URI setting
    *
    * @param   Array    $atts  The attributes sent through the shortcode
    *
    * @return  string   $out   The html output including iframe
    */
   function okm( $atts ) {
      global $current_user;
      get_currentuserinfo();

      if ( isset( $atts ) && ! empty( $atts ) ) extract( $atts );

      if ( ! isset( $org_id ) ) $org_id = get_user_meta( $current_user->ID, 'propel_org_admin', 1 );
      if ( ! isset( $width ) ) $width = '100%';
      if ( ! isset( $height ) ) $height = '3000';

      $settings = get_option( 'propel_settings' );
      $tenant_key = $settings['okm_tenant_secret_key'];
      $a0UserId = $_COOKIE["a0userId"];

      if ( empty( $org_id ) ) {
         return 'No Organization ID set for this Org Admin.';
      }

      wp_enqueue_script('jQuery');  
      wp_enqueue_script( 'porthole.min.js',  plugins_url() . '/propel-wordpress/vendor/porthole/porthole.min.js', array(), null, true );
      wp_enqueue_script( 'okm_parent_porthole.js',  plugins_url() . '/propel-wordpress/js/okm_parent_porthole.js', array(), null, true );
      
      // If OKM is not setup with auth0, use the old auth and iframe
      if ( $settings['okm_sso_enabled'] == 'on' && $a0UserId) {
         error_log("Authenticate with OKM using auth0");
         $out = '<iframe id="okm-frame" name="okm-frame" src="' . Propel_LMS::okm_server() . '/accounts/sso/' . $tenant_key . '" width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="auto"></iframe>';
      } else {
         error_log("Authenticate with OKM using auth tokens");
         $auth_array = array(
             'tenant_secret_key' => $tenant_key,
             'first_name'        => $current_user->user_firstname,
             'last_name'         => $current_user->user_lastname,
             'ext_user_id'       => $current_user->ID,
             'role'              => $current_user->roles[0],
             'email'             => $current_user->user_email,
             'org_id'            => $org_id
         );

         $propel_org_admin = get_user_meta( $current_user->ID, 'propel_org_admin', true );

         if ( ! empty( $propel_org_admin ) ) $auth_array['org_id'] = $propel_org_admin;

         $response = Propel_LMS::ping_api( $auth_array, 'authenticate' );
         $okm_token = $response['auth_token'];

         $out = '<iframe id="okm-frame" name="okm-frame"  width="' . $width . '" height="' .       $height . '" frameborder="0" scrolling="auto"></iframe><script> jQuery(document).ready(function(){ setTimeout(function(){ jQuery("#okm-frame").attr("src", "'. Propel_LMS::okm_server() . '/accounts/' . $okm_token . '/sign_in' .'");}, 200); });</script>';
      }
      return $out;
   }

  /**
   * Renders the [propel-certificate] shortcode for any given course on the course home page
   *   Generally includes an iframe contacting the Scitent OKM server
   *   Shortcode attributes include:
   *     - $embed_code   = short code from the OKM for that specific certificate package
   *     - $button_text
   *     - $height
   *
   * @author  timothy johnson
   *
   * @created 2015-09-28 
   *
   * @param   Array    $atts  The attributes sent through the shortcode
   *
   * @return  string   $out   The html output
   */
  function propel_certificate( $atts_in ) {

    global $current_user;
    get_currentuserinfo();

    if ( isset( $atts_in ) && ! empty( $atts_in ) ) {
      $atts = shortcode_atts( array(
            'embed_code' => '',
            'course' => 'COURSE'
      ), $atts_in );  
    }
    // var_dump($atts);
    // wp_die();
    
    $embed_code = $atts['embed_code'];
    if( '' === $embed_code ) {
      return '';
    }
    $course_id = $atts['course'];

    $propel_settings = get_option( 'propel_settings' );
    $tenant_secret_key = $propel_settings['okm_tenant_secret_key'];
    $okm_server = Propel_LMS::okm_server();
    $course_name = get_the_title( $course );
    $key_code = get_active_enrollment_key($current_user->ID, $course_id);
    
    $button_text = 'Access Your Certificate';
    global $propel_shortcode_embed_certificate_script_included_already;
    if (empty($propel_shortcode_embed_certificate_script_included_already)){

          $out = "
          <script>
            window.certificate_modal_shown = window.certificate_modal_hidden = function() {
              console.log('cert button pressed');
            };
            jQuery(document).ready(function(){
              Claimer = window.Claimer || {}; 
              Claimer.pageVars = {};
              Claimer.pageData = {};

              var cpv = Claimer.pageVars;
              cpv.uri = '".$okm_server."/';
              cpv.location = window.location;
              cpv.tenant_secret_key = '". $tenant_secret_key ."';
              cpv.product = '". $course_name ."';
              cpv.user_email = '". $current_user->user_email ."';
              cpv.first_name = '". $current_user->user_firstname ."';
              cpv.last_name = '". $current_user->user_lastname ."';
              cpv.ext_user_id = '". $current_user->ID ."';
              cpv.button_name = '". $button_text ."';
              cpv.showModalCallback = function(){
                window.certificate_modal_shown();
              };
              cpv.hideModalCallback = function(){
                window.certificate_modal_hidden();
              };
              var script = document.createElement('script');
              script.src = cpv.uri + 'claim/claim.js';
              script.async = true;
              var entry = document.getElementsByTagName('script')[0];
              entry.parentNode.insertBefore(script, entry);               
            });
          </script>
          ";
        
      $propel_shortcode_embed_certificate_script_included_already = true;        
    }
      //$out .= '<div data-claimer-embed-id="1" button_text="Get Your Certificate"></div>';
      $out .= '<div data-claimer-embed-id="' . $embed_code . '" data-claimer-key-code="' . $key_code . '"></div>';


    return $out;
  }


   function register_scripts_and_styles() {
      wp_register_script( 'key-activator',
         plugins_url( '../js/shortcodes/key-activator.js', __FILE__ ),  array( 'jquery' ) );

      wp_register_style( 'key-activator',
         plugins_url( '../css/shortcodes/key-activator.css', __FILE__ ) );
      wp_register_style( 'key-submit',
         plugins_url( '../css/shortcodes/key-submit.css', __FILE__ ) );
      wp_register_style( 'terms-of-service',
         plugins_url( '../css/shortcodes/terms-of-service.css', __FILE__ ) );
   }
}

new propel_shortcodes();