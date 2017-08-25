<h2 name="state_faq"><?php _e('State API Shortcodes', 'grassblade'); ?></h2>

<a href="#state_faq" onclick="return showHideOptional('state_whatfor');" name="state_whatfor"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What does State API do?', 'grassblade'); ?></span></h3></a>
<div id="state_whatfor"  class="infoblocks"  style="display:none;">
<p>
<?php _e('This is a scratch area for Activity Providers on the LRS. It can be used to store user specific data related or specific activities. A perfect example would be bookmark. State API can be used to track which lesson of a course user was last on. It could be in turn used to resume the users session from where he left.', 'grassblade'); ?>
</p>
</div>

<a href="#state_faq" onclick="return showHideOptional('state_example_usage');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What are the example usage?', 'grassblade'); ?></span></h3></a>
<div id="state_example_usage"  class="infoblocks"  style="display:none;">
<p>
<?php
_e('1. <b>[set_state activityid="http://www.nextsoftwaresolutions.com/activity/course-01" stateid="bookmark"  data="http://www.nextsoftwaresolutions.com/activity/lesson-01" ]</b>: When a user visits the page with this shortcode, it will store the lesson url as the current state of bookmark for the activity.<br>
<br>
2. <b>[get_state activityid="http://www.nextsoftwaresolutions.com/activity/course-01" stateid="bookmark"]</b>: This will fetch and display the bookmark url of the course anywhere this shortcode is used. 
<br><br>
<b>Example use: Pause and Resume</b><br>
One can easily add these shortcodes on each training/lesson page of a course. And create a resume button on the Course Page, something like:
<xmp><a href=\'[get_state activityid="http://www.nextsoftwaresolutions.com/activity/course-01" stateid="bookmark"]\'>Resume</a></xmp>

Now everytime a user comes back to the course page, he can click on the Resume button and start back where he left. 

</p>', 'grassblade');
?>
</div>