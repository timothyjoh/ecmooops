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


add_filter('the_content', 'propel_popup_bodies', 20);

function propel_popup_bodies($the_content ){
 	if (strpos ($the_content, 'launch-propel-popup-placeholder') === false){ return($the_content); }
	$dom = new DOMDocument();
	$dom->loadHTML($the_content);

   $finder = new DomXPath($dom);
   $classname="launch-propel-popup-placeholder";
   $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

  foreach($nodes as $node) {
    $slug = $node->attributes->getNamedItem('name')->nodeValue;

    $popupclasses = $node->attributes->getNamedItem('data-popupclasses')->nodeValue;
    $args = array(
      'name' => $slug,
      'post_type' => 'propel_popup',
      'numberposts' => 1
    );
    $post = get_posts( $args );
    $post_content = $post[0]->post_content;    
    $check_shortcodes = preg_match_all('/\[[\w\s=\'\"]+\]/', $post_content, $shortcodes);
    foreach ($shortcodes[0] as $shortcode){
      $post_content = str_replace($shortcode, do_shortcode($shortcode), $post_content);
    }

    $the_content .= '<div id="'.$slug.'" class="'.$popupclasses.' propel-popup-BG"><div class="propel-popup-body"><div class="propel-close">&times;</div>'.$post_content.'</div></div>';

  }

  // DONT DO THIS::: apply_filters('the_content',);

  $the_content = str_replace('launch-propel-popup-placeholder','launch-propel-popup',$the_content);
  return ($the_content);
}

//define and add shortcode
function propel_popup_shortcode_fn($given_atts){ 
  $atts = shortcode_atts( array(
      'slug' => '',
      'text' => 'PROPEL-POPUP-LINK-TEXT',
      'linkclasses' => '',
      'popupclasses' => ''
    ), $given_atts );

  $btnID = $atts['slug'].'-button';
  $result = '<a name="'.$atts['slug'].'" class="launch-propel-popup-placeholder '.$atts['linkclasses'].'" data-popupclasses="'. $atts['popupclasses'] .'">'.$atts['text'].'</a>';

  $clean_result1 = preg_replace('/<\/?p>/', ' ', $result, 2);
  $clean_result2 = preg_replace('/\r?\n/', ' ', $clean_result1, -1);
  return ($clean_result2);
}
add_shortcode( 'propel_popup', 'propel_popup_shortcode_fn' );

