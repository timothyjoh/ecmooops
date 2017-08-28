<?php
/**
 * Custom Fields Editor list table.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Groups_Fields
 * Create a table with the list of fields.
 *
 * @since 1.0.0
 */
class WPUM_Groups_Fields extends WP_List_Table {

	/**
     * Prepare the items for the table to process
     *
     * @since 1.0.0
     * @return Void
     */
    public function prepare_items() {

        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;

    }

    /**
     * Message to be displayed when there are no items
     *
     * @since 3.1.0
     * @access public
     */
    public function no_items() {
        esc_html_e( 'No fields have been found.', 'wpum' );
    }

    /**
     * Override the parent columns method. Defines the columns to use in the listing table
     *
     * @since 1.0.0
     * @return Array
     */
    public function get_columns() {

        $columns = array(
            'order'    => esc_html__('Order', 'wpum'),
            'title'    => esc_html__('Field Title', 'wpum'),
            'type'     => esc_html__('Field Type', 'wpum'),
            'required' => esc_html__('Required', 'wpum'),
            'actions'  => esc_html__('Actions', 'wpum'),
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @since 1.0.0
     * @return Array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @since 1.0.0
     * @return Array
     */
    public function get_sortable_columns() {
        return null;
    }

    /**
     * Get the table data
     *
     * @since 1.0.0
     * @return Array
     */
    private function table_data() {

        $which_group = null;

        // Detect if a group is selected -
        // if not get the primary group ID.
        if( isset( $_GET['group'] ) ) {
            $which_group = (int) $_GET['group'];
        } else {
            $primary_group = WPUM()->field_groups->get_group_by('primary');
            $which_group = $primary_group->id;
        }

        $data = WPUM()->fields->get_by_group( array( 'id' => $which_group, 'array' => true, 'orderby' => 'field_order', 'order' => 'ASC' ) );

        return $data;

    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {

        switch( $column_name ) {
            case 'order':
                return '<a href="#"><span class="dashicons dashicons-menu"></span></a>';
            break;
            case 'title':
                return esc_html( stripslashes( $item['name'] ) );
            break;
            case 'type':
                return $this->parse_type( $item['type'] );
            break;
            case 'required':
                return $this->parse_required( $item['is_required'] );
            break;
            case 'actions':
                return $this->get_actions( $item );
            break;
            default:
                return null;
        }

    }

    /**
     * Generate the table navigation above or below the table
     *
     * Overwriting this method allows to correctly save the options page
     * because this method adds new nonce fields too.
     *
     * @since 1.0.0
     * @access protected
     * @param string $which
     */
    protected function display_tablenav( $which ) {
        return null;
    }

    /**
     * Get a list of CSS classes for the list table table tag.
     *
     * @access protected
     * @return array List of CSS classes for the table tag.
     */
    protected function get_table_classes() {
        return array( 'widefat', 'fixed', $this->_args['plural'] );
    }

    /**
     * Displays a translatable string for the field type column.
     *
     * @access public
     * @return string the field type name.
     */
    public function parse_type( $type ) {

        $text = esc_html__( 'Text', 'wpum' );

        switch ( $type ) {
            case 'select':
                $text = esc_html__( 'Dropdown', 'wpum' );
                break;
            case 'display_name':
                $text = esc_html__( 'Dropdown', 'wpum' );
                break;
            case 'file':
                $text = esc_html__( 'Upload', 'wpum' );
                break;
            case 'avatar':
                $text = esc_html__( 'Upload', 'wpum' );
                break;
            case 'username':
                $text = esc_html__( 'Text', 'wpum' );
                break;
            case 'nickname':
                $text = esc_html__( 'Text', 'wpum' );
                break;
            default:
                $object = wpum_get_field_type_object( $type );
                $text   = $object->name;
                break;
        }

        return ucfirst( apply_filters( 'wpum_fields_editor_types', $text ) );

    }

    /**
     * Displays an icon for the required column
     *
     * @access public
     * @return string whether it's required or not.
     */
    public function parse_required( $is_required = false ) {

        $show_icon = '';

        if( $is_required == true ) {
            $show_icon = '<span class="dashicons dashicons-yes"></span>';
        }

        return $show_icon;

    }

    /**
     * Display action buttons for the fields.
     *
     * @param   array $item
     * @return  Mixed
     */
    private function get_actions( $item ) {

        $edit_url = add_query_arg( array( 'action' => 'edit_field', 'field' => sanitize_key( $item['id'] ), 'from_group' => sanitize_key( $item['group_id'] ) ), admin_url( 'users.php?page=wpum-edit-field' ) );
        echo '<a href="'.esc_url( $edit_url ).'" class="button">'.__( 'Edit', 'wpum' ).'</a> ';

        // Display delete button if field can be deleted.
        if( $item['can_delete'] ) {
            $current_group = ( isset( $_GET['group'] ) && is_numeric( $_GET['group'] ) ) ? $_GET['group'] : false;
            $delete_url = wp_nonce_url( add_query_arg( array( 'action' => 'delete_field', 'field' => sanitize_key( $item['id'] ), 'group' => $current_group ), admin_url( 'users.php?page=wpum-profile-fields' ) ), "delete_field_{$item['id']}" );
            echo '<a href="'.esc_url( $delete_url ).'" class="button wpum-confirm-dialog">'.__( 'Delete', 'wpum' ).'</a> ';
        }

    }

    /**
     * Generates content for a single row of the table
     *
     * @access public
     * @param object $item The current item
     */
    public function single_row( $item ) {
        static $row_class = '';
        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        // Add id
        $row_id = ' id="'.$item['name'].'"';

        echo '<tr' . $row_class . $row_id . ' data-priority="' .$item['field_order']. '" data-field-id="' . sanitize_key( $item['id'] ). '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

}
