<?php
$plugin_dir = plugin_dir_path( __FILE__ );
$include_path = get_include_path() . PATH_SEPARATOR . $plugin_dir . "vendor/google-api-php-client/src";
set_include_path( $include_path);
require_once $plugin_dir . 'vendor/google-api-php-client/src/Google/autoload.php';

class Propel_Reports {

   private $settings;

   function __construct() {

      if ( is_admin() ) {
         add_action( 'admin_menu',array( $this, 'add_reports_menu' ) );
         add_action( 'admin_init',array( $this, 'register_settings' ) );
         add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
      }
   }
   
   function register_scripts_and_styles() {
      wp_enqueue_style( 'propel-reports',plugins_url( '/css/admin.css', __FILE__ ) );
      wp_enqueue_script( 'propel-reports',plugins_url( '/js/reports.js', __FILE__ ) );
      wp_enqueue_script('google-jsapi', 
         "https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1','packages':['corechart','orgchart'],'language':'en'}]}");   
   }
   
   function get_access_token() {
      global $plugin_dir;
      /**
       * Get this key from the google developer console.
       * Create a new application and enable the analytics application
       * Create new credentials for a service account using json
       * renamame the downloaded key to match the name/location below
       * Ensure that it is readable by all
       */
      $key_file = $plugin_dir."key/ga-key.json";
      $str = file_get_contents($key_file);
      $key = json_decode($str);
      $scopes = array('https://www.googleapis.com/auth/analytics.readonly');
      $credentials = new Google_Auth_AssertionCredentials(
         $key->client_email,
         $scopes,
         $key->private_key
      );
      
      $client = new Google_Client();
      $client->setAssertionCredentials($credentials);
      if ($client->getAuth()->isAccessTokenExpired()) {
         $client->getAuth()->refreshTokenWithAssertion();
      }
      $token_str = $client->getAccessToken();  
      $token_obj = json_decode($token_str);
      return $token_obj->access_token;
   }

   function add_reports_menu() {
      add_menu_page('Reports', 'Reports', 'manage_options', 'propel-reports-settings',null,'dashicons-analytics');
      add_submenu_page( 'propel-reports-settings', 'Users', 'Users', 'manage_options', 'propel-report-users',
         array($this,'render_user_reports') );
      add_submenu_page( 'propel-reports-settings', 'Orders', 'Orders', 'manage_options', 'propel-report-orders',
         array($this,'render_order_reports') );
      add_submenu_page( 'propel-reports-settings', 'Enrollments', 'Enrollments', 'manage_options', 'propel-report-enrolls',
         array($this,'render_enroll_reports') );
      add_submenu_page( 'propel-reports-settings', 'Google Analytics', 'Google Analytics', 'manage_options', 'propel-report-ga',
         array($this,'render_ga') );

      // NOTE: make the 5th param match the 1st to removes teh 'Reports' submenu directly under Reports menu
      add_submenu_page( 'propel-reports-settings', 'Settings', 'Settings', 'manage_options', 'propel-reports-settings',
         array($this,'render_settings') );
   }

   function register_settings() {

      register_setting('propel_reports', 'propel_reports' );
      add_settings_section('propel-reports',null,null,'propel-reports');

      add_settings_field(
         'google_analytics_id',
         'Google Analytics View ID',
         array( $this, 'google_analytics_id_callback' ),
         'propel-reports',
         'propel-reports'
      );
      
      add_settings_field(
         'user_chart_ids',
         'Users Charts',
         array( $this, 'user_chart_ids_callback' ),
         'propel-reports',
         'propel-reports'
      );
      add_settings_field(
         'user_report_ids',
         'Users Report',
         array( $this, 'user_report_ids_callback' ),
         'propel-reports',
         'propel-reports'
      );
      
      add_settings_field(
         'order_chart_ids',
         'Orders Charts',
         array( $this, 'order_chart_ids_callback' ),
         'propel-reports',
         'propel-reports'
      );
      add_settings_field(
         'order_report_ids',
         'Orders Report',
         array( $this, 'order_report_ids_callback' ),
         'propel-reports',
         'propel-reports'
      );
      
      add_settings_field(
         'enroll_chart_ids',
         'Enrollments Charts',
         array( $this, 'enroll_chart_ids_callback' ),
         'propel-reports',
         'propel-reports'
      );
      add_settings_field(
         'enroll_report_ids',
         'Enrollments Report',
         array( $this, 'enroll_report_ids_callback' ),
         'propel-reports',
         'propel-reports'
      );
   }
   
   function google_analytics_id_callback() {
      $val = isset( $this->settings['google_analytics_id'] ) ? esc_attr( $this->settings['google_analytics_id'] ) : '';
      echo("<input style='width:450px' type='text' name='propel_reports[google_analytics_id]' value='$val' />");
      echo("<p style='font-size: 0.8em;'>Find on the View Settings page of Google Analytics page.</p>");
   }
   
   function user_chart_ids_callback() {
      $val = isset( $this->settings['user_chart_ids'] ) ? esc_attr( $this->settings['user_chart_ids'] ) : '';
      echo("<input style='width:450px' type='text' name='propel_reports[user_chart_ids]' value='$val' />");
      echo("<p style='font-size: 0.8em;'>A comma separared list of wpDataTables User Chart IDs</p>");
   }
   function user_report_ids_callback() {
      $val = isset( $this->settings['user_report_ids'] ) ? esc_attr( $this->settings['user_report_ids'] ) : '';
      echo("<input style='width:450px' type='text' name='propel_reports[user_report_ids]' value='$val' />");
      echo("<p style='font-size: 0.8em;'>A comma separared list of wpDataTables User Report IDs</p>");
   }
   
   function order_chart_ids_callback() {
      $val = isset( $this->settings['order_chart_ids'] ) ? esc_attr( $this->settings['order_chart_ids'] ) : '';
      echo("<input style='width:450px' type='text' name='propel_reports[order_chart_ids]' value='$val' />");
      echo("<p style='font-size: 0.8em;'>A comma separared list of wpDataTables Orders Chart IDs</p>");
   }
   function order_report_ids_callback() {
      $val = isset( $this->settings['order_report_ids'] ) ? esc_attr( $this->settings['order_report_ids'] ) : '';
      echo("<input style='width:450px' type='text' name='propel_reports[order_report_ids]' value='$val' />");
      echo("<p style='font-size: 0.8em;'>A comma separared list of wpDataTables Order Report IDs</p>");   
   }
   
   function enroll_chart_ids_callback() {
      $val = isset( $this->settings['enroll_chart_ids'] ) ? esc_attr( $this->settings['enroll_chart_ids'] ) : '';
      echo("<input style='width:450px' type='text' name='propel_reports[enroll_chart_ids]' value='$val' />");
      echo("<p style='font-size: 0.8em;'>A comma separared list of wpDataTables Enrollment Chart IDs</p>");
   }
   function enroll_report_ids_callback() {
      $val = isset( $this->settings['enroll_report_ids'] ) ? esc_attr( $this->settings['enroll_report_ids'] ) : '';
      echo("<input style='width:450px' type='text' name='propel_reports[enroll_report_ids]' value='$val' />");
      echo("<p style='font-size: 0.8em;'>A comma separared list of wpDataTables Enrollment Report IDs</p>");   
   }

   function render_settings() {
      $this->settings = get_option( "propel_reports" );
      ?>
      <div class="wrap">
            <h2>PROPEL Reports: Settings</h2>
            <form method="post" action="options.php">
               <div class="wrap">
                  <?php 
                     settings_fields( "propel_reports" );
                     do_settings_sections( "propel-reports" );
                     submit_button(); 
                  ?>
               </div>
            </form>
        </div>
        <?php
   }

   function render_user_reports() {      
      $settings = get_option( "propel_reports" );
      $current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'charts';
      $tabs = array(
         'charts'    => 'Overview',
         'tables'    => 'Details'
      );
      echo '<h2>PROPEL Reports: Users</h2>';
      echo '<h2 class="nav-tab-wrapper">';
      foreach( $tabs as $tab => $name ) {
         $class = ( $tab == $current ) ? ' nav-tab-active' : '';
         echo "<a class='nav-tab$class' href='?page=propel-report-users&tab=$tab'>$name</a>";
      }
      echo '</h2>';
      
      ?>
      <style>div.chart-report-wrap { border: 1px solid #ccc; margin: 5px;}</style>
      <div class="tab-content">
         <?php
         if ( $current == "charts") {
            if ( isset($settings['user_chart_ids'])) {
               $id_str = $settings['user_chart_ids'];
               $ids = explode(",", $id_str);
               foreach ($ids as $id) {
                  echo "<div class='chart-report-wrap'>";
                  echo do_shortcode( "[wpdatachart id=$id]" );
                  echo "</div>";
               }
            } 
         }
         if ( $current == "tables") {
            if ( isset($settings['user_report_ids'])) {
               $id_str = $settings['user_report_ids'];
               $ids = explode(",", $id_str);
               foreach ($ids as $id) {
                  echo do_shortcode( "[wpdatatable id=$id]" );
               }
            } 
         }
         ?>
      </div>
      <?php
   }
   
   function render_order_reports() {      
      $settings = get_option( "propel_reports" );
      $current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'charts';
      $tabs = array(
         'charts'    => 'Overview',
         'tables'    => 'Details'
      );
      echo '<h2>PROPEL Reports: Orders</h2>';
      echo '<h2 class="nav-tab-wrapper">';
      foreach( $tabs as $tab => $name ) {
         $class = ( $tab == $current ) ? ' nav-tab-active' : '';
         echo "<a class='nav-tab$class' href='?page=propel-report-orders&tab=$tab'>$name</a>";
      }
      echo '</h2>';
      
      ?>
      <style>div.chart-report-wrap { border: 1px solid #ccc; margin: 5px;}</style>
      <div class="tab-content">
         <?php
         if ( $current == "charts") {
            if ( isset($settings['order_chart_ids'])) {
               $id_str = $settings['order_chart_ids'];
               $ids = explode(",", $id_str);
               foreach ($ids as $id) {
                  echo "<div class='chart-report-wrap'>";
                  echo do_shortcode( "[wpdatachart id=$id]" );
                  echo "</div>";
               }
            } 
         }
         if ( $current == "tables") {
            if ( isset($settings['order_report_ids'])) {
               $id_str = $settings['order_report_ids'];
               $ids = explode(",", $id_str);
               foreach ($ids as $id) {
                  echo do_shortcode( "[wpdatatable id=$id]" );
               }
            } 
         }
         ?>
      </div>
      <?php
   }
   
   function render_enroll_reports() {      
      $settings = get_option( "propel_reports" );
      $current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'charts';
      $tabs = array(
         'charts'    => 'Overview',
         'tables'    => 'Details'
      );
      echo '<h2>PROPEL Reports: Enrollments</h2>';
      echo '<h2 class="nav-tab-wrapper">';
      foreach( $tabs as $tab => $name ) {
         $class = ( $tab == $current ) ? ' nav-tab-active' : '';
         echo "<a class='nav-tab$class' href='?page=propel-report-enrolls&tab=$tab'>$name</a>";
      }
      echo '</h2>';
      
      ?>
      <style>div.chart-report-wrap { border: 1px solid #ccc; margin: 5px;}</style>
      <div class="tab-content">
         <?php
         if ( $current == "charts") {
            if ( isset($settings['enroll_chart_ids'])) {
               $id_str = $settings['enroll_chart_ids'];
               $ids = explode(",", $id_str);
               foreach ($ids as $id) {
                  echo "<div class='chart-report-wrap'>";
                  echo do_shortcode( "[wpdatachart id=$id]" );
                  echo "</div>";
               }
            } 
         }
         if ( $current == "tables") {
            if ( isset($settings['enroll_report_ids'])) {
               $id_str = $settings['enroll_report_ids'];
               $ids = explode(",", $id_str);
               foreach ($ids as $id) {
                  echo do_shortcode( "[wpdatatable id=$id]" );
               }
            } 
         }
         ?>
      </div>
      <?php
   }
   
   function render_ga() {
      $settings = get_option( "propel_reports" );
      if ( !isset($settings['google_analytics_id']) || strlen($settings['google_analytics_id']) == 0) {
         ?>
            <h2>PROPEL Reports: Google Analytics</h2>
            <div class='main-report'>
               <div id='no-cfg'>Configure the Google Analytics View ID on the settings page to see this report.</div>
            </div>
         <?php
         return;
      }
      $view_id  = "ga:".$settings['google_analytics_id'];
      
      $token = $this->get_access_token();
      ?>
      <script>
         (function(w,d,s,g,js,fs){
           g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
           js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
           js.src='https://apis.google.com/js/platform.js';
           fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
         }(window,document,'script'));
      </script>
      <script src="https://ga-dev-tools.appspot.com/public/javascript/embed-api/components/date-range-selector.js"></script>
      <script src="https://ga-dev-tools.appspot.com/public/javascript/embed-api/components/active-users.js"></script>

      <h2>PROPEL Reports: Google Analytics</h2>
      <div class='main-report'>
         <div class='chart-header'>
            <p class='chart-title'>Site Traffic</p>
         </div>
         <div id="loading">Loading...</div>
         
         <div id="active-users"></div>
         <div id="chart-container"></div>
         <div id="date-range-selector-container"></div>
         <div class="separator"></div>
         <div class='chart-box'>
            <div class='chart-header'>
               <p class='chart-title'>Sessions by Country</p>
            </div>
            <div id="map-container"></div>
         </div>
         <div class='chart-box left'>
            <div class='chart-header'>
               <p class='chart-title'>Browser Usage for Last 30 Days</p>
            </div>
            <div id="chart_div"></div>
         </div>
         <div class="separator"></div>
         <div id="traffic-container">
            <div class='chart-header'>
               <p class='chart-title'>Traffic Details for Last 30 Days</p>
            </div>
            <div id="traffic-org"></div>
            <div class="traffic-charts">
               <div class="traffic-pie" id="visitor-type"></div>
               <div class="traffic-pie" id="mediums"></div>
               <div class="traffic-pie" id="searches"></div>
               <div class="traffic-pie" id="social"></div>
            </div>
         </div>
      </div>
         
      <script>
         jQuery(".chart-header").css("visibility", "hidden");
         jQuery(".chart-box").css("visibility", "hidden");
         jQuery(".separator").css("visibility", "hidden");
         var gaViewId = "<?php echo $view_id; ?>";
         gapi.analytics.ready(function() {
            gapi.analytics.auth.authorize({
               'serverAuth': {
                  'access_token': '<?php echo $token; ?>'
               }
            });
            
            var activeUsers = new gapi.analytics.ext.ActiveUsers({
               container: 'active-users',
               pollingInterval: 5
            });
            var data = {ids: gaViewId};
            activeUsers.set(data).execute();
            
            var dateRange = {
               'start-date': '30daysAgo',
               'end-date': 'yesterday'
            };
            var dateRangeSelector = new gapi.analytics.ext.DateRangeSelector({
               container: 'date-range-selector-container'
            })
            .set(dateRange)
            .execute();
         
            var dataChart = new gapi.analytics.googleCharts.DataChart({
               query: {
                  ids: gaViewId,
                  metrics: 'ga:sessions,ga:users,ga:bounces',
                  dimensions: 'ga:date'
               },
               chart: {
                  container: 'chart-container',
                  type: 'LINE',
                  options: {
                     width: '100%'
                  }
               }
            }).set({query: dateRange});
            dataChart.execute();
            
            var dataMap = new gapi.analytics.googleCharts.DataChart({
               query: {
                  ids: gaViewId,
                  metrics: 'ga:sessions',
                  dimensions: 'ga:country',
               },
               chart: {
                  container: 'map-container',
                  type: 'GEO'
               }
            }).set({query: dateRange});
            dataMap.execute();
            drawBrowserUsage( gaViewId,'chart_div');
            drawTrafficDetail( gaViewId, 'traffic-org','mediums','visitor-type','searches','social');
            
            jQuery("#loading").hide();
            jQuery(".chart-header").css("visibility", "visible");
            jQuery(".chart-box").css("visibility", "visible");
            jQuery(".separator").css("visibility", "visible");
            
            dateRangeSelector.on('change', function(data) {
               dataChart.set({query: data}).execute();
               dataMap.set({query: data}).execute();
            });
         });
      </script>
      
      <?php
   }
}
new Propel_Reports();
