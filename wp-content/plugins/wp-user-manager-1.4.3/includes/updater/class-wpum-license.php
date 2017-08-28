<?php
/**
 * WP User Manager Addons license handler.
 *
 * @package     wp-user-manager
 * @copyright   Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_License Class
 *
 * @since 1.2.4
 */
class WPUM_License {

  /**
	 * File path
	 *
	 * @var string
	 */
	private $file;

  /**
   * License stored.
   *
   * @var string
   */
  private $license;

  /**
   * Item name from the site.
   *
   * @var string
   */
  private $item_name;

  /**
   * Item shortname.
   *
   * @var string
   */
  private $item_shortname;

  /**
   * Item version.
   *
   * @var string
   */
  private $version;

  /**
   * The author of the plugin.
   *
   * @var string
   */
  private $author;

  /**
   * Api url.
   *
   * @var string
   */
  private $api_url = 'https://wpusermanager.com';

  /**
   * Construction function.
   *
   * @param string $file    file path.
   * @param string $item_name    item name.
   * @param string $version version of the addon.
   * @param string $author  author of the addon.
   */
	public function __construct( $file, $item_name, $version, $author, $_api_url = null ) {

    $this->file      = $file;
    $this->item_name = $item_name;
    $this->version   = $version;
    $this->author    = $author;

    if( ! empty( $_api_url ) )
      $this->api_url = $_api_url;

    $this->item_shortname = 'wpum_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
    $this->license        = trim( wpum_get_option( $this->item_shortname . '_license_key', '' ) );

    $this->includes();
    $this->hooks();

	}

  /**
   * Includes the EDD library.
   *
   * @since 1.2.4
   * @return void
   */
  private function includes() {

    if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
      include( WPUM_PLUGIN_DIR . '/includes/updater/EDD_SL_Plugin_Updater.php' );
    }

  }

  /**
   * Setup hooks.
   *
   * @since 1.2.4
   * @return void
   */
  private function hooks() {

    // Register settings.
		add_filter( 'wpum_settings_licenses', array( $this, 'settings' ), 1 );

    // Activate license key on settings save.
		add_action( 'admin_init', array( $this, 'activate_license' ) );

    // Deactivate license key.
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

    // Updater.
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

    // Notices.
    add_action( 'admin_notices', array( $this, 'notices' ) );

  }

  /**
   * Add new settings in admin panel.
   *
   * @since 1.2.4
   * @param  array $settings registered settings.
   * @return array           registered settings.
   */
  public function settings( $settings ) {

    $wpum_license_settings = array(
			array(
				'id'      => $this->item_shortname . '_license_key',
				'name'    => sprintf( __( '%1$s License Key', 'wpum' ), $this->item_name ),
				'desc'    => '',
				'type'    => 'license_key',
				'options' => array( 'is_valid_license_option' => $this->item_shortname . '_license_active' ),
				'size'    => 'regular'
			)
		);

    return array_merge( $settings, $wpum_license_settings );

  }

  /**
   * Process to activate the license.
   *
   * @return void
   * @since 1.2.4
   */
  public function activate_license() {

    if ( ! isset( $_POST['wpum_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['wpum_settings'][ $this->item_shortname . '_license_key'] ) ) {
			return;
		}

    foreach( $_POST as $key => $value ) {

      if( false !== strpos( $key, 'license_key_deactivate' ) ) {
				return;
			}

		}

    if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
			wp_die( __( 'Nonce verification failed', 'wpum' ), __( 'Error', 'wpum' ), array( 'response' => 403 ) );
		}

    if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( 'valid' === get_option( $this->item_shortname . '_license_active' ) ) {
			return;
		}

    $license = sanitize_text_field( $_POST['wpum_settings'][ $this->item_shortname . '_license_key'] );

    if( empty( $license ) ) {
			return;
		}

    // Data to send to the API.
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API.
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

    // Make sure there are no errors.
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Tell WordPress to look for updates.
		set_site_transient( 'update_plugins', null );

		// Decode license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

    update_option( $this->item_shortname . '_license_active', $license_data->license );

    if( ! (bool) $license_data->success ) {

			set_transient( 'wpum_license_error', $license_data, 1000 );

		} else {

      delete_transient( 'wpum_license_error' );

    }

  }

  /**
   * Process to deactivate a license.
   *
   * @since 1.2.4
   * @return void
   */
  public function deactivate_license() {

    if ( ! isset( $_POST['wpum_settings'] ) )
			return;

    if ( ! isset( $_POST['wpum_settings'][ $this->item_shortname . '_license_key'] ) )
			return;

    if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
  		wp_die( __( 'Nonce verification failed', 'wpum' ), __( 'Error', 'wpum' ), array( 'response' => 403 ) );
  	}

    if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

    // Run on deactivate button press.
		if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate'] ) ) {

      // Data to send to the API.
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API.
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors.
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			delete_option( $this->item_shortname . '_license_active' );

			if( ! (bool) $license_data->success ) {

				set_transient( 'wpum_license_error', $license_data, 1000 );

			} else {

				delete_transient( 'wpum_license_error' );

			}

    }

  }

  /**
   * Runs the EDD updater class.
   *
   * @since 1.2.4
   * @return void
   */
  public function auto_updater() {

    if ( 'valid' !== get_option( $this->item_shortname . '_license_active' ) )
			return;

    $edd_updater = new EDD_SL_Plugin_Updater( $this->api_url, $this->file, array(
    	'version'   => $this->version,		// current version number
    	'license'   => $this->license,	// license key (used get_option above to retrieve from DB)
    	'item_name' => $this->item_name,	// name of this plugin
    	'author'    => $this->author,	// author of this plugin
    	'url'       => home_url()
    ) );

  }

  /**
   * Admin notices.
   *
   * @since 1.2.4
   * @return void
   */
  public function notices() {

    if( ! isset( $_GET['page'] ) || 'wpum-settings' !== $_GET['page'] ) {
			return;
		}

    if( ! isset( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
			return;
		}

    $license_error = get_transient( 'wpum_license_error' );

    if( false === $license_error ) {
			return;
		}

    if( ! empty( $license_error->error ) ) {

    	switch( $license_error->error ) {
				case 'item_name_mismatch' :
					$message = esc_html__( 'This license does not belong to the product you have entered it for.', 'wpum' );
					break;
				case 'no_activations_left' :
					$message = esc_html__( 'This license does not have any activations left', 'wpum' );
					break;
				case 'expired' :
					$message = esc_html__( 'This license key is expired. Please renew it.', 'wpum' );
					break;
				default :
					$message = sprintf( esc_html__( 'There was a problem activating your license key, please try again or contact support. Error code: %s', 'wpum' ), $license_error->error );
					break;
			}

    }

    if( ! empty( $message ) ) {

    	echo '<div class="error">';
				echo '<p>' . $message . '</p>';
			echo '</div>';

    }

		delete_transient( 'wpum_license_error' );

  }

}
