<?php

class Propel_User_Reg {

  function __construct() {

    add_action( 'login_form_register',
      array( $this, 'email_address_is_username' ) );

    add_filter('registration_errors',
      array( $this, 'blank_username_is_ok'), 10, 3);

    add_action('wpum_get_registration_fields',
      array( $this, 'terms_conditions_link_reword'), 18 );

    add_action('wpum_get_registration_fields',
      array( $this, 'terms_conditions_link_to_end'), 20 ); // execute late, after other fields (below)

    // add_action('wpum/form/register/before/field=user_email',
    //   array( $this, 'email_icon'), 10 );

    // add_action('wpum/form/register/before/field=password',
    //   array( $this, 'padlock_icon'), 10 );

    $warnings = array( 'password', 'role', 'wpum_profession', 'terms' );
    foreach( $warnings as $id ) {
      add_action('wpum/form/register/after/field='.$id,
        array( $this, 'generic_required_warning' ), 10 );
    }

    add_action( 'wp_enqueue_scripts', 
      array( $this, 'registration_form_styles_and_scripts' ), 20 ); // execute really late, to dequeue ascend too


    add_filter( 'lostpassword_url',  
      array( $this, 'use_wpum_lostpassword'), 10, 0 );

    // add_action( 'after_propel_generate_keys',
    //   array( $this, 'auto_enroll_user_in_courses' ) );

    // add_action('woocommerce_checkout_update_user_meta', 
    //   array( $this, 'auto_enroll_update_user_meta'), 2, 3 );

  }

  function registration_form_styles_and_scripts() {
    if( strpos( $_SERVER['REQUEST_URI'], 'register' ) === false ) {
      return;
    }

    //////////////// JS ////////////////////////////
    global $wp_scripts;
    foreach( $wp_scripts->registered as $handle => $details ) {
      if( !in_array( $handle, array("jquery","jquery-core","jquery-migrate","jquery-ui-core"))) {
        unset( $wp_scripts->registered[ $handle ] );
      }
    }

    //////////////// CSS ///////////////////////////
    global $wp_styles;
    foreach( $wp_styles->registered as $handle => $details ) {
      if( !in_array( $handle, array("parent-style","main-styles","nectar-ie8","rgs","orbit",
        "twentytwenty","woocommerce","font-awesome","responsive","ascend","skin-ascend",
        "select2","non-responsive","common","admin-menu","dashboard","edit","themes","nav-menus",
        "site-icon","wp-admin","dashicons","forms","list-tables","revisions","media","about",
        "widgets","l10n","login","buttons","ie"))) {
        unset( $wp_styles->registered[ $handle ] );
      }
    }
    wp_enqueue_style( 'register-form-style', 
              plugins_url( '/css/register.css', __FILE__ ) );

    wp_enqueue_script( 'register-form-script',
              plugins_url( '/js/register.js', __FILE__ ),
              array('jquery') );
  }

  function blank_username_is_ok( $wp_error, $sanitized_user_login, $user_email ) {
    if(isset($wp_error->errors['empty_username'])){
      unset($wp_error->errors['empty_username']);
    }

    if(isset($wp_error->errors['username_exists'])){
      unset($wp_error->errors['username_exists']);
    }
    return $wp_error;
  }
  
  function terms_conditions_link_reword( $fields ) {
    if( array_key_exists('terms', $fields ) ) {
      $tc_mssg = $fields['terms']['description'];
      // error_log('Here are TERMS: ' . http_build_query($tc_mssg));
      $fields['terms']['description'] = 'To complete registration, you must read and agree to our ';
      $fields['terms']['description'] .= substr($tc_mssg, strpos($tc_mssg,'<a'));
    }
    return $fields;
  }

  function terms_conditions_link_to_end( $fields ) {
    if( array_key_exists('terms', $fields ) ) {
      $ts_and_cs = $fields['terms'];
      unset( $fields['terms'] );
      $fields['terms'] = $ts_and_cs;
    }
    return $fields;
  }

  function email_icon() {
    ?>
      <div class='propel-form-icon' id='email-icon'>
        <div class='icon-circle'>
          <i class="fa-envelope-o">
          </i>
        </div>
      </div>
    <?php
  }

  function padlock_icon() {
    ?>
      <div class='propel-form-icon' id='padlock-icon'>
        <div class='icon-circle'>
          <i class="fa-lock" style="font-size: 22px;">
          </i>
        </div>
      </div>
    <?php
  }

  function email_address_is_username() {
    if(isset($_POST['user_login']) && isset($_POST['user_email']) && !empty($_POST['user_email'])){
      $_POST['user_login'] = $_POST['user_email'];
    }
  }

  function generic_required_warning( $field ) {
    $this->echo_userpro_style_warning( __('This is required', 'propel' ) );
  }

  function use_wpum_lostpassword() {
    return site_url('/password-reset/');
  }


  private function echo_userpro_style_warning( $message ) {
    ?>
      <div class="userpro-warning" style="top: 0px; opacity: 1;">
        <i class="fa-caret-up">
        </i><?php echo $message; ?>
      </div>
    <?php
  }
}

new Propel_User_Reg();