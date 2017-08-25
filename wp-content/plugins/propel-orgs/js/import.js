jQuery( document ).ready( function() {

	jQuery( '#import-orgs' ).on( 'click', function ( e ) {
		e.preventDefault();

		jQuery( '.spinner' ).show();

		jQuery.post(
			'/wp-admin/admin-ajax.php',
			{
				'action' : 'import_propel_orgs'
			},
			function ( response ) {
				console.log( response );
				jQuery( '.spinner' ).hide();
				jQuery( '.message' ).html( 'Imported ' + response.data[1] + ' orgs. Did not import ' + response.data[2] + ' duplicates.' );
			}
		);

	} );

} );