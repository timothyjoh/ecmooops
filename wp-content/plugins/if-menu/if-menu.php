<?php
/*
Plugin Name: If Menu
Plugin URI: https://wordpress.org/plugins/if-menu/
Description: Show/hide menu items with conditional statements
Version: 0.6.3
Text Domain: if-menu
Author: Layered
Author URI: https://layered.studio
License: GPL2
*/

/*  Copyright 2012 Andrei Igna (email: andrei@rokm.ro)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class If_Menu {

	public static function init() {
    global $pagenow;

    load_plugin_textdomain('if-menu', false, dirname(plugin_basename(__FILE__)) . '/languages');

		if (is_admin()) {
			add_action('admin_enqueue_scripts', 'If_Menu::admin_init');
			add_action('wp_update_nav_menu_item', 'If_Menu::wp_update_nav_menu_item', 10, 2);
			add_filter('wp_edit_nav_menu_walker', 'If_Menu::customWalker'); 
      add_action('wp_nav_menu_item_custom_fields', 'If_Menu::menu_item_fields');
      add_action('wp_nav_menu_item_custom_title', 'If_Menu::menu_item_title');

      if ($pagenow !== 'nav-menus.php') {
        add_filter( 'wp_get_nav_menu_items', 'If_Menu::wp_get_nav_menu_items' );
      }
		} else {
      add_filter( 'wp_get_nav_menu_items', 'If_Menu::wp_get_nav_menu_items' );
    }
	}

	public static function get_conditions( $for_testing = false ) {
		$conditions = apply_filters( 'if_menu_conditions', array() );

		if ($for_testing) {
			$c2 = array();
			foreach ($conditions as $condition) {
        $c2[$condition['id']] = $condition;
        $c2[$condition['name']] = $condition;
      }
			$conditions = $c2;
		}

		return $conditions;
	}

	public static function wp_get_nav_menu_items($items) {
		$conditions = If_Menu::get_conditions($for_testing = true);
		$hidden_items = array();

		foreach ($items as $key => $item) {
			if (in_array($item->menu_item_parent, $hidden_items)) {
				unset($items[$key]);
				$hidden_items[] = $item->ID;
			} else {
        $enabled = get_post_meta($item->ID, 'if_menu_enable');

        if ($enabled && $enabled[0] !== '0') {
          $if_condition_types = get_post_meta($item->ID, 'if_menu_condition_type');
          $if_conditions = get_post_meta($item->ID, 'if_menu_condition');

          $eval = array();

          foreach ($enabled as $index => $operator) {
            $singleCondition = '';

            if ($index) {
              $singleCondition .= $operator . ' ';
            }

            $bit1 = $if_condition_types[$index] === 'show' ? 1 : 0;
            $bit2 = $if_condition_types[$index] === 'show' ? 0 : 1;

            $singleCondition .= call_user_func($conditions[$if_conditions[$index]]['condition'], $item) ? $bit1 : $bit2;

            $eval[] = $singleCondition;
          }

          if ((count($eval) === 1 && $eval[0] == 0) || (count($eval) > 1 && !eval('return ' . implode(' ', $eval) . ';'))) {
            unset($items[$key]);
            $hidden_items[] = $item->ID;
          }
        }
			}
		}

		return $items;
	}

	public static function admin_init() {
		global $pagenow;

    if ($pagenow == 'nav-menus.php') {
      wp_enqueue_script('if-menu-js', plugins_url('assets/if-menu.js', __FILE__), array('jquery'));
      wp_enqueue_style('if-menu-css', plugins_url('assets/if-menu.css', __FILE__));

      wp_localize_script('if-menu-js', 'IfMenu', array(
        'conflictErrorMessage'  =>  sprintf(
          wp_kses(
            __('<strong>If Menu</strong> detected a conflict with another plugin or theme and may not work as expected. <a href="%s" target="_blank">Read more about the issue here</a>', 'if-menu'),
            array('a' => array('href' => array()), 'strong' => array())
          ),
          esc_url('https://wordpress.org/plugins/if-menu/faq/')
        )
      ));
    }
	}

  public static function menu_item_fields( $item_id ) {
    $conditions = If_Menu::get_conditions();
    $if_menu_enable = get_post_meta( $item_id, 'if_menu_enable' );
    $if_menu_condition_type = get_post_meta( $item_id, 'if_menu_condition_type' );
    $if_menu_condition = get_post_meta( $item_id, 'if_menu_condition' );

    if (!count($if_menu_enable)) {
      $if_menu_enable[] = 0;
      $if_menu_condition_type[] = '';
      $if_menu_condition[] = '';
    }

    $groupedConditions = array();
    foreach ($conditions as $condition) {
      $groupedConditions[isset($condition['group']) ? $condition['group'] : 'Custom'][] = $condition;
    }
    ?>

    <p class="if-menu-enable description description-wide">
      <label>
        <input <?php if (isset($if_menu_enable[0])) checked( $if_menu_enable[0], 1 ) ?> type="checkbox" value="1" class="menu-item-if-menu-enable" name="menu-item-if-menu-enable[<?php echo esc_attr( $item_id ); ?>][]" />
        <?php esc_html_e( 'Change menu item visibility', 'if-menu' ) ?>
      </label>
    </p>

    <div class="if-menu-conditions" style="display: <?php echo $if_menu_enable[0] ? 'block' : 'none' ?>">
      <?php for ($index = 0; $index < count($if_menu_enable); $index++) : ?>
        <p class="if-menu-condition description description-wide">
          <select class="menu-item-if-menu-condition-type" id="edit-menu-item-if-menu-condition-type-<?php echo esc_attr( $item_id ); ?>" name="menu-item-if-menu-condition-type[<?php echo esc_html( $item_id ); ?>][]">
            <option <?php selected( 'show', $if_menu_condition_type[$index] ) ?> value="show"><?php esc_html_e( 'Show', 'if-menu' ) ?></option>
            <option <?php selected( 'hide', $if_menu_condition_type[$index] ) ?> value="hide"><?php esc_html_e( 'Hide', 'if-menu' ) ?></option>
          </select>
          <?php esc_html_e( 'if', 'if-menu' ); ?>
          <select class="menu-item-if-menu-condition" id="edit-menu-item-if-menu-condition-<?php echo esc_attr( $item_id ); ?>" name="menu-item-if-menu-condition[<?php echo esc_attr( $item_id ); ?>][]">
            <?php foreach ($groupedConditions as $group => $conditions) : ?>
              <optgroup label="<?php echo esc_attr( $group ) ?>">
                <?php foreach( $conditions as $condition ): ?>
                  <option value="<?php echo $condition['id'] ?>" <?php selected( $condition['id'], $if_menu_condition[$index] ) ?> <?php selected( $condition['name'], $if_menu_condition[$index] ) ?>><?php echo esc_html( $condition['name'] ); ?></option>
                <?php endforeach ?>
              </optgroup>
            <?php endforeach ?>
          </select>
          <select class="menu-item-if-menu-enable-next" name="menu-item-if-menu-enable[<?php echo esc_attr( $item_id ); ?>][]">
            <option value="false">+</option>
            <option value="and" <?php if (isset($if_menu_enable[$index + 1])) selected( 'and', $if_menu_enable[$index + 1] ) ?>><?php esc_html_e( 'AND', 'if-menu' ) ?></option>
            <option value="or" <?php if (isset($if_menu_enable[$index + 1])) selected( 'or', $if_menu_enable[$index + 1] ) ?>><?php esc_html_e( 'OR', 'if-menu' ) ?></option>-->
          </select>
        </p>
      <?php endfor ?>
    </div>

    <?php
  }

  public static function menu_item_title( $item_id ) {
    $if_menu_enabled = get_post_meta( $item_id, 'if_menu_enable' );

    if ( count( $if_menu_enabled ) && $if_menu_enabled[0] !== '0' ) {
      $conditionTypes = get_post_meta( $item_id, 'if_menu_condition_type' );
      $conditions = get_post_meta( $item_id, 'if_menu_condition' );
      $rules = If_Menu::get_conditions($for_testing = true);

      if ( $conditionTypes[0] === 'show' ) {
        $conditionTypes[0] = '';
      }

      echo '<span class="is-submenu">';
      printf( __( '%s if %s', 'if-menu' ), $conditionTypes[0], $rules[$conditions[0]]['name'] );
      if ( count( $if_menu_enabled ) > 1 ) {
        printf( ' ' . _n( 'and 1 more rule', 'and %d more rules', count( $if_menu_enabled ) - 1, 'if-menu' ), count( $if_menu_enabled ) - 1 );
      }
      echo '</span>';
    }
  }

  public static function customWalker() {
    global $wp_version;

    if (version_compare( $wp_version, '4.7.0', '>=')) {
      require_once(plugin_dir_path(__FILE__) . 'if-menu-nav-menu-4.7.php');
    } elseif ( version_compare( $wp_version, '4.5.0', '>=' ) ){
      require_once(plugin_dir_path(__FILE__) . 'if-menu-nav-menu-4.5.php');
    } else {
      require_once(plugin_dir_path(__FILE__) . 'if-menu-nav-menu.php');
    }

    return 'If_Menu_Walker_Nav_Menu_Edit';
  }

	public static function wp_update_nav_menu_item( $menu_id, $menu_item_db_id ) {
    if (isset($_POST['menu-item-if-menu-enable'])) {

      delete_post_meta( $menu_item_db_id, 'if_menu_enable' );
      delete_post_meta( $menu_item_db_id, 'if_menu_condition_type' );
      delete_post_meta( $menu_item_db_id, 'if_menu_condition' );

      foreach ( $_POST['menu-item-if-menu-enable'][$menu_item_db_id] as $index => $value ) {
        if ( in_array( $value, array('1', 'and', 'or') ) ) {
          add_post_meta( $menu_item_db_id, 'if_menu_enable', $value );
          add_post_meta( $menu_item_db_id, 'if_menu_condition_type', $_POST['menu-item-if-menu-condition-type'][$menu_item_db_id][$index] );
          add_post_meta( $menu_item_db_id, 'if_menu_condition', $_POST['menu-item-if-menu-condition'][$menu_item_db_id][$index] );
        } else {
          break;
        }
      }
    }
  }

}



/* ------------------------------------------------
	Include default conditions for menu items
------------------------------------------------ */

include 'conditions.php';



/* ------------------------------------------------
	Run the plugin
------------------------------------------------ */

add_action('plugins_loaded', 'If_Menu::init');
