<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Yaad Sarig Payment Gateway
 * Plugin Name: Yaad Sarig Payment Gateway For WC
 * Description: Allows receiving payments via Yaad Sarig ( YaadPay )
 * Version: 2.2.5
 * Author: YAAD-PAY & 10Bit
 * Author URI: https://yaadpay.yaad.net/developers/article.php?id=48
 * Text Domain: yaad-sarig-payment-gateway-for-wc
 * WC requires at least: 3.0
 * WC tested up to: 9.0
 */


require_once(plugin_dir_path(__FILE__) . "10bit-woocommerce-infra/tb_wc_adapter.php");


/**
 * Add the gateway to woocommerce
 */

function add_yaadpay_gateway($methods)
{
	$methods[] = 'WC_Gateway_Yaadpay';
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_yaadpay_gateway');

function tenbit_gateway_yaad_add_settings_link($links)
{
	$settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=wc_gateway_yaadpay">' . __('Settings', 'yaad-sarig-payment-gateway-for-wc') . '</a>';
	array_push($links, $settings_link);
	return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'tenbit_gateway_yaad_add_settings_link');

global $my_plugin_vars;

add_action('admin_enqueue_scripts', 'yaadpay_add_scripts');

function yaadpay_add_scripts($hook)
{
	if (isset($_GET['section']) == false) {
		return;
	}
	if ('wc_gateway_yaadpay' != sanitize_text_field($_GET['section']) && 'yaadpay' != sanitize_text_field($_GET['section'])) {
		return;
	}
	wp_enqueue_style('admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
	wp_enqueue_script('admin_js', plugin_dir_url(__FILE__) . 'assets/js/admin.js');
	
	$my_plugin_vars = array(
		'pluginUrl' => plugin_dir_url(__FILE__),
		'nonce' => wp_create_nonce('yaadpay_logs_nonce'), 
        'ajax_url' => admin_url('admin-ajax.php') 
	  );
	wp_localize_script('admin_js', 'my_plugin_vars', $my_plugin_vars);
}


add_action('plugins_loaded', 'woocommerce_yaadpay_init');
function woocommerce_yaadpay_init()
{

	if (!class_exists('WC_Payment_Gateway')) {
		return;
	};
	load_plugin_textdomain('yaad-sarig-payment-gateway-for-wc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	define('YAADPAY_PLUGIN_DIR', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)) . '/');
	define('YAAD_3DS_URL', 'https://icom.yaad.net/cgi-bin/yaadpay/yaadpay3ds.pl');
	define('YAAD_GATEWAY_URL', 'https://icom.yaad.net/cgi-bin/yaadpay/yaadpay3ds.pl');
	define('YAAD_LEUMI_GATEWAY_URL', 'https://icom.yaad.net/cgi-bin/yaadpay/yaadpay3ds.pl');
	//new WC_Gateway_Yaadpay();
	include 'classes/class-wc-gateway-yaadpay.php';
	include 'classes/class-yaadpay-meta-box.php';
	include 'classes/class-wc-yaadpay-user-fields.php';
	include 'classes/class-wc-gateway-yaadpay-refund.php';
	new WC_yaadpay_user_fields();
	WC_Gateway_YaadPay_Metabox::WC_Gateway_yaadpay_metabox_init();
	WC_Gateway_YaadPay_Refund::init();
}

function yaadpay_get_version()
{
	return get_plugin_data(__FILE__)['Version'];
}
