<?php
/**
 * Plugin Filters
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Settings Link To WP-Plugin Page
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_add_settings_link( $links ) {
	$settings_link = '<a href="'.admin_url( 'users.php?page=wpum-settings' ).'">'.__('Settings','wpum').'</a>';
	array_push( $links, $settings_link );
	return $links;
}
add_filter( "plugin_action_links_".WPUM_SLUG , 'wpum_add_settings_link');

/**
 * Add links to plugin row
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_plugin_row_meta( $input, $file ) {

	if ( $file != 'wp-user-manager/wp-user-manager.php' )
		return $input;

	$links = array(
		'<a href="http://docs.wpusermanager.com" target="_blank">' . esc_html__( 'Documentation', 'wpum' ) . '</a>',
		'<a href="http://wpusermanager.com/addons/" target="_blank">' . esc_html__( 'Extensions', 'wpum' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'wpum_plugin_row_meta', 10, 2 );

/**
 * Add User ID Column to users list
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_add_user_id_column( $columns ) {
    $columns['user_id'] = __( 'User ID', 'wpum' );
    return $columns;
}
add_filter( 'manage_users_columns', 'wpum_add_user_id_column' );

/**
 * Filters the upload dir when $wpum_upload is true
 *
 * @copyright mikejolley
 * @since 1.0.0
 * @param  array $pathdata
 * @return array
 */
function wpum_upload_dir( $pathdata ) {
	global $wpum_upload, $wpum_uploading_file;

	if ( ! empty( $wpum_upload ) ) {
		$dir = apply_filters( 'wpum_upload_dir', 'wp-user-manager-uploads' );

		if ( empty( $pathdata['subdir'] ) ) {
			$pathdata['path']   = $pathdata['path'] . '/' . $dir;
			$pathdata['url']    = $pathdata['url'] . '/' . $dir;
			$pathdata['subdir'] = '/' . $dir;
		} else {
			$new_subdir         = '/' . $dir . $pathdata['subdir'];
			$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
			$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
			$pathdata['subdir'] = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['subdir'] );
		}
	}

	return $pathdata;
}
add_filter( 'upload_dir', 'wpum_upload_dir' );

/**
 * Add rating links to the admin panel
 *
 * @since	    1.0.0
 * @global		string $typenow
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function wpum_admin_rate_us( $footer_text ) {

	$screen = get_current_screen();

	if ( $screen->base !== 'users_page_wpum-settings' )
		return;

	$rate_text = sprintf( __( 'Please support the future of <a href="%1$s" target="_blank">WP User Manager</a> by <a href="%2$s" target="_blank">rating us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'wprm', 'wpum' ),
		'https://wpusermanager.com',
		'http://wordpress.org/support/view/plugin-reviews/wp-user-manager?filter=5#postform'
	);

	return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . ' <span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span></span>';

}
add_filter( 'admin_footer_text', 'wpum_admin_rate_us' );

/**
 * Add custom classes to body tag
 *
 * @since	    1.0.0
 * @param       array $classes
 * @return      array
 */
function wpum_body_classes($classes) {

	if( is_page( wpum_get_core_page_id('login') ) ) {
		// add class if we're on a login page
		$classes[] = 'wpum-login-page';
	} else if( is_page( wpum_get_core_page_id('register') ) ) {
		// add class if we're on a register page
		$classes[] = 'wpum-register-page';
	} else if( is_page( wpum_get_core_page_id('account') ) ) {
		// add class if we're on a account page
		$classes[] = 'wpum-account-page';
	} else if( is_page( wpum_get_core_page_id('profile') ) ) {

		// add class if we're on a profile page
		$classes[] = 'wpum-profile-page';

		// add user to body class if set
		if( wpum_is_single_profile() )
			$classes[] = 'wpum-user-' . wpum_is_single_profile();

	} else if( is_page( wpum_get_core_page_id('password') ) ) {
		// add class if we're on a password page
		$classes[] = 'wpum-password-page';
	}

	return $classes;
}
add_filter( 'body_class', 'wpum_body_classes' );

/**
 * Retrieve custom avatar if any
 *
 * @since 1.0.0
 *
 * @param int|string|object $id_or_email A user ID,  email address, or comment object
 * @param int     $size        Size of the avatar image
 * @param string  $default     URL to a default image to use if no avatar is available
 * @param string  $alt         Alternative text to use in image tag. Defaults to blank
 * @return false|string `<img>` tag for the user's avatar.
 */
function wpum_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

	$safe_alt = esc_attr( $alt );
	$custom_avatar = false;

	if( is_object( $id_or_email ) ) {

		$comment_email = $id_or_email->comment_author_email;

		$user = get_user_by( 'email', $comment_email );

		if( $user ) {
			$custom_avatar = get_user_meta( $user->ID , 'current_user_avatar', true );
		}

	} elseif ( is_email( $id_or_email ) && email_exists( $id_or_email ) || is_numeric( $id_or_email ) ) {
		$custom_avatar = get_user_meta( $id_or_email, 'current_user_avatar', true );
	}

	if ( !empty( $custom_avatar ) ) {
		$avatar = "<img alt='{$safe_alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
	}

	return $avatar;

}
add_filter('get_avatar', 'wpum_get_avatar', 1, 5);

/**
 * Highlight which pages are selected as plugin's core page
 * within the Pages management screen.
 *
 * @since 1.0.0
 * @param array $post_states An array of post display states.
 * @param int   $post        The post ID.
 * @return array
 */
function wpum_highlight_pages( $post_states, $post ) {

	$icon = '<i class="wpum-shortcodes-icon" title="'.__( 'WPUM Page', 'wpum' ).'"></i>';

	if( wpum_get_core_page_id( 'login' ) == $post->ID ) {
		$post_states['page_for_login'] = $icon;
	} else if( wpum_get_core_page_id( 'account' ) == $post->ID ) {
		$post_states['page_for_account'] = $icon;
	} else if( wpum_get_core_page_id( 'password' ) == $post->ID ) {
		$post_states['page_for_password'] = $icon;
	} else if( wpum_get_core_page_id( 'register' ) == $post->ID ) {
		$post_states['page_for_registration'] = $icon;
	} else if( wpum_get_core_page_id( 'profile' ) == $post->ID ) {
		$post_states['page_for_profiles'] = $icon;
	}

	return $post_states;

}
add_filter( 'display_post_states', 'wpum_highlight_pages', 10, 2 );

/**
 * Adjust body class on admin panel
 *
 * @since 1.0.0
 * @return array
 */
function wpum_admin_body_classes( $classes ) {

	$screen = get_current_screen();

	if( $screen->base == 'plugin-install' && isset( $_GET['tab'] ) && $_GET['tab'] == 'wpum_addons' ) {
		$classes .= 'wpum_addons_page';
	}

	return $classes;

}
add_filter( 'admin_body_class', 'wpum_admin_body_classes' );

/**
 * Filter allowed file types on upload forms.
 *
 * @since 1.0.0
 * @param  array $upload_mimes list of file types
 * @return array $upload_mimes list of file types
 */
function wpum_adjust_mime_types( $upload_mimes ) {

	$allowed_types = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif'          => 'image/gif',
		'png'          => 'image/png'
	);

	$upload_mimes = array_intersect_key( $upload_mimes, $allowed_types );

	return $upload_mimes;
}

/**
 * Properly setup links for wpum powered nav menu items.
 * Determines which links should be displayed and what their url should be.
 *
 * @since 1.1.0
 * @param  object $menu_item the menu item object
 * @return object            the modified menu item object
 */
function wpum_setup_nav_menu_item( $menu_item ) {

	if ( is_admin() ) {
		return $menu_item;
	}

	// Prevent a notice error when using the customizer
	$menu_classes = $menu_item->classes;

	if ( is_array( $menu_classes ) ) {
		$menu_classes = implode( ' ', $menu_item->classes );
	}

	switch ( $menu_classes ) {
		case 'wpum-register-nav':
				if ( is_user_logged_in() ) {
					$menu_item->_invalid = true;
				} else {
					$menu_item->url = wpum_get_core_page_url( 'register' );
				}
			break;
		case 'wpum-login-nav':
				if ( is_user_logged_in() ) {
					$menu_item->_invalid = true;
				} else {
					$menu_item->url = wpum_get_core_page_url( 'login' );
				}
			break;
		case 'wpum-account-nav':
				if ( ! is_user_logged_in() ) {
					$menu_item->_invalid = true;
				} else {
					$menu_item->url = wpum_get_core_page_url( 'account' );
				}
			break;
		case 'wpum-logout-nav':
				if ( ! is_user_logged_in() ) {
					$menu_item->_invalid = true;
				} else {
					$menu_item->url = wpum_logout_url();
				}
			break;
		case 'wpum-psw-recovery-nav':
				if ( is_user_logged_in() ) {
					$menu_item->_invalid = true;
				} else {
					$menu_item->url = wpum_get_core_page_url( 'password' );
				}
			break;
	}

	return $menu_item;

}
add_filter( 'wp_setup_nav_menu_item', 'wpum_setup_nav_menu_item', 10, 1 );

/**
 * Allows login form to redirect to an url specified into a query string.
 *
 * @since 1.1.0
 * @param  string $url url
 * @return string      url specified into the query string
 */
function wpum_login_redirect_detection( $url ) {

	if( isset( $_GET[ 'redirect_to' ] ) && $_GET['redirect_to'] !== '' ) {
		$url = urldecode( $_GET['redirect_to'] );
	} elseif( wpum_get_option( 'login_redirect' ) ) {
		$url = get_permalink( wpum_get_option( 'login_redirect' ) );
	}

	return esc_url( $url );

}
add_filter( 'wpum_login_redirect_url', 'wpum_login_redirect_detection', 99, 1 );

/**
 * Adjusts page title when visiting users profiles.
 *
 * @since 1.3.0
 * @param string $title Default title for current view.
 * @param string $sep   Optional separator.
 */
function wpum_add_name_to_wptitle( $title, $sep ) {

	if( is_page( wpum_get_core_page_id( 'profile' ) ) && wpum_is_single_profile() ) {

		$name = wpum_get_user_displayname( wpum_get_displayed_user_id() );
		$title = "$name $sep $title";

		if ( in_array( 'wordpress-seo/wp-seo.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$sitename = get_bloginfo( 'name' );
		  $title = "$name $sep $sitename";
		}

	}

	return $title;

}
add_filter( 'wp_title', 'wpum_add_name_to_wptitle', 16, 2 );

/**
 * Changes the post's author url to a WPUM Profile url.
 *
 * @since 1.4.0
 *
 * @param  string $link            author's link.
 * @param  string $author_id       author's ID number.
 * @param  string $author_nicename author's nice name.
 * @return string                  new url.
 */
function wpum_change_author_url_to_profile( $link, $author_id, $author_nicename ) {

	$user = new WP_User( $author_id );

	return wpum_get_user_profile_url( $user );

}
add_filter( 'author_link', 'wpum_change_author_url_to_profile', 10, 3);
