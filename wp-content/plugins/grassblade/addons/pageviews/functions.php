<?php
require_once(dirname(__FILE__)."/../nss_xapi.class.php");
require_once(dirname(__FILE__)."/pv_xapi.class.php");
 
add_action('admin_menu', 'grassblade_pageviews_menu', 1);
function grassblade_pageviews_menu() {
	add_submenu_page("grassblade-lrs-settings", "PageViews Settings", "PageViews Settings",'manage_options','pageviews-settings', 'grassblade_pageviews_menupage');
}

function grassblade_pageviews_menupage()
{
   //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

	// See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ "update_GrassBladeSettings" ]) ) {
    
        // Save the posted value in the database
		$setting_keys = array(
					'grassblade_pageviews_all', 
					'grassblade_pageviews_usecatagories', 
					'grassblade_pageviews_catagories', 
					'grassblade_pageviews_usetags', 
					'grassblade_pageviews_tags', 
				);
		foreach($setting_keys as $key) {
			if(isset( $_POST[$key]))
			update_option( $key, $_POST[$key]);
			else
			update_option( $key, null);
		}
        // Put an settings updated message on the screen

?>
<div class="updated"><p><strong><?php _e('settings saved.', 'grassblade' ); ?></strong></p></div>
<?php

    }

     // Read in existing option value from database
	$grassblade_settings = grassblade_settings();

    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
    $grassblade_tincan_user = $grassblade_settings["user"];
    $grassblade_tincan_password = $grassblade_settings["password"];
	$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
    
	$grassblade_pageviews_all = get_option('grassblade_pageviews_all');
	$grassblade_pageviews_usecatagories = get_option('grassblade_pageviews_usecatagories');
    $grassblade_pageviews_catagories = get_option('grassblade_pageviews_catagories');
	$grassblade_pageviews_usetags = get_option('grassblade_pageviews_usetags');
    $grassblade_pageviews_tags = get_option('grassblade_pageviews_tags');
    
?>
<style>
.grayblock {
	border: solid 1px #ccc;
	background: #eee;
	padding: 1px 8px;
	width: 30%;
}
</style>
<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2><img style="top: 6px; position: relative;" src="<?php echo plugins_url('img/icon_30x30.png', dirname(dirname(__FILE__))); ?>"/>
GrassBlade PageViews Settings</h2>
<br>
<input name="grassblade_pageviews_all" type="checkbox" style="margin:0 20px" <?php if($grassblade_pageviews_all) echo "CHECKED"; ?> /> Track PageViews of All Pages

<?php if(empty($grassblade_pageviews_all)) { ?>
<br>
<input name="grassblade_pageviews_usecatagories" type="checkbox" style="margin:0 20px" <?php if($grassblade_pageviews_usecatagories) echo "CHECKED"; ?> /> Track PageViews on Specific Categories
	<?php if($grassblade_pageviews_usecatagories) { 
				$args = array(
					'type'                     => 'post',
					'child_of'                 => 0,
					'parent'                   => '',
					'orderby'                  => 'name',
					'order'                    => 'ASC',
					'hide_empty'               => 0,
					'hierarchical'             => 1,
					'exclude'                  => '',
					'include'                  => '',
					'number'                   => '',
					'taxonomy'                 => apply_filters('grassblade_trackable_taxonomies', 
																array('category')
																),
					'pad_counts'               => false ); 
			$categorylist = get_categories($args );
	?>
		<div style="margin-left: 50px; " class="grayblock">
			<h3><b>Select Categories</b></h3>
			<?php echo hierarchy($categorylist, $grassblade_pageviews_catagories); ?>
		</div>
	<?php } ?>
<br>
<input name="grassblade_pageviews_usetags" type="checkbox" style="margin:0 20px" <?php if($grassblade_pageviews_usetags) echo "CHECKED"; ?> /> Track PageViews on Specific Tags
	<?php if($grassblade_pageviews_usetags) { 
				$posttags = get_terms("post_tag", array('hide_empty' => false));

	?>
		<div style="margin-left: 50px; " class="grayblock">
			<h3><b>Select Tags</b></h3>
			<?php 
				$taginputs = "<ul>";
				foreach($posttags as $tag)
				{
					$checked = !empty($grassblade_pageviews_tags[$tag->term_id])? "CHECKED":"";
					$inputbox = '<li><input name="grassblade_pageviews_tags['.$tag->term_id.']" type="checkbox" style="margin:0 5px" '.$checked.'> '.$tag->name.'</li>';
					$taginputs .= $inputbox;
				}
				$taginputs .= "</li>";
				echo $taginputs;
			?>
		</div>
	<?php } ?>

	

<?php } ?>
<div class="submit">
<input type="submit" name="update_GrassBladeSettings" value="<?php _e('Update Settings', 'grassblade') ?>" /></div>
</form>

<br><br>
<?php include(dirname(__FILE__)."/help.php"); ?>
</div>
<?php
}

function hierarchy($categories, $grassblade_pageviews_catagories)
{
	$catpool = $categories;
	
	$hierarchy = array();
	
	$num = count($catpool);
	for($i = 0; $i < $num; $i++)
	{
		$categories_withcatid[$catpool[$i]->cat_ID] = $catpool[$i];
		
		for($j = 0; $j < $num; $j++)
		{
			$catid = $catpool[$j]->cat_ID;
			$parent = $catpool[$j]->category_parent;
			$hierarchy[$parent][$catid] = 1;
		}
	}

	return  hierarchy_rec(0, $hierarchy, $categories_withcatid, $grassblade_pageviews_catagories);
}

function hierarchy_rec($find, $hierarchy,$categories, $grassblade_pageviews_catagories) {
	if(empty($categories[$find]->term_id))
		$inputbox = '';
	else
	{
		$checked = !empty($grassblade_pageviews_catagories[$categories[$find]->term_id])? "CHECKED":"";
		$inputbox = '<input name="grassblade_pageviews_catagories['.$categories[$find]->term_id.']" type="checkbox" style="margin:0 5px" '.$checked.'> '.$categories[$find]->name;
	}
	if(empty($hierarchy[$find]))
		return $inputbox;
	else
	{
		$ret = "";
		foreach($hierarchy[$find] as $k => $v)
		{
			$ret .= "<li>".hierarchy_rec($k, $hierarchy,$categories, $grassblade_pageviews_catagories)."</li>";
		}
		
		if(empty($categories[$find]->term_id))
		return "<ul>".$ret."</ul>";
		else
		return $inputbox."<div style='position:relative; left: 30px;top:5px;'><ul>".$ret."</ul></div>";
	}
}

add_action('wp', 'check_pageview');

function check_pageview()
{
	global $post;

	$grassblade_pageviews_all = get_option('grassblade_pageviews_all');
	$grassblade_pageviews_usecatagories = get_option('grassblade_pageviews_usecatagories');
    $grassblade_pageviews_catagories = get_option('grassblade_pageviews_catagories');
	$grassblade_pageviews_usetags = get_option('grassblade_pageviews_usetags');
    $grassblade_pageviews_tags = get_option('grassblade_pageviews_tags');
	
	$grassblade_settings = grassblade_settings();

    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
    $grassblade_tincan_user = $grassblade_settings["user"];
    $grassblade_tincan_password = $grassblade_settings["password"];
	$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
	$actor = grassblade_getactor($grassblade_tincan_track_guest, "0.95");
	//$title = trim(get_bloginfo('name')." ".wp_title('', false));
	$title = trim(wp_title('|', false, 'right'));
	$title = apply_filters("gb_pageviews_title", $title, $post);

	if(empty($actor))
	return;
	
	if(!is_singular()) //Exit without sending page view for Category and Tag pages.
	return;
	
	$id = $post->ID;
	
	if(empty($grassblade_pageviews_all)) //If not for all pages check further
	{
		if($grassblade_pageviews_usecatagories) //If categories enabled check further
		{
		//Returns All Term Items for "my_term"
		$taxonomies = apply_filters('grassblade_trackable_taxonomies', 
										array('category')
									);
		
		$categories = wp_get_post_terms($id, $taxonomies, array("fields" => "all"));
			//$categories = get_the_category($id);
			//echo "<pre>";
			//print_r($categories);
			if(!empty($categories->errors)) {
				grassblade_debug($categories);
			}
			else
			if((is_object($categories ) || is_array($categories)))
			foreach($categories as $category)
			{
				$cats[$category->term_id] = 1;
			}
			//print_r($cats);
			//print_r($grassblade_pageviews_catagories);
			if(is_object($grassblade_pageviews_catagories ) || is_array($grassblade_pageviews_catagories))
			foreach($grassblade_pageviews_catagories as $category=>$v)
			{
				if(!empty($cats[$category]))
					$pv = 1;
			}
		}
		
		if($grassblade_pageviews_usetags) //If tags enabled check for tags
		{
			$tags = wp_get_post_tags($id);
			
			if(is_object($tags ) || is_array($tags))
			foreach($tags as $tag)
			{
				$tagsarray[$tag->term_id] = 1;
			}			
			if(is_object($grassblade_pageviews_tags ) || is_array($grassblade_pageviews_tags))
			foreach($grassblade_pageviews_tags as $tag=>$v)
			{
				if(!empty($tagsarray[$tag]))
					$pv = 1;
			}		
		}
		
		if(empty($pv))
		{	return;}
	}
	$grassblade_tincan_version = $grassblade_settings["version"];
	if($grassblade_tincan_version >= "1.0")
		$version = "1.0.0";
	else
		$version = "0.95";

	$xapi = new PV_XAPI($grassblade_tincan_endpoint,$grassblade_tincan_user, $grassblade_tincan_password, $version);
	$xapi->SendPageView($actor, $title);

}
