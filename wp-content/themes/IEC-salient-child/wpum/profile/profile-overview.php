<?php
/**
 * WPUM Template: "Overview" profile tab.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
?>

<div class="wpum-user-details-list">

	<?php do_action( 'wpum_before_user_details_list', $user_data, $tabs, $slug ); ?>

	<!-- No fields loop -->

	<?php do_action( 'wpum_after_user_details_list', $user_data, $tabs, $slug ); ?>

</div>
