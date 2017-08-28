<?php
/**
 * Handles the display of the tools page into the admin panel
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display content of the tools page into the admin panel.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_tools_page() {

	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'export_import';

  ?>

  <div class="wrap" id="wpum-tools-page">

		<h2 class="wpum-page-title"><?php printf( __( 'WP User Manager Tools', 'wpum' ), WPUM_VERSION ); ?> <?php do_action('wpum_next_to_settings_title');?></h2>

		<h2 class="nav-tab-wrapper">
			<?php
			foreach( wpum_get_tools_tabs() as $tab_id => $tab_name ) {
				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );
				$tab_url = remove_query_arg( array(
					'wpum-message'
				), $tab_url );
				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'wpum_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->

  <?php

}

/**
 * Get list of tabs for the tools page.
 *
 * @since 1.2.0
 * @return array list of tabs.
 */
function wpum_get_tools_tabs() {

	$tabs                  = array();
	$tabs['export_import'] = esc_html__( 'Export/Import Settings', 'wpum' );

	return apply_filters( 'wpum_tools_tabs', $tabs );

}

/**
 * Display content of the export/import tab.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_tools_tab_export_import() {

	?>

	<div class="postbox">

		<h3 class="hndle"><span><?php esc_html_e( 'Export Settings', 'wpum' ); ?></span></h3>

		<div class="inside">
			<p><?php _e( 'Export the WP User Manager settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'wpum' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'users.php?page=wpum-tools' ); ?>">
					<p><input type="hidden" name="wpum_action" value="export_settings" /></p>
					<p>
						<?php wp_nonce_field( 'wpum_export_nonce', 'wpum_export_nonce' ); ?>
						<?php submit_button( esc_html__( 'Export Settings', 'wpum' ), 'secondary', 'submit', false ); ?>
					</p>
			</form>
		</div>

	</div><!-- .postbox -->

	<div class="postbox">

		<h3 class="hndle"><span><?php esc_html_e( 'Import Settings', 'wpum' ); ?></span></h3>

		<div class="inside">
			<p><?php _e( 'Import the WP User Manager settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'wpum' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'users.php?page=wpum-tools' ); ?>">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="wpum_action" value="import_settings" />
					<?php wp_nonce_field( 'wpum_import_nonce', 'wpum_import_nonce' ); ?>
					<?php submit_button( esc_html__( 'Import Settings', 'wpum' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div>

	</div><!-- .postbox -->

	<?php

}
add_action( 'wpum_tools_tab_export_import', 'wpum_tools_tab_export_import' );

/**
 * Processes settings export functionality.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_process_settings_export() {

	if( empty( $_POST['wpum_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['wpum_export_nonce'], 'wpum_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_options' ) )
		return;

	$settings             = array();
	$settings['settings'] = get_option( 'wpum_settings' );
	$settings['emails']   = get_option( 'wpum_emails' );

	ignore_user_abort( true );

	if ( ! wpum_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	nocache_headers();

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . 'wpum-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );

	exit;

}
add_action( 'admin_init', 'wpum_process_settings_export' );

/**
 * Processes settings import functionality.
 *
 * @since 1.2.0
 * @return void
 */
function wpum_process_settings_import() {

	if( empty( $_POST['wpum_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['wpum_import_nonce'], 'wpum_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_options' ) )
		return;

	if( wpum_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
		wp_die( __( 'Please upload a valid .json file', 'wpum' ), __( 'Error', 'wpum' ), array( 'response' => 400 ) );
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'wpum' ), __( 'Error', 'wpum' ), array( 'response' => 400 ) );
	}

	$settings = wpum_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'wpum_settings', $settings['settings'] );
	update_option( 'wpum_emails', $settings['emails'] );

	$url = add_query_arg( array( 'message' => 'settings_imported' ), admin_url( 'users.php?page=wpum-tools' ) );
	wp_safe_redirect( $url ); exit;

}
add_action( 'admin_init', 'wpum_process_settings_import' );
