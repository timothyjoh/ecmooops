<?php
/* Plugin Name: Author Bio (not Scott Bao)
Plugin URI: http://www.scitent.com
Description: Widget for Showing author information.
Version: 0.1.a
Author: Scitent, Inc
Author URI: http://www.scitent.com
*/

class ScideaAuthorBio extends WP_Widget {
  function ScideaAuthorBio() {
            $widget_ops = array(
            'classname' => 'ScideaAuthorBio',
            'description' => 'Enable Author Bio as a widget.'
  );

  $this->WP_Widget(
            'ScideaAuthorBio',
            'Scidea Author Bio',
            $widget_ops
  );
}

  function widget($args, $instance) { // widget sidebar output
            extract($args, EXTR_SKIP);
            echo $before_widget; // pre-widget code from theme
            if(!$id){ $id = $GLOBALS['post']->ID; }
            $title = get_the_title($id);
            author_bio();
            echo $after_widget; // post-widget code from theme
  }
}
?>

<?php

if(!function_exists('author_bio')){
function author_bio($id=false){
  if(!$id){ $id = $GLOBALS['post']->ID; }
  ?>
            <div class="about-author author-widget">
              <h5 class="author-box-title">Course Author</h5>
              <div class="author-avatar">
                <?php 
                if(isset($_is_retina_)&&$_is_retina_){
                    echo get_avatar( get_the_author_meta('email'), 50, get_template_directory_uri() . '/images/avatar-2x-retina.jpg' ); 
                }else{
                    echo get_avatar( get_the_author_meta('email'), 50, get_template_directory_uri() . '/images/avatar-2x.jpg' ); 
                }?>
              </div>
              <div class="author-info">
                <h4><?php the_author_meta("display_name"); ?></h4>
                <h5><?php the_author_meta("wpseo_title"); ?></h5>
              </div>
              <div class="author-bio">
                <?php the_author_meta('description'); ?>
              </div>
              <div class="clearfix"></div>
            </div><!--/about-author-->
<?php }
}
add_action(
  'widgets_init',
  create_function('','return register_widget("ScideaAuthorBio");')
);
?>