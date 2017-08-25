<?php

/**
 * Creates a special WooCommerce 'Course' Product and dependant functions
 *
 * @TODO Should maybe extend WC_Product
 *			http://wordpress.stackexchange.com/questions/120215/how-to-add-a-new-product-type-on-woocommerce-product-types
 */
class Propel_Course_Product {

	function __construct() {
		add_filter( 'product_type_selector', 
			array( $this, 'add_product_type'), 1, 2 );
		add_action( 'woocommerce_product_options_general_product_data', 
			array( $this, 'render_course_selector' ) );

		add_action( 'admin_enqueue_scripts', 
			array( $this, 'add_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', 
			array( $this, 'add_front_scripts' ) );

		add_action( 'save_post_product', 
			array( $this, 'set_course_product_meta' ), 10, 2 );
	}


	/**
	 * Adds 'Course' as product type 
	 *
	 * @filter	product_type_selector
	 *
	 * @param	Array	$types			The array of available product types
	 * @param	?????	$product_type	Was here when I got here
	 *
	 * @return	Array	$types			The array of available product types
	 */
	function add_product_type( $types, $product_type ) {
		$types['course'] = __( 'Course', 'learndash' );

		return $types;
	}


	/**
	 * Adds necessary script for WooCommerce admin
	 *
	 * @action admin_enqueue_scripts
	 */
	function add_admin_scripts() {
		wp_enqueue_script( 'ld_wc', 
			plugins_url( '/js/learndash_woocommerce.js', __FILE__ ) );
	}


	/**
	 * Adds necessary script for front end
	 *
	 * @action wp_enqueue_scripts
	 */
	function add_front_scripts() {
		wp_enqueue_script( 'ld_wc_front', 
			plugins_url( '/js/front.js', __FILE__ ), array( 'jquery' ) );
	}


	/** 
	 * Renders related courses for product meta in WooCommerce
	 *
	 * @action woocommerce_product_options_general_product_data
	 */
	function render_course_selector() {
		global $post;

		$courses = $this->list_courses();
		echo '<div class="options_group show_if_course">';

		$values = get_post_meta( $post->ID, '_related_course', true );
		if ( ! $values )
			$values = array();

		woocommerce_wp_select( array(
			'id'          => '_related_course[]',
			'label'       => __( 'Related Courses', 'learndash' ),
			'options'     => $courses,
			'desc_tip'    => true,
			'description' => __( 'You can select multiple courses to sell together holding the SHIFT key when clicking.', 'learndash' )
		));

		echo '<script>ldRelatedCourses = ' . json_encode( $values ) . '</script>';

		echo '</div>';
	}


	/**
   * Attaches the product's related courses 
   * and forces virtual/downloadable during save
	 *
	 * @action	save_post_product
	 *
	 * @param	int		$id		The order id
	 * @param	Post	$post	The post object
	 */
	function set_course_product_meta( $id, $post ) {

    // A course product is best/only defined as having a '_related_course'
		if ( isset( $_POST['_related_course'] ) )
			update_post_meta( $id, '_related_course', $_POST['_related_course'] );

    // If it has a '_related_course', always force it as a 'virtual/downloadable' product
    // Virtual/Downloadable products are not 'processed' and got straight to 'completed'
    if ( get_post_meta( $id, '_related_course' ) ) {
      // Would prefer to use update_post_meta as below, 
      // but priority never seems to come after Woocommerce's DB write, 
      // no matter what number I use
      //
      // update_post_meta( $id, '_virtual', 'yes' ); 
      // update_post_meta( $id, '_downloadable', 'yes' ); 
      
      // A hack to always force '_virtual' and '_downloadable' for a course product
      $_POST['_virtual'] = 'yes';
      $_POST['_downloadable'] = 'yes';
    }
	}

	/**
	 * Returns an array of courses according to the current course (post)
	 *
	 * @return	array	$courses	The array of related courses by course id
	 */
	function list_courses() {
		global $post;

		$post_id = $post->ID;
		
		query_posts( 
			array( 
				'post_type' => 'sfwd-courses', 
				'posts_per_page' => -1 
			) 
		);

		$courses = array();

		while ( have_posts() ) {
			the_post(); 

			$courses[ get_the_ID() ] = get_the_title();
		}

		wp_reset_query();

		$post = get_post( $post_id );

		return $courses;
	}
	
}

new Propel_Course_Product();
