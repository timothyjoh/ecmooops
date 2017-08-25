<?php
if (!class_exists('Learndash_Admin_Settings_Support_Panel')) {
	class Learndash_Admin_Settings_Support_Panel {
		
		function __construct() {
			add_action( 'admin_menu', 			array( $this, 'admin_menu' ) );
		}
		
		/**
		 * Register settings page
		 */
		public function admin_menu() {
			$page_hook = add_submenu_page(
				'learndash-lms-non-existant',
				_x( 'Support', 'Support Tab Label', 'learndash' ),
				_x( 'Support', 'Support Tab Label', 'learndash' ),
				LEARNDASH_ADMIN_CAPABILITY_CHECK,
				'learndash_support',
				array( $this, 'admin_page' )
			);
			add_action( 'load-'. $page_hook, array( $this, 'on_load_panel' ) );
		}
		
		function on_load_panel() {

			// Load JS/CSS as needed for page
			wp_enqueue_style( 
				'sfwd-module-style', 
				LEARNDASH_LMS_PLUGIN_URL . 'assets/css/sfwd_module'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', 
				array(), 
				LEARNDASH_VERSION 
			);
			$learndash_assets_loaded['styles']['sfwd-module-style'] = __FUNCTION__;
		}

		/**
		 * Output settings page
		 */
		public function admin_page() {
			?>
			<div id="learndash-settings-support" class="learndash-settings" class="wrap">
				<h1><?php _e( 'Support', 'learndash' ); ?></h1>
				<p><a class="button button-primary" target="_blank" href="http://support.learndash.com/"><?php _e('LearnDash Support', 'learndash') ?></a></p>
				<hr />

				<?php
				global $wpdb, $wp_version;
				?>
				
				<h2><?php _e('Server', 'learndash' ); ?></h2>
				<table cellspacing="0" class="learndash-support-settings">
					<thead>
						<tr>
							<th class="learndash-support-settings-left"><?php _e('Setting', 'learndash') ?></th>
							<th class="learndash-support-settings-right"><?php _e('Value', 'learndash') ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td><strong><?php _e('PHP Version', 'learndash') ?></strong></td><td><?php 
							$php_version = phpversion(); 
							$version_compare = version_compare( '5.6', $php_version, '>' );
							$color = 'green';
							if ( $version_compare == -1) {
								$color = 'red';
							} 
							echo '<span style="color: '. $color .'">'. $php_version .'</span>'; 
							if ( $version_compare == -1) {
								echo ' - <a href="https://wordpress.org/about/requirements/" target="_blank">'. __('WordPress Minimum Requirements', 'learndash') .'</a>';
							}
							
						?></td></tr>
						<?php if ($wpdb->is_mysql == true) { ?>
						<tr><td><strong><?php _e('MySQL version', 'learndash') ?></strong></td><td><?php 
							$mysql_version = $wpdb->db_version();
							
							$version_compare = version_compare( '5.6', $mysql_version, '>' );
							$color = 'green';
							if ( $version_compare == -1) {
								$color = 'red';
							} 
							echo '<span style="color: '. $color .'">'. $mysql_version .'</span>'; 
							if ( $version_compare == -1) {
								echo ' - <a href="https://wordpress.org/about/requirements/" target="_blank">'. __('WordPress Minimum Requirements', 'learndash') .'</a>';
							}
							
						?><td></tr>
						<?php } ?>
					</tbody>
				</table>
				
				<h2><?php _e('WordPress Settings', 'learndash' ); ?></h2>
				<table cellspacing="0" class="learndash-support-settings">
					<thead>
						<tr>
							<th class="learndash-support-settings-left"><?php _e('Setting', 'learndash') ?></th>
							<th class="learndash-support-settings-right"><?php _e('Value', 'learndash') ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td><strong><?php _e('WordPress Version', 'learndash') ?></strong></td><td><?php echo $wp_version; ?></td></tr>
						<tr><td><strong><?php _e('Is Multisite', 'learndash') ?></strong></td><td><?php if (is_multisite()) { echo "Yes"; } else { echo "No"; } ?></td></tr>
						<tr><td><strong><?php _e('Site Language', 'learndash') ?></strong></td><td><?php echo get_locale(); ?></td></tr>
						<tr><td><strong><?php _e('DISABLE_WP_CRON', 'learndash') ?></strong></td><td><?php if ( defined('DISABLE_WP_CRON')) { DISABLE_WP_CRON; } else { _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('WP_DEBUG', 'learndash') ?></strong></td><td><?php if ( defined('WP_DEBUG')) { echo WP_DEBUG; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('WP_DEBUG_DISPLAY', 'learndash') ?></strong></td><td><?php if ( defined('WP_DEBUG_DISPLAY')) { echo WP_DEBUG_DISPLAY; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('SCRIPT_DEBUG', 'learndash') ?></strong></td><td><?php if ( defined('SCRIPT_DEBUG')) { echo SCRIPT_DEBUG; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('WP_DEBUG_DISPLAY', 'learndash') ?></strong></td><td><?php if ( defined('WP_DEBUG_DISPLAY')) { echo WP_DEBUG_DISPLAY; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('WP_DEBUG_LOG', 'learndash') ?></strong></td><td><?php if ( defined('WP_DEBUG_LOG')) { echo WP_DEBUG_LOG; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('WP_AUTO_UPDATE_CORE', 'learndash') ?></strong></td><td><?php if ( defined('WP_AUTO_UPDATE_CORE')) { echo WP_AUTO_UPDATE_CORE; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('WP_MAX_MEMORY_LIMIT', 'learndash') ?></strong></td><td><?php if ( defined('WP_MAX_MEMORY_LIMIT')) { echo WP_MAX_MEMORY_LIMIT; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('WP_MEMORY_LIMIT', 'learndash') ?></strong></td><td><?php if ( defined('WP_MEMORY_LIMIT')) { echo WP_MEMORY_LIMIT; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('DB_CHARSET', 'learndash') ?></strong></td><td><?php if ( defined('DB_CHARSET')) { echo DB_CHARSET; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						<tr><td><strong><?php _e('DB_COLLATE', 'learndash') ?></strong></td><td><?php if ( defined('DB_COLLATE')) { echo DB_COLLATE; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
					</tbody>
				</table>
				
				<h2><?php _e('Learndash settings', 'learndash' ); ?></h2>
				<table cellspacing="0" class="learndash-support-settings">
					<thead>
						<tr>
							<th class="learndash-support-settings-left"><?php _e('Setting', 'learndash') ?></th>
							<th class="learndash-support-settings-right"><?php _e('Value', 'learndash') ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td><strong><?php _e('Version', 'learndash') ?></strong></td><td><?php echo LEARNDASH_VERSION; ?></td></tr>
						<tr><td><strong><?php _e('DB Version', 'learndash') ?></strong></td><td><?php echo LEARNDASH_SETTINGS_DB_VERSION; ?></td></tr>
						<tr><td><strong><?php _e('Script Debug', 'learndash') ?></strong></td><td><?php if ( defined('LEARNDASH_SCRIPT_DEBUG')) { echo LEARNDASH_SCRIPT_DEBUG; } else { echo _e('not defined', 'learndash'); } ?></td></tr>
						
						
					</tbody>
				</table>

				<h2><?php _e('Learndash Templates', 'learndash' ); ?></h2>
				<?php $template_array = array('course_content_shortcode', 'course_info_shortcode', 'course_list_template', 'course_navigation_admin', 'course_navigation_widget', 'course_progress_widget', 'course', 'lesson', 'profile', 'quiz', 'topic', 'user_groups_shortcode'); ?>
				<table cellspacing="0" class="learndash-support-settings">
					<thead>
						<tr>
							<th class="learndash-support-settings-left"><?php _e('Template Name', 'learndash') ?></th>
							<th class="learndash-support-settings-right"><?php _e('Template Path', 'learndash') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($template_array as $template) { ?>
							<tr>
								<td><strong><?php echo $template ?></strong></td>
								<td><?php 
									$template_path = SFWD_LMS::get_template( $template, null, null, true );
									if (strncmp ( $template_path, LEARNDASH_LMS_PLUGIN_DIR , strlen(LEARNDASH_LMS_PLUGIN_DIR) ) != 0) {
										$color = 'red';
									} else {
										$color = 'inherit';
									}
								
									echo '<span style="color: '. $color .'">'. str_replace(ABSPATH, '', $template_path) .'</span>'; 
								?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				
				
				<?php
				$php_ini_settings = array('max_execution_time', 'max_input_time', 'max_input_vars', 'post_max_size', 'max_file_uploads', 'upload_max_filesize');
				sort($php_ini_settings);
				?>
				<h2><?php _e('PHP Settings', 'learndash' ); ?></h2>
				<table cellspacing="0" class="learndash-support-settings">
					<thead>
						<tr>
							<th class="learndash-support-settings-left"><?php _e('Setting', 'learndash') ?></th>
							<th class="learndash-support-settings-right"><?php _e('Value', 'learndash') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($php_ini_settings as $ini_key ) {?>
							<tr><td><strong><?php echo $ini_key ?></strong></td><td><?php echo ini_get( $ini_key ) ?></td></tr>
						<?php } ?>
					</tbody>
				</table>
						
				
				<?php /* ?>
				<h2><?php _e('Active Theme', 'learndash' ); ?></h2>
				<ul>
				<?php 
					$current_theme =  wp_get_theme(); 
					//echo "current_theme<pre>". print_r($current_theme, true) ."</pre>";
					if ( $current_theme->exists() ) {
						?><li><strong><?php echo $current_theme->get( 'Name' ) ?></strong>: <?php echo $current_theme->get( 'Version' ) ?> ( <?php echo $current_theme->get( 'ThemeURI' ) ?> )</li><?php
					}
				?>
				</ul>
				<?php */ ?>
				<?php /* ?>
				<h2><?php _e('Active Plugins', 'learndash' ); ?></h2>
				<?php 
					$all_plugins = get_plugins(); 
					//echo "all_plugins<pre>". print_r($all_plugins, true) ."</pre>";
					if (!empty( $all_plugins ) ) {
						?><ul><?php
						foreach( $all_plugins as $plugin_key => $plugin_data ) { 
							if (is_plugin_active($plugin_key)) {
								?><li><strong><?php echo $plugin_data['Name'] ?></strong>: <?php echo $plugin_data['Version'] ?> ( <?php echo $plugin_data['PluginURI'] ?> )</li><?php
							}
						}
						?></ul><?php
					}
				?>
				<?php */ ?>
				
			</div>
			<?php
		}
	}
}
