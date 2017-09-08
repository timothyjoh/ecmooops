<?php

function the_product_page_content( $more_link_text = null, $strip_teaser = false){
	global $product_page;
	$content = $product_page->post_content;
	$content = apply_filters( 'the_content', $content );
	$content = str_replace( ']]>', ']]&gt;', $content );
	echo $content;
}


function dhvc_woo_product_page_setting_field_categories($settings, $value){
	$category_slugs = explode(',',$value);
	$args = array(
			'orderby' => 'name',
			'hide_empty' => 0,
	);
	$categories = get_terms( 'product_cat', $args );
	$output = '<select id= "'.$settings['param_name'].'" multiple="multiple" class="dhvc-woo-product-page-select chosen_select_nostd '.$settings['param_name'].' '.$settings['type'].'">';
	if( ! empty($categories)){
		foreach ($categories as $cat):
		$output .= '<option value="' . esc_attr( $cat->slug ) . '"' . selected( in_array( $cat->slug, $category_slugs ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
		endforeach;
	}
	$output .= '</select>';
	$output .='<input id= "'.$settings['param_name'].'" type="hidden" class="wpb_vc_param_value wpb-textinput" name="'.$settings['param_name'].'" value="'.$value.'" />';
	return $output;
}

function dhvc_woo_product_page_setting_field_products_ajax($settings, $value){
	$product_ids = array();

	if(!empty($value))
		$product_ids = array_map( 'absint', explode( ',', $value ) );

	$output = '<select id= "'.$settings['param_name'].'" multiple="multiple" class="dhvc-woo-product-page-select dhvc-woo-product-page-ajax-products '.$settings['param_name'].' '.$settings['type'].'">';
	if(!empty($product_ids)){
		foreach ( $product_ids as $product_id ) {
			$product = get_product( $product_id );
			$output .= '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . wp_kses_post( dhvc_woo_get_product_formatted_name($product) ) . '</option>';

		}
	}
	$output .= '</select>';
	$output .='<input id= "'.$settings['param_name'].'" type="hidden" class="wpb_vc_param_value wpb-textinput" name="'.$settings['param_name'].'" value="'.$value.'" />';
	return $output;
}

function dhvc_woo_product_page_search_products (){
	header( 'Content-Type: application/json; charset=utf-8' );
	
	$term = (string) sanitize_text_field( stripslashes( $_GET['term'] ) );


	if (empty($term)) die();

	$post_types = array('product', 'product_variation');

	if ( is_numeric( $term ) ) {

		$args = array(
				'post_type'			=> $post_types ,
				'post_status'	 	=> 'publish',
				'posts_per_page' 	=> -1,
				'post__in' 			=> array(0, $term),
				'fields'			=> 'ids'
		);

		$args2 = array(
				'post_type'			=> $post_types,
				'post_status'	 	=> 'publish',
				'posts_per_page' 	=> -1,
				'post_parent' 		=> $term,
				'fields'			=> 'ids'
		);

		$args3 = array(
				'post_type'			=> $post_types,
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_query' 		=> array(
						array(
								'key' 	=> '_sku',
								'value' => $term,
								'compare' => 'LIKE'
						)
				),
				'fields'			=> 'ids'
		);

		$posts = array_unique(array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ) ));

	} else {

		$args = array(
				'post_type'			=> $post_types,
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				's' 				=> $term,
				'fields'			=> 'ids'
		);

		$args2 = array(
				'post_type'			=> $post_types,
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_query' 		=> array(
						array(
								'key' 	=> '_sku',
								'value' => $term,
								'compare' => 'LIKE'
						)
				),
				'fields'			=> 'ids'
		);

		$posts = array_unique(array_merge( get_posts( $args ), get_posts( $args2 ) ));

	}

	$found_products = array();

	if ( $posts ) foreach ( $posts as $post ) {

		$product = get_product( $post );

		$found_products[ $post ] = dhvc_woo_get_product_formatted_name($product);

	}

	echo json_encode( $found_products );

	die();
}

add_action('wp_ajax_dhvc_woo_product_page_search_products', 'dhvc_woo_product_page_search_products');

function get_the_product_page_content( $more_link_text = null, $strip_teaser = false){
	global $page, $more, $preview, $pages, $multipage,$product_page;
	
	$post = $product_page;
	if ( null === $more_link_text )
		$more_link_text = __( '(more&hellip;)' );

	$output = '';
	$has_teaser = false;

	// If post password required and it doesn't match the cookie.
	if ( post_password_required( $post ) )
		return get_the_password_form( $post );
	
	if ( $page > count( $pages ) ) // if the requested page doesn't exist
		$page = count( $pages ); // give them the highest numbered page that DOES exist

	$content = $pages[$page - 1];
	if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
		$content = explode( $matches[0], $content, 2 );
		if ( ! empty( $matches[1] ) && ! empty( $more_link_text ) )
			$more_link_text = strip_tags( wp_kses_no_null( trim( $matches[1] ) ) );

		$has_teaser = true;
	} else {
		$content = array( $content );
	}

	if ( false !== strpos( $post->post_content, '<!--noteaser-->' ) && ( ! $multipage || $page == 1 ) )
		$strip_teaser = true;

	$teaser = $content[0];
	
	if ( $more && $strip_teaser && $has_teaser )
		$teaser = '';

	$output .= $teaser;

	if ( count( $content ) > 1 ) {
		if ( $more ) {
			$output .= '<span id="more-' . $post->ID . '"></span>' . $content[1];
		} else {
			if ( ! empty( $more_link_text ) )

				$output .= apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>", $more_link_text );
			$output = force_balance_tags( $output );
		}
	}

	if ( $preview ) 
		$output =	preg_replace_callback( '/\%u([0-9A-F]{4})/', '_convert_urlencoded_to_entities', $output );

	return $output;
}