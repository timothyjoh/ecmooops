jQuery( document ).ready( function() {
	var key = get_key();

	if ( check_key( key ) )
		jQuery( '.dashicons-yes' ).show();

	jQuery( '#okm-tenant-secret-key' ).on( 'paste keypress', function(e) {
		setTimeout( function () {
			key = get_key();
			check_key( key );
		}, 100);
	});
});

function get_key() {
	return jQuery( '#okm-tenant-secret-key' ).val();
}

function check_key( key ) {
	jQuery( '.dashicons-no, .dashicons-yes' ).hide();

	if ( ! valid_key( key ) )
		return false;
	
	jQuery( 'img.load' ).show();

	var success = false;

	jQuery.ajax({
		type: "POST",
		url: '/wp-admin/admin-ajax.php',  
		data: {  
			action: 'check_okm_tenant_secret_key',
			key: key
		},  
		success: function( msg ) {  
			jQuery( 'img.load, .dashicons-no' ).hide();


			if ( msg.success ) {
				save_key( key );
			} else {
				jQuery( '.dashicons-no' ).show();
			}

			success = msg.success;

		},  
		error: function( XMLHttpRequest, textStatus, errorThrown ) {
			console.log( 'Error', XMLHttpRequest, textStatus, errorThrown );
		}
	});

	return success;
}

function valid_key( key ) {
	jQuery( '.dashicons-no, .dashicons-yes' ).hide();

	if ( key.length == 20 || key.length == 17 ) return true;
	else {
		jQuery( '.dashicons-no' ).show();
		return false;
	}
}

function save_key( key ) {
	jQuery( 'img.load' ).show();

	jQuery.ajax({
		type: "POST",
		url: '/wp-admin/admin-ajax.php',  
		data: {  
			action: 'save_okm_tenant_secret_key',
			key: key
		},  
		success: function( msg ) {
			jQuery( 'img.load' ).hide();
			jQuery( '.dashicons-yes' ).show();
			jQuery( '#okm-tenant-secret-key' ).blur();
			return false;  
		},  
		error: function( XMLHttpRequest, textStatus, errorThrown ) {
			console.log( 'error' );
		}

	});	
}
