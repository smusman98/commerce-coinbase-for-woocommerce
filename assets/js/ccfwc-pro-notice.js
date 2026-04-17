jQuery(document).ready(
    function ( $ ) {
        'use strict';

        // Dismiss notice and persist for this user.
        $(document).on(
            'click', '.ccfwc-pro-conversion-notice .notice-dismiss', function ( e ) {
                e.preventDefault();

                var $notice = $(this).closest('.ccfwc-pro-conversion-notice');

                $.ajax(
                    {
                        url: ccfwcProNotice.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'ccfwc_dismiss_pro_notice',
                            nonce: ccfwcProNotice.nonce,
                        },
                        success: function ( response ) {
                            if (response && response.success ) {
                                $notice.fadeOut(
                                    300, function () {
                                        $(this).remove();
                                    } 
                                );
                            }
                        },
                    } 
                );
            } 
        );

        // Copy coupon code button.
        $(document).on(
            'click', '.ccfwc-coupon-code-btn', function ( e ) {
                e.preventDefault();

                var $btn        = $(this);
                var couponCode  = $btn.data('coupon') || 'LIFETIME25';
                var $temp       = $('<textarea>');

                $('body').append($temp);
                $temp.val(couponCode).select();
                document.execCommand('copy');
                $temp.remove();

                $btn.addClass('copied');
                setTimeout(
                    function () {
                        $btn.removeClass('copied');
                    }, 2000 
                );
            } 
        );
    } 
);

