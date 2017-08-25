<h2 name="pageviews_tracking"><?php _e('PageViews Tracking','grassblade'); ?></h2>

<a href="#pageviews_tracking" onclick="return showHideOptional('grassblade_pv_whatis');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What is PageViews tracking?','grassblade'); ?></span></h3></a>
<div id="grassblade_pv_whatis"  class="infoblocks"  style="display:none;">
<p>
<?php _e('PageViews tracking feature sends page view details to the LRS. Every time someone visits a page that is being tracked by PageViews, an xAPI statement is sent to the LRS.','grassblade') ?>
</p>
</div>

<a href="#pageviews_tracking" onclick="return showHideOptional('grassblade_pv_ver');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('Which TinCan(xAPI) Version is used to send statement sent for PageViews?','grassblade'); ?></span></h3></a>
<div id="grassblade_pv_ver"  class="infoblocks"  style="display:none;">
<p>
<?php _e('Currently PageViews Tracking information is sent in the latest TinCan(xAPI) Version. i.e. 0.95<br>Please look for GrassBlade Updates if a newer xAPI version is released.','grassblade'); ?>
</p>
</div>


<a href="#pageviews_tracking" onclick="return showHideOptional('grassblade_pv_use');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How does GrassBlade decide which Pages/Posts to track?','grassblade'); ?></span></h3></a>
<div id="grassblade_pv_use"  class="infoblocks"  style="display:none;">
<p>
<?php _e('Based on the settings on PageViews Settings page, you can choose to track all Pages and Posts. Or, choose to track posts in specific categories or specific tags.','grassblade'); ?>
</p>
</div>
