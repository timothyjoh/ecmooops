<?php

function override_sfwd_lms_has_access(  $orig_val ,$post_id, $user_id){ 
  
  // Set variables
  if ( empty( $user_id ) )
    $user_id = get_current_user_id();

  $course_id = learndash_get_course_id( $post_id );

  
  // Allow if admin
  //  if ( user_can( $user_id, 'manage_options' ) )
  //    return true;

  // Allow if course doesn't exist?
  if ( empty( $course_id ) )
    return true;

  // Allow if what?
  if ( ! empty( $post_id ) && learndash_is_sample( $post_id ) ) {
    return true;
  }

  $meta = get_post_meta( $course_id, '_sfwd-courses', true );

  if ( @$meta['sfwd-courses_course_price_type'] == "open" )
    return true;

  if ( empty( $user_id ) )
    return false;

  global $wpdb;
  $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

  // Will return 0 or 1 depending if record exists
  $count = $wpdb->get_var( "
                  SELECT COUNT(*) 
                  FROM $propel_table 
                  WHERE user_id = $user_id
                    AND post_id = $course_id
                    AND expiration_date > NOW()
                " );

  if ( $count > 0 ) {
    return true;
  }
  else {
    return false;
  }
}

function get_active_enrollment_key( $user_id, $course_id ) {

  // Set variables
  if ( empty( $user_id ) )
    $user_id = get_current_user_id();

  // Allow if course doesn't exist?
  if ( empty( $course_id ) )
    return 'COURSE_NO_EXIST';

  if ( empty( $user_id ) )
    return 'USER_NO_EXIST';

  global $wpdb;
  $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

  // Will return 0 or 1 depending if record exists
  $keycode = $wpdb->get_var( "
                  SELECT activation_key
                  FROM $propel_table
                  WHERE user_id = $user_id
                    AND post_id = $course_id
                  ORDER BY id DESC
                  LIMIT 1
                " );

  return $keycode;
}


$if_shortcode_filter_prefix='evaluate_condition_';
$if_shortcode_block=NULL;

add_shortcode('if','process_if_shortcode');

function process_if_shortcode($atts,$content)
  {
  global $if_shortcode_filter_prefix;
  $false_strings=array('0','','false','null','no');
  $atts=normalize_empty_atts($atts);
  $result=false;
  foreach($atts as $condition=>$val) 
    {
    $mustbe=!in_array($val,$false_strings,true); // strict, or else emty atts don't work as expected
    $evaluate=apply_filters("{$if_shortcode_filter_prefix}{$condition}",false);
    $result|=$evaluate==$mustbe;
    }
  global $if_shortcode_block;
  $save_block=$if_shortcode_block;
  $if_shortcode_block=array('result'=>$result,'else'=>'',);
  $then=do_shortcode($content);
  $else=$if_shortcode_block['else'];
  $if_shortcode_block=$save_block;
  return $result?$then:$else;
  }
  
add_shortcode('else','process_else_shortcode');

function process_else_shortcode($atts,$content)
  {
  global $if_shortcode_block;
  if($if_shortcode_block&&!$if_shortcode_block['result'])
    $if_shortcode_block['else'].=do_shortcode($content);
  return '';
  }
  
add_shortcode('eitherway','process_eitherway_shortcode');

function process_eitherway_shortcode($atts,$content)
  {
  $content=do_shortcode($content);
  global $if_shortcode_block;
  if($if_shortcode_block) $if_shortcode_block['else'].=$content;
  return $content;
  }
  
// add supported conditional tags
add_action('init','if_shortcode_conditional_tags');

function if_shortcode_conditional_tags()
  {
  $supported=array(
    'is_single',
    'is_singular',
    'is_page',
    'is_home',
    'is_front_page',
    'is_category',
    'is_tag',
    'is_tax',
    'is_sticky',
    'is_author',
    'is_archive',
    'is_year',
    'is_month',
    'is_day',
    'is_time',
    'is_feed',
    'is_search',
    'comments_open',
    'pings_open',
    'is_404',
    'is_user_logged_in',
    'is_super_admin',
    );
  global $if_shortcode_filter_prefix;
  foreach($supported as $tag) add_filter("{$if_shortcode_filter_prefix}{$tag}",$tag);
  }

// normalize_empty_atts found here: http://wordpress.stackexchange.com/a/123073/39275
function normalize_empty_atts($atts) 
  {
  foreach($atts as $attribute=>$value) 
    {
    if(is_int($attribute))
      {
      $atts[strtolower($value)]=true;
      unset($atts[$attribute]);
      }
    }
  return $atts;
  }
  

add_filter($if_shortcode_filter_prefix.'has_active_enrollments','has_active_enrollments_evaluator');

function has_active_enrollments_evaluator($value)
  {
    $user_id = get_current_user_id();

    global $wpdb;
    $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

    // Will return 0 or 1 depending if record exists
    $count = $wpdb->get_var( "
                  SELECT COUNT(*) 
                  FROM $propel_table 
                  WHERE user_id = $user_id
                    AND expiration_date > NOW()
                " );
    error_log("User ".$user_id. " is enrolled in ".$count." courses");

    if ( intval($count) > 0 ) {
      return true;
    }
    else {
      return false;
    }
  }

add_filter($if_shortcode_filter_prefix.'is_enrolled_in_this_course','is_enrolled_in_this_course_evaluator');

function is_enrolled_in_this_course_evaluator($value)
  {
    $user_id = get_current_user_id();
    $post_id = get_the_ID();

    global $wpdb;
    $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

    // Will return 0 or 1 depending if record exists
    $count = $wpdb->get_var( "
                  SELECT COUNT(*) 
                  FROM $propel_table 
                  WHERE user_id = $user_id
                    AND post_id = $post_id
                    AND expiration_date > NOW()
                " );
    error_log("User ".$user_id. " is enrolled in ".$post_id." = ".$count);

    if ( intval($count) > 0 ) {
      return true;
    }
    else {
      return false;
    }
  }

add_filter($if_shortcode_filter_prefix.'is_completed_in_this_course','is_completed_in_this_course_evaluator');

function is_completed_in_this_course_evaluator($value)
  {
    $user_id = get_current_user_id();

    global $wpdb;
    $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

    // Will return 0 or 1 depending if record exists
    $count = $wpdb->get_var( "
                  SELECT COUNT(*) 
                  FROM $propel_table 
                  WHERE user_id = $user_id
                    AND expiration_date > NOW()
                " );
    error_log("User ".$user_id. " is enrolled in ".$count." courses");

    if ( intval($count) > 0 ) {
      return true;
    }
    else {
      return false;
    }
  }
