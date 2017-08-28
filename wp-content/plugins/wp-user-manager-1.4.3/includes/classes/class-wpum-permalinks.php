<?php
/**
 * WP User Manager Permalinks
 *
 * @package     wp-user-manager
 * @copyright   Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Permalinks Class
 *
 * @since 1.0.0
 */
class WPUM_Permalinks {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'profile_rewrite_rules' ) );

		// Execute only on admin panel.
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'add_new_permalink_settings' ) );
			add_action( 'wpum_save_permalink_structure', array( $this, 'save_structure' ) );
		}

	}

	/**
	 * Adds new rewrite rules to create pretty permalinks for users profiles.
	 *
	 * @access public
	 * @since 1.0.0
	 * @global object $wp
	 * @return void
	 */
	public function profile_rewrite_rules() {

		global $wp;

		// Define args for profile pages
		$wp->add_query_var( 'user' );
		$wp->add_query_var( 'tab' );

		$page_id   = wpum_get_core_page_id( 'profile' );
		$page_slug = esc_attr( get_post_field( 'post_name', intval( $page_id ) ) );

		add_rewrite_rule( $page_slug . '/([^/]*)/([^/]*)/page/([0-9]+)', 'index.php?page_id='. $page_id .'&user=$matches[1]&tab=$matches[2]&paged=$matches[3]', 'top' );
		add_rewrite_rule( $page_slug . '/([^/]*)/([^/]*)', 'index.php?page_id='. $page_id .'&user=$matches[1]&tab=$matches[2]', 'top' );
		add_rewrite_rule( $page_slug . '/([^/]*)', 'index.php?page_id='. $page_id .'&user=$matches[1]', 'top' );

		// Define args for account page
		$wp->add_query_var( 'account_tab' );
		$account_page_id   = wpum_get_core_page_id( 'account' );
		$account_page_slug = esc_attr( get_post_field( 'post_name', intval( $account_page_id ) ) );

		add_rewrite_rule( $account_page_slug . '/([^/]*)', 'index.php?page_id='. $account_page_id .'&account_tab=$matches[1]', 'top' );

	}

	/**
	 * Adds new settings section to the permalink options page.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function add_new_permalink_settings() {
		// Add a section to the permalinks page
		add_settings_section( 'wpum-permalink', __( 'User profiles permalink base', 'wpum' ), array( $this, 'display_settings' ), 'permalink' );
	}

	/**
	 * Displays the new settings section into the permalinks page.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function display_settings() {

		$structures = wpum_get_permalink_structures();
		$saved_structure = get_option( 'wpum_permalink', 'user_id' );

		ob_start();
		?>

		<?php if ( get_option( 'permalink_structure' ) == '' ) { ?>

		<p><?php printf( __( 'You must <a href="%s">change your permalinks</a> to anything else other than "default" for profiles to work.', 'wpum' ), admin_url( 'options-permalink.php' ) ) ?></p>

		<?php } else { ?>

			<p><?php _e( 'These settings control the permalinks used for users profiles. These settings only apply when <strong>not using "default" permalinks above</strong>.', 'wpum' ); ?></p>

			<table class="form-table">
				<tbody>
					<?php foreach ( $structures as $key => $settings ) : ?>
						<tr>
							<th>
								<label>
									<input name="user_permalink" type="radio" value="<?php echo $settings['name']; ?>" <?php checked( $settings['name'], $saved_structure ); ?> />
									<?php echo $settings['label']; ?>
								</label>
							</th>
							<td>
								<code>
									<?php echo wpum_get_core_page_url( 'profile' ); ?><?php echo $settings['sample']; ?>
								</code>
							</td>
						</tr>
					<?php endforeach; ?>
					<input type="hidden" name="wpum-action" value="save_permalink_structure"/>
				</tbody>
			</table>

		<?php } ?>

		<?php
		echo ob_get_clean();
	}

	/**
	 * Saves the permalink structure.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function save_structure() {

		// Check everything is correct.
		if ( ! is_admin() ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-permalink' ) ) {
			return;
		}

		// Bail if no cap
		if ( ! current_user_can( 'manage_options' ) ) {
			_doing_it_wrong( __FUNCTION__ , _x( 'You have no rights to access this page', '_doing_it_wrong error message', 'wpum' ), '1.0.0' );
			return;
		}

		// Check that the saved permalink method is one of the registered structures.
		if ( isset( $_POST['user_permalink'] ) && array_key_exists( $_POST['user_permalink'] , wpum_get_permalink_structures() ) ) {
			$user_permalink = sanitize_text_field( $_POST['user_permalink'] );
			update_option( 'wpum_permalink', $user_permalink );
		}

	}

}

return new WPUM_Permalinks;
