<?php
/**
 * Created by PhpStorm.
 * User: Nurit
 * Date: 23/04/2017
 * Time: 16:06
 */
namespace tb_infra_1_0_11{
	require_once(plugin_dir_path(__FILE__) . 'tb_wc_adapter.php');

//Interfaces:
	require_once (plugin_dir_path(__FILE__) . '/interfaces/itb_item_adapter.php');
	require_once (plugin_dir_path(__FILE__) . '/interfaces/itb_order_adapter.php');
	require_once (plugin_dir_path(__FILE__) . '/interfaces/itb_product_adapter.php');
//Adapters:
	require_once (plugin_dir_path(__FILE__) . '/adapters/tb_item_adapter.php');
	require_once (plugin_dir_path(__FILE__) . '/adapters/tb_order_adapter.php');
	require_once (plugin_dir_path(__FILE__) . '/adapters/tb_product_adapter.php');
//Factory:
	require_once (plugin_dir_path(__FILE__) . 'class_tb_adapter_factory.php');

}