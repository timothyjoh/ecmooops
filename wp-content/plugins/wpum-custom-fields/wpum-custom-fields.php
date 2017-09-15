<?php
/*
Plugin Name: WPUM - Custom Fields
Plugin URI:  https://wpusermanager.com/addons/custom-fields/
Description: Lets you visually create and manage custom fields for your users. Choose among different field types and customize your community.
Version: 1.1.4
Author:      Alessandro Tesoro
Author URI:  http://wpusermanager.com
License:     GPLv2+
Text Domain: wpum-custom-fields
Domain Path: /languages
*/

/**
 * Copyright (c) 2015 Alessandro Tesoro
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WPUM_Custom_Fields' ) ) :

/**
 * Main WPUM_Custom_Fields Class
 *
 * @since 1.0.0
 */
class WPUM_Custom_Fields {

	/**
   * Instance of the addon.
   *
   * @since 1.0.0
   * @var instance of the addon.
   */
	private static $instance;

	/**
   * Get activated instance.
   *
   * @return object instance
   * @since 1.0.0
   */
	public static function instance() {
	    if( ! self::$instance ) {

					self::$instance = new WPUM_Custom_Fields();
	        self::$instance->setup_constants();
					self::$instance->includes();

					add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

	    }
	    return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access protected
	 * @return void
	 * @since 1.0.0
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpum-custom-fields' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 * @since 1.0.0
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpum-custom-fields' ), '1.0.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private function setup_constants() {

		// Plugin version
		if ( ! defined( 'WPUMCF_VERSION' ) ) {
			define( 'WPUMCF_VERSION', '1.1.4' );
		}

		// Plugin Folder Path
		if ( ! defined( 'WPUMCF_PLUGIN_DIR' ) ) {
			define( 'WPUMCF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'WPUMCF_PLUGIN_URL' ) ) {
			define( 'WPUMCF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'WPUMCF_PLUGIN_FILE' ) ) {
			define( 'WPUMCF_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Slug
		if ( ! defined( 'WPUMCF_SLUG' ) ) {
			define( 'WPUMCF_SLUG', plugin_basename( __FILE__ ) );
		}

		define( 'WPUM_CF_PRODUCT_NAME', 'Custom Fields' );

	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private function includes() {

		require_once WPUMCF_PLUGIN_DIR . 'includes/functions.php';
		require_once WPUMCF_PLUGIN_DIR . 'includes/filters.php';
		require_once WPUMCF_PLUGIN_DIR . 'includes/class-wpumcf-update-account.php';
		require_once WPUMCF_PLUGIN_DIR . 'includes/class-wpumcf-register.php';

		if( is_admin() ) {

			require_once WPUMCF_PLUGIN_DIR . 'includes/assets.php';
			require_once WPUMCF_PLUGIN_DIR . 'includes/class-wpumcf-new-group-editor.php';
			require_once WPUMCF_PLUGIN_DIR . 'includes/class-wpumcf-new-fields-editor.php';
			require_once WPUMCF_PLUGIN_DIR . 'includes/class-wpumcf-extend-profile.php';

			if( class_exists( 'WPUM_License' ) ) {

				$wpumcf_license = new WPUM_License(
					__FILE__,
					WPUM_CF_PRODUCT_NAME,
					WPUMCF_VERSION,
					'Alessandro Tesoro'
				);

			}

		}

	}

	/**
	 * Load the language files for translation.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory
		$wpum_lang_dir = dirname( plugin_basename( WPUMCF_PLUGIN_FILE ) ) . '/languages/';
		$wpum_lang_dir = apply_filters( 'wpumcf_languages_directory', $wpum_lang_dir );

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'wpum-custom-fields' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'wpum-custom-fields', $locale );

		// Setup paths to current locale file
		$mofile_local  = $wpum_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/wpum-custom-fields/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/wpum-custom-fields folder
			load_textdomain( 'wpum-custom-fields', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/wpum-custom-fields/languages/ folder
			load_textdomain( 'wpum-custom-fields', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'wpum-custom-fields', false, $wpum_lang_dir );
		}

	}

	/**
	 * Display version mismatch message if base plugin does not meet minimum requirement.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public static function version_mismatch() {
		?>
		<div class="error">
			<p><?php printf( esc_html__( 'The WPUM - Custom Fields Addon requires version 1.2.0 or greater of the WP User Manager plugin. Please %s to continue.', 'wpum-custom-fields' ), '<a href="'.admin_url( 'update-core.php' ).'">update it</a>'  ); ?></p>
		</div>
		<?php
	}

}

endif;

/**
 * Handles activation of the addon.
 *
 * @return mixed
 * @since 1.0.0
 */
function WPUM_Custom_Fields_Load() {

	if( ! class_exists( 'WP_User_Manager' ) ) {

		// Show message if plugin is not activated.
		if( ! class_exists( 'WPUM_Extension_Activation' ) ) {
			require_once 'includes/class.extension-activation.php';
		}
		$activation = new WPUM_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

	} else {

		$base_version     = defined( 'WPUM_VERSION' ) ? WPUM_VERSION : false;
		$required_version = '1.2.4';

		if ( version_compare( $base_version, $required_version, '<' ) ) {
			return add_action( 'admin_notices', 'WPUM_Custom_Fields::version_mismatch' );
		}

		return WPUM_Custom_Fields::instance();

	}

}
add_action( 'plugins_loaded', 'WPUM_Custom_Fields_Load' );
