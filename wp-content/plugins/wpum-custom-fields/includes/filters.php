<?php
/**
 * Handles filters to work with the fields input/output
 *
 * @package     wpum-custom-fields
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Display a message within the plugin's name telling the user to add the license.
 *
 * @since 1.0.0
 * @param  string $plugin_name name of the plugin
 * @return void
 */
function wpumcf_license_message( $plugin_name ) {

  if( get_option( 'wpum_custom_fields_license_active' ) !== 'valid' ) {

  $register_link = sprintf( '<a href="%s" target="_blank">' . esc_html_x( 'Register', 'used within the license activation message', 'wpum-custom-fields' ) . '</a>' , '#' );
  $purchase_link = sprintf( '<a href="%s" target="_blank">' . esc_html__( 'Purchase one now.', 'wpum-custom-fields' ) . '</a>', '#' );

  $message = $register_link . ' ' . sprintf( esc_html__( 'your copy of the Custom Fields addon to receive access to automatic upgrades and support. Need a license key?', 'wpum-custom-fields' ) ) . ' ' . $purchase_link ;

  echo '</tr><tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">'.$message.'</div></td>';

  }

}
add_action( 'after_plugin_row_'.WPUMCF_SLUG, 'wpumcf_license_message' );

/**
 * Renders the list of files uploaded by the user.
 *
 * @since 1.1.0
 * @param  object $field             The current CMB2_Field object.
 * @param  string $escaped_value     The value of this field passed through the escaping filter.
 * @param  string $object_id         The id of the object you are working with. Most commonly, the post id.
 * @param  string $object_type       The type of object you are working with.
 * @param  object $field_type_object This is an instance of the CMB2_Types object and gives you access to all of the methods that CMB2 uses to build its field types.
 * @return void
 */
function wpumcf_cmb2_render_userfiles( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

  $files  = maybe_unserialize( $field->value );
  $output = '';

  if( ! is_array( $files ) || empty( $files ) ) {
    return esc_html_e( 'No files uploaded.', 'wpum-custom-fields' );
  }

  if( wpum_is_multi_array( $files ) ) {

    foreach ( $files as $key => $file ) {

      if( array_key_exists( 'url' , $file ) ) {

        $output .= '<ul>';
        $output .= '<li><a href="' . esc_url( $file['url'] ) . '" target="_blank">' . esc_html( basename( $file['url'] ) ) . '</a></li>';
        $output .= '</ul>';

      }

    }

  } else {

    if( array_key_exists( 'url' , $files ) ) {

      $output .= '<ul>';
      $output .= '<li><a href="' . esc_url( $files['url'] ) . '" target="_blank">' . esc_html( basename( $files['url'] ) ) . '</a></li>';
      $output .= '</ul>';

    }

  }

  echo $output;

}
add_action( 'cmb2_render_userfiles', 'wpumcf_cmb2_render_userfiles', 10, 5 );

/**
 * Prevent meta update when a profile is updated from the backend.
 * This will stop "file field types" to delete their meta.
 *
 * @since 1.1.1
 * @param  bool $a    Array of data about current field.
 * @param  array $args Field arguments.
 * @param  object $field Field object.
 * @return mixed
 */
function wpumcf_cmb2_prevent_file_deletion( $a, $args, $field ) {

  if( $field['type'] == 'userfiles' ) {
    return 'skip';
  }

  return null;

}
add_filter( 'cmb2_override_meta_remove', 'wpumcf_cmb2_prevent_file_deletion', 10, 3 );

/**
 * Adjusts the serialization output of user fields in the backend.
 */
function wpumcf_cmb2_adjust_meta_output( $value, $object_id, $a, $field ) {

  if( $field->object_type == 'user' ) {
    if( $field->args['type'] == 'multicheck' || $field->args['type'] == 'multicheck_inline' ) {

      $original_value = get_user_meta( $object_id, $field->args['id'], true );
      $value          = maybe_unserialize( $original_value );

    }
  }

  return $value;

}
add_filter( 'cmb2_override_meta_value', 'wpumcf_cmb2_adjust_meta_output', 10, 4 );
