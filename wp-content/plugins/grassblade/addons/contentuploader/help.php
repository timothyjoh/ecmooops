<h2 name="xapi_content"><?php _e('xAPI Content Manager','grassblade'); ?></h2>

<a href="#xapi_content" onclick="return showHideOptional('grassblade_cu_howto');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How to upload an xAPI (Tin Can) content package from Articualate or other provider?','grassblade'); ?></span></h3></a>
<div id="grassblade_cu_howto"  class="infoblocks"  style="display:none;">
<p>
<?php 
_e('xAPI Content menu option can be used to upload xAPI Content zip Package. You have to click on \'Add New\' under \'xAPI Content\' menu option. Write a title, select the zip package using uploader, select the version and hit publish. Simple!<br><br>
You can test the upload using the Preview button.<br><br>
Use metabox on any page or post to add the content on that page. Or, use the generated shortcode.<br><br>
Make sure you are using the right version. e.g. For a 0.90 Articulate Content you need to select 0.90 in both content uploader, and the shortcode.','grassblade'); ?>

</p>
</div>
<a href="#xapi_nonxapicontent" onclick="return showHideOptional('grassblade_cu_nonxapi');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('Can I upload Non Tin Can content package from Articulate or other provider?','grassblade'); ?></span></h3></a>
<div id="grassblade_cu_nonxapi"  class="infoblocks"  style="display:none;">
<p>
<?php _e('Currently you can upload non TinCan version of Articulate Studio, Articulate Storyline and Captivate packages using xAPI Content Upload tool. PageViews can be tracked for such packages if you have PageViews feature enabled on your GrassBlade version. Please contact us if you need support for more packages.','grassblade'); ?>

</p>
</div>
<a href="#xapi_completiontracking" onclick="return showHideOptional('grassblade_xapi_completiontracking');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How does completion tracking work?','grassblade'); ?></span></h3></a>
<div id="grassblade_xapi_completiontracking"  class="infoblocks"  style="display:none;">
<p>
<?php echo sprintf(__('Completion Tracking helps in getting content completion information back from the LRS and integrating with other actions. Currently its most relavent to xAPI Content posted on LearnDash lessons, topics, or quizzes. It currently works only with %s installed on the same database. If there are more ideas or requirements for other integrations please contact us.','grassblade'), '<a href="https://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/" target="_blank">GrassBlade LRS</a>'); ?>

</p>
</div>
<a href="#xapi_uploadlimit" onclick="return showHideOptional('grassblade_xapi_uploadlimit');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What to do if my filesize is larger than server upload limit?','grassblade'); ?></span></h3></a>
<div id="grassblade_xapi_uploadlimit"  class="infoblocks"  style="display:none;">
<p>
<?php _e("You have two options:", "grassblade"); ?><br>
<a href='http://www.nextsoftwaresolutions.com/direct-upload-of-tin-can-api-content-from-dropbox-to-wordpress-using-grassblade-xapi-companion/' target='_blank'><?php echo __("1. Use dropbox upload method", "grassblade"); ?></a><br>
<a href='http://www.nextsoftwaresolutions.com/increasing-file-upload-limit/' target='_blank'><?php echo __("2. Increase the file upload limit.", "grassblade"); ?></a><br>
</p>
</div>