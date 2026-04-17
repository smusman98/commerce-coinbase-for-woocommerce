( function( $ ) {
	$( document ).ready( function() {
		if ( ! $( 'input#woocommerce_coinbase_commerce_gateway_enabled' ).length ) {
			return;
		}

		var proBaseUrl = 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=modal&utm_campaign=upgrade-to-pro';
		var currentModalSource = 'general_modal';

		function getTrackedProUrl( sourceKey ) {
			return proBaseUrl + '&utm_content=' + encodeURIComponent( sourceKey || 'general_modal' );
		}

		// PRO badge after checkbox label text / after text inputs (not in title).
		function appendProBadgesAfterFields() {
			var badge = '<span class="ccfwc-pro-badge ccfwc-pro-badge--after-control" aria-hidden="true">PRO</span>';
			var $icon = $( '#woocommerce_coinbase_commerce_gateway_product_icon' );
			var $shop = $( '#woocommerce_coinbase_commerce_gateway_product_icons' );
			if ( $icon.length && $icon.prop( 'disabled' ) ) {
				var $li = $icon.closest( 'label' );
				if ( $li.length && ! $li.find( '.ccfwc-pro-badge--after-control' ).length ) {
					$li.append( badge );
				}
			}
			if ( $shop.length && $shop.prop( 'disabled' ) ) {
				var $ls = $shop.closest( 'label' );
				if ( $ls.length && ! $ls.find( '.ccfwc-pro-badge--after-control' ).length ) {
					$ls.append( badge );
				}
			}
			$( '#woocommerce_coinbase_commerce_gateway_product_ids, #woocommerce_coinbase_commerce_gateway_total_price_to_checkout, #woocommerce_coinbase_commerce_gateway_order_status' ).each( function() {
				var $inp = $( this );
				if ( ! $inp.prop( 'disabled' ) ) {
					return;
				}
				if ( $inp.next( '.ccfwc-pro-badge--after-control' ).length ) {
					return;
				}
				$inp.after( badge );
			} );
		}
		appendProBadgesAfterFields();

		if ( ! $( '#ccfwc-pro-popup-overlay' ).length ) {
			$( 'body' ).append(
				'<div id="ccfwc-pro-popup-overlay" class="ccfwc-pro-popup-overlay" style="display:none;">' +
					'<div class="ccfwc-pro-popup-content" role="dialog" aria-modal="true" aria-label="Unlock Pro Access">' +
						'<button type="button" class="ccfwc-pro-popup-close" aria-label="Close">❌</button>' +
						'<div class="ccfwc-pro-popup-inner">' +
							'<div class="ccfwc-pro-popup-rocket">🚀</div>' +
							'<h2>Unlock Your Pro Access</h2>' +
							'<p>Your premium license is ready — activate now and enjoy full access!</p>' +
							'<div class="ccfwc-pro-popup-offer">✨ Special intro offer - limited time only</div>' +
							'<a href="' + getTrackedProUrl( currentModalSource ) + '" target="_blank" rel="noopener noreferrer" class="ccfwc-pro-popup-upgrade ccfwc-pro-upgrade-link">Upgrade to Pro \u2192</a>' +
							'<button type="button" class="ccfwc-pro-popup-dismiss">No thanks, maybe later.</button>' +
							'<div class="ccfwc-divider"></div>' +
							'<div class="ccfwc-trust-badges">' +
								'<div class="ccfwc-trust-badge">' +
									'<div class="ccfwc-trust-icon-wrapper">' +
										'<span class="ccfwc-trust-icon dashicons dashicons-admin-site"></span>' +
									'</div>' +
									'<div class="ccfwc-trust-text">' +
										'<div class="ccfwc-trust-text-primary">Trusted by</div>' +
										'<div class="ccfwc-trust-text-secondary">3K+ website owners</div>' +
									'</div>' +
								'</div>' +
								'<div class="ccfwc-trust-badge">' +
									'<div class="ccfwc-trust-icon-wrapper">' +
										'<span class="ccfwc-trust-icon dashicons dashicons-star-filled"></span>' +
									'</div>' +
									'<div class="ccfwc-trust-text">' +
										'<div class="ccfwc-trust-text-primary">Rated 4.3/5</div>' +
										'<div class="ccfwc-trust-text-secondary">by customers</div>' +
									'</div>' +
								'</div>' +
								'<div class="ccfwc-trust-badge">' +
									'<div class="ccfwc-trust-icon-wrapper">' +
										'<span class="ccfwc-trust-icon dashicons dashicons-shield"></span>' +
									'</div>' +
									'<div class="ccfwc-trust-text">' +
										'<div class="ccfwc-trust-text-primary">14-day</div>' +
										'<div class="ccfwc-trust-text-secondary">money-back guarantee</div>' +
									'</div>' +
								'</div>' +
							'</div>' +
							'<p class="ccfwc-footer-text">Thank you for choosing Coinbase Commerce!</p>' +
						'</div>' +
					'</div>' +
				'</div>'
			);
		}

		var $proCheckboxRows = $(
			'#woocommerce_coinbase_commerce_gateway_product_icon, #woocommerce_coinbase_commerce_gateway_product_icons'
		).closest( 'tr' );
		$proCheckboxRows.addClass( 'ccfwc-pro-trigger-row' );
		$( '#woocommerce_coinbase_commerce_gateway_product_icon' ).closest( 'tr' ).attr( 'data-modal-source', 'product-page-modal' );
		$( '#woocommerce_coinbase_commerce_gateway_product_icons' ).closest( 'tr' ).attr( 'data-modal-source', 'shop-page-modal' );

		var $proTextInputs = $( '#woocommerce_coinbase_commerce_gateway_product_ids, #woocommerce_coinbase_commerce_gateway_total_price_to_checkout, #woocommerce_coinbase_commerce_gateway_order_status' );
		$proTextInputs.closest( 'tr' ).addClass( 'ccfwc-pro-trigger-row' );
		$( '#woocommerce_coinbase_commerce_gateway_product_ids' ).closest( 'tr' ).attr( 'data-modal-source', 'allowed-products-modal' );
		$( '#woocommerce_coinbase_commerce_gateway_total_price_to_checkout' ).closest( 'tr' ).attr( 'data-modal-source', 'min-checkout-modal' );
		$( '#woocommerce_coinbase_commerce_gateway_order_status' ).closest( 'tr' ).attr( 'data-modal-source', 'order-status-modal' );

		function openProPopup( sourceKey ) {
			currentModalSource = sourceKey || 'general_modal';
			$( '.ccfwc-pro-upgrade-link' ).attr( 'href', getTrackedProUrl( currentModalSource ) );
			$( '#ccfwc-pro-popup-overlay' ).fadeIn( 180 );
			$( 'body' ).addClass( 'ccfwc-no-scroll' );
		}

		function closeProPopup() {
			$( '#ccfwc-pro-popup-overlay' ).fadeOut( 180 );
			$( 'body' ).removeClass( 'ccfwc-no-scroll' );
		}

		$( document ).on( 'click', '.ccfwc-pro-trigger-row, .ccfwc-pro-badge', function( e ) {
			if ( $( e.target ).closest( 'a' ).length ) {
				return;
			}
			e.preventDefault();
			var sourceKey = $( this ).closest( 'tr' ).attr( 'data-modal-source' ) || 'general_modal';
			openProPopup( sourceKey );
		} );

		$( document ).on( 'click', '.ccfwc-pro-popup-close, .ccfwc-pro-popup-dismiss', function( e ) {
			e.preventDefault();
			e.stopPropagation();
			closeProPopup();
		} );

		$( document ).on( 'click', '#ccfwc-pro-popup-overlay', function( e ) {
			if ( e.target === this ) {
				closeProPopup();
			}
		} );

		// How to setup
		var howRowTarget = $( 'input#woocommerce_coinbase_commerce_gateway_enabled' ).closest( 'tr' );
		var howRow = $(
			'<tr valign="top">' +
				'<th scope="row" class="titledesc"><label>How to Setup?</label></th>' +
				'<td class="forminp">' +
					'<a href="https://coderpress.co/docs/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=how-to-setup" target="_blank" rel="noopener noreferrer">Documentation</a>' +
				'</td>' +
			'</tr>'
		);
		howRowTarget.after( howRow );

		// Pro Product Page preview
		var ppTarget = $( 'input#woocommerce_coinbase_commerce_gateway_product_icon' ).closest( 'tr' );
		var ppRow = $(
			'<tr valign="top">' +
				'<th scope="row" class="titledesc"><label>Pro Product Page Preview</label></th>' +
				'<td class="forminp">' +
					'<a href="https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=integration-page&utm_campaign=pro-product-page-preview" target="_blank" rel="noopener noreferrer">' +
						'<img src="' + ccfwc.images + 'product-page.png" width="400" alt="" />' +
					'</a>' +
				'</td>' +
			'</tr>'
		);
		ppTarget.after( ppRow );

		// Pro Shop Page preview
		var shopTarget = $( 'input#woocommerce_coinbase_commerce_gateway_product_icons' ).closest( 'tr' );
		var shopRow = $(
			'<tr valign="top">' +
				'<th scope="row" class="titledesc"><label>Pro Shop Page Preview</label></th>' +
				'<td class="forminp">' +
					'<a href="https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=integration-page&utm_campaign=pro-shop-page-preview" target="_blank" rel="noopener noreferrer">' +
						'<img src="' + ccfwc.images + 'shop-page.png" width="400" alt="" />' +
					'</a>' +
				'</td>' +
			'</tr>'
		);
		shopTarget.after( shopRow );

		// Launch demo
		var demoTarget = $( 'input#woocommerce_coinbase_commerce_gateway_order_status' ).closest( 'tr' );
		var demoRow = $(
			'<tr valign="top">' +
				'<th scope="row" class="titledesc"><label>Launch Demo 🚀</label></th>' +
				'<td class="forminp">' +
					'<a href="https://coinbase.coderpress.co/shop/" target="_blank" rel="noopener noreferrer"><b>Try Pro Demo 🚀</b></a>' +
				'</td>' +
			'</tr>'
		);
		demoTarget.after( demoRow );

		// Sidebar: Unlock Pro + Help
		var $settingsTable = $( 'table.form-table' );
		if ( $settingsTable.length ) {
			$settingsTable.wrap( '<div class="ccfwc-settings-grid"><div class="ccfwc-settings-main"></div></div>' );
			var sidebarHtml =
				'<aside class="ccfwc-sidebar">' +
					'<div class="ccfwc-card ccfwc-pro-card">' +
						'<div class="ccfwc-pro-header">' +
							'<h3>Unlock Pro Features</h3>' +
							'<span class="ccfwc-pro-badge">PRO</span>' +
						'</div>' +
						'<p>Join 3,000+ stores accepting Bitcoin, ETH, USDC &amp; more. Zero commission from CoderPress — same Pro highlights as on our product page:</p>' +
						'<ul class="ccfwc-pro-features">' +
							'<li><span class="dashicons dashicons-yes"></span>Attractive crypto icons on the Shop page</li>' +
							'<li><span class="dashicons dashicons-yes"></span>Individual crypto pricing on single Product pages</li>' +
							'<li><span class="dashicons dashicons-yes"></span>Classic checkout, HPOS &amp; block-based checkout support</li>' +
							'<li><span class="dashicons dashicons-yes"></span>Restrict which products can use Coinbase Commerce</li>' +
							'<li><span class="dashicons dashicons-yes"></span>Set a minimum checkout amount for crypto payments</li>' +
							'<li><span class="dashicons dashicons-yes"></span>Specify order status when payment is received (completed, processing, etc.)</li>' +
						'</ul>' +
						'<a href="https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=side-banner&utm_campaign=upgrade-to-pro" target="_blank" rel="noopener noreferrer" class="shine-button ccfwc-btn-primary">Upgrade to Pro</a>' +
					'</div>' +
					'<div class="ccfwc-card">' +
						'<h3>Need Help?</h3>' +
						'<p>Check out our documentation or contact support if you have any questions.</p>' +
						'<a href="https://coderpress.co/docs/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=side-banner&utm_campaign=view-documentation" target="_blank" rel="noopener noreferrer" class="ccfwc-btn-secondary">View Documentation</a>' +
					'</div>' +
				'</aside>';
			$( '.ccfwc-settings-grid' ).append( sidebarHtml );
		}
	} );
}( jQuery ) );

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
