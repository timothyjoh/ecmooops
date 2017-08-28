<?php
/**
 * Misc Functions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.0.0
 * @param bool    $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function wpum_get_pages( $force = false ) {

	$pages_options = array( 0 => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'wpum-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
}

/**
 * Retrieve a list of all user roles
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.0.0
 * @param bool    $force Force the roles to be loaded even if not on settings
 * @return array $roles An array of the roles
 */
function wpum_get_roles( $force = false ) {

	$roles_options = array( 0 => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'wpum-settings' != $_GET['page'] ) && ! $force ) {
		return $roles_options;
	}

	global $wp_roles;

	$roles = $wp_roles->get_names();

	return apply_filters( 'wpum_get_roles', $roles );
}

/**
 * Retrieve a list of allowed users role on the registration page
 *
 * @since 1.0.0
 * @return array $roles An array of the roles
 */
function wpum_get_allowed_user_roles() {

	global $wp_roles;

	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	$user_roles         = array();
	$selected_roles     = wpum_get_option( 'register_roles' );
	$allowed_user_roles = is_array( $selected_roles ) ? $selected_roles: array( $selected_roles );

	foreach ( $allowed_user_roles as $role ) {
		$user_roles[ $role ] = $wp_roles->roles[ $role ]['name'];
	}

	return $user_roles;

}

/**
 * Retrieve a list of disabled usernames
 *
 * @since 1.0.0
 * @return array $usernames An array of the usernames
 */
function wpum_get_disabled_usernames() {

	$usernames = array();

	if ( wpum_get_option( 'exclude_usernames' ) ) {

		$list = trim( wpum_get_option( 'exclude_usernames' ) );
		$list = explode( "\n", str_replace( "\r", "", $list ) );

		foreach ( $list as $username ) {
			$usernames[] = $username;
		}

	}

	return array_flip( $usernames );

}

/**
 * Get a list of available permalink structures.
 *
 * @since 1.0.0
 * @return array of all the structures.
 */
function wpum_get_permalink_structures() {

	$structures = array(
		'user_id' => array(
			'name'   => 'user_id',
			'label'  => _x( 'Display user ID', 'Permalink structure', 'wpum' ),
			'sample' => '123'
		),
		'username' => array(
			'name'   => 'username',
			'label'  => _x( 'Display username', 'Permalink structure', 'wpum' ),
			'sample' => _x( 'username', 'Example of permalink setting', 'wpum' )
		),
		'nickname' => array(
			'name'   => 'nickname',
			'label'  => _x( 'Display nickname', 'Permalink structure', 'wpum' ),
			'sample' => _x( 'nickname', 'Example of permalink setting', 'wpum' )
		),
	);

	return apply_filters( 'wpum_get_permalink_structures', $structures );
}

/**
 * Get ID of a core page.
 *
 * @since 1.0.0
 * @param string  $name the name of the page. Supports: login, register, password, account, profile.
 * @return int $id of the core page.
 */
function wpum_get_core_page_id( $page ) {

	$id = null;

	switch ( $page ) {
		case 'login':
			$id = wpum_get_option( 'login_page' );
			break;
		case 'register':
			$id = wpum_get_option( 'registration_page' );
			break;
		case 'password':
			$id = wpum_get_option( 'password_recovery_page' );
			break;
		case 'account':
			$id = wpum_get_option( 'account_page' );
			break;
		case 'profile':
			$id = wpum_get_option( 'profile_page' );
			break;
	}

	return $id;
}

/**
 * Get URL of a core page.
 *
 * @since 1.0.0
 * @param string  $name the name of the page. Supports: login, register, password, account, profile.
 * @return string $url of the core page.
 */
function wpum_get_core_page_url( $page ) {

	$url = null;

	switch ( $page ) {
		case 'login':
			$url = esc_url( get_permalink( wpum_get_core_page_id( 'login' ) ) );
			break;
		case 'register':
			$url = esc_url( get_permalink( wpum_get_core_page_id( 'register' ) ) );
			break;
		case 'password':
			$url = esc_url( get_permalink( wpum_get_core_page_id( 'password' ) ) );
			break;
		case 'account':
			$url = esc_url( get_permalink( wpum_get_core_page_id( 'account' ) ) );
			break;
		case 'profile':
			$url = esc_url( get_permalink( wpum_get_core_page_id( 'profile' ) ) );
			break;
	}

	return apply_filters( 'wpum_get_core_page_url', $url, $page );
}

/**
 * Display a message loading the message.php template file.
 *
 * @since 1.0.0
 * @param string  $id   html ID attribute.
 * @param string  $type message type: success/notice/error.
 * @param string  $text the text of the message.
 * @return void
 */
function wpum_message( $args ) {

	$defaults = array(
		'id'   => 'wpum-notice', // html ID attribute
		'type' => 'success', // message type: success/notice/error.
		'text' => '' // the text of the message.
	);

	// Parse incoming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	echo get_wpum_template( 'message.php', array(
			'id'   => $args['id'],
			'type' => $args['type'],
			'text' => $args['text']
		)
	);

}

/**
 * Gets a list of users orderded by most recent registration date.
 *
 * @since 1.0.0
 * @param int     $amount amount of users to load.
 * @return void
 */
function wpum_get_recent_users( $amount ) {

	$args = array(
		'number'  => $amount,
		'order'   => 'DESC',
		'orderby' => 'registered'
	);

	// The Query
	$user_query = new WP_User_Query( apply_filters( 'wpum_get_recent_users', $args ) );

	// Get the results
	$users = $user_query->get_results();

	return $users;
}

/**
 * Check if a given nickname already exists.
 *
 * @since 1.0.0
 * @param string  $nickname
 * @return bool
 */
function wpum_nickname_exists( $nickname ) {

	$exists = false;

	$args = array(
		'fields'         => 'user_nicename',
		'search'         => $nickname,
		'search_columns' => array( 'user_nicename' )
	);

	// The Query
	$user_query = new WP_User_Query( $args );

	// Get the results
	$users = $user_query->get_results();

	if ( !empty( $users ) )
		$exists = true;

	return $exists;

}

/**
 * Force 404 error headers.
 *
 * @since 1.0.0
 * @return void
 */
function wpum_trigger_404() {

	global $wp_query;

	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();

}

/**
 * Given $user_data checks against $method_type if the user exists.
 *
 * @since 1.0.0
 * @param string  $user_data   Either ID/Username/Nickname
 * @param string  $method_type Either user_id/username/nickname - usually retrieve thorugh get_option('wpum_permalink')
 * @return bool
 */
function wpum_user_exists( $user_data, $method_type ) {

	$exists = false;

	// Check if user exists by ID
	if ( !empty( $user_data ) && $method_type == 'user_id' && get_user_by( 'id', intval( $user_data ) ) ) {
		$exists = true;
	}

	// Check if user exists by username
	if ( !empty( $user_data ) && $method_type == 'username' && get_user_by( 'login', esc_attr( urldecode( $user_data ) ) ) ) {
		$exists = true;
	}

	// Check if user exists by nickname
	if ( !empty( $user_data ) && $method_type == 'nickname' && wpum_nickname_exists( $user_data ) ) {
		$exists = true;
	}

	return $exists;

}

/**
 * Triggers the mechanism to upload files.
 *
 * @copyright mikejolley
 * @since 1.0.0
 * @param array   $file_data Array of $_FILE data to upload.
 * @return array|WP_Error Array of objects containing either file information or an error
 */
function wpum_trigger_upload_file( $field_key, $field ) {

	if ( isset( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ]['name'] ) ) {

		if( $field_key == 'user_avatar' ) {
			add_filter( 'upload_mimes' , 'wpum_adjust_mime_types' );
		}

		$allowed_mime_types = get_allowed_mime_types();

		$file_urls       = array();
		$files_to_upload = wpum_prepare_uploaded_files( $_FILES[ $field_key ] );

		foreach ( $files_to_upload as $file_key => $file_to_upload ) {

			// Trigger validation rules for avatar only.
			if( $field_key == 'user_avatar' ) {

				if ( !in_array( $file_to_upload['type'] , $allowed_mime_types ) )
					return new WP_Error( 'validation-error', sprintf( __( 'Allowed files types are: %s', 'wpum' ), implode( ', ', array_keys( $allowed_mime_types ) ) ) );

				if ( defined( 'WPUM_MAX_AVATAR_SIZE' ) && $field_key == 'user_avatar' && $file_to_upload['size'] > WPUM_MAX_AVATAR_SIZE )
					return new WP_Error( 'avatar-too-big', __( 'The uploaded file is too big.', 'wpum' ) );

			} else {

				// Trigger verification for other file fields.
				if( array_key_exists( 'allowed_extensions' , $field ) && is_array( $field['allowed_extensions'] ) ) {

					$allowed_field_extensions = $field['allowed_extensions'];
					$uploaded_file_extension  = pathinfo( $file_to_upload['name'] );
					$uploaded_file_extension  = $uploaded_file_extension['extension'];

					if( ! in_array( $uploaded_file_extension , $allowed_field_extensions ) ) {
						return new WP_Error( 'validation-error', sprintf( esc_html__( 'Error: the "%s" field allows only %s files to be uploaded.', 'wpum' ), $field['label'], implode ( ", ", $allowed_field_extensions ) ) );
					}

				}

				if( array_key_exists( 'max_file_size' , $field ) && $file_to_upload['size'] > $field['max_file_size'] ) {
					return new WP_Error( 'file-too-big', sprintf( esc_html__('The uploaded file for "%s" is too big.', 'wpum' ), $field['label'] ) );
				}

			}

			$uploaded_file = wpum_upload_file( $file_to_upload, array( 'file_key' => $file_key ) );

			if ( is_wp_error( $uploaded_file ) ) {

				return new WP_Error( 'validation-error', $uploaded_file->get_error_message() );

			} else {

				$file_urls[] = array(
					'url'  => $uploaded_file->url,
					'path' => $uploaded_file->path,
					'size' => $uploaded_file->size
				);

			}

		}

		if ( ! empty( $field['multiple'] ) ) {
			return $file_urls;
		} else {
			return current( $file_urls );
		}

		if( $field_key == 'user_avatar' ) {
			remove_filter( 'upload_mimes' , 'wpum_adjust_mime_types' );
		}

		return $files_to_upload;
	}

}

/**
 * Prepare the files to upload.
 *
 * @copyright mikejolley
 * @since 1.0.0
 * @param array   $file_data Array of $_FILE data to upload.
 * @return array|WP_Error Array of objects containing either file information or an error
 */
function wpum_prepare_uploaded_files( $file_data ) {
	$files_to_upload = array();

	if ( is_array( $file_data['name'] ) ) {
		foreach ( $file_data['name'] as $file_data_key => $file_data_value ) {

			if ( $file_data['name'][ $file_data_key ] ) {
				$files_to_upload[] = array(
					'name'     => $file_data['name'][ $file_data_key ],
					'type'     => $file_data['type'][ $file_data_key ],
					'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
					'error'    => $file_data['error'][ $file_data_key ],
					'size'     => $file_data['size'][ $file_data_key ]
				);
			}
		}
	} else {
		$files_to_upload[] = $file_data;
	}

	return $files_to_upload;
}

/**
 * Upload a file using WordPress file API.
 *
 * @since 1.0.0
 * @copyright mikejolley
 * @param array   $file_data Array of $_FILE data to upload.
 * @param array   $args      Optional arguments
 * @return array|WP_Error Array of objects containing either file information or an error
 */
function wpum_upload_file( $file, $args = array() ) {
	global $wpum_upload, $wpum_uploading_file;

	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/media.php';

	$args = wp_parse_args( $args, array(
			'file_key'           => '',
			'file_label'         => '',
			'allowed_mime_types' => get_allowed_mime_types()
		) );

	$wpum_upload         = true;
	$wpum_uploading_file = $args['file_key'];
	$uploaded_file              = new stdClass();

	if ( ! in_array( $file['type'], $args['allowed_mime_types'] ) ) {
		if ( $args['file_label'] ) {
			return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s', 'wpum' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
		} else {
			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'wpum' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
		}
	} else {
		$upload = wp_handle_upload( $file, apply_filters( 'submit_wpum_handle_upload_overrides', array( 'test_form' => false ) ) );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'] );
		} else {
			$uploaded_file->url       = $upload['url'];
			$uploaded_file->name      = basename( $upload['file'] );
			$uploaded_file->path      = $upload['file'];
			$uploaded_file->type      = $upload['type'];
			$uploaded_file->size      = $file['size'];
			$uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
		}
	}

	$wpum_upload         = false;
	$wpum_uploading_file = '';

	return $uploaded_file;
}

/**
 * Wrapper function for size_format - checks the max size of the avatar field.
 *
 * @since 1.0.0
 * @param array   $field
 * @param string  $size  in bytes
 * @return string
 */
function wpum_max_upload_size( $field_name = '' ) {

	// Default max upload size
	$output = size_format( wp_max_upload_size() );

	// Check if the field is the avatar upload field and max size is defined
	if ( $field_name == 'user_avatar' && defined( 'WPUM_MAX_AVATAR_SIZE' ) )
		$output = size_format( WPUM_MAX_AVATAR_SIZE );

	return $output;
}

/**
 * Displays a button to check uploads folder permissions.
 *
 * @since 1.0.0
 * @return void
 */
function wpum_check_permissions_button() {

	$output = '<br/><br/>';
	$output .= '<a class="button" href="'.admin_url( 'users.php?page=wpum-settings&tab=profile&wpum_action=check_folder_permission' ).'">'.__( 'Verify upload permissions', 'wpum' ).'</a>';
	$output .= '<p class="description">'.__( 'Press the button above if avatar uploads does not work.', 'wpum' ).'</p>';

	return $output;

}

/**
 * Generates core pages and updates settings panel with the newly created pages.
 *
 * @since 1.0.0
 * @return void
 */
function wpum_generate_pages( $redirect = false ) {

	// Generate login page
	if ( ! wpum_get_option( 'login_page' ) ) {

		$login = wp_insert_post(
			array(
				'post_title'     => __( 'Login', 'wpum' ),
				'post_content'   => '[wpum_login_form]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		wpum_update_option( 'login_page', $login );

	}

	// Generate password recovery page
	if ( ! wpum_get_option( 'password_recovery_page' ) ) {

		$psw = wp_insert_post(
			array(
				'post_title'     => __( 'Password Reset', 'wpum' ),
				'post_content'   => '[wpum_password_recovery form_id="" login_link="yes" psw_link="no" register_link="yes" ]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		wpum_update_option( 'password_recovery_page', $psw );

	}

	// Generate password recovery page
	if ( ! wpum_get_option( 'registration_page' ) ) {

		$register = wp_insert_post(
			array(
				'post_title'     => __( 'Register', 'wpum' ),
				'post_content'   => '[wpum_register form_id="" login_link="yes" psw_link="yes" register_link="no" ]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		wpum_update_option( 'registration_page', $register );

	}

	// Generate account page
	if ( ! wpum_get_option( 'account_page' ) ) {

		$account = wp_insert_post(
			array(
				'post_title'     => __( 'Account', 'wpum' ),
				'post_content'   => '[wpum_account]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		wpum_update_option( 'account_page', $account );

	}

	// Generate password recovery page
	if ( ! wpum_get_option( 'profile_page' ) ) {

		$profile = wp_insert_post(
			array(
				'post_title'     => __( 'Profile', 'wpum' ),
				'post_content'   => '[wpum_profile]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		wpum_update_option( 'profile_page', $profile );

	}

	if ( $redirect ) {
		wp_redirect( admin_url( 'users.php?page=wpum-settings&tab=general&setup_done=true' ) );
		exit;
	}
}

/**
 * Generates tabs for the account page.
 * Tabs are needed to split content in multiple parts,
 * and not produce a very long form.
 *
 * @since 1.0.0
 * @todo  sort by priority for addon.
 * @return void
 */
function wpum_get_account_page_tabs() {

	$tabs = array();

	$tabs['details'] = array(
		'id'    => 'details',
		'title' => esc_html__('Edit Account', 'wpum'),
		'priority' => 0,
	);
	$tabs['change-password'] = array(
		'id'    => 'change-password',
		'title' => esc_html__('Change Password', 'wpum'),
		'priority' => 1,
	);

	uasort( $tabs, 'wpum_sort_by_priority' );

	return apply_filters( 'wpum_get_account_page_tabs', $tabs );

}

/**
 * Generates url of a single account tab.
 *
 * @since 1.0.0
 * @return string $tab_url url of the tab.
 */
function wpum_get_account_tab_url( $tab ) {

	if( get_option( 'permalink_structure' ) == '' ) :
		$tab_url = add_query_arg( 'account_tab', $tab, wpum_get_core_page_url( 'account' ) );
	else :
		$tab_url = wpum_get_core_page_url( 'account' ) . $tab;
	endif;

	return esc_url( $tab_url );

}

/**
 * Checks the current active account tab (if any).
 *
 * @since 1.0.0
 * @return bool|string
 */
function wpum_get_current_account_tab() {

	$tab = ( get_query_var( 'account_tab' ) ) ? get_query_var( 'account_tab' ) : null;
	return $tab;

}

/**
 * Checks the given account tab is registered.
 *
 * @since 1.0.0
 * @param string  $tab the key value of the array in wpum_get_account_page_tabs() must match slug
 * @return bool
 */
function wpum_account_tab_exists( $tab ) {

	$exists = false;

	if ( array_key_exists( $tab, wpum_get_account_page_tabs() ) )
		$exists = true;

	return $exists;

}

/**
 * Get the login redirect url
 *
 * @since 1.0.0
 * @return mixed
 */
function wpum_get_login_redirect_url() {

	$url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = add_query_arg( array(
		'login'       => false,
		'captcha'     => false,
	), $url );

	return apply_filters( 'wpum_login_redirect_url', esc_url( $url ) );

}

/**
 * Returns the url where users are redirected after a successfull registration.
 *
 * @since 1.1.0
 * @return string redirect url
 */
function wpum_registration_redirect_url() {

	$url = false;

	if( wpum_get_option( 'registration_redirect' ) ) {
		$url = get_permalink( wpum_get_option( 'registration_redirect' ) );
	}

	return apply_filters( 'wpum_registration_redirect_url', $url );

}

/**
 * Check whether a function is disabled.
 *
 * @since 1.2.0
 * @param  string  $function name of the function
 * @return boolean
 */
function wpum_is_func_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );
	return in_array( $function, $disabled );
}

/**
 * Gets file extension of a file.
 *
 * @since 1.2.0
 * @param  string $str file name
 * @return string      extension of the file
 */
function wpum_get_file_extension( $str ) {
	$parts = explode( '.', $str );
	return end( $parts );
}

/**
 * Covert object data to array.
 *
 * @since 1.2.0
 * @param  array|object $data data to pass and convert.
 * @return array
 */
function wpum_object_to_array( $data ) {
	if ( is_array( $data ) || is_object( $data ) ) {
		$result = array();
		foreach ( $data as $key => $value ) {
			$result[ $key ] = wpum_object_to_array( $value );
		}
		return $result;
	}
	return $data;
}

/**
 * Wrapper function to install groups database table and install primary group.
 *
 * @since 1.0.0
 * @return void
 */
function wpum_install_groups() {

	if( ! get_option( 'wpum_version_upgraded_from' ) ) {

		// Create database table for field groups
		@WPUM()->field_groups->create_table();

		// Add primary group
		$field_groups_args = array(
			'id'         => 1,
			'name'       => 'Primary',
			'can_delete' => false,
			'is_primary' => true
		);

		WPUM()->field_groups->add( $field_groups_args );

	}

}

/**
 * Wrapper function to install fields database table and install primary fields.
 *
 * @since 1.0.0
 * @return void
 */
function wpum_install_fields() {

	if( ! get_option( 'wpum_version_upgraded_from' ) ) {

		// Create database table for field groups
		@WPUM()->fields->create_table();

		// Get primary group id
		$primary_group = WPUM()->field_groups->get_group_by( 'primary' );

		// Install fields
		$fields = array(
			array(
				'id'                   => 1,
				'group_id'             => $primary_group->id,
				'type'                 => 'username',
				'name'                 => 'Username',
				'is_required'          => true,
				'show_on_registration' => true,
				'can_delete'           => false,
				'meta'                 => 'username',
			),
			array(
				'id'                   => 2,
				'group_id'             => $primary_group->id,
				'type'                 => 'email',
				'name'                 => 'Email',
				'is_required'          => true,
				'show_on_registration' => true,
				'can_delete'           => false,
				'meta'                 => 'user_email',
			),
			array(
				'id'                   => 3,
				'group_id'             => $primary_group->id,
				'type'                 => 'password',
				'name'                 => 'Password',
				'is_required'          => true,
				'show_on_registration' => true,
				'can_delete'           => false,
				'meta'                 => 'password',
			),
			array(
				'id'                   => 4,
				'group_id'             => $primary_group->id,
				'type'                 => 'text',
				'name'                 => 'First Name',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'first_name',
			),
			array(
				'id'                   => 5,
				'group_id'             => $primary_group->id,
				'type'                 => 'text',
				'name'                 => 'Last Name',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'last_name',
			),
			array(
				'id'                   => 6,
				'group_id'             => $primary_group->id,
				'type'                 => 'nickname',
				'name'                 => 'Nickname',
				'is_required'          => true,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'nickname',
			),
			array(
				'id'                   => 7,
				'group_id'             => $primary_group->id,
				'type'                 => 'display_name',
				'name'                 => 'Display Name',
				'is_required'          => true,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'display_name',
			),
			array(
				'id'                   => 8,
				'group_id'             => $primary_group->id,
				'type'                 => 'text',
				'name'                 => 'Website',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'user_url',
			),
			array(
				'id'                   => 9,
				'group_id'             => $primary_group->id,
				'type'                 => 'textarea',
				'name'                 => 'Description',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'description',
			),
			array(
				'id'                   => 10,
				'group_id'             => $primary_group->id,
				'type'                 => 'avatar',
				'name'                 => 'Profile Picture',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'user_avatar',
			)
		);

		foreach ( $fields as $field ) {
			WPUM()->fields->add( $field );
		}

	}

}

/**
 * Utility function to convert an array to an object.
 *
 * @since 1.2.0
 * @param  array $array the array to convert.
 * @return object        converted object.
 */
function wpum_array_to_object( $array ) {

	$object = new stdClass();

	if ( is_array( $array ) && count( $array ) > 0) {
		foreach ( $array as $name => $value ) {
			$name = strtolower( trim( $name ) );
			if ( ! empty( $name ) ) {
	      $object->$name = $value;
	    }
		}
	}

	return $object;

}

/**
 * Utility function to check if an array is multidimensional.
 *
 * @since 1.2.0
 * @param  array  $array the array to check.
 * @return boolean
 */
function wpum_is_multi_array( $array ) {
	return ( count( $array ) !== count( $array, COUNT_RECURSIVE ) );
}

/**
 * Function to sort arrays by priority key.
 *
 * @since 1.3.0
 * @param  array $a array
 * @param  array $b array
 * @return array
 */
function wpum_sort_by_priority( $a, $b ) {
	if ( $a['priority'] == $b['priority'] )
			return 0;
	return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
}

/**
 * Verify whether the psw cloacking script is enabled.
 * Can be disabled through filter.
 *
 * @since 1.3.0
 * @return boolean
 */
function wpum_is_psw_cloacking_enabled() {

	$enabled = true;

	return apply_filters( 'wpum_is_psw_cloacking_enabled', $enabled );

}
