jQuery( document ).ready( function() {

	jQuery( '#start-date' ).datepicker();
	jQuery( '#end-date' ).datepicker();

	jQuery( '#toggle-all' ).on( 'change', function(e) {

		jQuery( 'input[name^="column"]' ).prop( 'checked', jQuery( this ).is( ':checked' ) );

	} );

} );