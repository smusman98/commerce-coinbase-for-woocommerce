jQuery(document).ready( function() {
    jQuery("#click-me").click( function(e) {
        e.preventDefault();
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url : ajaxurl,
            data : {action: "custom_ajax"},
            success: function(response) {
                if(response.type == "success") {
                    alert(response);
                }
                else {
                    alert(response);
                }
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
