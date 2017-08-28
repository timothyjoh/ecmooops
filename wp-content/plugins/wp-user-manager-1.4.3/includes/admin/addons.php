<?php
/**
 * Handles the display of the addons page.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Addons Class
 *
 * @since 1.0.0
 */
class WPUM_Addons {

	/**
	 * API URL
	 */
	protected $api = 'http://wpusermanager.com/?feed=addons';

	/**
	 * All addons
	 */
	var $addons = null;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		if( is_admin() && isset( $_GET['tab'] ) && $_GET['tab'] == 'wpum_addons' ) {

			// Get the transient
			$cached_feed = get_transient( 'wpum_addons_feed' );

			// Check if feed exist -
			// if feed exists get content from cached feed.
			if ($cached_feed) {

				$this->addons = $cached_feed;

			// Feed is not cached, get content from live api.
			} else {

				$feed = wp_remote_get( $this->api, array( 'sslverify' => false ) );

				if ( ! is_wp_error( $feed ) ) {

					$feed_content = wp_remote_retrieve_body( $feed );
					set_transient( 'wpum_addons_feed', $feed_content, 3600 );
					$this->addons = $feed_content;

				}

			}

		}

		add_filter( 'install_plugins_tabs', array( $this, 'wpum_add_addon_tab' ) );
		add_action( 'install_plugins_wpum_addons', array( $this, 'wpum_addons_page' ) );

	}

	/**
	 * Adds a new tab to the install plugins page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wpum_add_addon_tab( $tabs ) {

		$tabs['wpum_addons'] = __( 'WP User Manager ', 'wpum' ) . '<span class="wpum-addons">'.__('Addons', 'wpum').'</span>' ;
		return $tabs;

	}

	/**
	 * Handles the display of the content of the new tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wpum_addons_page() {

		?>

		<div class="wp-list-table widefat plugin-install">

			<?php if( empty( $this->addons ) ) : ?>

				<p><?php echo sprintf( __('Looks like there was a problem while retrieving the list of addons. Please visit <a href="%s">%s</a> if you are looking for the WP User Manager addons.', 'wpum'), 'http://wpusermanager.com/addons/', 'http://wpusermanager.com/addons/' ); ?></p>

			<?php else : ?>

				<br/>

				<div id="the-list">

					<?php echo $this->addons; ?>

				</div>

			<?php endif; ?>

		</div>

		<?php

	}

}

new WPUM_Addons;
