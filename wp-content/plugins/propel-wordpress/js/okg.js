/** Propel okg.js
 * utilities for the OKG Admin role
 * heavily reused, recycled, refactored from user.js
 */
'use strict';
if(!scitent) { var scitent = {}; }
if(!scitent.utils) { scitent.utils = {}; }

/**************** 
 * settings panel - interactive form
 ****************/

/*********************************
 * Things to run after dom loads
 */
jQuery( document ).ready( function() {
	if( 'undefined' !== typeof scitent_backend ) { // see propel-settings.php::register_okg_js()
		scitent.okg_backend_helper
		.init_org_dropdown()
		.init_new_org_details()
		.init_connect_selected_org()
		.toggle_org_btn(); // for re-login or back button with item selected
	}
	if( 'undefined' !== typeof scitent_frontend ) { // see shortcodes/okg-shortcodes.php::enqueue_okg_js()
		switch (scitent_frontend['here']) {
			case 'okg_child_org_picker':
				scitent.okg_frontend_picker_helper
				.init_okm_picker()
				.init_buy_btn()
				.init_buy_btn_toggler()
				.init_add_more()
				.detect_queryparam_okg();
				// .init_propelify_forms();
				break;
			case 'okg_orders':
				scitent.okg_frontend_order_helper
				.init_orders();
				break;
			case 'create_okms':
				scitent.okg_frontend_newokm_helper
				.init_newokms()
				.init_newokm_parsley()
				.init_check_name_exists();
				break;
			case 'manage_okms':
				scitent.okg_frontend_manageokms_helper
				.init_manageokms();
				break;
			case 'edit_okms':
				scitent.okg_frontend_editokm_helper
				.init_editokm();
				break;
			default:
				console.log('scitent_frontend variable not initialized properly.');
				break;
		}
	}
});

/*********************************
 * Scitent extension
 */
scitent = jQuery.extend({}, scitent, {
	ajaxurl : function() {
		if( 'undefined' !== typeof scitent_frontend && scitent_frontend['ajaxurl'] ){
			return scitent_frontend['ajaxurl'];
		} else {
			return '/wp-admin/admin-ajax.php';
		}
	},
	manage_okms_url: function() {
		if( 'undefined' !== typeof scitent_frontend && scitent_frontend['manage_okms_url'] ){
			return scitent_frontend['manage_okms_url'];
		} else {
			return '/manage-okms/';
		}
	},
	get_id_from_queryparam: function( identifier ) {
		identifier = identifier + '=';
		var where = location.href.indexOf( identifier );
		if( -1 !== where ) {
			var querysuffix = location.href.slice(where + identifier.length);
			return querysuffix.split(/[^\d]/)[0]; // only numeric
		} else {
			return -1;
		}
	},
	okg_backend_helper: {
		init_org_dropdown: function() {
			var that = this;
			jQuery( '#propel_okg_for_org' ).on( 'change', function() {
				if ( jQuery( this ).val() !== '0' ) {
				  jQuery( 'tr.new_org' ).hide();
				} else {
				  jQuery( 'tr.new_org' ).show();
				}
				if ( jQuery( this ).val() !== '' ) {
				  jQuery( 'tr.propel_organization' ).removeClass( 'form-invalid' );
				}
				that.toggle_org_btn();
			} );
			return this; // chainable!
		},
		init_new_org_details: function() {
			var that = this;
			jQuery( '#propel_create_org' ).on( 'click', function() {
				if ( that.createFieldsAreValid() ) {
					that.createOrg( jQuery( '#propel_org_first' ).val(), jQuery( '#propel_org_last' ).val(), jQuery( '#propel_new_org' ).val() );
				} else {
					var msg = '<h4>Please fill in the required fields:</h4>';
					if ( jQuery( '#propel_new_org' ).val() === '' ) {
						jQuery( 'tr.new_org' ).addClass( 'form-invalid' );
						msg += '<li>Organization Name required</li>';
						jQuery( '#propel_new_org' ).focus();
					}
					if ( jQuery( '#propel_org_last' ).val() === '' ) {
						jQuery( '#propel_org_last' ).parent().parent().addClass( 'form-invalid' );
						msg += '<li>Last Name required</li>';
						jQuery( '#propel_org_last' ).focus();
					}
					if ( jQuery( '#propel_org_first' ).val() === '' ) {
						jQuery( '#propel_org_first' ).parent().parent().addClass( 'form-invalid' );
						msg += '<li>First Name required</li>';
						jQuery( '#propel_org_first' ).focus();
					}
					jQuery( 'tr.new_org .message' )
					.html( msg )
					.addClass( 'error' );
				}
			});
			return this; // chainable!
		},
		init_connect_selected_org: function() {
			var that = this;
			jQuery('input.selected_propel_org').on( 'click', function(){
				var child_id = jQuery( '#propel_okg_for_org' ).val();;
				var parent_id = jQuery('#parent_org_id').val();
				that.connectChildOrg( child_id, parent_id );
			});
			return this; // chainable!			
		},
		toggle_org_btn: function() {
			var chosenval = jQuery( '#propel_okg_for_org' ).val();
			var chosenname = jQuery( '#propel_okg_for_org option:selected' ).text();
			var prefix = jQuery( 'input.selected_propel_org' ).attr("data-val");
			if( '' === chosenval || '0' === chosenval ) {
				jQuery('label[for="propel_organization"]').show();
				jQuery('input.selected_propel_org')
					.hide()
					.val('Cannot generate keys here until an organization is chosen.');
			} else {
				jQuery('label[for="propel_organization"]').hide();
				jQuery('input.selected_propel_org')
					.show()
					.val(prefix+' '+chosenname);
			}
			return this; // chainable!			
		},
		createFieldsAreValid: function() {
			return jQuery( '#propel_org_first' ).val()     !== '' &&
			       jQuery( '#propel_org_last' ).val()      !== '' &&
			       jQuery( '#propel_new_org' ).val()       !== '';
		},
		createOrg: function( firstName, lastName, orgName ) {
		// POST to OKM to create org
		// On successful response, append new org
			var that = this;
			var org_data = {
				  'action'   : 'create_organization',
				  'contact_first_name' : firstName,
				  'contact_last_name' : lastName,
				  'name' : orgName
				};
			// console.log(org_data);
			// return 'without actually doing anything'; // DEV / DEBUG: prevent awkward ajax calls

			jQuery.post(
				scitent.ajaxurl(),
				org_data,
				that.successfullyCreatedOrg
			);
		},
		successfullyCreatedOrg: function( response ) {
		  var orgID = response.data.organization.id;
		  var orgName = response.data.organization.name;

		  jQuery( '#propel_okg_for_org' )
		    .append( 
		      jQuery( '<option></option>' ).attr( 'value', orgID ).text( orgName )
		    )
		    .val( orgID );

		  scitent.okg_backend_helper.toggle_org_btn();

		  jQuery( 'tr.new_org' ).hide();
		  jQuery( 'tr.propel_organization' )
		      .removeClass( 'form-invalid' )
		      .find( 'td' )
		      .append( '<span class="success"><span class="dashicons dashicons-yes"></span> Organization "' + orgName + '" added!</span>' );
		  return true;
		},
		connectChildOrg( child_id, parent_id ) {
			var that = this;
			var org_data = {
				  'action' : 'add_org_child',
				  'parent_id' : parent_id,
				  'child_id'  : child_id
			};
			jQuery.post(
				scitent.ajaxurl(),
				org_data,
				that.successfullyConnectedChildOrg
			);
		},
		successfullyConnectedChildOrg: function( response ) {
			if( true === response.success ) {
				alert('successfully connected '+response.data.child.name+' to '+response.data.parent.name);
			} else {
				alert('Action failed to connect organization.  Please see the developer console for details (or contact your administrator).');
				console.log( response );
				return false;
			}
			var kid = response.data.child.id;
			jQuery('ul#current_child_orgs').append('<li id="child_org_'+kid+'">'+response.data.child.name+' ('+kid+')</li>');
			jQuery('#propel_okg_for_org option[value="'+response.data.child.id+'"]').remove();
			scitent.okg_backend_helper.toggle_org_btn();
		}

	}, // END okg_backend_helper object
	okg_frontend_picker_helper: {
		init_okm_picker: function() {
			var that = this;
			jQuery('#propel_select_connected_okm').on('change.okm_chosen',function(){
				var selected_option = jQuery('#propel_select_connected_okm option:selected').attr('name');
				if ( selected_option !== '' && selected_option !== 'new' ) {
					that.show_table_with_okm_selected(selected_option);
				} else {
					jQuery('.wcplprotable_wrap, .propel-globalcartbtn').hide();
					jQuery('.propel-for-okm').val('0');
				}
				if( 'new' === selected_option ) {
					jQuery('#redir_notice').show();
					that.redirdot_dance();
					window.location = '/create-okms';
				}
			});
			return this; // chainable!
		},
		show_table_with_okm_selected( okm ) {
			var that = this;
			if( jQuery('#hide_wcplpro').length ) {
				jQuery('#hide_wcplpro').remove();
			}
			jQuery('.wcplprotable_wrap, .propel-globalcartbtn').show();
			jQuery('.propel-for-okm').val(okm);
			return this; // chainable
		},
		// init_propelify_forms: function() {  // prevent wcplpro ajax form submit, init propel
		// 	var that = this;
		// 	jQuery('form.vtajaxform').removeClass('vtajaxform').addClass('propelAjaxForm');
		// 	jQuery(document).on("submit", "form.propelAjaxForm", function(event) {
		// 		event.preventDefault();
		// 		// alert('submitted for propel!');
		// 		that.ajax_submit( event );
		// 	});
		// 	return this; // chainable!			
		// },
		redirdot_dance: function() {
			var that = this;
			setInterval(function(){ 
				that.redirdot_switch(); 
			}, 300);
		},
		redirdot_switch: function() {
			var dots = jQuery('.redirdot');
			var hiddendot = dots.filter(function(index){
				return jQuery(this).css('visibility') === 'hidden';
			});
			if( !hiddendot.length ) {
				dots.eq(0).css('visibility','hidden');
			} else {
				var idx = dots.index(hiddendot);
				dots.eq((idx+1)%dots.length).css('visibility','hidden');
				hiddendot.css('visibility','visible');
			}
		},
		init_buy_btn: function() {
			var that = this;
			jQuery('#gc__bottom')
				.detach()
				.appendTo('.wcplprotable_wrap');
			jQuery(document).on('click', '.propel-globalcartbtn', function(event){
				event.preventDefault();
				if( that.sum_selected_items() > 0 ){
					that.ajax_submit( event );
				} else {
					return false;
				}
			});
			return this; // chainable
		},
		init_buy_btn_toggler: function() {
			var that = this;
			jQuery(document).on('input click change','.qtywrap',function(){
				if( that.sum_selected_items() < 1 ){
					jQuery('.propel-globalcartbtn').attr('data-active','no');
				} else {
					jQuery('.propel-globalcartbtn').attr('data-active','yes');
				}
			});
			return this; // chainable
		},
		init_add_more: function() {
			var that = this;
			jQuery('#propel-add-more').on('click',function(e){
				// console.log('do the thing for: ' + jQuery(e.target) );
				that.show_table_with_okm_selected( e.target.dataset.okm );
			});
			return this; // chainable
		},
		detect_queryparam_okg: function() {
			var okm = scitent.get_id_from_queryparam('okm');
			if( -1 !== okm ) {
				jQuery('#propel_select_connected_okm option[name="'+okm+'"]').attr('selected',true);
				jQuery('#propel_select_connected_okm').trigger('change.okm_chosen');
			}
		},
		ajax_submit: function( event ) {
			var that = this,
				data = {},
			    $form = jQuery('form.vtajaxform'),
				product_id = 'input[name="product_id"]', // $form.find('input[name="product_id"]').val(),
				quantity = '.hidden_quantity', // $form.find('.hidden_quantity').val(),
				for_okm = 0;

			if( jQuery('#propel_select_connected_okm').length ) {
				for_okm = jQuery('#propel_select_connected_okm option:selected').attr('name');
			} else {
				for_okm = jQuery('#propel-add-more').attr('data-okm');
			}

			data.product_ids = scitent.utils.array_of_inputs($form, product_id);
			data.quantities = scitent.utils.array_of_inputs($form, quantity);
			for(var i=data.quantities.length-1;i>=0;i--){ 
				if(0===parseInt(data.quantities[i])){ 
					data.product_ids.splice(i,1); 
					data.quantities.splice(i,1); 
				} 
			}

			if ( quantity < 1 ) {
				return; // not buyin' nuthin
			}
			jQuery.ajaxQueue({
				type: "POST",
				url: scitent.ajaxurl(),
				data: {
					"action" : "add_product_to_cart",
					"product_id" : JSON.stringify(data.product_ids),
					"quantity" : JSON.stringify(data.quantities),
					"for_okm" : for_okm
				},
				success:function(data){
					window.location.href = ""+wcplprovars.cart_url+"";
				}
			});
		},
		strayify: function( something ) { // wrap in quotes, brackets, quotes
			return JSON.stringify([].concat( something ));
		},
		sum_selected_items: function() {
			var sum = 0;
			jQuery.each( jQuery('input[name="wcplpro_quantity"]'), function( index, value ) {
				sum += parseInt(jQuery(value).val());
			});
			return sum;
		}
	}, // END okg_frontend_picker_helper object
	okg_frontend_order_helper: {
		init_orders: function() {
			return this; // chainable!
		}
	}, // okg_frontend_order_helper
	okg_frontend_newokm_helper: {
		init_newokms: function() {
			var that = this;
			console.log('ready to make a new okm.');
			jQuery(document).on('submit','form.propel-new-okm', function(event) {
				event.preventDefault();
				jQuery('span.propel-spinner').html(' Submitting ...'); // todo: spinner
				var info = jQuery(event.target).serializeArray();
				that.ajax_submit( event, info );
			});
			return this; // chainable!
		},
		init_newokm_parsley: function() {
			var options = { 
				// optional options
			};
			jQuery('#propel-new-okm-form').parsley(options);
			return this; // chainable
		},
		init_check_name_exists: function(e) {
			var that = this;
			window.Parsley
				.addValidator('unusedName', {
					requirementType: 'string',
					validateString: that.ajax_check_name_exists,
					messages: {
						en: 'There is already an organization with that name',
					}
				});

			return this; // chainable
		},
		ajax_check_name_exists: function(name, e) {
			var that = this;
			this.name = name;
			return jQuery.ajax({
				type: "POST",
				url: scitent.ajaxurl(),
				data: {
					"action" : "does_org_exist",
					"name" : scitent.utils.html_entities(that.name)
				}
			}).then( scitent.okg_frontend_newokm_helper.callback_name_exists );
		},
		callback_name_exists: function( response ) {
			var dfd = jQuery.Deferred();
			var name_does_exist = ( !!response && !!response.data && response.data.exists );
			if( name_does_exist ) {
				console.log('That already exists!');
				return dfd.reject();
			} else {
				console.log('That is brand new!');
				return dfd.resolve();
			}
		},
		ajax_submit: function( event, info ) {
			var that = this;
		    var data = this.serialized_a_to_o( info );
			data['action'] = 'propel_newokm';
			data.name = scitent.utils.html_entities(data.name);
			jQuery.ajax({
				url: scitent.ajaxurl(),
				type: 'POST',
				data: data,
				success: that.successfully_newokmd
			});
		},
		serialized_a_to_o: function( a ) { // array to object: http://stackoverflow.com/questions/1184624/
			var o = {};
			jQuery.each(a, function() {
			    if (o[this.name] !== undefined) {
			        if (!o[this.name].push) {
			            o[this.name] = [o[this.name]];
			        }
			        o[this.name].push(this.value || '');
			    } else {
			        o[this.name] = this.value || '';
			    }
			});
			return o;
		},
		successfully_newokmd: function( data ) {
			var that = this;
			if( !data || !data.data || !data.data.child ) {
				scitent.okg_frontend_newokm_helper.actually_failed_to_newokm( data );
				return false;
			}
			location.href = scitent.manage_okms_url();
			jQuery('span.propel-spinner').html(''); // todo: spinner
			var message = 'Successfully created '+data.data.child.name+'! ';
			// message += 'You will now be redirected to the <a href="' + scitent.manage_okms_url() + '">';
			// message += 'OKM Management</a> page.';
			jQuery('span.propel-feedback').html(message);
			jQuery('input[type="text"]').val('');
			console.log(data);
			
		},
		actually_failed_to_newokm: function( data ) {
			jQuery('span.propel-feedback').html('An error occurred. Please check your okm details or the console.');
			console.log(data);
			return false;
		}
	}, // okg_frontend_newokm_helper
	okg_frontend_manageokms_helper: {
		init_manageokms: function() {
			console.log('ready to manage okms.');
			return this; // chainable
		}
	}, // okg_frontend_manageokms_helper
	okg_frontend_editokm_helper: {
		init_editokm: function() {
			var that = this;
			console.log('ready to edit an okm.');
			jQuery('#name').on('input',function(e){
				jQuery('.propel-okm-header-name').html(jQuery(this).val());
			});
			jQuery('.propel-editing-input').on('keyup keypress blur change',function(e){
				scitent.okg_frontend_editokm_helper.sync_button_state_to_placeholders(e);
			});
			jQuery(document).on('click','.propel-editing-button[data-active="yes"]',function(e){
				scitent.okg_frontend_editokm_helper.edit_by_ajax(e);
			});
			return this; // chainable
		},
		sync_button_state_to_placeholders: function(e) {
			var that = this;
			if(that.values_are_changed_from_placeholders()) {
				jQuery('.propel-editing-button').attr('data-active','yes');
			} else {
				jQuery('.propel-editing-button').attr('data-active','no');
			}
		},
		values_are_changed_from_placeholders: function( callback = null ) {
			for(var inp in jQuery('.propel-editing-input') ) {
				var input = jQuery('.propel-editing-input').eq(inp);
				callback && callback( input );
				if( input.length && input.val() !== input.attr('placeholder') ) {
					return true;
				}
			}
			return false;
		},
		sync_placeholders_to_values: function(e) {
			scitent.okg_frontend_editokm_helper.values_are_changed_from_placeholders( function(input){
				input.length && input.attr('placeholder',input.val());	
			});
		},
		edit_by_ajax: function(e) {
			e.preventDefault();
			var that = this;
			// var its_for = jQuery(e.target).data('for'); // field identifier
			// var new_val = jQuery('#'+its_for).val();
			var okm = scitent.get_id_from_queryparam('okm');
			var form_data = {};
			var $form = jQuery('form#propel-edit-okm');
			// data[its_for] = new_val;
			form_data['action'] = 'propel_editokm';
			form_data['id'] = okm;
			form_data = jQuery.extend(form_data, scitent.utils.assoc_array_of_inputs($form, '.propel-editing-input'));
			form_data.name = scitent.utils.html_entities(form_data.name);
			jQuery.post(
				scitent.ajaxurl(),
				form_data,
				scitent.okg_frontend_editokm_helper.successfullyEditedChildOrg
			);
		},
		successfullyEditedChildOrg: function() {
			console.log('successfullyEditedChildOrg');
			scitent.okg_frontend_editokm_helper.sync_placeholders_to_values();
			scitent.okg_frontend_editokm_helper.sync_button_state_to_placeholders();
		}
	}
});

/*********************************
 * Scitent utilities
 */
 scitent.utils.array_of_inputs = function( $form, selector ) {
			return jQuery.map( $form.find(selector), function(inp) {
				return jQuery(inp).val();
			});
		};

scitent.utils.assoc_array_of_inputs = function( $form, selector ) {
			var that = this;
			this.assoc = [];
			$form.find(selector).each(function(idx,inp){
				that.assoc[jQuery(inp).attr('name')] = jQuery(inp).val();
			});
			return this.assoc;
		}; 

scitent.utils.html_entities = function( str ) {
			return str
				.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
}