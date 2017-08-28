<?php
/**
 * Plugin Name: WP - Pretty Fields
 * Plugin URI:  https://alessandrotesoro.me/
 * Description: Lightweight WordPress metaboxes and fields framework.
 * Version:     1.1.0
 * Author:      Alessandro Tesoro
 * Author URI:  https://alessandrotesoro.me
 * License:     GPLv2+
 * Text Domain: wppf
 * Domain Path: /languages
 *
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
 * 
 * @package wp-pretty-fields
 * @author Alessandro Tesoro
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('Pretty_Base')) {
	
	/**
	 * Pretty Base class.
	 */
	class Pretty_Base {

		/**
		 * Constructor - get the plugin hooked in and ready
		 * @since    1.0.0
		 */
		public function __construct() {
			$this->setup_constants();
			$this->load();

			// load framework assets css and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
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
			if ( ! defined( 'WPPF_VERSION' ) ) {
				define( 'WPPF_VERSION', '1.1.0' );
			}
			// Plugin Folder Path
			if ( ! defined( 'WPPF_PLUGIN_DIR' ) ) {
				define( 'WPPF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}
			// Plugin Folder URL
			if ( ! defined( 'WPPF_PLUGIN_URL' ) ) {
				define( 'WPPF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
			// Plugin Root File
			if ( ! defined( 'WPPF_PLUGIN_FILE' ) ) {
				define( 'WPPF_PLUGIN_FILE', __FILE__ );
			}
		}

		/**
		 * Load framework classes.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load() {
			
			require_once( WPPF_PLUGIN_DIR . 'classes/class.metabox.php' );
			require_once( WPPF_PLUGIN_DIR . 'classes/class.taxonomy_metabox.php' );
			require_once( WPPF_PLUGIN_DIR . 'classes/class.user_metabox.php' );
			require_once( WPPF_PLUGIN_DIR . 'classes/class.fields.php' );
			
			// Load all fields files into the "fields" folder
			foreach ( glob( WPPF_PLUGIN_DIR . 'fields/*.php' ) as $file ){
				require_once $file;
			}

			// Load demo only if defined
			if( defined( 'WPPF_DEMO' ) && WPPF_DEMO == true )
				require_once( WPPF_PLUGIN_DIR . 'demo.php' );
		}

		/**
		 * Load framework assets css and scripts.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function assets() {

			$js_dir  = WPPF_PLUGIN_URL . 'assets/js/';
			$css_dir = WPPF_PLUGIN_URL . 'assets/css/';

			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			// Styles
			wp_register_style( 'wppf-admin', $css_dir . 'wp_pretty_fields' . $suffix . '.css', WPPF_VERSION );

			// Scripts
			wp_register_script( 'wppf-admin-js', $js_dir . 'wp_pretty_fields' . $suffix . '.js', array('jquery'), WPPF_VERSION, true );

			// Enqueue styles
			wp_enqueue_style( 'wppf-admin' );

			// Enqueue scripts
			wp_enqueue_script( 'wppf-admin-js' );

		}
		
	}

	new Pretty_Base;

}