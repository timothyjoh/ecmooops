<h2 name="security"><?php _e("Content Security", "grassblade"); ?></h2>

<a href="#security" onclick="return showHideOptional('grassblade_security');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e("What is content security?", "grassblade"); ?></span></h3></a>
<div id="grassblade_security"  class="infoblocks"  style="display:none;">
<p>
	<?php _e('You can disable guest tracking to make sure your content is available only to logged in users. Or you can use 3rd party plugins to create member only access.', 'grassblade'); ?> <br><br>
	<?php _e("However, most of the static content are not protected using these methods. To be able to protect static content from not looged in users, the content security feature of GrassBlade doesn't allow access to your uploaded content even if someone gets direct url to the content.", "grassblade"); ?> 
</p>
</div>
