<?php
class DHVC_Woo_Page_Admin {
	public function __construct(){
		add_action('admin_enqueue_scripts',array(&$this,'admin_enqueue_styles'));
		
		
		//product meta data
		add_action('add_meta_boxes', array(&$this,'add_meta_boxes'));
		add_action( 'save_post', array(&$this,'save_product_meta_data'),1,2 );
		
		//product category form
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_fields' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_fields' ), 10, 2 );
		add_action( 'created_term', array( $this, 'save_category_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );
	}
	
	public function admin_enqueue_styles(){
		wp_enqueue_style('dhvc-woo-page-chosen');
		wp_enqueue_style('dhvc-woo-page-admin', DHVC_WOO_PAGE_URL.'/assets/css/admin.css');
	}
	
	public function add_meta_boxes(){
		add_meta_box('dhvc-woo-page-bulder-products-meta-box', 'Page', array(&$this,'add_product_meta_box'), 'product','side');
	}
	
	public function add_product_meta_box(){
		$product_id = get_the_ID();
		$page_id = get_post_meta($product_id,'dhvc_woo_page_product',true);
		$args = array(
			'name'=>'dhvc_woo_page_product',
			'show_option_none'=>' ',
			'echo'=>false,
			'selected'=>absint($page_id)
		);
		echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;',DHVC_WOO_PAGE) .  "' class='chosen_select_nostd' id=", wp_dropdown_pages( $args ) );
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
		
		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}
		
		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if(!empty($_POST['dhvc_woo_page_product'])){
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
		<label for="dhvc_woo_page_cat_product"><?php _e( 'Single Product Page', DHVC_WOO_PAGE ); ?></label>
		<?php 
		$args = array(
				'name'=>'dhvc_woo_page_cat_product',
				'show_option_none'=>' ',
				'echo'=>false,
		);
		echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;',DHVC_WOO_PAGE) .  "' class='chosen_select_nostd' id=", wp_dropdown_pages( $args ) );
		
		?>
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
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label><?php _e( 'Single Product Page', DHVC_WOO_PAGE ); ?></label></th>
		<td>
			<?php 
			$args = array(
					'name'=>'dhvc_woo_page_cat_product',
					'show_option_none'=>' ',
					'echo'=>false,
					'selected'=>absint($dhvc_woo_page_cat_product)
			);
			echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;',DHVC_WOO_PAGE) .  "' class='chosen_select_nostd' id=", wp_dropdown_pages( $args ) );
			
			?>
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
		
		if(!empty($_POST['dhvc_woo_page_cat_product'])){
			update_woocommerce_term_meta( $term_id, 'dhvc_woo_page_cat_product', absint( $_POST['dhvc_woo_page_cat_product'] ) );
		}else{
			delete_woocommerce_term_meta($term_id,  'dhvc_woo_page_cat_product');
		}
	}
	
	
}
new DHVC_Woo_Page_Admin();