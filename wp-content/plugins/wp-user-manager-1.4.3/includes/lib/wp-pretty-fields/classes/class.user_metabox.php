<?php
/**
 * @package wp-pretty-fields
 * @author Alessandro Tesoro
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Pretty_User_Metabox') ) {
	
	/**
	 * Pretty user metabox class.
	 */
	class Pretty_User_Metabox {

		/**
		 * Holds meta box object
		 *
		 * @var object
		 * @access protected
		 */
		protected $_meta_box;

		/**
		 * Holds metaboxes errors
		 *
		 * @var object
		 * @access protected
		 */
		var $errors;

		/**
		 * @var array Fields information
		 */
		public $fields;

		/**
		 * Constructor - get the class hooked in and ready
		 * 
		 * @since 1.1.0
		 * @access public
   		 * @param array $meta_box 
		 */
		public function __construct( $meta_box ) {

			// Load only in admin area.
		    if ( ! is_admin() )
		      return;

			// Prepare the metabox values with the class variables.
		    $this->_meta_box = $meta_box;
		    $this->fields = &$this->_meta_box['fields'];

    		// Display error messages
    		add_action( 'admin_notices', array( $this, 'display_errors' ) );

    		$check_metabox = $this->check_meta_box( $this->_meta_box );
			if( !empty( $check_metabox->errors ) ) {
				$this->errors = $check_metabox;
				return;
			}

			// Set default values for fields
			$this->_meta_box['fields'] = Pretty_Metabox::normalize_fields( $this->_meta_box['fields'] );

			// Show Custom Fields
			add_action( 'show_user_profile', array( $this, 'show_metabox' ) );
			add_action( 'edit_user_profile', array( $this, 'show_metabox' ) );

			// Save Custom fields
			add_action( 'personal_options_update', array( $this, 'save_metabox' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_metabox' ) );

			// Enqueue styles and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		}

		/**
		 * Check metabox configuration. Trigger error if configuration is wrong.
		 *
		 * @since 1.1.0
		 * @access private
		 * @param variable $meta_box 
		 */
		private function check_meta_box( $meta_box ) {
			$errors = new WP_Error();
			// Check that the id exists
			if( ! array_key_exists( 'id', $meta_box ) ) {
				$errors->add( 'metabox-id-missing', __( 'Error: user metabox must have an ID.','wppf', 'wpum' ) );
			}
			return $errors;
		}

		/**
		 * Display errors if metabox configuration is wrong.
		 *
		 * @since 1.1.0
		 * @access public
		 */
		public function display_errors() {
			// grabs the error messages and cycle through each error
			if ( is_wp_error($this->errors) && !empty($this->errors->errors)) {
				// Let's get the errors
				$error_mesages = $this->errors->get_error_messages();
				foreach ($error_mesages as $message) {
					echo '<div class="error">';
					echo '<p><strong>'.$message.'</strong></p>';
					echo '</div>';
				}
			} 
		}

		/**
		 * Get the metabox id.
		 * 
		 * @since 1.1.0
		 * @access public
		 * @return string
		 */
		public function get_id() {
			return $this->_meta_box['id'];
		}

		/**
		 * Get the metabox title.
		 * 
		 * @since 1.1.0
		 * @access public
		 * @return string
		 */
		public function get_title() {
			return $this->_meta_box['title'];
		}

		/**
		 * Show metabox on term edit page.
		 * 
		 * @since 1.0.0
		 * @param term_id
		 * @return void
		 */
		public function show_metabox( $user ) {

			wp_nonce_field( "wppf-save-{$this->_meta_box['id']}", "nonce_{$this->_meta_box['id']}" );

			// Actions before all fields
			do_action( 'wppf_before_user', $user );
			do_action( "wppf_before_user_{$this->_meta_box['id']}", $user );

			// Wrap all fields
			echo self::before_fields();

			foreach ( $this->fields as $field ) {

				// Display content before markup of the single field
				echo Pretty_Fields::before_field_user($field);

				// Run actions before field markup
				do_action( 'wppf_before_user_field', $field, $user );
				do_action( "wppf_before_user_field_{$field['type']}", $field, $user );
				do_action( "wppf_before_user_field_{$field['id']}", $field, $user );

				// Get single field markup
				$meta = get_user_meta( $user->ID, $field['id'], true );
    			$meta = ( $meta !== '' ) ? $meta : (isset($field['std'])? $field['std'] : '');
				call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'show_user' ), $field, $meta );

				// Run actions before field markup
				do_action( 'wppf_after_user_field', $field, $user );
				do_action( "wppf_after_user_field_{$field['type']}", $field, $user );
				do_action( "wppf_after_user_field_{$field['id']}", $field, $user );

				// Display content before markup of the single field
				echo Pretty_Fields::after_field_user($field);

			}

			// Close wrap of all fields
			echo self::after_fields();
			
			// Actions after all fields
			do_action( 'wppf_after_user', $user );
			do_action( "wppf_after_user_{$this->_meta_box['id']}", $user );

		}

		/**
		 * Display content before all fields.
		 * Display metabox title if available.
		 * 
		 * @since 1.1.0
		 * @access public
		 * @return string
		 */
		public function before_fields() {
			
			$output = '<div class="wppf-user-fields" id="'.self::get_id().'">';

			if(self::get_title()) {
				$output .= '<h3 class="wppf-user-fields-metabox-title">'.self::get_title().'</h3>';
			}

			$output .= '<table class="form-table">';

			return $output;

		}

		/**
		 * Display content before all fields.
		 * 
		 * @since 1.1.0
		 * @access public
		 * @return string
		 */
		public function after_fields() {

			$output = '</table></div>';

			return $output;

		}

		/**
		 * Save data from meta box
		 *
		 * @param int $user_id USER ID
		 * @return void
		 */
		public function save_metabox( $user_id ) {

			// Check whether form is submitted properly
			$id    = $this->_meta_box['id'];
			$nonce = isset( $_POST["nonce_{$id}"] ) ? sanitize_key( $_POST["nonce_{$id}"] ) : '';
			if ( empty( $_POST["nonce_{$id}"] ) || ! wp_verify_nonce( $nonce, "wppf-save-{$id}" ) )
				return;

			// Before save action
			do_action( 'wppf_before_save_user', $user_id );
			do_action( "wppf_{$this->_meta_box['id']}_before_save_user", $user_id );

			// Cycle through each field and save the values.
			foreach ( $this->fields as $field ) {

				$name = $field['id'];
				$old  = get_user_meta( $user_id, $name, true );
				$new  = isset( $_POST[$name] ) ? $_POST[$name] : '';

				// Allow field class change the value
				$new = call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'value' ), $new, $old, $user_id, $field );

				// Call defined method to save meta value, if there's no methods, call common one
				call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'save' ), $new, $old, $user_id, $field, 'user' );

			}

			// After save action
			do_action( 'wppf_after_save_user', $user_id );
			do_action( "wppf_{$this->_meta_box['id']}_after_save_user", $user_id );

		}

		/**
		 * Enqueue common styles
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {

			$screen = get_current_screen();

			if( $screen->base == 'profile' || $screen->base == 'user-edit') {
				$fields = Pretty_Metabox::get_fields( $this->fields );
				foreach ( $fields as $field ) {
					// Enqueue scripts and styles for fields
					call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'admin_enqueue_scripts' ) );
				}
			}

		}

	}

}