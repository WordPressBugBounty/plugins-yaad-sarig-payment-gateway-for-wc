<?php

/**
 * Created by PhpStorm.
 * User: Nurit
 * Date: 13/11/2017
 * Time: 18:47
 */
class WC_Gateway_YaadPay_Refund
{
    public static function init()
    {
        new WC_Gateway_YaadPay_Refund;
    }

    function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'yaadpay_add_refund_manager_key'));
        add_action('wp_ajax_tb_save_refund_manager_key', array($this, 'tb_save_refund_manager_key_callback'));
    }

    public function yaadpay_add_refund_manager_key()
    {
        wp_register_script('refunds_js', plugin_dir_url(__DIR__) . 'assets/js/refunds.js', ['jquery'], yaadpay_get_version());
        wp_localize_script('refunds_js', 'yaadpay', array(
            'refunds_manager_password' => __('Manager Password:', 'yaad-sarig-payment-gateway-for-wc'),
        ));
        wp_enqueue_script('refunds_js');
    }

    function tb_save_refund_manager_key_callback()
    {
        WC_Gateway_Yaadpay::log('[REFUND]: ' . __METHOD__ . ' start', array('source' => 'yaad-sarig-payment-gateway-for-wc'));
        $manager_key = isset($_POST['manager_key']) ? sanitize_textarea_field($_POST['manager_key']) : 'no-key';
        WC()->session->set('manager_key', $manager_key);
        wp_send_json_success();
    }
}
