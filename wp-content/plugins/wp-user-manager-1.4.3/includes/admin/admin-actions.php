<?php
/**
 * Admin Actions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display links next to title in settings panel
 *
 * @since 1.0.0
 * @return array
*/
function wpum_add_links_to_settings_title() {
	echo '<a href="http://docs.wpusermanager.com" class="add-new-h2" target="_blank">'.__('Documentation', 'wpum').'</a>';
	echo '<a href="http://wpusermanager.com/addons" class="add-new-h2" target="_blank">'.__('Add Ons', 'wpum').'</a>';
}
add_action( 'wpum_next_to_settings_title', 'wpum_add_links_to_settings_title' );

/**
 * Function to display content of the "registration_status" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_registration_status() {

	$output = null;

	if( get_option( 'users_can_register' ) ) {
		$output = '<div class="wpum-admin-message">'.sprintf( __( '<strong>Enabled.</strong> <br/> <small>Registrations can be disabled in <a href="%s" target="_blank">Settings -> General</a>.</small>', 'wpum' ), admin_url( 'options-general.php#users_can_register' ) ).'</div>';
	} else {
		$output = '<div class="wpum-admin-message">'.sprintf( __( 'Registrations are disabled. Enable the "Membership" option in <a href="%s" target="_blank">Settings -> General</a>.', 'wpum' ), admin_url( 'options-general.php#users_can_register' ) ).'</div>';
	}

	echo $output;

}
add_action( 'wpum_registration_status', 'wpum_option_registration_status' );

/**
 * Check on which pages to enable the shortcodes editor.
 *
 * @access public
 * @since  1.0.0
 * @return void
*/
function wpum_shortcodes_add_mce_button() {
	// check user permissions
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	// check if WYSIWYG is enabled
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'wpum_shortcodes_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'wpum_shortcodes_register_mce_button' );
	}
}
add_action( 'admin_head', 'wpum_shortcodes_add_mce_button' );

/**
 * Load tinymce plugin
 *
 * @access public
 * @since  1.0.0
 * @return $plugin_array
*/
function wpum_shortcodes_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['wpum_shortcodes_mce_button'] = apply_filters( 'wpum_shortcodes_tinymce_js_file_url', WPUM_PLUGIN_URL . '/includes/admin/tinymce/js/wpum_shortcodes_tinymce.js' );
	return $plugin_array;
}

/**
 * Load tinymce button
 *
 * @access public
 * @since  1.0.0
 * @return $buttons
*/
function wpum_shortcodes_register_mce_button( $buttons ) {
	array_push( $buttons, 'wpum_shortcodes_mce_button' );
	return $buttons;
}

/**
 * Function to display content of the "registration_role" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_registration_role() {

	$role = get_option( 'default_role' );

	$output = '<span class="wpum-role-option">'.$role.'.</span>';
	$output .= '<br/><small>'.sprintf( __('The default user role for registrations can be changed in <a href="%s">Settings -> General</a>', 'wpum'), admin_url( 'options-general.php#default_role' ) ).'</small>';

	echo $output;

}
add_action( 'wpum_registration_role', 'wpum_option_registration_role' );

/**
 * Processes all WPUM actions sent via POST and GET by looking for the 'wpum-action'
 * request and running do_action() to call the function
 *
 * @since 1.0.0
 * @return void
 */
function wpum_process_actions() {
	if ( isset( $_POST['wpum-action'] ) ) {
		do_action( 'wpum_' . $_POST['wpum-action'], $_POST );
	}

	if ( isset( $_GET['wpum-action'] ) ) {
		do_action( 'wpum_' . $_GET['wpum-action'], $_GET );
	}
}
add_action( 'admin_init', 'wpum_process_actions' );

/**
 * Function to display content of the "restore_emails" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_restore_emails() {

	$output = '<a id="wpum-restore-emails" href="'.esc_url( add_query_arg( array('tool' => 'restore-email') , admin_url( 'users.php?page=wpum-settings&tab=tools' ) ) ).'" class="button">'.__('Restore default emails', 'wpum').'</a>';
	$output .= '<br/><p class="description">' . __('Click the button to restore the default emails content and subject.', 'wpum') . '</p>';
	$output .= wp_nonce_field( "wpum_nonce_email_reset", "wpum_backend_security" );

	echo $output;

}
add_action( 'wpum_restore_emails', 'wpum_option_restore_emails' );

/**
 * Function to display content of the "restore_default_pages" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_restore_pages() {

	$output = '<a id="wpum-restore-pages" href="'.esc_url( add_query_arg( array('tool' => 'restore-pages') , admin_url( 'users.php?page=wpum-settings&tab=tools' ) ) ).'" class="button">'.__('Restore default pages', 'wpum').'</a>';
	$output .= '<br/><p class="description">' . __('Click the button to restore the default core pages of the plugin.', 'wpum') . '</p>';
	$output .= wp_nonce_field( "wpum_nonce_default_pages_restore", "wpum_backend_pages_restore" );

	echo $output;

}
add_action( 'wpum_restore_pages', 'wpum_option_restore_pages' );

/**
 * Function to display content of the "wpum_profile_permalink" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_profile_permalink() {

	$output = '<p>'. sprintf(__('Current profile permalink structure: %s%s', 'wpum'), wpum_get_core_page_url('profile'), get_option( 'wpum_permalink', 'user_id' ) ) . '</p>';
	$output .= '<p class="description">' . sprintf( __('You can change the profiles permalink structure into your <a href="%s">permalink settings page</a>.', 'wpum'), admin_url( 'options-permalink.php' ) ) . '</p>';

	// Display error if something is wrong
	if( !wpum_get_core_page_id( 'profile' ) )
		$output = '<p style="color:red;"><strong>'. __('Your users profile page is not configured.', 'wpum') .'</strong>'. ' ' . sprintf( __('<a href="%s">Setup your profile page here.</a>', 'wpum'), admin_url( 'users.php?page=wpum-settings&tab=general' ) ) .'</p>';

	if( get_option('permalink_structure' ) == '' )
		$output = '<p style="color:red;"><strong>' . sprintf(__( 'You must <a href="%s">change your permalinks</a> to anything else other than "default" for profiles to work.', 'wpum' ), admin_url( 'options-permalink.php' ) ) .'</strong></p>' ;

	echo $output;

}
add_action( 'wpum_profile_permalinks', 'wpum_profile_permalink' );

/**
 * Runs pages setup
 *
 * @since 1.0.0
 * @return void
*/
function wpum_run_pages_setup() {

	if( is_admin() && current_user_can( 'manage_options' ) && isset( $_GET['wpum_action'] ) && $_GET['wpum_action'] == 'install_pages' || is_admin() && current_user_can( 'manage_options' ) && isset( $_GET['tool'] ) && $_GET['tool'] == 'restore-pages' ) :
		wpum_generate_pages( true );
	endif;

}
add_action( 'admin_init', 'wpum_run_pages_setup' );

/**
 * Add new quicktag when editing email.
 *
 * @since 1.0.0
 * @return void
*/
function wpum_new_line_quicktag() {

	$screen = get_current_screen();

	if ( wp_script_is( 'quicktags' ) && $screen->base == 'users_page_wpum-edit-email' ) {
	?>
	<script type="text/javascript">
	QTags.addButton( 'br', "<?php _e('Add New Line', 'wpum');?>", '<br/>', '', 's', "<?php _e('Add New Line', 'wpum');?>", 1 );
	</script>
	<?php
	}

}
add_action( 'admin_print_footer_scripts', 'wpum_new_line_quicktag' );

/**
 * Check if plugin has been installed on this site for more than 14 days.
 *
 * @since 1.0.0
 * @return void
*/
function wpum_check_installation_date() {

	$install_date = get_option( 'wpum_activation_date' );
  $past_date = strtotime( '-14 days' );

 	// Delete the notice
  if( isset( $_GET['hide_rating_notice'] ) && $_GET['hide_rating_notice'] == 1 ) {
  	delete_option( 'wpum_activation_date' );
  	wp_redirect( admin_url() );
  	exit();
  }

  // Display the notice
  if ( $install_date && $past_date >= $install_date ) {
      add_action( 'admin_notices', 'wpum_display_rating_notice' );
  }

}
add_action( 'admin_init', 'wpum_check_installation_date' );

/**
 * Display rating notice.
 *
 * @since 1.0.0
 * @return void
*/
function wpum_display_rating_notice() {

	$url_rate = 'http://wordpress.org/support/view/plugin-reviews/wp-user-manager?filter=5#postform';
	$remove_url = add_query_arg( array( 'hide_rating_notice' => true ), admin_url() );

  ?>
  <div class="updated">
      <p><?php echo sprintf( __( "Hey, looks like you've been using the <b>WP User Manager</b> plugin for some time now - that's awesome! <br/> Could you please give it a review on wordpress.org ? Just to help us spread the word and boost our motivation :) <br/> <br/><a href='%s' class='button button-primary' target='_blank'>Yes, you deserve it!</a> - <a href='%s'>I've already done this!</a>", 'wpum' ), $url_rate, esc_url( $remove_url ) ); ?></p>
  </div>
  <?php

}

/**
 * Fix the broken fields into the previous update.
 *
 * @since 1.2.6
 * @return void
*/
function wpum_run_fields_fix() {

	if( is_admin() && current_user_can( 'manage_options' ) && isset( $_GET['tool'] ) && $_GET['tool'] == 'restore-fields' ) :

		$primary_group = WPUM()->field_groups->get_group_by('primary');

		$args = array(
			'id'           => $primary_group->id,
			'array'        => true,
			'number'       => -1,
			'orderby'      => 'field_order',
			'order'        => 'ASC'
		);

		$data = WPUM()->fields->get_by_group( $args );

		foreach ( $data as $key => $field ) {

			if( $field['meta'] == 'username' || $field['meta'] == 'user_email' || $field['meta'] == 'password' || $field['meta'] == 'password' ) {

				$field_args = array(
					'show_on_registration' => true,
					'is_required' => true
				);

				WPUM()->fields->update( $field['id'], $field_args );

			}

		}

		$url = add_query_arg( array( 'message' => 'fields_fixed' ), admin_url( 'users.php?page=wpum-settings&tab=tools' ) );
		wp_redirect( $url );
  	exit();

	endif;

}
add_action( 'admin_init', 'wpum_run_fields_fix' );

/**
 * Display button to trigger the test email.
 *
 * @since 1.4.0
 * @return void
 */
function wpum_send_test_mail_button() {

	$trigger_test_mail_url = wp_nonce_url( add_query_arg( array( 'tool' => 'send_test_mail' ), admin_url( 'users.php?page=wpum-settings&tab=emails' ) ), 'tool', 'send_test_mail_nonce' );

	?>

	<a href="<?php echo esc_url( $trigger_test_mail_url ); ?>" class="button"><?php esc_html_e( 'Send test email', 'wpum' ); ?></a>
	<p><?php echo sprintf( esc_html__( 'Test email will be sent to %s', 'wpum' ), get_option( 'admin_email' ) ); ?></p>

	<?php

}
add_action( 'wpum_trigger_test_email', 'wpum_send_test_mail_button' );

/**
 * Send a test email to the site's admin email.
 *
 * @since 1.4.0
 * @return void
 */
function wpum_send_test_mail() {

	if( isset( $_GET['tool'] ) && $_GET['tool'] == 'send_test_mail' && current_user_can( 'manage_options' ) ) {

		if ( ! wp_verify_nonce( $_GET['send_test_mail_nonce'], 'tool' ) ) {
			return;
		}

		// Details of the site.
		$sitename    = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$admin_email = get_option( 'admin_email' );

		// Details of the message.
		$subject = sprintf( esc_html( 'Test email from: %s' ), $sitename );
		$message = esc_html( 'The following is a simple test email to verify that emails are correctly being delivered from your website.' );

		WPUM()->emails->__set( 'heading', esc_html__( 'Test Email', 'wpum' ) );
		WPUM()->emails->send( $admin_email, $subject, $message );

		$redirect_url = add_query_arg( array( 'message' => 'test_mail' ), admin_url( 'users.php?page=wpum-settings&tab=emails' ) );

		wp_redirect( $redirect_url );
		exit;

	}

}
add_action( 'admin_init', 'wpum_send_test_mail' );
