<?php
defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Coinbase Payments Blocks integration
 *
 * @since 1.0.3
 */
final class CoinbaseGatewayBlock extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var CoinbaseCommerceWC
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'coinbase_commerce_gateway';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_coinbase_commerce_gateway_settings', [] );
		$this->gateway  = new CoinbaseCommerceWC();
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {

		$script_path       = '/assets/blocks/frontend/blocks.js';
		$script_asset_path = CGFWC_PLUGIN_DIR_PATH . '/assets/blocks/frontend/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => CGFWC_VERSION
			);
		$script_url        = CCFWC_PLUGIN_DIR_URL . $script_path;

		wp_register_script(
			'wc-dummy-payments-cb',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		return [ 'wc-dummy-payments-cb' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}
}