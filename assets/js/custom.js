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
        window.open( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=settings-pro-checkbox', '_blank' );

    } );

    jQuery( document ).on( 'click', '#woocommerce_coinbase_commerce_gateway_product_icons', function( e ) {

        e.preventDefault();
        window.open( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=settings-pro-checkbox', '_blank' );

    } );

    jQuery( document ).on( {
        click( e ) {
            e.preventDefault();
            window.open( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=settings-pro-input', '_blank' );
        },
        input( e ) {
            e.preventDefault();
        },
        keypress( e ) {
            e.preventDefault();
        }
    }, '#woocommerce_coinbase_commerce_gateway_product_ids, #woocommerce_coinbase_commerce_gateway_total_price_to_checkout, #woocommerce_coinbase_commerce_gateway_order_status' );

    // How to setup
    var targetRow = jQuery( 'input#woocommerce_coinbase_commerce_gateway_enabled' ).closest( 'tr' );
    var newRow = jQuery( 
        `<tr valign="top">
            <th scope="row" class="titledesc">
                <label for="new_field">How to Setup?</label>
            </th>
            <td class="forminp">
                <a href="https://coderpress.co/docs/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=how-to-setup" target="_blank">
                    Documentation
                </a>
            </td>
        </tr>` );
    targetRow.after( newRow );

    // Pro Product Page
    var targetRow = jQuery( 'input#woocommerce_coinbase_commerce_gateway_product_icon' ).closest( 'tr' );
    var newRow = jQuery( 
        `<tr valign="top">
            <th scope="row" class="titledesc">
                <label for="new_field">Pro Product Page Preview</label>
            </th>
            <td class="forminp">
                <a href="https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=product-preview" target="_blank">
                    <img src="${ccfwc.images}/product-page.png" width="400px" />
                </a>
            </td>
        </tr>` );
    targetRow.after( newRow );

    // Pro Product Icons
    var targetRow = jQuery( 'input#woocommerce_coinbase_commerce_gateway_product_icons' ).closest( 'tr' );
    var newRow = jQuery( 
        `<tr valign="top">
            <th scope="row" class="titledesc">
                <label for="new_field">Pro Product Page Preview</label>
            </th>
            <td class="forminp">
                <a href="https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=shop-preview" target="_blank">
                    <img src="${ccfwc.images}/shop-page.png" width="400px" />
                </a>
            </td>
        </tr>` );
    targetRow.after( newRow );

    // Pro Demo
    var targetRow = jQuery( 'input#woocommerce_coinbase_commerce_gateway_order_status' ).closest( 'tr' );
    var newRow = jQuery( 
        `<tr valign="top">
            <th scope="row" class="titledesc">
                <label for="new_field">Launch Demo 🚀</label>
            </th>
            <td class="forminp">
                <a href="https://coinbase.coderpress.co/shop/" target="_blank">
                    <b>Try Pro Demo 🚀</b>
                </a>
            </td>
        </tr>` );
    targetRow.after( newRow );

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
