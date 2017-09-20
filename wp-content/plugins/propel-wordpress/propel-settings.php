<?php

class Propel_Settings {

	private $settings;

	function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_menu',
				array( $this, 'add_settings_menu' ) );

			add_action( 'admin_init',
				array( $this, 'register_settings' ) );

			add_action( 'admin_notices',
				array( $this, 'okm_tenant_notice' ) );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );

		add_action( 'wp_ajax_save_okm_tenant_secret_key',
			array( $this, 'save_okm_tenant_secret_key' ) );

		add_action( 'wp_ajax_check_okm_tenant_secret_key',
			array( $this, 'check_okm_tenant_secret_key' ) );

		add_action( 'admin_bar_menu',
			array( $this, 'add_okm_page_link' ), 999 );

		// TODO: Should be in admin?
		if ( isset( $_GET['export_quiz_csv'] ) ) {
			add_action( 'admin_init', array( $this, 'export_quiz_csv' ), 1);
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'register_olark_blocker' ) );
   		add_action( 'wp_enqueue_scripts', array( $this, 'register_scitent_namespace' ) );
   		add_action( 'admin_enqueue_scripts', array( $this, 'admin_regenque_okg_js' ) );
   		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_okg_js' ) ); // register, but don't actually enqueue
   		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_parsley_js' ) ); // reg w/o enqueue
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_wpum_style' ), 20 ); // late
   }

   public function dequeue_wpum_style() {
		wp_dequeue_style( 'wpum-frontend-css' );
   }

   public function register_olark_blocker() {
   	$propel_settings = get_option( 'propel_settings' );
   	if ( array_key_exists('olark_blocker_enabled', $propel_settings) && 
   		 $propel_settings['olark_blocker_enabled'] === 'on') {
	      wp_register_script( 'propel-olark', plugins_url( '/js/olark_blocker.js' , __FILE__ ) );
	      $vars = array('blacklist'=>$propel_settings['olark_blacklist']);
	      wp_localize_script( 'propel-olark', 'propelOlark', $vars );
	      wp_enqueue_script('propel-olark');
	   }
   }

   public function register_scitent_namespace() {
   	wp_register_script( 'scitent-js', plugins_url( '/js/scitent.js', __FILE__ ) );
   	wp_enqueue_script('scitent-js');
   }

   public function admin_regenque_okg_js() {
   	wp_register_script( 'propel_okg_js', plugins_url( '/js/okg.js', __FILE__ ) );
	wp_localize_script( 'propel_okg_js', 'scitent_backend', array() );
   	wp_enqueue_script( 'propel_okg_js' );
   }

   public function frontend_register_okg_js() {
   	wp_register_script( 'propel_okg_js', 
   						plugins_url( '/js/okg.js', __FILE__ ),
   						array('jquery','parsley_js') );   // may be enqueued later by shortcodes	
   }

   public function frontend_register_parsley_js() {
   	wp_register_script( 'parsley_js', 
   						plugins_url( '/vendor/parsley/parsley.min.js', __FILE__ ),
   						array('jquery') ); // enqueued later
   }

	/**
	 * Adds the PROPEL Settings menu to the 'Settings' admin
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-01-07 14:47:00
	 *
	 * @action admin_menu
	 */
	function add_settings_menu() {
		add_options_page(
			'PROPEL Settings',
			'PROPEL Settings',
			'manage_options',
			'propel-settings',
			array( $this, 'render_settings_page' )
		);
	}


	/**
	 * Registers the appropriate PROPEL Settings
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-01-07 20:01:28
	 * @edited  2015-02-05 16:29:26 - Adds okm_server field
	 *
	 * @action admin_init
	 */
	function register_settings() {

		register_setting(
			'propel_settings_general',
			'propel_settings',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'propel-settings-general',
			'General Settings',
			null,
			'propel-settings'
		);

		add_settings_field(
			'okm_page_id',
			'OKM Page',
			array( $this, 'okm_page_id_callback' ),
			'propel-settings',
			'propel-settings-general'
		);

		add_settings_field(
			'order_on_hold_page_id',
			'Order On-Hold Page',
			array( $this, 'order_on_hold_page_id_callback' ),
			'propel-settings',
			'propel-settings-general'
		);

		add_settings_field(
			'okm_server_stag',
			'OKM Server - Staging',
			array( $this, 'okm_server_stag_callback' ),
			'propel-settings',
			'propel-settings-general'
		);
      
		add_settings_field(
			'okm_server_prod',
			'OKM Server - Production',
			array( $this, 'okm_server_prod_callback' ),
			'propel-settings',
			'propel-settings-general'
		);
      
		// add_settings_field(
		// 	'okm_server',
		// 	'OKM Server - (Fallback)',
		// 	array( $this, 'okm_server_callback' ),
		// 	'propel-settings',
		// 	'propel-settings-general'
		// );
      
		add_settings_field(
          'store_url',
          'Store URL',
          array( $this, 'store_url_callback' ),
          'propel-settings',
          'propel-settings-general'
      );

      add_settings_field(
			'olark_blocker_enabled',
			'oLark Blocker',
			array( $this, 'olark_blocker_enabled_callback' ),
			'propel-settings',
			'propel-settings-general'
		);

		add_settings_field(
			'olark_blacklist',
			'oLark Blocker',
			array( $this, 'olark_blacklist_callback' ),
			'propel-settings',
			'propel-settings-general'
		);
      
      add_settings_field(
         'okm_sso_enabled',
         'SSO Enabled',
         array( $this, 'okm_sso_enabled_callback' ),
         'propel-settings',
         'propel-settings-general'
      );

		add_settings_field(
			'auth0_client_id',
			'SSO: Auth0 Client ID',
			array( $this, 'auth0_client_id_callback' ), 
			'propel-settings',
			'propel-settings-general'
		);

		add_settings_field(
			'auth0_database',
			'SSO: Auth0 Database',
			array( $this, 'auth0_database_callback' ), 
			'propel-settings',
			'propel-settings-general'
		);

		add_settings_field(
			'auth0_token',
			'SSO: Auth0 API Token',
			array( $this, 'auth0_token_callback' ),
			'propel-settings',
			'propel-settings-general'
		);
	}


	/**
	 * Renders the PROPEL Settings page
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-01-07 19:54:19
	 *
	 * @action add_settings_menu
	 */
	function render_settings_page() {
		$this->settings = get_option( 'propel_settings' );

		$current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

		$tabs = array(
							'general'    => 'General',
							'export'     => 'Export',
							'tenant_key' => 'Tenant Key'
						);

		echo '<div class="wrap">';

		echo '<h2>PROPEL Settings</h2>';

		echo '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ) {
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?page=propel-settings&tab=$tab'>$name</a>";

		}
		echo '</h2>';
		?>

				<form method="post" action="options.php">
					<div class="wrap">
					<?php

							switch ( $current ) {

								case 'general':
									settings_fields( 'propel_settings_general' );
									do_settings_sections( 'propel-settings' );
									submit_button();
									break;
								case 'export':
									$this->render_export_form();
									break;
								case 'tenant_key':
									$this->okm_tenant_key_input();
									break;

							}

					?>
					</div>
				</form>
		</div>
		<?php

	}

	/**
	 * Sanitizes both the update_option and propel-settings forms
	 *
	 * @author  caseypatrickriscoll
	 *
	 * @created 2015-01-07 21:40:05
	 * @edited  2015-02-05 16:27:27 - Adds okm_server
	 * @edited  2015-03-09 11:04:39 - Adds order_on_hold_page_id
	 *
	 * @param   $input   Array   The propel_settings form input
	 *
	 * @return  $propel_settings  Array  The rewritten propel_settings array
	 */
	function sanitize( $input ) {
		$propel_settings = get_option( 'propel_settings' );

		if ( isset( $_POST['key'] ) )
			$propel_settings['okm_tenant_secret_key'] = $_POST['key'];

		if ( isset( $input['okm_page_id'] ) )
			$propel_settings['okm_page_id'] = $input['okm_page_id'];

		if ( isset( $input['order_on_hold_page_id'] ) )
			$propel_settings['order_on_hold_page_id'] = $input['order_on_hold_page_id'];

		if ( isset( $input['okm_server_stag'] ) )
			$propel_settings['okm_server_stag'] = $input['okm_server_stag'];

		if ( isset( $input['okm_server_prod'] ) )
			$propel_settings['okm_server_prod'] = $input['okm_server_prod'];

		// if ( isset( $input['okm_server'] ) )
		// 	$propel_settings['okm_server'] = $input['okm_server'];

		if ( isset( $input['auth0_token'] ) )
			$propel_settings['auth0_token'] = $input['auth0_token'];

		if ( isset( $input['auth0_database'] ) )
			$propel_settings['auth0_database'] = $input['auth0_database'];

		if ( isset( $input['auth0_client_id'] ) )
			$propel_settings['auth0_client_id'] = $input['auth0_client_id'];

		if ( isset( $input['store_url'] ) )
			$propel_settings['store_url'] = $input['store_url'];

		if ( isset( $input['olark_blacklist'] ) )
			$propel_settings['olark_blacklist'] = $input['olark_blacklist'];

		if ( isset( $input['olark_blocker_enabled'] ) && 'on' === $input['olark_blocker_enabled'] ) {
			$propel_settings['olark_blocker_enabled'] = 'on';
		} else {
			$propel_settings['olark_blocker_enabled'] = 'off';
		}

		if ( isset( $input['okm_sso_enabled'] ) && 'on' === $input['okm_sso_enabled'] ) {
			$propel_settings['okm_sso_enabled'] = 'on';
		} else {
			$propel_settings['okm_sso_enabled'] = 'off';
		}
		
		return $propel_settings;
	}

	/**
	 * Helper functions, mostly used by OKG Administration
	 * @author petermalcolm
	 */

	static function get_org_name_from_id_by_api( $org_id ) {
		$propel_settings = get_option( 'propel_settings' );
		$request = "?id=" . $org_id . "&tenant_secret_key=" . $propel_settings['okm_tenant_secret_key'];
		$response = Propel_LMS::ping_api( $request, 'organization_info', 'POST', 'application/x-www-form-urlencoded' );
		if( !array_key_exists('organization', $response) || 
			!array_key_exists('name', $response['organization'] ) ){
			return false;
		}
		return $response['organization']['name'];
	}

	static function get_org_details_from_id_by_api( $org_id ) {
		$propel_settings = get_option( 'propel_settings' );
		$request = "?id=" . $org_id . "&tenant_secret_key=" . $propel_settings['okm_tenant_secret_key'];
		$response = Propel_LMS::ping_api( $request, 'organization_info', 'POST', 'application/x-www-form-urlencoded' );
		if( !array_key_exists('organization', $response) || 
			!array_key_exists('name', $response['organization'] ) ){
			return false;
		}
		return $response['organization'];
	}

	static function get_child_orgs_by_api( $parent_org_id ) {
		$propel_settings = get_option( 'propel_settings' );
		$request = "?parent_id=" . $parent_org_id . "&tenant_secret_key=" . $propel_settings['okm_tenant_secret_key'];
		$response = Propel_LMS::ping_api( $request, 'organization_children', 'POST', 'application/x-www-form-urlencoded' );

		if( 0 === count( $response['children']) ) {
			return 'No key distributors are associated with your organization.';
		}
		return $response['children'];
	}

	/**
	 * Renders the form to export WP Quiz Pro results as CSV
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-04-03 13:12:02
	 *
	 * @return @void
	 */
	function render_export_form() {

		global $wpdb;

		// TODO: $wpdb->prepare %s was not working, as 'wp_wp_pro_quiz_master' was a string literal instead of a part of the larger string
		$wp_pro_quiz_master_table = $wpdb->prefix . 'wp_pro_quiz_master';
		$quiz_query = $wpdb->prepare( "SELECT id, name FROM $wp_pro_quiz_master_table" );

		$quizzes = $wpdb->get_results( $quiz_query, ARRAY_A );

		$column_names = $wpdb->get_col( "DESC " . $wp_pro_quiz_master_table, 0 );

		?>

		<h3>Export</h3>

		<p>Use the options below to export a CSV of quiz results.</p>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Quiz</th>
					<td>
						<select id="wp_pro_quiz" name="wp_pro_quiz">
							<?php
								foreach( $quizzes as $quiz )
									echo '<option value="' . $quiz['id'] . '">[' . $quiz['id'] . '] ' . $quiz['name'] . '</option>';
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">Start Date</th>
					<td><input type="text" id="start-date" /></td>
				</tr>
				<tr>
					<th scope="row">End Date</th>
					<td><input type="text" id="end-date" /></td>
				</tr>
				<tr>
					<th scope="row">Columns</th>
					<td>
						<ul>
							<li>
								<label style="display:block;width:100%">
									<input type="checkbox" id="toggle-all" name="">[ Toggle All ]
								</label>
							</li>
							<?php
								foreach( $column_names as $key=>$column_name )
									echo '<li><label style="display:block;width:100%"><input type="checkbox" name="column[' . $key . ']">' . $column_name . '</label></li>';
							?>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>

		<a class="button button-default" id="export-csv" href="<?php echo admin_url( 'options-general.php?page=propel-settings&export_quiz_csv' ); ?>">Export CSV</a>

	<?php
		wp_enqueue_script( 'propel-export', plugin_dir_url( __FILE__ ) . 'js/export.js', array( 'jquery-ui-datepicker' ) );

		wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11/themes/base/jquery-ui.css');
		wp_enqueue_style( 'jquery-ui' );
	}


	/**
	 * Displays notice and link to enter tenant key if none present
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @return void
	 */
	function okm_tenant_notice() {
		$propel_settings = get_option( 'propel_settings' );

		if ( isset( $propel_settings['okm_tenant_secret_key'] ) ) return;

		?>
		<div class="error">
			<p>You have not entered a <a href="<?php echo admin_url( 'options-general.php?page=propel-settings&tab=license' ); ?>">PROPEL OKM tenant secret key</a>.</p>
		</div>
		<?php

	}


	/**
	 * Registers the icon font for the PROPEL logo
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-05-01 15:46:33
	 */
	function register_scripts_and_styles() {
		wp_enqueue_style( 'propel-fontello',
			plugins_url( '/css/fontello.css', __FILE__ ) );
      wp_enqueue_script('propel-sso', plugins_url( '/js/sso.js' , __FILE__ ));  
	}


	/**
	 * Renders select field for okm_page_id setting
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-01-07 20:49:13
	 *
	 * @edited  2015-03-09 10:56:15 - Converts input to select of available pages
	 */
	function okm_page_id_callback() {

		$pages = array(
			'post_type'   => 'page',
			'nopaging'    => true,
			'post_status' => 'publish'
		);

		$pages = new WP_Query( $pages );

		echo '<select id="okm_page_id" name="propel_settings[okm_page_id]">';

		foreach ( $pages->posts as $page ) {
			if ( isset( $this->settings['okm_page_id'] ) && $this->settings['okm_page_id'] == $page->ID )
				$selected = 'selected';
			else
				$selected = '';

			echo '<option value="' . $page->ID . '" ' . $selected . '>[#' . $page->ID . '] ' .$page->post_title . '</option>';
		}

		echo '</select>';

		echo ' <a href="' . get_permalink( $this->settings['okm_page_id'] ) . '" target="_blank">' . get_the_title( $this->settings['okm_page_id'] ) . '</a>';
	}


	/**
	 * Renders select field for order_on_hold_page_id setting
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-03-09 11:03:43
	 */
	function order_on_hold_page_id_callback() {

		$pages = array(
			'post_type'   => 'page',
			'nopaging'    => true,
			'post_status' => 'publish'
		);

		$pages = new WP_Query( $pages );

		echo '<select id="order_on_hold_page_id" name="propel_settings[order_on_hold_page_id]">';

		foreach ( $pages->posts as $page ) {
			if ( isset( $this->settings['order_on_hold_page_id'] ) && $this->settings['order_on_hold_page_id'] == $page->ID )
				$selected = 'selected';
			else
				$selected = '';

			echo '<option value="' . $page->ID . '" ' . $selected . '>[#' . $page->ID . '] ' .$page->post_title . '</option>';
		}

		echo '</select>';

		echo ' <a href="' . get_permalink( $this->settings['order_on_hold_page_id'] ) . '" target="_blank">' . get_the_title( $this->settings['order_on_hold_page_id'] ) . '</a>';
	}


	/**
	 * Renders input field for okm_server setting for prod
	 * @author  pmalcolm
	 * @return  void
	 */
	function okm_server_stag_callback() {
		$is_live = Propel_LMS::is_this_pantheon_live();
		$icon_color = $is_live ? ' filter: grayscale(100%);' : ' '; // gray beaker, live!
		?>
		<img style="width:60px;<?php echo $icon_color; ?>" src="<?php echo plugin_dir_url(__FILE__) . '/img/testing.png'; ?>" />
		<?php
		printf(
			'<input style="width:320px;" type="text" id="okm_server_stag" name="propel_settings[okm_server_stag]" value="%s" />',
			isset( $this->settings['okm_server_stag'] ) ? esc_attr( $this->settings['okm_server_stag'] ) : ''
		);
		if( !$is_live ) { echo '<- This OKM will be used, as this site is NOT on a Pantheon live environment.'; }
	}
   
	/**
	 * Renders input field for okm_server setting for staging
	 * @author  pmalcolm
	 * @return  void
	 */
	function okm_server_prod_callback() {
		$is_live = Propel_LMS::is_this_pantheon_live();
		$icon_color = $is_live ? ' ' : ' filter: grayscale(100%);'; // gray globe, not live
		?>
		<img style="width:60px;<?php echo $icon_color; ?>" src="<?php echo plugin_dir_url(__FILE__) . '/img/production.png'; ?>" />
		<?php
		printf(
			'<input style="width:320px;" type="text" id="okm_server_prod" name="propel_settings[okm_server_prod]" value="%s" />',
			isset( $this->settings['okm_server_prod'] ) ? esc_attr( $this->settings['okm_server_prod'] ) : ''
		);
		if( $is_live ) { echo '<- This OKM will be used, as this site IS on a Pantheon live environment.'; }
	}
   
	/**
	 * Renders input field for okm_server setting
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-05 16:08:19
	 *
	 * @edited  2015-04-07 11:41:51 - Refactors to require http/https in the URI setting
	 *
	 * @return  void
	 */
	function okm_server_callback() {
		printf(
			'<input style="width:320px;" type="text" id="okm_server" name="propel_settings[okm_server]" value="%s" />',
			isset( $this->settings['okm_server'] ) ? esc_attr( $this->settings['okm_server'] ) : ''
		);
		echo '<p>If blank, defaults to <a href="' . Propel_LMS::OKM_SERVER . '" target="_blank">' . Propel_LMS::OKM_SERVER . '</a>. URI must include http/https. Staging OKM currently at ' . Propel_LMS::OKM_STAGING.'</p>';
	}
   
   function sso_disabled() {
      $disabled = "";
      if ( $this->settings['okm_sso_enabled'] != 'on') {
         $disabled = "disabled";
      }
      return $disabled;
   }

	function auth0_database_callback() {
		printf(
			'<input type="text" class="sso-setting" id="auth0_database" '.$this->sso_disabled().' name="propel_settings[auth0_database]" value="%s" />',
			isset( $this->settings['auth0_database'] ) ? esc_attr( $this->settings['auth0_database'] ) : ''
		);
		echo '<p>Find this on the Auth0 Dashboard Propel application connections</p>';
	} 

	function auth0_client_id_callback() {
		printf(
			'<input '.$this->sso_disabled().' class="sso-setting" style="width:300px" type="text" id="auth0_client_id" name="propel_settings[auth0_client_id]" value="%s" />',
			isset( $this->settings['auth0_client_id'] ) ? esc_attr( $this->settings['auth0_client_id'] ) : ''
		);
		echo '<p>Find this on the Auth0 Dashboard Propel application settings</p>';
	}

	function auth0_token_callback() {
      
		printf(
			'<input '.$this->sso_disabled().' class="sso-setting" style="width:100%%;" type="text" id="auth0_token" name="propel_settings[auth0_token]" value="%s" />',
			isset( $this->settings['auth0_token'] ) ? esc_attr( $this->settings['auth0_token'] ) : ''
		);
		echo '<p>Generate this token on the <a href="https://auth0.com/docs/api/v2#!/Users/patch_users_by_id">Auth0 APIv2 Explorer</a>.<br/>Use Scope <b>update:users</b> and <b>update:users_app_metadata</b>.</p>';
	}

	function store_url_callback() {
        printf(
            '<input type="text" id="store_url" name="propel_settings[store_url]" value="%s" />',
            isset( $this->settings['store_url'] ) ? esc_attr( $this->settings['store_url'] ) : ''
        );
   }
	
	function okm_sso_enabled_callback() {
		echo('<label>');
		$checked = "";
		if ($this->settings['okm_sso_enabled']=="on") $checked="checked='checked'";
		echo("<input $checked id='sso_enabled_cb' type='checkbox' name='propel_settings[okm_sso_enabled]'/> SSO Enabled");
		echo("</label>");
      ?>
         <p style="font-size: 0.9em;padding: 5px;margin: 9px 0 0 0;border: 1px solid #a80;background: #ffffee;">
            Checking this box will enable the SSO-related fields below. Only check it if all of the 
            prerequsite plugins have been installed, and an Auth0 account has been set up.
         </p>
      <?php
	}
	
	function olark_blocker_enabled_callback() {
		echo('<label>');
      $checked = "";
      if ($this->settings['olark_blocker_enabled']=="on") $checked="checked='checked'";
      echo("<input $checked type='checkbox' id='olark-blocker-cb' name='propel_settings[olark_blocker_enabled]'/> oLark Blocker Enabled");
      echo("</label>");
	}

    function olark_blacklist_callback() {
      $disabled = "";
      if ($this->settings['olark_blocker_enabled']!="on") $disabled="disabled";
	    printf(
			'<input '.$disabled.' style="width:500px" type="text" id="olark_blacklist" name="propel_settings[olark_blacklist]" value="%s" />',
			isset( $this->settings['olark_blacklist'] ) ? esc_attr( $this->settings['olark_blacklist'] ) : ''
		);
		echo '<p>A comma separated list of URL Fragments that will not show oLark help, for example <b>/courses</b>.<br/>If left blank, oLark help will be available on all pages.</p>';
	}


	/**:q
	 * Creates input field for ajax key validation
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @edited 2015-01-16 17:06:20
	 */
	function okm_tenant_key_input() {
		$propel_settings = get_option( 'propel_settings' );
		$current_key = $propel_settings['okm_tenant_secret_key']; ?>

		<h3>OKM Tenant Secret Key</h3>
		<input id="okm-tenant-secret-key" type="text" style="min-width:30%" value="<?php echo $current_key; ?>"/>

		<span class="dashicons dashicons-yes" style="display: none;"></span>
		<span class="dashicons dashicons-no" style="display: none;"></span>
		<img class="load" src="/wp-includes/js/thickbox/loadingAnimation.gif" />

		<style>
			img.load { display: none; width: 200px; }

			.dashicons-no  { font-size: 30px !important; color: red; }
			.dashicons-yes { font-size: 30px !important; color: green; }
		</style>

		<script src="<?php echo plugin_dir_url( __FILE__ ); ?>/js/tenant.js"></script>
<?php
	}


	/**
	 *
	 *
	 *
	 */
	function check_okm_tenant_secret_key() {

		$request = '?tenant_secret_key=' . $_POST['key'];

		$response = Propel_LMS::ping_api( $request, 'validate_tenant', 'GET' );

		if ( $response['http_status'] == 200 )
			wp_send_json_success();
		else
			wp_send_json_error();
	}


	/**
	 *
	 *
	 *
	 */
	function save_okm_tenant_secret_key() {
		error_log("save_okm_tenant_secret_key");
		Try {
			$propel_settings = get_option( 'propel_settings' );

			if ( isset( $propel_settings ) )
				$propel_settings['okm_tenant_secret_key'] = $_POST['key'];
			else
				$propel_settings = array( 'okm_tenant_secret_key' => $_POST['key'] );

			update_option( 'propel_settings', $propel_settings );

		} catch ( Exception $e ) {
			// Todo: Need to consider fail cases
			return false;
		}
		return true;
	}


	/**
	 * Adds a link to the 'okm' page to the WP Admin bar
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-01-07 12:32:00
	 * @edited  2015-05-01 13:12:32 - Refactors to use admin tenant okm if current user can view tenant OKM
	 *
	 * @param $wp_admin_bar   WP_Admin_Bar   Class container for admin bar
	 *
	 * @action admin_bar_menu
	 */
	function add_okm_page_link( $wp_admin_bar ) {

		$propel_settings = get_option( 'propel_settings' );

		if ( current_user_can( 'view_tenant_okm' ) ) {
			$args = array(
				'id'    => 'propel_okm',
				'title' => 'PROPEL OKM',
				'href'  => admin_url( 'admin.php?page=propel-okm' )
			);
		} elseif ( isset( $propel_settings['okm_page_id'] ) && get_post( $propel_settings['okm_page_id'] ) ) {
			$args = array(
				'id'    => 'propel_okm',
				'title' => 'PROPEL OKM',
				'href'  => '/wp-admin/options-general.php?page=propel-settings&tab=okm'
			);

		} else {
			return;
		}

		$wp_admin_bar->add_node( $args );
	}


	/**
	 * Exports a csv of the requested quiz data
	 *
	 * @author caseypatrickdriscoll
	 *
	 */
	function export_quiz_csv() {
		header( 'Pragma: public' );
		header( 'HTTP/1.0 200 OK' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false );
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="quiz_report' . '.csv";' );
		header( 'Content-Transfer-Encoding: binary' );
		$string = 'hi';
		exit( $string );
	}

}

new Propel_Settings();
