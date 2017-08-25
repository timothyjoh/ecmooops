<?php
if (!class_exists('Learndash_Admin_Groups_Users_List')) {
	class Learndash_Admin_Groups_Users_List {
		
		var $list_table;
		var $form_method 	=	'get';
		var $title 			= 	'';

		var $current_action =	'';
		var $group_id 		= 	0;
		var $user_id 		= 	0;
		
		function __construct() {
			//add_action( 'load-edit.php', array( $this, 'on_load_groups') );
			
			add_action( 'admin_menu', array( $this, 'learndash_group_admin_menu' ) , 1 );
		}
		
		/*
		function on_load_groups() {
			
			if ( ( isset( $_GET['post_type'] ) ) && ( $_GET['post_type'] == 'groups' ) ) {

				add_filter( 'manage_groups_posts_columns', array( $this, 'set_groups_columns' ) );
				add_action( 'manage_groups_posts_custom_column' , array( $this, 'display_groups_columns' ), 10, 2 );

			}			
		}
		*/

		/*
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
		*/
		/*
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
		*/
		
		/**
		 * Register Group Administration submenu page
		 * 
		 * @since 2.1.0
		 */
		function learndash_group_admin_menu() {

			$menu_user_cap = '';
	
			if ( learndash_is_admin_user() ) 
				$menu_user_cap = LEARNDASH_ADMIN_CAPABILITY_CHECK;
			else if ( learndash_is_group_leader_user() ) 
				$menu_user_cap = LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK;
	
			if ( !empty( $menu_user_cap ) ) {

				$pagehook = add_submenu_page( 
					'admin.php', 
					__( 'Group Administration', 'learndash' ), 
					__( 'Group Administration', 'learndash' ), 
					$menu_user_cap, 
					'group_admin_page', 
					array( $this, 'show_page') 
				);
				add_action( 'load-'. $pagehook, array( $this, 'on_load') );	
			}
		}

		function on_load() {

			if ( ( isset( $_GET['action'] ) ) && ( !empty( $_GET['action'] ) ) ) {
				$this->current_action = esc_attr( $_GET['action'] );
				//$this->current_action = $this->list_table->current_action();
			}

			if ( ( isset( $_GET['group_id'] ) ) && ( !empty( $_GET['group_id'] ) ) ) {
				$this->group_id = intval($_GET['group_id']);
			}
			
			if ( ( isset( $_GET['user_id'] ) ) && ( !empty( $_GET['user_id'] ) ) ) {
				$this->user_id 	= intval($_GET['user_id']);
			}

			wp_enqueue_style( 
				'sfwd-module-style', 
				LEARNDASH_LMS_PLUGIN_URL . '/assets/css/sfwd_module'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', 
				array(), 
				LEARNDASH_VERSION 
			);
			$learndash_assets_loaded['styles']['sfwd-module-style'] = __FUNCTION__;

			wp_enqueue_script( 
				'sfwd-module-script', 
				LEARNDASH_LMS_PLUGIN_URL . '/assets/js/sfwd_module'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', 
				array( 'jquery' ), 
				LEARNDASH_VERSION,
				true 
			);
			$learndash_assets_loaded['scripts']['sfwd-module-script'] = __FUNCTION__;

			wp_localize_script( 'sfwd-module-script', 'sfwd_data', array() );


			if ( empty( $this->current_action ) ) {

				require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-groups-users-list-table.php' );
				$this->list_table = new Learndash_Admin_Groups_Users_List_Table();
				$screen = get_current_screen();
			
				$screen_key = $screen->id;
				if (!empty($this->group_id)) {
					$screen_key .= '_users';
				} else {
					$screen_key .= '_groups';
				}
				$screen_key .= '_per_page';
			
				$screen_per_page_option = str_replace( '-', '_', $screen_key );
			
				if ( isset( $_POST['wp_screen_options']['option'] ) ) {
	
					if ( isset($_POST['wp_screen_options']['value'] ) ) {
						$per_page = intval( $_POST['wp_screen_options']['value'] );
						if ((!$per_page) || ($per_page < 1)) {
							$per_page = 20;
						}
						update_user_meta(get_current_user_id(), $screen_per_page_option, $per_page);
					}
				}
				$per_page = get_user_meta(get_current_user_id(), $screen_per_page_option, true);
				if ( (!$per_page) || (empty($per_page)) || ($per_page < 1) ) {
					$per_page = 20;
				}
			
				$this->list_table->per_page = $per_page;
				add_screen_option( 'per_page', array('label' => __('per Page', 'learndash' ), 'default' => $per_page) );
						
				if ( ( !empty( $this->group_id ) ) && ( !empty( $this->user_id ) ) ) {
				
					$this->on_process_actions_list();					
				
					$this->form_method = 'post';
				
					$user           = get_user_by( 'id', $this->user_id );
			
					$this->title = __( 'Group Administration', 'learndash' ) . ': ';
				
					if (learndash_is_admin_user()) {
						$this->title .= '<a title="'. __('Edit Group', 'learndash') .'" tite="'. __('Edit User', 'learndash') .'" href="'. get_edit_user_link( $user->ID ).'">';
					}
				
					$this->title .= $user->display_name;
				
					if (learndash_is_admin_user()) {
						$this->title .= '</a>';
					}
				
					$this->title .= ' <small>| <a href="' . remove_query_arg( array('user_id', 's', 'paged', 'learndash-search', 'ld-group-list-view-nonce', '_wp_http_referer', '_wpnonce')) . '">' . __( 'Back', 'learndash' ) . '</a></small>';
					return;
				} else if ( !empty( $this->group_id ) ) {
					$group_post = get_post( $this->group_id );
					if ($group_post) {
						$this->title = __( 'Group Administration', 'learndash' ) . ': ';
					
						if (learndash_is_admin_user()) {
							$this->title .= '<a title="'. __('Edit Group', 'learndash') .'" href="'. get_edit_post_link( $this->group_id ).'">';
						}
					
						$this->title .= $group_post->post_title;
					
						if (learndash_is_admin_user()) {
							$this->title .= '</a>';
						}
					
						$this->title .= ' <small><a href="'. remove_query_arg( array('group_id', 's', 'paged', 'learndash-search', 'ld-group-list-view-nonce', '_wp_http_referer', '_wpnonce') ) .'">'. __( 'Back', 'learndash' ) .'</a></small>';
				
						$this->list_table->group_id = $this->group_id;
					
						$this->list_table->columns['username'] 		= 	__( 'Username', 'learndash' );
						$this->list_table->columns['name'] 			= 	__( 'Name', 'learndash' );
						$this->list_table->columns['email'] 		= 	__( 'Email', 'learndash' );
						$this->list_table->columns['user_actions'] 	= 	__( 'Actions', 'learndash' );
					
						return;
					}
				}
			} else if ($this->current_action == 'learndash-group-email') {
				//error_log('group_id['. $this->group_id .']');
				
				$group_post = get_post( $this->group_id );
				if ($group_post) {
					$this->title = __( 'Group Administration', 'learndash' ) . ': ';
				
					if (learndash_is_admin_user()) {
						$this->title .= '<a title="'. __('Edit Group', 'learndash') .'" href="'. get_edit_post_link( $this->group_id ).'">';
					}
				
					$this->title .= $group_post->post_title;
				
					if (learndash_is_admin_user()) {
						$this->title .= '</a>';
					}
				
					$this->title .= ' <small><a href="'. remove_query_arg( array('action', 's', 'paged', 'learndash-search', 'ld-group-list-view-nonce', '_wp_http_referer', '_wpnonce') ) .'">'. __( 'Back', 'learndash' ) .'</a></small>';
			
					return;
				}
			} 
			$this->title = __( 'Group Administration', 'learndash' );

			$this->list_table->columns['group_name'] 	= 	__( 'Group Name', 'learndash' );
			$this->list_table->columns['group_actions'] = 	__( 'Actions', 'learndash' );
		}

		function show_page() {

			?>
			<div class="wrap wrap-learndash-group-list">
				<h2><?php echo $this->title; ?></h2>
				<?php
					$current_user = wp_get_current_user();
					if ( ( !learndash_is_group_leader_user( $current_user ) ) && ( !learndash_is_admin_user( $current_user ) ) ) {
						die( __( 'Please login as a Group Administrator', 'learndash' ) );
					}
				?>
				<div class="wrap-learndash-view-content">
					<?php
					if ($this->current_action == 'learndash-group-email') {
						?>
						<!-- Email Group feature below the Group Table (on the Group Leader page) -->
						<table class="form-table">
							<tr>
								<th scope="row"><label for="group_email_sub"><?php _e( 'Email Subject:', 'learndash' );?></label></th>
								<td><input id="group_email_sub" rows="5" class="regular-text group_email_sub"/></td>
							</tr>
							<tr>
								<th scope="row"><label for="text"><strong><?php _e( 'Email Message:', 'learndash' );?></strong></label></th>
								<td><div class="groupemailtext" ><?php wp_editor( '', 'groupemailtext', array( 'media_buttons' => true, 'wpautop' => true) );?></div></td>
							</tr>
						</table>

						<p><button id="email_group" type="button"><?php _e( 'Send', 'learndash' );?></button></p>
						<script>
						jQuery(function( $){
							var sending = 0;
							$("#email_group").click(function(){
								tinyMCE.triggerSave();

								$("#email_group").html("<?php _e( 'Sending...', 'learndash' );?>");
								if(sending == 1) {
									alert("<?php _e( 'Please Wait', 'learndash' );?>");
									return;
								}

								sending = 1;
								var gid = <?php echo $this->group_id; ?>;
								var txt = $('#groupemailtext').val();
								var sub = $('#group_email_sub').val();

								$.post( "", 
									{ 
										group: gid,
										text: txt,
										sub: sub
									},
									function(data) {
										alert(data);
										$("#email_group").html("<?php _e( 'Send', 'learndash' );?>");
										sending = 0;
										tinyMCE.get('groupemailtext').setContent('');
										$('#group_email_sub').val('');
									}
								);
							});
						});
						</script>
						<?php
					} else {
					
						$this->list_table->views(); 
						?>
						<form id="learndash-view-form" action="" method="<?php echo $this->form_method; ?>">
							<input type="hidden" name="page" value="group_admin_page" />
						
							<?php
								if ( empty( $this->user_id ) ) {
									?><input type="hidden" name="user_id" value="<?php echo $this->user_id ?>" /><?php
									$this->list_table->check_table_filters();
									$this->list_table->prepare_items();
								
									if ( !empty( $this->group_id ) ) {
										?><input type="hidden" name="group_id" value="<?php echo $this->group_id ?>" /><?php
										$this->list_table->search_box( __( 'Search Users' ), 'learndash' );
									} else {
										$this->list_table->search_box( __( 'Search Groups' ), 'learndash' );
									}
									wp_nonce_field( 'ld-group-list-view-nonce', 'ld-group-list-view-nonce' );
									$this->list_table->display();
								} else {
									$group_user_ids = learndash_get_groups_user_ids( $this->group_id );
									if ( !empty( $group_user_ids ) ) {
										if ( in_array( $this->user_id, $group_user_ids ) ) {
										
											echo learndash_course_info_shortcode( array( 'user_id' => $this->user_id ) );
										
											if ( learndash_show_user_course_complete( $this->user_id ) ) {
												echo submit_button( __('Update User') ); 
											}
										}
									}
								}
							?>
						</form>
						<?php
					}
				?>
				</div>
			</div>
			<?php
		}

		
		/**
		 * Group admin page HTML output
		 * 
		 * @since 2.1.0
		 */
		/*
		function show_page_old() {

			if ( ( !learndash_is_group_leader_user( ) ) && ( !learndash_is_admin_user( ) ) ) {
				die( __( 'Please login as a Group Administrator', 'learndash' ) );
			}

			//global $wpdb;
			$group_ids = learndash_get_administrators_group_ids( $current_user->ID );
			
			if ( ! isset( $_GET['group_id'] ) || ! in_array( $_GET['group_id'], $group_ids ) ) {
				?>
					<div class="wrap">
					<h2><?php _e( 'Group Administration', 'learndash' );?></h2>

					<table cellspacing="0" class="wp-list-table widefat fixed groups_table">
						<thead>
							<tr>
								<th class="manage-column column-sno " id="sno" scope="col"><?php _e( 'S. No.', 'learndash' );?></th>
								<th class="manage-column column-group " id="group" scope="col"><?php _e( 'Group', 'learndash' );?></th>
								<th class="manage-column column-action" id="action" scope="col"><?php _e( 'Action', 'learndash' );?></span><span class="sorting-indicator"></span></th>
							</tr>
						</thead>
						<tfoot>
						<tr>
							<th class="manage-column column-sno " id="sno" scope="col"><?php _e( 'S. No.', 'learndash' );?></th>
							<th class="manage-column column-group " id="group" scope="col"><?php _e( 'Group', 'learndash' );?></th>
							<th class="manage-column column-action" id="action" scope="col"><?php _e( 'Action', 'learndash' );?></span><span class="sorting-indicator"></span></th>
						</tr>
						</tfoot>
						<tbody>
							<?php $sn = 1; ?>
							<?php foreach ( $group_ids as $group_id ) : ?>
								<?php $group = get_post( $group_id ); ?>
								<tr>
									<td><?php echo $sn++; ?></td>
									<td><?php echo $group->post_title; ?></td>
									<td><a href="<?php echo add_query_arg('group_id', $group_id ); ?>"><?php _e( 'List Users', 'learndash' );?></a> | <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&action=sfp_update_module&nonce-sfwd=' . wp_create_nonce( 'sfwd-nonce' ) . '&page_options=sfp_home_description&courses_export_submit=Export&group_id=' . $group_id );?>"><?php _e( 'Export Progress', 'learndash' );?></a> | <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&action=sfp_update_module&nonce-sfwd=' . wp_create_nonce( 'sfwd-nonce' ) . '&page_options=sfp_home_description&quiz_export_submit=Export&group_id=' . $group_id );?>"><?php _e( 'Export Results', 'learndash' );?></a><?php do_action( 'learndash_group_admin_page_actions', $group_id );?></td>
								</tr>
							<?php endforeach; ?>
						<tbody>
					</table>
					</div>
				<?php
			} else {
				if ( ! isset( $_GET['user_id'] ) ) {
					$group_id = intval($_GET['group_id']);
					$group = get_post( $group_id );
					if ($group) {
						?>
						<div class="wrap">
							<h2><?php echo __( 'Group Administration', 'learndash' ) . ': ' . $group->post_title;?> <small>| <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page' );?>"><?php echo __( 'Back', 'learndash' );?></a></small></h2>
							<p>
								<?php echo $group->post_content;?>
							</p>
							<?php echo learndash_group_user_list( $group_id ); ?>
						</div>
						<!-- Email Group feature below the Group Table (on the Group Leader page) -->
						<div id="learndash_groups_page_box">
							<br><br>

							<h2><?php _e( 'Email Users', 'learndash' );?></h2>

							<br/>

							<label for="email"><b><?php _e( 'Email Subject:', 'learndash' );?></b><br/>
								<input id="group_email_sub" rows="5" class="group_email_sub"/>
							</label>

							<br/><br/>

							<label for="text"><b><b><?php _e( 'Email Message:', 'learndash' );?></b><br/>
								<div class="groupemailtext" ><?php wp_editor( '', 'groupemailtext', array( 'media_buttons' => true, 'wpautop' => true) );?></div>
							</label>

							<br/>

							<button id="email_group" type="button"><?php _e( 'Send', 'learndash' );?></button>
							<br/><br/><br/><br/><br/>
						</div>
						<script>
						jQuery(function( $){
							var sending = 0;
							$("#email_group").click(function(){
								tinyMCE.triggerSave();

								$("#email_group").html("<?php _e( 'Sending...', 'learndash' );?>");
								if(sending == 1) {
									alert("<?php _e( 'Please Wait', 'learndash' );?>");
									return;
								}

								sending = 1;
								var gid = <?php echo $group_id?>;
								var txt = $('#groupemailtext').val();
								var sub = $('#group_email_sub').val();

								$.post( "", 
									{ 
										group: gid,
										text: txt,
										sub: sub
									},
									function(data) {
										alert(data);
										$("#email_group").html("<?php _e( 'Send', 'learndash' );?>");
										sending = 0;
										tinyMCE.get('groupemailtext').setContent('');
										$('#group_email_sub').val('');
									}
								);
							});
						});
						</script>
					<?php
					}
				} else {
					$user_id        = $_GET['user_id'];
					$group_id       = $_GET['group_id'];
					$group_user_ids = learndash_get_groups_user_ids( $group_id );
					$user           = get_user_by( 'id', $user_id );
					?>
						<div class="wrap">
							<h2><?php echo __( 'Group Administration', 'learndash' ) . ': ' . $user->display_name;?> <small>| <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&group_id=' . $group_id );?>"><?php echo __( 'Back', 'learndash' );?></a></small></h2>
							<?php if ( in_array( $user_id, $group_user_ids ) ) : ?>
								<?php echo learndash_course_info_shortcode( array( 'user_id' => $user_id ) ); ?>
							<?php endif; ?>
						</div>
					<?php
				}
			}
		}
		*/
		
		
		function on_process_actions_list() {
			//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');
			if ( !empty( $this->user_id ) ) {
				learndash_save_user_course_complete( $this->user_id );
			}
		}

		/*
		// For now the real function lives in the ld-groups.php file. 
		function learndash_group_emails() {
			if ( isset( $_POST['group'] ) && isset( $_POST['text'] ) && isset( $_POST['sub'] ) ) {

				if ( empty( $_POST['group'] ) || empty( $_POST['text'] ) || empty( $_POST['sub'] ) ) {
					echo __( 'Empty value', 'learndash' );
					exit;
				}

		require_once ABSPATH . 'wp-includes/pluggable.php';
		
				$current_user       = wp_get_current_user();
				$group_leader_email = $current_user->user_email;
				$group_leader_name  = $current_user->display_name;
				$status             = '';
				$er                 = learndash_get_groups_user_ids( $_POST['group'] );
				$sent               = '';
				$notsent            = '';

				foreach ( $er as $k => $v ) {

					$user    = get_userdata( $er[ $k ] );
					$email   = $user->user_email;
					$message = nl2br( stripcslashes( $_POST['text'] ) );
					$sub     = $_POST['sub'];

					$headers = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					// old format	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					//$headers .= "From: ".$user->display_name." <".$user->user_email.">" ;
					$headers .= 'From: ' . $group_leader_name . ' <' . $group_leader_email . '>';

					$state = wp_mail( $email, $sub, $message, $headers );

					if ( $state ) {
						$sent .= empty( $sent) ? $user->user_email : ', ' . $user->user_email;
					} else {
						$notsent .= empty( $notsent) ? $user->user_email : ', ' . $user->user_email;
					}

				}

				if ( ! empty( $notsent) ) {
					echo "\n" . __( 'Email could not be sent to: ', 'learndash' ) . $notsent . ' ' . $group_leader_email . ':' . $group_leader_name;
				}

				if ( ! empty( $sent) ) {
					echo "\n" . __( 'Email sent to: ', 'learndash' ) . $sent;
				}

				exit;
			}
		}
		*/

		// End of functions
	}
}

function learndash_data_group_reports_ajax() {
	//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');

	$reply_data = array( 'status' => false);

	
	if ( isset( $_POST['data'] ) )
		$post_data = $_POST['data'];
	else
		$post_data = array();
		
	$ld_admin_settings_data_reports = new Learndash_Admin_Settings_Data_Reports;
	$reply_data['data'] = $ld_admin_settings_data_reports->do_data_reports( $post_data, $reply_data );
	
	
	if ( !empty( $reply_data ) )
		echo json_encode($reply_data);

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_learndash_data_group_reports', 'learndash_data_group_reports_ajax' );
