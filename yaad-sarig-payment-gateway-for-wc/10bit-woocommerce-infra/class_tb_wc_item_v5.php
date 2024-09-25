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
namespace tb_infra_1_0_11 {
	$class_name = __NAMESPACE__ . '\\tb_wc_item_v5';
	if ( ! class_exists ( $class_name ) ) {


		class tb_wc_item_v5 extends tb_wc_item {
			/**
			 * @param tb_wc_order $order
			 * @return tb_wc_product|null
			 */
			public function get_product ( $order ) {
				return tb_wc_object::factory (tb_wc_object::PRODUCT, $this->get_WC_item ()->get_product () );
			}

			/**
			 * @param string $att
			 * @return mixed | bool
			 */
/*			public function get_attribute ( $att ) {
				$inner_item = $this->get_WC_item ();
				if(offsetExists( $att )) {
					$inner_item->offsetGet($att);
				}
				return false;
			}*/

		}
	}
}

