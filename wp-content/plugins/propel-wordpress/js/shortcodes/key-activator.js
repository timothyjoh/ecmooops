key = getCookie( 'key' );

jQuery( '#okm-key' ).val( key );

if ( key != undefined )
  validate_key();

function validate_key() {
  jQuery( '.validation .dashicons-yes' ).hide();
  jQuery( '.validation .dashicons-no' ).hide();

  if ( jQuery( '#okm-key' ).val().length == 9 ) {
    // jQuery( '.validation .dashicons-yes' ).show();
    jQuery( '.validation .message' ).html( '' )
      .removeClass( 'error' ).addClass( 'success' );

    jQuery( '#okm-key' ).removeClass( 'error' ).addClass( 'success' );

    jQuery( '#activate_key' ).removeAttr( 'disabled' );
  } else {
    // jQuery( '.validation .dashicons-no' ).show();
    jQuery( '.validation .message' ).html( 'Key must have 9 characters (letters and numbers)' )
      .removeClass( 'success' ).addClass( 'error' );

    jQuery( '#okm-key' ).removeClass( 'success' ).addClass( 'error' );

    jQuery( '#activate_key' ).attr( 'disabled', 'disabled' );
  }
}

jQuery( '#okm-key' ).on( 'focus', function(e) {
  jQuery( e.target ).removeClass( 'success error' );
} ).on( 'keyup paste', function() {
  jQuery("#okm-key").val((jQuery("#okm-key").val()).toUpperCase());
  validate_key(); 
} );


jQuery( '#activate_key' ).on( 'click', function(e) {
  key = jQuery( '#okm-key' ).val();

  activate_key( key ); 
} );



function activate_key( key ) {
  var success = false;

  jQuery.ajax({
    async: false,
    type: "POST",
    url: '/wp-admin/admin-ajax.php',  
    data: {  
      action: 'activate_key',  
      key: key
    },  
    success: function( msg ) {  
      jQuery( 'img.load' ).hide();
      jQuery( '#okm-key' ).removeClass( 'success' );

      if ( msg.success ) {
        jQuery( '.activation .dashicons-yes' ).show();
        jQuery( '.activation .message' ).html( 'Key is Activated!' )
          .removeClass( 'error' ).addClass( 'success' );

        jQuery( '#okm-key' ).addClass( 'success' );
       
        window.location = msg.data.url;
      } else {
        jQuery( '.activation .dashicons-no' ).show();
        jQuery( '.activation .message' ).html( msg.data[0] )
          .removeClass( 'success' ).addClass( 'error' );

        jQuery( '#okm-key' ).addClass( 'error' );
      }

      success = msg.success;
    },  
    error: function( XMLHttpRequest, textStatus, errorThrown ) {
      console.log( errorThrown );
    }
  });

  return success;
}

// Thanks to http://stackoverflow.com/questions/10730362/get-cookie-by-name
function getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
}
