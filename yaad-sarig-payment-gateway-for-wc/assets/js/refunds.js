jQuery(document).ready(function() {
    //tb_add_yaadpay_refund_btns();
    //jQuery('#refund-manager-password').on('change', tb_do_refund);
});

function tb_add_yaadpay_refund_btns() {
    var input = '<input type="text" id="refund-manager-password" value="">';
    var label = '<label for="refund-manager-password">' + yaadpay.refunds_manager_password + '</label>';
    var row = '<tr><td class="label">' + label + '</td><td class="total">' + input + '</td></tr>'
    var $table = jQuery('#refund_reason').parentsUntil('.wc-order-totals').parent().find('tbody');
    $table.append(row);
}

function tb_do_refund() {
    var $input = jQuery(this);
    var data = {
        'action': 'tb_save_refund_manager_key',
        'manager_key': $input.val()
    };

    var $container = jQuery('#woocommerce-order-items');

    jQuery.ajax({
        type: 'POST',
        url: woocommerce_admin_meta_boxes.ajax_url,
        data: data,
        beforeSend: function() {
            $container.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            $input.prop('disabled', true);
            jQuery('button.do-api-refund').prop('disabled', true);
        },
        complete: function() {
            $container.unblock();
            $input.prop('disabled', false);
            jQuery('button.do-api-refund').prop('disabled', false);
        }
    });
}