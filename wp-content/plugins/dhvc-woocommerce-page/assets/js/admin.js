(function ( $ ) {
	vc.events.once("app.render",function(){
		var previewButton = '<a class="dhvc_woo_product_page_editable" href="' + dhvc_woo_page_admin.url + '">' + dhvc_woo_page_admin.preview_builder + "</a>";
		$('.composer-switch').append(previewButton)
	});
	
})( window.jQuery );