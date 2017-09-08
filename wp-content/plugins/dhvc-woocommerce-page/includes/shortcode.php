<?php
class DHVC_Woo_Page_Shortcode {

	protected $shop_single;
	protected $shop_thumbnails_size;
	
	public function __construct() {
		
		add_shortcode ( 'dhvc_woo_product_page_images', array (&$this,'dhvc_woo_product_page_images_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_title', array (&$this,'dhvc_woo_product_page_title_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_rating', array (&$this,'dhvc_woo_product_page_rating_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_price', array (&$this,'dhvc_woo_product_page_price_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_excerpt', array (&$this,'dhvc_woo_product_page_excerpt_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_description', array (&$this,'dhvc_woo_product_page_description_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_additional_information', array (&$this,'dhvc_woo_product_page_additional_information' ) );
		add_shortcode ( 'dhvc_woo_product_page_add_to_cart', array (&$this,'dhvc_woo_product_page_add_to_cart_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_meta', array (&$this,'dhvc_woo_product_page_meta_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_sharing', array (&$this,'dhvc_woo_product_page_sharing_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_data_tabs', array (&$this,'dhvc_woo_product_page_data_tabs_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_reviews', array (&$this,'dhvc_woo_product_page_reviews_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_upsell', array (&$this,'dhvc_woo_product_page_upsell_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_related_products', array (&$this,'dhvc_woo_product_page_related_products_shortcode' ) );
		add_shortcode ( 'dhvc_woo_product_page_wishlist', array (&$this,'dhvc_woo_product_page_wishlist_shortcode' ) );
		$shortcodes = array(
				'dhvc_woo_product_page_product_category'           			=> 'product_category',
				'dhvc_woo_product_page_product_categories'        			=> 'product_categories',
				'dhvc_woo_product_page_products'                   			=> 'products',
				'dhvc_woo_product_page_product_recent_products'            	=> 'recent_products',
				'dhvc_woo_product_page_product_sale_products'              	=> 'sale_products',
				'dhvc_woo_product_page_product_best_selling_products'      	=> 'best_selling_products',
				'dhvc_woo_product_page_product_top_rated_products'         	=> 'top_rated_products',
				'dhvc_woo_product_page_product_featured_products'          	=> 'featured_products',
				'dhvc_woo_product_page_product_attribute'          			=> 'product_attribute',
				'dhvc_woo_product_page_shop_messages'              			=> 'shop_messages',
				'dhvc_woo_product_page_order_tracking' 						=> 'order_tracking',
				'dhvc_woo_product_page_cart'           						=> 'cart',
				'dhvc_woo_product_page_checkout'      						=> 'checkout',
				'dhvc_woo_product_page_my_account'     						=> 'my_account',
		);
		
		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode($shortcode , array(&$this,$function));
		}
		
	}
	
	protected function resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {
		// this is an attachment, so we have the ID
		if ( $attach_id ) {
			$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
			$actual_file_path = get_attached_file( $attach_id );
			// this is not an attachment, let's use the image url
		} else if ( $img_url ) {
			$file_path = parse_url( $img_url );
			$actual_file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
			$actual_file_path = ltrim( $file_path['path'], '/' );
			$actual_file_path = rtrim( ABSPATH, '/' ).$file_path['path'];
			$orig_size = getimagesize( $actual_file_path );
			$image_src[0] = $img_url;
			$image_src[1] = $orig_size[0];
			$image_src[2] = $orig_size[1];
		}
		$file_info = pathinfo( $actual_file_path );
		$extension = '.'. $file_info['extension'];
		
		// the image path without the extension
		$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];
		
		$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;
		
		// checking if the file size is larger than the target size
		// if it is smaller or the same size, stop right here and return
		if ( $image_src[1] > $width || $image_src[2] > $height ) {
		
			// the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
			if ( file_exists( $cropped_img_path ) ) {
				$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
				$vt_image = array (
						'url' => $cropped_img_url,
						'width' => $width,
						'height' => $height
				);
				return $vt_image;
			}
		
			// $crop = false
			if ( $crop == false ) {
				// calculate the size proportionaly
				$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
				$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;
		
				// checking if the file already exists
				if ( file_exists( $resized_img_path ) ) {
					$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );
		
					$vt_image = array (
							'url' => $resized_img_url,
							'width' => $proportional_size[0],
							'height' => $proportional_size[1]
					);
					return $vt_image;
				}
			}
		
			// no cache files - let's finally resize it
			$img_editor =  wp_get_image_editor($actual_file_path);
		
			if ( is_wp_error($img_editor) || is_wp_error( $img_editor->resize($width, $height, $crop)) ) {
				return array (
						'url' => '',
						'width' => '',
						'height' => ''
				);
			}
		
			$new_img_path = $img_editor->generate_filename();
		
			if ( is_wp_error( $img_editor->save( $new_img_path ) ) ) {
				return array (
						'url' => '',
						'width' => '',
						'height' => ''
				);
			}
			if(!is_string($new_img_path)) {
				return array (
						'url' => '',
						'width' => '',
						'height' => ''
				);
			}
		
			$new_img_size = getimagesize( $new_img_path );
			$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );
		
			// resized output
			$vt_image = array (
					'url' => $new_img,
					'width' => $new_img_size[0],
					'height' => $new_img_size[1]
			);
			return $vt_image;
		}
		
		// default output - without resizing
		$vt_image = array (
				'url' => $image_src[0],
				'width' => $image_src[1],
				'height' => $image_src[2]
		);
		return $vt_image;
	}
	
	public function dhvc_woo_product_page_images_shortcode($atts, $content = null) {
		global $product;
		extract ( shortcode_atts ( array (
				'hide_sale_flash' => '',
				'width'=>'',
				'height'=>'',
				'no_crop'=>'1',
				'thumb_width'=>'',
				'thumb_height'=>'',
				'thumb_no_crop'=>'1',
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		
		if (! empty ( $hide_sale_flash ))
			woocommerce_show_product_sale_flash ();
		
		$shop_single = wc_get_image_size('shop_single');
		
		if(!empty($width)){
			$shop_single['width'] = $width;
		}
		if(!empty($height)){
			$shop_single['height'] = $height;
		}
		
		$shop_single['crop'] = $no_crop;
		
		if(has_post_thumbnail($product->id)){
			$this->shop_single = $this->resize(get_post_thumbnail_id($product->id), null,$shop_single['width'],$shop_single['height'],$shop_single['crop']);
			add_filter('woocommerce_single_product_image_html', array($this,'woocommerce_single_product_image_html'),100,100);
		}
		
		$shop_thumbnail = wc_get_image_size('shop_thumbnail');
		if(!empty($thumb_width)){
			$shop_thumbnail['width'] = $thumb_width;
		}
		if(!empty($thumb_height)){
			$shop_thumbnail['height'] = $thumb_height;
		}
		$shop_thumbnail['crop'] = $thumb_no_crop;
		$this->shop_thumbnails_size = $shop_thumbnail;
		add_filter('woocommerce_single_product_image_thumbnail_html', array($this,'woocommerce_single_product_image_thumbnail_html'),100,100);
		
		woocommerce_show_product_images ();
		
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function woocommerce_single_product_image_html($html,$post_id){
		global $product;
		$attachment_count = count( $product->get_gallery_attachment_ids() );
		if ( $attachment_count > 0 ) {
			$gallery = '[product-gallery]';
		} else {
			$gallery = '';
		}
		$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
		$image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
		
		return sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '"><img src="%s" /></a>', $image_link, $image_title, $this->shop_single['url'] );
	}
	
	public function woocommerce_single_product_image_thumbnail_html($html,$attachment_id, $post_id, $image_class){
		//var_dump(func_get_args());
		$image_title = esc_attr( get_the_title( $attachment_id ) );
		$image_link = wp_get_attachment_url( $attachment_id );
		$image = $this->resize($attachment_id, null,$this->shop_thumbnails_size['width'],$this->shop_thumbnails_size['height'],$this->shop_thumbnails_size['crop']);
		return sprintf( '<a href="%s" class="%s" title="%s" data-rel="prettyPhoto[product-gallery]"><img class="class="attachment-shop_thumbnail" src="%s" /></a>', $image_link, $image_class, $image_title, $image['url'] );
	}
	
	public function dhvc_woo_product_page_title_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_template_single_title ();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_rating_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_template_single_rating();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_price_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_template_single_price ();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_excerpt_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_template_single_excerpt();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_description_shortcode($atts, $content = null){
		extract ( shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		
		the_content();
		
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_additional_information($atts, $content = null){
		global $product, $post;
		extract ( shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';

		if ( $product && ( $product->has_attributes() || ( $product->enable_dimensions_display() && ( $product->has_dimensions() || $product->has_weight() ) ) ) ) {
			wc_get_template( 'single-product/tabs/additional-information.php' );
		}

		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_add_to_cart_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_template_single_add_to_cart ();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_meta_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_template_single_meta ();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_sharing_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_template_single_sharing ();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_data_tabs_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_output_product_data_tabs ();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_reviews_shortcode($atts, $content = null){
		extract ( shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(comments_open() ){
			comments_template();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_related_products_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'posts_per_page'=>4,
				'columns'=>4,
				'orderby'=>'date',
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		echo WC_Shortcodes::related_products($atts);
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_upsell_shortcode($atts, $content = null) {
		extract ( shortcode_atts ( array (
				'posts_per_page'=>4,
				'columns'=>4,
				'orderby'=>'date',
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		woocommerce_upsell_display ( $posts_per_page, $columns, $orderby);
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_wishlist_shortcode($atts, $content = null){
		extract ( shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		$output = '';
		$output .= '<div class="dhvc-woocommerce-page-wishlist ' . ($el_class ? $el_class :'') . '">';
		$output .= do_shortcode('[yith_wcwl_add_to_wishlist]');
		$output .= '</div>';
		return $output;
	}
	
	public function product_category($atts, $content = null) {
		extract ( shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::product_category($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function product_categories($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::product_categories($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function products($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::products($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function recent_products($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::recent_products($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function sale_products($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::sale_products($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function best_selling_products($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::best_selling_products($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function top_rated_products($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::top_rated_products($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function featured_products($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::featured_products($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function product_attribute($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::product_attribute($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function shop_messages($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::shop_messages($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function order_tracking($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::order_tracking($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function cart($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::cart($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function checkout($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
				), $atts ) );
				$output = '';
				if (! empty ( $el_class ))
					$output .= '<div class="' . $el_class . '">';
				$output .= WC_Shortcodes::checkout($atts);
				if (! empty ( $el_class ))
					$output .= '</div>';
				return $output;
	}
	public function my_account($atts, $content = null) {
		extract ( shortcode_atts ( array (
		'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= WC_Shortcodes::my_account($atts);
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
}
new DHVC_Woo_Page_Shortcode ();