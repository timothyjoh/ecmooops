<?php
 
add_action('admin_menu', 'grassblade_statementviewer_menu', 1);
function grassblade_statementviewer_menu() {
	add_submenu_page("grassblade-lrs-settings", "Statement Viewer", "Statement Viewer",'manage_options','statementviewer-settings', 'grassblade_statementviewer_menupage');
}

function grassblade_statementviewer_menupage()
{
   //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

	echo grassblade_statementviewer(null);

}

function grassblade_statementviewer($attr)
{

	 $shortcode_atts = shortcode_atts ( array(
			'endpoint' => '',
			'user' => '',
			'pass' => '',
			'context' => '',
			'activityid' => '',
			'verb' => '',
			'email' => '',
			'hidecontrol' => false
			), $attr);
		extract($shortcode_atts);

	wp_enqueue_script('jquery');        
	wp_enqueue_script('jquery-ui');
	wp_enqueue_script('jquery-ui-datepicker');
	//wp_enqueue_script('jquery-ui', plugins_url( 'scripts/jquery-ui-1.8.17.custom.min.js' , __FILE__ ));            
	$grassblade_settings = grassblade_settings();

    $grassblade_tincan_endpoint = !empty($endpoint)? $endpoint:$grassblade_settings["endpoint"];
    $grassblade_tincan_user = !empty($endpoint)? $user:$grassblade_settings["user"];
    $grassblade_tincan_password = !empty($endpoint)? $pass:$grassblade_settings["password"];
	
    if (empty($grassblade_tincan_endpoint) || empty($grassblade_tincan_user) || empty($grassblade_tincan_password)) {
    	return "";
    }

	$GrassBladeConfig = array(
							'endpoint' => $grassblade_tincan_endpoint,
							'user' =>  $grassblade_tincan_user,
							'pass' =>  $grassblade_tincan_password
	
						);
	wp_enqueue_script('grassblade-sv-tabs', plugins_url( 'scripts/tabs.js' , __FILE__ ));            
	wp_enqueue_script('grassblade-sv-base64', plugins_url( 'scripts/base64.js' , __FILE__ ));            
	wp_enqueue_script('grassblade-sv-tincan', plugins_url( 'scripts/xapiwrapper.js' , __FILE__ ), array('jquery'));            
	//wp_enqueue_script('grassblade-sv-TinCanQueryUtils', plugins_url( 'resources/scripts/TinCanQueryUtils.js' , __FILE__ ), array('jquery'));            
	wp_enqueue_script('grassblade-sv-config', plugins_url( 'scripts/config.js' , __FILE__ ), array('jquery'));      
	wp_localize_script( 'grassblade-sv-config', 'GrassBladeConfig', $GrassBladeConfig );
	wp_enqueue_script('grassblade-sv-TinCanViewer', plugins_url( 'scripts/XAPIViewer.js' , __FILE__ ), array('jquery', 'grassblade-sv-tincan','grassblade-sv-config' ));            
	

    wp_enqueue_style('grassblade-sv-base', plugins_url( 'css/base.css' , __FILE__ ));             
    wp_enqueue_style('grassblade-sv-skeleton', plugins_url( 'css/skeleton.css' , __FILE__ ));             
    wp_enqueue_style('grassblade-sv-layout', plugins_url( 'css/layout.css' , __FILE__ ));             
    wp_enqueue_style('grassblade-sv-TinCanViewer', plugins_url( 'css/TinCanViewer.css' , __FILE__ ));             
	wp_enqueue_style('grassblade-sv-jquery-ui', plugins_url( 'css/smoothness/jquery-ui-1.8.17.custom.css' , __FILE__ ));             

	
	$html = file_get_contents(dirname(__FILE__)."/index.html");
	$html = str_replace("img/loading.gif", plugins_url( 'img/loading.gif' , __FILE__ ),$html);
	$html = str_replace("[Activity ID]", $activityid,$html);
	$html = str_replace("[Verb]", $verb,$html);

	if($email == "auto") {
		$user_id = get_current_user_id();
		$email = grassblade_user_email($user_id);
	}
	$html = str_replace("[EMAIL]", $email,$html);
	//$html = str_replace("[CONTEXT_CHECKED]", !empty($context)? "CHECKED":"", $html);
	if($hidecontrol)
	$html = str_replace("[HIDE]", "display:none", $html);
	else
	$html = str_replace("[HIDE]", "", $html);
	
	return $html;
}
 add_shortcode("grassblade_statementviewer", "grassblade_statementviewer");

 