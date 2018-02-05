var dhvc_woo_page_iframe = {};
(function ( $ ) {
	dhvc_woo_page_iframe.product_gallery = function(model_id){
		var $el = $("[data-model-id=" + model_id + "]");
		$().wc_product_gallery && $el.find( '.woocommerce-product-gallery' ).each( function() {
			$( this ).wc_product_gallery();
		} );
		$( document.body ).trigger( 'dhvc_woo_product_page_images_iframe_edit', [$el] );
	}
	dhvc_woo_page_iframe.product_tab = function(model_id){
		var $el = $("[data-model-id=" + model_id + "]");
		$el.find( '.wc-tabs-wrapper, .woocommerce-tabs, #rating' ).trigger( 'init' );
		$( document.body ).trigger( 'dhvc_woo_product_page_data_tabs_iframe_edit', [$el] );
	}
})( window.jQuery );