<?php
/**
 * WP User Manager Forms
 *
 * @package     wp-user-manager
 * @author      Mike Jolley
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Forms Class
 *
 * @since 1.0.0
 */
class WPUM_Forms {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'load_posted_form' ) );

	}

	/**
	 * Loads the submitted class on form submit.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_posted_form() {
		if ( ! empty( $_POST['wpum_submit_form'] ) ) {
			$this->load_form_class( sanitize_title( $_POST['wpum_submit_form'] ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @access public
	 * @since 1.0.0
	 * @param  string $form_name
	 * @return string class name on success, false on failure
	 */
	public function load_form_class( $form_name ) {

		// Load the form abtract
		if ( ! class_exists( 'WPUM_Form' ) )
			include( WPUM_PLUGIN_DIR . 'includes/abstracts/abstract-wpum-form.php' );

		// Now try to load the form_name
		$form_class  = 'WPUM_Form_' . str_replace( '-', '_', $form_name );
		$form_file   = WPUM_PLUGIN_DIR . '/includes/forms/class-wpum-form-' . $form_name . '.php';

		if ( class_exists( $form_class ) )
			return $form_class;

		if ( ! file_exists( $form_file ) )
			return false;

		if ( ! class_exists( $form_class ) )
			include $form_file;

		// Init the form
		call_user_func( array( $form_class, "init" ) );

		return $form_class;

	}

	/**
	 * get_form function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $form_name
	 * @param  array $atts Optional passed attributes
	 * @return string
	 */
	public function get_form( $form_name, $atts = array() ) {
		if ( $form = $this->load_form_class( $form_name ) ) {
			ob_start();
			call_user_func( array( $form, "output" ), $atts );
			return ob_get_clean();
		}
	}

}
