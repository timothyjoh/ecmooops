<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class DHVC_Woo_Page_Admin {
	public function __construct(){
		
		add_action('admin_enqueue_scripts',array(&$this,'admin_enqueue_styles'));
		
		add_action( 'admin_print_scripts-post.php', array( &$this, 'admin_enqueue_scripts' ),100 );
		add_action( 'admin_print_scripts-post-new.php', array( &$this, 'admin_enqueue_scripts' ),100 );
		
		//product meta data
		add_action('add_meta_boxes', array(&$this,'add_meta_boxes'));
		add_action( 'save_post', array(&$this,'save_product_meta_data'),1,2 );
		
		//product category form
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_fields' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_fields' ), 10, 2 );
		add_action( 'created_term', array( $this, 'save_category_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );
		
		//Product display setting
		add_action('woocommerce_settings_catalog_options_after', array(&$this,'woocommerce_product_settings'),9);
		add_action('woocommerce_update_options_products',array(&$this,'woocommerce_update_options_products'));
	}
	
	protected function _product_template_type_setting(){
		$post_types = get_post_types(array('show_ui' => true),'objects');
		$options = array();
		foreach ($post_types as $post_type){
			$options[$post_type->name] = $post_type->labels->name.' ['.$post_type->name.']';
		}
		$custom_page_options = array();
		$custom_page_options[''] = __('Select default template&hellip;',DHVC_WOO_PAGE);
		$selected_post_type =  get_option('dhvc_woo_page_template_type','dhwc_template');
		$selected_default_template =  get_option('dhvc_woo_page_template_default','');
		if(get_post_type($selected_default_template)!=$selected_post_type)
			update_option('dhvc_woo_page_template_default', '');
		
		$pages = get_posts(array(
			'post_type'=>$selected_post_type,
			'posts_per_page'=>-1
		));
		if(is_array($pages) && !empty($pages)){
			foreach ($pages as $p){
				$custom_page_options[$p->ID] = $p->post_title;
			}
		}
		//$post_type = apply_filters('dhvc_woocommerce_page_template_type', $post_type_setting);
		return array(
			array(
				'title'    => __( 'Custom Product Template', DHVC_WOO_PAGE ),
				'type'     => 'title',
				'id' => 'dhvc_woo_page_product_title'
			),
			array(
				'title'    => __( 'Product Template Type', DHVC_WOO_PAGE ),
				'desc'     => __( 'This controls what is template post type for product.', DHVC_WOO_PAGE ),
				'id'       => 'dhvc_woo_page_template_type',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width:300px;',
				'default'  => 'dhwc_template',
				'type'     => 'select',
				'options'  => $options,
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Default Custom Template for product', DHVC_WOO_PAGE ),
				'desc'     => __( 'This controls what is custom template default.', DHVC_WOO_PAGE ),
				'id'       => 'dhvc_woo_page_template_default',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => $custom_page_options,
				'desc_tip' => true,
			),
			array(
				'type' => 'sectionend',
				'id' => 'dhvc_woo_page_product_template'
			),
		);
	}
	
	public function woocommerce_update_options_products(){
		woocommerce_update_options($this->_product_template_type_setting());
	}
	
	public function woocommerce_product_settings(){
		woocommerce_admin_fields($this->_product_template_type_setting());
	}
	
	public function admin_enqueue_scripts(){
		global $post;
		$post_type =  get_option('dhvc_woo_page_template_type','dhwc_template');
		$url = '';
		if($post_type===get_post_type($post) && $product_id  = dhvc_woo_product_page_find_product_by_template($post->ID)){
			$url = dhvc_woo_product_page_get_preview_editor_url($post->ID,'',$product_id);
		}
		if('product'===get_post_type($post) && $product_template_id = dhvc_woo_product_page_get_custom_template($post)){
			$url = dhvc_woo_product_page_get_preview_editor_url($product_template_id);
		}
		if(!empty($url)){
			wp_register_script('dhvc_woo_page_admin',DHVC_WOO_PAGE_URL.'/assets/js/admin.js',array('jquery'),DHVC_WOO_PAGE_VERSION,true);
			wp_localize_script('dhvc_woo_page_admin', 'dhvc_woo_page_admin', array(
				'preview_builder'=>__("Preview Template Editor",DHVC_WOO_PAGE),
				'url'=>$url
			));
			wp_enqueue_script('dhvc_woo_page_admin');
		}
	}
	
	public function admin_enqueue_styles(){
		wp_enqueue_style('dhvc-woo-page-chosen');
		wp_enqueue_style('dhvc-woo-page-admin', DHVC_WOO_PAGE_URL.'/assets/css/admin.css');
	}
	
	public function add_meta_boxes(){
		add_meta_box('dhvc-woo-page-bulder-products-meta-box', __('Product Template',DHVC_WOO_PAGE), array(&$this,'add_product_meta_box'), 'product','side');
	}
	
	public function add_product_meta_box(){
		global $post;
		$product_id = get_the_ID();
		$page_id = get_post_meta($product_id,'dhvc_woo_page_product',true);
		
		$selected_post_type =  get_option('dhvc_woo_page_template_type','dhwc_template');
		if(get_post_type($page_id)!=$selected_post_type)
			delete_post_meta($product_id,'dhvc_woo_page_product');
		
		$args = array(
			'post_status' => 'publish,private',
			'name'=>'dhvc_woo_page_product',
			'show_option_none'=>' ',
			'echo'=>false,
			'selected'=>absint($page_id)
		);
		wp_nonce_field ('dhvc_woocommerce_page_nonce', 'dhvc_woocommerce_page_nonce',false);
		if(empty($page_id)){
			list($product_template_id,$product_term) = dhvc_woo_product_page_get_custom_template($post,true);
			if(!empty($product_term)){
				echo __('Current use Custom Template by Category:',DHVC_WOO_PAGE).' <strong>'.$product_term.'</strong>';
				echo '<br>';
				echo __("OR",DHVC_WOO_PAGE);
			}
		}
		echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='chosen_select_nostd' id=", dhvc_woo_product_page_dropdown_custom( $args ) );
		echo '<span class="description" style="margin-top: 5px;display: block;opacity: 0.8;">'.__('You can change template post type with setting "Product Template Type" in "WooCommerce Settings -> Products -> Display"',DHVC_WOO_PAGE).'</span>';
	}
	
	public function save_product_meta_data($post_id,$post){
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
		
		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		// Check the nonce
		if (empty ( $_POST ['dhvc_woocommerce_page_nonce'] ) || ! wp_verify_nonce ( $_POST ['dhvc_woocommerce_page_nonce'], 'dhvc_woocommerce_page_nonce' )) {
			return;
		}
		
		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}
		
		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if(isset($_POST['dhvc_woo_page_product']) && !empty($_POST['dhvc_woo_page_product'])){
			update_post_meta( $post_id, 'dhvc_woo_page_product', absint($_POST['dhvc_woo_page_product']) );
		}else{
			delete_post_meta( $post_id, 'dhvc_woo_page_product');
		}
		
	}
	
	public function add_category_fields(){
		wp_enqueue_script( 'ajax-chosen' );
		wp_enqueue_script( 'chosen' );
		
	?>
	<div class="form-field">
		<label for="dhvc_woo_page_cat_product"><?php _e( 'Single Product Page Template', DHVC_WOO_PAGE ); ?></label>
		<?php 
		$args = array(
				'post_status' => 'publish,private',
				'name'=>'dhvc_woo_page_cat_product',
				'show_option_none'=>' ',
				'echo'=>false,
		);
		echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='chosen_select_nostd' id=", dhvc_woo_product_page_dropdown_custom( $args ) );
		
		?>
		<span class="description"><?php _e('You can change template post type with setting "Product Template Type" in "WooCommerce Settings -> Products -> Display"',DHVC_WOO_PAGE)?></span>
	</div>
	<script type="text/javascript">
	<!--
	jQuery("select.chosen_select_nostd").chosen({
		allow_single_deselect: 'true'
	});
	//-->
	</script>
	
	<?php
	}
	
	public function edit_category_fields( $term, $taxonomy ) {
		wp_enqueue_script( 'ajax-chosen' );
		wp_enqueue_script( 'chosen' );
		$dhvc_woo_page_cat_product = get_woocommerce_term_meta( $term->term_id, 'dhvc_woo_page_cat_product', true );
		$selected_post_type =  get_option('dhvc_woo_page_template_type','dhwc_template');
		if(get_post_type($dhvc_woo_page_cat_product)!=$selected_post_type)
			delete_woocommerce_term_meta($term->term_id,'dhvc_woo_page_cat_product');
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label><?php _e( 'Single Product Page', DHVC_WOO_PAGE ); ?></label></th>
		<td>
			<?php 
			$args = array(
					'post_status' => 'publish,private',
					'name'=>'dhvc_woo_page_cat_product',
					'show_option_none'=>' ',
					'echo'=>false,
					'selected'=>absint($dhvc_woo_page_cat_product)
			);
			echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='chosen_select_nostd' id=", dhvc_woo_product_page_dropdown_custom( $args ) );
			
			?>
			<span class="description"><?php _e('You can change template post type with setting "Product Template Type" in "WooCommerce Settings -> Products -> Display"',DHVC_WOO_PAGE)?></span>
			<script type="text/javascript">
			<!--
			jQuery("select.chosen_select_nostd").chosen({
				allow_single_deselect: 'true'
			});
			//-->
			</script>
		</td>
	</tr>
	<?php
	}
	
	public function save_category_fields( $term_id, $tt_id, $taxonomy ) {
		
		if( isset($_POST['dhvc_woo_page_cat_product']) && !empty($_POST['dhvc_woo_page_cat_product'])){
			update_woocommerce_term_meta( $term_id, 'dhvc_woo_page_cat_product', absint( $_POST['dhvc_woo_page_cat_product'] ) );
		}else{
			delete_woocommerce_term_meta($term_id,  'dhvc_woo_page_cat_product');
		}
	}
	
	
}
new DHVC_Woo_Page_Admin();