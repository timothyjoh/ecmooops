<?php

add_action('widgets_init',
     create_function('', 'return register_widget( "Propel_LMS_Widget" );')
);
class Propel_LMS_Widget extends WP_Widget {


	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
		parent::__construct(
			'propel_lms_widget', // Base ID
			__('PROPEL LMS', 'propel-lms'), // Name
			array( 'description' => __( 'The PROPEL Widget', 'propel-lms' ), ) // Args
		);

    wp_register_script( 'propel-widget', plugins_url( 'js/widget.js', __FILE__ ), array( 'jquery' ) );
	}


	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
    wp_enqueue_script( 'propel-widget' );
    wp_localize_script( 'propel-widget', 'widgetData', 
      array( 
        'user_is_logged_in' => is_user_logged_in(),
        'url' => get_bloginfo( 'url' )
      ) );

		if ( isset($instance) ) extract($instance);

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		// If there is a title, print it out 
		if ( !empty($title) )
			echo $args['before_title'] . $title . $args['after_title']; ?>

    <p>
      <input type="text" id="okm-key-input" class="wide-fat" placeholder="key" />
      <span class="error message" style="display:none;"></span>
    </p>

    <?php if ( ! isset( $submit_text ) ) $submit_text = 'Submit'; ?>

    <input type="submit" id="okm-key-submit" value="<?php echo $submit_text; ?>" />

		<?php echo $args['after_widget']; ?> 

    <style>
      .error {
        border-color: red;
        color: red;
      }
    </style>

<?php
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		if ( isset($instance) ) extract($instance); ?>

		<p><?php // Standard Title form ?>
			<label for="<?php echo $this->get_field_id('title');?>">Title:</label> 
			<input  type="text"
				  class="widefat"
				  id="<?php echo $this->get_field_id('title'); ?>"
				  name="<?php echo $this->get_field_name('title'); ?>"
				  value="<?php if ( isset($title) ) echo esc_attr($title); ?>" />
    </p>
    <p>
			<label for="<?php echo $this->get_field_id('submit_text');?>">Submit Text:</label> 
			<input  type="text"
				  class="widefat"
				  id="<?php echo $this->get_field_id('submit_text'); ?>"
				  name="<?php echo $this->get_field_name('submit_text'); ?>"
				  value="<?php if ( isset($submit_text) ) echo esc_attr($submit_text); ?>" />
    </p><?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = $old_instance;
   
		// Fields
 		$instance['title'] = strip_tags($new_instance['title']);
 		$instance['submit_text'] = strip_tags($new_instance['submit_text']);
  
		return $instance;
	}
}
