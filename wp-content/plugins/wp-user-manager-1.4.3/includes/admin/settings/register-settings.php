<?php
/**
 * Register Settings
 *
 * @package     wp-user-manager
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get an option
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @return mixed
 */
function wpum_get_option( $key = '', $default = false ) {
	global $wpum_options;
	$value = ! empty( $wpum_options[ $key ] ) ? $wpum_options[ $key ] : $default;
	$value = apply_filters( 'wpum_get_option', $value, $key, $default );
	return apply_filters( 'wpum_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an wpum setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the wpum_options array.
 *
 * @since 1.0.0
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @return boolean True if updated, false if not.
 * @copyright Copyright (c) 2015, Pippin Williamson
 */
function wpum_update_option( $key = '', $value = false ) {
	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}
	if ( empty( $value ) ) {
		$remove_option = wpum_delete_option( $key );
		return $remove_option;
	}
	// First let's grab the current settings
	$options = get_option( 'wpum_settings' );
	// Let's let devs alter that value coming in
	$value = apply_filters( 'wpum_update_option', $value, $key );
	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update = update_option( 'wpum_settings', $options );
	// If it updated, let's update the global variable
	if ( $did_update ){
		global $wpum_options;
		$wpum_options[ $key ] = $value;
	}
	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an wpum setting value in both the db and the global variable.
 *
 * @since 1.0.0
 * @param string $key The Key to delete
 * @return boolean True if updated, false if not.
 */
function wpum_delete_option( $key = '' ) {
	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}
	// First let's grab the current settings
	$options = get_option( 'wpum_settings' );
	// Next let's try to update the value
	if( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}
	$did_update = update_option( 'wpum_settings', $options );
	// If it updated, let's update the global variable
	if ( $did_update ){
		global $wpum_options;
		$wpum_options = $options;
	}
	return $did_update;
}

/**
 * Get Settings
 * Retrieves all plugin settings
 *
 * @since 1.0.0
 * @return array WPUM settings
 */
function wpum_get_settings() {

	$settings = get_option( 'wpum_settings' );
	return apply_filters( 'wpum_get_settings', $settings );

}

/**
 * Add all settings sections and fields
 *
 * @since 1.0.0
 * @return void
*/
function wpum_register_settings() {

	if ( false == get_option( 'wpum_settings' ) ) {
		add_option( 'wpum_settings' );
	}

	foreach( wpum_get_registered_settings() as $tab => $settings ) {

		add_settings_section(
			'wpum_settings_' . $tab,
			__return_null(),
			'__return_false',
			'wpum_settings_' . $tab
		);

		foreach ( $settings as $option ) {

			$name = isset( $option['name'] ) ? $option['name'] : '';

			add_settings_field(
				'wpum_settings[' . $option['id'] . ']',
				$name,
				function_exists( 'wpum_' . $option['type'] . '_callback' ) ? 'wpum_' . $option['type'] . '_callback' : 'wpum_missing_callback',
				'wpum_settings_' . $tab,
				'wpum_settings_' . $tab,
				array(
					'section'     => $tab,
					'id'          => isset( $option['id'] ) ? $option['id']      : null,
					'desc'        => ! empty( $option['desc'] ) ? $option['desc']    : '',
					'class'       => ! empty( $option['class'] ) ? $option['class']    : '',
					'name'        => isset( $option['name'] ) ? $option['name']    : null,
					'size'        => isset( $option['size'] ) ? $option['size']    : null,
					'options'     => isset( $option['options'] ) ? $option['options'] : '',
					'std'         => isset( $option['std'] ) ? $option['std']     : '',
					'min'         => isset( $option['min'] ) ? $option['min']     : null,
					'max'         => isset( $option['max'] ) ? $option['max']     : null,
					'step'        => isset( $option['step'] ) ? $option['step']    : null,
					'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder']     : ''
				)
			);
		}

	}

	// Creates our settings in the options table
	register_setting( 'wpum_settings', 'wpum_settings', 'wpum_settings_sanitize' );

}
add_action('admin_init', 'wpum_register_settings');

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.0.0
 * @return array
*/
function wpum_get_registered_settings() {

	/**
	 * 'Whitelisted' WPUM settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$wpum_settings = array(
		/** General Settings */
		'general' => apply_filters( 'wpum_settings_general',
			array(
				'header1' => array(
					'id'   => 'header1',
					'name' => __( 'Pages Setup', 'wpum' ),
					'type' => 'header'
				),
				'login_page' => array(
					'id'      => 'login_page',
					'name'    => __( 'Login page:', 'wpum' ),
					'desc'    => __('Select the page where you have added the login shortcode.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'password_recovery_page' => array(
					'id'      => 'password_recovery_page',
					'name'    => __( 'Password recovery page:', 'wpum' ),
					'desc'    => __('Select the page where you have added the password recovery shortcode.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'registration_page' => array(
					'id'      => 'registration_page',
					'name'    => __( 'Registration page:', 'wpum' ),
					'desc'    => __('Select the page where you have added the registration shortcode.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'account_page' => array(
					'id'      => 'account_page',
					'name'    => __( 'Account page:', 'wpum' ),
					'desc'    => __('Select the page where you have added the account shortcode.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'profile_page' => array(
					'id'      => 'profile_page',
					'name'    => __( 'Profile page:', 'wpum' ),
					'desc'    => __('Select the page where you have added the profile shortcode.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'header3' => array(
					'id'   => 'header3',
					'name' => __( 'Passwords Setup', 'wpum' ),
					'type' => 'header'
				),
				'password_strength' => array(
					'id'      => 'password_strength',
					'name'    => __( 'Minimum Password Strength:', 'wpum' ),
					'desc'    => __('Select the required password strength. This will be forced on registration and account page.', 'wpum'),
					'type'    => 'select',
					'options' => wpum_get_psw_lengths()
				),
				'header_n' => array(
					'id'   => 'header_n',
					'name' => __( 'Extra', 'wpum' ),
					'type' => 'header'
				),
				'login_method' => array(
					'id'      => 'login_method',
					'name'    => __( 'Allow users to login with:', 'wpum' ),
					'type'    => 'select',
					'options' => wpum_get_login_methods()
				),
				'adminbar_roles' => array(
					'id'          => 'adminbar_roles',
					'name'        => __( 'Admin Bar:', 'wpum' ),
					'desc'        => __('Hide admin bar for specific user roles.', 'wpum'),
					'type'        => 'multiselect',
					'placeholder' => __('Select the user roles from the list.', 'wpum'),
					'class'       => 'select2_multiselect',
					'options'     => wpum_get_roles()
				),
			)
		),
		'registration' => apply_filters( 'wpum_settings_registration',
			array(
				'registration_status' => array(
					'id'   => 'registration_status',
					'name' => __( 'Registrations Status:', 'wpum' ),
					'type' => 'hook'
				),
				'registration_role' => array(
					'id'   => 'registration_role',
					'name' => __( 'Default user registration role:', 'wpum' ),
					'type' => 'hook'
				),
				'custom_passwords' => array(
					'id'   => 'custom_passwords',
					'name' => __( 'Users custom passwords:', 'wpum' ),
					'desc' => __('Enable to allow users to set custom passwords on the registration page.', 'wpum'),
					'type' => 'checkbox'
				),
				'allow_role_select' => array(
					'id'   => 'allow_role_select',
					'name' => __( 'Allow role section:', 'wpum' ),
					'desc' => __('Enable to allow users to select a user role on registration.', 'wpum'),
					'type' => 'checkbox'
				),
				'register_roles' => array(
					'id'          => 'register_roles',
					'name'        => __( 'Allowed Roles:', 'wpum' ),
					'desc'        => __('Select which roles can be selected upon registration.', 'wpum'),
					'type'        => 'multiselect',
					'placeholder' => __('Select the user roles from the list.', 'wpum'),
					'class'       => 'select2_multiselect',
					'options'     => wpum_get_roles()
				),
				'header4' => array(
					'id'   => 'header4',
					'name' => __( 'Terms &amp; Conditions', 'wpum' ),
					'type' => 'header'
				),
				'enable_terms' => array(
					'id'   => 'enable_terms',
					'name' => __( 'Enable terms &amp conditions:', 'wpum' ),
					'desc' => __('Enable to force users to agree to your terms before registering an account.', 'wpum'),
					'type' => 'checkbox'
				),
				'terms_page' => array(
					'id'      => 'terms_page',
					'name'    => __( 'Terms Page:', 'wpum' ),
					'desc'    => __('Select the page that contains your terms.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'header5' => array(
					'id'   => 'header5',
					'name' => __( 'Extra', 'wpum' ),
					'type' => 'header'
				),
				'enable_honeypot' => array(
					'id'   => 'enable_honeypot',
					'name' => __( 'Anti-spam Honeypot:', 'wpum' ),
					'desc' => __('Enables honeypot spam protection technique.', 'wpum'),
					'type' => 'checkbox'
				),
				'login_after_registration' => array(
					'id'   => 'login_after_registration',
					'name' => __( 'Login after registration:', 'wpum' ),
					'desc' => __('Enable this option to authenticate users after registration.', 'wpum'),
					'type' => 'checkbox'
				),
			)
		),
		'emails' => apply_filters( 'wpum_settings_emails',
			array(
				'from_name' => array(
					'id'   => 'from_name',
					'name' => __( 'From Name:', 'wpum' ),
					'desc' => __( 'The name emails are said to come from. This should probably be your site name.', 'wpum' ),
					'type' => 'text',
					'std'  => get_option( 'blogname' )
				),
				'from_email' => array(
					'id'   => 'from_email',
					'name' => __( 'From Email:', 'wpum' ),
					'desc' => __( 'This will act as the "from" and "reply-to" address.', 'wpum' ),
					'type' => 'text',
					'std'  => get_option( 'admin_email' )
				),
				'email_template' => array(
					'id'      => 'email_template',
					'name'    => __( 'Email Template', 'wpum' ),
					'desc'    => __( 'Choose a template.', 'wpum' ),
					'type'    => 'select',
					'options' => wpum_get_email_templates()
				),
				'email_logo' => array(
					'id'   => 'email_logo',
					'name' => __( 'Logo', 'wpum' ),
					'desc' => __( 'Upload or choose a logo to be displayed at the top of emails. Displayed on HTML emails only.', 'wpum' ),
					'type' => 'upload'
				),
				'emails_editor' => array(
					'id'   => 'emails_editor',
					'name' => __( 'Emails Editor:', 'wpum' ),
					'type' => 'hook'
				),
				'header6' => array(
					'id'   => 'header6',
					'name' => __( 'Notifications Settings', 'wpum' ),
					'type' => 'header'
				),
				'trigger_test_email' => array(
					'id'   => 'trigger_test_email',
					'name' => __( 'Send test email:', 'wpum' ),
					'type' => 'hook'
				),
				'disable_admin_register_email' => array(
					'id'   => 'disable_admin_register_email',
					'name' => __( 'Disable admin registration email:', 'wpum' ),
					'desc' => __( 'Enable this option to stop receiving notifications when a new user registers.', 'wpum' ),
					'type' => 'checkbox'
				),
				'disable_admin_password_recovery_email' => array(
					'id'   => 'disable_admin_password_recovery_email',
					'name' => __( 'Disable admin password recovery email:', 'wpum' ),
					'desc' => __( 'Enable this option to stop receiving notifications when a new user resets his password.', 'wpum' ),
					'type' => 'checkbox'
				),
			)
		),
		'profile' => apply_filters( 'wpum_settings_profile',
			array(
				'profile_permalinks' => array(
					'id'   => 'profile_permalinks',
					'name' => __( 'Profile permalink:', 'wpum' ),
					'type' => 'hook'
				),
				'guests_can_view_profiles' => array(
					'id'   => 'guests_can_view_profiles',
					'name' => __( 'Allow guests to view profiles', 'wpum' ),
					'desc' => __( 'Enable this option to allow guests to view users profiles.', 'wpum' ),
					'type' => 'checkbox'
				),
				'members_can_view_profiles' => array(
					'id'   => 'members_can_view_profiles',
					'name' => __( 'Allow members to view profiles', 'wpum' ),
					'desc' => __( 'Enable this option to allow members to view users profiles. If disabled, users can only see their own profile.', 'wpum' ),
					'type' => 'checkbox'
				),
				'custom_avatars' => array(
					'id'   => 'custom_avatars',
					'name' => __( 'Custom Avatars', 'wpum' ),
					'desc' => __( 'Enable this option to allow users to upload custom avatars for their profiles.', 'wpum' ) . wpum_check_permissions_button(),
					'type' => 'checkbox'
				),
				'profile_posts' => array(
					'id'   => 'profile_posts',
					'name' => __( 'Display posts', 'wpum' ),
					'desc' => __( 'Enable this option to display users submitted post on their profile page.', 'wpum' ),
					'type' => 'checkbox'
				),
				'profile_comments' => array(
					'id'   => 'profile_comments',
					'name' => __( 'Display comments', 'wpum' ),
					'desc' => __( 'Enable this option to display users submitted comments on their profile page.', 'wpum' ),
					'type' => 'checkbox'
				),
			)
		),
		'redirects' => apply_filters( 'wpum_settings_redirects',
			array(
				'login_redirect' => array(
					'id'      => 'login_redirect',
					'name'    => __( 'Login', 'wpum' ),
					'desc'    => __('Select the page where you want to redirect users after they login.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'logout_redirect' => array(
					'id'      => 'logout_redirect',
					'name'    => __( 'Logout', 'wpum' ),
					'desc'    => __('Select the page where you want to redirect users after they logout. If empty will return to wp-login.php', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'registration_redirect' => array(
					'id'      => 'registration_redirect',
					'name'    => __( 'Registration Redirect', 'wpum' ),
					'desc'    => __('Select the page where you want to redirect users after they successfully register. If empty a message will be displayed instead.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'wp_login_signup_redirect' => array(
					'id'      => 'wp_login_signup_redirect',
					'name'    => __( 'Backend register', 'wpum' ),
					'desc'    => sprintf(__('Select a page if you wish to redirect users who try to signup through <a href="%s">the default registration page on wp-login.php</a>', 'wpum'), site_url( 'wp-login.php?action=register' ) ),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'wp_login_password_redirect' => array(
					'id'      => 'wp_login_password_redirect',
					'name'    => __( 'Backend lost password', 'wpum' ),
					'desc'    => sprintf(__('Select a page if you wish to redirect users who try to recover a lost password through <a href="%s">the default password recovery page on wp-login.php</a>', 'wpum'), site_url( 'wp-login.php?action=lostpassword' ) ),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
				'backend_profile_redirect' => array(
					'id'      => 'backend_profile_redirect',
					'name'    => __( 'Backend profile', 'wpum' ),
					'desc'    => __('Select the page where you want to redirect users who try to access their profile on the backend.', 'wpum'),
					'type'    => 'select',
					'class'   => 'select2',
					'options' => wpum_get_pages()
				),
			)
		),
		/** Extension Settings */
		'extensions' => apply_filters('wpum_settings_extensions',
			array()
		),
		'licenses' => apply_filters('wpum_settings_licenses',
			array()
		),
		'tools' => apply_filters( 'wpum_settings_tools',
			array(
				'restore_emails' => array(
					'id'   => 'restore_emails',
					'name' => __( 'Restore default emails:', 'wpum' ),
					'type' => 'hook'
				),
				'restore_pages' => array(
					'id'   => 'restore_pages',
					'name' => __( 'Restore Pages:', 'wpum' ),
					'type' => 'hook'
				),
				'exclude_usernames' => array(
					'id'   => 'exclude_usernames',
					'name' => __( 'Excluded usernames:', 'wpum' ),
					'desc' => '<br/>'.__('Enter the usernames that you wish to disable. Separate each username on a new line.', 'wpum'),
					'type' => 'textarea'
				),
			)
		),
	);

	return apply_filters( 'wpum_registered_settings', $wpum_settings );
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function wpum_settings_sanitize( $input = array() ) {

	global $wpum_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = wpum_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();
	$input = apply_filters( 'wpum_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[$key] = apply_filters( 'wpum_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[$key] = apply_filters( 'wpum_settings_sanitize', $input[$key], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	if ( ! empty( $settings[$tab] ) ) {
		foreach ( $settings[$tab] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[$key] ) ) {
				unset( $wpum_options[$key] );
			}

		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $wpum_options, $input );

	add_settings_error( 'wpum-notices', '', __( 'Settings successfully updated.', 'wpum' ), 'updated' );

	return $output;
}

/**
 * Sanitize text fields
 *
 * @since 1.0.0
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function wpum_sanitize_text_field( $input ) {
	return trim( $input );
}
add_filter( 'wpum_settings_sanitize_text', 'wpum_sanitize_text_field' );

/**
 * Retrieve settings tabs
 *
 * @since 1.0.0
 * @return array $tabs
 */
function wpum_get_settings_tabs() {

	$settings = wpum_get_registered_settings();

	$tabs                   = array();
	$tabs['general']        = __( 'General', 'wpum' );
	$tabs['registration']   = __( 'Registration', 'wpum' );
	$tabs['emails']         = __( 'Emails', 'wpum' );
	$tabs['profile']        = __( 'Profiles', 'wpum' );
	$tabs['redirects']      = __( 'Redirects', 'wpum' );

	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'wpum' );
	}

	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'wpum' );
	}

	$tabs['tools']          = __( 'Tools', 'wpum' );

	return apply_filters( 'wpum_settings_tabs', $tabs );
}

/**
 * Header Callback
 * Renders the header.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function wpum_header_callback( $args ) {
	echo '<hr/>';
}

/**
 * Checkbox Callback
 * Renders checkboxes.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_checkbox_callback( $args ) {
	global $wpum_options;

	$checked = isset( $wpum_options[ $args[ 'id' ] ] ) ? checked( 1, $wpum_options[ $args[ 'id' ] ], false ) : '';
	$html = '<input type="checkbox" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Multicheck Callback
 * Renders multiple checkboxes.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_multicheck_callback( $args ) {
	global $wpum_options;

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
			if( isset( $wpum_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="wpum_settings[' . $args['id'] . '][' . $key . ']" id="wpum_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			echo '<label for="wpum_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		endforeach;
		echo '<p class="description">' . $args['desc'] . '</p>';
	}
}

/**
 * Radio Callback
 * Renders radio boxes.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_radio_callback( $args ) {
	global $wpum_options;

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $wpum_options[ $args['id'] ] ) && $wpum_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $wpum_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="wpum_settings[' . $args['id'] . ']"" id="wpum_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="wpum_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/><div class="radio-spacer"></div>';
	endforeach;

	echo '<p class="description">' . $args['desc'] . '</p>';
}

/**
 * Text Callback
 * Renders text fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_text_callback( $args ) {
	global $wpum_options;

	if ( isset( $wpum_options[ $args['id'] ] ) )
		$value = $wpum_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_number_callback( $args ) {
	global $wpum_options;

    if ( isset( $wpum_options[ $args['id'] ] ) )
		$value = $wpum_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Textarea Callback
 * Renders textarea fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_textarea_callback( $args ) {
	global $wpum_options;

	if ( isset( $wpum_options[ $args['id'] ] ) )
		$value = $wpum_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<textarea class="large-text" cols="50" rows="5" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Missing Callback
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function wpum_missing_callback($args) {
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'wpum' ), $args['id'] );
}

/**
 * Select Callback
 * Renders select fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_select_callback($args) {
	global $wpum_options;

	if ( isset( $wpum_options[ $args['id'] ] ) )
		$value = $wpum_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$class = isset( $args['class'] ) ? $args['class'] : '';

	$html = '<select id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']" class="'.$class.'" />';

	foreach ( $args['options'] as $option => $name ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Multicheck Callback
 * Renders multiple checkboxes.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_multiselect_callback( $args ) {
	global $wpum_options;

	if ( ! empty( $args['options'] ) ) {

		$class = isset( $args['class'] ) ? $args['class'] : '';

		$html =  '<select id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . '][]" class="'.$class.'" multiple="multiple" data-placeholder="'.$args['placeholder'].'"/>';

		if ( isset( $wpum_options[ $args['id'] ] ) )
			$value = $wpum_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		foreach ( $args['options'] as $option => $name ) :
			$selected = selected( in_array( $option, (array) $value ), true, false );
			$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<br/><br/><label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;

	}
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_color_select_callback( $args ) {
	global $wpum_options;

	if ( isset( $wpum_options[ $args['id'] ] ) )
		$value = $wpum_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @global $wp_version WordPress Version
 */
function wpum_rich_editor_callback( $args ) {
	global $wpum_options, $wp_version;

	if ( isset( $wpum_options[ $args['id'] ] ) ) {
		$value = $wpum_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor( stripslashes( $value ), 'wpum_settings_' . $args['id'], array( 'textarea_name' => 'wpum_settings[' . $args['id'] . ']', 'textarea_rows' => $rows ) );
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text" rows="10" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the WPUM Options
 * @return void
 */
function wpum_color_callback( $args ) {
	global $wpum_options;

	if ( isset( $wpum_options[ $args['id'] ] ) )
		$value = $wpum_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="wpum-color-picker" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Upload Callback.
 *
 * @since 1.1.0
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the Options
 * @return void
 */
function wpum_upload_callback( $args ) {

	global $wpum_options;

	if ( isset( $wpum_options[ $args['id'] ] ) ) {
	  $value = $wpum_options[$args['id']];
	} else {
	  $value = isset($args['std']) ? $args['std'] : '';
	}
	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="wpum_settings_upload_button button-secondary" value="' . __( 'Upload File', 'wpum' ) . '"/></span>';
	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	echo $html;

}

/**
 * Renders license field.
 *
 * @param array $args Arguments passed by the setting
 * @global $wpum_options Array of all the Options
 */
function wpum_license_key_callback( $args ) {

	global $wpum_options;

	if ( isset( $wpum_options[ $args['id'] ] ) ) {
		$value = $wpum_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="wpum_settings[' . $args['id'] . ']" name="wpum_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

	if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
		$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License', 'wpum' ) . '"/>';
	}

	$html .= '<label for="wpum_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );
	echo $html;

}


/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function wpum_descriptive_text_callback( $args ) {
	echo esc_html( $args['desc'] );
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function wpum_hook_callback( $args ) {
	do_action( 'wpum_' . $args['id'] );
}

/**
 * Set manage_shop_settings as the cap required to save WPUM settings pages
 *
 * @since 1.0.0
 * @return string capability required
 */
function wpum_set_settings_cap() {
	return 'manage_options';
}
add_filter( 'option_page_capability_wpum_settings', 'wpum_set_settings_cap' );
