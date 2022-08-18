jQuery(document).ready( function() {
    jQuery("#ccfwc-pro-notice").click( function(e) {
        e.preventDefault();
        jQuery.ajax({
            type : "POST",
            url : ajaxurl,
            data : {action: "ccfwc-exit-pro-notice"},
            success: function(response) {
                jQuery( '.ccfwc-pro-notice-main .notice-dismiss' ).click();
            }
        })

    })

});

function CoinbaceCommerceCallBack() {
    BuyWithCrypto.registerCallback('onSuccess', function(e){
        alert('onSuccess');
    });

    BuyWithCrypto.registerCallback('onFailure', function(e){
        alert('onFailure');
    });

    BuyWithCrypto.registerCallback('onPaymentDetected', function(e){
        alert('onPaymentDetected');
    });
}
