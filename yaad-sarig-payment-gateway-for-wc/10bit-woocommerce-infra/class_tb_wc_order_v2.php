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

	$class_name = __NAMESPACE__ . '\\tb_wc_order_v2';
	if ( ! class_exists ( $class_name ) ) {


		class tb_wc_order_v2 extends tb_wc_order {
			/**
			 * @return mixed
			 */
			public function get_id () {
				return $this->inner->id;
			}

			/**
			 * @param tb_wc_item $item
			 *
			 * @return tb_wc_product|null
			 */
			public function get_product ( $item ) {
				$inner_item = $item->get_WC_item();
				$product    = $this->get_WC_order ()->get_product_from_item ( $inner_item );
				if ( $product ) {
					return tb_wc_object::factory(tb_wc_object::PRODUCT,$product );
				}

				return null;
			}

			/**
			 * @param string $context
			 *
			 * @return mixed
			 */
			public function get_user_id ( $context = 'view' ) {
				return $this->get_WC_order ()->get_user_id ();
			}

			public function get_type () {
				return $this->get_WC_product ()->product_type;
			}

			public function get_billing_company ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_company;
			}

			public function get_billing_email ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_email;
			}

			public function get_billing_phone ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_phone;
			}

			public function get_billing_address_1 ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_address_1;
			}

			public function get_billing_address_2 ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_address_2;
			}

			public function get_billing_city ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_city;
			}

			public function get_billing_country ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_country;
			}

			public function get_billing_postcode ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_postcode;
			}

			public function get_customer_note ( $context = 'view' ) {
				return $this->get_WC_order ()->customer_note;
			}

			public function get_billing_first_name ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_first_name;
			}

			public function get_billing_last_name ( $context = 'view' ) {
				return $this->get_WC_order ()->billing_last_name;
			}

			public function get_shipping_total ( $context = 'view' ) {
				return $this->get_WC_order ()->get_total_shipping ();
			}

			public function get_currency ( $context = 'view' ) {
				return $this->get_WC_order ()->get_order_currency ();
			}

			public function get_payment_method ( $context = 'view' ) {
				return $this->get_WC_order ()->payment_method;
			}
		}
	}
}