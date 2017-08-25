<?php
/**
 * Description: Exposes an API to be used by propel okm to manage administrators.
 *              Requires WP API 2 - https://wordpress.org/plugins/rest-api/
 * Author:      Adam Schiller
 * Author URI: 
 * Version: 0.1
 * Plugin URI: 
 */

/**
 * Authorization function
 */
function validate_tenant( $okm_secret_key ) {
  $propel_settings = get_option( 'propel_settings' );
  $wp_secret_key = $propel_settings['okm_tenant_secret_key'];
  if ( $okm_secret_key == $wp_secret_key ) {
    return true;
  } else {
    return false;
  }
}

/**
 * Find or Create user and add as appropriate administrator (with correct meta data if necessary).  Send email.
 * Return json: ext_user_id, role
 */
function add_administrator( $data ) {
  error_log("administrator api /add called");

  // Authorization based on tenant_secret_key
  $secret_key      = $data->get_param( 'tenant_secret_key' );
  if ( validate_tenant( $secret_key ) == false ) {
    return new WP_REST_Response( array("errors"=>"Permission denied.  Invalid tenant_secret_key."), 401 );
  }

  $email           = $data->get_param( 'email' );
  $role            = $data->get_param( 'role' );
  $propel_org_id   = $data->get_param( 'propel_org_id' );
  $propel_org_name = $data->get_param( 'propel_org_name' );

  $u = get_user_by("email", $email);

  $sitename = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

  if ( $u == false ) {

    global $nosyncuser;
    $nosyncuser = true;

    // create new user and send email
    $password = 'password'; //wp_generate_password(8, false);
    $return = wp_create_user($email, $password, $email);
    if ( is_wp_error( $return ) ) {
      return new WP_REST_Response( $return->get_error_messages(), 401 );
    } else {
      $body  = sprintf(__('Welcome to %s:'), $sitename) . "\r\n\r\n";
      $body .= sprintf(__('Username: %s'), $email) . "\r\n";
      $body .= sprintf(__('Password: %s'), $password) . "\r\n\r\n";

      $u = get_user_by( "id", $return );
    }
  } else {
    // check that user doesn't already have role or higher
    if ( user_can($u, 'administrator') || user_can($u, 'org_admin') ) {
      $message = ($u->propel_org_admin == $propel_org_id) ? "User already has administrator role for this organization" : "User already has equal or greater role";
      return new WP_REST_Response( array("errors"=>$message), 401 );
    }
    $body = "";
  }
  
  // apply role/propel_org_id to user
  $u->set_role( $role );
  if ( isset($propel_org_id) ) {
    update_user_meta( $u->ID, 'propel_org_admin', $propel_org_id );
    $body .= sprintf(__('Organization: %s'), $propel_org_name) . "\r\n\r\n";
  }

  // send email to new admin
  $subject = $sitename." -- You have been invited as an administrator.";
  $body .= "Follow this link to login: ".wp_login_url()."\r\n\r\n";
  $body .= "Thanks!";

  wp_mail( $email, $subject, $body );

  $out = array("ext_user_id"=>$u->ID, "role"=>$u->roles[0]);
  error_log("success: ".json_encode($out));
  return new WP_REST_Response( $out, 201 );
}


/**
 * Remove role from administrator (and propel_org_admin meta if present)
 * returns json: ext_user_id, role
 */
function remove_administrator( $data ) {
  error_log("administrator api /remove called");

  // Authorization based on tenant_secret_key
  $secret_key = $data->get_param( 'tenant_secret_key' );
  if ( validate_tenant( $secret_key ) == false ) {
    return new WP_REST_Response( array("errors"=>"Permission denied.  Invalid tenant_secret_key."), 401 );
  }

  $id             = $data->get_param( 'id' );
  $role           = $data->get_param( 'role' );
  $propel_org_id  = $data->get_param( 'propel_org_id' );

  $u = get_user_by("id", $id);
  if ( $u != false ) {
    $u->remove_role( $role );
    if ( isset($propel_org_id) ) {
      delete_user_meta( $u->ID, 'propel_org_admin' );
    }
  }
  
  $out = array("ext_user_id"=>$u->ID, "role"=>$u->roles[0]);
  error_log("success: ".json_encode($out));
  return new WP_REST_Response( $out, 200 );
}

//
// Register the endpoints with WP API
// 
add_action( 'rest_api_init', function () {
    register_rest_route( '/api/v1/manage_administrators', '/add', array(
        'methods' => 'POST',
        'callback' => 'add_administrator'
    ) );

    register_rest_route( '/api/v1/manage_administrators', '/remove', array(
        'methods' => 'PUT',
        'callback' => 'remove_administrator'
    ) );
} );
