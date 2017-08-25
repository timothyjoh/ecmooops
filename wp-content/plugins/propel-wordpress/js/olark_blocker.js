jQuery(function() {
	if( !propelOlark.blacklist ) {
		return;
	}
	var blacklist = propelOlark.blacklist.split(",");
	jQuery.each( blacklist, function(idx, val) {
		if ( jQuery.trim(val).length == 0) return true;
		if(window.location.href.indexOf(val) >= 0){
	    	olark('api.box.hide');
	    	return false;
		}
	});
});
