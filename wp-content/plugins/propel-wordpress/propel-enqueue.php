<?php

//add_action('wp_enqueue_scripts', 'propel_CDNJS_scripts');
function propel_CDNJS_scripts() 
{
  wp_enqueue_script('eqcss-polyfill','https://cdnjs.cloudflare.com/ajax/libs/eqcss/1.2.1/EQCSS-polyfills.min.js', array(), false, false );
  wp_script_add_data( 'eqcss-polyfill', 'conditional', 'lt IE 9' );

  wp_enqueue_script('eqcss', plugins_url( '/js/eqcss.min.js', __FILE__ ), array(), false, false);
} 

function propel_enqueue_OKG_css(){
	if( current_user_can('administer_okg') ) {  
		//wp_enqueue_style( 'okg-styles', 'plugin_dir_url(__FILE__)/css'  );
		wp_enqueue_style( 'okg-styles', plugins_url( '/css/okg.css', __FILE__ ) );
		//wp_enqueue_style( 'okg-styles', plugins_url( __FILE__ ) . '/css/okg.css' );
	}
}
add_action('wp_enqueue_scripts', 'propel_enqueue_OKG_css');

