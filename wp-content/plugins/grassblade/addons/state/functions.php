<?php
require_once(dirname(__FILE__)."/../nss_xapi_state.class.php");

function get_state($atts) {
	global $post;
		grassblade_debug("get_state: ".$post->post_title);
	 $shortcode_atts = shortcode_atts ( array(
			'activityid' => '',
			'stateid' => '',
			'registration' => '',
			'user_id' => '',
			'email' => '',
			'name' => '',
			'data' => '',
			'display' => true,
			'set' => false,
			), $atts);
	extract($shortcode_atts);
	$grassblade_settings = grassblade_settings();	
	
	$xapi = new NSS_XAPI_STATE($grassblade_settings["endpoint"], $grassblade_settings["user"], $grassblade_settings["password"]);
	if(empty($activityid) && !empty($_GET['activityid']))
	$activityid = @$_GET['activityid'];
	
	if(empty($stateid) && !empty($_GET['stateid']))
	$stateid = @$_GET['stateid'];
	
	if(empty($user_id)) {
		$email = (!empty($_GET['email']) && empty($email))? $_GET['email']:$email;
		if(!empty($email))
		{
			$user = get_user_by_grassblade_email($email);
		}
	}
	else
	$user = get_user_by("id", $user_id);

	if(empty($user->ID)) {
		if(!empty($email)) {
			$email_parts = explode("@", $email);
			$name = !empty($name)? $name:$email_parts[0];
			$agent = $xapi->set_actor($name, $email);
		}
		else
		$agent = grassblade_getactor($grassblade_settings["track_guest"]);
	}
	else
	$agent = $xapi->set_actor($user->display_name, $user->user_email);

	if(empty($data) && isset($_GET['data']))
	$data = @$_GET['data'];
	
	if(!empty($data) && $set == true) {
		grassblade_debug("calling sendstate");
		grassblade_debug($data);
		grassblade_debug($set);
		grassblade_debug(get_the_title());
		$ret = $xapi->SendState($activityid, $agent, $stateid, $data);
		grassblade_debug($ret);
	}

	$value = $xapi->GetState($activityid, $agent, $stateid);
	
	if(!empty($display))
	return print_r($value, true);
	else
	return '';
		
}
function set_state($attr) {
	global $post;
		grassblade_debug("set_state: ".$post->post_title);

	 $attr = shortcode_atts ( array(
			'activityid' => '',
			'stateid' => '',
			'registration' => '',
			'user_id' => '',
			'email' => '',
			'name' => '',
			'data' => '',	 
			'display' => false,
			'set' => true
			), $attr);	
	return get_state($attr);
}
add_shortcode("get_state", "get_state");
add_shortcode("set_state", "set_state");