<?php
/*
Plugin Name: Woocommerce Hubtel Gateway
Plugin URI:
Description: Accept payments on your WooCommerce powered website directly to your Hubtel account.
Version: 1.0
Author: Kwame Twum
Author URI: https://github.com/kmtwum
Developer: Kwame Twum
Developer URI: https://github.com/kmtwum
Text Domain: hubtel-pay
WC requires at least: 2.2
WC tested up to: 5.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

@package   Hubtel Pay
@author    Kwame Twum
@category  Admin
@license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
*/

defined('ABSPATH') or die;

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

add_action('plugins_loaded', 'hubtel_init', 11);
add_filter('woocommerce_payment_gateways', 'add_to_gateways');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_hubtel_pay_plugin_links');

function wc_hubtel_pay_plugin_links($links): array
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=hubtel-pay') . '">'
        . __('Configure', 'hubtel-pay') . '</a>'
    );
    return array_merge($plugin_links, $links);
}


function add_to_gateways($gateways)
{
    $gateways[] = 'wc_money';
    return $gateways;
}


function hubtel_init()
{
    require_once 'helpers/money.php';
    return new wc_money();
}
