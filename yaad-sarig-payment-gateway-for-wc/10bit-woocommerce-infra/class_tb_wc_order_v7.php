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

	$class_name = __NAMESPACE__ . '\\tb_wc_order_v7';
	if ( ! class_exists ( $class_name ) ) {


		class tb_wc_order_v7 extends tb_wc_order {
			/**
			 * @return mixed
			 */
			public function get_id () {
				return $this->get_WC_order ()->get_id ();
			}

			/**
			 * @param tb_wc_item $item
			 *
			 * @return tb_wc_product |null
			 */
			public function get_product ( $item ) {
				return tb_wc_object::factory(tb_wc_object::PRODUCT, $item->get_product ($this) );
			}

			/**
			 * @param string $context
			 *
			 * @return mixed
			 */
			public function get_user_id ( $context = 'view' ) {
				return $this->get_WC_order ()->get_user_id ( $context );
			}

			public function get_billing_company ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_company ( $context );
			}

			public function get_billing_email ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_email ( $context );
			}

			public function get_billing_phone ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_phone ( $context );
			}

			public function get_billing_address_1 ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_address_1 ( $context );
			}

			public function get_billing_address_2 ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_address_2 ( $context );
			}

			public function get_billing_city ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_city ( $context );
			}

			public function get_billing_country ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_country ( $context );
			}

			public function get_billing_postcode ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_postcode ( $context );
			}

			public function get_customer_note ( $context = 'view' ) {
				return $this->get_WC_order ()->get_customer_note ( $context );
			}

			public function get_billing_first_name ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_first_name ( $context );
			}

			public function get_billing_last_name ( $context = 'view' ) {
				return $this->get_WC_order ()->get_billing_last_name ( $context );
			}

			public function get_shipping_total ( $context = 'view' ) {
				return $this->get_WC_order ()->get_shipping_total ( $context );
			}

			public function get_currency ( $context = 'view' ) {
				return $this->get_WC_order ()->get_currency ( $context );
			}

			public function get_payment_method ( $context = 'view' ) {
				return $this->get_WC_order ()->get_payment_method ( $context );
			}
		}
	}

}