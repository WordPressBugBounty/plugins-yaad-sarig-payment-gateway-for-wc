<?php

/**
 * Created by PhpStorm.
 * User: Bar
 * Date: 07/08/2023
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

	$class_name = __NAMESPACE__ . '\\tb_wc_product_v8';
	if ( ! class_exists ( $class_name ) ) {


		class tb_wc_product_v8 extends tb_wc_product {
			/**
			 * @return mixed
			 */
			public function get_id () {
				return $this->get_WC_product ()->get_id ();
			}

			/**
			 * @param array $args
			 * @return mixed
			 */
			public function get_price_including_tax ( $args = array() ) {
				return wc_get_price_including_tax ( $this->get_WC_product (), $args );
			}

			/**
			 * @param string $context
			 * @return mixed
			 */
			public function get_description ( $context = 'view' ) {
				return $this->get_WC_product ()->get_description ( $context );
			}

			/**
			 * @param string $context
			 * @return mixed
			 */
			public function get_short_description ( $context = 'view' ) {
				return $this->get_WC_product ()->get_short_description ( $context );
			}
		}
	}
}