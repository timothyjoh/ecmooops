jQuery(function($) {


	// Show or hide conditions section
	$('body').on('change', '.menu-item-if-menu-enable', function() {
		$( this ).closest( '.if-menu-enable' ).next().toggle( $( this ).prop( 'checked' ) );

		if ( ! $( this ).prop( 'checked' ) ) {
			var firstCondition = $( this ).closest( '.if-menu-enable' ).next().find('p:first');
			firstCondition.find('.menu-item-if-menu-enable-next').val('false');
			firstCondition.nextAll().remove();
		}
	});


	// Show or hide conditions section for multiple rules
	$('body').on( 'change', '.menu-item-if-menu-enable-next', function() {
		var elCondition = $( this ).closest( '.if-menu-condition' );

		if ($(this).val() === 'false') {
			elCondition.nextAll().remove();
		} else if (!elCondition.next().length) {
			elCondition.clone().appendTo(elCondition.parent()).find('option:selected').removeAttr('selected');
		}
	});


	// Check if menu extra fields are actually displayed
	if ($('#menu-to-edit li').length !== $('#menu-to-edit li .if-menu-enable').length) {
		$('<div class="notice error is-dismissible if-menu-notice"><p>' + IfMenu.conflictErrorMessage + '</p></div>').insertAfter('.wp-header-end');
	}


});
