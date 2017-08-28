<?php
/**
 * @package wp-pretty-fields
 * @author Alessandro Tesoro
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Pretty_Taxonomy_Metabox') ) {
	
	/**
	 * Pretty metabox class.
	 */
	class Pretty_Taxonomy_Metabox {

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
		 * Holds the form being displayed
		 * either add or edit
		 *
		 * @var object
		 * @access protected
		 */
		protected $_form_type;

		/**
		 * Constructor - get the class hooked in and ready
		 * 
		 * @since 1.0.0
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

			// Run metabox output & save methods
			foreach ( $this->_meta_box['taxonomy'] as $page ) {
				
				//add fields to edit form
      			add_action($page.'_edit_form_fields',array( $this, 'show_edit_form' ));
      			//add fields to add new form
      			add_action($page.'_add_form_fields',array( $this, 'show_new_form' ));
      			// this saves the edit fields
      			add_action( 'edited_'.$page, array( $this, 'save' ), 10, 2 );
		    	// this saves the add fields
		    	add_action( 'created_'.$page,array( $this, 'save' ), 10, 2 );

			}

			//delete term meta on term deletion
    		add_action('delete_term', array( $this, 'delete_term_metadata'), 10, 2 );

			// Enqueue styles and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		}

		/**
		 * Check metabox configuration. Trigger error if configuration is wrong.
		 *
		 * @since 1.0
		 * @access private
		 * @param variable $meta_box 
		 */
		private function check_meta_box( $meta_box ) {

			$errors = new WP_Error();

			// Check that the id exists
			if( ! array_key_exists( 'id', $meta_box ) ) {
				$errors->add( 'metabox-id-missing', sprintf( __('Error: metabox "%s" must have an ID.','wppf', 'wpum'), self::get_id() ) );
			}

			// Check that the taxonomy exists
			if( ! array_key_exists( 'taxonomy', $meta_box ) ) {
				$errors->add( 'metabox-taxonomy-missing', sprintf( __('Error: metabox "%s" must have a taxonomy parameter assigned.','wppf', 'wpum'), self::get_id() ) );
			}

			// Check that the taxonomy exists
			if( !is_array($meta_box['taxonomy']) ) {
				$errors->add( 'metabox-taxonomy-array', sprintf( __('Error: metabox "%s" taxonomy parameter must be an array.','wppf', 'wpum'), self::get_id() ) );
			}

			return $errors;
		}

		/**
		 * Display errors if metabox configuration is wrong.
		 *
		 * @since 1.0
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
		 * @since 1.0.0
		 * @access public
		 * @return string
		 */
		public function get_id() {
			return $this->_meta_box['id'];
		}

		/**
		 * Load metabox on term add page.
		 * And set form type.
		 * 
		 * @since 1.0.0
		 * @param term_id
		 * @return void
		 */
		public function show_new_form( $term_id ) {
			 $this->_form_type = 'new';
			 $this->show_metabox($term_id);
		}

		/**
		 * Load metabox on term edit page.
		 * And set form type.
		 * 
		 * @since 1.0.0
		 * @param term_id
		 * @return void
		 */
		public function show_edit_form( $term_id ) {
			 $this->_form_type = 'edit';
			 $this->show_metabox($term_id);
		}

		/**
		 * Show metabox on term edit page.
		 * 
		 * @since 1.0.0
		 * @param term_id
		 * @return void
		 */
		public function show_metabox( $term_id ) {

			wp_nonce_field( "wppf-save-{$this->_meta_box['id']}", "nonce_{$this->_meta_box['id']}" );

			do_action( 'wppf_before_taxonomy', $term_id );
			do_action( "wppf_before_taxonomy_{$this->_meta_box['id']}", $term_id );

			foreach ( $this->fields as $field ) {

				// Display content before markup of the single field
				echo Pretty_Fields::before_field_taxonomy($field, $this->_form_type);

				// Run actions before field markup
				do_action( 'wppf_before_taxonomy_field', $field, $term_id );
				do_action( "wppf_before_taxonomy_field_{$field['type']}", $field, $term_id );
				do_action( "wppf_before_taxonomy_field_{$field['id']}", $field, $term_id );

				// Get single field markup
				$meta = $this->get_tax_meta( $term_id, $field['id'] );
    			$meta = ( $meta !== '' ) ? $meta : (isset($field['std'])? $field['std'] : '');
				call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'show_taxonomy' ), $field, $meta );

				// Run actions after field markup
				do_action( 'wppf_after_taxonomy_field', $field, $term_id );
				do_action( "wppf_after_taxonomy_field_{$field['type']}", $field, $term_id );
				do_action( "wppf_after_taxonomy_field_{$field['id']}", $field, $term_id );

				// Display content before markup of the single field
				echo Pretty_Fields::after_field_taxonomy($field, $this->_form_type);

			}

			do_action( 'wppf_after_taxonomy', $term_id );
			do_action( "wppf_after_taxonomy_{$this->_meta_box['id']}", $term_id );

		}

		/**
		 * Save data from meta box
		 *
		 * @param int $term_id Term ID
		 * @since 1.0.0
		 * @return void
		 */
		public function save( $term_id ) {

			// Check if the we are coming from quick edit.
		    if (isset($_REQUEST['action'])  &&  $_REQUEST['action'] == 'inline-save-tax') {
		      return $term_id;
		    }

		    // Check Revision
		    // Check if current taxonomy type is set.
		    // Check if current taxonomy type is supported.
		    // Check permission
		    if ( ! isset( $term_id )                            
		    || ( ! isset( $_POST['taxonomy'] ) )              
		    || ( ! in_array( $_POST['taxonomy'], $this->_meta_box['taxonomy'] ) )              
		    || ( ! current_user_can('manage_categories') ) )                 
		    {
		      return $term_id;
		    }

		    // Check whether form is submitted properly
			$id    = $this->_meta_box['id'];
			$nonce = isset( $_POST["nonce_{$id}"] ) ? sanitize_key( $_POST["nonce_{$id}"] ) : '';
			if ( empty( $_POST["nonce_{$id}"] ) || ! wp_verify_nonce( $nonce, "wppf-save-{$id}" ) )
				return;

			// Cycle through each field and save the values.
			foreach ( $this->fields as $field ) {

				$name = $field['id'];
				$type = $field['type'];
				$old = $this->get_tax_meta( $term_id, $name );
				$new  = isset( $_POST[$name] ) ? $_POST[$name] : '';

				// Allow field class change the value
				$new = call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'value' ), $new, $old, $term_id, $field );

				//Call defined method to save meta value, if there's no methods, call common one
				call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'save' ), $new, $old, $term_id, $field, 'taxonomy' );

			}

		}

		/**
		 * Retrieve data from taxonomy term
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		static function get_tax_meta( $term_id, $key ) {

			$t_id = (is_object($term_id))? $term_id->term_id: $term_id;
			$m = get_option( 'tax_meta_'.$t_id);

			if ( isset($m[$key]) ){
				return $m[$key];
		    } else{
		    	return '';
		    }

		}

		/**
		 * Delete data from taxonomy term
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		static function delete_tax_meta( $term_id, $key ) {
			$m = get_option( 'tax_meta_'.$term_id );
			if ( isset($m[$key]) ){
		      unset($m[$key]);
		    }
		    update_option( 'tax_meta_'.$term_id, $m );
		}
		
		/**
		 * Update data from taxonomy term
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		static function update_tax_meta( $term_id, $key, $value ) {
			$m = get_option( 'tax_meta_'.$term_id);
			$m[$key] = $value;
			update_option( 'tax_meta_'.$term_id, $m );
		}

		/**
		 * Delete term meta options on term delete
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		static function delete_term_metadata( $term, $term_id ) {
			delete_option( 'tax_meta_'.$term_id );
		}

		/**
		 * Enqueue common styles
		 *
		 * @return void
		 */
		function admin_enqueue_scripts() {

			$screen = get_current_screen();

			// Enqueue scripts and styles for registered pages (post types) only
			if ( ! in_array( $screen->taxonomy, $this->_meta_box['taxonomy'] ) )
				return;

			$fields = Pretty_Metabox::get_fields( $this->fields );

			foreach ( $fields as $field ) {
				// Enqueue scripts and styles for fields
				call_user_func( array( Pretty_Metabox::get_class_name( $field ), 'admin_enqueue_scripts' ) );
			}

		}

	}

}