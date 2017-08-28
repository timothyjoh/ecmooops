<?php
/**
 * Shortcodes
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Shortcodes Class
 * Registers shortcodes together with a shortcodes editor.
 *
 * @since 1.0.0
 */
class WPUM_Shortcodes {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_filter( 'widget_text', 'do_shortcode' );
		add_shortcode( 'wpum_login_form', array( $this, 'wpum_login_form' ) );
		add_shortcode( 'wpum_logout', array( $this, 'wpum_logout' ) );
		add_shortcode( 'wpum_login', array( $this, 'wpum_login' ) );
		add_shortcode( 'wpum_register', array( $this, 'wpum_registration' ) );
		add_shortcode( 'wpum_password_recovery', array( $this, 'wpum_password' ) );
		add_shortcode( 'wpum_account', array( $this, 'wpum_account' ) );
		add_shortcode( 'wpum_profile', array( $this, 'wpum_profile' ) );
		add_shortcode( 'wpum_recently_registered', array( $this, 'wpum_recently_registered' ) );
		add_shortcode( 'wpum_profile_card', array( $this, 'wpum_profile_card' ) );
		add_shortcode( 'wpum_restrict_logged_in', array( $this, 'wpum_restrict_logged_in' ) );
		add_shortcode( 'wpum_restrict_to_users', array( $this, 'wpum_restrict_to_users' ) );
		add_shortcode( 'wpum_restrict_to_user_roles', array( $this, 'wpum_restrict_to_user_roles' ) );
		add_shortcode( 'wpum_user_directory', array( $this, 'wpum_user_directory' ) );

	}

	/**
	 * Login Form Shortcode
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_login_form( $atts, $content=null ) {

		extract( shortcode_atts( array(
			'id'             => '',
			'label_username' => '',
			'label_password' => '',
			'label_remember' => '',
			'label_log_in'   => '',
			'login_link'     => '',
			'psw_link'       => '',
			'register_link'  => ''
		), $atts ) );

		// Set default values if options missing
		if(empty($id))
			$id = 'wpum_loginform';
		if(empty($label_username))
			$label_username = wpum_get_username_label();
		if(empty($label_password))
			$label_password = __('Password', 'wpum');
		if(empty($label_remember))
			$label_remember = __('Remember Me', 'wpum');
		if(empty($label_log_in))
			$label_log_in = __('Login', 'wpum');

		$args = array(
			'echo'           => true,
			'redirect'       => wpum_get_login_redirect_url(),
			'form_id'        => esc_attr($id),
			'label_username' => esc_attr($label_username),
			'label_password' => esc_attr($label_password),
			'label_remember' => esc_attr($label_remember),
			'label_log_in'   => esc_attr($label_log_in),
			'id_username'    => esc_attr($id).'user_login',
			'id_password'    => esc_attr($id).'user_pass',
			'id_remember'    => esc_attr($id).'rememberme',
			'id_submit'      => esc_attr($id).'wp-submit',
			'login_link'     => esc_attr($login_link),
			'psw_link'       => esc_attr($psw_link),
			'register_link'  => esc_attr($register_link)
		);

		ob_start();

		// Show already logged in message
		if( is_user_logged_in() ) :

			get_wpum_template( 'already-logged-in.php',
				array(
					'args' => $args,
					'atts' => $atts,
				)
			);

		// Show login form if not logged in
		else :

			get_wpum_template( 'forms/login-form.php',
				array(
					'args' => $args,
					'atts' => $atts,
				)
			);

			// Display helper links
			do_action( 'wpum_do_helper_links', $login_link, $register_link, $psw_link );

		endif;

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Render logout url
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_logout( $atts, $content=null ) {

		extract( shortcode_atts( array(
			'redirect' => '',
			'label'    => __('Logout', 'wpum')
		), $atts ) );

		$output = null;

		if( is_user_logged_in() )
			$output = sprintf( __('<a href="%s">%s</a>', 'wpum'), wpum_logout_url( $redirect ), esc_attr( $label ) );

		return $output;

	}

	/**
	 * Login Form Shortcode
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_login( $atts, $content=null ) {

		extract( shortcode_atts( array(
			'redirect' => '',
			'label'    => esc_html__( 'Login', 'wpum' )
		), $atts ) );

		if( is_user_logged_in() ) {

			$output = '';

		} else {

			$url = wpum_get_core_page_url( 'login' );

			if( ! empty( $redirect ) ) {
				$url = add_query_arg( array( 'redirect_to' => urlencode( $redirect ) ), $url );
			}

			$output = '<a href="'. esc_url( $url ) .'" class="wpum-login-link">'.esc_html( $label ).'</a>';

		}

		return $output;

	}

	/**
	 * Registration Form Shortcode
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_registration( $atts, $content=null ) {

		extract( shortcode_atts( array(
			'form_id'       => 'default_registration_form',
			'login_link'    => '',
			'psw_link'      => '',
			'register_link' => ''
		), $atts ) );

		// Set default values
		if( !array_key_exists('form_id', $atts) || empty($atts['form_id']) )
			$atts['form_id'] = 'default_registration_form';

		return WPUM()->forms->get_form( 'register', $atts );

	}

	/**
	 * Password Recovery Form Shortcode
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_password( $atts, $content=null ) {

		extract( shortcode_atts( array(
			'form_id'       => 'default_password_form',
			'login_link'    => '',
			'psw_link'      => '',
			'register_link' => ''
		), $atts ) );

		return WPUM()->forms->get_form( 'password', $atts );

	}

	/**
	 * Profile Edit Form Shortcode
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_account( $atts, $content=null ) {

		return WPUM()->forms->get_form( 'profile', $atts );

	}

	/**
	 * Profile Shortcode.
	 * Display currently logged in user profile
	 * or selected if profile is given by URL.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_profile( $atts, $content=null ) {

		ob_start();

		if( wpum_can_access_profile() )
			get_wpum_template( 'profile.php', array(
					'user_data' => wpum_get_user_by_data(),
			)
		);

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Recently Registered Users Shortcode
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_recently_registered( $atts, $content=null ) {

		extract( shortcode_atts( array(
			'amount'          => '1',
			'link_to_profile' => 'yes'
		), $atts ) );

		ob_start();

		get_wpum_template( 'recently-registered.php', array( 'amount' => intval( $amount ), 'link_to_profile' => $link_to_profile ) );

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Profile Card Shortcode.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_profile_card( $atts, $content=null ) {

		extract( shortcode_atts( array(
			'user_id'         => null,
			'template'        => null,
			'wrapper_id'      => null,
			'link_to_profile' => 'yes',
			'display_buttons' => 'yes',
		), $atts ) );

		ob_start();

		// Prepare attributes for the profile card
		if( $wrapper_id ) {
			$wrapper_id = '-'.$wrapper_id;
		}
		if( $link_to_profile == 'yes' ) {
			$link_to_profile = true;
		} else {
			$link_to_profile = false;
		}
		if( $display_buttons == 'yes' ) {
			$display_buttons = true;
		} else {
			$display_buttons = false;
		}

		// Detect which template should be loaded
		$card_template = 'profile-card.php';
		if( ! empty( $template ) ) {
			$card_template = "profile-card-{$template}.php";
		}

		get_wpum_template( $card_template, array(
				'user_data'       => get_user_by( 'id', intval( $user_id ) ),
				'wrapper_id'      => $wrapper_id,
				'link_to_profile' => $link_to_profile,
				'display_buttons' => $display_buttons,
				'atts'            => $atts
			)
		);

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Restrict content to logged in users only.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_restrict_logged_in( $atts, $content = null ) {

		ob_start();

		if ( is_user_logged_in() && !is_null( $content ) && !is_feed() ) {

			echo do_shortcode( $content );

		} else {

			$args = array(
				'id'   => 'wpum-guests-disabled',
				'type' => 'notice',
				'text' => sprintf( __('This content is available to members only. Please <a href="%s">login</a> or <a href="%s">register</a> to view this area.', 'wpum'), wpum_create_redirect_url( wpum_get_core_page_url('login'), get_permalink() ), wpum_get_core_page_url('register')  )
			);
			$warning = wpum_message( apply_filters( 'wpum_restrict_logged_in_message', $args ), true );

		}

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Restrict content to logged in users only and by ID.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_restrict_to_users( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'ids' => null,
		), $atts ) );

		ob_start();

		$allowed_users = explode( ',', $ids );
		$current_user = get_current_user_id();

		if( is_user_logged_in() && !is_null( $content ) && !is_feed() && in_array( $current_user , $allowed_users ) ) {

			echo do_shortcode( $content );

		} else {

			$args = array(
				'id'   => 'wpum-guests-disabled',
				'type' => 'notice',
				'text' => sprintf( __('This content is available to members only. Please <a href="%s">login</a> or <a href="%s">register</a> to view this area.', 'wpum'), wpum_create_redirect_url( wpum_get_core_page_url('login'), get_permalink() ), wpum_get_core_page_url('register')  )
			);
			$warning = wpum_message( apply_filters( 'wpum_restrict_to_users_message', $args ), true );

		}

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Restrict content to logged in users only and by user roles.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_restrict_to_user_roles( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'roles' => null,
		), $atts ) );

		ob_start();

		$allowed_roles = explode( ',', $roles );
		$allowed_roles = array_map( 'trim', $allowed_roles );

		$current_user = wp_get_current_user();

		if( is_user_logged_in() && !is_null( $content ) && !is_feed() && array_intersect( $current_user->roles, $allowed_roles ) ) {

			echo do_shortcode( $content );

		} else {

			$args = array(
				'id'   => 'wpum-guests-disabled',
				'type' => 'notice',
				'text' => sprintf( __('This content is available to members only. Please <a href="%s">login</a> or <a href="%s">register</a> to view this area.', 'wpum'), wpum_create_redirect_url( wpum_get_core_page_url('login'), get_permalink() ), wpum_get_core_page_url('register')  )
			);
			$warning = wpum_message( apply_filters( 'wpum_restrict_to_user_roles_args', $args ), true );

		}

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * User directory shortcode
	 *
	 * @access public
	 * @since  1.0.0
	 * @return $output shortcode output
	 */
	public function wpum_user_directory( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'id' => null,
		), $atts ) );

		ob_start();

		$directory_id = intval( $id );

		// Check if directory exists
		$check_directory = get_post_status( $directory_id );

		// Display error if something is wrong.
		if( !$id || $check_directory !== 'publish' ) :
			$args = array(
				'id'   => 'wpum-no-user-directory-id',
				'type' => 'error',
				'text' => __( 'Something went wrong, you have not set a directory ID or the directory is not published.', 'wpum' )
			);
			$warning = wpum_message( $args, true );
			return;
		endif;

		// Prepare Pagination
		$number = wpum_directory_profiles_per_page( $directory_id );

		// Prepare amount sorter.
		$can_sort = wpum_directory_display_amount_sorter( $directory_id );

		if( $can_sort && isset( $_GET['amount'] ) && is_numeric( $_GET['amount'] ) ) {
			$number = sanitize_key( $_GET['amount'] );
		}

		$paged  = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

		if( $paged == 1 ) {
			$offset = 0;
		} else {
			$offset = ( $paged -1 ) * $number;
		}

		// Prepare the query.
		$args = array();
		$args['number'] = $number;
		$args['offset'] = $offset;

		$user_query = new WP_User_Query( apply_filters( "wpum_user_directory_query", $args, $directory_id ) );

		// Detect which template we should be using.
		$template     = "user-directory.php";
		$template_tag = wpum_directory_has_custom_template( $directory_id );

		if( $template_tag ) {
			$template = "user-directory-{$template_tag}.php";
		}

		// Build Pagination Count
		// Modify $number var if a custom amount is set from the frontend
		// This updates the pagination too.
		if( isset( $_GET['amount'] ) && is_numeric( $_GET['amount'] ) ) {
			$number = $_GET['amount'];
		}

		$total_users = $user_query->total_users;
		$total_pages = ceil( $total_users / $number );

		// Merge directory details in array
		$directory_args = array(
			'user_data'    => $user_query->get_results(),
			'users_found'  => $user_query->get_total(),
			'total_users'  => $total_users,
			'total_pages'  => $total_pages,
			'directory_id' => $directory_id,
			'paged'        => $paged,
			'search_form'  => wpum_directory_has_search_form( $directory_id )
		);

		// Load the template
		get_wpum_template( $template, array( 'directory_args' => $directory_args ) );

		$output = ob_get_clean();

		return $output;

	}

}

new WPUM_Shortcodes;
