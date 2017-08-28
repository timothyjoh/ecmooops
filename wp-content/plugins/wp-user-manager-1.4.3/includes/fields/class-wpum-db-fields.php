<?php
/**
 * Fields DB class
 * This class is for interacting with the fields database table
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_DB_Field_Groups Class
 *
 * @since 1.0.0
 */
class WPUM_DB_Fields extends WPUM_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'wpum_fields';
		$this->primary_key = 'id';
		$this->version     = '1.0';

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function get_columns() {
		return array(
			'id'                      => '%d',
			'group_id'                => '%d',
			'type'                    => '%s',
			'name'                    => '%s',
			'description'             => '%s',
			'field_order'             => '%d',
			'is_required'             => '%s',
			'show_on_registration'    => '%s',
			'can_delete'              => '%s',
			'default_visibility'      => '%s',
			'allow_custom_visibility' => '%s',
			'meta'                    => '%s',
			'options'                 => '%s'
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function get_column_defaults() {
		return array(
			'id'                      => false,
			'group_id'                => false,
			'type'                    => '',
			'name'                    => '',
			'description'             => '',
			'field_order'             => false,
			'is_required'             => false,
			'show_on_registration'    => false,
			'can_delete'              => true,
			'default_visibility'      => 'public',
			'allow_custom_visibility' => 'disallowed',
			'meta'                    => '',
			'options'                 => false
		);
	}

	/**
	 * Add a field
	 *
	 * @see get_column_defaults for default parameters.
	 * @access  public
	 * @since   1.0.0
	*/
	public function add( $args = array() ) {

		$defaults = $this->get_column_defaults();

		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );

		// group_id is required
		if ( empty( $args['group_id'] ) || !is_numeric( $args['group_id'] ) ) {
			return false;
		}

		// Bail if no field name
		if ( empty( $args['name'] ) ) {
			return false;
		}

		// Todo: check for field type existance.

		return $this->insert( $args, 'field' );

	}

	/**
	 * Delete a field
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		if ( $id > 0 ) {

			if( ! $this->can_delete( $id ) ) {
				wp_die( 'You cannot delete this field.' );
			}

			global $wpdb;
			return $wpdb->delete( $this->table_name, array( 'id' => $id ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Checks if a field can be deleted
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function can_delete( $field_id = '' ) {
		return (bool) $this->get_column_by( 'can_delete', 'id', $field_id );
	}

	/**
	 * Checks if a field is visible into the registration form
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function show_on_registration( $field_meta = '' ) {
		return (bool) $this->get_column_by( 'show_on_registration', 'meta', $field_meta );
	}

	/**
	 * Retrieve the type of a field.
	 *
	 * @param  string $field_id the id number of the field.
	 * @return string           the field type.
	 * @since 1.2.0
	 */
	public function get_type( $field_id = '' ) {
		return $this->get_column_by( 'type', 'id', $field_id );
	}

	/**
	 * Get all fields of a group
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function get_by_group( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'id'             => '',
			'array'          => false,
			'registration'   => false,
			'number'         => -1,
			'offset'         => 0,
			'orderby'        => 'id',
			'order'          => 'DESC',
			'exclude_fields' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		if( empty( $args['id'] ) )
			return false;

		$where = '';

		// specific fields in a group
		if( ! empty( $args['id'] ) ) {

			if( is_array( $args['id'] ) ) {
				$ids = implode( ',', $args['id'] );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= "WHERE `group_id` IN( {$ids} ) ";

		}

		// If we have items to exclude, exclude them.
		if( ! empty( $args['exclude_fields'] ) ) {
			$exclude  = explode( ',', $args['exclude_fields'] );
			if( ! empty( $where ) && is_array( $exclude ) ) {
				foreach ( $exclude as $key => $value ) {
					$where .= " AND `ID` NOT IN( {$value} ) ";
				}
			}
		}

		// only registration fields ?
		if( $args['registration'] === true ) {
			if( ! empty( $where ) ) {
				$where .= " AND `show_on_registration` = 1";
			} else {
				$where .= "WHERE `show_on_registration` = 1";
			}
		}

		// Return as array?
		$return_type = 'OBJECT';
		if( $args['array'] === true )
			$return_type = 'ARRAY_A';

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'wpum_fields_by_group_' . serialize( $args ) );

		$fields = wp_cache_get( $cache_key, 'fields_by_group' );

		if( $fields === false ) {
			$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ), $return_type );
			wp_cache_set( $cache_key, $fields, 'fields_by_group', 3600 );
		}

		return $fields;

	}

	/**
	 * Get all fields.
	 *
	 * @param  array  $args settings for the query.
	 * @return mixed
	 */
	public function get_fields( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'         => 20,
			'offset'         => 0,
			'orderby'        => 'id',
			'order'          => 'DESC',
			'array'          => false,
		);

		$args  = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		// Return as array?
		$return_type = 'OBJECT';
		if( $args['array'] === true )
			$return_type = 'ARRAY_A';

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'wpum_fields_' . serialize( $args ) );

		$field_groups = wp_cache_get( $cache_key, 'fields' );

		if( $field_groups === false ) {
			$field_groups = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ), $return_type );
			wp_cache_set( $cache_key, $field_groups, 'fields', 3600 );
		}

		return $field_groups;

	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		group_id bigint(20) unsigned NOT NULL,
		type varchar(150) NOT NULL,
		name varchar(150) NOT NULL,
		description longtext NOT NULL,
		field_order bigint(20) NOT NULL DEFAULT '0',
		is_required tinyint(1) NOT NULL DEFAULT '0',
		show_on_registration tinyint(1) NOT NULL DEFAULT '0',
		can_delete tinyint(1) NOT NULL DEFAULT '1',
		default_visibility varchar(150) NOT NULL DEFAULT 'public',
		allow_custom_visibility varchar(150) NOT NULL DEFAULT 'disallowed',
		options longtext DEFAULT NULL,
		meta longtext DEFAULT NULL,
		PRIMARY KEY (id),
		KEY group_id (group_id),
		KEY field_order (field_order),
		KEY can_delete (can_delete),
		KEY is_required (is_required),
		KEY show_on_registration (show_on_registration)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
