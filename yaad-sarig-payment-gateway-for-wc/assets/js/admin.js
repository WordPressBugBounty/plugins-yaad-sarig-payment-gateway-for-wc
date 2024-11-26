jQuery(document).ready(function () {
  jQuery("ul.tabs li").click(function () {
    var tab_id = jQuery(this).attr("data-tab");

    jQuery("ul.tabs li").removeClass("current");
    jQuery(".tab-content").removeClass("current");

    jQuery(this).addClass("current");
    jQuery("#" + tab_id).addClass("current");
    window.location.hash = tab_id;
  });

  if (window.location.hash != "") {
    jQuery("ul.tabs li").removeClass("current");
    jQuery(".tab-content").removeClass("current");
    jQuery(window.location.hash).addClass("current");
    jQuery(window.location.hash + "-tab").addClass("current");
  }
});

function view_log() {
  var data = {
    action: "yaadpay_view_log",
    security: my_plugin_vars.nonce,
  };

  jQuery.post(my_plugin_vars.ajax_url, data, function (response) {
    var logDisplay = document.getElementById("log-display");
    if (response.success) {
      logDisplay.innerHTML = response.data;
    } else {
      logDisplay.innerHTML = "Error: " + response.data;
    }
  });
}
function delete_log() {
  var data = {
    action: "yaadpay_delete_log",
    security: my_plugin_vars.nonce,
  };

  jQuery.post(my_plugin_vars.ajax_url, data, function (response) {
    var logDisplay = document.getElementById("log-display");
    if (response.success) {
      logDisplay.innerHTML = "Logs deleted successfully.";
    } else {
      logDisplay.innerHTML = "Error: " + response.data;
    }
  });
}

var pluginUrl = my_plugin_vars.pluginUrl;
var ajaxUrl = pluginUrl + "10bit-woocommerce-infra/apple-pay-ajax-actions.php";

function add_apple_file() {
  jQuery("#update_apple").prop("disabled", false);
  jQuery.ajax({
    type: "POST",
    url: ajaxUrl,
    dataType: "json",
    data: { action: "copy" },
  });
}
function update_apple_file() {
  jQuery.ajax({
    type: "POST",
    url: ajaxUrl,
    dataType: "json",
    data: { action: "update" },
  });
}
function remove_apple_file() {
  jQuery("#update_apple").prop("disabled", true);
  jQuery.ajax({
    type: "POST",
    url: ajaxUrl,
    dataType: "json",
    data: { action: "delete" },
  });
}

jQuery(document).ready(function () {
  const invoiceOptions = document.getElementById(
    "woocommerce_yaadpay_invoice_options"
  );
  var dataInInvoice = document.getElementById(
    "woocommerce_yaadpay_data_in_invoice"
  );
  var table = document.getElementById("advanced");
  var rows = table.getElementsByTagName("tr");
  if (invoiceOptions.value == "without_invoice") {
    rows[2].style.display = "none";
    rows[3].style.display = "none";
    rows[4].style.display = "none";
    rows[5].style.display = "none";
    rows[6].style.display = "none";
  } else if (invoiceOptions.value == "yaad_invoice") {
    rows[2].style.display = "";
    rows[3].style.display = "none";
    rows[4].style.display = "none";
    rows[5].style.display = "none";
    rows[6].style.display = "none";
  } else if (invoiceOptions.value == "hyp_invoice") {
    rows[2].style.display = "";
    rows[3].style.display = "";
    rows[4].style.display = "";
    rows[5].style.display = "";
    rows[6].style.display = "";
  }

  document
    .getElementById("woocommerce_yaadpay_invoice_options")
    .addEventListener("change", function () {
      if (invoiceOptions.value == "without_invoice") {
        dataInInvoice.checked = false;
        rows[2].style.display = "none";
        rows[3].style.display = "none";
        rows[4].style.display = "none";
        rows[5].style.display = "none";
        rows[6].style.display = "none";
      } else if (invoiceOptions.value == "yaad_invoice") {
        dataInInvoice.checked = true;
        rows[2].style.display = "";
        rows[3].style.display = "none";
        rows[4].style.display = "none";
        rows[5].style.display = "none";
        rows[6].style.display = "none";
      } else if (invoiceOptions.value == "hyp_invoice") {
        dataInInvoice.checked = true;
        rows[2].style.display = "";
        rows[3].style.display = "";
        rows[4].style.display = "";
        rows[5].style.display = "";
        rows[6].style.display = "";
      }
    });

  const iframe = document.getElementById("woocommerce_yaadpay_yaad_iframe");
  if (!iframe.checked) {
    jQuery("#display").find("tr:eq(5)").remove();
    jQuery("#update_apple").remove();
    remove_apple_file();
  }
  const applePay = document.getElementById("woocommerce_yaadpay_apple_pay");
  if (applePay) {
    jQuery("#display")
      .find("tr:eq(5)")
      .append(
        "<button id='update_apple' title='update apple file' onclick='update_apple_file()'>update</button>"
      );
    if (applePay.checked) {
      add_apple_file();
    } else {
      remove_apple_file();
    }
  }
});
