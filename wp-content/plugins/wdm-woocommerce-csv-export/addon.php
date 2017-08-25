<?php

/* Plugin Name: Woocommerce CSV export Addon
 * Plugin URI: http://wisdmlabs.com/
 * Description: 
 * Version: 1.0
 * Author: Wisdmlabs
 * */

add_filter( 'wc_customer_order_csv_export_order_headers', 'wdm_wc_customer_order_csv_export_order_headers', 10, 1 );

function wdm_wc_customer_order_csv_export_order_headers( $column_headers ) {
	$result = array_slice( $column_headers, 0, 4, true ) +
	array( "transaction_id" => "transaction_id" ) +
	array_slice( $column_headers, 4, count( $column_headers ) - 1, true );
	//echo '<pre>';print_R($res);echo '</pre>';exit;
	return $result;
}

add_filter( 'wc_customer_order_csv_export_order_row_one_row_per_item', 'wdm_wc_customer_order_csv_export_order_row_one_row_per_item', 10, 4 );

function wdm_wc_customer_order_csv_export_order_row_one_row_per_item( $order_data, $item, $order, $this ) {
	global $wpdb;
	$order_id		 = $order->id;
	$order_id		 = 3572;


	$sql	 = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_post_id=$order_id AND comment_approved like '1' AND comment_agent LIKE 'WooCommerce' AND comment_type LIKE 'order_note' ORDER BY comment_ID desc";
	$notes	 = $wpdb->get_results( $sql );
	if ( ! empty( $notes ) ) {
		foreach ( $notes as $note ) {
			if(strpos($note->comment_content,'Transaction ID:') !== false ){
				$temp			 = explode( 'Transaction ID:', $note->comment_content );
				$transaction_id	 = trim( $temp[ count( $temp ) - 1 ] );
			}else{
			$order_notes[] = str_replace( array( "\r", "\n" ), ' ', $note->comment_content );
			}
		}
		$order_data[ 'order_notes' ] = implode( '|', $order_notes );
	}
	$result	 = array_slice( $order_data, 0, 4, true ) +
	array( "transaction_id" => $transaction_id ) +
	array_slice( $order_data, 4, count( $order_data ) - 1, true );
	return $result;
}
