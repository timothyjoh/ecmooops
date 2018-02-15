<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DHVC_Woo_Page_Shortcode {
	protected $_loaded = false;
	public function __construct() {
		$this->hook_add_shortcodes();
	}
	
	public function hook_add_shortcodes(){
		add_action('dhvc_woo_product_page_shortcode_placeholder_render', array(&$this,'add_shortcode'));
		if($this->_loaded)
			return;
		add_action('cornerstone_shortcodes_loaded', array(&$this,'add_shortcode'));
		
		add_action('vc_load_shortcode', array(&$this,'add_shortcode'));
		
		if(apply_filters('dhvc_woocommerce_page_use_hook_before_override',false))
			add_action( 'dhvc_woocommerce_page_before_override', array( &$this, 'add_shortcode' ));
		else
			add_action( 'template_redirect', array( &$this, 'add_shortcode' ));
		$this->_loaded = true;
	}
	
	public function add_shortcode(){
		global $post;
		foreach ( dhvc_woo_product_page_single_shortcodes() as $shortcode => $function ) {
			if('product'===get_post_type($post)){
				add_shortcode($shortcode , array(&$this,$function));
			}else{
				add_shortcode($shortcode , array(&$this,'shortcode_error2'));
			}
		}
		foreach ( dhvc_woo_product_page_wc_shortcodes() as $shortcode => $function ) {
			add_shortcode($shortcode , array(&$this,$function));
		}
		if (class_exists ( 'acf' )) {
			add_shortcode ( 'dhvc_woo_product_page_acf_field', array(&$this,'dhvc_woo_product_page_acf_field_shortcode') );
		}
		if(function_exists('fpd_get_option')){
			add_shortcode ( 'dhvc_woo_product_page_fpd', array(&$this,'dhvc_woo_product_page_fpd'));
		}
		if(defined( 'YITH_YWZM_DIR' )){
			remove_shortcode('dhvc_woo_product_page_images');
			add_shortcode ( 'dhvc_woo_product_page_images',array(&$this,'dhvc_woo_product_page_images_shortcode_custom') );
		}
	}
	
	
	public function shortcode_error($atts='',$content='',$tag=''){
		 return '<em style="color:red;display:block">Use shortcode "'.$tag.'" is wrong (Please view Product after assigning Custom Template), to use plugin please see <a target="_blank" href="https://www.youtube.com/watch?v=DhqOQdR7K_8">Video</a><br><br></em>';
	}
	
	public function shortcode_error2($atts='',$content='',$tag=''){
		return '<em style="display: block; color: rgb(51, 51, 51); font-weight: bold; white-space: pre-wrap;">Shortcode "'.ucwords(str_replace(array('dhvc_woo','_'), array('Single ',' '), $tag)).'". <i style="font-size: inherit; color: rgb(255, 0, 0); font-weight: normal;">Please view Product after assigning Custom Template</i></em>';
	}
	
	public function dhvc_woo_product_page_fpd($atts){
		extract ( $this->_shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="dhvc-woo-product-page-fpd ' . $el_class . '">';
		echo '<style type="text/css">#fpd-start-customizing-button~#fpd-start-customizing-button{display:none}</style>';
		$FPD_Frontend_Product = new FPD_Frontend_Product;
		$FPD_Frontend_Product->add_product_designer();
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_images_shortcode_custom($atts, $content = null){
		global $product;
		extract ( $this->_shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(function_exists('dhwcpl_single_product')){
			dhwcpl_single_product();
			dhwcpl_product_sale();
			dhwcpl_product_out_of_store();
		}
	
		$wc_get_template = function_exists( 'wc_get_template' ) ? 'wc_get_template' : 'woocommerce_get_template';
		$wc_get_template( 'single-product/product-image-magnifier.php', array(), '', YITH_YWZM_DIR . 'templates/' );
	
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	protected function _shortcode_atts($defaults=array(),$atts){
		if(isset($atts['class']))
			$atts['el_class'] = $atts['class'];
		$atts['css'] = isset($atts['css']) ? $atts['css'] : '';
		$atts['el_class'] = isset($atts['el_class']) ? isset($atts['el_class']).$this->_get_vc_shortcode_custom_css_class($atts['css']) 
			: $this->_get_vc_shortcode_custom_css_class($atts['css']);
		return shortcode_atts ( $defaults, $atts );
	}
	
	protected function _get_vc_shortcode_custom_css_class($param_value, $prefix = ' ' ){
		if(function_exists('vc_shortcode_custom_css_class'))
			return vc_shortcode_custom_css_class($param_value, $prefix);
		return '';
	}
	
	public function dhvc_woo_product_page_acf_field_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'field' => '',
			'el_class' => ''
		), $atts ) );
		if (empty ( $field )) {
			return '';
		}
		ob_start ();
		echo '<div class="dhvc_woo_product_page_acf_field ' . $el_class . '">';
		//the_field ( $field );
		$value = get_field($field);
		//filter to custom display
		$value = apply_filters('dhvc_woo_product_page_acf_field', $value, $field);
		if( is_array($value) )
		{
			$value = @implode(', ',$value);
		}
			
		echo do_shortcode($value);
			
		echo '</div>';
		return ob_get_clean ();
	}
	
	
	public function dhvc_woo_product_page_images_shortcode($atts, $content = null) {
		global $product;
		extract ( $this->_shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(function_exists('dhwcpl_single_product')){
			dhwcpl_single_product();
			dhwcpl_product_sale();
			dhwcpl_product_out_of_store();
		}
		if(class_exists('JCKWooThumbs')){
			$JCKWooThumbs = new JCKWooThumbs;
			$JCKWooThumbs->show_product_images();
		}else if(class_exists('WC_Product_Gallery_slider')){
			$enabled = get_option( 'woocommerce_product_gallery_slider_enabled' );
			$enabled_for_post   = get_post_meta( $post->ID, '_woocommerce_product_gallery_slider_enabled', true );
		
			if ( ( $enabled == 'yes' && $enabled_for_post !== 'no' ) || ( $enabled == 'no' && $enabled_for_post == 'yes' ) ) {
					WC_Product_Gallery_slider::setup_scripts_styles();
					WC_Product_Gallery_slider::show_product_gallery();
			}
		}else{
			woocommerce_show_product_sale_flash();
			woocommerce_show_product_images ();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_title_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );

		extract ( $this->_shortcode_atts ( array (
				'heading_type' => '' 
		), $atts ) );

		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		
		
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<h1 itemprop="name" class="single_product_title entry-title"><?php the_title(); ?></h1>
			<?php
		}else{
			echo '<' . $heading_type . '>';
			woocommerce_template_single_title (); 
			echo '</' . $heading_type . '>';

		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_rating_shortcode($atts, $content = null) {
		global $post,$product;
		extract ( $this->_shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<?php
			$count   = $product->get_rating_count();
			$average = $product->get_average_rating();
		
			if ( $count > 0 ) : ?>
			
			<div class="woocommerce-product-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
				<div class="star-rating" title="<?php printf( __( 'Rated %s out of 5', 'woocommerce' ), $average ); ?>">
					<span style="width:<?php echo ( ( $average / 5 ) * 100 ); ?>%">
						<strong itemprop="ratingValue" class="rating"><?php echo esc_html( $average ); ?></strong> <?php _e( 'out of 5', 'woocommerce' ); ?>
					</span>
				</div>
				<a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $count, 'woocommerce' ), '<span itemprop="ratingCount" class="count">' . $count . '</span>' ); ?>)</a>
			</div>
			<?php
			endif;
		}else{
			woocommerce_template_single_rating();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_price_shortcode($atts, $content = null) {
		global $post,$product;
		extract ( $this->_shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
	
				<div itemprop="price" class="mk-single-price"><?php echo $product->get_price_html(); ?></div>
	
				<meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
				<link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />
	
			</div>
			<?php
		}else{
			woocommerce_template_single_price ();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_excerpt_shortcode($atts, $content = null) {
		global $post,$product;
		extract ( $this->_shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<div itemprop="description">
				<?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
			</div>
			<?php
		}else{
			woocommerce_template_single_excerpt();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_description_shortcode($atts, $content = null){
		global $post;
		extract ( $this->_shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(defined('DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR')){
			$content = $post->post_content;
			
			/**
			 * Filters the post content.
			 *
			 * @since 0.71
			 *
			 * @param string $content Content of the current post.
			*/
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			echo $content;
		}else{
			the_content();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_additional_information($atts, $content = null){
		global $product, $post;
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		global $post,$product;
		extract ( $this->_shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<div class="mk_product_meta">
	
				<?php do_action( 'woocommerce_product_meta_start' ); ?>
	
				<?php
				$cat_count = sizeof( get_the_terms( $post->ID, 'product_cat' ) );
				$tag_count = sizeof( get_the_terms( $post->ID, 'product_tag' ) );
	
				 if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
	
					<span class="sku_wrapper"><?php _e( 'SKU:', 'woocommerce' ); ?> <span class="sku" itemprop="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : __( 'N/A', 'woocommerce' ); ?></span>.</span>
	
				<?php endif; ?>
	
				<?php echo $product->get_categories( ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', $cat_count, 'woocommerce' ) . ' ', '.</span>' ); ?>
	
				<?php echo $product->get_tags( ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', $tag_count, 'woocommerce' ) . ' ', '.</span>' ); ?>
	
				<?php do_action( 'woocommerce_product_meta_end' ); ?>
	
			</div>
			<?php
		}else{
			woocommerce_template_single_meta ();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_sharing_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<ul class="woocommerce-social-share">
				<li><a class="facebook-share" data-title="<?php the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-jupiter-icon-simple-facebook"></i></a></li>
				<li><a class="twitter-share" data-title="<?php the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-moon-twitter"></i></a></li>
				<li><a class="googleplus-share" data-title="<?php the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-jupiter-icon-simple-googleplus"></i></a></li>
				<li><a class="pinterest-share" data-image="<?php echo $image_src_array[0]; ?>" data-title="<?php echo get_the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-jupiter-icon-simple-pinterest"></i></a></li>
			</ul>
			<?php
		}else{
			woocommerce_template_single_sharing ();
		}
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	public function dhvc_woo_product_page_data_tabs_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
				'posts_per_page'=>4,
				'columns'=>4,
				'orderby'=>'date',
				'el_class' => '' 
		), $atts ) );
		ob_start ();
		if (! empty ( $el_class ))
			echo '<div class="' . $el_class . '">';
		echo woocommerce_related_products($atts);
		if (! empty ( $el_class ))
			echo '</div>';
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_custom_field_shortcode($atts, $content = null){
		global $post;
		extract ( $this->_shortcode_atts ( array (
			'key'=>'',
			'label'=>'',
			'el_class' => ''
		), $atts ) );

		$css_class = 'dhvc_woo_product-meta-field-' . $key. ( strlen( $el_class ) ? ' ' . $el_class : '' );
		$label_html = '';
		if ( strlen( $label ) ) {
			$label_html = '<span class="dhvc_woo_product-meta-label">' . esc_html( $label ) . '</span>';
		}
		ob_start ();
		if ( !empty( $key ) && $value = get_post_meta($post->ID,$key,true) ) :  
		?>
			<div class="dhvc_woo_product_page_custom_field <?php echo esc_attr( $css_class ) ?>">
				<?php echo $label_html ?>
				<?php echo apply_filters('dhvc_woo_product_page_custom_field_value',$value,$key,$post);?>
			</div>
		<?php 
		endif;
		return ob_get_clean ();
	}
	
	public function dhvc_woo_product_page_upsell_products_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		$output = '';
		$output .= '<div class="dhvc-woocommerce-page-wishlist ' . ($el_class ? $el_class :'') . '">';
		$output .= do_shortcode('[yith_wcwl_add_to_wishlist]');
		$output .= '</div>';
		return $output;
	}
	
	public function product_category($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
	
	public function breadcrumb($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => ''
		), $atts ) );
		$output = '';
		if (! empty ( $el_class ))
			$output .= '<div class="' . $el_class . '">';
		$output .= woocommerce_breadcrumb();
		if (! empty ( $el_class ))
			$output .= '</div>';
		return $output;
	}
	public function checkout($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
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
		extract ( $this->_shortcode_atts ( array (
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
new DHVC_Woo_Page_Shortcode;