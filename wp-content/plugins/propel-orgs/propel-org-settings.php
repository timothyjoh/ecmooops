<?php

class Propel_Org_Settings {

	private $settings;

	function __construct() {

		add_action( 'admin_menu', array( $this, 'register_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action( 'wp_ajax_import_propel_orgs', array( $this, 'ajax_import_propel_orgs' ) );

		add_action( 'admin_init', array( $this, 'register_settings' ) );



	}


	/**
	 * Loads the script
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @return void
	 */
	function load_scripts( $hook ) {

		if ( $hook != 'propel_org_page_import-propel-orgs' ) return;

		wp_enqueue_script( 'propel-orgs-scripts', plugins_url( '/js/import.js', __FILE__ ), array( 'jquery' ) );
	}


	/**
	 * Registers the 'Propel Orgs' menu
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @return void
	 */
	function register_menu() {

		add_submenu_page(
			'edit.php?post_type=propel_org',
			'PROPEL Orgs Settings',
			'Settings',
			'edit_others_posts',
			'propel-orgs-settings',
			array( $this, 'render' )
		);

	}


	/**
	 * Renders the PROPEL Settings page
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 10:01:40
	 *
	 * @action add_settings_menu
	 */
	function render() {
		wp_enqueue_script( 'propel_groups_settings' );

		$current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings';

		$tabs = array(
			'settings' => 'Settings',
			'import'   => 'Import'
		);

		echo '<div class="wrap">';

		echo '<h2>PROPEL Orgs Settings</h2>';

		echo '<h2 class="nav-tab-wrapper">';

		foreach( $tabs as $tab => $name ) {

			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?post_type=propel_org&page=propel-orgs-settings&tab=$tab'>$name</a>";

		}

		echo '</h2>';

		echo '<div class="wrap">';

		?>

		<?php

		switch ( $current ) {

			case 'settings':
				self::render_settings_tab();
				break;

			case 'import':
				self::render_import_tab();
				break;

		}

		?>
			</div>
    </div>
    <?php

	}


	function register_settings() {
		register_setting( 'propel-orgs', 'propel-orgs' );
	}


	static function render_settings_tab() {

		// Rank the order of the org_type

		echo '<form method="post" action="options.php">';

		settings_fields( 'propel-orgs' );
		$settings = get_option( 'propel-orgs' );


		$org_types = get_terms( array( 'org_type' ) );

		$select = '<select name="propel-orgs[org_type_priority]">';

		foreach ( $org_types as $org_type ) {

			$selected = $settings['org_type_priority'] == $org_type->term_id ? 'selected' : '';

			$select .= '<option value="' . $org_type->term_id . '" ' . $selected . '>' . $org_type->name . '</option>';

		}

		$select .= '</select>';

		?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">Org Type Priority</th>
				<td><?php echo $select; ?></td>
			</tr>
		</table>

		<?php

		submit_button();

		echo '</form>';

	}


	static function render_import_tab() { ?>
		<h3>Import Organizations</h3>

		<p>Assumes an 'orgs.csv' file in the 'propel-organizations' plugin folder.</p>

		<p>Values should be listed as the example below:</p>

		<pre>[tag_id, parent_tag_id, tag_name, tag_value, sort, tag_other, createdate]</pre>
		<pre>1,0,League,Jefferson Swim League,1,1,2010-01-14 15:15:00.000</pre>


		<a id="import-orgs" class="button button-primary" style="float:left;">Import Orgs</a>
		<span class="spinner"></span><span class="message"></span>

		<style>
			.message {
				margin: 10px 0 0 10px;
			}
			.spinner {
				background: url('/wp-admin/images/spinner.gif') no-repeat;
				background-size: 16px 16px;
				display: none;
				float: left;
				opacity: .7;
				filter: alpha(opacity=70);
				width: 16px;
				height: 16px;
				margin: 5px 5px 0;
				}
		</style>
	<?php


	}


	function ajax_import_propel_orgs() {
		$path = plugin_dir_path( __FILE__ ) . 'orgs.csv';

		$file = fopen( $path, 'r' );

		ini_set( 'auto_detect_line_endings', TRUE );

		// tag_id, parent_tag_id, tag_name, tag_value, sort, tag_other, createdate


		$lines = Array();

		while ( ( $line = fgetcsv( $file, 1000, "," ) ) !== FALSE ) {

			$num = $line[0];

			$lines[$num] = $line;

		}

		$created = 0;
		$duplicates = 0;

		foreach ( $lines as $line ) {

			$org = array(
				'post_title' => $line[3],
				'post_status' => 'publish',
				'post_type' => 'propel_org',
			);

			$exists = get_page_by_title( $line[3], OBJECT, 'propel_org' );

			if ( ! empty( $exists ) ) {
				$duplicates++;
				continue;
			}

			if ( $line[1] > 0 ) {
				$parent = $lines[$line[1]][3];

				$parent = get_page_by_title( $parent, OBJECT, 'propel_org' );

				$org['post_parent'] = $parent->ID;

			}


			$org = wp_insert_post( $org );

			$created++;

			$type = get_term_by( 'name', $line[2], 'org_type' );

			wp_set_object_terms( $org, (int)$type->term_id, 'org_type' );

		}


		wp_send_json_success( array( count( $lines ), $created, $duplicates ) );

	}

}

new Propel_Org_Settings();