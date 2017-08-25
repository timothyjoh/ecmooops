<?php
/**
 * Plugin Name: Gravity Forms Multi-Column
 * Description: Adds basic multi-column support to Gravity Forms
 * Version: 1.1.0
 * Author: Jordan Crown
 * Author URI: http://www.jordancrown.com
 * License: GNU General Pulic License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


if(!class_exists('GF_Multi_Column')) {
	class GF_Multi_Column {

		public static $version = '1.0.0';


		public static function init() {
			add_action('init', array('GF_Multi_Column', 'register_gf_field_column'), 20);
			add_action('gform_field_standard_settings', array('GF_Multi_Column', 'add_gf_field_column_settings'), 10, 2);
			add_filter('gform_field_container', array('GF_Multi_Column', 'filter_gf_field_column_container'), 10, 6);
			add_filter('gform_pre_render', array('GF_Multi_Column', 'filter_gf_multi_column_pre_render'), 10, 3);
			add_action('gform_enqueue_scripts', array('GF_Multi_Column', 'enqueue_gf_multi_column_scripts'), 10, 2);
		}


		public static function register_gf_field_column() {
			if(!class_exists('GFForms') || !class_exists('GF_Field_Column')) return;
			GF_Fields::register(new GF_Field_Column());
		}


		public static function add_gf_field_column_settings($placement, $form_id) {
			if($placement == 0) {
				$description = 'Column breaks should be placed between fields to split form into separate columns. You do not need to place any column breaks at the beginning or end of the form, only in the middle.';
				echo '<li class="column_description field_setting">'.$description.'</li>';
			}
		}


		public static function filter_gf_field_column_container($field_container, $field, $form, $css_class, $style, $field_content) {
			if(IS_ADMIN) return $field_container;
			if($field['type'] == 'column') {
				$column_index = 2;
				foreach($form['fields'] as $form_field) {
					if($form_field['id'] == $field['id']) break;
					if($form_field['type'] == 'column') $column_index++;
				}
				return '</ul><ul class="'.GFCommon::get_ul_classes($form).' column column_'.$column_index.' '.$field['cssClass'].'">';
			}
			return $field_container;
		}


		public static function filter_gf_multi_column_pre_render($form, $ajax, $field_values) {
			$column_count = 0;
			$prev_page_field = null;
			foreach($form['fields'] as $field) {
				if($field['type'] == 'column') {
					$column_count++;
				} else if($field['type'] == 'page') {
					if($column_count > 0 && empty($prev_page_field)) {
						$form['firstPageCssClass'] = trim((isset($field['firstPageCssClass']) ? $field['firstPageCssClass'] : '').' gform_page_multi_column gform_page_column_count_'.($column_count + 1));
					} else if($column_count > 0) {
						$prev_page_field['cssClass'] = trim((isset($prev_page_field['cssClass']) ? $prev_page_field['cssClass'] : '').' gform_page_multi_column gform_page_column_count_'.($column_count + 1));
					}
					$prev_page_field = $field;
					$column_count = 0;
				}
			}
			if($column_count > 0 && empty($prev_page_field)) {
				$form['cssClass'] = trim((isset($form['cssClass']) ? $form['cssClass'] : '').' gform_multi_column gform_column_count_'.($column_count + 1));
			} else if($column_count > 0) {
				$prev_page_field['cssClass'] = trim((isset($prev_page_field['cssClass']) ? $prev_page_field['cssClass'] : '').' gform_page_multi_column gform_page_column_count_'.($column_count + 1));
			}
			return $form;
		}


		public static function enqueue_gf_multi_column_scripts($form, $ajax) {
			if(!get_option('rg_gforms_disable_css')) {
				wp_enqueue_style('gforms_multi_column_css', plugins_url('gravityforms-multi-column.css', __FILE__), null, self::$version);
			}
		}


	}
	GF_Multi_Column::init();
}


if(!class_exists('GF_Field_Column') && class_exists('GF_Field')) {
	class GF_Field_Column extends GF_Field {

		public $type = 'column';

		public function get_form_editor_field_title() {
			return esc_attr__('Column Break', 'gravityforms');
		}

		public function is_conditional_logic_supported() {
			return false;
		}

		function get_form_editor_field_settings() {
			return array(
				'column_description',
				'css_class_setting'
			);
		}

		public function get_field_input($form, $value = '', $entry = null) {
			return '';
		}

		public function get_field_content($value, $force_frontend_label, $form) {

			$is_entry_detail = $this->is_entry_detail();
			$is_form_editor = $this->is_form_editor();
			$is_admin = $is_entry_detail || $is_form_editor;

			if($is_admin) {
				$admin_buttons = $this->get_admin_buttons();
				return $admin_buttons.'<label class=\'gfield_label\'>'.$this->get_form_editor_field_title().'</label>{FIELD}<hr>';
			}

			return '';
		}

	}
}