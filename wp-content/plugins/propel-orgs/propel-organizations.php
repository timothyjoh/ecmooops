<?php
/**
 * Plugin Name: PROPEL Organizations
 * Author: Casey Patrick Driscoll
 * Author URI: http://caseypatrickdriscoll.com
 * Version: 2015-05-01 13:32:49
 * Description: A plugin for adding users to organizations
 */

include 'propel-org-cpt.php';
include 'propel-org-type.php';
include 'propel-org-settings.php';


class Propel_Organizations {


	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_enqueue_scripts', function() {
			wp_register_script(
				'propel_orgs_userpro',
				plugin_dir_url( __FILE__ ) . '/js/user.js',
				array( 'jquery' )
			);
			wp_register_script(
				'propel_orgs_woocommerce',
				plugin_dir_url( __FILE__ ) . '/js/user.js',
				array( 'jquery' )
			);
		} );

		add_action( 'wp_ajax_get_child_orgs', array( $this, 'ajax_get_child_orgs' ) );
		add_action( 'wp_ajax_nopriv_get_child_orgs', array( $this, 'ajax_get_child_orgs' ) );


		// Render fields
		add_action( 'user_new_form',
			array( $this, 'render_user_fields' ) );
		add_action( 'show_user_profile',
			array( $this, 'render_user_fields' ) );
		add_action( 'edit_user_profile',
			array( $this, 'render_user_fields' ) );

		add_action( 'userpro_before_form_submit',
			array( $this, 'render_userpro_fields' ), 1 );

		add_action( 'woocommerce_after_checkout_billing_form',
			array( $this, 'render_woocommerce_fields' ) );


		// Save fields
		add_action( 'personal_options_update',
			array( $this, 'save_user_fields' ) );
		add_action( 'edit_user_profile_update',
			array( $this, 'save_user_fields' ) );
		add_action( 'user_register',
			array( $this, 'save_user_fields' ) );

		add_action( 'woocommerce_checkout_update_order_meta',
			array( $this, 'save_woocommerce_user_fields' ) );
	}


	function load_scripts( $page ) {

		$pages = array( 'user-new.php', 'user-edit.php', 'profile.php' );

		if ( in_array( $page, $pages ) )

			wp_register_script(
				'propel-orgs-user',
				plugin_dir_url( __FILE__ ) . '/js/user.js',
				array( 'jquery' )
			);

	}


	/**
	 * Renders the group fields for the user form
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 11:12:34
	 * @edited  2015-02-26 14:55:02  - sorts orgs by ASC
	 *
	 * @param WP_User   $user   The WP_User object
	 *
	 * @action user_new_form
	 * @action show_user_profile
	 * @action edit_user_profile
	 */
	function render_user_fields( $user ) {

		wp_localize_script( 'propel-orgs-user', 'data', array( 'user_id' => $user->ID, 'public' => false ) );

		wp_enqueue_script( 'propel-orgs-user' );

		$org_types = get_categories( array( 'taxonomy' => 'org_type', 'hierarchical' => 1 ) );

		?>


		<table class="form-table">

			<?php

			foreach ( $org_types as $org_type ) {

				$org = get_user_meta( $user->ID, 'propel_org_' . $org_type->slug, 1 );

				if ( $org_type->parent == 0 ) {
					$parent = 'parent';
					$disabled = '';
				} else {
					$parent = '';
					$disabled = 'disabled';
				}
				?>
				<tr class="form-field">
					<th>
						<label for="<?php echo $org_type->slug; ?>">
							<img class="spinner-<?php echo $org_type->slug; ?>" src="/wp-admin/images/spinner.gif" style="display:none;width:15px;height:15px;" />
							<?php echo $org_type->name; ?>
						</label>

					</th>
					<td>
						<select
							class="propel-org <?php echo $parent; ?>"
							id="<?php echo $org_type->slug; ?>"
							name="propel_org_<?php echo $org_type->slug; ?>"
							data-type="<?php echo $org_type->term_id; ?>"
							<?php echo $disabled;?> >

							<option value="">Please select a <?php echo $org_type->slug; ?></option>

							<?php

							if ( $org_type->category_parent == 0 ) {

								$org_query = array(
									'post_type'   => 'propel_org',
									'post_status' => array( 'publish', 'draft' ),
									'nopaging'    => 1,
									'orderby'     => 'name',
									'order'       => 'ASC',
									'tax_query'   => array( array(
										'taxonomy'         => 'org_type',
										'field'            => 'slug',
										'terms'            => $org_type->slug,
										'include_children' => 0
									) )
								);

								$orgs = new WP_Query( $org_query );

								if ( $orgs->have_posts() ): while ( $orgs->have_posts() ):

									$orgs->the_post();

									$selected = $org == get_the_id() ? 'selected' : '';

									echo '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

								endwhile; endif;

							}


							?>
						</select>
					</td>
				</tr>

				<?php
			}
	}


	/**
	 * Renders the org fields for the userpro form
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 14:43:46
	 * @edited  2015-02-26 14:54:51 - sorts orgs by ASC
	 *
	 * @param  Array   $user   The WP_User object
	 *
	 * @action userpro_after_fields
	 */
	function render_userpro_fields( $args ) {
		global $wp_query;

		$page = $wp_query->post->post_name;

		if ( $page == 'login' || $page == 'profile' || $_POST['action'] == 'userpro_process_form' ) return;

//		$_POST['action'] == 'userpro_shortcode_template'

		$user = wp_get_current_user();

		wp_localize_script( 'propel_orgs_userpro', 'data', array( 'args' => $args, 'public' => true ) );

		wp_enqueue_script( 'propel_orgs_userpro' );

		$org_types = get_categories( array( 'taxonomy' => 'org_type', 'hierarchical' => 1 ) );


		foreach ( $org_types as $org_type ) {

			$org = get_user_meta( $user->ID, 'propel_org_' . $org_type->slug, 1 );

			if ( $org_type->parent == 0 ) {
				$parent = 'parent';
				$type = $org_type->slug;
				$disabled = '';
			} else {
				$parent = '';
				$type = get_term_by( 'id', $org_type->parent, 'org_type' );
				$type = $type->slug . ' first';
				$disabled = 'disabled';
			}
			?>

			<div class="userpro-field">
				<div class="userpro-label">
					<label for="<?php echo $org_type->slug; ?>"><?php echo $org_type->name; ?></label>
					<img class="spinner-<?php echo $org_type->slug; ?>" src="/wp-admin/images/spinner.gif" style="display:none;width:15px;height:15px;" />
				</div>
				<div class="userpro-input">
					<select
							class="propel-org <?php echo $parent; ?>"
							id="<?php echo $org_type->slug; ?>"
							name="propel_org_<?php echo $org_type->slug; ?>"
							data-type="<?php echo $org_type->term_id; ?>"
							style="height: 30px !important;"
							<?php echo $disabled;?> >

							<option value="">Please select a <?php echo $type; ?></option>

							<?php

								if ( $org_type->category_parent == 0 ) {

									$org_query = array(
										'post_type' => 'propel_org',
										'nopaging'  => 1,
										'orderby'   => 'name',
										'order'     => 'ASC',
										'tax_query' => array( array(
											'taxonomy'         => 'org_type',
											'field'            => 'slug',
											'terms'            => $org_type->slug,
											'include_children' => 0
										) )
									);

									$orgs = new WP_Query( $org_query );

									if ( $orgs->have_posts() ): while ( $orgs->have_posts() ):

										$orgs->the_post();

										$selected = $org == get_the_id() ? 'selected' : '';

										echo '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

									endwhile; endif;

								}


								?>
								<option value="add_organization">+ Add <?php echo $org_type->name; ?>...</option>
						</select>
					<div class="userpro-clear"></div>
				</div>
				<div class="userpro-clear"></div>
			</div>


	<?php
		}

		?>
<script>

jQuery( document ).ready( function() {

	jQuery( '.userpro-section' ).hide();

	setChildOrgs();

	jQuery( '.propel-org' ).on( 'change', function(e) {

		// I put this here so fields wouldn't duplicate on submit cause User Pro is weird
		if ( ! e.hasOwnProperty( 'originalEvent' ) ) return;

		if ( jQuery( e.target ).val() == 'add_organization' )
			addOrganization( e.target.id );
		else
			removeOrganization( e.target.id );

	} );

	jQuery( '.propel-org.parent' ).on( 'change', function(e) {

		if ( ! e.hasOwnProperty( 'originalEvent' ) ) return;

		if ( jQuery( e.target ).val() != 'add_organization' )
			setChildOrgs( e.target.id );

	} );
} );


function addOrganization( id ) {
	input = '<input type="text" id="new_propel_org_' + id +'" name="new_propel_org_' + id + '" style="width: 100% !important;margin: 15px 15px 0 0 !important;"></input>';
	jQuery( '#' + id ).after( input ).next().focus();
	jQuery( '.propel-org' ).attr( 'disabled', false );
}


function removeOrganization( id ) {
	jQuery( '#new_propel_org_' + id ).remove();
}


function setChildOrgs() {
	parent = jQuery( '.propel-org.parent' ).val();
	parentType = jQuery( '.propel-org.parent' ).data( 'type' );

	if ( parent == '' ) return;

	jQuery( '.spinner-' + jQuery( '.propel-org.parent' ).attr( 'id' ) ).show();

	jQuery.post(
		'/wp-admin/admin-ajax.php',
		{
			'action'  : 'get_child_orgs',
			'parent'  : parent,
			'type'    : parentType,
			'user_id' : undefined,
			'public'  : "1"
		},
		function( response ) {
			jQuery( '.spinner-' + response.data.parent ).hide();
			jQuery( '#' + response.data.child ).html( response.data.html ).attr( 'disabled', false);

			removeOrganization( response.data.child );

			if ( response.data.numChildren == 0 ) {
				jQuery( '#' + response.data.child ).val( 'add_organization' ).trigger("change");
				addOrganization( response.data.child )
			}
		}
	);

}
</script>
			<?php
	}


	/**
	 * Renders the league and team fields for the woocommerce review order form
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 15:16:12
	 * @edited  2015-02-25 15:33:59
	 * @edited  2015-02-26 14:54:35 - sorts orgs by ASC
	 * @edited  2015-03-03 09:50:33 - Adds spinner and better select language
	 *
	 * @param  Array   $args
	 *
	 * @action woocommerce_after_checkout_billing_form
	 */
	function render_woocommerce_fields( $args ) {

		$user = wp_get_current_user();

		if ( $user->ID != 0 ) return;

		wp_localize_script( 'propel_orgs_woocommerce', 'data', array( 'args' => $args, 'public' => true ) );

		wp_enqueue_script( 'propel_orgs_woocommerce' );

		$org_types = get_categories( array( 'taxonomy' => 'org_type', 'hierarchical' => 1 ) );


		foreach ( $org_types as $org_type ) {

			$org = get_user_meta( $user->ID, 'propel_org_' . $org_type->slug, 1 );

			if ( $org_type->parent == 0 ) {
				$parent = 'parent';
				$type = $org_type->slug;
				$disabled = '';
			} else {
				$parent = '';
				$type = get_term_by( 'id', $org_type->parent, 'org_type' );
				$type = $type->slug . ' first';
				$disabled = 'disabled';
			}

			?>

			<p class="form-row">
				<label for="<?php echo $org_type->slug; ?>">
					<?php echo $org_type->name; ?>
					<img class="spinner-<?php echo $org_type->slug; ?>" src="/wp-admin/images/spinner.gif" style="display:none;width:15px;height:15px;" />
				</label>

				<select
						class="propel-org <?php echo $parent; ?>"
						id="<?php echo $org_type->slug; ?>"
						name="propel_org_<?php echo $org_type->slug; ?>"
						data-type="<?php echo $org_type->term_id; ?>"
						<?php echo $disabled; ?> >

						<option value="">Please select a <?php echo $type; ?></option>


					<?php

			if ( $org_type->category_parent == 0 ) {

				$org_query = array(
					'post_type' => 'propel_org',
					'nopaging'  => 1,
					'orderby'   => 'name',
					'order'     => 'ASC',
					'tax_query' => array( array(
						'taxonomy'         => 'org_type',
						'field'            => 'slug',
						'terms'            => $org_type->slug,
						'include_children' => 0
					) )
				);

				$orgs = new WP_Query( $org_query );

				if ( $orgs->have_posts() ):

					while ( $orgs->have_posts() ):

						$orgs->the_post();

						$selected = $org == get_the_id() ? 'selected' : '';

						echo '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

					endwhile;

				else:

					echo '<option value="">League has no ' . $org_type->slug . 's</option>';

				endif;




			}

			echo '<option value="add_organization">+ Add ' . $org_type->name . '...</option>';

			?>
						</select>
			</p>

	<?php
		}
	}


	/**
	 * Generates a list of options for the 'team' select list in user profiles
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-12 11:07:39
	 * @edited  2015-02-26 14:54:08 - sorts orgs by ASC
	 *
	 * @return  json with 'options' in html
	 */
	function ajax_get_child_orgs() {

		$parent     = $_POST['parent'];
		$parentType = $_POST['type'];
		$public     = $_POST['public'];
		$user       = $_POST['user_id'];

		$type = get_categories(
			array(
				'taxonomy' => 'org_type',
				'hierarchical' => 1,
				'child_of' => $parentType
			)
		);

		$parentType = get_term( $parentType, 'org_type' );

		$org_query = array(
			'post_type'   => 'propel_org',
			'nopaging'    => 1,
			'post_parent' => $parent,
			'orderby'     => 'name',
			'order'       => 'ASC',
			'tax_query'   => array( array(
				'taxonomy'         => 'org_type',
				'field'            => 'slug',
				'terms'            => $type[0]->slug,
				'include_children' => 0
			) )
		);

		// If the 'get_child_orgs' action originated from a public request
		// WooCommerce and UserPro are public, wp-admin is private
		//
		// data.public is a js variable set by the wp_localize_script in each render function
		if ( $public )
			$org_query['post_status'] = array( 'publish' );
		else
			$org_query['post_status'] = array( 'publish', 'draft' );

		$child_orgs = new WP_Query( $org_query );

		$org = get_user_meta( $user, 'propel_org_' . $type[0]->slug, 1 );

		$out = '';

		if ( $child_orgs->have_posts() ):

			$out .= '<option value="">Please select a ' . $type[0]->slug . '</option>';

			while ( $child_orgs->have_posts() ):

				$child_orgs->the_post();

				$selected = $org == get_the_id() ? 'selected' : '';

				$out .= '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

			endwhile;

		else:

			$out .= '<option value="">' . $parentType->name . ' has no ' . $type[0]->slug . 's</option>';

		endif;

		$out .= '<option value="add_organization">+ Add ' . $type[0]->name . '...</option>';


		wp_send_json_success(
			array(
				'html'        => $out,
				'numChildren' => $child_orgs->found_posts,
				'parent'      => $parentType->slug,
				'child'       => $type[0]->slug
			)
		);
	}


	/**
	 * @function save_user_fields()
	 * Saves the selected or created propel_org as user_meta information for the given user
	 *
	 * Every user has the ability to belong to one or more 'propel_orgs'.
	 * These propel_orgs are stored as Custom Post Types (cpt),
	 *   and each 'org_type' has a corresponding dropdown selector in the user form.
	 *
	 * The 'user form' is a form for creating and editing the User's information.
	 * It is found in three places in core
	 *   and we additionally hook into the WooCommerce and UserPro forms (See corresponding render_ functions)
	 *
	 * This method is used to save existing and newly created orgs
	 *   to the User's profile through the use of user_meta
	 *
	 * It is hooked to three core actions to save at:
	 *   - editing an arbitrary user,  /wp-admin/user-edit.php
	 *   - editing your own user,      /wp-admin/profile.php
	 *   - registering a new user      /wp-admin/user-new.php
	 *
	 * Additionally, it also is called while creating a user through WooCommerce,
	 *   (which must be hooked separately)
	 *
	 * There are two possibilities in the saving algorithm
	 *   - [Easy Case] Save a preexisting propel_org to user_meta
	 *   - [Hard Case] First create a new propel org and then save to user_meta
	 *
	 * [Easy Case]
	 * Since there are arbitrary 'types' of orgs depending on the client (for example Leagues, Teams, etc),
	 *   and since there are an arbitrary number of orgs the user can belong to (zero, one or many),
	 *   we need to grab each $_POST key that matches 'propel_org_*' (for example 'propel_org_league' and 'propel_org_team')
	 *   to be sure we grab every possible propel_org the user may be attached to.
	 *
	 * So while looping through each $_POST key, if we find one matching 'propel_org_*',
	 *   simply add the $value (which is the propel_org's wp_posts.ID) with the current $key to the user_meta
	 *   (for example save $key 'propel_org_team' with $value '3300' to the given user_meta)
	 *
	 * [Hard Case]
	 * If the user wishes to add a propel_org that doesn't exist,
	 *   they have the option to 'add_organization' through an additional text field
	 *
	 * These text fields are added dynamically to the UI,
	 *   one text field per propel_org dropdown,
	 *   so they may only exist on exceptional occasions.
	 *
	 * Each text field borrows the name of the dropdown (propel_org_*) and prepends 'new_' to the front
	 *
	 * So while looping through each $_POST key, if we find one matching 'new_propel_org_*',
	 *   we need to first create a propel_org cpt with the given name,
	 *   then simply add the $value (which is the new propel_org's wp_posts.ID) to the user_meta.
	 *
	 * However, unlike the Easy Case above,
	 *   the $key to save as the user_meta can not be 'new_propel_org_*'.
	 *   So we must strip the '$type' from the current $key to properly save the user_meta as 'propel_org_' . $type
	 *
	 * Additionally, when creating a new propel_org,
	 *   we must also retain the propel_org parental hierarchy in the cpt.
	 *   This means if a user creates a new propel_org,
	 *   that is the child of another propel_org, we have to assign that when we insert the new propel_org into the DB.
	 *   (for example, adding a new Team that belongs to a League)
	 *
	 *   This may be confusing as the plugin allows for arbitrary relationships within the org_types
	 *   so we can't simply hard code the relationships (if new org is Team, look for League, etc).
	 *   However, it is pretty straight forward once you understand it.
	 *
	 *   To find the new propel_org's parent, we must find the parent org_type.
	 *   We get the child $org_type by removing 'new_propel_org_' from the current key
	 *   then looking up the $org_type with get_term_by().
	 *   This returns a WP_Term object, which includes the parent term id at $org_type->parent.
	 *
	 *   If the child term has a parent term, the $org_type->parent will be something greater than 0
	 *   If it is, look up the parent term with $org_parent_type = get_term()
	 *   and use $org_parent_type->slug to find the propel_org parent in the $_POST array.
	 *
	 *   For example, we know we have an $org_type of 'team'.
	 *   Look up the term id of 'team' by using get_term_by()
	 *   Then, once we have the full 'team' WP_Term assigned to $org_type
	 *   we can check to see if it has a parent term
	 *   If $org_type->parent is greater than zero, it means the term has a parent
	 *   Now, get the full term object of the parent 'league' get_term()
	 *   We know the parent cpt value is stored in the $_POST array, but we didn't know what it was called
	 *   Now that we have the term object of the parent in $org_type_parent
	 *   we can get the wp_posts.ID value of the parent, by looking in $_POST[ 'propel_org_' . $org_type_parent->slug ]
	 *   which in this case is $_POST['propel_org_league']
	 *
	 *   Now that we know the ID of the new propel_org's parent,
	 *     we can add it to the new cpt with $org['post_parent']
	 *
	 *   This is very abstract and may be confusing at first, but allows for fully dynamic relationships,
	 *   without having to push the parent-child relationship information through the UI
	 *
	 * Lastly, if the given new propel_org already exists, we need to do something else.
	 *   Right now it looks it up and uses the currently existing one, but we may want a different functionality.
	 *   @TODO
	 *
	 *
	 * [Both Cases]
	 * In both the Easy Case and the Hard Case, we need to save a duplicated user_meta piece,
	 *   the 'propel_okm_org_id', which will be sent to the OKM during Propel_LMS::request_keys()
	 *
	 * The propel_okm_org_id is stored as the wp_posts.ID of propel_org that the user belongs to.
	 * It is duplicated during this saving, so for example you will have user_metas of
	 *   propel_org_team and propel_okm_org_id with the same propel_org ID
	 *
	 * Since there are possibly many propel_orgs a user belongs to,
	 *   we need a priority to know which one to save and send to the OKM
	 *
	 *   This priority is saved in the PROPEL Orgs Settings page
	 *   [/wp-admin/edit.php?post_type=propel_org&page=propel-orgs-settings]
	 *
	 *   For example, if we set the priority org_type to 'team',
	 *   we will set the propel_okm_org_id to the user's team, instead of their league
	 *
	 *   However, if they don't have a 'team' set, propel_okm_org_id should be set to another org
	 *   We will look for the parent of the priority org_type, up the chain until we find one set, then assigning the first one.
	 *   If we don't find any org_type set, we will not assign a propel_okm_org_id
	 *
	 *
	 * [FUBAR]
	 * UserPro filter problem
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-12 13:58:15
	 * @edited  2015-03-02 11:26:38 - Adds org to user meta
	 * @edited  2015-03-03 10:05:47 - Refactors for proper $org saving
	 * @edited  2015-03-04 15:51:37 - Major logic refactoring and comments added
	 * @edited  2015-03-10 15:40:06 - Refactors to prevent blank 'propel_okm_org_id' overwrite
	 * @edited  2015-03-10 16:35:11 - Refactors to only set 'propel_okm_org_id' if propel_org has _org_id
	 *
	 * @param   int   $user_id   The user id
	 *
	 * @from    $this->save_woocommerce_user_fields
	 *
	 * @action  edit_user_profile_update
	 * @action  personal_options_update
	 * @action  user_register
	 */
	static function save_user_fields( $user_id ) {

		// An associative array of term_id => propel_org.ID for the propel_orgs the user belongs to
		// Used during the [Both Cases] section at the end
		$orgs = array();

		// Work through each POST item, looking for 'propel_org_*' and 'new_propel_org_*' keys
		// $key   is propel_org_* or new_propel_org_*
		// $value is wp_posts.ID of propel_org_* or new name string of new_propel_org_*
		foreach ( $_POST as $key => $value) {

			// [Easy Case] saving a org id that already exists
			//   Don't add the org if the value is blank or 'add'
			if ( substr( $key, 0, 11 ) == "propel_org_" && $value != "add_organization" && $value !== "" ) {

				// Save the selected meta with key as is (for example, propel_org_team)
				update_user_meta( $user_id, $key, $value );

				$org_type = get_term_by( 'slug', str_replace( 'propel_org_', '', $key ), 'org_type' );

				$orgs[$org_type->term_id] = $value;

			} /* End [Easy Case] */


			// [Hard Case] saving a newly created org
			// A field is added for every organization, so 'new_' is added for these fields
			if ( substr( $key, 0, 15 ) == "new_propel_org_" && $value !== "" ) {

				add_filter( 'userpro_pre_profile_update_filters', array( $this, 'update_form_array' ) );

				$org = array(
					'post_title'  => wp_strip_all_tags( $value ),
					'post_status' => 'draft',
					'post_type'   => 'propel_org',
				);

				$org_type = str_replace( 'new_propel_org_', '', $key );


				/* FIND POST PARENT */
				// Look up the parent type of the current type, if it exists
				// For example, find the parent of a 'team', which would be a 'league'
				$org_type = get_term_by( 'slug', $org_type, 'org_type' );

				// We need to set the 'post_parent' to the ID of the parent post
				if ( $org_type->parent > 0 ) {

					// get_term returns a WP_Term object
					$org_type_parent = get_term( $org_type->parent, 'org_type' );

					$org['post_parent'] = $_POST['propel_org_' . $org_type_parent->slug];

				}


				/* EXISTING PROPEL_ORG */
				// If the propel_org they gave you already exists, use the preexisting one
				$exists = get_page_by_title( $value, OBJECT, 'propel_org' );

				if ( ! empty( $exists ) ) { // It exists, add to user

					// Add to user
					update_user_meta( $user_id, 'propel_org_' . $org_type->slug, $exists->ID );

					$new_org_id = $exists->ID;

				} else { // It doesn't exist, create propel_org and add to user

					// Create propel_org
					$new_org_id = wp_insert_post( $org );

					// Add to user
					update_user_meta( $user_id, 'propel_org_' . $org_type->slug, $new_org_id );

					// Terms must be set in a separate function
					wp_set_object_terms( $new_org_id, $org_type->name, 'org_type' );

				}

				// Push org_id to array for later use in [Both Cases]
				$orgs[$org_type->term_id] = $new_org_id;


				// @TODO This is an ugly work around.
				// UserPro RESAVES all the $_POST information with their $form variable ( see $this->update_form_array )
				// So when we create a new org, we must copy that info to the 'propel_org_*' item
				$_POST['propel_org_' . $org_type->slug] = $new_org_id;


			} /* End [Hard Case] */

		} /* End foreach $_POST */


		// [Both Cases]
		// Save the selected meta as 'propel_okm_org_id' for OKM key generation [Propel_LMS::request_keys()]
		//   (if the current org_type is the privileged one)
		$propel_orgs = get_option( 'propel-orgs' );

		// When WooCommerce is creating a new user $orgs will be full of $_POST[propel_org_*] fields from above
		// But when an existing user buys more keys, $orgs will be empty, as there are no $_POST[propel_org_*] fields above
		//
		// Returning User
		if ( empty( $orgs ) ) {

			// Don't do anything with the 'propel_okm_org_id' user_meta

		// New User, with $orgs set
		} else {

			// If there is a priority org_type set,
			if ( isset( $org_type_priority ) ) {

				// A term ID
				$org_type_priority = $propel_orgs['org_type_priority'];

				if ( array_key_exists( $org_type_priority, $orgs ) ) {
					// set the propel_okm_org_id to the propel_org id
					$propel_okm_org_id = $orgs[$org_type_priority];
				} else {
					// otherwise, use the term parent
					$propel_okm_org_id = self::find_parent_okm_org_id( $org_type_priority, $orgs );
				}

			// Else if there is no priority set, just grab the last propel_org with an _org_id set
			} else {

				foreach ( $orgs as $term_id => $propel_org_id ) {

					// _org_id is the external OKM organization_id
					$orgs_org_id = get_post_meta( $propel_org_id, '_org_id', true );

					if ( $orgs_org_id )
						$propel_okm_org_id = $propel_org_id;

				}

			}

			if ( isset( $propel_okm_org_id ) )
				update_user_meta( $user_id, 'propel_okm_org_id', $propel_okm_org_id );

		}


	}


	/**
	 * A recursive function to find the parent term in the given array
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-03-04 15:16:10
	 *
	 * @from    $this->save_user_fields
	 * @from    $this->find_parent_okm_org_id
	 *
	 * @param   int     $child               The given WP_Term->term_id of the org_type
	 * @param   array   $orgs                The associative array of term_id => propel_org.ID (team => cpt ID)
	 *
	 * @return  int     $propel_okm_org_id   The wp_posts.ID of the desired propel_org
	 */
	static protected function find_parent_okm_org_id( $child, $orgs ) {

		if ( $child == 0 ) {

			$propel_okm_org_id = '';

		} else {

			$child_term = get_term( $child, 'org_type' );

			$parent = $child_term->parent;

			if ( array_key_exists( $parent, $orgs ) ) {
				// set the propel_okm_org_id to the propel_org id
				$propel_okm_org_id = $orgs[$parent];
			} else {
				// otherwise, use the term parent
				$propel_okm_org_id = self::find_parent_okm_org_id( $parent, $orgs );
			}

		}

		return $propel_okm_org_id;

	}


	/**
	 * Saves the orgs user meta information after purchase
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 15:16:29
	 * @edited  2015-02-25 15:39:41
	 *
	 * @param   int   $order_id   The order id
	 *
	 * @action  woocommerce_checkout_update_order_meta
	 */
	function save_woocommerce_user_fields( $order_id ) {
		$order = new WC_Order( $order_id );
		$user_id = $order->user_id;

		self::save_user_fields( $user_id );

	}


	/**
	 * When UserPro creates a new user, they update the new user at the end of their 'new_user' function process.
	 * This 'userpro_update_user_profile' process was overwriting our just written 'propel_org_' user meta data
	 * 
	 * To make sure the correct data is retained, we have to filter their 'form' array, a duplicate of $_POST
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-25 10:44:35
	 *
	 * @filter  userpro_pre_profile_update_filters
	 * 
	 * @param   Array   $form      The UserPro form array, a duplicate of the $_POST request
	 * @param   int     $user_id   The created user
	 * 
	 * @return  Array  $form       The amended UserPro form array
	 */
	function update_form_array( $form, $user_id ) {

		foreach ( $form as $key => $value ) {

			if ( substr( $key, 0, 11 ) == "propel_org_" ) {

				$form[$key] = $_POST[$key];

			}

		}

		return $form;

	}

}

new Propel_Organizations();