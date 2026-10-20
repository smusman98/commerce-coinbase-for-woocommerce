<?php
/**
 * Plugin Name: Coinbase Commerce For WooCommerce
 * Plugin URI: https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=plugin-uri
 * Author: CoderPress
 * Description: Coinbase Commerce For WooCommerce, Let your customer checkout with well known payment gateway.
 * Version: 2.1.0
 * Author: CoderPress
 * Author URI: https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=author-uri
 * Requires Plugins: woocommerce
 * License: GPL v2 or later
 * Stable tag: 2.1.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: woocommerce, coinbase commerce, payment, payment gateway, commerce, product
 * @author Syed Muhammad Usman
 * @url https://www.fiverr.com/mr_ussi
 * @version 2.1.0
 */

if ( ! function_exists( 'ccfw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function ccfw_fs() {
        global $ccfw_fs;

        if ( ! isset( $ccfw_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $ccfw_fs = fs_dynamic_init( array(
                'id'                  => '10657',
                'slug'                => 'coinbase-commerce-for-woocommerce',
                'type'                => 'plugin',
                'public_key'          => 'pk_7fa95bbb71a798b8cd3764dc762f4',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'first-path'     => 'admin.php?page=wc-settings&tab=checkout&section=coinbase_commerce_gateway&from=WCADMIN_PAYMENT_SETTINGS',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $ccfw_fs;
    }

    // Init Freemius.
    ccfw_fs();
    // Signal that SDK was initiated.
    do_action( 'ccfw_fs_loaded' );
}

if ( ! defined( 'ABSPATH' ) ) exit;

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

define( 'CCFWC_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CGFWC_VERSION', '2.1.0' );

if ( ! defined( 'CCFWC_PLUGIN_FILE' ) ) {
	define( 'CCFWC_PLUGIN_FILE', __FILE__ );
}

/**
 * Whether WooCommerce is active (single site or network).
 *
 * @return bool
 */
function ccfwc_is_woocommerce_active() {
	if ( class_exists( 'WooCommerce' ) ) {
		return true;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		return true;
	}

	if ( is_multisite() && is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
		return true;
	}

	return false;
}

/**
 * Prevent activation when WooCommerce is not active.
 */
function ccfwc_activation_requires_woocommerce() {
	if ( ccfwc_is_woocommerce_active() ) {
		return;
	}

	deactivate_plugins( plugin_basename( CCFWC_PLUGIN_FILE ) );

	wp_die(
		wp_kses_post(
			sprintf(
				/* translators: %s: WooCommerce plugin name link */
				__( 'Coinbase Commerce For WooCommerce requires %s to be installed and active.', 'cgfwc' ),
				'<a href="' . esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ) . '">WooCommerce</a>'
			)
		),
		esc_html__( 'Plugin Activation Error', 'cgfwc' ),
		array( 'back_link' => true )
	);
}
register_activation_hook( CCFWC_PLUGIN_FILE, 'ccfwc_activation_requires_woocommerce' );

/**
 * True when viewing WooCommerce → Settings → Payments → Coinbase Commerce.
 *
 * @return bool
 */
function ccfwc_is_wc_coinbase_settings_page() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
	$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

	return 'wc-settings' === $page && 'checkout' === $tab && 'coinbase_commerce_gateway' === $section;
}

// New Year / Pro promo banner on gateway settings (same pattern as NOWPayments).
add_action(
	'admin_notices',
	function () {
		$dismissed = get_user_meta( get_current_user_id(), 'ccfwc_pro_notice_dismissed_2026', true );

		if ( $dismissed || ! current_user_can( 'manage_options' ) || ! ccfwc_is_wc_coinbase_settings_page() ) {
			return;
		}

		$pro_url = 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=admin_notice&utm_campaign=new_year_2026&coupon=LIFETIME25';
		?>
		<div class="ccfwc-pro-conversion-notice notice notice-info is-dismissible" data-dismiss-action="ccfwc_dismiss_pro_notice">
			<div class="ccfwc-pro-notice-banner">
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'cgfwc' ); ?></span>
				</button>

				<div class="ccfwc-banner-confetti">
					<div class="ccfwc-banner-confetti-item ccfwc-banner-confetti-1"></div>
					<div class="ccfwc-banner-confetti-item ccfwc-banner-confetti-2"></div>
					<div class="ccfwc-banner-confetti-item ccfwc-banner-confetti-3"></div>
					<div class="ccfwc-banner-confetti-item ccfwc-banner-confetti-4"></div>
					<div class="ccfwc-banner-confetti-item ccfwc-banner-confetti-5"></div>
				</div>

				<div class="ccfwc-pro-notice-content">
					<div class="ccfwc-new-year-section">
						<div class="ccfwc-pro-notice-left">
							<div class="ccfwc-pro-notice-icon">
								🎉
							</div>
							<div class="ccfwc-pro-notice-text">
								<h3 class="ccfwc-ny-heading">
									<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'New Year Sale', 'cgfwc' ); ?>
									</a>
								</h3>
								<div class="ccfwc-ny-text">
									<?php
									printf(
										/* translators: %s: discount amount */
										esc_html__( 'Kick Off 2026 With a Whopping %s OFF! Use Code: LIFETIME25', 'cgfwc' ),
										'25%'
									);
									?>
								</div>
							</div>
						</div>
						<div class="ccfwc-pro-notice-right">
							<button
								type="button"
								class="ccfwc-coupon-code-btn shine-button"
								data-coupon="LIFETIME25"
								aria-label="<?php esc_attr_e( 'Copy coupon code LIFETIME25', 'cgfwc' ); ?>"
							>
								<span class="ccfwc-coupon-text">
									<?php esc_html_e( 'Use Code', 'cgfwc' ); ?>
									<strong>LIFETIME25</strong>
								</span>
							</button>
							<a
								href="https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=top-banner&utm_campaign=upgrade-to-pro"
								target="_blank"
								rel="noopener noreferrer"
								class="ccfwc-pro-notice-button shine-button"
							>
								<?php esc_html_e( 'Upgrade to Pro', 'cgfwc' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
);

add_action(
	'wp_ajax_ccfwc_dismiss_pro_notice',
	function () {
		check_ajax_referer( 'ccfwc_dismiss_pro_notice', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		update_user_meta( get_current_user_id(), 'ccfwc_pro_notice_dismissed_2026', true );
		wp_send_json_success();
	}
);

/**
 * Keep Pro-only gateway options off/empty in the free (non-paying) build.
 *
 * @param mixed $settings Option value.
 * @return mixed
 */
function ccfwc_clamp_free_gateway_pro_options( $settings ) {
	if ( ! is_array( $settings ) || ! function_exists( 'ccfw_fs' ) || ccfw_fs()->is_paying() ) {
		return $settings;
	}
	$settings['product_icon']            = 'no';
	$settings['product_icons']           = 'no';
	$settings['product_ids']             = '0';
	$settings['total_price_to_checkout'] = '0';
	$settings['order_status']            = '';
	return $settings;
}
add_filter( 'option_woocommerce_coinbase_commerce_gateway_settings', 'ccfwc_clamp_free_gateway_pro_options', 10, 1 );

if ( !function_exists( 'init_coinbase_commerce_wc' ) )
{
    function init_coinbase_commerce_wc()
    {
        if( ! class_exists( 'CoinbaseCommerceWC' ) ) {
            class CoinbaseCommerceWC extends WC_Payment_Gateway
            {
                /**
                 * CoinbaseCommerceWC constructor.
                 * @since 1.0
                 * @version 1.0.1
                 */
                public function __construct()
                {
                    $this->run();
                    $this->id = 'coinbase_commerce_gateway';
                    $this->title = $this->get_option( 'title' );
                    $this->icon = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
                    $this->has_fields = false;
                    $this->method_title = __( 'Coinbase Commerce Gateway', 'cgfwc' );
                    $this->method_description = __( 'Coinbase Commerce Redirects to Coinbase Off-Site Checkout', 'cgfwc' );
                    $this->init_form_fields();

                    $this->init_settings();
                    $this->enabled = $this->get_option( 'enabled' );

                    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                    add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
                    add_action( 'woocommerce_api_coinbasecommercewc', array( $this, 'handle_webhook' ) );

                }

                /**
                 * Runs Plugins
                 * @since 1.0
                 * @version 1.0
                 */
                public function run()
                {
                    $this->constants();
                    $this->includes();
                    $this->add_actions();
                    $this->register_hooks();
                }

                /**
                 * @param $name Name of constant
                 * @param $value Value of constant
                 * @since 1.0
                 * @version 1.0
                 */
                public function define($name, $value)
                {
                    if (!defined($name))
                        define($name, $value);
                }

                protected function init_api() {
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-constants.php';
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-jwt-auth.php';
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-api-handler.php';

                    Coinbase_API_Handler::$log             = null;
                    Coinbase_API_Handler::$cdp_key_name    = $this->get_option( 'cdp_key_name' );
                    Coinbase_API_Handler::$cdp_private_key = $this->get_option( 'cdp_private_key' );
                    Coinbase_API_Handler::$sandbox_mode    = 'yes' === $this->get_option( 'sandbox_mode' );
                }

                /**
                 * Defines Constants
                 * @since 1.0
                 * @version 1.0
                 */
                public function constants()
                {
                    $this->define('CGFWC_VERSION', '1.5.0');
                    $this->define('CGFWC_PREFIX', 'cgfwc_');
                    $this->define('CGFWC_TEXT_DOMAIN', 'starter-plugin');
                    $this->define('CGFWC_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
                    $this->define('CGFWC_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
                }

                /**
                 * Require File
                 * @since 1.0
                 * @version 1.0
                 */
                public function file( $required_file ) {
                    if ( file_exists( $required_file ) )
                        require_once $required_file;
                    else
                        echo 'File Not Found';
                }

                /**
                 * Include files
                 * @since 1.0
                 * @version 1.0.1
                 */
                public function includes()
                {
                    $this->file(CGFWC_PLUGIN_DIR_PATH. 'includes/coinbase-gateway-for-woocommerce-functions.php');
                }

                public function custom_ajax()
                {
                    die('YAY');
                }

                /**
                 * Add Actions
                 * @since 1.0
                 * @version 1.0
                 */
                public function add_actions()
                {
                    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                /**
                 * Register Activation, Deactivation and Uninstall Hooks
                 * @since 1.0
                 * @version 1.0
                 */
                public function register_hooks()
                {
                    register_activation_hook( __FILE__, [$this, 'activate'] );
                    register_deactivation_hook( __FILE__, [$this, 'deactivate'] );
                    register_uninstall_hook(__FILE__, 'pluginprefix_function_to_run');
                }

                /**
                 * @return mixed
                 */
                public function add_gateway_class()
                {
                    $methods[] = 'WC_Gateway_Your_Gateway';
                    return $methods;
                }

                /**
                 * Fields
                 * @since 1.0
                 * @since 1.4 added `Webhook URL`
                 * @version 1.0.1
                 */
                public function init_form_fields()
                {
                    $pro_locked = ! ( function_exists( 'ccfw_fs' ) && ccfw_fs()->is_paying() );
                    $pro_dis    = $pro_locked ? array( 'disabled' => 'disabled' ) : array();

                    $this->form_fields = array(
                        'enabled'   =>  array(
                             'title'     =>  __( 'Enabled/ Disabled', 'cgfwc' ),
                             'type'      =>  'checkbox',
                             'label'     =>  __( 'Enable or Disable Coinbase Payments', 'cgfwc' ),
                             'default'   =>  'no'
                        ),
                        'sandbox_mode' => array(
                            'title'       => __( 'Sandbox mode', 'cgfwc' ),
                            'type'        => 'checkbox',
                            'label'       => __( 'Enable sandbox (test) mode', 'cgfwc' ),
                            'default'     => 'no',
                            'description' => __( 'When enabled, the sandbox Checkouts API and sandbox webhook secret are used.', 'cgfwc' ),
                        ),
                        'title' =>  array(
                            'title'         =>  __( 'Coinbase Business', 'cgfwc' ),
                            'type'          =>  'text',
                            'default'       =>  __( 'Coinbase Business Gateway', 'cgfwc' ),
                            'desc_tip'      => true,
                            'description'   =>  __( 'Add a new title for Coinbase Business Payment Gateway. Customers will see this at checkout.', 'cgfwc' ),
                        ),
                        'description'   =>  array(
                            'title'         =>  __( 'Pay with Coinbase', 'cgfwc' ),
                            'type'          =>  'textarea',
                            'default'       =>  __( 'Please checkout with Coinbase Business to place order', 'cgfwc' ),
                            'desc_tip'      => true,
                            'description'   =>  __( 'Add a new description for Coinbase Payment Gateway', 'cgfwc' ),
                        ),
                        'cdp_key_name' => array(
                            'title'       => __( 'CDP API key name', 'cgfwc' ),
                            'type'        => 'text',
                            'default'     => '',
                            'description' => sprintf(
                                __( 'From the Coinbase Developer Platform / Business dashboard. %s', 'cgfwc' ),
                                '<a target="_blank" href="https://coinbase.com/business">coinbase.com/business</a>'
                            ),
                        ),
                        'cdp_private_key' => array(
                            'title'       => __( 'CDP API private key', 'cgfwc' ),
                            'type'        => 'textarea',
                            'default'     => '',
                            'description' => __( 'EC private key in PEM format, including BEGIN/END lines.', 'cgfwc' ),
                            'css'         => 'font-family: monospace; height: 150px;',
                        ),
                        'webhook_secret' => array(
                            'title'       => __( 'Webhook secret', 'cgfwc' ),
                            'type'        => 'text',
                            'description' =>
                                __( 'Coinbase Business sends payment events to your site. Set up webhooks per Coinbase documentation, then paste the secret here.', 'cgfwc' )
                                . '<br /><br />'
                                . sprintf(
                                    '<a target="_blank" href="https://docs.cdp.coinbase.com/coinbase-business/checkout-apis/webhooks">%s</a>',
                                    __( 'Webhook documentation', 'cgfwc' )
                                )
                                . '<br />'
                                . sprintf(
                                    __( 'Endpoint URL: %s', 'cgfwc' ),
                                    '<code>' . esc_html( add_query_arg( 'wc-api', 'CoinbaseCommerceWC', home_url( '/', 'https' ) ) ) . '</code>'
                                ),
                        ),
                        'sandbox_webhook_secret' => array(
                            'title'       => __( 'Sandbox webhook secret', 'cgfwc' ),
                            'type'        => 'text',
                            'description' => __( 'Webhook secret for sandbox subscriptions (when sandbox mode is on).', 'cgfwc' ),
                        ),
                        'product_icon'   =>  array(
                            'title'             =>  __( 'Enable/ Disable', 'cgfwc' ),
                            'type'              =>  'checkbox',
                            'label'             =>  __( 'Show Crypto Icons and Individual Pricing on Product Page', 'cgfwc' ),
                            'default'           =>  'no',
                            'custom_attributes' => $pro_dis,
                       ),
                       'product_icons'   =>  array(
                        'title'             =>  __( 'Enable/ Disable', 'cgfwc' ),
                        'type'              =>  'checkbox',
                        'label'             =>  __( 'Show Crypto Icons and Individual Pricing on Shop Page', 'cgfwc' ),
                        'default'           =>  'no',
                        'custom_attributes' => $pro_dis,
                   ),

                        'product_ids'    => array(
	                        'title'             => __( 'Allowed Products', 'cgfwc' ),
	                        'type'              => 'text',
	                        'class'             => 'input-text regular-input ccfwc-pro-text-field',
	                        'default'           => '0',
	                        'description'       => __( 'Enter comma-separated Product IDs to allow to purchased with Coinbase, keep 0 or set empty for default behaviour.', 'cgfwc' ),
	                        'custom_attributes' => $pro_dis,
                        ),
                        'total_price_to_checkout' => array(
	                        'title'             => __( 'Minimum Checkout Amount', 'cgfwc' ),
	                        'type'              => 'text',
	                        'class'             => 'input-text regular-input ccfwc-pro-text-field',
	                        'default'           => '0',
	                        'description'       => __( 'Minimum checkout amount to purchase with Coinbase, keep 0 or set empty for default behaviour.', 'cgfwc' ),
	                        'custom_attributes' => $pro_dis,
                        ),
                        'order_status' => array(
	                        'title'             => __( 'Order Status When Payment Done', 'cgfwc' ),
	                        'type'              => 'text',
	                        'class'             => 'input-text regular-input ccfwc-pro-text-field',
	                        'default'           => '',
	                        'description'       => __( 'Order status to set when payment is done, keep empty for default behaviour.', 'cgfwc' ),
	                        'custom_attributes' => $pro_dis,
                        ),
                    );
                }

                public function payment_scripts()
                {
                    if ( empty( $this->get_option( 'cdp_key_name' ) ) ) {
                        return;
                    }
                }

                public function process_payment( $order_id )
                {
                    global $woocommerce;
                    $order = new WC_Order( $order_id );

                    if ( get_woocommerce_currency() !== 'USD' ) {
                        wc_add_notice( __( 'Coinbase Business requires the store currency to be USD.', 'cgfwc' ), 'error' );
                        return array( 'result' => 'fail' );
                    }

                    // Mark as on-hold (we're awaiting the payment)
                    $order->update_status('on-hold', __( 'Awaiting payment', 'woocommerce' ));

                    $this->init_api();

                    $metadata = array(
                        'order_id'  => (string) $order->get_id(),
                        'order_key' => $order->get_order_key(),
                        'source'    => 'woocommerce',
                    );

                    $success_url = $this->get_return_url( $order );
                    $cancel_url  = $order->get_cancel_order_url();
                    if ( is_ssl() || 'yes' === get_option( 'woocommerce_force_ssl_checkout' ) ) {
                        $cancel_url = str_replace( 'http:', 'https:', $cancel_url );
                    }

                    $result = Coinbase_API_Handler::create_checkout(
                        $order->get_total(),
                        $metadata,
                        $success_url,
                        $cancel_url,
                        'WooCommerce Order ID: ' . $order_id
                    );

                    if ( ! $result[0] ) {
                        wc_add_notice( __( 'Unable to create Coinbase checkout. Please try again or contact support.', 'cgfwc' ), 'error' );
                        return array( 'result' => 'fail' );
                    }

                    $checkout = $result[1];

                    $order->update_meta_data( '_coinbase_checkout_id', $checkout['id'] );
                    $order->update_meta_data( '_coinbase_checkout_url', $checkout['url'] );
                    $order->save();

                    // Remove cart
                    $woocommerce->cart->empty_cart();

                    // Return thankyou redirect
                    return array(
                        'result' => 'success',
                        'redirect' => $checkout['url']
                    );
                }

                public function cc_through_error()
                {
                    return 'YAYY';
                }

                public function handle_webhook() {
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-constants.php';

                    $payload = file_get_contents( 'php://input' );
                    if ( ! empty( $payload ) && $this->validate_webhook( $payload ) ) {
                        $data = json_decode( $payload, true );

                        $raw_event_type = isset( $data['eventType'] ) ? $data['eventType'] : null;
                        $event_type     = $raw_event_type ? Coinbase_Constants::normalize_event_type( $raw_event_type ) : null;
                        $metadata       = isset( $data['metadata'] ) ? $data['metadata'] : array();

                        if ( ! isset( $metadata['source'] ) || $metadata['source'] !== 'woocommerce' ) {
                            exit;
                        }

                        if ( ! isset( $metadata['order_id'] ) ) {
                            exit;
                        }

                        $order_id = $metadata['order_id'];
                        $order    = wc_get_order( $order_id );

                        if ( ! $order ) {
                            exit;
                        }

                        if ( isset( $metadata['order_key'] ) && $order->get_order_key() !== $metadata['order_key'] ) {
                            exit;
                        }

                        $this->_update_order_status( $order, $event_type, $data );
                        exit;
                    }
                    status_header( 200 );
                    exit;
                }

                public function validate_webhook( $payload ) {
                    $signature_header = $this->get_webhook_signature_header();
                    if ( '' === $signature_header ) {
                        return false;
                    }

                    $secret = 'yes' === $this->get_option( 'sandbox_mode' )
                        ? $this->get_option( 'sandbox_webhook_secret' )
                        : $this->get_option( 'webhook_secret' );

                    $parts = array();
                    foreach ( explode( ',', $signature_header ) as $part ) {
                        $split = explode( '=', $part, 2 );
                        if ( count( $split ) === 2 ) {
                            $parts[ $split[0] ] = $split[1];
                        }
                    }

                    $timestamp = isset( $parts['t'] ) ? $parts['t'] : null;
                    $signature = isset( $parts['v0'] ) ? $parts['v0'] : null;

                    if ( ! $timestamp || ! $signature ) {
                        return false;
                    }

                    if ( abs( time() - (int) $timestamp ) > 300 ) {
                        return false;
                    }

                    $signed_payload     = $timestamp . '.' . $payload;
                    $expected_signature = hash_hmac( 'sha256', $signed_payload, $secret );

                    return hash_equals( $expected_signature, $signature );
                }

                protected function get_webhook_signature_header() {
                    if ( function_exists( 'wp_get_request_header' ) ) {
                        $h = wp_get_request_header( 'x-hook0-signature' );
                        if ( is_string( $h ) && $h !== '' ) {
                            return $h;
                        }
                    }
                    if ( ! empty( $_SERVER['HTTP_X_HOOK0_SIGNATURE'] ) ) {
                        return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_HOOK0_SIGNATURE'] ) );
                    }
                    return '';
                }

                public function _update_order_status( $order, $event_type, $data = array() ) {
                    if ( ! $order ) {
                        return;
                    }
                    $prev_status = $order->get_meta( '_coinbase_status' );
                    if ( $event_type === $prev_status ) {
                        return;
                    }
                    $order->update_meta_data( '_coinbase_status', $event_type );

                    if ( Coinbase_Constants::EVENT_PAYMENT_SUCCESS === $event_type ) {
                        $txn_id = ! empty( $data['url'] ) ? basename( $data['url'] ) : '';
                        $order->payment_complete( $txn_id );
                        $order->add_order_note( __( 'Coinbase payment was successfully processed.', 'cgfwc' ) );
                    } elseif ( Coinbase_Constants::EVENT_PAYMENT_FAILED === $event_type ) {
                        $order->update_status( 'failed', __( 'Coinbase payment failed.', 'cgfwc' ) );
                    } elseif ( Coinbase_Constants::EVENT_PAYMENT_EXPIRED === $event_type ) {
                        if ( in_array( $order->get_status(), array( 'pending', 'on-hold' ), true ) ) {
                            $order->update_status( 'cancelled', __( 'Coinbase payment expired.', 'cgfwc' ) );
                        }
                    }
                    $order->save();
                }

                /**
                 * @return bool|void
                 */
                public function process_admin_options()
                {
                    // phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce settings form handles nonce.
                    $key_name_field = $this->get_field_key( 'cdp_key_name' );
                    $priv_key_field = $this->get_field_key( 'cdp_private_key' );
                    $posted_name    = isset( $_POST[ $key_name_field ] ) ? trim( wp_unslash( $_POST[ $key_name_field ] ) ) : '';
                    $posted_key     = isset( $_POST[ $priv_key_field ] ) ? trim( wp_unslash( $_POST[ $priv_key_field ] ) ) : '';
                    // phpcs:enable WordPress.Security.NonceVerification.Missing

                    $saving_enabled = ! empty( $_POST[ $this->get_field_key( 'enabled' ) ] );
                    if ( $saving_enabled ) {
                        if ( '' === $posted_name ) {
                            WC_Admin_Settings::add_error( __( 'Coinbase Business: CDP API key name is required when the gateway is enabled.', 'cgfwc' ) );
                            return false;
                        }
                        if ( '' === $posted_key ) {
                            WC_Admin_Settings::add_error( __( 'Coinbase Business: CDP API private key is required when the gateway is enabled.', 'cgfwc' ) );
                            return false;
                        }
                    }

                    parent::process_admin_options();

                    if ( ! ( function_exists( 'ccfw_fs' ) && ccfw_fs()->is_paying() ) ) {
                        $key  = $this->get_option_key();
                        $opts = get_option( $key, array() );
                        if ( is_array( $opts ) ) {
                            $opts['product_icon']            = 'no';
                            $opts['product_icons']           = 'no';
                            $opts['product_ids']             = '0';
                            $opts['total_price_to_checkout'] = '0';
                            $opts['order_status']            = '';
                            update_option( $key, $opts );
                            $this->init_settings();
                        }
                    }
                }

                /**
                 * Runs on Plugin's activation
                 * @since 1.0
                 * @version 1.0
                 */
                public function activate()
                {

                }

                /**
                 * Runs on Plugin's Deactivation
                 * @since 1.0
                 * @version 1.0
                 */
                public function deactivate()
                {

                }
            }
        }
    }
}

/**
 * Runs on Plugin's activation
 * @since 1.1
 * @version 1.0
 */
if ( !function_exists( 'om_woocommerce_requirements' ) ) {
    function om_woocommerce_requirements() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_attr_e( 'Please activate', 'woocommerce' );?> <a href="https://wordpress.org/plugins/woocommerce/"><?php esc_attr_e( 'Woocommerce', 'woocommerce' ); ?></a> <?php esc_attr_e( 'to use this plugin.', 'woocommerce' ); ?></p>
        </div>
        <?php
    }
}

if ( ccfwc_is_woocommerce_active() ) {
    add_action( 'plugins_loaded', 'init_coinbase_commerce_wc' );

    if ( ! function_exists( 'add_coinbase_to_wc' ) ) {
        function add_coinbase_to_wc( $gateways ) {
            $gateways[] = 'CoinbaseCommerceWC';
            return $gateways;
        }
    }

    add_filter( 'woocommerce_payment_gateways', 'add_coinbase_to_wc' );
} else {
    add_action( 'admin_notices', 'om_woocommerce_requirements' );
}

require_once plugin_dir_path(__FILE__) . 'includes/webhook.php';


//Notice
add_action( 'admin_enqueue_scripts', 'ccfwc_enqueue_scripts' );

if( !function_exists( 'ccfwc_enqueue_scripts' ) ):
function ccfwc_enqueue_scripts( $hook_suffix )
{
    wp_enqueue_style( 'ccfwc-css', CCFWC_PLUGIN_DIR_URL . 'assets/css/style.css', '', CGFWC_VERSION);
    wp_enqueue_script( 'ccfwc-custom-js', CCFWC_PLUGIN_DIR_URL . 'assets/js/custom.js', array( 'jquery' ), CGFWC_VERSION, true );

    wp_localize_script( 'ccfwc-custom-js', 'ccfwc', 
        array( 
            'images' => CCFWC_PLUGIN_DIR_URL . 'assets/images/',
        ) 
    );

    if ( 'plugins.php' === $hook_suffix ) {
        wp_enqueue_style(
            'ccfwc-admin-poppins',
            'https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap',
            array(),
            null
        );
        wp_add_inline_style(
            'ccfwc-admin-poppins',
            '.plugins .ccfwc-pro-link, .plugins .ccfwc-demo-pro-link { font-family: "Poppins", sans-serif !important; }'
        );
    }

    if ( ! ccfwc_is_wc_coinbase_settings_page() ) {
        return;
    }

    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'ccfwc-admin-marketing', CCFWC_PLUGIN_DIR_URL . 'assets/css/ccfwc-admin-marketing.css', array(), CGFWC_VERSION );

    $dismissed = get_user_meta( get_current_user_id(), 'ccfwc_pro_notice_dismissed_2026', true );
    if ( ! $dismissed ) {
        wp_enqueue_style( 'ccfwc-pro-notice', CCFWC_PLUGIN_DIR_URL . 'assets/css/ccfwc-pro-notice.css', array(), CGFWC_VERSION );
        wp_enqueue_script( 'ccfwc-pro-notice', CCFWC_PLUGIN_DIR_URL . 'assets/js/ccfwc-pro-notice.js', array( 'jquery' ), CGFWC_VERSION, true );
        wp_localize_script(
            'ccfwc-pro-notice',
            'ccfwcProNotice',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ccfwc_dismiss_pro_notice' ),
            )
        );
    }

}
endif;


add_filter( 'plugin_action_links_' . plugin_basename( CCFWC_PLUGIN_FILE ), 'ccfwc_plugin_action_links', 10, 1 );

if ( ! function_exists( 'ccfwc_plugin_action_links' ) ) {
	/**
	 * Green "Upgrade to Pro" next to Deactivate (same pattern as NOWPayments).
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	function ccfwc_plugin_action_links( $links ) {

		$upgrade_link = sprintf(
			'<a href="%s" class="ccfwc-pro-link" style="color: green; font-weight: bold;" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=plugins-page&utm_campaign=upgrade-to-pro' ),
			esc_html__( 'Upgrade to Pro', 'cgfwc' )
		);

		array_unshift( $links, $upgrade_link );

		return $links;
	}
}

add_filter( 'plugin_row_meta', 'ccfwc_plugin_row', 10, 4 );

if ( ! function_exists( 'ccfwc_plugin_row' ) ) {
	/**
	 * Blue bold "Demo PRO 🚀" in description row (same pattern as NOWPayments).
	 *
	 * @param array  $plugin_meta An array of the plugin's metadata.
	 * @param string $plugin_file Path to the plugin file.
	 * @param array  $plugin_data Plugin data from plugin headers.
	 * @param string $status      Plugin status (unused).
	 * @return array
	 */
	function ccfwc_plugin_row( $plugin_meta, $plugin_file, $plugin_data, $status ) {

		unset( $status, $plugin_data );

		if ( strpos( $plugin_file, basename( CCFWC_PLUGIN_FILE ) ) === false ) {
			return $plugin_meta;
		}

		$plugin_meta[] = sprintf(
			'<a href="%s" class="ccfwc-demo-pro-link" style="color: blue; font-weight: bold;" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( 'https://coinbase.coderpress.co/shop/' ),
			esc_html__( 'Demo PRO 🚀', 'cgfwc' )
		);

		return $plugin_meta;
	}
}

add_action( 'woocommerce_blocks_payment_method_type_registration', 'cb_register_payment_method_block_integrations', 5, 1 );

/**
 * Register the Square Credit Card checkout block integration class
 *
 * @since 2.5.0
 */
function cb_register_payment_method_block_integrations( $payment_method_registry ) {

    if ( class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {

        require_once 'includes/coinbase-gateway-block.php';
        $payment_method_registry->register( new CoinbaseGatewayBlock );
    
    }

}

/**
 * Save Cart Notice
 * 
 * @since 1.6.1
 */
$is_hidden_notice = get_option( 'ccwc_hide_save_cart_notice' );
$is_hidden_notice = true;

if( !$is_hidden_notice ) {

    add_action( 'admin_notices', 'ccwc_save_cart_notice' );
    add_action( 'admin_post_ccwc_hide_notice', 'ccwc_save_cart_hide_notice' );

}

if( !function_exists( 'ccwc_save_cart_notice' ) ) {

    function ccwc_save_cart_notice() {

        ?>
        <div class="notice notice-success is-dismissible ccwc-sc-notice" style="border-left-color: #03a0c7; padding: 15px 0;">
            <div style="display: flex; align-items: center;">
                <div>
                    <img src="https://i0.wp.com/coderpress.co/wp-content/uploads/2023/12/New-Project.jpg?w=125&ssl=1" />
                </div>
                <div style="margin-left: 25px;">
                    <h1 style="font-size: 20px;">Transform "Abundant Carts🛒" into "Successful Sales💹".</h1>
                    <p>
                        Say Good Bye🙋‍♂️! to your Abundant Carts, Sometimes users remove product from cart🛒, the reason could be unaffordability, this is the right time to secure the sale by offering a discount🚀.
                    </p>
                    <h4>
                        Grab Your 30% OFF By Using Coupon: "CCSPECIAL"
                    </h4>
                    <a href="https://coderpress.co/products/save-cart-for-woocommerce/?utm_source=ccwc&utm_medium=notice&utm_campaign=1&utm_id=savecart" target="_blank" class="button button-primary" style="background-color: #ffd814; border-color: #ffd814; color: #0F1111; font-weight: 400;">Get Started</a>
                    <a href="<?php echo admin_url( 'admin-post.php?action=ccwc_hide_notice' ); ?>" style="vertical-align: -webkit-baseline-middle; margin-left: 20px;">Not interested</a>
                </div>
            </div>
        </div>
        <?php

    }

}

if( !function_exists( 'ccwc_save_cart_hide_notice' ) ) {

    function ccwc_save_cart_hide_notice() {

        update_option( 'ccwc_hide_save_cart_notice', 1 );

        wp_redirect( wp_get_referer() );

    }

}