<?php
/**
 * Demo Metabox for Pretty_Fields
 */

if (is_admin()) {

  	/* 
	 * configure your meta box
	 */
	$config = array(
		'id'    => 'demo_meta_box',
		'title' => 'Demo Fields',
		'pages' => array('page'),
		'fields' => array(
			array(
				'id'   => 'text',
				'name' => __( 'Text Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'text'
			),
			array(
				'id'   => 'textarea',
				'name' => __( 'Textarea Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'textarea'
			),
			array(
				'id'   => 'url',
				'name' => __( 'URL Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'url'
			),
			array(
				'id'   => 'number',
				'name' => __( 'Number Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'number'
			),
			array(
				'id'   => 'email',
				'name' => __( 'Email Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'email'
			),
			array(
				'id'   => 'button',
				'name' => __( 'Button Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'url' => 'http://google.com',
				'type' => 'button'
			),
			array(
				'id'   => 'color',
				'name' => __( 'Color Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'color'
			),
			array(
				'id'   => 'image',
				'name' => __( 'Image Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'image'
			),
			array(
				'id'   => 'select',
				'name' => __( 'Select Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'select',
				'options' => array('value' => 'label', 'value1' => 'Another label')
			),
			array(
				'id'   => 'radio',
				'name' => __( 'Radio Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'radio',
				'options' => array('value' => 'label', 'value1' => 'Another label')
			),
			array(
				'id'   => 'multiselect',
				'name' => __( 'Multiselect Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'multiselect',
				'options' => array('value' => 'label', 'value1' => 'Another label')
			),
			array(
				'id'   => 'checkbox_list',
				'name' => __( 'Checkbox list Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'checkbox_list',
				'options' => array('value' => 'label', 'value1' => 'Another label')
			),
			array(
				'id'   => 'checkbox',
				'name' => __( 'Checkbox Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'editor',
				'name' => __( 'Editor Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'editor'
			),
			array(
				'id'   => 'gallery',
				'name' => __( 'Gallery Field', 'wpum' ),
				'sub' => __( 'Description goes here', 'wpum' ),
				'desc' => __( 'Field Description goes here', 'wpum' ),
				'type' => 'gallery'
			),
		),
	);

	/*
	 * Initiate your meta box
	 */
  	$demo_meta_box =  new Pretty_Metabox($config);

}