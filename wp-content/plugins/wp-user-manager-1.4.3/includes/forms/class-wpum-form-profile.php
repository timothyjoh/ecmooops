<?php
/**
 * WP User Manager Forms: Profile Edit Form
 *
 * @package     wp-user-manager
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Form_Password Class
 *
 * @since 1.0.0
 */
class WPUM_Form_Profile extends WPUM_Form {

	public static $form_name = 'profile';

	private static $user;

	/**
	 * Init the form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {

		add_action( 'wp', array( __CLASS__, 'process' ) );

		// Set values to the fields
		if( ! is_admin() ) {

			self::$user = wp_get_current_user();
			add_filter( 'wpum_profile_field_value', array( __CLASS__, 'set_fields_values' ), 10, 3 );
			add_filter( 'wpum_profile_field_options', array( __CLASS__, 'set_fields_options' ), 10, 3 );
			add_filter( 'wpum/form/validate=profile', array( __CLASS__, 'validate_email' ), 10, 3 );
			add_filter( 'wpum/form/validate=profile', array( __CLASS__, 'validate_nickname' ), 10, 3 );

		}

		// Store uploaded avatar
		if( wpum_get_option( 'custom_avatars' ) ) {
			add_action( 'wpum_after_user_update', array( __CLASS__, 'add_avatar' ), 10, 3 );
		}

	}

	/**
	 * Setup field values on the frontend based on the user
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $value value of the field.
	 */
	public static function set_fields_values( $default, $field ) {

		switch ( $field['meta'] ) {
			case 'first_name':
				return self::$user->user_firstname;
				break;
			case 'last_name':
				return self::$user->user_lastname;
				break;
			case 'nickname':
				return self::$user->nickname;
				break;
			case 'user_email':
				return self::$user->user_email;
				break;
			case 'user_url':
				return self::$user->user_url;
				break;
			case 'description':
				return self::$user->description;
				break;
			case 'display_name':
				return self::get_selected_name();
				break;
			case 'user_avatar':
				return get_user_meta( self::$user->ID, 'current_user_avatar', true );
				break;
			default:
				return apply_filters( 'wpum_edit_account_field_value', null, $field, self::$user->ID );
				break;
		}

	}

	/**
	 * Setup field options on the frontend based on the user
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $value value of the field.
	 */
	public static function set_fields_options( $default, $new_field ) {

		$options = array();

		switch ( $new_field['meta'] ) {
			case 'display_name':
				$options = self::get_display_name_options( self::$user );
				break;
		}

		return $options;

	}

	/**
	 * Returns the options for the "display_name" field on the profile form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $options list of the available options
	 */
	public static function get_display_name_options( $user ) {

		$options = array();

		// Generate the options
		$public_display = array();
		$public_display['display_username']  = $user->user_login;
		$public_display['display_nickname']  = $user->nickname;

		if ( !empty($user->first_name) )
			$public_display['display_firstname'] = $user->first_name;

		if ( !empty($user->last_name) )
			$public_display['display_lastname'] = $user->last_name;

		if ( !empty($user->first_name) && !empty($user->last_name) ) {
			$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
			$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
		}

		if ( !in_array( $user->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
			$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;

		$public_display = array_map( 'trim', $public_display );
		$public_display = array_unique( $public_display );

		// Add options to original array
		foreach ( $public_display as $id => $item ) {
			$options += array( $id => $item );
		}

		return $options;

	}

	/**
	 * Returns the correct default selected option based
	 * on what display_name the user has chosen.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $options list of the available options
	 */
	public static function get_selected_name() {

		$selected_name = self::$user->display_name;
		$user_login    = self::$user->user_login;
		$nickname      = self::$user->nickname;
		$first_name    = self::$user->first_name;
		$last_name     = self::$user->last_name;
		$firstlast     = self::$user->first_name . ' ' . self::$user->last_name;
		$lastfirst     = self::$user->last_name . ' ' . self::$user->first_name;

		$selected_value = $user_login;

		switch ( $selected_name ) {
			case $nickname:
				$selected_value = 'display_nickname';
				break;
			case $first_name:
				$selected_value = 'display_firstname';
				break;
			case $last_name:
				$selected_value = 'display_lastname';
				break;
			case $firstlast:
				$selected_value = 'display_firstlast';
				break;
			case $lastfirst:
				$selected_value = 'display_lastfirst';
				break;
			default:
				$selected_value = $user_login;
				break;
		}

		return $selected_value;

	}

	/**
	 * Define profile fields
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function get_profile_fields() {

		self::$fields = array(
			'profile' => wpum_get_account_fields()
		);
		
	}

	/**
	 * Validate email field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_email( $passed, $fields, $values ) {

		$email = $values['profile'][ 'user_email' ];

		// If current email hasn't changed - abort.
		if( $email == self::$user->user_email )
			return;

		if( email_exists( $email ) && $email !== self::$user->user_email )
			return new WP_Error( 'email-validation-error', __( 'Email address already exists.', 'wpum' ) );

		return $passed;

	}

	/**
	 * Validate nickname field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_nickname( $passed, $fields, $values ) {

		$nickname = $values['profile'][ 'nickname' ];

		if( wpum_get_option('exclude_usernames') && array_key_exists( $nickname , wpum_get_disabled_usernames() ) )
			return new WP_Error( 'nickname-validation-error', __( 'This nickname cannot be used.', 'wpum' ) );

		// Check for nicknames if permalink structure requires unique nicknames.
		if( get_option('wpum_permalink') == 'nickname'  ) :

			$current_user = wp_get_current_user();

			if( $nickname !== $current_user->user_nicename && wpum_nickname_exists( $nickname ) )
				return new WP_Error( 'username-validation-error', __( 'This nickname cannot be used.', 'wpum' ) );

		endif;

		return $passed;

	}

	/**
	 * Process the submission.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function process() {

		// Get fields
		self::get_profile_fields();

		// Get posted values
		$values = self::get_posted_fields();

		if ( empty( $_POST['wpum_submit_form'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'profile' ) ) {
			return;
		}

		// Validate required
		if ( is_wp_error( ( $return = self::validate_fields( $values, self::$form_name ) ) ) ) {
			self::add_error( $return->get_error_message() );
			return;
		}

		// Update the profile
		self::update_profile( $values );

	}

	/**
	 * Trigger update process.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function update_profile( $values ) {

		if( empty($values) || !is_array($values) )
			return;

		$user_data = array( 'ID' => self::$user->ID );

		foreach ( $values['profile'] as $meta_key => $meta_value ) {

			switch ( $meta_key ) {

				case 'user_email':

					if( is_email( $meta_value ) ) :
						$user_data += array( 'user_email' => $meta_value );
					else :
						self::add_error( __( 'Please enter a valid email address.', 'wpum') );
						return;
					endif;

				break;

				case 'display_name':
					$user_data += array( 'display_name' => self::store_display_name( $values['profile'], $meta_value ) );
				break;

				case 'nickname':
					$user_data += array( 'user_nicename' => $meta_value );
					$user_data += array( 'nickname' => $meta_value );
				break;

				default:
					$user_data += array( $meta_key => $meta_value );
					break;

			}

		}

		do_action( 'wpum_before_user_update', $user_data, $values, self::$user->ID );

		$user_id = wp_update_user( $user_data );

		do_action( 'wpum_after_user_update', $user_data, $values, self::$user->ID );

		if ( is_wp_error( $user_id ) ) {

			$this_page = add_query_arg( array( 'updated' => 'error' ), get_permalink() );
			wp_redirect( esc_url( $this_page ) );
			exit();

		} else {

			$this_page = add_query_arg( array( 'updated' => 'success' ), get_permalink() );
			wp_redirect( esc_url( $this_page ) );
			exit();

		}


	}

	/**
	 * Decides which option should be stored into the database.
	 * This avoids the "display_name" option into the profile form to
	 * save the select field option value into the database.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function store_display_name( $values, $meta_value ) {

		$name = self::$user->user_login;

		switch ($meta_value) {
			case 'display_nickname':
				$name = $values['nickname'];
				break;
			case 'display_firstname':
				$name = $values['first_name'];
				break;
			case 'display_lastname':
				$name = $values['last_name'];
				break;
			case 'display_firstlast':
				$name = $values['first_name'] . ' ' . $values['last_name'];
				break;
			case 'display_lastfirst':
				$name = $values['last_name'] . ' ' . $values['first_name'];
				break;

			default:
				$name = self::$user->user_login;
				break;
		}

		return $name;

	}

	/**
	 * Add avatar to user custom field.
	 * Also deletes previously selected avatar.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_avatar( $user_data, $values, $user_id ) {

		$avatar_field = $values['profile'][ 'user_avatar' ];

		if( !empty( $avatar_field ) && is_array( $avatar_field ) ) {

			// Deletes previously selected avatar.
			$previous_avatar = get_user_meta( $user_id, '_current_user_avatar_path', true );
			if( $previous_avatar )
				wp_delete_file( $previous_avatar );

			update_user_meta( $user_id, "current_user_avatar", esc_url( $avatar_field['url'] ) );
			update_user_meta( $user_id, '_current_user_avatar_path', $avatar_field['path'] );

		}

	}

	/**
	 * Output the form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function output( $atts = array() ) {

		// Get the tabs
		$current_account_tab = wpum_get_current_account_tab();
		$all_tabs            = array_keys( wpum_get_account_page_tabs() );

		// Get fields
		self::get_profile_fields();

		// Display template
		if( is_user_logged_in() ) :

			if( isset( $_POST['submit_wpum_profile'] ) ) {
				// Show errors from fields
				self::show_errors();
			}

			get_wpum_template( 'account.php',
				array(
					'atts'        => $atts,
					'form'        => self::$form_name,
					'fields'      => self::get_fields( 'profile' ),
					'user_id'     => self::$user->ID,
					'current_tab' => $current_account_tab,
					'all_tabs'    => $all_tabs
				)
			);

		// Show login form if not logged in
		else :

			echo wpum_login_form();

		endif;

	}

}
