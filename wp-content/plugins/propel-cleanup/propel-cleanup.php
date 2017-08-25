<?php
/** 
 * Plugin Name: PROPEL Cleanup
 * Version: 1.0
 * Author:  Lou Foster
 * Author URI: http://scitent.com
 * Description: Helper plugin to restore Propel Enterprise Demo site to a clean state
 */
class Propel_Cleanup {
   private $settings;
   private $set;

   function __construct() {
      if ( is_admin() ){
         add_action( 'admin_menu',array( $this, 'add_admin_menu' ) );
         add_action( 'wp_ajax_do_reset', array($this,'do_reset_callback') );
      }

      add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
   }

   public function register_scripts() {
      wp_enqueue_style( 'propel-cleanup', plugins_url( '/css/style.css', __FILE__ ) );
      wp_enqueue_script('propel-cleanup', plugins_url( '/js/cleanup.js' , __FILE__ ));   
   }

   function add_admin_menu() {
      add_menu_page('PROPEL Cleanup','PROPEL Cleanup','delete_pages',
                    'propel-cleanup', array( $this, 'render_admin_menu'), 'dashicons-trash');   
   }

   function render_admin_menu() {
      ?>
      <style>
         .danger {
            color: #900;
            font-weight: bold;
         }
      </style>
      <div class="wrap">
         <h2>PROPEL Cleanup</h2>
         <div class="wrap">
            <p>
               Set the cleanup options below and click the cleanup button to remove
               unwated data from the PROPEL database. By default, all logs, auto-drafts
               and items in the trash will be deleted. 
            </p>
            <p>
               This process may take several minutes, be patient!
            </p>
            <p class="danger">
               This cannot be reversed, so be sure to backup the WP Database first!
            </p>
            <hr/>
            <div style="margin: 0 0 20px 20px">
               <label><input class="clean-opt" type="checkbox" value="orders"/>Delete orders/enrollments/refunds</label><br/>
               <label><input class="clean-opt" type="checkbox" value="coupons"/>Delete coupons</label><br/>
               <label><input class="clean-opt" type="checkbox" value="comments" checked/>Delete comments</label><br/>
               <label><input class="clean-opt" type="checkbox" value="posts" checked/>Delete posts</label><br/>
               <label><input class="clean-opt" type="checkbox" value="users" checked/>Delete non-scitent/non-dispostable users</label><br/>
               <label><input class="clean-opt" type="checkbox" value="subscribers"/>Delete non-admin users (including scitent and dispostable)</label><br/>
               <hr/>
               <label class="danger"><input class="clean-opt" type="checkbox" value="pages"/>Delete pages</label><br/>
               <label class="danger"><input class="clean-opt" type="checkbox" value="courses"/>Delete courses (including lessons, topics, quizzes and certificates)</label><br/>
               <label class="danger"><input class="clean-opt" type="checkbox" value="products"/>Delete products</label><br/>
            </div>
            <button id="do-reset" class="reset button button-primary">Cleanup Database</button>
         </div>
      <div id="working"><div id="work-spinner-box">Working...</div></div>
      <?php
   }

   function do_reset_callback() {
      $wp = "/usr/local/bin/wp";
      global $wpdb;

      error_log("Starting PROPEL Cleanup...");
      $audit = $wpdb->prefix . "audit_trail";
      $wpdb->query("truncate table $audit");
      $login_log = $wpdb->prefix . "user_login_log";
      $wpdb->query("truncate table $login_log");
      
      error_log("Clearing trash and autodraft...");
      shell_exec("$wp post delete $($wp post list --post_type=post --post_status=auto-draft,trash --format=ids) --force");
      shell_exec("$wp post delete $( $wp post list --format=ids --post_type=page,product,sfwd-quiz,sfwd-courses,sfwd-lessons,sfwd-topic --post_status=trash) --force");   
      shell_exec("$wp post delete $($wp post list --post_status=trash --post_type=shop_order,shop_order_refund,shop_coupon --format=ids) --force");
      
      $opts_str = $_POST['opts'];
      $opts = array();
      if ($opts_str ) {
         error_log("OPTS $opts_str");
         $opts = explode(",", $opts_str);
      }
      
      if ( in_array("orders", $opts) ) {
         error_log("Clear Orders...");
         shell_exec("$wp post delete $($wp post list --post_type=shop_order,shop_order_refund --format=ids) --force");
         $woo = $wpdb->prefix . "woocommerce_order_items";
         $wpdb->query("truncate table $woo");
         $woo = $wpdb->prefix . "woocommerce_order_itemmeta";
         $wpdb->query("truncate table $woo");
         $enroll = $wpdb->prefix . "propel_enrollments";
         $wpdb->query("truncate table $enroll");
      }
      
      if (in_array("coupons", $opts)) {
         error_log("Clear Coupons...");
         shell_exec("$wp post delete $($wp post list --post_type=shop_coupon --format=ids) --force");
      }
      
      if (in_array("comments", $opts)) {
         error_log("Clear Comments...");
         shell_exec("$wp comment delete $($wp comment list --format=ids) --force");
      }
      
      if (in_array("posts", $opts)) {
         error_log("Clear posts...");
         shell_exec("$wp post delete $($wp post list --post_type=post --format=ids) --force");
      }
      
      if (in_array("pages", $opts)) {
         error_log("Clear pages...");
         shell_exec("$wp post delete $($wp post list --post_type=page --format=ids) --force");
      }
      
      if (in_array("products", $opts)) {
         error_log("Clear products...");
         shell_exec("$wp post delete $($wp post list --post_type=product --format=ids) --force");
      }
      
      if (in_array("courses", $opts)) {
         error_log("Clear courses...");
         shell_exec("$wp post delete $($wp post list --post_type=sfwd-certificates --format=ids) --force");
         shell_exec("$wp post delete $($wp post list --post_type=sfwd-courses --format=ids) --force");
         shell_exec("$wp post delete $($wp post list --post_type=sfwd-lessons --format=ids) --force");
         shell_exec("$wp post delete $($wp post list --post_type=sfwd-topic --format=ids) --force");
         shell_exec("$wp post delete $($wp post list --post_type=sfwd-quiz --format=ids) --force");
      }
      
      if (in_array("users", $opts)) {
         error_log("Clear non-scitent/non-dispostable users...");
         $ids = "";
         $roles = ["administrator","org_admin","tenant_admin","subscriber","customer"];
         foreach ($roles as $role) {
            $out = shell_exec("$wp user list --role=$role --fields=ID,user_email --format=json");
            $json = json_decode($out, true);
            foreach ($json as $u) {
               if ( !strpos($u['user_email'], "scitent.com") && 
                    !strpos($u['user_email'], "dispostable.com") && 
                    $u['user_email'] != "jhnsntmthy@me.com" && 
                    !strpos($u['user_email'], "wpengine.com")) {
                  $uid = $u['ID'];
                  $ids .= " $uid";
               } 
            }
         }
         shell_exec("$wp user delete $ids --yes");
      }
      
      if (in_array("subscribers", $opts)) {
         error_log("Clear Subscribers...");
         shell_exec("$wp user delete $($wp user list --role=customer --format=ids) --yes");
         shell_exec("$wp user delete $($wp user list --role=subscriber --format=ids) --yes");
         shell_exec("$wp user delete $($wp user list --role=org_admin --format=ids) --yes");
      }

      wp_send_json_success();
      wp_die();
   }
}

new Propel_Cleanup();
