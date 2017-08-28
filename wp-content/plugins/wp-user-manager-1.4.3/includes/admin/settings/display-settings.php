<?php
/**
 * Admin Pages handler
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_options_page() {
	global $wpum_options;

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], wpum_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap" id="wpum-settings-panel">

		<h2 class="wpum-page-title"><?php printf( __( 'WP User Manager v%s Settings', 'wpum' ), WPUM_VERSION ); ?> <?php do_action('wpum_next_to_settings_title');?></h2>

		<h2 class="nav-tab-wrapper" style="margin-bottom:10px;">
			<?php
			foreach( wpum_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'emails-updated' => false,
					'wpum_action' => false,
					'setup_done' => false,
					'message' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
				<?php
				settings_fields( 'wpum_settings' );
				do_settings_fields( 'wpum_settings_' . $active_tab, 'wpum_settings_' . $active_tab );
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}
