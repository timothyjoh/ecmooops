/**
 * WP User Manager
 * http://wp-user-manager.com
 *
 * Copyright (c) 2015 Alessandro Tesoro
 * Licensed under the GPLv2+ license.
 */

jQuery(document).ready(function ($) {

	/**
	 *  Get current page url
	 */
	var wpum_location = $( location );

	/**
	 * Frontend Scripts
	 */
	var WPUM_Frontend = {

		init : function() {
			this.ajax_remove_file();
			this.directory_sort();
			this.show_password();
			this.datepicker();
		},

		// Check password strenght function
		checkPasswordStrength : function( $pass1, $strengthResult, $submitButton, blacklistArray ) {

				var pass1 = $pass1.val();

				// Reset the form & meter
				$strengthResult.removeClass( 'short bad good strong' );

				// Extend our blacklist array with those from the inputs & site data
				blacklistArray = blacklistArray.concat( wp.passwordStrength.userInputBlacklist() )

				// Get the password strength
				var strength = wp.passwordStrength.meter( pass1, blacklistArray );

				// Add the strength meter results
				switch ( strength ) {

						case 2:
								$strengthResult.addClass( 'bad' ).html( pwsL10n.bad );
								break;

						case 3:
								$strengthResult.addClass( 'good' ).html( pwsL10n.good );
								break;

						case 4:
								$strengthResult.addClass( 'strong' ).html( pwsL10n.strong );
								break;

						case 5:
								$strengthResult.addClass( 'short' ).html( pwsL10n.mismatch );
								break;

						default:
								$strengthResult.addClass( 'short' ).html( pwsL10n.short );

				}

				return strength;

		},

		// Process removal of the user avatar
		ajax_remove_file : function() {

			$('a.wpum-remove-uploaded-file').on('click', function(e) {

				e.preventDefault();
				var wpum_removal_button = this; // form element
				var wpum_removal_nonce  = $( '.wpum-profile-form' ).find('#_wpnonce').val();
				var wpum_field_id = $( wpum_removal_button ).data("remove");
				var wpum_submitted_form = $( '.wpum-profile-form' ).find("[name='wpum_submit_form']").val();

				console.log( wpum_submitted_form );

				$.ajax({
					type: 'GET',
					dataType: 'json',
					url: wpum_frontend_js.ajax,
					data: {
						'action' : 'wpum_remove_file', // Calls the ajax action
						'wpum_removal_nonce' : wpum_removal_nonce,
						'field_id' : wpum_field_id,
						'submitted_form' : wpum_submitted_form
					},
					beforeSend: function() {
						$( wpum_removal_button ).find('div.wpum-message').remove();
						$( wpum_removal_button ).before('<div class="wpum-message notice"><p class="the-message">' + wpum_frontend_js.checking_credentials + '</p></div>');
					},
					success: function(results) {

						// Check the response
						if( results.data.valid === true ) {
							$( wpum_removal_button ).prev('div').prev().remove();
							$( '#wpum-form-profile' ).find('div.wpum-message').removeClass('notice').addClass('success').children('p').text(results.data.message);
							location.reload(true);
						} else {
							$( '#wpum-form-profile' ).find('div.wpum-message').removeClass('notice').addClass('error').children('p').text(results.data.message);
						}

					},
					error: function(xhr, status, error) {
							alert(xhr.responseText);
					}
				});


			});

		},

		// User directory sort function
		directory_sort : function() {

			jQuery("#wpum-dropdown, #wpum-amount-dropdown").change(function () {
						location.href = jQuery(this).val();
				});

		},

		// Show password functionality.
		show_password : function() {

			if ( jQuery.isFunction(jQuery.fn.hideShowPassword) ) {
				jQuery('#wpum-show-password').change(function(){
					jQuery('#password').hideShowPassword( jQuery(this).prop('checked') );
				});
			}

		},

		// Run jQuery datepicker on form fields.
		datepicker : function() {

			if( jQuery( 'input[data-dateformat]' ).length > 0 && jQuery.isFunction( jQuery.fn.datepicker ) ) {

				jQuery( "input[data-dateformat]" ).each(function(){

						var dateformat = jQuery(this).data( 'dateformat' );

						jQuery( this ).datepicker({
							dateFormat: dateformat
						});

				});

			}

		}

	};

	WPUM_Frontend.init();

	/**
	 * Remove query arguments from pages to prevent multiple message to appear.
	 */
	window.wpum_removeArguments = function() {
			function removeParam(key, sourceURL) {
					var rtn = sourceURL.split("?")[0],
							param, params_arr = [],
							queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
					if (queryString !== "") {
							params_arr = queryString.split("&");
							for (var i = params_arr.length - 1; i >= 0; i -= 1) {
									param = params_arr[i].split("=")[0];
									if ($.inArray(param, key) > -1) {
											params_arr.splice(i, 1);
									}
							}
							rtn = rtn + "?" + params_arr.join("&");
					}
					return rtn;
			}

			var remove_query_args = ['updated'];

			url = wpum_location.attr('href');
			url = removeParam(remove_query_args, url);

			if (typeof history.replaceState === 'function') {
					history.replaceState({}, '', url);
			}
	};

	 // Run the above script only on plugin's pages
	 if( jQuery( 'body' ).hasClass('wpum-account-page') ) {
		 window.wpum_removeArguments();
	 }

	// Run pwd meter if enabled
	if( wpum_frontend_js.pwd_meter == 1 ) {
		$( 'body' ).on( 'keyup', 'input[name=password]',
					function( event ) {
							WPUM_Frontend.checkPasswordStrength(
									$('.wpum-registration-form-wrapper input[name=password], .wpum-profile-form-wrapper input[name=password], .wpum-update-password-form-wrapper input[name=password], .wpum-password-form input[name=password]'),         // First password field
									$('.wpum-registration-form-wrapper #password-strength, .wpum-profile-form-wrapper #password-strength, .wpum-update-password-form-wrapper #password-strength, .wpum-password-form #password-strength'),           // Strength meter
									$('#submit_wpum_register, #submit_wpum_profile, #submit_wpum_password'),           // Submit button
									['admin', 'administrator', 'test', 'user', 'demo']        // Blacklisted words
							);
					}
			);
	}

});
