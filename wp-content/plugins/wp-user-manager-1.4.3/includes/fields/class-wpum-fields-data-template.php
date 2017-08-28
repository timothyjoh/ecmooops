<?php
/**
 * This class is responsible for loading the profile, groups and data and displaying it.
 *
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Fields_Data_Template Class.
 * The profile fields loop class.
 *
 * @since 1.2.0
 */
class WPUM_Fields_Data_Template {

	/**
	 * The loop iterator.
	 *
	 * @since 1.2.0
	 * @var int
	 */
	public $current_group = -1;

	/**
	 * The number of groups returned by the query.
	 *
	 * @since 1.2.0
	 * @var int
	 */
	public $group_count;

	/**
	 * List of groups found by the query.
	 *
	 * @since 1.2.0
	 * @var array
	 */
	public $groups;

	/**
	 * The current group object being iterated on.
	 *
	 * @since 1.2.0
	 * @var object
	 */
	public $group;

	/**
	 * The current field.
	 *
	 * @since 1.2.0
	 * @var int
	 */
	public $current_field = -1;

	/**
	 * The field count.
	 *
	 * @since 1.2.0
	 * @var int
	 */
	public $field_count;

	/**
	 * Whether the field has data.
	 *
	 * @since 1.2.0
	 * @var bool
	 */
	public $field_has_data;

	/**
	 * The field.
	 *
	 * @since 1.2.0
	 * @var int
	 */
	public $field;

	/**
	 * Flag to check whether the loop is currently being iterated.
	 *
	 * @since 1.2.0
	 * @var bool
	 */
	public $in_the_loop;

	/**
	 * The user id.
	 *
	 * @since 1.2.0
	 * @var int
	 */
	public $user_id;

	/**
	 * Let's get things going.
	 *
	 * @since 1.2.0
	 * @param array $args arguments.
	 */
	public function __construct( $args = '' ) {

		$defaults = array(
			'user_id'           => false,
			'field_group_id'    => false,
			'number'            => false,
			'number_fields'     => false,
			'hide_empty_groups' => true,
			'exclude_groups'    => false,
			'exclude_fields'    => false,
			'orderby'           => 'id',
			'order'             => 'ASC',
			'array'             => true
		);

		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );

		$this->groups      = wpum_get_field_groups( $args );
		$this->group_count = count( $this->groups );
		$this->user_id     = $args['user_id'];

	}

	/**
	 * Whether there are groups available.
	 *
	 * @access public
	 * @return boolean
	 * @since 1.2.0
	 */
	public function has_groups() {

		if( ! empty( $this->group_count ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get next group within the loop.
	 *
	 * @access public
	 * @return array
	 * @since 1.2.0
	 */
	public function next_group() {

		$this->current_group++;

		$this->group = $this->groups[ $this->current_group ];
		$this->field_count = 0;

		$this->group = wpum_array_to_object( $this->group );

		if( ! empty( $this->group->fields ) ) {
			$this->group->fields = apply_filters( 'wpum_group_fields', $this->group->fields, $this->group->id );
			$this->field_count = count( $this->group->fields );
		}

		return $this->group;

	}

	/**
	 * Rewind groups.
	 *
	 * @access public
	 * @return void
	 * @since 1.2.0
	 */
	public function rewind_groups() {

		$this->current_group = -1;
		if( $this->group_count > 0 ) {
			$this->group = $this->groups[0];
		}

	}

	/**
	 * Check whether we've reached the end of the loop or keep looping.
	 *
	 * @access public
	 * @return bool
	 * @since 1.2.0
	 */
	public function profile_groups() {

		if( $this->current_group + 1 < $this->group_count ) {
			return true;
		} elseif ( $this->current_group + 1 == $this->group_count ) {
			do_action( 'wpum_field_groups_loop_end' );
			$this->rewind_groups();
		}

		$this->in_the_loop = false;
		return false;

	}

	/**
	 * Setup global variable for current group within the loop.
	 *
	 * @access public
	 * @global $wpum_fields_group
	 * @return void
	 * @since 1.2.0
	 */
	public function the_profile_group() {

		global $wpum_fields_group;

		$this->in_the_loop = true;
		$wpum_fields_group = $this->next_group();

		if( 0 === $this->current_group ) {
			do_action( 'wpum_field_groups_loop_start' );
		}

	}

	/**
	 * Verify whether the current group within the loop has fields.
	 *
	 * @access public
	 * @return boolean
	 * @since 1.2.0
	 */
	public function has_fields() {

		$has_data = false;

		for ( $i = 0, $count = count( $this->group->fields ); $i < $count; ++$i ) {
			$field = &$this->group->fields[ $i ];

			if ( ! empty( $field->value ) || ( '0' === $field->value ) ) {
				$has_data = true;
			}
		}

		return $has_data;

	}

	/**
	 * Proceed to next field within the loop.
	 *
	 * @access public
	 * @return object field details.
	 * @since 1.2.0
	 */
	public function next_field() {

		$this->current_field++;
		$this->field = $this->group->fields[ $this->current_field ];

		return $this->field;

	}

	/**
	 * Cleanup the fields loop once it ends.
	 *
	 * @access public
	 * @return void
	 * @since 1.2.0
	 */
	public function rewind_fields() {

		$this->current_field = -1;
		if( $this->field_count > 0 ) {
			$this->field = $this->group->fields[0];
		}

	}

	/**
	 * Verify the visibility of the field.
	 *
	 * @since 1.2.0
	 * @param  object  $field the current field to verify.
	 * @return boolean        true or false.
	 */
	public function is_visible( $field ) {

		$visible = true;

		// Verify if publicly visible.
		if( $field->default_visibility == 'hidden' ) {
			$visible = false;
		}

		return $visible;

	}

	/**
	 * Start the fields loop.
	 *
	 * @access public
	 * @return mixed
	 * @since 1.2.0
	 */
	public function profile_fields() {

		if ( $this->current_field + 1 < $this->field_count ) {
			return true;
		} elseif ( $this->current_field + 1 == $this->field_count ) {
			$this->rewind_fields();
		}

		return false;

	}

	/**
	 * Setup global variable for field within the loop.
	 *
	 * @access public
	 * @global $wpum_field
	 * @return void
	 * @since 1.2.0
	 */
	public function the_profile_field() {

		global $wpum_field;

		$wpum_field = $this->next_field();

		if ( ! empty( $wpum_field->value ) ) {
			$value = maybe_unserialize( $wpum_field->value );
		} else {
			$value = false;
		}

		if ( ! empty( $value ) || ( '0' === $value ) ) {

			$this->field_has_data = true;

			// Now verify if the field is visible or not.
			if( false === $this->is_visible( $wpum_field ) )
				$this->field_has_data = false;

		} else {
			$this->field_has_data = false;
		}

	}

}
