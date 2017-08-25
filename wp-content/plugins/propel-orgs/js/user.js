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
