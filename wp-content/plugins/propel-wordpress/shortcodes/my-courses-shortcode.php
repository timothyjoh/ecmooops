<?php


/**
 * Shortcode to list expired courses
 * 
 * @since 2.1.0
 * 
 * @param  array  $attr   shortcode attributes
 * @return string       shortcode output
 */
function propel_expired_courses_shortcode( $attr ) {
  
  global $post;
  $user_id = get_current_user_id();
  global $wpdb;
  $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;
  // Will return 0 or 1 depending if record exists
  wp_enqueue_script('jQuery');
  wp_enqueue_script( 'certificate_launch',  plugins_url() . '/propel-wordpress/js/shortcodes/certificate_launch.js', array(), null, true );

  $post_ids = $wpdb->get_col( "
                SELECT post_id 
                FROM $propel_table 
                WHERE user_id = $user_id
                  AND expiration_date < NOW()
              " );

  if ( ! empty( $post_ids) ){
    foreach( $post_ids as $post_id ) {
      propel_render_my_courses_list($post_id, false);
    }
  } else {
    echo "You don't have any expired courses. When you do, they will show up here";
  }
}

/**
 * Shortcode to list courses
 * 
 * @since 2.1.0
 * 
 * @param  array 	$attr 	shortcode attributes
 * @return string   		shortcode output
 */
function propel_my_courses_shortcode( $attr ) {
	
  global $post;
  $user_id = get_current_user_id();
  global $wpdb;
  $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;
  // Will return 0 or 1 depending if record exists
  wp_enqueue_script('jQuery');
  wp_enqueue_script( 'easy_launch_xapi',  plugins_url() . '/propel-wordpress/js/shortcodes/easy_launch_xapi.js', array(), null, true );
  wp_enqueue_script( 'certificate_launch',  plugins_url() . '/propel-wordpress/js/shortcodes/certificate_launch.js', array(), null, true );

  $post_ids = $wpdb->get_col( "
                SELECT e.post_id 
                FROM $propel_table e
                LEFT JOIN wp_posts p ON p.id = e.post_id
                WHERE e.user_id = $user_id
                AND e.expiration_date > NOW()
                ORDER BY e.activation_date DESC, p.menu_order ASC
              " );

  foreach( $post_ids as $post_id ) {
    propel_render_my_courses_list($post_id, true);
  }
  if (empty($post_ids)){
    ?>
    <div class="OoopsNotice">
    <p>You aren't enrolled in any courses! <a href="/course-catalog/">Click here to purchase a course!</a></p>
    <p>Already have a key? <a href="/activate-key/">Click here to activate a key!</a></p>
    </div>
    <?php
  }
}

add_shortcode( 'expired_courses', 'propel_expired_courses_shortcode' );
add_shortcode( 'my_courses', 'propel_my_courses_shortcode' );


function propel_render_my_courses_list($post_id, $active) {

  $post = get_post( $post_id );
  $claimable = 1;
  
  $options = get_option('sfwd_cpt_options');
  ?>
    <div class="ld_course_grid remodal-bg">
    <article id="post-<?php echo $post_id; ?>" <?php //post_class('thumbnail course'); ?>> 
      <?php $post_image_id = get_post_thumbnail_id($post->ID);
      if ($post_image_id) {
        $thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
        if ($thumbnail) (string)$thumbnail = $thumbnail[0];
      }?>
      <a class="CourseGridImage_wrapper" href="<?php if($active) { get_post_permalink($post,false,false); } ?>" rel="bookmark">
        <div class="CourseGridImage" style="background-image:url('<?php echo $thumbnail?>')"></div>
      </a>
      <div class="infoSection">
        <div class="CourseGridTitle"> 
          <a href="<?php if($active) { get_post_permalink($post,false,false); } ?>" rel="bookmark">
          <h3><?php echo $post->post_title; ?></h3>
          </a>
        </div>
        <?php 
        $user_id = get_current_user_id();
        $user_courses = ld_get_mycourses($user_id);
        $usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );    
        $progress = learndash_course_progress(array("user_id" => $user_id, "course_id" => $post_id, "array" => true));?>
        
    <div class="overview">
      <div class= "progressWrapper">
        <dd class="course_progress" title="<?php echo sprintf("%s out of %s lessons completed",$progress["completed"], $progress["total"]); ?>">
         
          <div class="course_progress_blue" style="width: <?php echo $progress["percentage"]; ?>%;">
            <span class="percentCompleteLable"><?php echo sprintf("%s%% Complete", $progress["percentage"]); ?></span> 
          </div> 
        </dd>
      </div><!-- end .table-cell .progressWrapper -->
    </div><!-- end .overview -->
  </div> <!-- end .infoSection -->  

      <div class="btnWrapper"> 
        <div class="caption">

        <?php 
          if($active) { 
            echo scitent_render_xapi_button($post_id, $progress["percentage"]);
          } 

          if ($progress["percentage"] ==100) {
            echo link_to_certificate($post_id, $user_id, $claimable, get_field("certificate_button_label", $post_id));
          }
        ?>
         </div><!-- end .caption -->
      </div><!-- end .btnWrapper -->
    </article>
  </div><!--end .ld_course_grid -->
  <hr class="myCoursesHR"/>
<?php 
 // end the function
}

function link_to_certificate($post_id, $user_id, $claimable, $button_label) {
  if( get_field('embed_code', $post_id) ){
    $link = link_to_propelokm_certificate($post_id, $claimable, $button_label);
  } else {
    $link = link_to_learndash_certificate($post_id, $user_id, $button_label);
  }
  if ($link == null) {
    return "No certificate available";
  } else {
    return $link;
  }
}
function link_to_learndash_certificate($post_id, $user_id, $button_label) {
  $cert_details = new_learndash_certificate_details($post_id);
  $course_meta = get_post_meta($post_id);
  $course_array = get_post_meta($post_id,'_sfwd-courses');
  $cert_id = $course_array[0]['sfwd-courses_certificate'];
  $cert_path = get_post_permalink($cert_id);
  $course_certficate_link = new_learndash_get_course_certificate_link( $course_id, $user_id );
  $new_course_certficate_link = $cert_path ."?". "course_id=" . $post_id . "&user_id=" . $user_id;

  $quiz_id = $course_array[0]['sfwd-courses_wdm_final_quiz'];
  $quiz_meta = get_post_meta($quiz_id);
  $quiz_array = $quiz_meta['_sfwd-quiz'];
  $unser_quiz_array = maybe_unserialize($quiz_array[0]);
  $quiz_cert_id = $unser_quiz_array['sfwd-quiz_certificate'];
  $quiz_cert_path = get_post_permalink($quiz_cert_id);
  $new_quiz_certficate_link = $quiz_cert_path ."?". "quiz=" . $quiz_id . "&print=" . wp_create_nonce( $quiz_id . $user_id );
  if ($cert_id != 0){
    $url = $new_course_certficate_link;
  } elseif ($quiz_cert_id != 0) {
    $url = $new_quiz_certficate_link;
  }
  if (isset($url)) {
    return "<a href='$url' class='cert-button push-bottom' target='_blank'>".$button_label."</a>";
  } else {
    return null;
  }
}
function link_to_propelokm_certificate($post_id, $claimable, $button_label) {
  $embedCode = get_field('embed_code', $post_id);
  $embedThis = " embed_code='".$embedCode . "' course='" . $post_id . "' button_label='" . $button_label . "' claimable='" . $claimable . "'";
  return do_shortcode('[propel-certificate ' . $embedThis . ']');
}


function scitent_render_xapi_button( $post_id, $percentage ) {
	if ($percentage == 0) {
		$button_label = "Access Course";
	} else if ($percentage == 100) {
		$button_label = "View Completed Course";
	} else {
		$button_label = "Resume Course";
	}

  global $wpdb;
  // var_dump($post_id);
  $lesson_ids = $wpdb->get_col( "
                            SELECT post_id
                            FROM wp_postmeta 
                            WHERE meta_key = 'course_id'
                              AND meta_value = $post_id
                          ;" );
  if (sizeof($lesson_ids) == 1) {
  	$lesson_id = $lesson_ids[0];
	  $xapi_id = $wpdb->get_var( "
                            SELECT meta_value
                            FROM wp_postmeta 
                            WHERE post_id = $lesson_id
                              AND meta_key = 'show_xapi_content'
                          ;" );
	  if ($xapi_id != 0) {
			error_log("scitent_render_xapi_button " . $xapi_id);
		  return do_shortcode("[grassblade text='$button_label' id=".$xapi_id." target=_blank]");
		} else {
			error_log("scitent_render_xapi_button NO EMBED");
		}

  } 

  // If the above didnt work, embed the old button to go to the course page
	return "<a class='btn btn-primary courseBtn' role='button' href='". get_the_permalink($post_id) ."' rel='bookmark'> $button_label </a>";

}


///////////Certificate functions//////////////////////////

/**
 * Get course certificate link for user
 *
 * @since 2.1.0
 * 
 * @param  int     $course_id
 * @param  int     $user_id
 * @return string
 */
function new_learndash_get_course_certificate_link( $course_id, $user_id = null ) {
  $user_id = get_current_user_id();
  if ( empty( $course_id ) || empty( $user_id ) || ! sfwd_lms_has_access( $course_id, $user_id ) ) {
    return '';
  }

  $certificate_id = learndash_get_setting( $course_id, 'certificate' );

  if ( empty( $certificate_id ) ) {
    return '';
  }

  $course_status = learndash_course_status( $course_id, $user_id );

  if ( $course_status != __( 'Completed', 'learndash' ) ) {
    return '';
  }

  $url = get_permalink( $certificate_id );
  $url = ( strpos( '?', $url ) === false ) ? $url.'?' : $url.'&';
  $url = $url.'course_id='.$course_id.'&user_id='.$user_id;

  return $url;
}
//////////////////////////////////


/**
 * Get certificate details
 *
 * Return a link to certificate and certificate threshold
 *
 * @since 2.1.0
 * 
 * @param  int    $post_id
 * @param  int    $user_id
 * @return array    certificate details
 */
function new_learndash_certificate_details( $post_id, $user_id = null ) {
  $user_id = ! empty( $user_id ) ? $user_id : get_current_user_id();

  $certificateLink = '';
  $post = get_post( $post_id );
  $meta = get_post_meta( $post_id, '_sfwd-quiz' );
  $cert_post = '';
  $certificate_threshold = '0.8';

  if ( is_array( $meta ) && ! empty( $meta ) ) {
    $meta = $meta[0];

    if ( is_array( $meta ) && ( ! empty( $meta['sfwd-quiz_certificate'] ) ) ) {
      $certificate_post = $meta['sfwd-quiz_certificate'];
    }

    if ( is_array( $meta ) && ( ! empty( $meta['sfwd-quiz_threshold'] ) ) ) {
      $certificate_threshold = $meta['sfwd-quiz_threshold'];
    }
  }

  if ( ! empty( $certificate_post ) ) {
    $certificateLink = get_permalink( $certificate_post );
  }

  if ( ! empty( $certificateLink ) ) {
    $certificateLink .= ( strpos( 'a'.$certificateLink,'?' ) ) ? '&' : '?';
    $certificateLink .= "quiz={$post->ID}&print=" . wp_create_nonce( $post->ID . $user_id );
  }

  return array( 'certificateLink' => $certificateLink, 'certificate_threshold' => $certificate_threshold );
}