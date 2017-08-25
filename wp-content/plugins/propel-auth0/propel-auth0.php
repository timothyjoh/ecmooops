<?php
/**
 * Plugin Name: PROPEL/Auth0 Database bridge
 * Description: Exposes an API to be used by an auth0 custom database to validate user credentials. 
 *              Requires WP API 2 - https://wordpress.org/plugins/rest-api/
 * Author:  Lou Foster
 * Author URI: 
 * Version: 0.1
 * Plugin URI: 
 */

/**
 * Extract username/password from POST payload and validate credentials.
 * Return an Auth0 profile as if successful
 */
function validate_credentials( $data ) {
	$email  = $data->get_param( 'email' );
	$pw = $data->get_param( 'password' );
	$u = get_user_by("email", $email);
	if ( $u == false ) {
		return new WP_REST_Response( "Invalid username or password", 401 );
	}
	$result = wp_authenticate($u->user_login, $pw);
	if ( is_wp_error($result) ) {
		return new WP_REST_Response( "Invalid username or password", 401 );
	}

	$role = $u->roles[0];
	$id = "propel-wp-".$u->ID;
	$out = array("user_id"=>$id, "name"=>$u->display_name, "email"=>$email, "role"=>$role);
	return new WP_REST_Response( $out, 200 );
}

/**
 * Extract base64 encoded email from URL params and check if this is
 * a registered WP user. If so, return the minimal auth0 profile as json
 */
function get_user_profile( $data ) {
	$id  = $data->get_param( 'id' );
	$email = base64_decode($id);
	$u = get_user_by("email", $email);
	if ( $u == false ) {
		return new WP_REST_Response( "",404 );
	}
	
	$role = $u->roles[0];
	$id = "propel-wp-".$u->ID;
	$out = array("user_id"=>$id, "name"=>$u->display_name, "email"=>$email, "role"=>$role);
	return  new WP_REST_Response( $out, 200 );
}

//
// Register the endpoints with WP API
// 
add_action( 'rest_api_init', function () {
    register_rest_route( 'scitent/v1', '/auth', array(
        'methods' => 'POST',
        'callback' => 'validate_credentials',
    ) );

    register_rest_route( 'scitent/v1', '/profile/(?P<id>.+)', array(
        'methods' => 'GET',
        'callback' => 'get_user_profile',
    ) );
} );
