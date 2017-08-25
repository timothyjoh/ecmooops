jQuery(function() {
	jQuery('#propel-quicklinks').prependTo("#dashboard-widgets-wrap");
	jQuery('#propel_quicklinks_widget').remove();
	jQuery("#propel_quicklinks_widget-hide").closest("label").remove();

	var interval = -1;
	var fixWooLabel = function() {
		var woo = jQuery("h3.hndle.ui-sortable-handle span:contains('WooCommerce Status')");
		if ( woo.length > 0 ) {
			var txt = woo.text();
			woo.text( txt.replace("WooCommerce", "eCommerce") );
			clearInterval(interval);
			return true;
		}
		return false;
	}
	if ( !fixWooLabel() ) {
		interval = setInterval( fixWooLabel, 100 );
	}
});
