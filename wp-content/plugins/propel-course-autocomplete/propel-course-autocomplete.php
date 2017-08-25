<?php
/** 
 * Plugin Name: PROPEL Course Autocomplete
 * Version: 1.0
 * Author:  Lou Foster
 * Author URI: http://scitent.com
 * Description: Helper plugin to allow an admin to autocomplete courses for users
 */
class Propel_Course_Autocomplete {
   private $settings;
   private $set;

   function __construct() {
      if ( is_admin() ){
         add_action( 'admin_menu',array( $this, 'add_admin_menu' ) );
         add_action( 'wp_ajax_do_autocomplete', array($this,'do_autocomplete_callback') );
      }

      add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
   }

   public function register_scripts() {
      wp_enqueue_style( 'propel-autocomplete', plugins_url( '/css/style.css', __FILE__ ) );
      wp_enqueue_script('propel-autocomplete', plugins_url( '/js/autocomplete.js' , __FILE__ ));   
   }

   function add_admin_menu() {
      add_menu_page('PROPEL Autocomplete','PROPEL Course Autocomplete','delete_pages',
                    'propel-autocomplete', array( $this, 'render_admin_menu'), 'dashicons-welcome-learn-more');   
   }

   function render_admin_menu() {
      $courses = [];
      $query = new WP_Query( array( 'post_type' => 'sfwd-courses', 'post_status'=>'published' ) );
      if ( $query->have_posts() ) {
         while ( $query->have_posts() ) {
            $query->the_post();
            array_push($courses, array( 'ID'=>get_the_ID(), 'name'=> get_the_title()) );
         }
      }
      wp_reset_postdata();
      
      // Any courses exist? If not, noting can be done here!!
      if (sizeof($courses) == 0 ) {
         ?>
         <div class="wrap">
            <h2>PROPEL Course Autocomplete</h2>
            <div class="wrap">
               <h3>
                  No courses exist!
               </h3>
            </div>
         </div>
         <?php
      } else {
         ?>
         <div class="wrap">
            <h2>PROPEL Course Autocomplete</h2>
            <div class="wrap">
               <p>
                  Select a course and fill in a comma separated list of user emails. <br/>
                  Click the Autocomplete button and these users will automatically be marked 100% coomplete
                  for the course.
               <p>
                  This cannot be reversed!
               </p>
               <div>
                  <label class='autocomplete-label'>Course:</label>
                  <select id='course'>
                     <?php
                     foreach ($courses as $c) {
                        echo('<option value="'.$c['ID'].'">'.$c['name'].'</option>');
                     }
                     ?>
                  </select>
               </div>
               <div>
                  <label class='autocomplete-label'>User Emails (comma separated):</label>
                  <textarea rows='5' id='user-emails'></textarea>
               </div>
               <button id="do-autocomplete" class="reset button button-primary">Autocomplete</button>
            </div>
         </div>
         <div id="working"><div id="work-spinner-box">Working...</div></div>
         <?php
      }
   }
   
   function do_autocomplete_callback() {
      $postid = $_POST['course'];
      $emails_str = $_POST['emails'];
      error_log("Autocomplete course ID $postid");
      
      $course_id = learndash_get_course_id( $postid );
      $lessons = learndash_get_lesson_list( $postid );
      $topics = learndash_get_topic_list( $postid );
      $global_quizzes = learndash_get_global_quiz_list($postid);
      
      if ( $course_id == 0 ) {
         error_log("Autocomplete with invalid course ID $postid");
         wp_send_json_error("Invalid course");
         wp_die();
         return;
      } 
      
      $bad_emails = array();
      $not_enrolled = array();
      $emails = explode(",", $emails_str);
      foreach ($emails as $email) {
         $user = get_user_by_email( trim($email) );
         if ( !$user ) {
            array_push( $bad_emails, $email);
         } else {
            if ( $this->has_enrollment($user->ID, $course_id) ) {
               $this->mark_complete($user->ID, $course_id, $lessons, $topics, $global_quizzes);
            } else {
               array_push($not_enrolled, $email);
            }
         }
      }
      
      $err = "";
      if ( sizeof($bad_emails) > 0 ) {
         $err = "The following emails were invalid:\n\n     * ".join(",",$bad_emails);
      }
      if ( sizeof($bad_emails) > 0 ) {
         if ( strlen($err) > 0 ) {
            $err .= "\n\n";
         }
         $err .= "The following emails were not enrolled:\n\n     * ".join(",",$not_enrolled);
      }
      
      if ( strlen($err) > 0 ) {
         wp_send_json_success($err);
      } else {
         wp_send_json_success();
      }
      
      wp_die();
   }
   
   function has_enrollment($user_id, $course_id) {
      global $wpdb;
      $propel_table = $wpdb->prefix . "propel_enrollments";

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
   
   function mark_complete($user_id, $course_id, $lessons, $topics, $global_quizzes) {
      if ( sizeof($global_quizzes) ) {
         $globalquiz = 1;
         $globalquizcompleted = 1;
      } else {
         $globalquiz = 0;
         $globalquizcompleted = 0;
      }
      
      // Get or init user course progress and quiz progress
      $course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
      if ( empty( $course_progress[ $course_id ] ) ) {
         $course_progress[ $course_id ] = array( 'lessons' => array(), 'topics' => array() );
      }
      $quiz_usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );
      $quiz_usermeta = maybe_unserialize( $quiz_usermeta );
      if ( ! is_array( $quiz_usermeta ) ) {
         $quiz_usermeta = array();
      }
      
      // Mark all lessons complete, and collect quiz info for each lesson
      $posts = get_posts( Array( 'post_type' => 'sfwd-quiz' , 'numberposts' => -1) );
      foreach ($lessons as $lesson ) {
         $course_progress[ $course_id ]['lessons'][$lesson->ID] = 1;
         
         if ( !empty( $posts ) )  {
            foreach( $posts as $p ) {
               $meta = get_post_meta( $p->ID, '_sfwd-quiz' );
               if ( is_array( $meta ) && !empty( $meta ) ) 
               {
                  $meta = $meta[0];
                  if ( is_array( $meta ) && ( !empty( $meta['sfwd-quiz_lesson'] ) ) ) {
                     if ( $meta['sfwd-quiz_lesson'] == $lesson->ID ) {
                        $quiz_usermeta = $this->add_or_update_quiz_complete($quiz_usermeta, $p->ID, $meta['sfwd-quiz_quiz_pro']);
                     }
                  }
               }
            }
         }
      }
      
      
      // mark all GLOBAL quizzes complete
      foreach ($global_quizzes as $q) {
         $meta = get_post_meta( $q->ID, '_sfwd-quiz' );
         if ( is_array( $meta ) && !empty( $meta ) ) {
            $meta = $meta[0];
            $quiz_usermeta = $this->add_or_update_quiz_complete($quiz_usermeta, $q->ID, $meta['sfwd-quiz_quiz_pro']);
         }   
      }
      update_user_meta( $user_id, '_sfwd-quizzes', $quiz_usermeta );
            
      # mark entire course complete
      $course_progress[ $course_id ]['completed'] = count( $lessons ) + $globalquiz;
      $course_progress[ $course_id ]['total'] = count( $lessons ) + $globalquiz;
      
      // update user_meta with the progress
      update_user_meta( $user_id, '_sfwd-course_progress', $course_progress );
      
      // Mark propel-enrollments table as complete / passed for this user
      global $wpdb;
   	$table = $wpdb->prefix . 'propel_enrollments';
      $wpdb->update(
         $table,
         array( 'completion_date' => date('Y-m-d h:i:s', time()), 'passed' => true ),
         array( 'user_id'=>$user_id, 'post_id'=>$course_id)
      );
   }
   
   function add_or_update_quiz_complete($quiz_usermeta, $quiz_id, $pro_quiz_id) {
      error_log("add_or_update_quiz_complete Q: $quiz_id PRO: $pro_quiz_id");
      $found = false;
      foreach ($quiz_usermeta as $quiz_meta) {
         if ( $quiz_meta['quiz'] == $quiz_id ) {
            $found = true;
            $quiz_meta['pass'] = 1;
            $quiz_meta['rank'] = '-';
            $quiz_meta['percentage'] = 100;
            $quiz_meta['time'] = time();
         }
      }
      
      if ( $found == false ) {
         $quizdata = array(
            'quiz' => $quiz_id,
            'pass' => 1,
            'rank' => '-',
            'pro_quizid' => $pro_quiz_id,
            'percentage' => 100,
            'time' => time()
         );
         $quiz_usermeta[] = $quizdata;
      }
      return $quiz_usermeta;
   }
}

new Propel_Course_Autocomplete();
