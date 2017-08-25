<?php

class Propel_Org_Admin {


  /**
   * Registers the necessary actions for 
   *   rendering additional user fields and saving them
   *
   * @author caseypatrickdriscoll
   *
   * @created 2015-01-08 16:09:55
   */
  function __construct() {

    // Load js file
    add_action( 'admin_enqueue_scripts', 
      array( $this, 'load_js' ) );


    // Render fields
    add_action( 'user_new_form',
      array( $this, 'render_user_fields' ) );

    add_action( 'show_user_profile',
      array( $this, 'render_user_fields' ) );

    add_action( 'edit_user_profile',
      array( $this, 'render_user_fields' ) );


    // Save fields
    add_action( 'personal_options_update',
      array( $this, 'save_user_fields' ) );

    add_action( 'edit_user_profile_update',
      array( $this, 'save_user_fields' ) );

    add_action( 'user_register',
      array( $this, 'save_user_fields' ) );


    add_action( 'wp_ajax_create_organization',
      array( $this, 'ajax_create_organization' ) );
  }

  
  /**
   * Load the js files only if on the correct admin pages
   *   - user-new.php
   *   - profile.php
   *   - user-edit.php
   *
   * @author caseypatrickdriscoll
   *
   * @created 2015-01-08 16:36:42
   *
   * @param string   $page   The current admin page name
   */
  function load_js( $page ) {
    $pages = array( 'user-new.php', 'user-edit.php', 'profile.php' );

    if ( ! in_array( $page, $pages ) )
      return;

    wp_register_script( 
      'propel_user', 
      plugin_dir_url( __FILE__ ) . 'js/user.js'
    );
  
  }

  /**
   * Creates the 'org_admin' role only during plugin activation
   *
   * @author caseypatrickdriscoll
   *
   * @created 2015-01-08 16:02:52
   *
   * @called statically from Propel_LMS->create_necessary_roles
   */
  public static function create_role() {
    if ( get_role( 'org_admin' ) )
      return;

    $capabilities = get_role( 'subscriber' )->capabilities;

    add_role( 'org_admin', 'Organization Admin', $capabilities );
  }


  /**
   * Renders the org_admin fields for the user form
   *
   * @author caseypatrickdriscoll
   *
   * @created 2015-01-08 15:40:26
   * @edited  2015-01-09 16:07:52
   *
   * @param WP_User   $user   The WP_User object
   *
   * @action user_new_form
   * @action show_user_profile
   * @action edit_user_profile
   */
  function render_user_fields( $user ) { 

    wp_localize_script( 
      'propel_user', 
      'org_id', 
      get_the_author_meta( 'propel_org_admin', $user->ID ) 
    );
    wp_enqueue_script( 'propel_user' ); 

    $org_options = $this->get_org_options();
    ?>


    <table class="form-table propel" style="display:none;">
      <tr class="form-field propel_organization">
        <th>
          <label for="propel_organization"><?php _e( 'Organization' ); ?></label>
        </th>
        <td>
          <select id="propel_organization" name="propel_organization">
            <option value="">Please select an organization</option>
            <?php echo $org_options; ?>
            <option value="0">Create a New Organization</option>
          </select>
        </td>
      </tr>
      <tr class="form-field new_org" style="display:none;">
        <th>
          <label for="propel_new_org"><?php _e( 'New Organization Name' ); ?></label>
        </th>
        <td>
          <input type="text" class="regular-text" size="16" style="max-width: 25em;" id="propel_new_org" />
          <a id="propel_create_org" class="button button-default">Create New Org</a>
          <ul class="message"></ul>
        </td>
      </tr>
    </table>

    <style>
      ul.message { 
        list-style: disc; 
        }
        ul.message li { 
          margin-left: 20px; 
        }
      .error { color: red; }
			.success { color: green; }
				.success .dashicons { line-height: inherit; }
    </style>

  <?php
  }


  /**
   * Retrieves the list of organizations from the OKM
   *
   * @author caseypatrickdriscoll
   *
   * @created 2015-01-19 17:58:51
   *
   * @return string   $out   HTML of organization select options
   */
  function get_org_options() {

    $propel_settings = get_option( 'propel_settings' );

    $request = '?tenant_secret_key=' . $propel_settings['okm_tenant_secret_key'];

    $response = Propel_LMS::ping_api( $request, 'organizations', 'GET' );

    $out = '';

    foreach ( $response['api'] as $org ) {
      $out .= '<option value="' . $org['id'] . '">' . $org['name'] . ' (' . $org['id'] . ')</option>';
    }

    return $out;
  }


  /**
   * Saves the org_admin user meta information
   *
   * @author  caseypatrickdriscoll
   *
   * @created 2015-01-09 15:39:24
   * @edited  2015-01-20 10:46:30 - Allows users to edit themselves
   * @edited  2015-01-30 13:10:51 - Syncs user with OKM
   *
   * @param   int   $user_id   The user id
   *
   * @action  edit_user_profile_update
   * @action  personal_options_update
   * @action  user_register
   */
  function save_user_fields( $user_id ) {
    // if ( ! current_user_can( 'edit_user', $user_id ) )
    //       return false;

    $propel_settings = get_option( 'propel_settings' );

    $user = get_user_by( 'id', $user_id );
    $user_info = get_userdata($user_id);
    $username = $user_info->user_login;
    $first_name = $user_info->first_name;
    $last_name = $user_info->last_name;

    if ( isset( $_POST['first_name'] ) && ! empty( $_POST['first_name'] ) ) {
      $first_name = $_POST['first_name'];
    }
    if ( isset( $_POST['last_name'] ) && ! empty( $_POST['last_name'] ) ) {
      $last_name = $_POST['last_name'];
    }
    if ( isset( $_POST['billing_first_name'] ) && ! empty( $_POST['billing_first_name'] ) ) {
      $first_name = $_POST['billing_first_name'];
    }
    if ( isset( $_POST['billing_last_name'] ) && ! empty( $_POST['billing_last_name'] ) ) {
      $last_name = $_POST['billing_last_name'];
    }
     if ( isset( $_POST['unique_id'] ) ) {
       $unique_id = $_POST['unique_id'];
       if ( isset( $_POST["first_name-" . "$unique_id"] ) ) {
         $first_name = $_POST["first_name-" . "$unique_id"];
       }
       if ( isset( $_POST["last_name-" . "$unique_id"] ) ) {
         $last_name = $_POST["last_name-" . "$unique_id"];
       }
     }

    $post_data = array(
                    'tenant_secret_key' => $propel_settings['okm_tenant_secret_key'],
                    'first_name'        => $first_name,
                    'last_name'         => $last_name,
                    'email'             => $user->user_email,
                    'ext_user_id'       => $user_id
                  );

    if ( isset( $_POST['propel_organization'] ) ) {
      $post_data['org_id']   = $_POST['propel_organization'];
      $post_data['password'] = $_POST['pass1'];

      update_user_meta( $user_id, 'propel_org_admin', $_POST['propel_organization'] );
    }

    global $nosyncuser;
    if ( ! isset($nosyncuser) ) {
      $response = Propel_LMS::ping_api( $post_data, 'sync_user' );
    }

  }


  /**
   * Contacts the OKM api to create a new organization
   *
   * DEPRECATED - 2016-11-10 @by petermalcolm
   * Please use Propel_OKG::ajax_create_organization_from_post_array() instead
   * It's more flexible.  defined in propel-okg.php
   * 
   * @author  caseypatrickdriscoll
   * @created 2015-01-19 18:39:01
   * @todo    Handle for failed cases
   * @return  wp_send_json_success
   */
  function ajax_create_organization() {
    $propel_settings = get_option( 'propel_settings' );

    $post_data = array(
                    'tenant_secret_key' => $propel_settings['okm_tenant_secret_key'],
                    'contact_first_name' => $_POST['contact_first_name'],
                    'contact_last_name'  => $_POST['contact_last_name'],
                    'name'               => $_POST['name']
                  );

    $response = Propel_LMS::ping_api( $post_data, 'sync_org' );

    wp_send_json_success( $response );
  }


}

new Propel_Org_Admin();
