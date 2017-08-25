<?php
function grassblade_security_check() {

	if(empty($_REQUEST['grassblade_security_check']) || empty($_REQUEST['file']))
	{
		return;
	}
	$request_uri = $_SERVER["REQUEST_URI"];
	$file_path_part = explode("wp-content", $request_uri, 2);
	$file_with_query_strings = WP_CONTENT_DIR.(@$file_path_part[1]);
	$file_parts = parse_url($file_with_query_strings);
	$file = @$file_parts["path"];
	$user = wp_get_current_user();
	if(!file_exists($file))
	{
		echo "Invalid Request.";
		exit;
	}
	if(!empty($user->ID))
	readfile($file);
	else {
		echo "Access Denied.";
	}
	exit;
}
add_action("parse_request", "grassblade_security_check");

add_action( 'save_post', 'grassblade_security_check_gb_xapi_content_box_save', 11, 1);
function grassblade_security_check_gb_xapi_content_box_save($post_id) {
	$post = get_post( $post_id);
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	if ( !isset($_POST['gb_xapi_content_box_content_nonce']) || !wp_verify_nonce( $_POST['gb_xapi_content_box_content_nonce'], "grassblade/addons/contentuploader/functions.php" ) )
		return;
	

	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return;
	}
	grassblade_security_secure_content($post_id);
}
function grassblade_security_secure_content($post_id) {
    $params = grassblade_xapi_content::get_params($post_id);
    if(empty($params["src"]))
            return;

    $src = $params["src"];
    $siteurl = get_bloginfo('url');
    $siteurl_without_http = str_replace(array("http://", "https://"), array("", ""), $siteurl);

    if(strpos($src, $siteurl_without_http) === false)
            return;

    $grassblade_settings = grassblade_settings();
    $security_enable = $grassblade_settings["security_enable"];
    if(empty($security_enable))
    $add_htaccess = false;
    else
    {
	    if(@$params["guest"] == "0" || @$params["guest"] == 1)
	    $add_htaccess = empty($params["guest"]);
	    else
	    {
	            $track_guest = $grassblade_settings["track_guest"];
	            $security_enable = $grassblade_settings["security_enable"];
	            $add_htaccess = empty($track_guest) && $security_enable;
	    }
    }
    $file_path_part = explode("wp-content", $src, 2);
    if(empty($file_path_part[1]))
            return;

    $path_diff = dirname($file_path_part[1])."/";//WP_CONTENT_DIR.(@$file_path_part[1]);

    $slashes = preg_replace("/[^\\/]/", "", $path_diff);
    $wordpress_index_file = get_home_path();//str_replace("/", "../", $slashes)."index.php";
    $htaccess_file = WP_CONTENT_DIR.$path_diff.".htaccess";
    grassblade_security_add_htaccess($add_htaccess, $wordpress_index_file, $htaccess_file, $post_id);
} 
function grassblade_security_add_htaccess($add_htaccess, $wordpress_index_file, $htaccess_file, $post_id) {
	$htaccess_file_exists = file_exists($htaccess_file);

	if(empty($add_htaccess) && $htaccess_file_exists) {
		$deleted = @unlink($htaccess_file);
		if(empty($deleted)) {
			$notice = sprintf(__("Could not delete .htaccess file: %s. ", "grassblade"), $htaccess_file);
			if(!is_writable($htaccess_file)) {
				$notice = $notice . " " . __("File is not writable.", "grassblade");
			}
			grassblade_admin_notice($notice);
		}
	}

	if($add_htaccess && empty($htaccess_file_exists))
	{
		$htaccess_file_template = apply_filters("grassblade_security_htaccess_file_template", dirname(__FILE__)."/htaccess.txt", $post_id);
		$htaccess_file_content = file_get_contents($htaccess_file_template);
		$htaccess_file_content = str_replace("[WORDPRESS_INDEX_FILE]", $wordpress_index_file, $htaccess_file_content);
		$fh = @fopen($htaccess_file, "w");
		$error = false;
		if(!$fh)
			$error = true;
		else
		{
			$written = fwrite($fh, $htaccess_file_content);
			if(empty($written))
				$error = true;
			fclose($fh);
			if($error) {
				$notice = sprintf(__("Could not add .htaccess file: %s. ", "grassblade"), $htaccess_file);			
				$notice = $notice . " " . __("File is not writable.", "grassblade");
				grassblade_admin_notice($notice);				
			}
		}	
	}
}

add_action("grassblade_settings_update", "grassblade_security_grassblade_settings_update", 5, 2);
function grassblade_security_grassblade_settings_update($grassblade_settings_old, $grassblade_settings_new) {
	if($grassblade_settings_old['track_guest'] != $grassblade_settings_new['track_guest']) {
		$posts = get_posts('post_type=gb_xapi_content&posts_per_page=-1');
		foreach ($posts as $post) {
			grassblade_security_secure_content($post->ID);
		}
	}
}

function grassblade_security_fields($fields) {
	$updated_fields = array();
	foreach ($fields as $key => $value) {
		if($value["id"] == "content_settings_end")
		{
			$updated_fields[] = array( 'id' => 'security_enable', 'label' => __( 'Enable Content Security', 'grassblade' ),  'placeholder' => '', 'type' => 'checkbox', 'values'=> '', 'never_hide' => true ,	'help' => __('Disables direct url access to key static files in the content to users who are not logged in. Disabled for content with guest access. Protected file types: gif,jpeg,jpg,png,mp4,mp3,mpg,mpeg,avi,html.', 'grassblade'));
		}
		$updated_fields[] = $value;

	}
	return $updated_fields;
}
add_filter("grassblade_settings_fields", "grassblade_security_fields", 1, 1);

