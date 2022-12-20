jQuery(function ($) {
  const getURLParams = window.location.search;
  var selectedServiceType = Cookies.get("service_type");
  var serviceTime = Cookies.get("service_time");
  var asapLabel = wfsCheckoutParams.asap_label;
  //Append the service type to the URL
  if (getURLParams == "" || getURLParams == null || getURLParams == undefined) {
    let checkoutUrl = wfsCheckoutParams.checkout_url;
    window.location = checkoutUrl + "?type=" + selectedServiceType;
  }

  if (serviceTime == asapLabel) {
    checkoutASAPOption();
  }

  //Set the selected service type in the option
  if (serviceTime !== asapLabel && $(".checkout-asap-block").length > 0) {
    $(".checkout-asap-block")
      .find('a[href="#' + selectedServiceType + '_asap"]')
      .removeClass("active");
    $(".checkout-asap-block")
      .find('a[href="#' + selectedServiceType + '_schedule"]')
      .addClass("active");
    $("#wfs_checkout_fields")
      .find("#" + selectedServiceType + "_asap")
      .removeClass("active");
    $("#wfs_checkout_fields")
      .find("#" + selectedServiceType + "_schedule")
      .addClass("active");
  }

  $("body").on("click", ".checkout-asap-block a", function (e) {
    e.preventDefault();
    let serviceTime = $(this).attr("href");
    let serviceASAP = serviceTime.includes("asap");
    if (serviceASAP) {
      Cookies.set("service_time", asapLabel);
      checkoutASAPOption();
    } else {
      jQuery("#wfs_service_time option[value='" + asapLabel + "']").remove();
    }
  });

  //Add ASAP option to the service time dropdown in the checkout page
  function checkoutASAPOption() {
    let checkASAPOptionExists = jQuery(
      "#wfs_service_time option[value='" + asapLabel + "']"
    ).length;

    if (checkASAPOptionExists == 0) {
      $("<option/>")
        .val(asapLabel)
        .text(asapLabel)
        .appendTo("#wfs_service_time");
    }
    $("#wfs_service_time").val(asapLabel);
  }
});
