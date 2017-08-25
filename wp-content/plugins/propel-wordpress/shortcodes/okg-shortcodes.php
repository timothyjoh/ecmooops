<?php
/**
 * Propel OKG (online key generator) shortcode functionality
 * @author Peter Malcolm
 * Note - supplemental okg functionality is in:
 *  /propel-okg.php
 *
 * Assumes that plugins/woo-products-list (wcplpro) is installed
 */

class propel_okg_shortcodes {

	function __construct() {

		add_shortcode( 'propel_select_connected_okm',
			array( $this, 'child_org_picker' ) ); 

		add_shortcode( 'propel_okg_orders',
			array( $this, 'okg_orders' ) ); // supplemental columns in ../propel-okg.php

		add_shortcode( 'propel_manage_okms',
			array( $this, 'manage_okms' ) );

		add_shortcode( 'propel_create_okms',
			array( $this, 'create_okms' ) );

		add_shortcode( 'propel_edit_okms',
			array( $this, 'edit_okms' ) );

		add_shortcode( 'propel_woo_list_select2_fix',
			array( $this, 'select2_fix' ) ); 
	}


	/**
	 * shortcode to fix weird error
	 * wcplpro.js?ver=4.6.1:621 Uncaught TypeError: jQuery(...).select2 is not a function
	 * 
	 * similar bug described here:
	 * https://codecanyon.net/item/woocommerce-products-list-pro/17893660/comments?page=2
	 * by ChampCorp
	 * 
	 * (better fix might be to see if jQuery is loaded in twice on the cart page?)
	 */
	function select2_fix( $given_atts ) {
		// ignore atts for now
		?>
		<script type="text/javascript">
			if( !jQuery().select2 ) {
				jQuery.fn.extend({ select2: function(){ 
						return { on: function( a1, a2 ) { /* nada */ } };
					} 
				});
			}
		</script>
		<?php
	}

	function child_org_picker( $given_atts ) {
		$atts = shortcode_atts( array(
			'okg_id' => ''
		), $given_atts, 'child_org_picker' );

		// hide products
		?>
			<style id='hide_wcplpro'> 
				.wcplprotable_wrap, .propel-globalcartbtn { display: none; } 
			</style>
			<style> 
				.globalcartbtn { display: none; } 
			</style>

		<?php 
		$allow_info = $this->allow_access( $atts );
		if( 'array' !== gettype( $allow_info ) || !$allow_info['allowed'] ) {
			return $allow_info;
		}
		$okg_id = $allow_info['okg_id'];
		$this->enqueue_okg_js( array( 'here' => 'okg_child_org_picker') );
		$opener = '<div class="propel-okg propel-override">' . $this->header_info_html( $atts, $okg_id );

		$currently_carted_recipient_okm = Propel_OKG::get_recipient_okm_from_cart();

		$label = '<p>' . __('Which Organization are you purchasing for?') . '</p>';
		$redir_notice = '<h2 id="redir_notice" style="display:none;">';
		$redir_notice .= 'You are being redirected ';
		for( $i = 0; $i < 5; $i++ ) {
			$redir_notice .= '<span class="redirdot">. </span>';
			$redir_notice .= '<span class="propel-spacer"> </span>';
		}
		$redir_notice .= '</h2>';
		$buy_btn_top = '<a data-position="top" data-active="no" href="#globalcart" class="propel-globalcartbtn" id="gc__top">Add selected to cart<span class="vt_products_count"> (0)</span></a>';
		$buy_btn_bottom = '<a data-position="bottom" data-active="no" href="#globalcart" class="propel-globalcartbtn" id="gc__bottom">Add selected to cart<span class="vt_products_count"> (0)</span></a>';
		$closer = '</div> <!-- end .propel-okg div -->';

		if( false === $currently_carted_recipient_okm ) {
			$html = $label . $this->child_org_picker_html( $atts, $okg_id );
		} else {
			$okm_name = Propel_Settings::get_org_name_from_id_by_api( $currently_carted_recipient_okm );
			$html = '<p>' . __('You have an order in process. ', 'propel');
			$html .= '<a href="'.site_url('/cart/').'">';
			$html .= __('Please finish purchasing keys for ', 'propel');
			$html .= $okm_name;
			$html .= '</a>';
			$html .= __(' before returning to create more keys.', 'propel') . '</p>';
			$html .= '<a id="propel-add-more" data-okm="' . $currently_carted_recipient_okm . '" class="button" href="#">';
			$html .= __('Or add more keys to this order', 'propel');
			$html .= '</a>';
		}
		$html .= $redir_notice . $buy_btn_top . $buy_btn_bottom;

		return $opener . $html . $closer;
	}

   /**
    * A shortcode for just orders, courtesy of:
    * http://stackoverflow.com/questions/29980505/in-woocommerce-is-there-a-shortcode-page-to-view-all-orders
    */
   function okg_orders( $given_atts ) {
		$atts = shortcode_atts( array(
			'okg_id' => '',
        	'order_count' => -1
		), $given_atts, 'okg_orders' );
		$allow_info = $this->allow_access( $atts );
		if( 'array' !== gettype( $allow_info ) || !$allow_info['allowed'] ) {
			return $allow_info;
		}
		$okg_id = $allow_info['okg_id'];
		$this->enqueue_okg_js( array( 'here' => 'okg_orders') );
		$opener = '<div class="propel-okg propel-override">' . $this->header_info_html( $atts, $okg_id );
		$label = '<h2>' . __('OKG Order History', 'propel') . '</h2>';
		ob_start();
		wc_get_template( 'myaccount/my-orders.php', array(
	        'current_user'  => get_user_by( 'id', get_current_user_id() ),
	        'order_count'   => $atts['order_count']
	    ) );

	    $just_orders = ob_get_clean();
	    if( '' === $just_orders ) {
	    	$just_orders = '<p>' . __('You have not made any orders yet.', 'propel') . '</p>';
	    }
	    // create keys button
	    $create_button = '<a href="'.site_url('/okg/').'" class="button">';
	    $create_button .= __('Create Keys', 'propel').' &gt;';
	    $create_button .= '</a>';

	    $html = $create_button . $just_orders . $create_button;
	    $closer = '</div><!-- end propel-okg div -->';
    	return $opener . $label . $html . $closer;
		// return  $this->okg_orders_html( $atts, $okg_id );
   }

   function manage_okms( $given_atts ) {
		$atts = shortcode_atts( array(
			'okg_id' => ''
		), $given_atts, 'manage_okms' );
		$allow_info = $this->allow_access( $atts );
		if( 'array' !== gettype( $allow_info ) || !$allow_info['allowed'] ) {
			return $allow_info;
		}
		$okg_id = $allow_info['okg_id'];
		$this->enqueue_okg_js( array( 'here' => 'manage_okms') );
		$opener = '<div class="propel-okg propel-override">';
		$opener .= $this->header_info_html( $atts, $okg_id );
		$opener .= '<h1 class="propel-title propel-manage">' . __('Manage Your OKMs', 'propel') . '</h1>';
		$new_link = '<a class="button propel-new-okm-button propel-override" ';
		$new_link .= 'href="' . site_url('/create-okms/') .'">';
		$new_link .= __('+ Create a new OKM','propel');
		$new_link .= '</a>';
		$manage_grid = $this->manage_okms_grid_html( $atts, $okg_id );
	    $closer = '</div><!-- end propel-okg div -->';
		return $opener . $new_link . $manage_grid . $new_link . $closer;
   }

   function create_okms( $given_atts ) {
		$atts = shortcode_atts( array(
			'okg_id' => ''
		), $given_atts, 'create_okms' );
		$allow_info = $this->allow_access( $atts );
		if( 'array' !== gettype( $allow_info ) || !$allow_info['allowed'] ) {
			return $allow_info;
		}
		$okg_id = $allow_info['okg_id'];
		$this->enqueue_okg_js( array( 'here' => 'create_okms') );
		$this->enqueue_parsley_js();
		$opener .= $this->header_info_html( $atts, $okg_id );
		$label = '<h2>' . __('Create an OKM') . '</h2>';
		return $opener . $label . $this->create_okms_html( $atts, $okg_id );

   }

   function edit_okms( $given_atts ) {
		$atts = shortcode_atts( array(
			'okg_id' => ''
		), $given_atts, 'edit_okms' );
		$allow_info = $this->allow_access( $atts );
		if( 'array' !== gettype( $allow_info ) || !$allow_info['allowed'] ) {
			return $allow_info;
		}
		$okg_id = $allow_info['okg_id'];
		$this->enqueue_okg_js( array( 'here' => 'edit_okms') );
		$this->enqueue_parsley_js();
		$opener = '<div class="propel-okg propel-override">';
		$opener .= $this->header_info_html( $atts, $okg_id );
		$label = '<h2>' . __('Edit an OKM') . '</h2>';
		if( !$_GET || !$_GET['okm'] || !preg_match( '/[\d]+/', $_GET['okm'] ) ) {
			return $this->error_div_html( $atts, __('Error: no OKM selected' ,'propel' ) );
		}
		$closer = '</div> <!-- end .propel-okg div -->';
		$okm_id = $_GET['okm'];
		if( !$this->valid_child_okm( $okg_id, $okm_id ) ) {
			return $this->error_div_html( $atts, __('Error: You do not have admin rights over okm #','propel') . $okm_id );
		}		
		return $opener . $label . $this->edit_okms_html( $atts, $okg_id, $okm_id ) . $closer;
   }

   /**********************************************
	*      HTML
    **********************************************/

	function child_org_picker_html( $atts, $okg_id ) {
		$okg_id = intval( $okg_id );
		$okg_name = Propel_Settings::get_org_name_from_id_by_api( $okg_id );
		if( false === $okg_name ) {
			return $this->error_div_html($atts, __('No OKG.', 'propel'));
		}
		ob_start();
		?>
			<select id="propel_select_connected_okm" name="propel_select_connected_okm" class="propel-okg-select">
				<?php $child_orgs = Propel_Settings::get_child_orgs_by_api($okg_id); ?>
				<option name=""><?php _e('- Please select an OKM -','propel'); ?></option>
				<?php foreach ( $child_orgs as $org ) { ?>
					<option name="<?php echo $org['id']; ?>"><?php echo $org['name'] . ' (' . $org['id'] . ')'; ?></option>
				<?php } ?>
				<option name="new"><b><?php _e('- or CREATE a new OKM -','propel'); ?></b></option>
			</select>
		<?php
		return ob_get_clean();
	}

	/**
	 * deprecated - shortcode above provides just orders
	 */
	function okg_orders_html( $atts, $okg_id ) {
		?>
		<?php
		echo do_shortcode('[woocommerce_my_account]');
	}

	/**
	 * a gridded list of existing OKMs, with actions that we can take for each.
	 */
	function manage_okms_grid_html( $atts, $okg_id ) {
		$okg_id = intval( $okg_id );
		$okg_name = Propel_Settings::get_org_name_from_id_by_api( $okg_id );
		if( false === $okg_name ) {
			return $this->error_div_html($atts, __('No OKG.', 'propel'));
		}
		$child_orgs = Propel_Settings::get_child_orgs_by_api($okg_id);
		if( is_string($child_orgs) ) {
			return $this->error_div_html($atts, $child_orgs );
		}
		ob_start();
		?>
		<div class="propel-grid">
			<div class="propel-grid-header">
				<div class="propel-grid-name-spot propel-grid-cell">
					<?php echo __('OKM Name', 'propel'); ?>
				</div>
				<div class="propel-grid-date-spot propel-grid-cell">
					<?php echo __('Date Created', 'propel'); ?>
				</div>
				<div class="propel-grid-edit-spot propel-grid-cell">
					<?php echo __('Edit', 'propel'); ?>
				</div>
				<div class="propel-grid-purchase-spot propel-grid-cell">
					<?php echo __('Create Keys', 'propel'); ?>
				</div>
			</div>
			<div class="propel-grid-body">
			<?php 
			usort( $child_orgs, function( $child1, $child2 ) {
				// sort from most recent to oldest
				return strtotime($child2['created_at']) - strtotime($child1['created_at']);
			} );
			foreach ( $child_orgs as $org ) { 
				$datestamp = strtotime($org['created_at']);
			?>
				<div class="propel-grid-row">
					<div 	class="propel-grid-cell propel-grid-name-spot " 
							id="okm-name-<?php echo $org['id']; ?>">
						<span><?php echo $org['name'] . ' (' . $org['id'] . ')'; ?></span>	
					</div>
					<div class="propel-grid-date-spot propel-grid-cell">
						<span><?php echo date('M d, Y',$datestamp); ?></span>
					</div>
					<div class="propel-grid-edit-spot propel-grid-cell">
						<a href="<?php echo site_url('/edit-okms/') . '?okm=' . $org['id']; ?>" class="button"><?php _e('Edit','propel'); ?></a>
					</div>
					<div class="propel-grid-purchase-spot propel-grid-cell">
						<a 	href="<?php echo site_url('/okg/') . '?okm=' . $org['id']; ?>" 
							class="button"><?php _e('Create Keys','propel'); ?></a>
					</div>
				</div>
			<?php } ?>				
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Updates an OKM in an ajaxy way
	 */
	function edit_okms_html( $atts, $okg_id, $okm_id ) {
		$okg_id = intval( $okg_id );
		$okg_name = Propel_Settings::get_org_name_from_id_by_api( $okg_id );
		if( false === $okg_name ) {
			return $this->error_div_html($atts, __('No OKG.', 'propel'));
		}
		$okm_info = Propel_Settings::get_org_details_from_id_by_api($okm_id);
		if( false === $okm_info ) {
			return $this->error_div_html($atts, __('Invalid OKM id.', 'propel'));
		}
		$standard_fields = $this->fields_for_new_okm();
		$formopener .='<form id="propel-edit-okm">';
		$html = '<h3>Editing <span class="propel-okm-header-name">' . $okm_info['name'] .'</span></h3>';
		$html .= '<a id="propel-update-all-button" class="button propel-editing-button" data-active="no">';
		$html .= __('Update','propel');
		$html .= '</a>';
		foreach( $okm_info as $name => $val ) {
			$this_field = $standard_fields[$name];
			if( array_key_exists( $name, $standard_fields ) ) {
				$html .= $this->edit_input_html( $atts, $name, $this_field['label'], $val );
			}		
		}
		$formcloser = '</form>';		
		return $formopener . $html . $formcloser;
	}

	/**
	 * a form to create a brand new OKM
	 */
	function create_okms_html( $atts, $okg_id ) {
		ob_start();
		?>
			<div class="propel-okg propel-override">
			<form class="propel-new-okm propel-override" id="propel-new-okm-form" method="post">
				<section id="propel-okm-basic-info">
				<span id="propel-okm-basic-title" class="propel-title-span">OKM Information</span>
				<br class="propel-form-br" />
				<?php foreach ( $this->fields_for_new_okm() as $id => $info ) {
					echo $this->form_input_html( $atts, $id, $info );
				}
				?>
				</section>
				<section id="propel-okm-address-info">
				<span id="propel-okm-address-title" class="propel-title-span">OKM Address</span>
				<br class="propel-form-br" />
				<?php foreach ( $this->address_fields_for_new_okm() as $id => $info ) {
					echo $this->form_input_html( $atts, $id, $info, 'addresses_attributes[0][', ']' );
				}
				?>
				</section>
				<input type="submit" />
				<span class="propel-spinner"></span>
				<span class="propel-feedback"></span>
			</form>
			</div><!-- end div propel-okg -->
		<?php
		return ob_get_clean();
	}

	/**
	 * DRY-ish form elements
	 */
	function form_input_html( $atts, $id, $info, $prefix='', $suffix='' ) {
		ob_start();
		$identifier = $prefix . $id . $suffix;
		$placeholder = array_key_exists( 'placeholder', $info ) ? $info['placeholder'] : $info['label'];
		$type = array_key_exists( 'type', $info ) ? $info['type'] : 'text';
		if( array_key_exists( 'label', $info ) ) { ?>
			<label for='<?php echo $id; ?>'><?php echo $info['label']; ?></label>
		<?php } 
		// parsley_custom_validation_key
		$parsley_custom = ' ';
		if( array_key_exists( 'parsley_custom_validation_key', $info ) ) { 
			$parsley_custom .= $info['parsley_custom_validation_key'];
			$parsley_custom .= '="' . $info['parsley_custom_validation_val'] . '"';
		} ?>

		<input 	type='<?php echo $type; ?>'
				id='<?php echo $identifier; ?>'
				name='<?php echo $identifier; ?>' 
				data-parsley-required='<?php echo $info['required']; ?>'
				<?php echo $parsley_custom; ?>
				placeholder='<?php echo $placeholder; ?>'
				<?php if( array_key_exists( 'value', $info ) ){ ?>
					value='<?php echo $info['value']; ?>'
				<?php } ?>
		/><br class="propel-form-br" />
		<?php
		return ob_get_clean();
	}

	/**
	 * DRY-ish edit elements
	 */
	function edit_input_html( $atts, $slug, $name, $value ) {
		ob_start();
		?>
		<label 	class="propel-label"
				for='<?php echo $slug; ?>'><?php echo $name; ?></label>
		<input 	class="propel-editing-input"
		 		type='text'
				id='<?php echo $slug; ?>'
				name='<?php echo $slug; ?>' 
				placeholder='<?php echo $value; ?>'
				value='<?php echo $value; ?>'
		/>
		<!-- a 	class="button propel-editing-button" 
			data-for="<?php // echo $slug; ?>"><?php // _e('Update','propel'); ?></a -->
		<br class="propel-form-br" />
		<?php
		return ob_get_clean();
	}

	function header_info_html( $atts, $okg_id ) {
		$okg_name = Propel_Settings::get_org_name_from_id_by_api( $okg_id );
		ob_start();
		?>
		<h1 class="propel-org-name-header"><?php echo $okg_name; ?></h1>
		<?php
		return ob_get_clean();
	}

	function error_div_html( $atts, $mssg, $extra_classes='' ) {
		ob_start();
		?>
			<div class="propel-error <?php echo $extra_classes; ?>">
				<?php echo $mssg;?>
			</div>
		<?php
		return ob_get_clean();
	}

   /**********************************************
	*      HELPERS
    **********************************************/

   	function enqueue_okg_js( $where = null ) { // already registered in settings
   		if( null === $where ) {
   			$where = array();
   		}
   		$where['ajaxurl'] = site_url('/wp-admin/admin-ajax.php');
   		$where['manage_okms_url'] = site_url('/manage-okms/');
		wp_localize_script( 'propel_okg_js', 'scitent_frontend', $where );
		wp_enqueue_script( 'propel_okg_js' );
   	}

   	function enqueue_parsley_js() {
   		wp_enqueue_script( 'parsley_js' );
   	}

	/** fields_for_new_okm()
	 * 
	  # POST
	  # Body x-www-form-urlencoded {
	  #   name: Organization Name,
	  #   contact_first_name: Admin First,
	  #   contact_last_name: Admin Last,
	  #   contact_email: admin@organizationname.com,
	  #   contact_phone: 1-555-555-5555,
	  #   custom_registration_portal: http://google.com,
	  #   background: http://www.comohotels.com/metropolitanbangkok/sites/default/files/styles/background_image/public/images/background/metbkk_bkg_nahm_restaurant.jpg,
	  #   addresses_attributes[0][category]: Company
	  #   addresses_attributes[0][address1]: 123 Green Lane
	  #   addresses_attributes[0][address2]: Apt #4
	  #   addresses_attributes[0][city]: Charlottesville
	  #   addresses_attributes[0][state]: Virginia
	  #   addresses_attributes[0][country]: United States
	  #   addresses_attributes[0][zipcode]: 22902
	  #   addresses_attributes[0][phone]: 1-555-555-5555
	  #   tenant_secret_key: *****************
	  # }	 
	 */
	public static function fields_for_new_okm() {
		return array(
'name'                 => array('label' => 'Organization Name', 'required' => 'true', 
								'parsley_custom_validation_key' => 'data-parsley-unused-name',
								'parsley_custom_validation_val' => ''),
'contact_first_name'   => array('label' => 'Contact first name', 'required' => 'true'),
'contact_last_name'    => array('label' => 'Contact last name', 'required' => 'true'),
'contact_email'        => array('label' => 'Contact email', 'required' => 'true'),
'contact_phone'        => array('label' => 'Contact phone', 'required' => 'true'),
		);
	}

	public static function address_fields_for_new_okm() {
		return array(
'category' => array('type' => 'hidden', 'value' => 'Company', 'required' => 'false', ),
'address1' => array('label' => '', 'required' => 'true', 'placeholder' =>'Street Address'),
'address2' => array('label' => '', 'required' => 'false', 'placeholder' => 'Apartment, suite, unit, etc. (optional)'),
'city'     => array('label' => 'City', 'required' => 'true'),
'state'    => array('label' => 'State', 'required' => 'true'),
'country'  => array('label' => 'Country', 'required' => 'true'),
'zipcode'  => array('label' => 'Zipcode', 'required' => 'true'),
'phone'    => array('label' => 'Phone', 'required' => 'true'),

		);
	}

   /**********************************************
	*      ERROR HANDLING
    **********************************************/

	private function allow_access( $atts ) {
		if( !current_user_can('administer_okg') ) {
			$message = __('Error: You are not logged in as an OKG admin. ', 'propel');
			if( !is_user_logged_in() ) {
				$message .= '<a href="' . site_url('/login/') . '">' . __( 'Log in now. &gt;', 'propel' ) . '</a>';
			}
			return $this->error_div_html( $atts, $message );
		}
		$okg_id_user = Propel_OKG::current_okg_id();

		// Try logged-in user's OKG first, shortcode attribute next:
		$okg_id = ( '' === $atts['okg_id'] ) ?  $okg_id_user : $atts['okg_id'];

		// If neither works, error out:
		if( '' === $okg_id || 0 === $okg_id || false === $okg_id ) {
			return $this->error_div_html( $atts, __('Error: You do not have an OKG connected to your account.', 'propel') );
		}
		return array(
			'allowed' => true,
			'okg_id'  => $okg_id
		);	
	}

	private function valid_child_okm( $okg_id, $okm_id ) {
		$child_orgs = Propel_Settings::get_child_orgs_by_api($okg_id);
		foreach( $child_orgs as $child_org ) {
			if( intval($okm_id) === $child_org['id'] ) {
				return true;
			}
		}
		return false;
	}
}

new propel_okg_shortcodes();
