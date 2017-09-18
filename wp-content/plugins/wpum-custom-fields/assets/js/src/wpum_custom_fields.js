/**
 * WP User Manager
 * http://wp-user-manager.com
 *
 * Copyright (c) 2015 Alessandro Tesoro
 * Licensed under the GPLv2+ license.
 */

jQuery(document).ready(function($) {

	/**
	 * Custom fields addon backend scripts.
	 */
	var WPUMCF_ADMIN = {

		init : function() {
			this.optionsComposer();
			this.extensionsSelector();
		},

		/**
		 * Handles creation of options for fields such as select, multiselect and checkboxes.
		 *
		 * @return void
		 */
		optionsComposer : function() {

			// Hide/show options value field.
			jQuery('#show_values').change(function(){
				if( this.checked ) {
					jQuery( '.repeater-table' ).removeClass('hide-values');
				} else {
					jQuery( '.repeater-table' ).addClass('hide-values');
				}
			});

			// Keep one single option as default selected.
			jQuery(document).on('click','.set_as_default', function(){

				if( jQuery( '.set_as_default' ).hasClass( 'allow-multiple' ) ) {
					return;
				}

				jQuery('.set_as_default').attr('checked', false);
				jQuery(this).attr('checked', true);
			});

			// Duplicate option title as option value.
			//

			jQuery(document).on('change','.repeater-element:nth-child(3) input[type=text]', function(){
				var current_value = jQuery( this ).val();
				var option_value = jQuery( this ).parent().next( '.repeater-element' ).find('input').attr('name');
				jQuery('[name="'+option_value+'"]').val( current_value );
			});

			// Options repeater and sortable.
			var options_repeater = jQuery('.wpumcf-field-repeater-options');
			var options_sortable = jQuery('.repeater-table');

			options_sortable.sortable({
				axis: "y",
				cursor: 'pointer',
				opacity: 0.5,
				placeholder: "row-dragging",
				delay: 150,
				handle: ".sort-option",
				start: function(e, ui){
        	ui.placeholder.height(ui.item.height());
    		}

			});

			options_repeater.repeater({
					show: function () {
							jQuery(this).slideDown();

							jQuery('.repeater-wrapper').animate({
									scrollTop: jQuery('.repeater-table').height()
							}, 300);
					},
					hide: function ( deleteElement ) {
							if( confirm( wpum_admin_js.confirm ) ) {
									jQuery(this).slideUp( deleteElement );
							}
					},
					ready: function ( setIndexes ) {
              //$dragAndDrop.on( 'drop', setIndexes );
					},
					isFirstItemUndeletable: true
			});

		},

		sortable_table_fix : function( e, tr ) {
			var $originals = tr.children();
		    var $helper = tr.clone();
		    $helper.children().each(function(index){
		      $(this).width($originals.eq(index).width())
		    });
		    return $helper;
		},

		/**
		 * Handles the file extension selector into the fields creation panel.
		 * @return void
		 */
		extensionsSelector : function() {

			jQuery('#wpum-extensions-wrap input[type=text]').selectize({
				delimiter: ',',
		    persist: false,
		    create: function(input) {
		        return {
		            value: input,
		            text: input
		        }
		    }
			});

		}

	};

	WPUMCF_ADMIN.init();

});
