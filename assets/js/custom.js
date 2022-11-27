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

    });

    jQuery( document ).on( 'click', '#woocommerce_coinbase_commerce_gateway_product_icon', function( e ) {

        e.preventDefault();
        window.open( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/', '_blank' );

    } );

    jQuery( document ).on( 'click', '#woocommerce_coinbase_commerce_gateway_product_icons', function( e ) {

        e.preventDefault();
        window.open( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/', '_blank' );

    } );

    jQuery( document ).on( {
        click( e ) {
            e.preventDefault();
            window.open( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/', '_blank' );
        },
        input( e ) {
            e.preventDefault();
        },
        keypress( e ) {
            e.preventDefault();
        }
    }, '#woocommerce_coinbase_commerce_gateway_product_ids, #woocommerce_coinbase_commerce_gateway_total_price_to_checkout, #woocommerce_coinbase_commerce_gateway_order_status' );

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
