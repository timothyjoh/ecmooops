<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once vc_path_dir( 'EDITORS_DIR', 'class-vc-frontend-editor.php' );

class DHVC_Woo_Page_Vc_Frontend_Editor extends Vc_Frontend_Editor{
	
	public function init(){
		if(apply_filters('dhvc_woo_product_page_disable_fontend', false))
			return;
		$this->addHooks();
		add_action('vc_load_iframe_jscss', array(&$this,'vc_load_iframe_jscss'));
		add_action('vc_frontend_editor_enqueue_js_css', array(&$this,'vc_frontend_editor_enqueue_js_css'));
		
		if(isset($_GET['dhvc_woo_product_page_editor']) && 'frontend'===$_GET['dhvc_woo_product_page_editor']){
			remove_all_actions('admin_notices');
			remove_all_actions('network_admin_notices');
			 ! defined( 'DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR' ) && define( 'DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR', true );
			add_filter('vc_frontend_editor_iframe_url',array($this,'vc_frontend_editor_iframe_url'));
			$this->hookLoadEdit();
		}elseif ( vc_is_page_editable() ) {
			/**
			 * if page loaded inside frontend editor iframe it has page_editable mode.
			 * It required to some some js/css elements and add few helpers for editor to be used.
			 */
			$this->buildEditablePage();
		}
	}
	
	public function vc_load_iframe_jscss(){
		wp_enqueue_script('dhvc_woo_page_frontend_editable',DHVC_WOO_PAGE_URL.'/assets/js/vc-page-editable.js',array('jquery'),DHVC_WOO_PAGE_VERSION,true);
	}
	
	public function vc_frontend_editor_enqueue_js_css(){
		wp_enqueue_script('dhvc_woo_page_frontend_editor',DHVC_WOO_PAGE_URL.'/assets/js/vc-frontend.js',array('jquery'),DHVC_WOO_PAGE_VERSION,true);
	}
	
	public function vc_frontend_editor_iframe_url($url){
		return $url.'&dhvc_woo_product_page_editable=true';
	}
	
	public static function inlineEnabled() {
		return true;
	}
	
	public function hookLoadEdit() {
		add_action( 'current_screen', array(
			$this,
			'adminInit',
		) );
		do_action( 'vc_frontend_editor_hook_load_edit' );
	}
	
	public function adminInit() {
		$this->setPost();
		$this->renderEditor();
	}
	
	function renderEditor() {
		global $current_user;
		wp_get_current_user();
		$this->current_user = $current_user;
		$this->post_url = vc_str_remove_protocol( get_permalink( $this->post_id ) );

		if ( ! self::inlineEnabled() || ! vc_user_access()->wpAny( array(
				'edit_post',
				$this->post_id,
			) )->get()
		) {
			header( 'Location: ' . $this->post_url );
		}
		$this->registerJs();
		$this->registerCss();
		visual_composer()->registerAdminCss(); //bc
		visual_composer()->registerAdminJavascript(); //bc
		if ( $this->post && 'auto-draft' === $this->post->post_status ) {
			$post_data = array(
				'ID' => $this->post_id,
				'post_status' => 'draft',
				'post_title' => '',
			);
			add_filter( 'wp_insert_post_empty_content', array(
				$this,
				'allowInsertEmptyPost',
			) );
			wp_update_post( $post_data, true );
			$this->post->post_status = 'draft';
			$this->post->post_title = '';

		}
		add_filter( 'admin_body_class', array(
			$this,
			'filterAdminBodyClass',
		) );

		$this->post_type = get_post_type_object( $this->post->post_type );
		$this->url = $this->post_url . ( preg_match( '/\?/', $this->post_url ) ? '&' : '?' ) . 'vc_editable=true&vc_post_id=' . $this->post->ID . '&_vcnonce=' . vc_generate_nonce( 'vc-admin-nonce' );
		$this->url = apply_filters( 'vc_frontend_editor_iframe_url', $this->url );
		$this->enqueueAdmin();
		
		$this->enqueueMappedShortcode();
		wp_enqueue_media( array( 'post' => $this->post_id ) );
		remove_all_actions( 'admin_notices', 3 );
		remove_all_actions( 'network_admin_notices', 3 );
		
		$template_id = (int) dhvc_woo_product_page_get_custom_template($this->post);
		
		$post_custom_css = strip_tags( get_post_meta( $template_id, '_wpb_post_custom_css', true ) );
		$this->post_custom_css = $post_custom_css;

		if ( ! defined( 'IFRAME_REQUEST' ) ) {
			define( 'IFRAME_REQUEST', true );
		}
		/**
		 * @deprecated vc_admin_inline_editor action hook
		 */
		do_action( 'vc_admin_inline_editor' );
		/**
		 * new one
		 */
		do_action( 'vc_frontend_editor_render' );

		add_filter( 'admin_title', array(
			$this,
			'setEditorTitle',
		) );
		$this->render( 'editor' );
		die();
	}
	
	function render( $template ) {
		if('editor'===$template)
			dhvc_woo_product_page_include_editor_template('editor_frontend.tpl.php',array( 'editor' => $this ));
		else
			vc_include_template( 'editors/frontend_' . $template . '.tpl.php', array( 'editor' => $this ) );
	}
	
	public function buildEditablePage() {
		! defined( 'CONCATENATE_SCRIPTS' ) && define( 'CONCATENATE_SCRIPTS', false );
		visual_composer()->shared_templates->init();
		add_filter( 'the_title', array(
			$this,
			'setEmptyTitlePlaceholder',
		) );
		add_action( 'the_post', array(
			$this,
			'parseEditableContent',
		), 9999 ); // after all the_post actions ended
	
		do_action( 'vc_inline_editor_page_view' );
		add_filter( 'wp_enqueue_scripts', array(
			$this,
			'loadIFrameJsCss',
		) );
	
		add_action( 'wp_footer', array(
			$this,
			'printPostShortcodes',
		) );
		add_action( 'wp_footer', array(
			$this,
			'printCustomCSS',
		) );
	}
	
	public function printCustomCSS(){
		?>
		<style>
			.dhvc-woocommerce-page{
				overflow: visible !important;
			}
		</style>
		<?php
		do_action('dhvc_woo_product_page_inline_editor_view_custom_js_css');
	}
	
	public function parseEditableContent( $post ) {
		if ( ! vc_is_page_editable() || vc_action() || vc_post_param( 'action' ) ) {
			return;
		}
		
		$template_id = (int) dhvc_woo_product_page_get_custom_template($post);
		$post_id = (int) vc_get_param( 'vc_post_id' );
		if ( $template_id > 0 && $post_id > 0 && $post->ID === $post_id && ! defined( 'VC_LOADING_EDITABLE_CONTENT' ) ) {
			$template_data = get_post($template_id);
			define( 'VC_LOADING_EDITABLE_CONTENT', true );
			! defined( 'DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR' ) && define( 'DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR', true );
			remove_filter( 'the_content', 'wpautop' );
			do_action( 'vc_load_shortcode' );
			ob_start();
			$this->getPageShortcodesByContent( $template_data->post_content );
			vc_include_template( 'editors/partials/vc_welcome_block.tpl.php' );
			$post_content = ob_get_clean();
	
			ob_start();
			vc_include_template( 'editors/partials/post_shortcodes.tpl.php', array( 'editor' => $this ) );
			$post_shortcodes = ob_get_clean();
			$GLOBALS['vc_post_content'] = '<script type="template/html" id="vc_template-post-content" style="display:none">' . rawurlencode( apply_filters( 'the_content', $post_content ) ) . '</script>' . $post_shortcodes;
			// We already used the_content filter, we need to remove it to avoid double-using
			remove_all_filters( 'the_content' );
			// Used for just returning $post->post_content
			add_filter( 'the_content', array(
				$this,
				'editableContent',
			) );
		}
	}
	
	function loadShortcodes() {
		if ( vc_is_page_editable() && self::inlineEnabled() ) {
			$action = vc_post_param( 'action' );
			if ( 'vc_load_shortcode' === $action ) {
				! defined( 'DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR' ) && define( 'DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR', true );
				! defined( 'CONCATENATE_SCRIPTS' ) && define( 'CONCATENATE_SCRIPTS', false );
				ob_start();
				$this->setPost();
				$shortcodes = (array) vc_post_param( 'shortcodes' );
				do_action( 'vc_load_shortcode', $shortcodes );
				$this->renderShortcodes( $shortcodes );
				echo '<div data-type="files">';
				_print_styles();
				print_head_scripts();
				print_late_styles();
				print_footer_scripts();
				do_action( 'wp_print_footer_scripts' );
				echo '</div>';
				$output = ob_get_clean();
				die( apply_filters( 'vc_frontend_editor_load_shortcode_ajax_output', $output ) );
			} elseif ( 'vc_frontend_load_template' === $action ) {
				$this->setPost();
				visual_composer()->templatesPanelEditor()->renderFrontendTemplate();
			} else if ( '' !== $action ) {
				do_action( 'vc_front_load_page_' . esc_attr( vc_post_param( 'action' ) ) );
			}
		}
	}
}