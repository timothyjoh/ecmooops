<h2 name="grassblade_help_faq"><?php _e('Help FAQ', 'grassblade'); ?></h2>

<a href="#grassblade_help_faq" onclick="return showHideOptional('grassblade_whatfor');" name="grassblade_whatfor"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What can "GrassBlade xAPI Companion" be used for?', 'grassblade'); ?></span></h3></a>
<div id="grassblade_whatfor"  class="infoblocks"  style="display:none;">
<p>
<?php _e('1. You can use it for launching TinCan content from wordpress, and track user activity on your LRS.', 'grassblade'); ?>
<br>
<?php _e('2. GrassBlade can track and send statements for common activities like page views to your LRS.', 'grassblade'); ?>
<br>
</p>
</div>


<a href="#grassblade_help_faq" onclick="return showHideOptional('grassblade_userpass');" name="grassblade_userpass"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('Where will I get the endpoint url, user and password from?', 'grassblade'); ?></span></h3></a>
<div id="grassblade_userpass"  class="infoblocks"  style="display:none;">
<p><?php _e('These details are provided by your LRS. If you are using Scorm Cloud from Rustici or WaxLRS. The following details can help:', 'grassblade'); ?><br>
<br>
<b><?php echo sprintf(__('Configuring GrassBlade with %s', 'grassblade'), "<a href='http://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/' target='_blank'>GrassBlade LRS</a>"); ?></b><br>
<?php _e('1. Login to the LRS. Go to All Users. Edit a User.', 'grassblade'); ?><br>
<?php _e('2. Click on Add New Basic AuthToken.', 'grassblade'); ?><br>
<?php _e('3. You will get the required details on the page.', 'grassblade'); ?><br><br>
<b><?php _e('Configuring GrassBlade with Scorm Cloud', 'grassblade'); ?></b><br>
<?php _e('1. Create an application in Scorm Cloud. Get the Application ID and Secret.', 'grassblade'); ?><br>
<?php _e('2. In GrassBlade Settings you will have to enter the following details:', 'grassblade'); ?><br>
<?php _e('<b>Endpoint URL:</b> https://cloud.scorm.com/ScormEngineInterface/TCAPI/<b>&lt;APPLICATIONID&gt;</b>/', 'grassblade'); ?><br>
<?php _e('<b>User:</b> <b>&lt;APPLICATIONID&gt;</b>', 'grassblade'); ?><br>
<?php _e('<b>Password:</b> <b>&lt;APPLICATION SECRET&gt;</b>', 'grassblade'); ?><br><br>

<?php _e('<b>Configuring GrassBlade with WaxLRS</b>', 'grassblade') ?><br>
<?php _e('1. Login at https://&lt;yourcompany&gt;.waxlrs.com/ and go to Settings.<br> 2. Under "Basic Credentials" click on "New Basic Credentials".<br><b>Endpoint URL:</b> Endpoint as mentioned on the settings page e.g. https://&lt;yourcompany&gt;.waxlrs.com/TCAPI/<br><b>User:</b> <b>&lt;Login&gt;</b><br><b>Password:</b> <b>&lt;Password&gt;</b><br><br>', 'grassblade');
?>
<br>
</p>
</div>



<a href="#grassblade_userpass" onclick="return showHideOptional('grassblade_scgen');" name="grassblade_scgen"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How do I add a TinCan Content to a page/post/lesson?', 'grassblade'); ?></span></h3></a>
<div id="grassblade_scgen"  class="infoblocks"  style="display:none;">
<p><?php _e('There are multiple ways to do it. <br><br>
1. Upload the Tin Can content from xAPI Content Manager and use the xAPI Content meta box on your edit page. OR, <br>
2. You can use the shortcode generated on the xAPI Content page.','grassblade'); ?> 
<br>
</p>
</div>


<a href="#grassblade_userpass" onclick="return showHideOptional('grassblade_example_usage');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What are the example usage of shortcode?', 'grassblade'); ?></span></h3></a>
<div id="grassblade_example_usage"  class="infoblocks"  style="display:none;">
<p>
<?php
_e('1. <b>[grassblade id=\'10\']</b>: This will launch xAPI Content (ID = 10), with the settings as defined in xAPI Content section. <br>
<br>
2. <b>[grassblade id=\'10\' width=\'100%\' height=\'100%\' target=\'lightbox\']</b>: This will launch xAPI Content (ID = 10) in a full screen lightbox with other settings as defined in xAPI Content section. <br>
<br>
3. <b>[grassblade src=\'http://www.nextsoftwaresolutions.com/demo/articulate/story.html\']</b>: This will launch the page url http://www.nextsoftwaresolutions.com/demo/articulate/story.html in a 960 X 640px iframe<br>
<br>
4. <b>[grassblade src=\'http://www.nextsoftwaresolutions.com/demo/articulate/story.html\' width=\'900px\' height=\'600px\']</b>: This will launch the page url http://www.nextsoftwaresolutions.com/demo/articulate/story.html in a 900 X 600px iframe
<br>
<br>
5. <b>[grassblade src=\'http://www.nextsoftwaresolutions.com/demo/articulate/story.html\' width=\'900px\' height=\'600px\' endpoint=\'https://mylrsendpoint.com\' user=\'myauthuser\' pass=\'myauthpass\']</b>: Using endpoint details for the tag instead of using them from this settings page. This is for being able to use diffenent LRS for different pieces of content.
</p>', 'grassblade');
?>
</div>

<a href="#grassblade_userpass" onclick="return showHideOptional('grassblade_parameters');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What parameters can be used in the shortcode?', 'grassblade'); ?></span></h3></a>
<div id="grassblade_parameters"  class="infoblocks" style="display:none;">
<p>
<?php _e('1. <b>id</b>: Optional. xAPI Content ID, if provided all other parameters below are optional and will be pulled from values configured in the xAPI Content section. <b>id</b> along with parameters below will overwride the configured parameters.', 'grassblade'); ?>
<br>
<?php _e('1. <b>src</b>*: Required . This URL of content\'s launch page where the content is to be accessed. Optional if <b>id</b> is provided.', 'grassblade'); ?>
<br>
<?php _e('2. <b>width</b>: Optional. Default 940px. Width is the iframe width for the content in which content is launched.','grassblade'); ?>
<br>
<?php _e('3. <b>height</b>: Optional. Default 640px. Height is the iframe height for the content in which content is launched.','grassblade'); ?>
<br>
<?php _e('4. <b>endpoint</b>: Optional. Default is the value of LRS endpoint on this page.','grassblade'); ?>
<br>
<?php _e('5. <b>user</b>: Optional. Default is the value of User on this page.','grassblade'); ?>
<br>
<?php _e('6. <b>pass</b>: Optional. Default is the value if Password on this page.','grassblade'); ?>
<br>
<?php _e('7. <b>Version</b>: Optional. Default <b>1.0</b>. If using 0.90 content, set the version to 0.90','grassblade'); ?>
<br>
<?php _e('8. <b>target</b>: Optional. Default <b>iframe</b>. Use target=\'_blank\' to open in new window. and target=\'lightbox\' to open in a lightbox.','grassblade'); ?>
<br>
<?php _e('9. <b>guest</b>: Optional. Default is the setting on this page. Use <b>1</b> to allow or <b>0</b> to disable guest access for specific content.','grassblade'); ?>
<br>
<?php _e('10. <b>activity_id</b>: Optional**.  This should be a URL unique to your content/activity. **Required by some package types.','grassblade'); ?>
<br>
<?php _e('11. <b>registration</b>: ', 'grassblade'); _e('Optional. Defaults to "36fc1ee0-2849-4bb9-b697-71cd4cad1b6e", type in the UUID for a specifc fixed UUID. Or "auto" if you want a unique UUID generated for a group of activities for every launch. Hence every attempt is assumed to be unique, as long as the page is refreshed before re-launch.','grassblade'); ?>

</p>

</div>

<a href="#grassblade_userpass" onclick="return showHideOptional('grassblade_guest');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How does Guest tracking work?','grassblade'); ?></span></h3></a>
<div id="grassblade_guest"  class="infoblocks"  style="display:none;">
<p>
<?php _e('Guest Tracking feature is to be able to track activities of users who are not logged in. Just for tracking purposes, Name and Email will be decided as mentioned in the grassblade settings page.','grassblade'); ?>
</p>
</div>
<?php
$GrassBladeAddons = new GrassBladeAddons();
$GrassBladeAddons->IncludeHelpFiles();
?>
<br>
<br> 
<h2><?php _e('Need More Features?','grassblade'); ?></h2>
<p>
<?php _e('Contact us if you need more features, custom reports or want any customizations to GrassBlade.','grassblade'); ?><br><br>
<b><?php _e('Email','grassblade'); ?>:</b> <a href="mailto:contact@nextsoftwaresolutions.com" target="_blank">contact@nextsoftwaresolutions.com</a><br><br>
<b><?php _e('Contact Page','grassblade'); ?>:</b> <a href="http://www.nextsoftwaresolutions.com/contact-us/"  target="_blank">http://www.nextsoftwaresolutions.com/contact-us/</a><br>
</p>
