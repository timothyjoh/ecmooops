<?php

/*
Plugin Name: Scitent Admin Theme
Plugin URI: 
Description: Admin theme Plugin for PROPEL Enterprise Site
Author: SDaimond
Version: 1.0
Author URI: 
*/


//Load Style Sheet
function sci_admin_theme_style() {
    wp_enqueue_style('sci-admin-theme', plugins_url('minAdmin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'sci_admin_theme_style');
add_action('login_enqueue_scripts', 'sci_admin_theme_style');
add_action('admin_print_styles', 'sci_admin_theme_style', 51); 


//replace "Howdy"
function howdy_message($translated_text, $text, $domain) {
    $new_message = str_replace('Howdy', 'Welcome to PROPEL<sup>TM</sup>', $text);
    return $new_message;
}
add_filter('gettext', 'howdy_message', 10, 3);

// Change Footer
function remove_footer_admin () {
    echo "PROPEL Powered by <a href='http://www.scitent.com' target='_blank'>Scitent Inc</a>";
} 

add_filter('admin_footer_text', 'remove_footer_admin');

//change login page
// loginlogo
function my_login_logo() { ?>
    <style type="text/css">
        .login h1 a {
            background-image: url(https://propel.scitent.us/wp-content/uploads/2014/10/propel-mark-only-admin-logo-large.png);
            padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );
//loginlink
function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
    return 'Propel: Enterprise + Distribute';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );