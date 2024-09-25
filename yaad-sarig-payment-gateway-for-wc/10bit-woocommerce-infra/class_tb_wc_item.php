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
	$class_name = __NAMESPACE__ . '\\tb_wc_item' ;
	if(!class_exists($class_name)) {


		abstract class tb_wc_item extends tb_wc_object {
			/**
			 * tb_wc_item constructor.
			 * has the ability to wrap an exiting object, but not to generate a new one
			 *
			 * @param \WC_Order_Item_Product $item
			 */
			public function __construct ( $item ) {
				$this->inner = $item;
			}

			/**
			 * @param tb_wc_order $order
			 *
			 * @return tb_wc_product|null
			 */
			abstract public function get_product ( $order );

			public function get_attribute ( $att ) {
				$inner_item = $this->get_WC_item ();
				if ( isset( $inner_item[ $att ] ) ) {
					return $inner_item[ $att ];
				}

				return false;
			}


			public function get_name () {
				return $this->get_attribute ( 'name' );
			}

			public function get_line_subtotal () {
				return $this->get_attribute ( 'line_subtotal' );
			}

			public function get_line_subtotal_tax () {
				return $this->get_attribute ( 'line_subtotal_tax' );
			}

			public function get_qty () {
				return $this->get_attribute ( 'qty' );
			}

			public function get_product_id () {
				return $this->get_attribute ( 'product_id' );
			}

			public function get_variation_id () {
				return $this->get_attribute ( 'variation_id' );
			}

			/**
			 * @return \WC_Order_Item_Product
			 */
			public function get_WC_item () {
				return $this->inner;
			}
		}
	}
/*//https://stackoverflow.com/questions/20763744/type-hinting-specify-an-array-of-objects

	class tb_wc_items_array extends ArrayObject {

		public function offsetSet($key, $val) {
			if ($val instanceof tb_wc_item) {
				return parent::offsetSet($key, $val);
			}
			throw new InvalidArgumentException('Value must be a tb_wc_item');
		}
	}*/


}