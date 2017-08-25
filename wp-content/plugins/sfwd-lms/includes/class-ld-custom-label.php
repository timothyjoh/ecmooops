<?php

/**
* LearnDash Custom Label class
*/
class LearnDash_Custom_Label {
	
	/**
	 * Label fields
	 * @var array
	 */
	private $fields;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->fields   = $this->settings_fields();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'pre_update_option_' . 'learndash_custom_label_settings', array( $this, 'pre_update_option' ), 10, 3 );
		add_action( 'updated_option', array( $this, 'update_option' ), 10 , 3 );
	}

	/**
	 * Enqueue learndash script and style
	 */
	public function admin_enqueue_scripts() {
		global $learndash_assets_loaded;

		if ( ! is_admin() || ((isset($_GET['page'])) && ('learndash_custom_label' != $_GET['page'] ) ) ) {
			return;
		}

		wp_enqueue_style( 
			'learndash_style', 
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/style'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css',
			array(), 
			LEARNDASH_VERSION 
		);
		$learndash_assets_loaded['styles']['learndash_style'] = __FUNCTION__;

		wp_enqueue_style( 
			'sfwd-module-style', 
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/sfwd_module'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', 
			array(), 
			LEARNDASH_VERSION 
		);
		$learndash_assets_loaded['styles']['sfwd-module-style'] = __FUNCTION__;

		wp_enqueue_script( 
			'sfwd-module-script', 
			LEARNDASH_LMS_PLUGIN_URL . 'assets/js/sfwd_module'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', 
			array( 'jquery' ), 
			LEARNDASH_VERSION,
			true 
		);
		$learndash_assets_loaded['scripts']['sfwd-module-script'] = __FUNCTION__;

		wp_localize_script( 'sfwd-module-script', 'sfwd_data', array() );
	}

	/**
	 * Register settings for custom label
	 */
	public function register_setting() {
		register_setting( 'learndash_custom_label_settings_group', 'learndash_custom_label_settings', array( $this, 'sanitize_setting' ) );
	}

	/**
	 * Sanitize setting inputs
	 * @param  array $inputs Settings inputted
	 * @return array         Sanitized settings
	 */
	public function sanitize_setting( $inputs ) {
		$settings = get_option( 'learndash_custom_label_settings', array() );

		foreach ( $inputs as $key => $input ) {
			$inputs[ $key ] = sanitize_text_field( $input );
		}

		return apply_filters( 'learndash_custom_label_sanitized_settings', array_merge( $settings, $inputs ) );
	}

	/**
	 * Register settings page
	 */
	public function admin_menu() {
		add_submenu_page(
			'learndash-lms-non-existant',
			__( 'Custom Labels', 'learndash' ),
			__( 'Custom Labels', 'learndash' ),
			LEARNDASH_ADMIN_CAPABILITY_CHECK,
			'learndash_custom_label',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Output settings page
	 */
	public function admin_page() {
		?>
		<div id="learndash-settings" class="wrap">
			<h1><?php _e( 'Custom Labels', 'learndash' ); ?></h1>
			<form method="post" action="options.php">
				<div class="sfwd_options_wrapper sfwd_settings_left">
					<div id="advanced-sortables" class="meta-box-sortables">
						<div id="sfwd-courses_metabox" class="postbox learndash-settings-postbox">
							<div class="handlediv" title="<?php _e( 'Click to toggle', 'learndash' ); ?>"><br></div>
							<h3 class="hndle"><span><?php _e( 'Custom Labels', 'learndash' ); ?></span></h3>
							<div class="inside">
								<div class="sfwd sfwd_options sfwd-courses_settings">
									<?php settings_fields( 'learndash_custom_label_settings_group' ); ?>
									<?php foreach ( $this->fields as $key => $field ) : ?>
										<?php $field['id'] = $key; ?>

										<div class="sfwd_input " id="sfwd-custom-label_<?php echo $key; ?>">
											<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
												<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('sfwd-custom-label_<?php echo $key; ?>_tip');">
													<img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
													<label class="sfwd_label textinput"><?php echo $field['label']; ?></label>
												</a>
											</span>
											<span class="sfwd_option_input">
												<div class="sfwd_option_div">
													
													<?php $callback = $field['type'] . '_callback'; ?>

													<?php $this->$callback( $field ); ?>

												</div>
												<div class="sfwd_help_text_div" style="display:none" id="sfwd-custom-label_<?php echo $key; ?>_tip"><label class="sfwd_help_text"><?php echo $field['desc']; ?></label></div>
											</span>
											<p style="clear:left"></p>
										</div>

									<?php endforeach; ?>
								</div>
							</div>
						
						</div>
					</div>
				</div>
				<p class="submit" style="clear: both;">
				<?php submit_button( __( 'Update Options &raquo;', 'learndash' ), 'primary', 'submit', false );?>
				<?php submit_button( __( 'Reset to Defaults &raquo;', 'learndash' ), 'primary', 'reset', false );?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Pre Update option hook, Used to capture the reset 
	 * @param  string/array $value    New settings value
	 * @param  string/array $old_value  Old settings value
	 * @param  string $option  Our Option key. Not used 
	 */
	function pre_update_option(	$value, $old_value, $option ) {
		if ( 'learndash_custom_label_settings' == $option ) {
		
			if ((isset($_POST['reset'])) && (!empty($_POST['reset']))) {
				$value = array();
			}
		}		
		return $value;	
	}

	/**
	 * Update option hook, flush rewrite rule
	 * @param  string $option    Option name
	 * @param  array $old_value  Old settings value
	 * @param  array $new_value  New settings value
	 */
	public function update_option( $option, $old_value , $new_value ) {
		if ( 'learndash_custom_label_settings' == $option ) {
			flush_rewrite_rules();
		}
	}

	/**
	 * Define settings fields
	 * @return array Array of settings fields for custom label
	 */
	public function settings_fields() {
		$settings = get_option( 'learndash_custom_label_settings', array() );

		$fields = array(
			'course' => array(
				'name'  => 'course',
				'type'  => 'text',
				'label' => __( 'Course', 'learndash' ),
				'desc'  => __( 'Label to replace "course" (singular).', 'learndash' ),
				'value' => isset( $settings['course'] ) ? $settings['course'] : '',
			),
			'courses' => array(
				'name'  => 'courses',
				'type'  => 'text',
				'label' => __( 'Courses', 'learndash' ),
				'desc'  => __( 'Label to replace "courses" (plural).', 'learndash' ),
				'value' => isset( $settings['courses'] ) ? $settings['courses'] : '',
			),
			'lesson' => array(
				'name'  => 'lesson',
				'type'  => 'text',
				'label' => __( 'Lesson', 'learndash' ),
				'desc'  => __( 'Label to replace "lesson" (singular).', 'learndash' ),
				'value' => isset( $settings['lesson'] ) ? $settings['lesson'] : '',
			),
			'lessons' => array(
				'name'  => 'lessons',
				'type'  => 'text',
				'label' => __( 'Lessons', 'learndash' ),
				'desc'  => __( 'Label to replace "lessons" (plural).', 'learndash' ),
				'value' => isset( $settings['lessons'] ) ? $settings['lessons'] : '',
			),
			'topic' => array(
				'name'  => 'topic',
				'type'  => 'text',
				'label' => __( 'Topic', 'learndash' ),
				'desc'  => __( 'Label to replace "topic" (singular).', 'learndash' ),
				'value' => isset( $settings['topic'] ) ? $settings['topic'] : '',
			),
			'topics' => array(
				'name'  => 'topics',
				'type'  => 'text',
				'label' => __( 'Topics', 'learndash' ),
				'desc'  => __( 'Label to replace "topics" (plural).', 'learndash' ),
				'value' => isset( $settings['topics'] ) ? $settings['topics'] : '',
			),
			'quiz' => array(
				'name'  => 'quiz',
				'type'  => 'text',
				'label' => __( 'Quiz', 'learndash' ),
				'desc'  => __( 'Label to replace "quiz" (singular).', 'learndash' ),
				'value' => isset( $settings['quiz'] ) ? $settings['quiz'] : '',
			),
			'quizzes' => array(
				'name'  => 'quizzes',
				'type'  => 'text',
				'label' => __( 'Quizzes', 'learndash' ),
				'desc'  => __( 'Label to replace "quizzes" (plural).', 'learndash' ),
				'value' => isset( $settings['quizzes'] ) ? $settings['quizzes'] : '',
			),
			'button_take_this_course' => array(
				'name'  => 'button_take_this_course',
				'type'  => 'text',
				'label' => __( 'Take this Course (Button)', 'learndash' ),
				'desc'  => __( 'Label to replace "Take this Course" button.', 'learndash' ),
				'value' => isset( $settings['button_take_this_course'] ) ? $settings['button_take_this_course'] : '',
			),
			'button_mark_complete' => array(
				'name'  => 'button_mark_complete',
				'type'  => 'text',
				'label' => __( 'Mark Complete (Button)', 'learndash' ),
				'desc'  => __( 'Label to replace "Mark Complete" button.', 'learndash' ),
				'value' => isset( $settings['button_mark_complete'] ) ? $settings['button_mark_complete'] : '',
			),
			'button_click_here_to_continue' => array(
				'name'  => 'button_click_here_to_continue',
				'type'  => 'text',
				'label' => __( 'Click Here to Continue (Button)', 'learndash' ),
				'desc'  => __( 'Label to replace "Click Here to Continue" button.', 'learndash' ),
				'value' => isset( $settings['button_click_here_to_continue'] ) ? $settings['button_click_here_to_continue'] : '',
			),
		);

		return apply_filters( 'learndash_custom_label_fields', $fields );
	}

	/**
	 * Callback for text setting fields
	 * @param  array $field Field arguments
	 */
	public function text_callback( $field ) {
		$html  = '<input type="text" name="learndash_custom_label_settings[' . $field['name'] . ']" value="' . $field['value'] . '" class="regular-text">';
		echo $html;
	}

	/**
	 * Get label based on key name
	 * @param  string $key Key name of setting field
	 * @return string      Label entered on settings page
	 */
	public static function get_label( $key ) {
		$labels = get_option( 'learndash_custom_label_settings' );

		switch ( strtolower( $key ) ) {
			case 'course':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Course', 'learndash' );
				break;

			case 'courses':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Courses', 'learndash' );
				break;

			case 'lesson':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Lesson', 'learndash' );
				break;

			case 'lessons':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Lessons', 'learndash' );
				break;

			case 'topic':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Topic', 'learndash' );
				break;

			case 'topics':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Topics', 'learndash' );
				break;

			case 'quiz':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Quiz', 'learndash' );
				break;

			case 'quizzes':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Quizzes', 'learndash' );
				break;

			case 'button_take_this_course':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Take this Course', 'learndash' );
				break;

			case 'button_mark_complete':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Mark Complete', 'learndash' );
				break;

			case 'button_click_here_to_continue':
				$label = ! empty( $labels[ $key] ) ? $labels[ $key ] : __( 'Click Here to Continue', 'learndash' );
				break;
		}

		return $label;
	}

	/**
	 * Get slug-ready string
	 * @param  string $key Key name of setting field
	 * @return string      Lowercase string
	 */
	public static function label_to_lower( $key ) {
		$label = strtolower( self::get_label( $key ) );
		return $label;
	}

	/**
	 * Get slug-ready string
	 * @param  string $key Key name of setting field
	 * @return string      Slug-ready string
	 */
	public static function label_to_slug( $key ) {
		//$label = sanitize_title_with_dashes( strtolower( self::get_label( $key ) ) );
		$label = sanitize_title( self::get_label( $key ) );
		return $label;
	}

	/**
	 * Check if translation is enabled
	 * @return boolean True if enabled, false otherwise
	 */
	/*
	public static function is_lang_set() {
		$lang = get_option( 'WPLANG' );
		
		if ( ! empty( $lang ) ) {
			return true;
		} else {
			return false;
		}
	}
	*/
}

new LearnDash_Custom_Label();