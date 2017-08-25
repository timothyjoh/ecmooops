<?php
/** 
 * Plugin Name: PROPEL Quick Links
 * Version: 0.9
 * Author:  Lou Foster
 * Author URI: http://scitent.com
 * Description: Helper plugin to add a panel of quicklinks to the top of the admin dashboard
 */


class Propel_Quicklinks {
   private $settings;

   function __construct() {
      if ( is_admin() ) {
         add_action('wp_dashboard_setup', array($this,'add_dashboard_widget') );
         add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
         add_filter('screen_options_show_screen', array($this,'hide_dashboard_screen_options') );
         add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
         add_action( 'admin_init', array( $this, 'register_settings' ) );
      }
   }

   public function register_settings() {
      register_setting(
         "propel_quicklink_settings",   // group name - use in settings_fields call
         "propel_quicklink_settings"    // wp_options:option_value 
      );

      add_settings_section(   
         "propel-quicklink-settings",  // id of the section
         null,                         // title displayed at top of section
         null,                         // sanitize
         "propel-quicklink-settings"   // slug name. Match in do_settings_sections call
      );

      add_settings_field(
         'hide_screen_options',
         'Hide Dashboard Screen Options',
         array( $this, 'hide_screen_options_callback' ),
         "propel-quicklink-settings",
         "propel-quicklink-settings"   // match first param of add_settings_section
      );
   }

   public function hide_screen_options_callback() {
      echo('<label>');
      $checked = "";
      if ($this->settings['hide_screen_options']=="on") $checked="checked='checked'";
      echo("<input $checked type='checkbox' name='propel_quicklink_settings[hide_screen_options]'/> Hide Screen Options");
      echo("</label>");
   }

   public function add_settings_menu() {
      add_options_page('PROPEL Quicklinks Settings',
         'PROPEL Quicklinks Settings',
         'manage_options',
         'propel-quicklinks-settings',
         array( $this, 'render_settings_page' )
      );
   }

   public function render_settings_page() {
      $this->settings = get_option( "propel_quicklink_settings" );
      ?>
         <div class="wrap">
            <h2>PROPEL Quicklinks Settings</h2>
         
            <form method="post" action="options.php">
               <div class="wrap">
                  <?php 
                     settings_fields( "propel_quicklink_settings" );
                     do_settings_sections( "propel-quicklink-settings" );
                     submit_button(); 
                  ?>
               </div>
            </form>
         </div>   
      <?php
   }

   public function hide_dashboard_screen_options() {
      $settings = get_option( "propel_quicklink_settings" );
      if ( is_admin() && $settings['hide_screen_options'] == 'on' ) {
         return ( !strcmp($GLOBALS['pagenow'], "index.php") == 0  );
      }
      return true;
   }

   public function add_dashboard_widget() {
      if ( is_admin() ) {
         wp_add_dashboard_widget(
            'propel_quicklinks_widget', 
            'Propel Quicklinks', 
            array($this, 'quicklinks_panel')
         );
      }
   }

   public function quicklinks_panel() {
      $adminUrl = get_admin_url();
      $adminUrl = substr($adminUrl, 0, -1);

      echo('<div id="propel-quicklinks">');
      echo('  <span class="link-header">Quick Links: </span>');
      echo("  <a class='quicklink button first' href='$adminUrl/edit.php?post_type=sfwd-courses'>Learning Content</a>");
      echo("  <a class='quicklink button' href='$adminUrl/edit.php?post_type=page'>Site Content</a>");
      echo("  <a class='quicklink button' href='$adminUrl/admin.php?page=propel-okm'>Distribution</a>");
      echo("  <a class='quicklink button' href='$adminUrl/edit.php?post_type=shop_order'>eCommerce</a>");
      echo("  <a class='quicklink button' href='$adminUrl/edit.php?post_type=product'>Product Catalog</a>");
      echo('</div>');
   }

   public function register_styles() {
      wp_enqueue_style( 'propel-quicklinks',plugins_url( '/css/quicklinks.css', __FILE__ ) );
      wp_enqueue_script('propel-quicklinks', plugins_url( '/js/quicklinks7.js' , __FILE__ ));   
   }
}

new Propel_Quicklinks();
