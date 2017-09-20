<?php 

if( !defined( 'PROPEL_CSR_ADMIN_EMAIL' ) ) {
  define( 'PROPEL_CSR_ADMIN_EMAIL', 'purchase.orders@scitent.com' );
}

function salient_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array('font-awesome'));
    wp_enqueue_style( 'catalog-style', get_stylesheet_directory_uri() . '/css/catalog-style.css');
    wp_enqueue_style( 'my-courses-style', get_stylesheet_directory_uri() . '/css/my-courses-style.css');
}
add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles');

function course_catalog_js() {
    wp_enqueue_script( 'course_catalog_js', get_stylesheet_directory_uri() . '/js/tmci-catalog.js', array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'course_catalog_js' );

function return_to_course_button() {
  $post_type = get_post_type();
  $post_id = get_post()->ID;

  if ( $post_type == 'sfwd-lessons' || $post_type == 'sfwd-quiz' ) {
    $course_id = learndash_get_course_id($post_id);
    $course_link = get_permalink($course_id);
    echo "<a class='button' href='$course_link'> Return to Course </a>";
  }
}



//sku product page redirects
function redirect_sku_slugs() {
  global $wpdb;
  $uri = explode('/', $_SERVER["REQUEST_URI"]);
  if ($uri[1] == 'sku') {
    error_log("sku slugs loaded: ".$uri[2]);
    $product_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1";
    $product_id = $wpdb->get_var( 
      $wpdb->prepare( 
        $product_query, 
        $uri[2] 
      ) );
    error_log($product_id);
    error_log(get_permalink( $product_id ));
    if ($product_id){
      wp_redirect(get_permalink( $product_id )); 
      exit;
    }
  }
}
add_action( 'init', 'redirect_sku_slugs' );

//OKG Menu
function register_okg_menu() {
  register_nav_menu('okg-menu',__( 'OKG Menu' ));
}
add_action( 'init', 'register_okg_menu' );