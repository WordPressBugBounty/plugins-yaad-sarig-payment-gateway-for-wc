<?php
use tb_infra_1_0_11\tb_wc_object as tb_wc_object;
use tb_infra_1_0_11\tb_wc_order as tb_wc_order;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

final class  WC_Gateway_YaadPay_Metabox {
    const YAADPAY_CC_PAYMENT = 'yaad_credit_card_payment';

	function __construct () {
		add_action ( 'wp_ajax_yaadpay_token_pay', array( $this, 'yaadpay_token_pay_callback' ) );
		add_action ( 'wp_ajax_yaadpay_commit_trans', array( $this, 'yaadpay_commit_trans_callback' ) );
		add_action ( 'wp_ajax_yaadpay_get_token_data', array( $this, 'yaadpay_get_token_data_callback' ) );
		add_action ( 'wp_ajax_yaadpay_update_subscription_parent', array( $this, 'yaadpay_update_subscription_parent_callback' ) );

		$hpos_status = "yes";
		//$hpos_status = WC_Gateway_Yaadpay::get_hpos_status();
		//WC_Gateway_Yaadpay::log('HPOS status: ' . $hpos_status);
		if ($hpos_status === 'no') {
			add_action ( 'add_meta_boxes', array( $this, 'yaadpay_add_meta_box' ) );
		} else {
			add_action ( 'add_meta_boxes', array( $this, 'yaadpay_add_meta_box2' ) );
		}
	}


	public function yaadpay_add_meta_box($screen) {
		global $post;

		if ($screen !== 'shop_order') {
			return;
		}

		$order = wc_get_order($post->ID);
		$allGateways = WC_Payment_Gateways::instance()->payment_gateways();
		$gateway = $allGateways[$order->get_payment_method()] ?? false;
		if (!$gateway || $order->get_payment_method() !== $gateway->id) {
			return;
		}

		if ($gateway->get_option('yaad_invoices') === 'yes') {
			add_meta_box(
				'yaadpay_invoice',
				__ ( 'Yaadpay Invoice','yaad-sarig-payment-gateway-for-wc' ),
				array( $this, 'yaad_invoice_meta_box_callback' ),
				$screen,
				'side',
				'default',
				['gateway' => $gateway]
			);
		}

		add_meta_box(
			'yaadpay_sectionid',
			__ ( 'Yaadpay Transaction information', 'yaad-sarig-payment-gateway-for-wc' ),
			array( $this, 'yaadpay_order_meta_box_callback' ),
			$screen
		);
	}

	public function yaadpay_add_meta_box2($screen) {
		global $post;
			
		// to enter only in screens of ORDERS (shop_order = legacy, woocommerce_page_wc-orders = HPOS)
		if ($screen !== 'shop_order' && $screen !== 'woocommerce_page_wc-orders') { 
			return;
		}
		
		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

		$order = wc_get_order($post->ID);

		// to find the order in HPOS
		$order_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
		if ($screen == 'woocommerce_page_wc-orders'){
			$order = wc_get_order($order_id);
			// to exit in ADD ORDER screen in HPOS
			if (isset($_GET['action']) && sanitize_text_field($_GET['action'] == 'new')){
				return;
			}	
		}

		$allGateways = WC_Payment_Gateways::instance()->payment_gateways();
		$gateway = $allGateways[$order->get_payment_method()] ?? false;
		if (!$gateway || $order->get_payment_method() !== $gateway->id) {
			return;
		}

		if ($gateway->get_option('yaad_invoices') === 'yes') {
			add_meta_box(
				'yaadpay_invoice',
				__ ( 'Yaadpay Invoice','yaad-sarig-payment-gateway-for-wc' ),
				array( $this, 'yaad_invoice_meta_box_callback2' ),
				$screen,
				'side',
				'default',
				['gateway' => $gateway]
			);
		}

		add_meta_box(
			'yaadpay_sectionid',
			__ ( 'Yaadpay Transaction information', 'yaad-sarig-payment-gateway-for-wc' ),
			array( $this, 'yaadpay_order_meta_box_callback2' ),
			$screen,
			'side',
			'default',
			['gateway' => $gateway]
		);
	}

	//display invoice link to download invoice from WC Admin Orders
	public function yaad_invoice_meta_box_callback(\WP_Post $post, $metabox) {
		$order = wc_get_order($post->ID);
		$invoiceLink = $order->get_meta('yaad_invoice_link');
		if (!$invoiceLink && !$order->has_status('on-hold')) {
			$invoiceLink = $metabox['args']['gateway']->get_invoice_link($order);
		}

		if ($invoiceLink) {
			printf(
				'<a class="button" href="%s" target="_blank">%s</a>',
				esc_url($invoiceLink),
				__('Download Invoice', 'yaad-sarig-payment-gateway-for-wc')
			);
		} else {
			printf(
				'<div class="button disabled">%s</div>',
				__('Download Invoice','yaad-sarig-payment-gateway-for-wc')
			);
		}
	}

	//display invoice link to download invoice from WC Admin Orders
	public function yaad_invoice_meta_box_callback2($post_or_order_object, $metabox) {
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		
		$invoiceLink = $order->get_meta('yaad_invoice_link');
		if (!$invoiceLink && !$order->has_status('on-hold')) {
			$invoiceLink = $metabox['args']['gateway']->get_invoice_link($order);
		}

		if ($invoiceLink) {
			printf(
				'<a class="button" href="%s" target="_blank">%s</a>',
				esc_url($invoiceLink),
				__('Download Invoice', 'yaad-sarig-payment-gateway-for-wc')
			);
		} else {
			printf(
				'<div class="button disabled">%s</div>',
				__('Download Invoice','yaad-sarig-payment-gateway-for-wc')
			);
		}
	}

	function yaadpay_get_token_data_callback () {
		WC_Gateway_Yaadpay::log('[INFO]: yaadpay_get_token_data_callback', array('source' => 'yaad-sarig-payment-gateway-for-wc'));
		if (isset($_POST['order_id']) && isset($_POST['transaction_id'])) {
		$order_id       = sanitize_text_field($_POST['order_id']);
		$transaction_id = sanitize_text_field($_POST['transaction_id']);
		WC_Gateway_Yaadpay::log('[INFO]: order id: ' . $order_id . " transaction id: " . $transaction_id, array('source' => 'yaad-sarig-payment-gateway-for-wc'));
		$gateways              = WC_Payment_Gateways::instance ();
		$available_gateways    = $gateways->get_available_payment_gateways ();
		$yaadpay               = $available_gateways['yaadpay'];
		$got_a_token_from_yaad = $yaadpay->request_token_data_by_trans_id ( $transaction_id, $order_id );
		$what_to_reply         = $got_a_token_from_yaad ? 'success' : __ ( 'an error occurred, please contact Yaad Sarig', 'yaad-sarig-payment-gateway-for-wc' );
		WC_Gateway_Yaadpay::log('[INFO]: reply: ' . $what_to_reply, array('source' => 'yaad-sarig-payment-gateway-for-wc'));
		echo esc_html($what_to_reply);
		}		
		wp_die ();
	}

	function yaadpay_update_subscription_parent_callback(){
		WC_Gateway_Yaadpay::log('[INFO]: yaadpay_update_subscription_parent_callback', array('source' => 'yaad-sarig-payment-gateway-for-wc'));
		if ( isset( $_POST['order_id'] ) ) {
			$order_id       = sanitize_text_field($_POST['order_id']);
			WC_Gateway_Yaadpay::log('[INFO]: order id: ' . $order_id, array('source' => 'yaad-sarig-payment-gateway-for-wc'));
			$gateways              = WC_Payment_Gateways::instance ();
			$available_gateways    = $gateways->get_available_payment_gateways ();
			$yaadpay               = $available_gateways['yaadpay'];
			$updated_ok = $yaadpay->update_subscription_parent_order( $order_id );
			$what_to_reply         = $updated_ok ? 'success' : __( 'Failed to update subscription parent order, please contact 10bit', 'yaad-sarig-payment-gateway-for-wc' );
			echo esc_html( $what_to_reply );
			WC_Gateway_Yaadpay::log('[INFO]: reply: ' . $what_to_reply, array('source' => 'yaad-sarig-payment-gateway-for-wc'));
			wp_die ();
		}
	}

	function yaadpay_token_pay_callback () {
		WC_Gateway_Yaadpay::log('[INFO]: ' . __METHOD__, array('source' => 'yaad-sarig-payment-gateway-for-wc'));

		// Sanitize and validate the input data
		$orderId = isset( $_POST['orderId'] ) ? intval( $_POST['orderId'] ) : '';
		if ( empty( $orderId ) || ! is_numeric( $orderId ) ) {
			echo __( 'Invalid order ID.', 'yaad-sarig-payment-gateway-for-wc' );
			wp_die();
		}

		$transaction_id = isset( $_POST['transaction_id'] ) ? sanitize_text_field( $_POST['transaction_id'] ) : '';
		if ( empty( $transaction_id ) ) {
			echo __( 'Transaction ID is required.', 'yaad-sarig-payment-gateway-for-wc' );
			wp_die();
		}

		$token_code = isset( $_POST['YaadpayTK'] ) ? sanitize_textarea_field( $_POST['YaadpayTK'] ) : '';
		if ( empty( $token_code ) ) {
			echo __( 'Token code is required.', 'yaad-sarig-payment-gateway-for-wc' );
			wp_die();
		}

		$token_epiration_month = isset( $_POST['expmonth'] ) ? sanitize_text_field( $_POST['expmonth'] ) : '';
		if ( empty( $token_epiration_month ) || ! is_numeric( $token_epiration_month ) ) {
			echo __( 'Token expiration month is required and must be a number.', 'yaad-sarig-payment-gateway-for-wc' );
			wp_die();
		}

		$token_expiration_year = isset( $_POST['expyear'] ) ? sanitize_text_field( $_POST['expyear'] ) : '';
		if ( empty( $token_expiration_year ) || ! is_numeric( $token_expiration_year ) ) {
			echo __( 'Token expiration year is required and must be a number.', 'yaad-sarig-payment-gateway-for-wc' );
			wp_die();
		}

		$gateways           = WC_Payment_Gateways::instance ();
		$available_gateways = $gateways->get_available_payment_gateways ();
		$yaadpay            = $available_gateways['yaadpay'];

		$resp = $yaadpay->process_token_payment($orderId, $transaction_id, $token_code, $token_epiration_month, $token_expiration_year);

		WC_Gateway_Yaadpay::log('resp: ' . print_r($resp, true) . ' type: ' . gettype($resp), array('source' => 'yaad-sarig-payment-gateway-for-wc'));
		$payment_error = '';
		switch ( $resp ) {
			case '0' :
				add_post_meta ( $orderId, 'Payment Gateway', 'Yaadpay' );
				$order   = tb_wc_object::factory ( "order", $orderId );
				$order->add_order_note ( __ ( 'Yaadpay payment completed', 'yaad-sarig-payment-gateway-for-wc' ) );
				$order->payment_complete ();
				echo 'success';
				break;
			default :
				$payment_error .= __ ( 'Paymnet failure, please try again or contact the store administrator', 'yaad-sarig-payment-gateway-for-wc' );
				$payment_error .= print_r ( $resp, true );
				echo esc_html ($payment_error);
				break;
		}

		wp_die ();
	}

	function yaadpay_commit_trans_callback () {
		WC_Gateway_Yaadpay::log('[INFO]: ' . __METHOD__, array('source' => 'yaad-sarig-payment-gateway-for-wc'));
		$orderId = isset( $_POST['orderId'] ) ? (int)sanitize_text_field( $_POST['orderId'] ) : '';

		$gateways           = WC_Payment_Gateways::instance ();
		$available_gateways = $gateways->get_available_payment_gateways ();
		$yaadpay            = $available_gateways['yaadpay'];

		$transaction_id = isset( $_POST['transaction_id'] ) ? sanitize_text_field( $_POST['transaction_id'] ) : '';

		$resp = $yaadpay->process_postpone_payment($orderId, $transaction_id);

		WC_Gateway_Yaadpay::log('resp: ' . print_r($resp, true) . ' type: ' . gettype($resp), array('source' => 'yaad-sarig-payment-gateway-for-wc'));
		$payment_error = '';
		switch ( $resp ) {
			case '0' :
				add_post_meta ( $orderId, 'Payment Gateway', 'Yaadpay' );
				$order   = tb_wc_object::factory ( "order", $orderId );
				$order->add_order_note ( esc_html(__( 'Yaadpay payment completed', 'yaad-sarig-payment-gateway-for-wc' )) );
				$order->payment_complete ();
				echo 'success';
				break;
			default :
				$payment_error .= esc_html(__( 'Paymnet failure, please try again or contact the store administrator', 'yaad-sarig-payment-gateway-for-wc' ));
				$payment_error .= print_r ( $resp, true );
				echo esc_html ($payment_error);
				break;
		}

		wp_die ();
	}

	/**
	 * Prints the box content.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	function yaadpay_order_meta_box_callback ( WP_Post $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field ( 'yaadpay_meta_box', 'yaadpay_meta_box_nonce' );
		$order     = tb_wc_object::factory ( "order", get_the_ID () );
		$YaadpayTK = get_post_meta ( $order->get_id (), '_yaadpay_token', true );
		$YaadPostpone = get_post_meta ( $order->get_id (), '_yaad_postpone', true );

		if ( empty( $YaadpayTK ) == false ) { // J5
			$this->build_token_payment_form ( $order, $YaadpayTK );
		} else if ($YaadPostpone) { // Postpone
			$this->build_commit_trans_form( $order );
		} else { // Direct
			$this->build_missing_token_form( $order);
		}

		if (function_exists('wcs_order_contains_renewal')){
			if(wcs_order_contains_renewal($order->get_id())) {
				$this->update_subscription_parent_order($order);
			}
		}

	}

	/**
	 * Prints the box content.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	function yaadpay_order_meta_box_callback2 () {

		// Add an nonce field so we can check for it later.
		wp_nonce_field ( 'yaadpay_meta_box', 'yaadpay_meta_box_nonce' );

		$order;
		$YaadpayTK;
		$YaadPostpone;

		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';
	
		if ( $screen == 'woocommerce_page_wc-orders') {
			$order = wc_get_order($_GET['id']);
			$YaadpayTK = $order->get_meta('_yaadpay_token', true);
			$YaadPostpone = $order->get_meta('_yaad_postpone', true);
		} else {
			$order     = tb_wc_object::factory ("order", get_the_ID ());
			$YaadpayTK = get_post_meta ($order->get_id (), '_yaadpay_token', true);
			$YaadPostpone = get_post_meta ($order->get_id (), '_yaad_postpone', true);
		}

		if ( empty( $YaadpayTK ) == false ) { // J5
			$this->build_token_payment_form2 ( $order, $YaadpayTK );
		} else if ($YaadPostpone) { // Postpone
			$this->build_commit_trans_form2( $order );
		} else { // Direct
			$this->build_missing_token_form2( $order);
		}

		if (function_exists('wcs_order_contains_renewal')){
			if(wcs_order_contains_renewal($order->get_id())) {
				$this->update_subscription_parent_order($order);
			}
		}

	}

	public static function WC_Gateway_yaadpay_metabox_init () {
		new WC_Gateway_yaadpay_metabox;

	}

	/**
	 * @param string $YaadpayTK
	 * @param tb_wc_order $order
	 */
	private function build_token_payment_form ( tb_wc_order $order, $YaadpayTK ) {
		$expmonth       = get_post_meta ( $order->get_id (), '_yaadpay_tokef_month', true );
		$expyear        = get_post_meta ( $order->get_id (), '_yaadpay_tokef_year', true );
		$transaction_id = get_post_meta ( $order->get_id (), '_yaadpay_id', true );
        $arg            = get_post_meta ( $order->get_id (), 'yaad_credit_card_payment', true );
        $args_array = array();
        parse_str($arg, $args_array);
        $acode = "";
        $uid = "";
        if (isset($args_array['ACode'])) {
            $acode = $args_array['ACode'];
        }
        if (isset($args_array['UID'])) {
            $uid = $args_array['UID'];
        }

		echo "<script>
				function yaadpay_pay(button) {
					button.disabled      = true;
					var loader           = document.getElementById('chargeLoader');
					loader.style.display = 'block';

					var data = {
						'action':         'yaadpay_token_pay',
						'orderId':        '" . $order->get_id () . "',
						'YaadpayTK':      '" . $YaadpayTK . "',
						'expmonth':       '" . $expmonth . "',
						'expyear':        '" . $expyear . "',
						'transaction_id': '" . $transaction_id . "'
					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response=='success'){
							location.reload();
						}
						else{
							alert(response);
							loader.style.display = 'none';
							button.disabled 	 = false;
						}
					});

			};</script>";

		echo '<div>';
		_e ( 'Transaction Type :','yaad-sarig-payment-gateway-for-wc' );
		_e ( 'Token ', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_token">';
		_e ( 'Transaction Id :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $transaction_id );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_token">';
		_e ( 'Yaadpay Token :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $YaadpayTK );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_acode">';
		_e ( 'Yaadpay ACode :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $acode );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_uid">';
		_e ( 'Yaadpay uid :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $uid );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_expmonth">';
		_e ( 'Card expiration month : ', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $expmonth );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_expyear">';
		_e ( 'Card expiration year :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $expyear );
		echo '</div>';
		if ( $order->get_status () != "on-hold" ) {
			return;
		}
		echo '<div>';
		$pay_btn_str = __("Charge","yaad-sarig-payment-gateway-for-wc");
		echo '<button type="button" class="button" onclick="yaadpay_pay(this);">'. esc_html($pay_btn_str) .'</button>';
		echo '</div>';
		echo '<div id="chargeLoader" style="text-align: center; display:none; margin-top: -100px;">
				<h3>ברגעים אלה מתבצעת עסקה, אנא המתינו ...</h3>
				<div style="border: 5px solid #f3f3f3;border-top-color: rgb(243, 243, 243);border-top-style: solid;	border-top-width: 5px;-webkit-animation: spin 1s linear infinite;animation: spin 1s linear infinite;border-top: 5px solid #555;border-radius: 50%;width: 50px;height: 50px;margin: auto;"></div>
			  </div>';
	}

	/**
	 * @param string $YaadpayTK
	 * @param tb_wc_order $order
	 */
	private function build_token_payment_form2 ( $order, $YaadpayTK ) {

		$expmonth;
		$expyear;
		$transaction_id;
		$arg;

		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';
	
		if ( $screen == 'woocommerce_page_wc-orders') {
			$expmonth = $order->get_meta('_yaadpay_tokef_month', true);
			$expyear = $order->get_meta('_yaadpay_tokef_year', true);
			$transaction_id = $order->get_meta('_yaadpay_id', true);
			$arg = $order->get_meta('yaad_credit_card_payment', true);
		} else {
			$expmonth       = get_post_meta ( $order->get_id (), '_yaadpay_tokef_month', true );
			$expyear        = get_post_meta ( $order->get_id (), '_yaadpay_tokef_year', true );
			$transaction_id = get_post_meta ( $order->get_id (), '_yaadpay_id', true );
			$arg            = get_post_meta ( $order->get_id (), 'yaad_credit_card_payment', true );
		}

        $args_array = array();
        parse_str($arg, $args_array);
        $acode = "";
        $uid = "";
        if (isset($args_array['ACode'])) {
            $acode = $args_array['ACode'];
        }
        if (isset($args_array['UID'])) {
            $uid = $args_array['UID'];
        }

		echo "<script>
				function yaadpay_pay(button) {
					button.disabled      = true;
					var loader           = document.getElementById('chargeLoader');
					loader.style.display = 'block';

					var data = {
						'action':         'yaadpay_token_pay',
						'orderId':        '" . $order->get_id () . "',
						'YaadpayTK':      '" . $YaadpayTK . "',
						'expmonth':       '" . $expmonth . "',
						'expyear':        '" . $expyear . "',
						'transaction_id': '" . $transaction_id . "'
					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response=='success'){
							location.reload();
						}
						else{
							alert(response);
							loader.style.display = 'none';
							button.disabled 	 = false;
						}
					});

			};</script>";

		echo '<div>';
		_e ( 'Transaction Type :','yaad-sarig-payment-gateway-for-wc' );
		_e ( 'Token ', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_token">';
		_e ( 'Transaction Id :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $transaction_id );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_token">';
		_e ( 'Yaadpay Token :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $YaadpayTK );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_acode">';
		_e ( 'Yaadpay ACode :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $acode );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_uid">';
		_e ( 'Yaadpay uid :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $uid );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_expmonth">';
		_e ( 'Card expiration month : ', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $expmonth );
		echo '</div>';
		echo '<div>';
		echo '<label for="yaadpay_expyear">';
		_e ( 'Card expiration year :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $expyear );
		echo '</div>';
		if ( $order->get_status () != "on-hold" ) {
			return;
		}
		echo '<div>';
		$pay_btn_str = __("Charge","yaad-sarig-payment-gateway-for-wc");
		echo '<button type="button" class="button" onclick="yaadpay_pay(this);">'. esc_html($pay_btn_str) .'</button>';
		echo '</div>';
		echo '<div id="chargeLoader" style="text-align: center; display:none; margin-top: -100px;">
				<h3>ברגעים אלה מתבצעת עסקה, אנא המתינו ...</h3>
				<div style="border: 5px solid #f3f3f3;border-top-color: rgb(243, 243, 243);border-top-style: solid;	border-top-width: 5px;-webkit-animation: spin 1s linear infinite;animation: spin 1s linear infinite;border-top: 5px solid #555;border-radius: 50%;width: 50px;height: 50px;margin: auto;"></div>
			  </div>';
	}

/**
	 * @param string $YaadpayTK
	 * @param tb_wc_order $order
	 * 800 - postpone
	 */
	private function build_commit_trans_form ( tb_wc_order $order ) {
		$transaction_id = get_post_meta ( $order->get_id (), '_yaadpay_id', true );
		$transaction_amount = get_post_meta ( $order->get_id (), '_yaadpay_amount', true );
		echo "<script>
				function yaadpay_commit_trans(button) {
					button.disabled = true;

					var data = {
						'action': 'yaadpay_commit_trans',
						'orderId':'" . $order->get_id () . "',
						'transaction_id':'" . $transaction_id . "',

					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response=='success'){
							location.reload();
						}
						else{
							alert(response);
							button.disabled = false;
						}
					});

			};</script>";

		echo '<div>';
		_e ( 'Transaction Type :','yaad-sarig-payment-gateway-for-wc' );
		_e ( 'Postpone', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_token">';
		_e ( 'Transaction Id :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $transaction_id );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_amount">';
		_e ( 'Amount :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $transaction_amount );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaad_helper"><strong>';
		_e ( "Do not change the order's total amount", "yaad-sarig-payment-gateway-for-wc" );
		echo '</strong></label> ';
		echo '</div>';

		if ( $order->get_status () != "on-hold" ) {
			return;
		}
		echo '<div>';
		$pay_btn_str = __("Charge","yaad-sarig-payment-gateway-for-wc");
		echo '<button type="button" class="button" onclick="yaadpay_commit_trans(this);">'. esc_html($pay_btn_str) .'</button>';
		echo '</div>';
	}

	private function build_commit_trans_form2 ($post_or_order_object) {
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		//$transaction_id = get_post_meta ($order->get_id (), '_yaadpay_id', true);
		//$transaction_amount = get_post_meta ($order->get_id (), '_yaadpay_amount', true);

		$transaction_id;
		$transaction_amount;

		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

		if ($screen == 'woocommerce_page_wc-orders') {
			$transaction_id = $order->get_meta('_yaadpay_id', true);
			$transaction_amount = $order->get_meta('_yaadpay_amount', true);
		} else {
			$transaction_id = get_post_meta ($order->get_id (), '_yaadpay_id', true);
			$transaction_amount = get_post_meta ($order->get_id (), '_yaadpay_amount', true);
		}

		echo "<script>
				function yaadpay_commit_trans(button) {
					button.disabled = true;

					var data = {
						'action': 'yaadpay_commit_trans',
						'orderId':'" . $order->get_id () . "',
						'transaction_id':'" . $transaction_id . "',

					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response=='success'){
							location.reload();
						}
						else{
							alert(response);
							button.disabled = false;
						}
					});

			};</script>";

		echo '<div>';
		_e ( 'Transaction Type :','yaad-sarig-payment-gateway-for-wc' );
		_e ( 'Postpone', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_token">';
		_e ( 'Transaction Id :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $transaction_id );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_amount">';
		_e ( 'Amount :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		echo esc_attr ( $transaction_amount );
		echo '</div>';

		echo '<div>';
		echo '<label for="yaad_helper"><strong>';
		_e ( "Do not change the order's total amount", "yaad-sarig-payment-gateway-for-wc" );
		echo '</strong></label> ';
		echo '</div>';

		if ( $order->get_status () != "on-hold" ) {
			return;
		}
		echo '<div>';
		$pay_btn_str = __("Charge","yaad-sarig-payment-gateway-for-wc");
		echo '<button type="button" class="button" onclick="yaadpay_commit_trans(this);">'. esc_html($pay_btn_str) .'</button>';
		echo '</div>';
	}

	private function build_missing_token_form( tb_wc_order $order ) {
		$arg=get_post_meta($order->get_id(),'yaad_credit_card_payment',true);
		$args_array= array();
		parse_str($arg,$args_array);
		if ( empty( $args_array['Id']) ) {		return;		}

		WC_Gateway_Yaadpay::log ( '[INFO]: CCode : ' . $args_array['CCode'] . ' (should NOT be 800)' );

		echo '<div>';
		if ( $args_array['CCode']==800){
			return ; //_e( 'Postponed', 'yaad-sarig-payment-gateway-for-wc' );
		}else{
			_e( 'Transaction Type : ', 'yaad-sarig-payment-gateway-for-wc' );
			_e( 'Regular payment', 'yaad-sarig-payment-gateway-for-wc' );
		}

		$token_btn_str = __("Get Token","yaad-sarig-payment-gateway-for-wc");

		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_id">';
		_e( 'Transaction Id :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		$trans_id = $args_array['Id'];
		echo esc_attr($trans_id);
		echo '</div>';
		echo '<div>';
		echo '<button type="button" class="button" onclick="yaadpay_get_token();">'. esc_html($token_btn_str) .'</button>';
		echo '</div>';


		echo '<script>
			function yaadpay_get_token(p1, p2) {
				var yaadpay_id = jQuery("#yaadpay_id").val();
		        var data = {
            		"action" : "yaadpay_get_token_data",
            		"transaction_id" : "'.$trans_id.'",
            		"order_id": "'.$order->get_id().'"
        		};
				jQuery.post(ajaxurl, data, function(response) {
					console.log(response);
					if (response=="success"){
						location.reload();
					}
					else{
						alert(response);
					}
				});
			};
			</script>';
	}

	private function build_missing_token_form2( $post_or_order_object ) {

		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		
		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

		$arg;
	
		if ( $screen == 'woocommerce_page_wc-orders') {
			$arg = $order->get_meta('yaad_credit_card_payment', true);
		} else {
			$arg = get_post_meta($order->get_id(),'yaad_credit_card_payment',true);
		}

		$args_array= array();
		parse_str($arg,$args_array);
		if ( empty( $args_array['Id']) ) {		return;		}

		WC_Gateway_Yaadpay::log('[INFO]: CCode : ' . $args_array['CCode'] . ' (should NOT be 800)', array('source' => 'yaad-sarig-payment-gateway-for-wc'));

		echo '<div>';
		if ( $args_array['CCode']==800){
			return ; //_e( 'Postponed', 'yaad-sarig-payment-gateway-for-wc' );
		}else{
			_e( 'Transaction Type : ', 'yaad-sarig-payment-gateway-for-wc' );
			_e( 'Regular payment', 'yaad-sarig-payment-gateway-for-wc' );
		}

		$token_btn_str = __("Get Token","yaad-sarig-payment-gateway-for-wc");

		echo '</div>';

		echo '<div>';
		echo '<label for="yaadpay_id">';
		_e( 'Transaction Id :', 'yaad-sarig-payment-gateway-for-wc' );
		echo '</label> ';
		$trans_id = $args_array['Id'];
		echo esc_attr($trans_id);
		echo '</div>';
		echo '<div>';
		echo '<button type="button" class="button" onclick="yaadpay_get_token();">'. esc_html($token_btn_str) .'</button>';
		echo '</div>';


		echo '<script>
			function yaadpay_get_token(p1, p2) {
				var yaadpay_id = jQuery("#yaadpay_id").val();
		        var data = {
            		"action" : "yaadpay_get_token_data",
            		"transaction_id" : "'.$trans_id.'",
            		"order_id": "'.$order->get_id().'"
        		};
				jQuery.post(ajaxurl, data, function(response) {
					console.log(response);
					if (response=="success"){
						location.reload();
					}
					else{
						alert(response);
					}
				});
			};
			</script>';
	}

	private function update_subscription_parent_order( tb_wc_order $order ) {
		$btn_str = __("Update Parent Oder","yaad-sarig-payment-gateway-for-wc");
		echo '<div><br>';
		echo '<button type="button" class="button" onclick="yaadpay_update_subscription_parent_order();">'. esc_html($btn_str) .'</button>';
		echo '</div>';
		echo '<script>
			function yaadpay_update_subscription_parent_order(p1, p2) {
		        var data = {
            		"action" : "yaadpay_update_subscription_parent",
            		"order_id": "'.$order->get_id().'"
        		};
				jQuery.post(ajaxurl, data, function(response) {
					console.log(response);
					if (response=="success"){
						location.reload();
					}
					else{
						alert(response);
					}
				});
			};
			</script>';
	}

}



