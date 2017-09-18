<?php
/**
 * Addon activation handler.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Extension_Activation class.
 *
 * @since 1.0.0
 */
class WPUM_Extension_Activation {

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     * @var string
     */
    public $plugin_name;

    /**
     * The path of the plugin.
     *
     * @since 1.0.0
     * @var string
     */
    public $plugin_path;

    /**
     * The file of the plugin.
     *
     * @since 1.0.0
     * @var string
     */
    public $plugin_file;

    /**
     * Whether the main plugin is activated.
     *
     * @since 1.0.0
     * @var bool
     */
    public $has_wpum;

    /**
     * Base plugin path
     *
     * @since 1.0.0
     * @var string
     */
    public $wpum_base;

    /**
     * Setup the activation class.
     *
     * @since 1.0.0
     * @param string $plugin_path path of the plugin
     * @param string $plugin_file file of the plugin
     */
    public function __construct( $plugin_path, $plugin_file ) {

        // We need plugin.php!
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $plugins = get_plugins();

        // Set plugin directory.
        $plugin_path       = array_filter( explode( '/', $plugin_path ) );
        $this->plugin_path = end( $plugin_path );

        // Set plugin file.
        $this->plugin_file = $plugin_file;

        // Set plugin name.
        if( isset( $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] ) ) {
            $this->plugin_name = str_replace( 'WP User Manager - ', '', $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] );
        } else {
            $this->plugin_name = __( 'This plugin', 'wpum-custom-fields' );
        }

        // Is WPUM installed?
        foreach( $plugins as $plugin_path => $plugin ) {
            if( $plugin['Name'] == 'WP User Manager' ) {
                $this->has_wpum = true;
                $this->wpum_base = $plugin_path;
                break;
            }
        }
    }

    /**
     * Process plugin activation.
     *
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function run() {
        // Display notice.
        add_action( 'admin_notices', array( $this, 'missing_wpum_notice' ) );
    }

    /**
     * Display activation notice.
     *
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function missing_wpum_notice() {

        if( $this->has_wpum ) {

            $url  = esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $this->wpum_base ), 'activate-plugin_' . $this->wpum_base ) );
            $link = '<a href="' . $url . '">' . __( 'activate it', 'wpum-custom-fields' ) . '</a>';
            $message = $this->plugin_name . sprintf( __( ' requires WP User Manager! Please %s to continue!', 'wpum-custom-fields' ), $link );

        } else {

            $url  = esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wp-user-manager' ), 'install-plugin_wp-user-manager' ) );
            $link = '<a href="' . $url . '">' . __( 'install it', 'wpum-custom-fields' ) . '</a>';
            $message = $this->plugin_name . sprintf( __( ' requires WP User Manager! Please %s to continue!', 'wpum-custom-fields' ), $link );

        }

        echo '<div class="error"><p>'. $message .'</p></div>';

    }

}
