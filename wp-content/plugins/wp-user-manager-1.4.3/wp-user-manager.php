<?php
/*
Plugin Name: WP User Manager
Plugin URI:  http://wpusermanager.com
Description: Create customized user profiles and easily add custom user registration, login and password recovery forms to your WordPress website. WP User Manager is the best solution to manage your users.
Version: 1.4.3
Author:      Alessandro Tesoro
Author URI:  http://wpusermanager.com
License:     GPLv2+
Text Domain: wpum
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

if ( ! class_exists( 'WP_User_Manager' ) ) :

/**
 * Main WP_User_Manager Class
 *
 * @since 1.0.0
 */
class WP_User_Manager {

	/** Singleton *************************************************************/
	/**
	 * @var WP_User_Manager.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Forms Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	public $forms;

	/**
	 * WPUM Emails Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	public $emails;

	/**
	 * WPUM Email Template Tags Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	public $email_tags;

	/**
	 * HTML Element Helper Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	public $html;

	/**
	 * Field Groups DB Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	public $field_groups;

	/**
	 * Fields DB Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	public $fields;

	/**
	 * Main WP_User_Manager Instance
	 *
	 * Insures that only one instance of WP_User_Manager exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0.0
	 * @uses WP_User_Manager::setup_constants() Setup the constants needed
	 * @uses WP_User_Manager::includes() Include the required files
	 * @uses WP_User_Manager::load_textdomain() load the language files
	 * @see WPUM()
	 * @return WP_User_Manager
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_User_Manager ) ) {

			self::$instance               = new WP_User_Manager;
			self::$instance->setup_constants();
			self::$instance->includes();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->emails       = new WPUM_Emails();
			self::$instance->email_tags   = new WPUM_Email_Template_Tags();
			self::$instance->forms        = new WPUM_Forms();
			self::$instance->html         = new WPUM_HTML_Elements();
			self::$instance->field_groups = new WPUM_DB_Field_Groups();
			self::$instance->fields       = new WPUM_DB_Fields();

		}

		return self::$instance;

	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpum' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpum' ), '1.0.0' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version
		if ( ! defined( 'WPUM_VERSION' ) ) {
			define( 'WPUM_VERSION', '1.4.3' );
		}

		// Plugin Folder Path
		if ( ! defined( 'WPUM_PLUGIN_DIR' ) ) {
			define( 'WPUM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'WPUM_PLUGIN_URL' ) ) {
			define( 'WPUM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'WPUM_PLUGIN_FILE' ) ) {
			define( 'WPUM_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Slug
		if ( ! defined( 'WPUM_SLUG' ) ) {
			define( 'WPUM_SLUG', plugin_basename( __FILE__ ) );
		}

	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {

		global $wpum_options;

		require_once WPUM_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		$wpum_options = wpum_get_settings();

		// Load Assets Files
		require_once WPUM_PLUGIN_DIR . 'includes/assets.php';
		// Load General Functions
		require_once WPUM_PLUGIN_DIR . 'includes/functions.php';
		// Load Misc Functions
		require_once WPUM_PLUGIN_DIR . 'includes/misc-functions.php';
		// Templates
		require_once WPUM_PLUGIN_DIR . 'includes/templates-loader.php';
		// Plugin's filters
		require_once WPUM_PLUGIN_DIR . 'includes/filters.php';
		// Plugin's actions
		require_once WPUM_PLUGIN_DIR . 'includes/actions.php';
		// Shortcodes
		require_once WPUM_PLUGIN_DIR . 'includes/classes/class-wpum-shortcodes.php';
		// Emails
		require_once WPUM_PLUGIN_DIR . 'includes/emails/class-wpum-emails.php';
		require_once WPUM_PLUGIN_DIR . 'includes/emails/class-wpum-emails-tags.php';
		require_once WPUM_PLUGIN_DIR . 'includes/emails/functions.php';
		// Load html helper class
		require_once WPUM_PLUGIN_DIR . 'includes/classes/class-wpum-html-helper.php';
		// Load db helper class
		require_once WPUM_PLUGIN_DIR . 'includes/abstracts/abstract-wpum-db.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/class-wpum-db-field-groups.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/class-wpum-db-fields.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/class-wpum-fields-data-template.php';
		// Load fields helpers
		require_once WPUM_PLUGIN_DIR . 'includes/abstracts/abstract-wpum-field-type.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/avatar.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/checkbox.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/checkboxes.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/display_name.php';
		//require_once WPUM_PLUGIN_DIR . 'includes/fields/types/datepicker.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/email.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/file.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/multiselect.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/nickname.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/number.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/password.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/radio.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/select.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/text.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/textarea.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/url.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/types/username.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/functions.php';
		require_once WPUM_PLUGIN_DIR . 'includes/fields/filters.php';
		// Forms
		require_once WPUM_PLUGIN_DIR . 'includes/classes/class-wpum-forms.php';
		// Menu controller
		require_once WPUM_PLUGIN_DIR . 'includes/classes/class-wpum-menu-controller.php';

		// Files loaded only on the admin side.
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

			require_once WPUM_PLUGIN_DIR . 'includes/admin/welcome.php';
			require_once WPUM_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once WPUM_PLUGIN_DIR . 'includes/admin/admin-notices.php';
			require_once WPUM_PLUGIN_DIR . 'includes/admin/admin-actions.php';
			require_once WPUM_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once WPUM_PLUGIN_DIR . 'includes/admin/upgrade/upgrades.php';

			// Load Emails
			require_once WPUM_PLUGIN_DIR . 'includes/admin/emails/class-wpum-emails-editor.php';
			require_once WPUM_PLUGIN_DIR . 'includes/admin/emails/class-wpum-emails-list.php';
			require_once WPUM_PLUGIN_DIR . 'includes/emails/registration-email.php';
			require_once WPUM_PLUGIN_DIR . 'includes/emails/password-recovery-email.php';
			// Load Custom Fields Editor
			require_once WPUM_PLUGIN_DIR . 'includes/admin/fields/class-wpum-fields-editor.php';

			// Load admin menu manager functionalities
			require_once WPUM_PLUGIN_DIR . 'includes/classes/class-wpum-walker-nav-menu-checklist.php';
			require_once WPUM_PLUGIN_DIR . 'includes/admin/menu-functions.php';
			require_once WPUM_PLUGIN_DIR . 'includes/walkers/class-walker-wpum-nav-menu-roles-controller.php';

			// Load dashboard widget
			require_once WPUM_PLUGIN_DIR . 'includes/admin/dashboard-widget.php';

			// Custom Fields Framework
			if ( ! class_exists( 'Pretty_Metabox' ) )
				require_once WPUM_PLUGIN_DIR . 'includes/lib/wp-pretty-fields/wp-pretty-fields.php';

			// Load Tools Page
			require_once WPUM_PLUGIN_DIR . 'includes/admin/tools.php';
			// Load Addons Page
			require_once WPUM_PLUGIN_DIR . 'includes/admin/addons.php';

			// License Handler.
			require_once WPUM_PLUGIN_DIR . 'includes/updater/class-wpum-license.php';

		}

		// Directory for WPUM
		require_once WPUM_PLUGIN_DIR . 'includes/directories/class-wpum-directory.php';
		require_once WPUM_PLUGIN_DIR . 'includes/directories/actions.php';
		require_once WPUM_PLUGIN_DIR . 'includes/directories/filters.php';
		require_once WPUM_PLUGIN_DIR . 'includes/directories/functions.php';
		// Ajax Handler
		require_once WPUM_PLUGIN_DIR . 'includes/classes/class-wpum-ajax-handler.php';
		// Permalinks for WPUM
		require_once WPUM_PLUGIN_DIR . 'includes/classes/class-wpum-permalinks.php';
		// Template actions
		require_once WPUM_PLUGIN_DIR . 'includes/template-actions.php';
		// Load Profiles
		require_once WPUM_PLUGIN_DIR . 'includes/profiles/functions.php';
		require_once WPUM_PLUGIN_DIR . 'includes/profiles/actions.php';
		require_once WPUM_PLUGIN_DIR . 'includes/profiles/tabs.php';
		// Load all widgets
		if( class_exists( 'WP_Widget' ) ) {

			require_once WPUM_PLUGIN_DIR . 'includes/lib/wph-widget-class.php';
			require_once WPUM_PLUGIN_DIR . 'includes/widgets/wpum-recent-users.php';
			require_once WPUM_PLUGIN_DIR . 'includes/widgets/wpum-password-recovery.php';
			require_once WPUM_PLUGIN_DIR . 'includes/widgets/wpum-registration.php';
			require_once WPUM_PLUGIN_DIR . 'includes/widgets/wpum-login-form.php';

		}

		// Installation Hook
		require_once WPUM_PLUGIN_DIR . 'includes/install.php';

	}

	/**
	 * Load the language files for translation
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		// Set filter for plugin's languages directory
		$wpum_lang_dir = dirname( plugin_basename( WPUM_PLUGIN_FILE ) ) . '/languages/';
		$wpum_lang_dir = apply_filters( 'wpum_languages_directory', $wpum_lang_dir );

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'wpum' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'wpum', $locale );

		// Setup paths to current locale file
		$mofile_local  = $wpum_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/wpum/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/wpum folder
			load_textdomain( 'wpum', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/wp-user-manager/languages/ folder
			load_textdomain( 'wpum', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'wpum', false, $wpum_lang_dir );
		}
	}

}

endif;

/**
 * The main function responsible for returning WP_User_Manager
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $wpum = WPUM(); ?>
 *
 * @since 1.0.0
 * @return object WP_User_Manager Instance
 */
function WPUM() {
	return WP_User_Manager::instance();
}

// Get WPUM Running
WPUM();
