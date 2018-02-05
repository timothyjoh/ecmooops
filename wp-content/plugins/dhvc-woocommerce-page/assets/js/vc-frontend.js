(function ( $ ) {
	window.InlineShortcodeView_dhvc_woo_product_page_images = window.InlineShortcodeView.extend( {
		render: function () {
			var model_id = this.model.get( 'id' );
			window.InlineShortcodeView_dhvc_woo_product_page_images.__super__.render.call( this );
			vc.frame_window.vc_iframe.addActivity(function() {
				vc.frame_window.dhvc_woo_page_iframe.product_gallery(model_id);
            })
			return this;
		},
		parentChanged: function () {
			window.InlineShortcodeView_dhvc_woo_product_page_images.__super__.parentChanged.call( this );
			vc.frame_window.dhvc_woo_page_iframe.product_gallery( this.model.get( 'id' ));
		}
	} );
	
	window.InlineShortcodeView_dhvc_woo_product_page_data_tabs = window.InlineShortcodeView.extend( {
		render: function () {
			var model_id = this.model.get( 'id' );
			window.InlineShortcodeView_dhvc_woo_product_page_data_tabs.__super__.render.call( this );
			vc.frame_window.vc_iframe.addActivity(function() {
				vc.frame_window.dhvc_woo_page_iframe.product_tab(model_id);
            })
			return this;
		},
		parentChanged: function () {
			window.InlineShortcodeView_dhvc_woo_product_page_data_tabs.__super__.parentChanged.call( this );
			vc.frame_window.dhvc_woo_page_iframe.product_tab( this.model.get( 'id' ));
		}
	} );
})( window.jQuery );