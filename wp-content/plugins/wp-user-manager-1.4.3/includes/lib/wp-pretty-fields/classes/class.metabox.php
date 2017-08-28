<?php
/**
 * @package wp-pretty-fields
 * @author Alessandro Tesoro
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Pretty_Metabox') ) {
	
	/**
	 * Pretty metabox class.
	 */
	class Pretty_Metabox {

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
		 * @var bool Used to prevent duplicated calls like revisions, manual hook to wp_insert_post, etc.
		 */
		public $saved = false;

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
		    $this->default_metabox_args();
		    $this->fields = &$this->_meta_box['fields'];

		    // Add additional actions for fields
			$fields = self::get_fields( $this->fields );
			foreach ( $fields as $field ) {
				call_user_func( array( self::get_class_name( $field ), 'add_actions' ) );
			}
		    
		    // Load Metaboxes
    		add_action( 'add_meta_boxes', array( $this, 'load_metaboxes' ) );

    		// Display error messages
    		add_action( 'admin_notices', array( $this, 'display_errors' ) );

    		// Save post meta
			add_action( 'save_post', array( $this, 'save_post' ) );

			// Add custom classes to metabox
			$post_type_object = $this->_meta_box['pages'];
			foreach ($post_type_object as $page) {
				add_filter( "postbox_classes_{$page}_{$this->get_id()}", array( $this, 'add_class_to_metabox' ) );
			}

			// Enqueue styles and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

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
		 * Get the metabox title.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @return string
		 */
		public function get_title() {
			return $this->_meta_box['title'];
		}

		/**
		 * Get the metabox context.
		 * 
		 * @since 1.0.0
		 * @access public
		 */
		public function get_context() {
			return $this->_meta_box['context'];
		}

		/**
		 * Get the metabox priority.
		 * 
		 * @since 1.0.0
		 * @access public
		 */
		public function get_priority() {
			return $this->_meta_box['priority'];
		}

		/**
		 * Check if metabox has tabs.
		 * 
		 * @since 1.0.0
		 * @access public
		 */
		public function has_tabs() {
			return $this->_meta_box['has_tabs'];
		}

		/**
		 * Get all fields of a meta box, recursively
		 *
		 * @param array $fields
		 * @return array
		 * @since 1.0.0
		 */
		static function get_fields( $fields ) {
			$all_fields = array();

			foreach ( $fields as $field ) {
				$all_fields[] = $field;
				if ( isset( $field['fields'] ) )
					$all_fields = array_merge( $all_fields, self::get_fields( $field['fields'] ) );
			}

			return $all_fields;
		}

		/**
		 * Add the metabox.
		 *
		 * @since 1.0
		 * @access public
		 * @param variable $post_type 
		 */
		public function load_metaboxes($post_type) {
			
			$check_metabox = $this->check_meta_box( $this->_meta_box );

			if( !empty( $check_metabox->errors ) ) {
				$this->errors = $check_metabox;
				return;
			}

			if(in_array($post_type, $this->_meta_box['pages'])){
				
				add_meta_box(
					self::get_id(),
					self::get_title(),
					array( $this, 'show_metaboxes' ),
					$post_type,
					self::get_context(),
					self::get_priority()
				);

			}

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
				$errors->add( 'metabox-id-missing', __('Error: metabox must have an id.','wppf', 'wpum') );
			}

			// Check that the title exists
			if( ! array_key_exists( 'title', $meta_box ) ) {
				$errors->add( 'metabox-title-missing', sprintf( __('Error: metabox "%s" must have a title.','wppf', 'wpum'), $meta_box['id'] ) );
			}

			return $errors;
		}

		/**
		 * Show the metabox.
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @todo Allow users to add custom code before meta box content through filters
		 */
		public function show_metaboxes() {

			global $post;

			$saved = self::has_been_saved( $post->ID, $this->fields );

			wp_nonce_field( "wppf-save-{$this->_meta_box['id']}", "nonce_{$this->_meta_box['id']}" );

			do_action( 'wppf_before', $this );
			do_action( "wppf_before_{$this->_meta_box['id']}", $this );

			self::before_fields();

			// Display regular fields markup only for regular metaboxes.
			// Dispaly simple markup depending on the context.
			if($this->get_context() == 'side') {

				foreach ( $this->fields as $field ) {
					
					// Display content before markup of the single field
					echo Pretty_Fields::before_field_simple($field);
					
					// Run actions before field markup
					do_action( 'wppf_before_field', $field );
					do_action( "wppf_before_field_{$field['type']}", $this );
					do_action( "wppf_before_field_{$field['id']}", $this );
					
					// Get single field markup
					call_user_func( array( self::get_class_name( $field ), 'show' ), $field, $saved );

					// Run actions after field markup
					do_action( 'wppf_after_field', $field );
					do_action( "wppf_after_field_{$field['type']}", $this );
					do_action( "wppf_after_field_{$field['id']}", $this );
					
					// Display content after markup of the single field.
					echo Pretty_Fields::after_field_simple($field);

				}

			} else {

				foreach ( $this->fields as $field ) {
					
					// Display content before markup of the single field
					echo Pretty_Fields::before_field( $field, $this->has_tabs() );
					
					// Run actions before field markup
					do_action( 'wppf_before_field', $field );
					do_action( "wppf_before_field_{$field['type']}", $this );
					do_action( "wppf_before_field_{$field['id']}", $this );

					// Get single field markup
					call_user_func( array( self::get_class_name( $field ), 'show' ), $field, $saved );

					// Run actions after field markup
					do_action( 'wppf_after_field', $field );
					do_action( "wppf_after_field_{$field['type']}", $this );
					do_action( "wppf_after_field_{$field['id']}", $this );

					// Display content after markup of the single field.
					echo Pretty_Fields::after_field( $field, $this->has_tabs() );
				}

			}

			self::after_fields();

			do_action( 'wppf_after', $this );
			do_action( "wppf_after_{$this->_meta_box['id']}", $this );

		}

		/**
		 * Add default args for the add_meta_box function.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function default_metabox_args() {
			
    		$this->_meta_box = array_merge( array( 'context' => 'normal', 'priority' => 'high', 'pages' => array( 'post' ), 'has_tabs' => false ), (array)$this->_meta_box );

    		// Set default values for fields
			$this->_meta_box['fields'] = self::normalize_fields( $this->_meta_box['fields'] );

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
		 * Get field class name
		 *
		 * @param array $field Field array
		 * @return bool|string Field class name OR false on failure
		 * @since 1.0
		 */
		static function get_class_name( $field ) {
			
			// Convert underscores to whitespace so ucwords works as expected. Otherwise: plupload_image -> Plupload_image instead of Plupload_Image
			$type = str_replace( '_', ' ', $field['type'] );

			// Uppercase first words
			$class = 'WPPF_' . ucwords( $type ) . '_Field';

			// Relace whitespace with underscores
			$class = str_replace( ' ', '_', $class );
			return class_exists( $class ) ? $class : false;

		}

		/**
		 * Show custom markup before fields
		 *
		 * @since 1.0
		 */
		public function before_fields() {
			
			$output = '<div class="wppf-metabox-container"><table class="options-table-responsive wppf-options-table" style="display: table;">';
			echo $output;

		}

		/**
		 * Show custom markup after fields
		 *
		 * @since 1.0
		 */
		public function after_fields() {
			
			$output = '</table></div>';
			echo $output;

		}

		/**
		 * Save data from meta box
		 *
		 * @param int $post_id Post ID
		 * @return void
		 */
		function save_post( $post_id ) {

			// Check if this function is called to prevent duplicated calls like revisions, manual hook to wp_insert_post, etc.
			if ( true === $this->saved ) 
				return;
			$this->saved = true;

			// Check whether form is submitted properly
			$id    = $this->_meta_box['id'];
			$nonce = isset( $_POST["nonce_{$id}"] ) ? sanitize_key( $_POST["nonce_{$id}"] ) : '';
			if ( empty( $_POST["nonce_{$id}"] ) || ! wp_verify_nonce( $nonce, "wppf-save-{$id}" ) )
				return;

			// Autosave
			if ( defined( 'DOING_AUTOSAVE' ) && ! $this->meta_box['autosave'] )
				return;

			// Make sure meta is added to the post, not a revision
			if ( $the_post = wp_is_post_revision( $post_id ) )
				$post_id = $the_post;

			// Before save action
			do_action( 'wppf_before_save_post', $post_id );
			do_action( "wppf_{$this->_meta_box['id']}_before_save_post", $post_id );

			// Cycle through each field and save the values.
			foreach ( $this->fields as $field ) {

				$name = $field['id'];
				$old  = get_post_meta( $post_id, $name, true );
				$new  = isset( $_POST[$name] ) ? $_POST[$name] : '';

				// Allow field class change the value
				$new = call_user_func( array( self::get_class_name( $field ), 'value' ), $new, $old, $post_id, $field );

				// Call defined method to save meta value, if there's no methods, call common one
				call_user_func( array( self::get_class_name( $field ), 'save' ), $new, $old, $post_id, $field, 'post' );

			}

			// After save action
			do_action( 'wppf_after_save_post', $post_id );
			do_action( "wppf_{$this->_meta_box['id']}_after_save_post", $post_id );

		}

		/**
		 * Check if meta box has been saved
		 * This helps saving empty value in meta fields (for text box, check box, etc.)
		 *
		 * @param int   $post_id
		 * @param array $fields
		 *
		 * @return bool
		 */
		static function has_been_saved( $post_id, $fields ) {

			foreach ( $fields as $field ) {
				$value = get_post_meta( $post_id, $field['id'], true );
				if ( '' !== $value ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Normalize an array of fields
		 *
		 * @param array $fields Array of fields
		 * @return array $fields Normalized fields
		 */
		static function normalize_fields( $fields ) {

			foreach ( $fields as &$field ) {
				$field = wp_parse_args( $field, array(
					'id'          => '',
					'std'         => '',
					'desc'        => '',
					'sub'         => '',
					'name'        => isset( $field['id'] ) ? $field['id'] : '',
					'placeholder' => '',
					'classes'     => 'wppf-field'
				) );

				// Allow field class add/change default field values
				$field = call_user_func( array( self::get_class_name( $field ), 'normalize_field' ), $field );

				if ( isset( $field['fields'] ) )
					$field['fields'] = self::normalize_fields( $field['fields'] );
			}

			return $fields;

		}

		/**
		 * Add custom class to metaboxes.
		 *
		 * @param array $classes
		 * @return array $classes
		 */
		public function add_class_to_metabox($classes) {
		    array_push($classes,'wppf_metabox');
		    return $classes;
		}

		/**
		 * Enqueue common styles
		 *
		 * @return void
		 */
		function admin_enqueue_scripts() {

			$screen = get_current_screen();

			// Enqueue scripts and styles for registered pages (post types) only
			if ( 'post' != $screen->base || ! in_array( $screen->post_type, $this->_meta_box['pages'] ) )
				return;

			$fields = self::get_fields( $this->fields );

			foreach ( $fields as $field ) {
				// Enqueue scripts and styles for fields
				call_user_func( array( self::get_class_name( $field ), 'admin_enqueue_scripts' ) );
			}

		}

	}

}