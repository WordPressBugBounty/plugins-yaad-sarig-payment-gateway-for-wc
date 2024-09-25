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

	$class_name = __NAMESPACE__ . '\\tb_wc_order';
	if ( ! class_exists ( $class_name ) ) {


		abstract class tb_wc_order extends tb_wc_object {
			/**
			 * tb_wc_order constructor.
			 *
			 * @param \WC_Order | int $order
			 */
			public function __construct ( $order ) {
				if ( is_int ( $order ) ) {
					$this->inner = new \WC_Order( $order );
				} elseif ( is_a ( $order, "WC_Order" ) ) {
					$this->inner = $order;
				} else {
					$this->inner = null;
				}
			}

			/**
			 * @return mixed
			 */
			abstract public function get_id ();

			/**
			 * @param tb_wc_item $item
			 *
			 * @return tb_wc_product|null
			 */
			abstract public function get_product ( $item );

			/**
			 * @param string $context
			 *
			 * @return mixed
			 */
			abstract public function get_user_id ( $context = 'view' );

			abstract public function get_billing_company ( $context = 'view' );

			abstract public function get_billing_email ( $context = 'view' );

			abstract public function get_billing_phone ( $context = 'view' );

			abstract public function get_billing_address_1 ( $context = 'view' );

			abstract public function get_billing_address_2 ( $context = 'view' );

			abstract public function get_billing_city ( $context = 'view' );

			abstract public function get_billing_country ( $context = 'view' );

			abstract public function get_billing_postcode ( $context = 'view' );

			abstract public function get_customer_note ( $context = 'view' );

			abstract public function get_billing_first_name ( $context = 'view' );

			abstract public function get_billing_last_name ( $context = 'view' );

			abstract public function get_shipping_total ( $context = 'view' );

			abstract public function get_currency ( $context = 'view' );

			abstract public function get_payment_method ( $context = 'view' );

			public function get_status ( $context = 'view' ) {
				return $this->get_WC_order ()->get_status ( $context );
			}

			public function get_total_tax ( $context = 'view' ) {
				return $this->get_WC_order ()->get_total_tax ( $context );
			}

			public function add_order_note ( $note, $is_customer_note = 0, $added_by_user = false ) {
				return $this->get_WC_order ()->add_order_note ( $note, $is_customer_note, $added_by_user );
			}

			public function update_status ( $new_status, $note = '', $manual = false ) {
				return $this->get_WC_order ()->update_status ( $new_status, $note, $manual );
			}

			public function payment_complete ( $transaction_id = '' ) {
				return $this->get_WC_order ()->payment_complete ( $transaction_id );
			}

			/*
			 * @param tb_wc_item $item
			 * @param bool $inc_tax
			 * @param bool $round
			 * @return float
			 */
			public function get_line_subtotal( $item, $inc_tax = false, $round = true ) {
				return $this->get_WC_order()->get_line_subtotal($item->get_WC_object(), $inc_tax, $round);
			}
			/*
			 * @param tb_wc_item $item
			 * @param bool $inc_tax
			 * @param bool $round
			 * @return float
			 */
			public function get_item_subtotal( $item, $inc_tax = false, $round = true ) {
				return $this->get_WC_order()->get_item_subtotal($item->get_WC_object(), $inc_tax, $round);
			}
			/*
			 * @param tb_wc_item $item
			 * @param bool $inc_tax
			 * @param bool $round
			 * @return float
			 */
			public function get_item_total( $item, $inc_tax = false, $round = true ) {
				return $this->get_WC_order()->get_item_total($item->get_WC_object(), $inc_tax, $round);
			}

			/*
			 * @param tb_wc_item $item
			 * @param bool $round
			 * @return float
			 */
			public function get_item_tax( $item, $round = true ) {
				return $this->get_WC_order()->get_item_tax($item->get_WC_object(), $round);
			}
			/*
			 * @param tb_wc_item $item
			 * @param bool $inc_tax
			 * @param bool $round
			 * @return float
			 */
			public function get_line_total( $item, $inc_tax = false, $round = true ){
				return $this->get_WC_order()->get_line_total($item->get_WC_object(), $inc_tax, $round);
			}

			/*
			 * @param tb_wc_item $item
			 * @return float
			 */
			public function get_line_tax( $item ) {
				return $this->get_WC_order()->get_line_tax($item->get_WC_object());
			}
			/**
			 * @param string $types
			 *
			 * @return array tb_wc_item
			 */
			public function get_items ( $types = 'line_item' ) {
				$items    = $this->get_WC_order ()->get_items ( $types );
				$tb_items = array();//new tb_wc_items_array();
				foreach ( $items as $item ) {
					$tb_items[] = tb_wc_object::factory ( tb_wc_object::ITEM, $item );
				}

				return $tb_items;
			}

			public function calculate_totals ( $and_taxes = true ) {
				return $this->get_WC_order ()->calculate_totals ( $and_taxes );
			}

			public function get_total ( $context = 'view' ) {
				return $this->get_WC_order ()->get_total ( $context );
			}

			public function get_formatted_billing_full_name () {
				return $this->get_WC_order ()->get_formatted_billing_full_name ();
			}

			public function get_cancel_order_url ( $redirect = '' ) {
				return $this->get_WC_order ()->get_cancel_order_url ( $redirect );
			}

			public function get_checkout_payment_url ( $on_checkout = false ) {
				return $this->get_WC_order ()->get_checkout_payment_url ( $on_checkout );
			}

			public function get_checkout_order_received_url () {
				return $this->get_WC_order ()->get_checkout_order_received_url ();
			}

			public function get_total_discount ( $ex_tax = true ) {
				return $this->get_WC_order ()->get_total_discount ( $ex_tax );
			}

			public function get_shipping_method () {
				return $this->get_WC_order ()->get_shipping_method ();
			}

			public function get_total_shipping () {
				return $this->get_WC_order ()->get_total_shipping ();
			}

			public function get_shipping_tax ( $context = 'view' ) {
				return $this->get_WC_order ()->get_shipping_tax ( $context );
			}

			public function get_transaction_id ( $context = 'view' ) {
				return $this->get_WC_order ()->get_transaction_id ( $context );
			}

			public function get_fees ( $context = 'view' ) {
				return $this->get_WC_order ()->get_fees ( $context );
			}

			/**
			 * @return \WC_Order
			 */
			public function get_WC_order () {
				return $this->inner;
			}

		}
	}

}
