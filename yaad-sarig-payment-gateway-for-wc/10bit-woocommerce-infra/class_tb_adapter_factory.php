<?php

/**
 * Created by PhpStorm.
 * User: Nurit
 * Date: 23/04/2017
 * Time: 16:06
 */



/**
 * Class tb_wc_adapter_factory
 * @package standard\includes
 */
namespace tb_infra_1_0_11{

    $class_name = __NAMESPACE__ . '\\tb_wc_object';
	if ( ! class_exists ( $class_name ) ) {

		//TODO: factory methods should throw exception instead of returning null

		abstract class tb_wc_adapter_factory {

            /**
             * @param $obj
             * @return null| ITB_Order_adapter
             */
            public static function get_order_adapter ($obj ) {

            	$order = tb_wc_object::factory(tb_wc_object::ORDER,$obj);
            	if($order) {
					return new TB_Order_Adapter($order);
				}
				return null;
 /*
                $wc_version = get_option ( 'woocommerce_version', null );
                $wc_version = strstr ( $wc_version, '.', true );
                $class_name = 'tb_wc_order_v' . $wc_version;
                $class_name = __NAMESPACE__ . '\\' . $class_name;
                if ( class_exists ( $class_name ) ) {
                    $order = new $class_name($obj);

                    return new TB_Order_Adapter($order );
                }

                return null;
 */           }


            /**
             * @param $obj
             * @return null| ITB_Product_Adapter
             */
            public static function get_product_adapter ($obj ) {
				$product = tb_wc_object::factory(tb_wc_object::PRODUCT,$obj);
				if($product){
					return new TB_Product_Adapter($product);
				}
				return null;
/*
                $wc_version = get_option ( 'woocommerce_version', null );
                $wc_version = strstr ( $wc_version, '.', true );
                $class_name = 'tb_wc_product_v' . $wc_version;
                $class_name = __NAMESPACE__ . '\\' . $class_name;
                if ( class_exists ( $class_name ) ) {
                    $product = new $class_name($obj);
                    return new TB_Product_Adapter($product );
                }

                return null;
*/
            }

            /**
             * @param $obj
             * @return null| ITB_Item_Adapter
             */
            public static function get_item_adapter ($obj ) {
				$item = tb_wc_object::factory(tb_wc_object::ITEM,$obj);
				if($item){
					return new TB_Item_Adapter($item);
				}
				return null;
/*
                $wc_version = get_option ( 'woocommerce_version', null );
                $wc_version = strstr ( $wc_version, '.', true );
                $class_name = 'tb_wc_items_v' . $wc_version;
                $class_name = __NAMESPACE__ . '\\' . $class_name;
                if ( class_exists ( $class_name ) ) {
                    $item = new $class_name($obj);
                    return new TB_Item_Adapter($item );
                }

                return null;
*/
            }
		}
	}

}