<?php 
  //Register and equeue styles and scripts
  function add_propel_popups_files() {
    wp_register_style('propel-popups-style',  plugin_dir_url( __FILE__ ) .'popups.css');
      wp_enqueue_style('propel-popups-style');
      wp_register_script('propel-popups-script', plugin_dir_url( __FILE__ ) . 'popups.js', array( 'jquery' ));
      wp_enqueue_script('propel-popups-script');
  };
  add_action( 'wp_enqueue_scripts', 'add_propel_popups_files' );  

//register CPT for popups
function propel_popups_custom_post_type()
{
    register_post_type('propel_popup',
       [
         'labels'      => [
             'name'          => __('Propel Popups'),
             'singular_name' => __('Propel Popup'),
         ],
         'public'      => true,
         'has_archive' => false,
         'exclude_from_search' => true,
         'publicly_queryable' => true,
         'menu_icon' => 'dashicons-external',
       ]
    );
}
add_action('init', 'propel_popups_custom_post_type');

//define and add shortcode
function propel_popup_shortcode_fn($given_atts){ 
  $atts = shortcode_atts( array(
      'slug' => '',
      'text' => 'PROPEL-POPUP-LINK-TEXT',
      'linkclasses' => '',
      'popupclasses' => ''
    ), $given_atts );
  $args = array(
    'name' => $atts['slug'],
    'post_type' => 'propel_popup',
    'numberposts' => 1
  );
  $post = get_posts( $args );
  $content = $post[0]->post_content;

  $btnID = $atts['slug'].'-button';

  return (apply_filters ('the_content','<a name="'.$atts['slug'].'" class="launch-propel-popup '.$atts['linkclasses'].'">'.$atts['text'].'</a><div id="'.$atts['slug'].'" class="'.$atts['popupclasses'].' propel-popup-BG"><div class="propel-popup-body"><div class="propel-close">&times;</div>'.$content.'</div></div>'));
}
add_shortcode( 'propel_popup', 'propel_popup_shortcode_fn' );

