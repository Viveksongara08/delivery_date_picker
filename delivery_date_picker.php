<?php

/**
 * Theme options plugin for use for Delivery date picker
 * Take this as a base plugin and modify as per your need.
 *
 * @package Delivery date picker
 * @author vivek songara
 * @license GPL-2.0+
 * @link http://www.example.com
 * @copyright 2019 Vivek songara, LLC. All rights reserved.
 *
 *            @wordpress-plugin
 *            Plugin Name: Delivery date picker
 *            Plugin URI: http://www.example.com
 *            Description: Delivery date picker Plugin
 *            Version: 3.1
 *            Author: Vivek Songara
 *            Author URI: 
 *            Text Domain: Delivery date picker
 *            Contributors: Delivery date picker
 *            License: GPL-2.0+
 *            License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
//*************//Delivery date picker//***************//
function AddDeliveryDate()
{


  function delivery_add_date_picker_Product_page()
  {
?>
    <label>Select delivery date</label>
    <input placeholder="Select a delivery date" style="width: 100%;margin-bottom: 10px;" class="web_delivery" type="text" name="deliverydate" id="deliverydate" required />

  <?php
  }
  add_action('woocommerce_before_add_to_cart_quantity', 'delivery_add_date_picker_Product_page');

  //Add date in cart

  add_filter("woocommerce_add_cart_item_data", "cs_add_cart_item_data", 10, 2);
  function cs_add_cart_item_data($cart_item, $product_id)
  {


    if (isset($_POST['deliverydate'])) {

      $_SESSION["deliverydate"] = $_POST['deliverydate'];
      $cart_item['deliverydate'] = sanitize_text_field($_POST['deliverydate']);
    }
    return $cart_item;
  }



  // Display in cart items the chosen date

  add_filter('woocommerce_get_item_data', 'delivery_date_display_custom_item_data', 10, 2);
  function delivery_date_display_custom_item_data($cart_item_data, $cart_item)
  {
    if (isset($cart_item['deliverydate'])) {
      $cart_item_data[] = array(
        'name'  => __("Date", "woocommerce"),
        'value' => date('Y-m-d', strtotime($cart_item['deliverydate'])), // Formatting date (optional)
      );
    }
    return $cart_item_data;
  }



  //******* add js and css ********//

  add_action('wp_enqueue_scripts', 'delivery_date_picker_scripts_styles');


  function delivery_date_picker_scripts_styles()
  {


    wp_enqueue_style('jquery-ui', plugins_url('/css/jquery-ui.css', __FILE__), false, '1.0.0', 'all');

    wp_enqueue_script('jquery-js', plugins_url('/js/jquery-3.6.0.js', __FILE__));
    wp_enqueue_script('jquery-ui-js', plugins_url('/js/jquery-ui.js', __FILE__));
  }

  // **************  add date picker javascript *********//

  function delivery_date_picker_hook_javascript_footer()
  {
  ?>
    <script>
	var j = jQuery.noConflict();
      j(function() {
        j("#deliverydate").datepicker({
          minDate: 0
        });
        j("#delivery_date").datepicker({
          minDate: 0
        });

      });
    </script>
<?php
  }
  add_action('wp_footer', 'delivery_date_picker_hook_javascript_footer');


  //*********** one product add in cart at a time **************//

  add_filter('woocommerce_add_to_cart_validation', 'delivery_date_only_one_in_cart', 9999, 2);

  function delivery_date_only_one_in_cart($passed, $added_product_id)
  {
    wc_empty_cart();
    return $passed;
  }


  //************* checkout page *******************//

  //********** add custom filed in cart page ************//

  add_action('woocommerce_after_checkout_billing_form', 'delivery_date_add_custom_checkout_field');

  function delivery_date_add_custom_checkout_field($checkout)
  {
    /* $current_user = wp_get_current_user();
   $saved_license_no = $current_user->license_no;
    */
    woocommerce_form_field('delivery_date', array(
      'type' => 'text',
      'class' => array('form-row-wide'),
      'label' => 'Select delivery date',
      'placeholder' => 'Select a delivery date',
      'required' => true,
      'default' => $_SESSION["deliverydate"],
    ), $checkout->get_value('delivery_date'));
  }

  add_action('woocommerce_checkout_process', 'delivery_date_validate_new_checkout_field');

  function delivery_date_validate_new_checkout_field()
  {
    if (!$_POST['delivery_date']) {
      wc_add_notice('Please select your delivery date', 'error');
    }
  }


  add_action('woocommerce_checkout_update_order_meta', 'delivery_date_save_new_checkout_field');

  function delivery_date_save_new_checkout_field($order_id)
  {
    if ($_POST['delivery_date']) update_post_meta($order_id, 'delivery_date', esc_attr($_POST['delivery_date']));
  }

  add_action('woocommerce_admin_order_data_after_billing_address', 'delivery_date_show_new_checkout_field_order', 10, 1);

  function delivery_date_show_new_checkout_field_order($order)
  {
    $order_id = $order->get_id();
    if (get_post_meta($order_id, 'delivery_date', true)) echo '<p><strong>delivery date:</strong> ' . get_post_meta($order_id, 'delivery_date', true) . '</p>';
  }

  add_action('woocommerce_email_after_order_table', 'delivery_date_show_new_checkout_field_emails', 20, 4);

  function delivery_date_show_new_checkout_field_emails($order, $sent_to_admin, $plain_text, $email)
  {
    if (get_post_meta($order->get_id(), 'delivery_date', true)) echo '<p><strong> delivery date:</strong> ' . get_post_meta($order->get_id(), 'delivery_date', true) . '</p>';
  }


  function register_delivery_date_session()
  {
    if (!session_id()) {
      session_start();
    }
  }

  add_action('init', 'register_delivery_date_session');
}
add_action('after_setup_theme', 'AddDeliveryDate');
