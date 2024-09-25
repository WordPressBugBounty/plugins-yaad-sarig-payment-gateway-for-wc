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
	$class_name = __NAMESPACE__ . '\\tb_wc_product';
	if ( ! class_exists ( $class_name ) ) {


		abstract class tb_wc_product extends tb_wc_object {
			/**
			 * @return mixed
			 */
			abstract public function get_id ();

			abstract function get_price_including_tax ( $args = array() );

			abstract public function get_description ( $context = 'view' );

			abstract public function get_short_description ( $context = 'view' );

			/**
			 * tb_wc_product constructor.
			 *
			 * @param \WC_Product | int $product
			 */
			public function __construct ( $product ) {
				if ( is_int ( $product ) ) {
					$this->inner = wc_get_product($product);//new \WC_Product( $product );
				} elseif ( $product instanceof \WC_Product ) {
					$this->inner = $product;
				} else {
					$this->inner = null;
				}
			}

			/**
			 * @return string
			 */
			public function get_title () {
				return $this->get_WC_product ()->get_title ();
			}

			/**
			 * @return string
			 */
			public function get_type () {
				return $this->get_WC_product ()->get_type ();
			}

			/**
			 * @param string $context
			 * @return mixed
			 */
			public function get_price ( $context = 'view' ) {
				return $this->get_WC_product ()->get_price ( $context );
			}

			/**
			 * @param string $context
			 * @return mixed
			 */
			public function get_sku ( $context = 'view' ) {
				return $this->get_WC_product ()->get_sku ( $context );
			}

			/**
			 * @param string $context
			 * @return mixed
			 */
			public function get_attributes ( $context = 'view' ) {
				return $this->get_WC_product ()->get_attributes ( $context );
			}

			/**
			 * @param string $context
			 * @return mixed
			 */
			public function get_formatted_variation_attributes ( $context = 'view' ) {
				return $this->get_WC_product ()->get_formatted_variation_attributes ( $context );
			}

			/**
			 * @return array
			 */
			public function get_children(){
				return $this->get_WC_product()->get_children();
			}

			/**
			 * @param string $context
			 * @return mixed
			 */
			public function get_shipping_class_id ( $context = 'view' ) {
				return $this->get_WC_product ()->get_shipping_class_id ( $context );
			}

			/**
			 * @return mixed
			 */
			public function get_shipping_class () {
				return $this->get_WC_product ()->get_shipping_class ();
			}

			/**
			 * @return \WC_Product
			 */
			public function get_WC_product () {
				return $this->inner;
			}

			/**
			 * @return boolean
			 */
			public function is_taxable () {
				return $this->get_WC_product()->is_taxable();
			}
		}
	}
}