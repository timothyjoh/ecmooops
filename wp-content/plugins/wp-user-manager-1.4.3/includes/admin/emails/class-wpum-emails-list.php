<?php
/**
 * Emails List
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Emails_List
 * Create a table with the list of emails the plugin can send.
 * 
 * @since 1.0.0
 */
class WPUM_Emails_List extends WP_List_Table {

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
     * Override the parent columns method. Defines the columns to use in the listing table
     *
     * @since 1.0.0
     * @return Array
     */
    public function get_columns() {
        
        $columns = array(
            'title' => __('Email Title', 'wpum'),
            'description' => __('Email Description', 'wpum'),
            'actions' => __('Actions', 'wpum'),
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

        $data = wpum_get_emails();

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
            case 'id':
                return $item['id'];
            break;
            case 'title':
                return $item['title'];
            break;
            case 'description':
                return $item['description'];
            break;
            case 'actions':
                return $this->table_actions($item);
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
     * Displays edit button for the email.
     *
     * @param   array $item - The email item being passed
     * @return  Mixed
     */
    private function table_actions( $item ) {

        $edit_url = add_query_arg( 
            array(
                'email-id' => $item['id'],
                'email-title' => urlencode($item['title']),
                'wpum_action' => 'edit'
            ), 
            admin_url( 'users.php?page=wpum-edit-email' )
        );
        
        echo '<a href="'.esc_url( $edit_url ).'" class="button">'.__('Edit Email', 'wpum').'</a> ';

    }

}