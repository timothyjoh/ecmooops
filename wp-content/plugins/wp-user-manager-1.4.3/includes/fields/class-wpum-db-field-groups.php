<?php
/**
 * Groups DB class
 * This class is for interacting with the fields groups database table
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
class WPUM_DB_Field_Groups extends WPUM_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'wpum_field_groups';
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
			'id'          => '%d',
			'name'        => '%s',
			'description' => '%s',
			'can_delete'  => '%s',
			'is_primary'  => '%s'
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
			'id'          => 0,
			'name'        => '',
			'description' => '',
			'can_delete'  => true,
			'is_primary'  => false
		);
	}

	/**
	 * Add a group
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function add( $args = array() ) {

		$defaults = array(
			'id'          => false,
			'name'        => false,
			'description' => '',
			'can_delete'  => true,
			'is_primary'  => false
		);

		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );

		// Bail if no group name
		if ( empty( $args['name'] ) ) {
			return false;
		}

		return $this->insert( $args, 'field_group' );

	}

	/**
	 * Delete a group
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		if ( $id > 0 ) {

			if( $this->is_primary( $id ) && ! $this->can_delete( $id ) ) {
				wp_die( 'You cannot delete this group.' );
			}

			global $wpdb;
			return $wpdb->delete( $this->table_name, array( 'id' => $id ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Get a list of groups
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  array list of groups
	*/
	public function get_groups( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'         => 20,
			'offset'         => 0,
			'orderby'        => 'id',
			'order'          => 'DESC',
			'exclude_groups' => false,
			'array'          => false,
		);

		$args  = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		// If we have groups to exclude, exclude them.
		if( ! empty( $args['exclude_groups'] ) ) {

			$exclude_groups = explode( ',', $args['exclude_groups'] );

			if( is_array( $exclude_groups ) ) {
				foreach ( $exclude_groups as $key => $group_id ) {
					if( $key == 0 ) {
						$where .= "WHERE `ID` NOT IN( {$group_id} )";
					} elseif( $key > 0 ) {
						$where .= " AND `ID` NOT IN( {$group_id} )";
					}
				}
			}

		}

		// Return as array?
		$return_type = 'OBJECT';
		if( $args['array'] === true )
			$return_type = 'ARRAY_A';

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'wpum_field_groups_' . serialize( $args ) );

		$field_groups = wp_cache_get( $cache_key, 'field_groups' );

		if( $field_groups === false ) {
			$field_groups = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ), $return_type );
			wp_cache_set( $cache_key, $field_groups, 'field_groups', 3600 );
		}

		return $field_groups;

	}

	/**
	 * Checks if is a primary group
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function is_primary( $group_id = '' ) {

		return (bool) $this->get_column_by( 'id', 'is_primary', $group_id );

	}

	/**
	 * Checks if a group can be deleted
	 *
	 * @access  public
	 * @since   1.0.0
	*/
	public function can_delete( $group_id = '' ) {

		return (bool) $this->get_column_by( 'id', 'can_delete', $group_id );

	}

	/**
	 * Retrieves a single group from the database
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  string $column id or primary.
	 * @param  mixed  $value  the id to search.
	 * @param  bool   $array  whether content returned should be an array.
	 * @return mixed
	 */
	public function get_group_by( $field = 'id', $value = 0, $array = false ) {
		global $wpdb;

		if ( empty( $field ) || $field == 'id' && empty( $value ) ) {
			return NULL;
		}

		if ( 'id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}

		}

		if( $field == 'primary' ) {
			$value = true;
		}

		// Return as array?
		$return_type = 'OBJECT';
		if( $array === true )
			$return_type = 'ARRAY_A';

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {
			case 'id':
				$db_field = 'id';
				break;
			case 'primary':
				$db_field = 'is_primary';
				break;
			default:
				return false;
		}

		if ( ! $group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ), $return_type ) ) {
			return false;
		}

		return $group;
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
		name varchar(150) NOT NULL,
		description mediumtext NOT NULL,
		group_order bigint(20) NOT NULL DEFAULT '0',
		can_delete tinyint(1) NOT NULL,
		is_primary bool NOT NULL DEFAULT '0',
		PRIMARY KEY (id),
		KEY can_delete (can_delete),
		KEY is_primary (is_primary)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}
