<?php
if (!defined('ABSPATH') && !$_GET["ajax"]) {
    exit; // Exit if accessed directly
}
/*
  Plugin Name: UserEngage Plugin
  Description: UserEngage Plugin for Wordpress.
  Version: 1.3.4.2
  Author: UserEngage
  Author URI: https://userengage.com/en-us/
  License: GPLv2 or later
  Text Domain: userengage
 */

/*  Copyright 2015-2017 UserEngage

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

function UserEngageScript_admin_style()
{
    wp_register_style('UserEngageScript_wp_admin_css', plugin_dir_url(__FILE__) . 'assets/css/style.css', false, '1.0.0');
    wp_enqueue_style('UserEngageScript_wp_admin_css');
}

add_action('admin_enqueue_scripts', 'UserEngageScript_admin_style');

function UserEngageScript_widget($meta)
{
    if (isset($_GET["key"])) {
        $order_id = wc_get_order_id_by_order_key($_GET["key"]);
        $order = new WC_Order($order_id);
        $order_meta = get_post_meta($order_id);
        $billing_address = $order->get_billing_address_1();
        $shipping_address = $order->get_shipping_address_1();
        $billing_address = explode('<br/>', $billing_address);
        $shipping_address = explode('<br/>', $shipping_address);

        $attribs = '';
        $data = array();
        $x = 0;
        foreach ($shipping_address as $addr) {
            $name = 'billing_' . $x;
            $data[$name] = $addr;
            $x++;
        }
        $x = 0;
        foreach ($billing_address as $addr) {
            $name = 'shipping_' . $x;
            $data[$name] = $addr;
            $x++;
        }
        foreach ($data as $key => $dat) {
            $attribs .= '"' . $key . '": "' . $dat . '",';
        }
        $attribs .= '"email": "' . $order_meta["_billing_email"]["0"] . '",';
    }

    $current_user = wp_get_current_user();
    $name = null;
    if (0 == $current_user->ID) {
        $name = "";
    } else {
        if (strlen($current_user->user_firstname) > 0 && strlen($current_user->user_lastname) > 0) {
            $name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
        } else if (strlen($current_user->user_firstname) > 0) {
            $name = $current_user->user_firstname;
        } else if (strlen($current_user->user_lastname) > 0) {
            $name = $current_user->user_lastname;
        }
    }
    if (isset($_GET["key"]) && $order_id) {
        $name = $order_meta["_billing_first_name"]["0"] . ' ' . $order_meta["_billing_last_name"]["0"];
        $output = "<script type='text/javascript' data-cfasync='false'>
window.civchat = {
    apiKey: \"$meta\",
    name: \"$name\",
    email: \"$current_user->user_email\",
    " . $attribs . "
    phone: '" . $order_meta["_billing_phone"]["0"] . "'
};
</script>";
        echo $output;
    } else {
        $output = "<script type='text/javascript' data-cfasync='false'>
window.civchat = {
    apiKey: \"$meta\",
    name: \"$name\",
    email: \"$current_user->user_email\"
};
</script>";
        echo $output;
    }
}

add_action('wp_head', 'hook_userengage_javascript');

function hook_userengage_javascript()
{
    $output = '<script type="text/javascript">';
    $output .= 'var userID = "";var userName = "";var userEmail = "";';
    if (isset($_SESSION["user"])) {
        $obj_user = get_user_by('id', $_SESSION["user"]);

        $output .= 'var userID = ' . $obj_user->data->ID . ';';
        $output .= 'var userName = "' . $obj_user->data->display_name . '";';
        $output .= 'var userEmail = "' . $obj_user->data->user_email . '";';
    }
    $output .= 'var templateUrl = "' . plugin_dir_url(__FILE__) . 'ajax-post.php";';
    $output .= '</script>';

    echo $output;
    unset($_SESSION["user"]);
}

add_action('woocommerce_after_add_to_cart_button', 'custome_add_to_cart');

function custome_add_to_cart($product_id)
{
    global $woocommerce;
    global $product;
    $product_id = $product->id;
    $_product = new WC_Product($product_id);
    $attributes = $_product->get_attributes();
    $ptitle = $_product->get_title();
    $pprice = $_product->get_price();
    $pimage = $_product->get_image($size = 'shop_thumbnail');
    $attribs = '';
    $thumb_id = get_post_thumbnail_id();
    $thumb_url = wp_get_attachment_image_src($thumb_id, 'medium', true);

    $attributes = $_product->get_attributes();
    $attribs = "'name': '" . $ptitle . "','productid': '" . $product_id . "','sku': '" . $_product->get_sku() . "','price': '" . $pprice . "',";
    foreach ($attributes as $attrib) {
        $attribs .= "'" . $attrib["name"] . "': '" . $attrib["value"] . "',";
    }

    $output = '<script type="text/javascript"> ';
    $output .= 'var timecheck =  setInterval(function() { if (typeof userengage == "function") { ';
    $output .= "userengage('event.AddToCart', {'pid': '" . $product_id . "','title': '" . $ptitle . "','image_url': '" . $thumb_url[0] . "'," . $attribs . "'price': '" . $pprice . "' });";
    $output .= ' clearInterval(timecheck);} },500);';
    $output .= '</script>';
    echo $output;
}

add_action('user_register', 'userengage_registration_save', 10, 1);
add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');

function myStartSession()
{
    if (!session_id()) {
        session_start();
    }
}

function myEndSession()
{
    session_destroy();
}

add_action('woocommerce_thankyou', 'ue_send', 10, 1);

function ue_send($order_id)
{
    $order = new WC_Order($order_id);
    $order_meta = get_post_meta($order_id);
    echo '<pre>';

    $items = $order->get_items();
    foreach ($items as $item) {
        $product_name = $item['name'];
        $product_id = $item['product_id'];
        $_product = new WC_Product($product_id);
        $pprice = $_product->get_price();
        $product_variation_id = $item['variation_id'];
        $attributes = $_product->get_attributes();
        $attribs = '"name": "' . $product_name . '","sku": "' . $_product->get_sku() . '","productid": "' . $product_id . '","variationid": "' . $product_variation_id . '","price": "' . $pprice . '",';
        foreach ($attributes as $attrib) {
            $attribs .= '"' . $attrib["name"] . '": "' . $attrib["value"] . '",';
        }
    }
    $output = '<script type="text/javascript"  data-cfasync="false">';
    $output .= 'jQuery(document).ready(function($) { ';
    $output .= 'var timecheck =  setInterval(function() { if (typeof userengage == "function") { ';
    $output .= "userengage('event.NewOrder', {'orderId': '" . $order->post->ID . "','paymentType': '" . $order->payment_method_title . "','orderTotal': '" . $order->get_total() . "'," . $attribs . "'email': '" . $order_meta["_billing_email"]["0"] . "'  });";
    $output .= ' clearInterval(timecheck);} },500);';
    $output .= '}); </script>';
    echo $output;

}

function userengage_registration_save($user_id)
{
    if ($user_id) {
        $_SESSION["user"] = $user_id;
    }
}

add_action('wp_head', 'userengage_registration_save');

function UserEngageScript_widget_js()
{
    add_action('wp_print_scripts', 'add_ue_widget');

    function add_ue_widget()
    {
        if (esc_html(get_option('UserEngageScript_toggle_version')) == 0) {
            echo '<script data-cfasync="false" type="text/javascript" src="https://widget.userengage.io/widget.js"></script>';
            wp_enqueue_script('script', plugin_dir_url(__FILE__) . 'assets/js/ue.js', array('jquery'), 1.1, true);
        } else {
            echo '<script data-cfasync="false" type="text/javascript" src="https://widget.userengage.com/widget.js"></script>';
            wp_enqueue_script('script', plugin_dir_url(__FILE__) . 'assets/js/ue.js', array('jquery'), 1.1, true);
        }
    }

}

add_action('wp_enqueue_scripts', 'UserEngageScript_widget_js');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
if (!class_exists('UserEngageScripts')) {

    class UserEngageScripts
    {

        function __construct()
        {
            add_action('admin_init', array(&$this, 'UserEngageScript_admin_init'));
            add_action('admin_menu', array(&$this, 'UserEngageScript_admin_menu'));
            add_action('wp_head', array(&$this, 'UserEngageScript_wp_head'));
        }

        function UserEngageScript_admin_init()
        {
            register_setting('UserEngageScript-apiKey', 'UserEngageScript__apiKey');
            register_setting('UserEngageScript-apiKey', 'UserEngageScript_toggle_version');
        }

        function UserEngageScript_admin_menu()
        {
            add_menu_page(
                'UserEngage.com', 'UserEngage.com', 'manage_options', __FILE__, array(&$this, 'UserEngageScript__panel'), 'dashicons-admin-userengage'
            );
        }

        function UserEngageScript_wp_head()
        {
            $meta = get_option('UserEngageScript__apiKey', '');
            if ($meta != '') {
                UserEngageScript_widget($meta);
            }
        }

        function UserEngageScript__panel()
        {
            ?>
            <?php settings_errors(); ?>
          <div class="wrap">
            <h2>UserEngage.com Plugin - Options</h2>
            <hr/>
            <div class="ue container UserEngageScript__wrap">
              <div class="UserEngageScript__brand"></div>
              <form name="dofollow" action="options.php" method="post">
                  <?php settings_fields('UserEngageScript-apiKey'); ?>
                <div class="ue row two">
                  <div class="ue col">
                    <fieldset class="form-group">
                      <label for="apiKey">API Key</label>
                      <input type="text" id="apiKey"
                             name="UserEngageScript__apiKey"
                             class="ue input"
                             placeholder="xxxxxxxxxxx"
                             value="<?php echo esc_html(get_option('UserEngageScript__apiKey')); ?>"
                             maxlength="<?php if (esc_html(get_option('UserEngageScript_toggle_version')) == 0) echo '64'; else echo '6'; ?>"
                             required>
                    </fieldset>
                    <p>Please enter your application key which has been sent to
                      your email address. The api key is
                      a <?php if (esc_html(get_option('UserEngageScript_toggle_version')) == 0) echo '64'; else echo '6'; ?>
                      letter and number
                      random string, find your API Key <a
                        href="https://app.userengage.com/integrations/"
                        target="_blank">here</a>.</p>
                  </div>
                  <div class="ue col">
                    <fieldset class="form-group">
                      <label for="UserEngageScript_toggle_version">UserEngage
                        version</label>
                      <select name="UserEngageScript_toggle_version"
                              id="UserEngageScript_toggle_version"
                              class="ue input">
                        <option
                          value="0" <?php if (esc_html(get_option('UserEngageScript_toggle_version')) == 0) echo 'selected="selected"'; ?>>
                          1.0 (app.userengage.io)
                        </option>
                        <option
                          value="1" <?php if (esc_html(get_option('UserEngageScript_toggle_version')) == 1) echo 'selected="selected"'; ?>>
                          2.0 (app.userengage.com)
                        </option>
                      </select>
                    </fieldset>
                  </div>
                </div>
                <div class="ue row one">
                  <div class="ue col">
                    <input class="ue button info rounded block button-large"
                           type="submit" name="Submit" value="Save"/>
                  </div>
                </div>
              </form>
            </div>
          </div>
            <?php
        }

    }

    $userengage_scripts = new UserEngageScripts();
}
