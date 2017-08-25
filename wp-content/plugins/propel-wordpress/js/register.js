jQuery( document ).ready( function() {
	//console.log('ready to register!');
	jQuery('#wpum-show-password').on('change', function() {
		document.getElementById('password').type = this.checked ? 'text' : 'password';
	});
	
    jQuery('#user_email').on('change',function() {
      jQuery('#username').val(jQuery(this).val());
    });

});