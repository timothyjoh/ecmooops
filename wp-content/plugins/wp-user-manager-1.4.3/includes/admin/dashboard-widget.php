<?php
/**
 * User overview widget for admin dashboard.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the dashboard widgets.
 *
 * @since 1.1.0
 * @return void
 */
function wpum_register_dashboard_widgets() {

  if( current_user_can( apply_filters( 'wpum_stats_cap', 'manage_options' ) ) ) {
    wp_add_dashboard_widget( 'wpum_dashboard_users', __( 'WP User Manager Overview', 'wpum' ), 'wpum_dashboard_users_overview' );
  }

}
add_action( 'wp_dashboard_setup', 'wpum_register_dashboard_widgets', 10 );

/**
 * Build and render the users overview widget.
 *
 * @since 1.1.0
 * @return void
 */
function wpum_dashboard_users_overview() {
  echo '<div class="spinner is-active wpum-admin-widget"></div>';
}

/**
 * Loads the dashboard widget via ajax
 *
 * @since 1.1.0
 * @return void
 */
function wpum_load_dashboard_users_overview() {

  if( ! current_user_can( apply_filters( 'wpum_stats_cap', 'manage_options' ) ) ) {
    die();
  }

  // Todays Registrations.
  $today_date = new DateTime();
  $today_date = $today_date->format('Y-m-d');

  $args = array(
    'date_query' => array(
        array( 'after' => $today_date, 'inclusive' => true )
    )
  );
  $registered_today = new WP_User_Query( $args );

  // This week Registrations
  $query_this_week = array(
    'date_query' => array(
      'week' => date('W')
    )
  );
  $registered_this_week = new WP_User_Query( $query_this_week );

  // This month registrations
  $query_this_month = array(
    'date_query' => array(
      'month' => date('m')
    )
  );
  $registered_this_month = new WP_User_Query( $query_this_month );

  // Last month registrations
  $query_last_month = array(
    'date_query' => array(
      'month' => date( 'm' , strtotime( 'first day of previous month' ) )
    )
  );
  $registered_last_month = new WP_User_Query( $query_last_month );

  // This year registrations
  $query_this_year = array(
    'date_query' => array(
      'year' => date('Y')
    )
  );
  $registered_this_year = new WP_User_Query( $query_this_year );

  $users = esc_html_x( 'Users', 'Used within the dashboard widget', 'wpum' );

  ?>

  <div class="wpum_dashboard_widget">

    <?php do_action( 'wpum_dashboard_widget_top' ); ?>

    <ul class="wpum_status_list">

      <li class="fullwidth users-today">
        <?php echo sprintf( esc_html__( '%s Registered today', 'wpum' ), '<strong><span class="amount">' . $registered_today->get_total() . ' ' . $users . '</span></strong>' ); ?>
      </li>

      <li class="users-this-week spacer">
        <?php echo sprintf( esc_html__( '%s Registered this week', 'wpum' ), '<strong><span class="amount">'.$registered_this_week->get_total() . ' ' . $users . '</span></strong>' ); ?>
      </li>
      <li class="users-this-month">
        <?php echo sprintf( esc_html__( '%s Registered this month', 'wpum' ), '<strong><span class="amount">'.$registered_this_month->get_total() . ' ' . $users . '</span></strong>' ); ?>
      </li>
      <li class="users-last-month spacer">
        <?php echo sprintf( esc_html__( '%s Registered last month', 'wpum' ), '<strong><span class="amount">'.$registered_last_month->get_total() . ' ' . $users . '</span></strong>' ); ?>
      </li>
      <li class="users-this-year">
        <?php echo sprintf( esc_html__( '%s Registered this year', 'wpum' ), '<strong><span class="amount">'.$registered_this_year->get_total() . ' ' . $users . '</span></strong>' ); ?>
      </li>

    </ul>

    <?php do_action( 'wpum_dashboard_widget_bottom' ); ?>

  </div>

  <?php
  die();

}
add_action( 'wp_ajax_wpum_load_dashboard_users_overview', 'wpum_load_dashboard_users_overview' );
