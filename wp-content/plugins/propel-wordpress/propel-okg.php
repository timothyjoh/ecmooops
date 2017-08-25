<?php
/**
 * Propel OKG
 * For wholesale purchases of keys (Like AHA OKG)
 * OKG Administrators manage multiple orgs
 * Note - shortcode functionality is in:
 *  /shortcodes/okg-shortcodes.php
 *
 * Assumes that plugins/woo-products-list (wcplpro) is installed
 */

Class Propel_OKG {

	function __construct() {

		////////
		//// Menuing / General OKG UI
		add_filter( 'wp_nav_menu_items', 
		  array( $this, 'okg_menu' ), 10, 2 );

		add_action( 'template_redirect',
		  array( $this, 'special_homepage' ));

		////////
		//// Create / Modify OKMs
		add_action( 'wp_ajax_add_org_child',
		  array( $this, 'wp_ajax_add_org_child' ) );

		add_action( 'wp_ajax_does_org_exist',
		  array( $this,'does_org_exist_from_post') ); // priv
		add_action( 'wp_ajax_nopriv_does_org_exist',
		  array( $this,'does_org_exist_from_post') ); // nopriv

		add_action( 'wp_ajax_propel_newokm', 
		  array( $this, 'ajax_new_okm_from_post') ); // priv
		add_action( 'wp_ajax_nopriv_propel_newokm', 
		  array( $this, 'ajax_new_okm_from_post') ); // nopriv

		add_action( 'wp_ajax_propel_editokm', 
		  array( $this, 'ajax_edit_okm_from_post') ); // priv
		add_action( 'wp_ajax_nopriv_propel_editokm', 
		  array( $this, 'ajax_edit_okm_from_post') ); // nopriv

		////////
		//// My Orders
		add_filter( 'woocommerce_my_account_my_orders_columns', //woo calls this deprecated, hence next add_filter
		  array( $this, 'extra_okg_columns') );

		add_filter( 'woocommerce_account_orders_columns', // does nothing, previous is deprecated, so ...
		  array( $this, 'extra_okg_columns') );

		add_action( 'woocommerce_my_account_my_orders_column_okg_okm_column',
		  array( $this, 'okg_okm_column') );

		////////
		//// OKG Cart
		add_action('wcplpro_inside_add_to_cart_form',
		  array( $this, 'hidden_field_for_okm' ) );

		add_filter('woocommerce_add_cart_item',
		  array( $this, 'add_to_cart_for_okm' ) );

		add_filter( 'woocommerce_get_cart_item_from_session', 
		  array( $this, 'get_cart_item_from_session' ), 10, 3 );

		add_filter( 'woocommerce_get_item_data', 
		  array( $this, 'get_item_data'), 10, 2 );

		add_action( 'woocommerce_add_order_item_meta',
		  array( $this, 'transfer_item_data_to_order'), 10, 2);

		add_action( 'woocommerce_before_cart',
		  array( $this, 'okg_admins_show_receiving_okm' ) );

		////////
		//// Checkout
		add_action( 'woocommerce_review_order_before_cart_contents',
		  array( $this, 'okg_admins_show_receiving_okm' ) );

		add_filter( 'woocommerce_available_payment_gateways', 
		  array( $this, 'purchase_orders_okg_only') );		

		////////
		//// After an Order is complete
		add_action( 'woocommerce_thankyou', 
		  array( $this, 'auto_complete_okg_purchase_order') );

		add_action( 'woocommerce_thankyou',
		  array( $this, 'after_order_redirect') );

		add_filter( 'propel_filter_data_before_generate_keys',
		  array( $this, 'give_to_okm'), 10, 2 );

		add_action( 'woocommerce_view_order',
		  array( $this, 'okg_view_order_recipient_okm' ), 8 ); // <10, so the top of the order details page

		add_action( 'woocommerce_view_order',
		  array( $this, 'style_po' )); // same hook as previous, does not matter where

	}

	/***************************************************
	                STATIC FUNCTIONS
	***************************************************/

 /**  create_role()
  * Creates the 'OKG Administrator' role during plugin activation
  */
	public static function create_role() {
		$role = get_role( 'okg_admin' );
		if ( $role ) {
			$role->add_cap('administer_okg');
			$role->add_cap('pay_via_purchase_order');
			return;
		}
		$capabilities = get_role( 'subscriber' )->capabilities;	
		$newly_created_role = add_role( 'okg_admin', 'OKG Administrator', $capabilities );
		$newly_created_role->add_cap('administer_okg');
	}

	public static function current_okg_id() {
		$user_id = get_current_user_id();
		$okg_id_user = get_user_meta( $user_id, 'propel_org_admin', true);
		if( false === $okg_id_user || is_wp_error( $okg_id_user) ) {
			$okg_id_user = 0;
		}
		return $okg_id_user;
	}

	/**
	 * Find out who received the okg key purchase
	 * For now (12/20/16), this assumes entire orders are submitted for one okm each
	 * (that is, all items have same _for_okm value)
	 */
	public static function get_recipient_okm_from_order( $order ) {
		$items = $order->get_items();
		$item_id = array_keys( $items )[0]; // only look at first item
		return $order->get_item_meta( $item_id, '_for_okm', true );
	}

	/**
	 * Inspect the global WooCommerce object (if not empty) for the recipient okm
	 */
	public static function get_recipient_okm_from_cart() {
		global $woocommerce;
		$items = $woocommerce->cart->get_cart();
		if( empty($items) ) {
			return false;
		}
		$item = array_values( $items )[0]; // only look at first item
		if( array_key_exists('for_okm', $item) ) {
			return $item['for_okm'];
		} else {
			return false;
		}
	}

	/***************************************************
	             END OF STATIC FUNCTIONS
	***************************************************/


	/**
	 * For OKG Admins, redirect away from the designated homepage or /my-courses
	 * to the 'OKG Order History' page
	 */
	public function special_homepage() {
		$okg_order_history = 'okg-order-history';
		if( !current_user_can('administer_okg') || is_page($okg_order_history) ) {
			return; // without changing it
		}
		if( is_front_page() || is_page('my-courses') ){
			wp_safe_redirect( $okg_order_history );
			exit();
		}
	}

	public function wp_ajax_add_org_child() {  // TODO - use initialize_postdata_with_tenant_secret_key()
		$propel_settings = get_option( 'propel_settings' );
		$tenant_secret_key = $propel_settings['okm_tenant_secret_key'];

		$post_data = array(
		                'parent_id'          => $_POST['parent_id'],
		                'child_id'           => $_POST['child_id'],
		                'tenant_secret_key'  => $tenant_secret_key
		              );
		$response = $this->ping_api_with_formdata( $post_data, 'add_org_child' );
		wp_send_json_success( $response );
	}

	/**
	 * Use the API to connect a (newly created) org to the current user's org
	 */
	public function add_org_child( $child_id ) {  // TODO - use initialize_postdata_with_tenant_secret_key()
		error_log( 'inside add_org_child for child_id: ' . $child_id );
		$parent_id = get_user_meta( get_current_user_id(), 'propel_org_admin', true);
		$propel_settings = get_option( 'propel_settings' );
		$tenant_secret_key = $propel_settings['okm_tenant_secret_key'];

		$post_data = array(
		                'parent_id'          => $parent_id,
		                'child_id'           => $child_id,
		                'tenant_secret_key'  => $tenant_secret_key
		              );
		error_log( 'about to call ping_api_with_formdata for child_id: ' . $child_id );
		$response = $this->ping_api_with_formdata( $post_data, 'add_org_child' );
		return $response;
	}

	/**
	 * Create a new WP user based on POST data to administer a (newly created) org.
	 * Assign the id as the user's okm.
	 * Assumes that the following fields exist as POST params:
	 * contact_first_name
	 * contact_last_name
	 * contact_email
	 * name // ie., Organization Name
	 */
	private function org_child_user_account( $child_id ) {
	    $current_user = wp_get_current_user();
	    $okm_admin_is_new = true;
		$new_pass = wp_generate_password( 24, false );
		$new_user_id = wp_create_user( $_POST['contact_email'], $new_pass, $_POST['contact_email'] );
		if( is_wp_error($new_user_id) ) {
			$error_code = $new_user_id->get_error_code();
			if( 'existing_user_email' === $error_code ) {
				$okm_admin_is_new = false;
				// update an existing user
				$new_user_id = get_user_by( 'email', $_POST['contact_email']);
			} else {
				// trigger an email to the OKG Admin with the error
				$admin_message = __('There was an error creating a new OKM admin account for ', 'propel');
				$admin_message .= $_POST['contact_email'];
				$admin_message .= __('. The error message was: ', 'propel');
				$admin_message .= $new_user_id->get_error_message();
				wp_mail( $current_user->user_email, __('New OKM Error: ','propel') . $error_code, $admin_message );
				return $new_user_id; // === a WP_Error 
			}
		} 
		// email new user about his / her new account
		$this->email_new_okm_admin( $okm_admin_is_new, $new_pass, $current_user );
		
		// supply personal details:
		if( $okm_admin_is_new ){
			$usermeta_result = wp_update_user( array( 	'ID' => $new_user_id, 
														'first_name' => $_POST['contact_first_name'],
														'last_name'  => $_POST['contact_last_name'],
														) );
		}
		// assign org admin status
		$role_result = wp_update_user( array( 	'ID' => $new_user_id, 
												'role' => 'org_admin' ) );

		// assign the $child_org id to them
		update_user_meta( $new_user_id, 'propel_org_admin', $child_id );

	}

	private function email_new_okm_admin( $okm_admin_is_new, $new_pass, $current_user ) {
		$okm_message = sprintf( esc_html__( 'Your organization, %s, ', 'propel' ), $_POST['name'] );
		$okm_message .= __( 'was added to ', 'propel' ) . get_bloginfo( 'name' ); // e.g., TMCI
		$okm_message .= __(" with an online key manager to manage and track your learners' course keys.",'propel');
		$okm_message .= __(" Please log into your account as:\r\n ",'propel') . $_POST['contact_email'];
		if( $okm_admin_is_new ) {
			$okm_message .= __(" \r\nYour temporary password is:\r\n ",'propel') . $new_pass;
		}
		$okm_message .= __(" \r\nThe login page is:\r\n ",'propel') . site_url( '/login/' );
		$okm_message .= __(" \r\n\r\n If you believe this was done by mistake, please email ",'propel') . $current_user->user_email;
		$okm_message .= __(" \r\n\r\n Thanks.",'propel');
		// subject line
		$okm_subject = __('Key Management for ','propel') . $_POST['name'];
		wp_mail( $_POST['contact_email'], $okm_subject, $okm_message );
	}

	public function does_org_exist_from_post() {
		$post_data = $this->initialize_postdata_with_tenant_secret_key();
		if( !$_POST ) {
			wp_send_json_error(); // if this ajax call was made without post data, get out
		} else {
			$post_data = array_merge( $post_data, $_POST );
			// error_log(http_build_query($post_data));
		}
		$this->ping_api_with_formdata( $post_data, 'organization_exists' );
	}

	/**
	 * Private helper:
	 *  return $postdata as an array with ( 'tenant_secret_key' => $tsk )
	 */
	private function initialize_postdata_with_tenant_secret_key() {
		$propel_settings = get_option( 'propel_settings' );
		$tenant_secret_key = $propel_settings['okm_tenant_secret_key'];
		return array( 'tenant_secret_key'  => $tenant_secret_key );
	}

	/**
	 * Private helper:
	 *  ping the and Propel OKM API with given data to given endpoint
	 */
	private function ping_api_with_formdata( $post_data, $endpoint ) {
		$post_query = '?' . http_build_query( $post_data );

		$response = Propel_LMS::ping_api( $post_query, $endpoint, 'POST', 'application/x-www-form-urlencoded' );

		return $response;
		// wp_send_json_success( $response );		
	}

	public function hidden_field_for_okm( $product_id ) {
		$current_okg_id = Propel_OKG::current_okg_id();
		?>
		<input type="hidden" class="propel-for-okm" name="propel-<?php echo $product_id; ?>_for_okm" value="<?php echo $current_okg_id; ?>" />
		<?php
	}

	/**
	 * not used
	 */
	public function replacement_buy_button( $product_id ) {
		?>
		<button id="" type="submit" data-product_id="<?php echo $product_id?>" class="propel-add_to_cart single_add_to_cart_button button button_theme ajax avia-button fusion-button button-flat button-round">Add to cart</button>
		<?php
	}

	/**
	 * Add the OKM for whom the item is for to the $cart_item_data
	 * @ref https://docs.woocommerce.com/wc-apidocs/source-class-WC_Cart.html#
	 */
	public function add_to_cart_for_okm( $cart_item_data, $cart_item_key ) {
		// Maybe modify $cart_item_data, and return it
		error_log('-- HEY! In add_to_cart_for_okm() !' );	
		if( !current_user_can('administer_okg') ) {
			return $cart_item_data; // without changing it
		}
		error_log('--~ HEY!! Proper permissions to add_to_cart_for_okm()');	
		if( $_POST && array_key_exists('for_okm', $_POST ) ){
			error_log('--~~ HEY!!! In add_to_cart_for_okm() I can add' . $_POST['for_okm'] );	
			$cart_item_data['for_okm'] = $_POST['for_okm'];
		}
		return $cart_item_data;
	}

	public function get_cart_item_from_session( $cartItemData, $cartItemSessionData, $cartItemKey ) {
	    if ( isset( $cartItemSessionData['for_okm'] ) ) {
	        $cartItemData['for_okm'] = $cartItemSessionData['for_okm'];
	    }
	    return $cartItemData;
	}

	public function get_item_data( $data, $cartItem ) {
		$for_okm = $cartItem['for_okm'];
		$org_name = Propel_Settings::get_org_name_from_id_by_api( $for_okm );
		$name_and_id = $org_name . ' (' . $for_okm . ')'; 
	    if ( isset( $cartItem['for_okm'] ) ) {
	        $data[] = array(
	            'name' => 'For OKM',
	            'value' => $name_and_id,
	        );
	    }

	    return $data;
	}

	public function transfer_item_data_to_order( $item_id, $values ) {
		if( array_key_exists( 'for_okm', $values ) ) {
			$r = wc_add_order_item_meta( $item_id, '_for_okm', $values['for_okm']);
			// error_log('---~~~~ transfer result was '. $r );
		}
	}

	/**
	 * entirely replace menu for OKG admins
	 */
	public function okg_menu($menu,$args) {  
		if ( !is_user_logged_in() || !current_user_can('administer_okg') ){
		    return $menu;
		} else if( 'Main Menu - no mega' === $args->menu->name ) {
			ob_start();
			?>
			<li><a href="<?php echo site_url('/okg/'); ?>"><i class="fa fa-key" aria-hidden="true"></i> Create Keys</a></li>
			<li><a href="<?php echo site_url('/manage-okms/'); ?>"><i class="fa fa-building-o" aria-hidden="true"></i> Manage OKMs</a>			

			<?php
			return ob_get_clean();
		} else {
		    ob_start();
			?>
			<li><a href="<?php echo site_url('/okg-order-history/'); ?>">Home / Order History</a></li>
			<li><a href="<?php echo site_url('/cart/'); ?>">Cart</a></li>
			<?php
			$logout_html = $this->grab_snippet_from_html( $menu, "wpum-logout-nav" );
		    return ob_get_clean() . $logout_html;
	    }
	}

	private function grab_snippet_from_html( $html, $classname ) {
		$snippet = '';
		$doc = new DOMDocument();
		$doc->loadHTML( $html );
		$finder = new DomXPath($doc);
		$nodes = $finder->query("//*[contains(@class, '$classname')]");
		foreach ($nodes as $node) {
			$snippet .= $node->ownerDocument->saveXML( $node );
		}
		return $snippet;
	}

    public function ajax_new_okm_from_post() { // TODO - use initialize_postdata_with_tenant_secret_key()
		$propel_settings = get_option( 'propel_settings' );

		$post_data = array(
		                'tenant_secret_key' => $propel_settings['okm_tenant_secret_key'],
		              );

		if( !$_POST ) {
			wp_send_json_error(); // if this ajax call was made without post data, get out
		} else {
			// see propel_okg_shortcodes::fields_for_new_okm()
			$post_data = array_merge( $post_data, $_POST );	
		}

		$response = Propel_LMS::ping_api( $post_data, 'sync_org' );
		if( array_key_exists('organization', $response ) ) {
			error_log('created org with id ' . $response['organization']['id'] );
			$add_org_response = $this->add_org_child( $response['organization']['id'] );
			$add_user_response = $this->org_child_user_account( $response['organization']['id'] );
			wp_send_json_success( $add_org_response );
		} else {
			wp_send_json_error( $response ); // no organization created means an error
		}
		wp_send_json_success( $response );
    }

    public function ajax_edit_okm_from_post() {
    	$this->ajax_new_okm_from_post(); // functionality is identical
    }

    /**
     * Override the $post_data['organization_id'] with order_item_meta '_for_okm' value
     */
    public function give_to_okm( $post_data, $order ) {
    	if( !current_user_can('administer_okg') ) {
    		return $post_data;
    	}
    	error_log('--~~~ Preparing to give keys to OKM ... ' );

		$org_id = $this->get_recipient_okm_from_order( $order );
		if( $org_id ) {
    		error_log('--~~~ Giving keys to OKM: '. $org_id );
			$post_data['organization_id'] = $org_id;
		} else {
			error_log('--~~~ Failed to Give keys to OKM. Recipient not found. :-(' );
		}
    	return $post_data;
    }

    /**
     * Modifying the columns in the order history table.
     * Thanks to: http://stackoverflow.com/questions/13683162/woocommerce-show-custom-column
     */
	public function extra_okg_columns( $columns ) {
		if( !current_user_can('administer_okg') ) {
			return $columns;
		}
		$new_columns = (is_array($columns)) ? $columns : array();
		unset( $new_columns['order-actions'] );

		//edit this for you column(s)
		//all of your columns will be added before the actions column
		$new_columns['okg_okm_column'] = __('Recipient Organization', 'propel');
		//stop editing

		if( is_array($columns) && array_key_exists('order-actions', $columns) ) {
			$new_columns['order-actions'] = $columns['order-actions'];
		}
		return $new_columns;
	}

   	/**
     * Modifying the columns in the order history table.
     * Thanks to: http://stackoverflow.com/questions/13683162/woocommerce-show-custom-column
     */
	public function okg_okm_column( $order ){
		if( !current_user_can('administer_okg') ) {
			return;
		}
		$org_name = 'unknown';
		$org_id = $this->get_recipient_okm_from_order( $order );

		if( !$org_id ) {
			$org_id = get_user_meta( get_current_user_id(), 'propel_org_admin', true);
			/////////////// if they are not an org admin, skip it:
			if( '' === $org_id ) {
				echo 'No okm is associated with your account or with this purchase. Sorry.';
				return;
			}
		}

		/////////////// cache org names to limit network traffic a bit:
		if( defined('PROPEL_ORG_CACHED_' . $org_id ) ){   
			$org_name = constant(PROPEL_ORG_CACHED_ . $org_id);
		} else {
			$org_name = Propel_Settings::get_org_name_from_id_by_api( $org_id );
			define( 'PROPEL_ORG_CACHED_' . $org_id, $org_name );
		}
		echo $org_name;
	}

	/**
	* Displays the "For OKM" data in a pleasing way, and hides it from each item (course) listed
	*/
	function okg_admins_show_receiving_okm() {
		if( !current_user_can('administer_okg') ) {
			return; // only OKG Admins here
		}
		$for_okm = $this->get_recipient_okm_from_cart();
		if(false!==$for_okm) { 
			$org_name = Propel_Settings::get_org_name_from_id_by_api( $for_okm );
			$name_and_id = $org_name . ' (' . $for_okm . ')'; 
			?>
			<p class="propel-for-okm propel-override"><?php echo __('FOR OKM: ','propel') . $name_and_id; ?></p>
		<?php } ?>
		<style>
			.propel-for-okm{
				font-weight: bold;
			}
			dl.variation {
				display: none;
			}
			div.product-container {
				float: left;
			}
		</style>
		<?php
	}

	/**
	 * Uses the woocommerce_available_payment_gateways
	 * filter, puts P.O. purchases at the top
	 */
	public function purchase_orders_okg_only( $available_gateways ) {
		if ( isset( $available_gateways['woocommerce_gateway_purchase_order'] ) ) {
			if( current_user_can('administer_okg') || 
				current_user_can('org_admin') ||
				current_user_can('administrator') ) {
				// choose it:
				$available_gateways['woocommerce_gateway_purchase_order']->chosen = true;				
			} else {
				// remove it:
				unset( $available_gateways['woocommerce_gateway_purchase_order'] );
			}
		}
		return $available_gateways;
	}

	public function auto_complete_okg_purchase_order( $order_id ) { 
	    if ( !current_user_can('administer_okg') || !$order_id ) {
	        return;
	    }
	    $order = wc_get_order( $order_id );
	    $order->update_status( 'completed' );
	}

	/**
	 * Send OKG Admins to their order history after purchase
	 */
	public function after_order_redirect() {
		if( current_user_can('administer_okg') ) {
			wp_safe_redirect('/okg-order-history');
			exit();
		}
	}

	/**
	 * For OKGs, show the recipient on the order details page
	 */
	public function okg_view_order_recipient_okm( $order_id ) {
		if( !current_user_can('administer_okg') ) {
			return false;
		}
		$order = new WC_Order( $order_id );
		$okm_id = $this->get_recipient_okm_from_order( $order );
		$okm_name = Propel_Settings::get_org_name_from_id_by_api( $okm_id );
		if( false === $okm_name ) {
			return false;
		}
		?> <p class="propel-behalf"> <?php
		_e('Ordered on behalf of ','propel');
			?> <span class="propel-okm-name"> <?php
			echo $okm_name;
			?> </span> <?php
		?> </p> <?php
	}

	public function style_po( $order_id ) {
		?>
		<style type="text/css">
			.woocommerce .form-field-wide {
				padding: 0px;
			}
			.woocommerce .form-field-wide + h2 {
				font-style: italic;
				padding: 0px 0px 15px 0px;
			}
		</style>
		<?php
	}

}

new Propel_OKG();