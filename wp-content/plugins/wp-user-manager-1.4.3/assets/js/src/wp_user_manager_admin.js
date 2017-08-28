/**
 * WP User Manager
 * http://wp-user-manager.com
 *
 * Copyright (c) 2015 Alessandro Tesoro
 * Licensed under the GPLv2+ license.
 */

jQuery(document).ready(function ($) {

	/**
	 * Frontend Scripts
	 */
	var WPUM_Admin = {

		init : function() {
			this.general();
			this.restore_emails();
			this.drag_and_drop_fields_table();
			this.confirm_dialog();
			this.admin_upload();
			this.menu_controller();
		},

		// General Functions
		general : function() {

			if ( $.isFunction($.fn.select2) ) {

				jQuery("select.select2").select2({
					width: 'resolve'
				});

				jQuery(".wppf-multiselect, select.select2_multiselect").select2();

			}

		},

		// Ajax Function to restore emails
		restore_emails : function() {

			$('#wpum-restore-emails').on('click', function(e) {

				e.preventDefault();

				if( confirm( wpum_admin_js.confirm ) ) {

					var wpum_nonce = $('#wpum_backend_security').val();

					$.ajax({
						type: 'GET',
						dataType: 'json',
						url: wpum_admin_js.ajax,
						data: {
							'action' : 'wpum_restore_emails', // Calls the ajax action
							'wpum_backend_security' : wpum_nonce
						},
						beforeSend: function() {
							$( '.wpum-spinner' ).remove();
							$( '.wpum-ajax-done-message' ).remove();
							$( '#wpum-restore-emails' ).after('<span id="wpum-spinner" class="spinner wpum-spinner is-active"></span>');
						},
						success: function(results) {
							$( '#wpum-restore-emails' ).after( '<p class="wpum-ajax-done-message"> <span class="dashicons dashicons-yes"></span> ' + results.data.message + '</p>' );
							$( '.wpum-spinner' ).remove();
						},
						error: function(xhr, status, error) {
								alert(xhr.responseText);
						}

					});

					} else {

							return false;

					}

			});

		},

		// Re-order the fields into the admin panel
		drag_and_drop_fields_table : function() {

			if ( $.isFunction($.fn.sortable) ) {

				$(".users_page_wpum-profile-fields tbody").sortable({
					helper: this.sortable_table_fix,
					axis: "y",
					cursor: 'pointer',
					opacity: 0.5,
					placeholder: "row-dragging",
					delay: 150,
					handle: ".column-order, .move-field",
					update: function(event, ui) {

						// Update TR data
						$(this).children('tr').each(function() {
							$(this).data('priority',$(this).index());
						});

						// Prepare field data
						dataArray = $.map($(this).children('tr'), function(el){
							return { 'priority':$(el).data('priority'), 'field_id':$(el).data('field-id') };
						});

						// Get nonce
						var wpum_editor_nonce = $('#wpum_fields_editor_nonce').val();

						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: wpum_admin_js.ajax,
							data: {
								'action' : 'wpum_update_fields_order', // Calls the ajax action
								'items' : dataArray,
								'wpum_editor_nonce': wpum_editor_nonce
							},
							beforeSend: function() {
								WPUM_Admin.display_loader();
								WPUM_Admin.remove_message();
							},
							success: function(results) {
								// Update odd even table classes
								$('.users_page_wpum-profile-fields').find("tr").removeClass('alternate');
								$('.users_page_wpum-profile-fields').find("tr:even").addClass('alternate');
								// Hide loading indicator
								WPUM_Admin.hide_loader();
								// Show message
								WPUM_Admin.display_success_message( '.wpum-page-title', results.data.message );
							},
							error: function(xhr, status, error) {
									alert(xhr.responseText);
							}
						});

					}

				}).disableSelection();
			}

		},

		// Adjust table width when dragging
		sortable_table_fix : function( e, tr ) {
			var $originals = tr.children();
				var $helper = tr.clone();
				$helper.children().each(function(index){
					$(this).width($originals.eq(index).width())
				});
				return $helper;
		},

		// Display spinner
		display_loader : function() {

			// Set height of loader indicator the same as the
			// editor table.
			var table_height = $( '.wp-list-table' ).height();
			$('.wpum-table-loader').css('display','table');
			$('.wpum-table-loader').css('height', table_height );
			$('.wpum-table-loader #wpum-spinner').addClass('is-active');

		},
		// Hide the spinner
		hide_loader : function() {
			$('.wpum-table-loader').hide();
			$('.wpum-table-loader #wpum-spinner').removeClass('is-active');
		},
		// Display a success message
		display_success_message : function( after, message, status, scroll ) {
			status = status || "updated";
			scroll = scroll || true;

			$( after ).after( '<div class="wpum-message '+ status +' notice is-dismissible"><p>' + message + '</p></div>' );

			if( scroll ) {
				// scroll back
				$("html, body").animate({ scrollTop: 0 }, "slow");
					return false;
			}

		},

		remove_message : function() {
			$( '.wpum-message' ).remove();
		},

		// Ask to confirm before deleting field group
		confirm_dialog : function() {

			$('#wpum-group-settings-edit a.submitdelete, .wpum-confirm-dialog').click(function(e){
					return confirm( wpum_admin_js.confirm );
			})

		},

		// Handles files upload in admin panel
		admin_upload : function() {

			// Uploading files
			var file_frame;
			window.formfield = '';

			$( document.body ).on('click', '.wpum_settings_upload_button', function(e) {

					e.preventDefault();

					var button = $(this);

					window.formfield = $(this).parent().prev();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						title: wpum_admin_js.upload_title,
						button: {
							text: wpum_admin_js.use_this_file,
						},
						multiple: false  // Set to true to allow multiple files to be selected
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();

						// Send file url to text field
						window.formfield.val( attachment.url );
					});

					// Finally, open the modal
					file_frame.open();

			});

		},

		// Handles menu controller functionalities.
		menu_controller : function() {

			jQuery('select.wpum-menu-visibility-setter option:selected').each(function() {

				var selected_status = jQuery(this).val();
				var locate_role = jQuery(this).parent().parent().next();

				if( selected_status == 'in' || selected_status == 'out' ) {
					jQuery( locate_role ).show();
					jQuery( locate_role ).find('select').select2();
				}

			});

			jQuery('#menu-to-edit').on('change', 'select.wpum-menu-visibility-setter', function() {

				var field          = jQuery(this);
				var id             = field.parent().prev().val();
				var roles_selector = jQuery( '#wpum-wpum_nav_menu_status_roles' + id + '-wrap' );

				jQuery( '#wpum-wpum_nav_menu_status_roles' + id + '-wrap select' ).select2({
					width: 'resolve'
				});

				if( jQuery( this ).val() === 'in' ){

					jQuery( roles_selector ).show().css( 'display', 'block' );

				} else {

					jQuery( roles_selector ).hide();

				}

			});

		}

	};

	WPUM_Admin.init();

	// Load dashboard widget via ajax
	if( jQuery( '#wpum_dashboard_users' ).length ) {
		$.ajax({
			type: "GET",
			data: {
				action: 'wpum_load_dashboard_users_overview'
			},
			url: wpum_admin_js.ajax,
			success: function ( response ) {
				$('#wpum_dashboard_users .inside').html( response );
			}
		});
	}

});
