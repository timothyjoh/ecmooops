// var org_id is current user org_admin id
//   as passed in locally from WordPress
//   
//   See Propel_Org_Admin->render_user_fields()

jQuery( document ).ready( function() {

  jQuery( '#propel_organization' ).val( org_id );

  if ( jQuery( '#role' ).val() == 'org_admin' )
      jQuery( 'table.propel' ).show();

  jQuery( '#role' ).on( 'change', function() {
    if ( jQuery( this ).val() != 'org_admin' )
      jQuery( 'table.propel' ).hide();
    else
      jQuery( 'table.propel' ).show();
  } );


  jQuery( '#propel_organization' ).on( 'change', function() {
    if ( jQuery( this ).val() != '0' ) {
      jQuery( 'tr.new_org' ).hide();
    } else
      jQuery( 'tr.new_org' ).show();

    if ( jQuery( this ).val() != '' )
      jQuery( 'tr.propel_organization' ).removeClass( 'form-invalid' );
  } );


  jQuery( '#propel_create_org' ).on( 'click', function() {
    if ( createFieldsAreValid() ) {
      createOrg( jQuery( '#first_name' ).val(), jQuery( '#last_name' ).val(), jQuery( '#propel_new_org' ).val() );
    } else {

      msg = '<h4>Please fill in the required fields:</h4>';

      if ( jQuery( '#propel_new_org' ).val() == '' ) {
        jQuery( 'tr.new_org' ).addClass( 'form-invalid' );
        msg += '<li>Organization Name required</li>';
        jQuery( '#propel_new_org' ).focus();
      }

      if ( jQuery( '#last_name' ).val() == '' ) {
        jQuery( '#last_name' ).parent().parent().addClass( 'form-invalid' );
        msg += '<li>Last Name required</li>';
        jQuery( '#last_name' ).focus();
      }

      if ( jQuery( '#first_name' ).val() == '' ) {
        jQuery( '#first_name' ).parent().parent().addClass( 'form-invalid' );
        msg += '<li>First Name required</li>';
        jQuery( '#first_name' ).focus();
      }

      jQuery( 'tr.new_org .message' )
        .html( msg )
        .addClass( 'error' );

    }
  } );


  jQuery( 'form' ).on( 'submit', function(e) {
    if ( ! isComplete() ) {
      e.preventDefault();
      jQuery( 'tr.propel_organization' ).addClass( 'form-invalid' );
    }

  } );


  jQuery( '#propel_new_org' ).on( 'keypress', function(e) {
    if ( e.which == 13 ) { // Enter
      e.preventDefault();
      jQuery( '#propel_create_org' ).click();
    }
  } );
} );


function createFieldsAreValid() {
  return jQuery( '#first_name' ).val()     != '' &&
         jQuery( '#last_name' ).val()      != '' &&
         jQuery( '#propel_new_org' ).val() != '';
}


function isComplete() {
  if ( jQuery( '#role' ).val() != 'org_admin' )
    return true;
  else
    if ( jQuery( '#propel_organization' ).val() == '' || jQuery( '#propel_organization' ).val() == '0' )
      return false;
    else
      return true;
}


function createOrg( firstName, lastName, orgName ) {
  // POST to OKM to create org
  // On successful response, append new org

  jQuery.post(
    '/wp-admin/admin-ajax.php',
    {
      'action'   : 'create_organization',
      'contact_first_name' : firstName,
      'contact_last_name' : lastName,
      'name' : orgName
    },
    function( response ) {
      orgID = response.data.organization.id;

      jQuery( '#propel_organization' )
        .append( 
          jQuery( '<option></option>' ).attr( 'value', orgID ).text( orgName )
        )
        .val( orgID );

      jQuery( 'tr.new_org' ).hide();
      jQuery( 'tr.propel_organization' )
          .removeClass( 'form-invalid' )
          .find( 'td' )
          .append( '<span class="success"><span class="dashicons dashicons-yes"></span> Organization "' + orgName + '" added!</span>' );
    }

  );
  
  
}
