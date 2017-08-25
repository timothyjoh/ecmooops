jQuery( document ).ready( function() {
  jQuery( '#okm-key-input' ).on( 'focus', function(e) {
    jQuery( e.target ).removeClass( 'error' );
    jQuery( '.error.message' ).hide();
  } ).on( 'keypress', function(e) {
    if ( e.keyCode == 13 ) // enter
      jQuery( '#okm-key-submit' ).click();
  } );

  jQuery( '#okm-key-submit' ).on( 'click', function(e) {
    url = widgetData.url;
    key = jQuery( '#okm-key-input' ).val();

    if ( key == '' ) {
      jQuery( '#okm-key-input' ).addClass( 'error' );  
      jQuery( '.error.message' ).html( 'Please enter a key' ).show();
      return;
    }

    document.cookie = 'key=' + key + ';path=/';

    if ( widgetData.user_is_logged_in )
      window.location =  url + "/activate-key/";
    else
      window.location =  url + "/wp-login.php?redirect_to=activate-key";
  } );
} );  
