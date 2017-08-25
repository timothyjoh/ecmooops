<?php

if (!class_exists('Learndash_Admin_Groups_Edit')) {
	class Learndash_Admin_Groups_Edit {
		
		private $groups_type = 'groups';
	    
		function __construct() {
			// Hook into the on-load action for our post_type editor
			add_action( 'load-post.php', 			array( $this, 'on_load_groups') );
			add_action( 'load-post-new.php', 		array( $this, 'on_load_groups') );
			
			add_filter( 'manage_groups_posts_columns', array( $this, 'set_groups_columns' ) );
			add_action( 'manage_groups_posts_custom_column' , array( $this, 'display_groups_columns' ), 10, 2 );

			
		}
		
		function on_load_groups() {
			global $typenow;	// Contains the same as $_GET['post_type]
			
			if ((empty($typenow)) || ($typenow != $this->groups_type)) return;

			if ((defined('LEARNDASH_GROUPS_LEGACY_v220') && (LEARNDASH_GROUPS_LEGACY_v220 === true))) {
				wp_enqueue_script( 
					'sfwd-admin-groups-script', 
					LEARNDASH_LMS_PLUGIN_URL . 'assets/js/sfwd-admin-groups'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', 
					array( 'jquery' ),
					LEARNDASH_VERSION,
					true
				);
			} else {
				
				wp_enqueue_script( 
					'learndash-admin-binary-selector-script', 
					LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-binary-selector'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', 
					array( 'jquery' ),
					LEARNDASH_VERSION,
					true
				);

				wp_enqueue_style( 
					'learndash-admin-binary-selector-style', 
					LEARNDASH_LMS_PLUGIN_URL . 'assets/css/learndash-admin-binary-selector'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', 
					array( ),
					LEARNDASH_VERSION
				);
			}
			
			// Add Metabox and hook for saving post metabox
			add_action( 'add_meta_boxes', 			array( $this, 'learndash_groups_add_custom_box' ) );
			add_action( 'save_post', 				array( $this, 'learndash_groups_save_postdata') );
			
		}
		
		/**
		 * Register Groups meta box for admin
		 *
		 * Managed enrolled groups, users and group leaders
		 * 
		 * @since 2.1.2
		 */
		function learndash_groups_add_custom_box() {
			
			add_meta_box(
				'learndash_groups',
				__( 'LearnDash Group Admin', 'learndash' ), 
				array( $this, 'learndash_groups_page_box' ),
				$this->groups_type
			);
		}


		/**
		 * Prints content for Groups meta box for admin
		 *
		 * @since 2.1.2
		 * 
		 * @param  object $post WP_Post
		 * @return string 		meta box HTML output
		 */
		function learndash_groups_page_box( $post ) {
			global $wpdb;

			//echo "post<pre>"; print_r($post); echo "</pre>";
			//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');
			//error_log('post<pre>'. print_r($post, true) .'</pre>');

			$post_id = $post->ID;
			// Use nonce for verification
			wp_nonce_field( plugin_basename( __FILE__ ), 'learndash_groups_nonce' );
			
			?>
			
			<div id="learndash_groups_page_box" class="learndash_groups_page_box">
			<?php
				if ((defined('LEARNDASH_GROUPS_LEGACY_v220') && (LEARNDASH_GROUPS_LEGACY_v220 === true))) {
					// The actual fields for data entry
					// Use get_post_meta to retrieve an existing value from the database and use the value for the form
					$data = get_post_meta( $post->ID, '_learndash_groups', true );
					$users = get_users("orderby=display_name&order=ASC");
					$all_group_leaders = learndash_all_group_leaders();	
					$group_users = learndash_get_groups_user_ids( $post_id );	
					$group_leaders = learndash_get_groups_administrator_ids( $post_id );
	
					$group_enrolled_courses = learndash_group_enrolled_courses( $post_id );
					//echo "group_enrolled_courses<pre>"; print_r($group_enrolled_courses); echo "</pre>";
					$ld_course_list = ld_course_list( array( 'orderby' => 'name', 'order' => 'ASC', 'array' => true ) );
					//echo "ld_course_list<pre>"; print_r($ld_course_list); echo "</pre>";
					
					
					?>
					<h2><?php printf( _x( 'Enrolled %s', 'Enrolled Courses Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'courses' ) ); ?> </h2> 
						<ol>
							<?php foreach ( $group_enrolled_courses as $course_id ) : ?>
								<?php $course = get_post( $course_id ); ?>
								<li ><a href="<?php echo get_permalink( $course->ID ); ?>"><?php echo $course->post_title; ?></a> (<a href="<?php echo get_edit_post_link( $course->ID ); ?>"><?php echo __( "Edit" ); ?></a>)</li>
							<?php endforeach; ?>
						</ol>

					<h2><?php printf( _x( 'Enroll a %s', 'Enroll a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' )); ?> :
						<select name="learndash_group_enroll_course">
							<option value=''><?php printf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></option>
							<?php foreach ( $ld_course_list as $course ) : ?>
								<?php if ( ! in_array($course->ID, $group_enrolled_courses ) ) : ?>
									<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>;
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</h2>

					<h2><?php printf( _x( 'Unenroll a %s', 'Unenroll a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?> :
						<select name="learndash_group_unenroll_course">
							<option value=''><?php printf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></option>
							<?php foreach ( $group_enrolled_courses as $course_id ) : ?>
								<?php $course = get_post( $course_id ); ?>
								<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>
							<?php endforeach; ?>
						</select>
					</h2> 
					
					<h2><?php _e( 'Group Leaders', 'learndash' ); ?> </h2> 
					<?php 
						if ( ! empty( $all_group_leaders ) ) : ?>
							<table class="learndash-group-users-select">
								<tr>
									<td  class="td_learndash_group_users_search">
										<br>
										<select multiple="multiple" id="learndash_group_leaders_search" class="learndash_group_leaders_search_multiple">
											<?php foreach ( $all_group_leaders as $user ) : ?>
												<?php $name = $user->display_name.' ('.$user->user_login.')'; ?>
												<?php $val = $user->ID; ?>
												<option value="<?php echo $val; ?>"  title="<?php echo $name; ?>"><?php echo $name; ?></option>
											<?php endforeach; ?>
										<select>
									</td>

									<td align="center">
										<a href="#" onClick="SelectAddRows(document.getElementById('learndash_group_leaders_search'), document.getElementById('learndash_group_leaders')); SelectAll(document.getElementById('learndash_group_leaders')); return false;"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL ."/assets/images/arrow_right.png"; ?>" /></a><br>
										<a href="#" onClick="SelectRemoveRows(document.getElementById('learndash_group_leaders')); SelectAll(document.getElementById('learndash_group_leaders')); return false;"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL ."assets/images/arrow_left.png"; ?>" /></a>
									</td>

									<td class="td_learndash_group_users_search">		
										<b><?php _e( 'Selected:', 'learndash' ); ?></b><br>
										<select multiple="multiple" id="learndash_group_leaders" name="learndash_group_leaders[]"  class="learndash_group_leaders">
											<?php foreach( $all_group_leaders as $user ) : ?>
												<?php $name = $user->display_name.' ('.$user->user_login.')'; ?>
												<?php $val = $user->ID; ?>
												<?php $selected = in_array( $user->ID, $group_leaders ) ? 'SELECTED="SELECTED"' : ''; ?>
												<?php if ( $selected == 'SELECTED="SELECTED"' ) : ?>
													<option value="<?php echo $val; ?>"<?php echo $selected; ?> title="<?php echo $name; ?>"><?php echo $name; ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
										<select>
									</td>

								</tr>
							</table>		
					<?php else : ?>
							<?php _e( 'Please add some users with Group Leader role', 'learndash' ); ?>
					<?php endif; ?>
					<br><br>

					<h2><?php _e( 'Assign Users', 'learndash' ); ?> </h2>	
	
					<label for="search_group"><?php _e( 'Search Users:', 'learndash' ); ?>
						<input type="text" id="search_group" onChange="group_user_search();" onKeyUp="group_user_search();" onKeyPress="if(event.keyCode == 13) return false;" />
					</label>

					<br/><br/>

					<?php _e( '<b>Instructions: </b> Hold CNTRL to select muliple users for this group, <br>and click the arrow to move to selected list of users.', 'learndash' ); ?>
	
					<br/>
					<table class="learndash-group-users-select">
						<tr>
							<td class="td_learndash_group_users_search" ><br>
								<select multiple="multiple" id="learndash_group_users_search" class="learndash_group_users_search">
									<?php foreach ( $users as $user ) : ?>
										<?php $name = $user->display_name.' ('.$user->user_login.')'; ?>
										<?php $val = $user->ID;	?>
										<option value="<?php echo $val; ?>" title="<?php echo $name; ?>"><?php echo $name; ?></option>
									<?php endforeach; ?>
								<select>
								<select multiple="multiple" id="learndash_group_users_view" class="learndash_group_users_view">
									<?php foreach ( $users as $user ) : ?>
										<?php $name = $user->display_name.' ('.$user->user_login.')'; ?>
										<?php $val = $user->ID; ?>
										<option value="<?php echo $val; ?>" title="<?php echo $name; ?>"><?php echo $name; ?></option>
									<?php endforeach; ?>
								<select>				
							</td>

							<td align="center">
								<a href="#" onClick="SelectAddRows(document.getElementById('learndash_group_users_view'), document.getElementById('learndash_group_users')); SelectAll(document.getElementById('learndash_group_users')); return false;"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL ."assets/images/arrow_right.png"; ?>" /></a><br>
								<a href="#" onClick="SelectRemoveRows(document.getElementById('learndash_group_users')); SelectAll(document.getElementById('learndash_group_users')); return false;"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL ."assets/images/arrow_left.png"; ?>" /></a>
							</td>

							<td class="td_learndash_group_users_search">
								<b><?php _e( 'Selected:', 'learndash' ); ?></b><br>
								<select multiple="multiple" id="learndash_group_users" name="learndash_group_users[]" class="learndash_group_users">
									<?php foreach ( $users as $user ) : ?>
										<?php $name = $user->display_name.'('.$user->user_login.')'; ?>
										<?php $val = $user->ID; ?>
										<?php $selected = in_array( $user->ID, $group_users ) ? 'SELECTED="SELECTED"' : ''; ?>								
										<?php if ( $selected == 'SELECTED="SELECTED"' ) : ?>
												<option value="<?php echo $val; ?>"<?php echo $selected; ?> title="<?php echo $name; ?>"><?php echo $name; ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								<select>
							</td>

						</tr>
					</table>
					
					<?php
				} else {

					$ld_binary_selector_group_courses = new Learndash_Binary_Selector_Group_Courses(
						array(
							'group_id'		=>	$post_id,
							'selected_ids'	=>	learndash_group_enrolled_courses( $post_id, true ),
							'search_posts_per_page' => 100
						)
					);
					$ld_binary_selector_group_courses->show();


					/**
					 * Set the included users IDs to be user for the Group Leader selector
					 * As of LD v2.3 we include users in the group_leader and administrator roles. 
					 */
					$gl_included_ids = array();
					$group_leader_query = new WP_User_Query(
											array(
												'role' 		=>	'group_leader',
												'fields'	=>	'ID'
											)
										);
					$group_leader_results = $group_leader_query->get_results();
					if (!empty( $group_leader_results ) ) {
						$gl_included_ids = array_merge( $gl_included_ids, $group_leader_results );
					}
					$admin_query = new WP_User_Query(
											array(
												'role' 		=>	'administrator',
												'fields'	=>	'ID'
											)
										);
					$admin_results = $admin_query->get_results();
					if (!empty( $admin_results ) ) {
						$gl_included_ids = array_merge( $gl_included_ids, $admin_results );
					}

					if ( !empty( $gl_included_ids ) ) {

						$ld_binary_selector_group_leaders = new Learndash_Binary_Selector_Group_Leaders(
							array(
								'group_id'				=>	$post_id,
								'selected_ids' 			=>	learndash_get_groups_administrator_ids( $post_id, true ),
								'included_ids'			=>	$gl_included_ids
							)
						);
						$ld_binary_selector_group_leaders->show();
					}
					
					$ld_binary_selector_group_users = new Learndash_Binary_Selector_Group_Users(
						array(
							'group_id'				=>	$post_id,
							'exclude'				=>	learndash_get_groups_administrator_ids( $post_id ),	// Exclude current group leaders.
							'selected_ids' 			=>	learndash_get_groups_user_ids( $post_id, true ),
						)
					);
					$ld_binary_selector_group_users->show();
				}
			?>

			</div>
			<?php 
		}


		/**
		 * When the post is saved, save the data in the Groups custom metabox
		 *
		 * @since 2.1.0
		 * 
		 * @param  int $post_id
		 */
		function learndash_groups_save_postdata( $post_id ) {
			// verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			if ( ! isset( $_POST['learndash_groups_nonce'] ) || ! wp_verify_nonce( $_POST['learndash_groups_nonce'], plugin_basename( __FILE__ ) ) ) {
				return;
			}

			// Check permissions
			if ( 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			if ( 'groups' != $_POST['post_type'] ) {
				return;
			}
			
			if ((defined('LEARNDASH_GROUPS_LEGACY_v220') && (LEARNDASH_GROUPS_LEGACY_v220 === true))) {
				// OK, we're authenticated: we need to find and save the data
			
				$group_leaders_new = isset( $_POST['learndash_group_leaders'] ) ? $_POST['learndash_group_leaders'] : array();
				learndash_set_groups_administrators($post_id, $group_leaders_new);
						
				$group_users_new = isset( $_POST['learndash_group_users'] ) ? $_POST['learndash_group_users'] : array();
				learndash_set_groups_users( $post_id, $group_users_new);
			
			
				$group_enroll_course   = isset( $_POST['learndash_group_enroll_course'] ) ? $_POST['learndash_group_enroll_course'] : array();
				if ( is_numeric( $group_enroll_course ) ) {
					$group_enroll_course = array( $group_enroll_course );
				}
				if (!empty($group_enroll_course)) {
					foreach ( $group_enroll_course as $course_id ) {
						update_post_meta( $course_id, 'learndash_group_enrolled_' . $post_id, time() );
					}
				}
			
				$group_unenroll_course = isset( $_POST['learndash_group_unenroll_course'] ) ? $_POST['learndash_group_unenroll_course'] : array();
						
				if ( is_numeric( $group_unenroll_course ) ) {
					$group_unenroll_course = array( $group_unenroll_course );
				}

				if (!empty($group_unenroll_course)) {
					foreach ( $group_unenroll_course as $course_id ) {
						delete_post_meta( $course_id, 'learndash_group_enrolled_' . $post_id );
					}
				}
			
			} else {
				
				//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');
				
				if ( ( isset( $_POST['learndash_group_leaders'] ) ) && ( isset( $_POST['learndash_group_leaders'][$post_id] ) ) && ( !empty( $_POST['learndash_group_leaders'][$post_id] ) ) ) {
					$group_leaders = (array)json_decode( stripslashes( $_POST['learndash_group_leaders'][$post_id] ) );
					//error_log('group_leaders<pre>'. print_r($group_leaders, true) .'</pre>');
					//if ( ( is_array( $group_leaders ) ) && ( !empty( $group_leaders ) ) ) {
						learndash_set_groups_administrators( $post_id, $group_leaders );
					//}
				}

				if ( ( isset( $_POST['learndash_group_users'] ) ) && ( isset( $_POST['learndash_group_users'][$post_id] ) ) && ( !empty( $_POST['learndash_group_users'][$post_id] ) ) ) {
					$group_users = (array)json_decode( stripslashes( $_POST['learndash_group_users'][$post_id] ) );
					//error_log('group_users<pre>'. print_r($group_users, true) .'</pre>');
					//if ( ( is_array( $group_users ) ) && ( !empty( $group_users ) ) ) {
						learndash_set_groups_users( $post_id, $group_users );
					//}
				}

				if ( ( isset( $_POST['learndash_group_courses'] ) ) && ( isset( $_POST['learndash_group_courses'][$post_id] ) ) && ( !empty( $_POST['learndash_group_courses'][$post_id] ) ) ) {
					$group_courses = (array)json_decode( stripslashes( $_POST['learndash_group_courses'][$post_id] ) );
					//error_log('group_courses<pre>'. print_r($group_courses, true) .'</pre>');
					//if ( ( is_array( $group_courses ) ) && ( !empty( $group_courses ) ) ) {
						learndash_set_group_enrolled_courses( $post_id, $group_courses );
					//}
				}
			}
		}
		
		function set_groups_columns($columns) {

			$columns_new = array();
			
			foreach( $columns as $col_key => $col_label ) {
				if ($col_key == 'date') {
					$columns_new['groups_group_leaders'] = __('Group Leaders', 'learndash');
					$columns_new['groups_group_courses'] = sprintf( __('Group %s', 'Group Courses', 'learndash'), LearnDash_Custom_Label::get_label( 'courses' ));
					$columns_new['groups_group_users'] = __('Group Users', 'learndash');
				}
				$columns_new[$col_key] = $col_label;
			}
			return $columns_new;
			
		}

		function display_groups_columns( $column_name, $group_id ) {
		    switch ( $column_name ) {

		        case 'groups_group_leaders':
					$group_leaders = learndash_get_groups_administrator_ids( $group_id );
					if ( ( empty( $group_leaders ) ) || ( !is_array( $group_leaders ) ) ) {
						$group_leaders = array();
					}
					
					echo  sprintf(__('Total %s', 'learndash'), count( $group_leaders ) );
					
					if ( !empty( $group_leaders ) ) {
						$user_names = '';
						
						if ( count( $group_leaders ) > 5 ) {
							$group_leaders = array_slice( $group_leaders, 0, 5);
						}
						
						foreach( $group_leaders as $user_id ) {
							$user = get_user_by( 'id', $user_id );
							if ( !empty( $user_names ) ) $user_names .= ', ';
							$user_names .= '<a href="'. get_edit_user_link( $user_id ) .'">'. $user->display_name .' ('.$user->user_login.')' .'</a>';
						}
						
						if ( !empty( $user_names ) )
							echo '<br />' . $user_names;
					} 
		            break;

		        case 'groups_group_users':
					$group_users = learndash_get_groups_user_ids( $group_id );
					if ( ( empty( $group_users ) ) || ( !is_array( $group_users ) ) ) {
						$group_users = array();
					}
					
					echo sprintf(__('Total %s', 'learndash'), count( $group_users ) );
				
					if ( !empty( $group_users ) ) {
						$user_names = '';

						if ( count( $group_users ) > 5 ) {
							$group_users = array_slice( $group_users, 0, 5 );
						}
					
						foreach( $group_users as $user_id ) {
							$user = get_user_by( 'id', $user_id );
							if ( !empty( $user_names ) ) $user_names .= ', ';
							$user_names .= '<a href="'. get_edit_user_link( $user_id ) .'">'. $user->display_name .' ('.$user->user_login.')' .'</a>';
						}
						
						if ( !empty( $user_names ) )
							echo '<br />'. $user_names;
					}
		            break;

		        case 'groups_group_courses':
					$group_courses = learndash_group_enrolled_courses( $group_id );
					if ( ( empty( $group_courses ) ) || ( !is_array( $group_courses ) ) ) {
						$group_courses = array();
					}
					
					echo sprintf(__('Total %s', 'learndash'), count( $group_courses ) );
					
					if ( !empty( $group_courses ) ) {

						$course_names = '';
						if ( count( $group_courses ) > 5 ) {
							$group_courses = array_slice( $group_courses, 0, 5 );
						}
				
						foreach( $group_courses as $course_id ) {
							
							if ( !empty( $course_names ) ) $course_names .= ', ';
							$course_names .= '<a href="'. get_edit_post_link( $course_id ) .'">'. get_the_title( $course_id ) .'</a>';
						}

						if ( !empty( $course_names ) )
							echo '<br />'. $course_names;
					}
		            break;


		    }
		}
		
		// End of functions
	}
}
