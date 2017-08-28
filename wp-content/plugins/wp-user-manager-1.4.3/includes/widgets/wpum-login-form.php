<?php
/**
 * Display login form.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Login_Form_Widget Class
 *
 * @since 1.0.0
 */
class WPUM_Login_Form_Widget extends WPH_Widget {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Configure widget array
		$args = array(
			'label'       => __( '[WPUM] Login Form', 'wpum' ),
			'description' => __( 'Display login form.', 'wpum' ),
		);

		$args['fields'] = array(
			array(
				'name'   => __( 'Title', 'wpum' ),
				'id'     => 'title',
				'type'   => 'text',
				'class'  => 'widefat',
				'std'    => __( 'Login', 'wpum' ),
				'filter' => 'strip_tags|esc_attr'
			),
			array(
				'name'   => __( 'Logged In title', 'wpum' ),
				'desc'   => __(' This title will be displayed when logged in.', 'wpum'),
				'id'     => 'logged_in_title',
				'type'   => 'text',
				'class'  => 'widefat',
				'std'    => __( 'My Account', 'wpum' ),
				'filter' => 'strip_tags|esc_attr'
			),
			array(
				'name'     => __( 'Display login link', 'wpum' ),
				'id'       => 'login_link',
				'type'     =>'checkbox',
				'std'      => 0,
				'filter'   => 'strip_tags|esc_attr',
			),
			array(
				'name'     => __( 'Display password recovery link', 'wpum' ),
				'id'       => 'psw_link',
				'type'     =>'checkbox',
				'std'      => 1,
				'filter'   => 'strip_tags|esc_attr',
			),
			array(
				'name'     => __( 'Display registration link', 'wpum' ),
				'id'       => 'register_link',
				'type'     =>'checkbox',
				'std'      => 1,
				'filter'   => 'strip_tags|esc_attr',
			),
			array(
				'name'   => __( 'Display profile overview', 'wpum' ),
				'desc'   => __('If enabled, once logged in, an overview of the current user profile will appear.', 'wpum'),
				'id'     => 'current_profile',
				'type'   =>'checkbox',
				'std'    => 1,
				'filter' => 'strip_tags|esc_attr',
			),
		);

		// create widget
		$this->create_widget( $args );

	}

	/**
	 * Display widget content.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	public function widget( $args, $instance ) {

		ob_start();

		echo $args['before_widget'];
		echo $args['before_title'];
		if( is_user_logged_in() ) :
			echo $instance['logged_in_title'];
		else :
			echo $instance['title'];
		endif;
		echo $args['after_title'];

		// Default form settings
		$settings = array();

		$settings['redirect'] = wpum_get_login_redirect_url();

		$settings['psw_link']      = false;
		$settings['login_link']    = false;
		$settings['register_link'] = false;

		if( $instance['psw_link'] )
			$settings['psw_link'] = 'yes';
		if( $instance['register_link'] )
			$settings['register_link'] = 'yes';
		if( $instance['login_link'] )
			$settings['login_link'] = 'yes';

		if( is_user_logged_in() && $instance['current_profile'] ) :
			echo wpum_current_user_overview();
		else :
			echo wpum_login_form( $settings );
		endif;

		echo $args['after_widget'];

		$output = ob_get_clean();

		echo $output;

	}

}
