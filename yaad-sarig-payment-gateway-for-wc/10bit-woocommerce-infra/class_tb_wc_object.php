<?php

/**
 * Created by PhpStorm.
 * User: Nurit
 * Date: 23/04/2017
 * Time: 16:06
 */


/*
 * Class names naming-convention:
 * tb_prefix    ::= "tb_wc_"
 * object_type  ::= "order"/"item"/"product"
 * v            ::= "_v"
 * wc_version   ::= "2"/"3"
 * <tb_prefix><object_type>[<v><wc_version>]
 */


/**
 * Class tb_wc_object
 * @package standard\includes
 */
namespace tb_infra_1_0_11{

    $class_name = __NAMESPACE__ . '\\tb_wc_object';
	if ( ! class_exists ( $class_name ) ) {


		abstract class tb_wc_object {

			protected $inner;
			const ORDER = 'order';
			const PRODUCT = 'product';
			const ITEM = 'item';

			/**
			 * @return mixed
			 */
			public function get_WC_object () {
				return $this->inner;
			}

			/**
			 * @param string $which_object
			 * @param mixed $obj

			 * @return tb_wc_item|tb_wc_product|tb_wc_order|null

             */

			public static function factory ( $which_object, $obj ) {
				$wc_version = get_option ( 'woocommerce_version', null );
				$wc_version = strstr ( $wc_version, '.', true );
				$class_name = 'tb_wc_' . $which_object . '_v' . $wc_version;
				$class_name = __NAMESPACE__ . '\\' . $class_name;
				if ( class_exists ( $class_name ) ) {
					return new $class_name( $obj );
				}

				return null;
			}

 		}
	}

}