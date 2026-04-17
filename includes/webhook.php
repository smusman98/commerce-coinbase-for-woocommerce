<?php
/**
 * Legacy Coinbase Commerce REST hook (charge:confirmed) lived here.
 * Coinbase Business confirms payments via the WooCommerce endpoint:
 * ?wc-api=CoinbaseCommerceWC (see gateway settings for the full URL).
 *
 * @package CoinbaseCommerceWC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
