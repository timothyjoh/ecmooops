<?php
//require_once(dirname(__FILE__)."/../nss_xapi.class.php");
//require_once(dirname(__FILE__)."/pv_xapi.class.php");
add_action( 'wp_ajax_nopriv_grassblade_completion_tracking', 'grassblade_grassbladelrs_process_triggers' );
add_action( 'wp_ajax_grassblade_completion_tracking', 'grassblade_grassbladelrs_process_triggers' );

add_action('admin_menu', 'grassblade_grassbladelrs_menu', 1);
function grassblade_grassbladelrs_menu() {
	add_submenu_page("grassblade-lrs-settings", "GrassBlade LRS", "GrassBlade LRS",'manage_options','grassbladelrs-settings', 'grassblade_grassbladelrs_menupage');
}

function grassblade_grassbladelrs_menupage()
{
   //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    $grassblade_settings = grassblade_settings();
    $endpoint = $grassblade_settings["endpoint"];
    $api_user = $grassblade_settings["user"];
    $api_pass = $grassblade_settings["password"];
    $sso_auth = grassblade_file_get_contents_curl($endpoint."?api_user=".$api_user."&api_pass=".$api_pass);
    $sso_auth = json_decode($sso_auth);
    if(!empty($sso_auth) && !empty($sso_auth->sso_auth_token)) {
    	?>
		<div class="wrap">
    	<iframe width="100%" height="1000px" src="<?php echo $endpoint."?sso_auth_token=".$sso_auth->sso_auth_token; ?>"></iframe>
    	</div>
    	<?php
    }
    else {
	?>
		<div class=wrap>
		<h2><img style="top: 6px; position: relative;" src="<?php echo plugins_url('img/icon_30x30.png', dirname(dirname(__FILE__))); ?>"/>
		GrassBlade LRS</h2>
		<br>
		<?php echo sprintf(__("Please install %s and configure the API credentials to use this LRS Management Page"), "<a href='http://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/' target='_blank'>GrassBlade LRS</a>"); ?>
		</div>
	<?php
	}
}

add_action("parse_request", "grassblade_grassbladelrs_process_triggers");

function grassblade_grassbladelrs_process_triggers() {
    if(empty($_REQUEST["grassblade_trigger"]) || empty($_REQUEST["grassblade_completion_tracking"]))
        return;

    if(empty($_REQUEST["statement"]) || empty($_REQUEST["objectid"]) || empty($_REQUEST["agent_id"]))
    {
        echo "Incomplete Data";
        exit;
    }
    $statement = stripcslashes($_REQUEST["statement"]);
    $statement_array = json_decode($statement);
    $objectid = urldecode(stripcslashes($_REQUEST["objectid"]));
    $xapi_content_id = grassblade_xapi_content::get_id_by_activity_id($objectid);
    if(empty( $xapi_content_id)) {
        echo "Activity [".$objectid."] not linked to any content";
        exit;
    }

    $email = urldecode(stripcslashes($_REQUEST["agent_id"]));
    $user = get_user_by_grassblade_email($email);
    if(empty($user->ID)) {
        echo "Unknown user: ".$email;
        exit;
    }

    $statement = apply_filters("grassblade_completed_pre", $statement, $xapi_content_id, $user);
    if(!empty($statement)) {
        update_user_meta($user->ID, "completed_".$xapi_content_id, $statement);
        do_action("grassblade_completed", $statement, $xapi_content_id, $user);
    }
    echo "Processed ".$xapi_content_id;
    exit;
}
