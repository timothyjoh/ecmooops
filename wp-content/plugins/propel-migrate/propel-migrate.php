<?php
/** 
 * Plugin Name: PROPEL Migrate
 * Version: 0.01
 * Author:  Lou Foster
 * Author URI: http://scitent.com
 * Description: Helper plugin to ease migration between Production and Staging PROPEL installs
 */
class Propel_Migrate {
   private $settings;
   private $set;

   function __construct() {
      if ( is_admin() ){
         add_action( 'admin_menu',array( $this, 'add_admin_menu' ) );
         add_action( 'admin_init', array($this,'register_settings') );
         add_action( 'wp_ajax_do_migrate', array($this,'do_migrate_callback') );
         add_action( 'wp_ajax_validate_key', array($this,'validate_key_callback') );
         add_action( 'wp_ajax_new_tenant', array($this,'new_tenant_callback') );
      }

      add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
   }

   public function register_styles() {
     wp_enqueue_style( 'propel-mograte',plugins_url( '/css/style.css', __FILE__ ) );
     wp_enqueue_script('propel-mograte', plugins_url( '/js/migrate.js' , __FILE__ ));   
   }

   function add_admin_menu() {
      add_menu_page('PROPEL Migrate','PROPEL Migrate','read',
                    'propel-migrate', array( $this, 'render_admin_menu'));   
   }

   function register_settings() {
      $servers = array("stage", "prod");
      foreach ($servers as $server) {
         register_setting(
            "migrate_settings_$server",   // group name - use in settings_fields call
            "migrate_settings_$server"    // wp_options:option_value 
         );
         add_settings_section(
            "migrate-settings-$server",     // id of the section
            null,                         // title displayed at top of section
            null,                         // sanitize
            "migrate-settings-$server"      // slug name. Match in do_settings_sections call
         );

         add_settings_field('okm_url',
            'OKM Server',
            array( $this, 'okm_url_callback' ),
            "migrate-settings-$server",
            "migrate-settings-$server" // match first param of add_settings_section
         );
         add_settings_field('tenant_key',
            'OKM Tenant Key',
            array( $this, 'tenant_key_callback' ),
            "migrate-settings-$server",
            "migrate-settings-$server" // match first param of add_settings_section
         );
         add_settings_field('lrs_user',
            'LRS API User',
            array( $this, 'lrs_user_callback' ),
            "migrate-settings-$server",
            "migrate-settings-$server"
         );
         add_settings_field('lrs_pass',
            'LRS API Password',
            array( $this, 'lrs_pass_callback' ),
            "migrate-settings-$server",
            "migrate-settings-$server"
         );
         add_settings_field('woo_gateway',
            'WooCommerce Payment Gateway',
            array( $this, 'woo_gateway_callback' ),
            "migrate-settings-$server",
            "migrate-settings-$server"
         );
         add_settings_field('cod_enabled',
            'COD (Cashless purchase)',
            array( $this, 'cod_enabled_callback' ),
            "migrate-settings-$server",
            "migrate-settings-$server"
         );
      }
   }

   function okm_url_callback() {
      $val = isset( $this->settings['okm_url'] ) ? esc_attr( $this->settings['okm_url'] ) : '';
      echo("<input style='width:300px' type='text' id='okm-url' name='migrate_settings_$this->set[okm_url]' value='$val' />");
      echo("<p style='font-size: 0.85em;'><b>Staging:</b> http://propelokm.scitent.us</p>");
      echo("<p style='font-size: 0.85em;'><b>Production:</b> https://propellms.com</p>");
   }
   function tenant_key_callback() {
      $val = isset( $this->settings['tenant_key'] ) ? esc_attr( $this->settings['tenant_key'] ) : '';
      echo("<input style='width:300px' id='tenant-key' type='text' name='migrate_settings_$this->set[tenant_key]' value='$val' />");
      echo("<span id='okm-status' class='none'></span>");
      echo("<div id='okm-controls'>");
      echo("<input type='button' style='margin-left:5px' class='button button-primary test-okm $this->set' value='Test Connection'/>");
      echo("<input type='button' style='margin-left:5px' class='button button-primary new-tenant $this->set' value='New Tenant'/>");
      echo("</div>");
      ?>
         <div class="new-tenant hidden">
            <p><b>New Tenant Setup</b></p>
            <table class="new-tenant">
               <tr>
                  <td class='label'>Tenant Name</td><td><input id="new-name" type="text"/></td>
               </tr>
               <tr>
                  <td class='label'>URL</td><td><input id="new-url" type="text"/></td>
               </tr>
               <tr>
                  <td class='label'>Mandrill API Key</td><td><input id="new-key" type="text"/></td>
               </tr>
               <tr>
                  <td class='label'>Mandrill Sub-Account</td><td><input id="new-subaccount" type="text"/></td>
               </tr>
               <tr>
                  <td class='label'>Assignment Template</td><td><input id="new-assign" type="text"/></td>
               </tr>
               <tr>
                  <td class='label'>Revoke Template</td><td><input id="new-revoke" type="text"/></td>
               </tr>
               <tr>
                  <td class='label'>Enable Auth0 SSO</td><td><input id="enable-sso" type="checkbox"/></td>
               </tr>
               <tr>
                  <td class='label'>Auth0 App Name</td><td><input id="auth0-name" type="text"/></td>
               </tr>
               <tr>
                  <td class='label'></td>
                  <td style="text-align:right">
                     <input type='button' class='cancel-tenant button button-primary' value='Cancel' />
                     <?php
                     echo("<input type='button' class='button button-primary create-tenant $this->set' value='Create'/>");
                     ?>
                  </td>
               </tr>
            </table>
         </div>
      <?php
   }
   function lrs_user_callback() {
      $val = isset( $this->settings['lrs_user'] ) ? esc_attr( $this->settings['lrs_user'] ) : '';
      echo("<input style='width:300px' type='text' name='migrate_settings_$this->set[lrs_user]' value='$val' />");
   }
   function lrs_pass_callback() {
      $val = isset( $this->settings['lrs_pass'] ) ? esc_attr( $this->settings['lrs_pass'] ) : '';
      echo("<input style='width:300px' type='text' name='migrate_settings_$this->set[lrs_pass]' value='$val' />");
   }
   function woo_gateway_callback() {
      echo('<label>');
      $checked = "";
      if ($this->settings['woo_gateway']=="stripe") $checked="checked='checked'";
      echo("<input $checked type='radio' name='migrate_settings_$this->set[woo_gateway]' value='stripe'/>Stripe");
      echo("</label><br>");
      

      echo("<label>");
      $checked = "";
      if ($this->settings['woo_gateway']=="braintree") $checked="checked='braintree'";
      echo("<input $checked type='radio' name='migrate_settings_$this->set[woo_gateway]' value='braintree'/>Braintree");
      echo("</label>");
   }
   function cod_enabled_callback() {
      echo('<label>');
      $checked = "";
      if ($this->settings['cod_enabled']=="on") $checked="checked='checked'";
      echo("<input $checked type='checkbox' name='migrate_settings_$this->set[cod_enabled]'/> COD Enabled");
      echo("</label>");
   }

   function detect_current_settings() {
      error_log("No settings found for $this->set. Detecting current setup...");

      $prod = get_option( "migrate_settings_prod", FALSE );
      $stage = get_option( "migrate_settings_stage", FALSE );
      $initial_view = false;
      if ( !$prod && !$stage ) {
         error_log("First time vistit; no settings");
         $initial_view = true;
      }

      $propel_settings = get_option('propel_settings');
      
      $settings = array();
      $settings['okm_url'] = $propel_settings['okm_server'];
      $settings['tenant_key'] = $propel_settings['okm_tenant_secret_key'];
      $settings['lrs_user'] = get_option('grassblade_tincan_user');
      $settings['lrs_pass'] = get_option('grassblade_tincan_password');

      $settings['cod_enabled'] = "off";
      $cod = get_option('woocommerce_cod_settings', FALSE);
      if ( $cod && $cod['enabled'] == 'yes' ) {
         $settings['cod_enabled'] = "on";
      }

      $settings['woo_gateway'] = "stripe";
      $bt = get_option('woocommerce_braintree_settings', FALSE);
      if ( $bt ) {
         $settings['woo_gateway'] = "braintree";
      }

      if ( $propel_settings['okm_server'] == "https://propellms.com" ) {
         $set = "prod"; 
      } else {
         $set = "stage"; 
      }

      $existing_settings = get_option( "migrate_settings_$set", FALSE );
      if (!$existing_settings ) {
         error_log("Creating $set settings");
         update_option("migrate_settings_$set", $settings);
      }
      if ( $set == $this->set || $initial_view ) {
         // first time viewing migrate page always default to showing the 
         // settings that are currently defined (ie show production tab
         // and settings if ths server is a production server)
         $this->set = $set;
         $this->settings = $settings;
      } else {
         error_log("Current setup is not $this->set, leaving settings blank");
      }
   }

   function render_admin_menu() {
      $this->set = isset( $_GET['tab'] ) ? $_GET['tab'] : 'stage';
      $this->settings = get_option( "migrate_settings_$this->set", FALSE );
      if ( !$this->settings ) {
         // no settings found, try to detect current settings
         $this->detect_current_settings();   
      }

      $current_settings = get_option("active_settings");
      $tabs = array('stage' => 'Staging', 'prod' => 'Production');

      ?>
         <div class="wrap">
            <h2>PROPEL Migration Settings</h2>
            <h2 class="nav-tab-wrapper">
               <?php 
                  foreach( $tabs as $tab => $name ) {
                     $class = ( $tab == $this->set ) ? ' nav-tab-active' : '';
                     $display_name = $name;
                     if ($tab == $current_settings ) $display_name = "$name (Current)";
                     echo "<a id='tab-$tab' class='nav-tab$class' href='?page=propel-migrate&tab=$tab'>$display_name</a>";
                  }
               ?> 
               <div style="float:right">
                  <button id="do-migration-stage" class="migrate button button-primary">Migrate to Staging</button>
                  <button id="do-migration-prod" class="migrate button button-primary">Migrate to Production</button>
               </div>
            </h2>

            <form method="post" action="options.php">
               <div class="wrap">
                  <?php 
                     settings_fields( "migrate_settings_$this->set" );
                     do_settings_sections( "migrate-settings-$this->set" );
                     submit_button(); 
                  ?>
                  <p style="display:none" id="migrate-msg"></p>
               </div>
            </form>
         </div>   
         <div id="working"><div id="work-spinner-box">Working...</div></div>
      <?php
   }

   /**
    * Handle the AJAX request to do the migration
    */
   function do_migrate_callback() {
      $this->migrate( $_POST['target'] );
      wp_die();
   }

   function new_tenant_callback() {
      $a0_settings = get_option( "wp_auth0_settings" );
      $sso = $_POST['sso'];
      error_log(json_encode($a0_settings) );
      
      $user = wp_get_current_user();
      $auth = base64_encode("$user->ID:$user->user_email");
      $params = array( "token"=>$auth);
      $params = array_merge($params, array( "sso"=>$sso) );
      if ($sso == 'true' ) {
         $params = array_merge($params, array( "a0Name"=>$_POST['a0name']) );
         $params = array_merge($params, array( "a0Client"=>$a0_settings['client_id']) );
         $params = array_merge($params, array( "a0Secret"=>$a0_settings['client_secret']) );
         $params = array_merge($params, array( "a0Domain"=>$a0_settings['domain']) );
      }
      $params = array_merge($params, $_POST);
      $settings = get_option( "migrate_settings_". $params['target'] );
      $url = $settings['okm_url'];
      
      
      $out = $this->curl_okm( $url, $params, 'create_tenant' );
      error_log("RESPONSE ".json_encode($out));
      if ( $out['http_status'] == 200 ) {
         wp_send_json_success($out);
      }
      else{
         wp_send_json_error($out);
      }
      wp_die();
   }

   function validate_key_callback() {
      $out = $this->curl_okm( $_POST['url'], "?tenant_secret_key=".$_POST['key'], 'validate_tenant', 'GET' );
      if ( $out['http_status'] == 200 ) {
         error_log("SUCCESS");
         wp_send_json_success();
      }
      else{
         wp_send_json_error();
      }
      wp_die();
   }

   /**
    * Do the actual work of the migration. Called from either API or Admin page
    */
   function migrate($target) {
      error_log("MIGRATE to $target");
      $settings = get_option( "migrate_settings_$target" );
      if ( !$settings ) {
         error_log("$target SETTINGS NOT FOUND!");
         wp_send_json_error("$target SETTINGS NOT FOUND!");
         return false;
      }
     
      if ( $target != "prod" && $target != "stage") {
         error_log("Invalid migration target");
         wp_send_json_error("$target is not a valid settings target!");
         return false;
      }

      // flag the settings that are now in use
      update_option("active_settings", $target);

      error_log("Clear wordpress-https settings...");
      $home = get_option("home");
      $siteurl = get_option("siteurl");
      if ( $target == "stage") {
         update_option('wordpress-https_ssl_admin',"0");
         $home = str_replace("https:", "http:", $home);
         $siteurl = str_replace("https:", "http:", $siteurl);
      } else {
         update_option('wordpress-https_ssl_admin',"1");
         $home = str_replace("http:", "https:", $home);
         $siteurl = str_replace("http:", "https:", $siteurl);
      }
      update_option('home',$home);
      update_option('siteurl',$siteurl);
 
      $propel_settings = get_option('propel_settings');

      error_log("Update OKM settings...");
      // NOTE: the call to update_options below triggers the
      // sanitize function in propel-settings.php. It ignores that
      // data coming in from the settings array for the okm_key and
      // instead expects it to be in the $_POST data, named 'key'
      $propel_settings['okm_server'] = $settings['okm_url']; 
      $_POST['key']  = $settings['tenant_key'];
      update_option('propel_settings', $propel_settings);

      error_log("Update Grassblade settings...");
      update_option('grassblade_tincan_user', $settings['lrs_user']);
      update_option('grassblade_tincan_password', $settings['lrs_pass']);

      // COD
      $cod = get_option('woocommerce_cod_settings');
      if ($settings['cod_enabled']=="on"){
         $cod['enabled'] = 'yes';
      }
      else{
         $cod['enabled'] = 'no';
      }
      update_option('woocommerce_cod_settings', $cod);

      // Payment getway settings     
      if ( $settings['woo_gateway'] == 'stripe') {
         error_log('Update WooCommerce Stripe gateway');
         $stripe = get_option('woocommerce_stripe_settings');
         if ( $target == "stage") {
            $stripe['testmode'] = 'yes';
            update_option('woocommerce_force_ssl_checkout', 'no');
            update_option('woocommerce_unforce_ssl_checkout', 'yes');
         } else {
            $stripe['testmode'] = 'no';
            update_option('woocommerce_force_ssl_checkout', 'yes');
            update_option('woocommerce_unforce_ssl_checkout', 'no');  
         }
         update_option('woocommerce_stripe_settings', $stripe);
      } else if ( $settings['woo_gateway'] == 'braintree' ) {
         error_log('Update WooCommerce Braintree gateway');
         $bt = get_option('woocommerce_braintree_settings');
         
         if ( $target == "stage") {
            $bt['environment'] = 'development';
         } else {
            $bt['environment'] = 'production';
         }
         update_option('woocommerce_braintree_settings', $bt);
      } else {
         error_log('Unknown WooCommerce gateway '.$settings['woo_gateway']);
      }

      wp_send_json_success();
      return true;
   }

   function curl_okm( $url, $request, $method, $action = 'POST' ) {
      // If is it is a string (not array), add it to the end of the url
      // For example, ?tenant_secret_key=xxxxxxxxxxx
      $url = $url . "/api/$method";
      if ( ! is_array( $request ) ) {
         $url .= $request;
      }

      $ch = curl_init( $url );
      error_log("CURL $url");

      if ( $action == 'POST' ){
         curl_setopt( $ch, CURLOPT_POST, TRUE );
      }

      if ( is_array( $request ) ){
         curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $request ) );
      }

      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
      curl_setopt( $ch, CURLINFO_HEADER_OUT, TRUE);
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0'); 

      curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
              'Accept: application/json',
              'Content-Type: application/json',
              'Authorization: Bearer '.$_COOKIE["authToken"]
      ));

      // Send the request
      $response = curl_exec( $ch );

      // Check for errors
      if ( $response === FALSE ) {
         die( curl_error( $ch ) );
      }

      // Decode the response
      $responseData = json_decode( $response, TRUE );
      $responseData['http_status'] = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
      curl_close( $ch );

      return $responseData;
   }
}

$pm = new Propel_Migrate();

//
// Disabled the WP_API call to do a migrate for now. Needs to be authenticated
//
// function do_migration($data) {
//    global $pm;
//    $out = $pm->migrate( $data->get_param('target') );
//    return new WP_REST_Response( $out, 200 );
// }

// // curl -H "Content-Type: application/json" -X POST -d '{"target":"stage|prod"}' http://test.dev/wp-json/scitent/v1/migrate
// add_action( 'rest_api_init', function () {
//     register_rest_route( 'scitent/v1', '/migrate', array(
//         'methods' => 'POST',
//         'callback' => 'do_migration',
//     ) );
// } );
