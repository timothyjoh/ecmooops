<?php

/**
 * Creates the necessary database checks and upgrades,
 *   adding the 'propel_enrollments' table if necessary
 *
 * Assumes class is instantiated because upgrades are needed.
 *
 * Propel_DB instantiation is in Propel_LMS->check_db_upgrade()
 *
 * @author caseypatrickdriscoll
 *
 * @created 2015-01-07 12:00:00
 * @edited  2015-01-08 14:53:39
 * @edited  2015-01-16 10:18:20
 *
 *
 * VERSION HISTORY:
 * version 2: activation_key  added to enrollments table
 * version 3: completion_date added to enrollments table
 * version 4: passed bool     added to enrollments table
 * version 5: key_products_table CREATED: (ID, activation_key, product_id, order_id)
 */
class Propel_DB {

  const VERSION = 5;
  const enrollments_table = 'propel_enrollments';
  const key_products_table = 'propel_key_products';

  public function __construct() {
    //wp_die('upgrading DB');
    self::upgrade_db();
  }


  /**
   * Upgrades the database as necessary,
   *   sets the database version in settings
   *
   * @author caseypatrickdriscoll
   *
   * @created 2015-01-07 12:00:00
   * @edited  2015-01-16 09:57:30
   */
  private function upgrade_db() {
    
    $this->create_dbdelta_enrollments_table();

    $this->add_key_products_table();

    // Update version setting
    $propel_settings = get_option( 'propel_settings' );
    if ( isset( $propel_settings ) )
      $propel_settings['db_version'] = self::VERSION;
    else
      $propel_settings = array( 'db_version' => self::VERSION );

    update_option( 'propel_settings', $propel_settings );
  }


  /**
   * Creates the propel_enrollments table
   *
   * @author caseypatrickdriscoll
   *
   * @created 2015-01-07 12:00:00
   */
  private function create_dbdelta_enrollments_table() {
    global $wpdb;

    // $wpdb->hide_errors();

    // $collate declaration taken from WooCommerce
    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
      if ( ! empty( $wpdb->charset ) ) {
        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
      }
      if ( ! empty( $wpdb->collate ) ) {
        $collate .= " COLLATE $wpdb->collate";
      }
    }

    $create = "
      CREATE TABLE {$wpdb->prefix}" . Propel_DB::enrollments_table . "(
        ID BIGINT(20) NOT NULL AUTO_INCREMENT,
        activation_key VARCHAR(20) NOT NULL,
        post_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        activation_date DATETIME DEFAULT NULL,
        expiration_date DATETIME DEFAULT NULL,
        completion_date DATETIME DEFAULT NULL,
        passed TINYINT(1),
        UNIQUE KEY  ID (ID),
        KEY  post_id (post_id),
        KEY  user_id (user_id)
      ) $collate
    ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $result = dbDelta( $create ); 

    $result_activation_key = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}" . Propel_DB::enrollments_table . " LIKE 'activation_key'", OBJECT);
    if(empty($result_activation_key)) {
      $this->add_activation_key_to_enrollments_table();
    }


    $result_completion_date = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}" . Propel_DB::enrollments_table . " LIKE 'completion_date'", OBJECT);
    if(empty($result_completion_date)) {
      $this->add_completion_date_to_enrollments_table();
    }


    $result_passed = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}" . Propel_DB::enrollments_table . " LIKE 'passed'", OBJECT);
    if(empty($result_passed)) {
      $this->add_passed_to_enrollments_table();
    }

  }

  function add_completion_date_to_enrollments_table() {
    error_log("=== ADDING COMPLETION DATE TO ENTOLLMENTS");
    global $wpdb;
    $add_key = "
      ALTER TABLE {$wpdb->prefix}" . Propel_DB::enrollments_table . "
        ADD completion_date DATETIME
    ";

    $wpdb->query( $add_key );  
  }

  function add_passed_to_enrollments_table() {
    error_log("=== ADDING PASSED COLUMN TO ENTOLLMENTS");
    global $wpdb;
    $add_key = "
      ALTER TABLE {$wpdb->prefix}" . Propel_DB::enrollments_table . "
        ADD passed bool
    ";

    $wpdb->query( $add_key );  
  }

  /**
   * Adds 'activation_key' column varchar(20) to propel_enrollments table
   *
   * @author  caseypatrickdriscoll
   *
   * @created 2015-01-16 10:04:05
   *
   * @return  void
   */
  function add_activation_key_to_enrollments_table() {
    global $wpdb;

    $add_key = "
      ALTER TABLE {$wpdb->prefix}" . Propel_DB::enrollments_table . "
        ADD activation_key VARCHAR(20)
    ";

    $wpdb->query( $add_key );

  }

  /**
   * Builds the table that connnects keys to their products.
   * This is used to inform the OKM about the status of a key.
   *
   * @author scitent
   *
   * @created 2016-04-05
   *
   */
  private function add_key_products_table() {
    global $wpdb;
    // $collate declaration taken from WooCommerce
    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
      if ( ! empty( $wpdb->charset ) ) {
        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
      }
      if ( ! empty( $wpdb->collate ) ) {
        $collate .= " COLLATE $wpdb->collate";
      }
    }

    $create = "
      CREATE TABLE {$wpdb->prefix}" . Propel_DB::key_products_table . "(
        id bigint(20) NOT NULL AUTO_INCREMENT,
        activation_key varchar(20) NOT NULL,
        product_id int(11),
        order_id int(11),
        UNIQUE KEY  id (id)
      ) $collate
    ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $create );

  }
}
