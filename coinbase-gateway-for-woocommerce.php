<?php
/**
 * Plugin Name: Coinbase Commerce For WooCommerce
 * Plugin URI: https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=plugin-uri
 * Author: CoderPress
 * Description: Coinbase Commerce For WooCommerce, Let your customer checkout with well known payment gateway.
 * Version: 1.7.0
 * Author: CoderPress
 * Author URI: https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=author-uri
 * License: GPL v2 or later
 * Stable tag: 1.7.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: woocommerce, coinbase commerce, payment, payment gateway, commerce, product
 * @author Syed Muhammad Usman
 * @url https://www.fiverr.com/mr_ussi
 * @version 1.7.0
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
                    'first-path'     => 'plugins.php',
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
define( 'CGFWC_VERSION', '1.7.0' );

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
                            'description'   =>  __( 'Add a new title for Coinbase Business Payment Gateway, Customers will se at checkout', 'cgfwc' ),
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
                            'title'     =>  __( 'Enable/ Disable', 'cgfwc' ),
                            'type'      =>  'checkbox',
                            'label'     =>  __( 'Show Crypto Icons and Individual Pricing on Product Page (Pro)', 'cgfwc' ),
                            'default'   =>  'no'
                       ),
                       'product_icons'   =>  array(
                        'title'     =>  __( 'Enable/ Disable', 'cgfwc' ),
                        'type'      =>  'checkbox',
                        'label'     =>  __( 'Show Crypto Icons and Individual Pricing on Shop Page (Pro)', 'cgfwc' ),
                        'default'   =>  'no'
                   ),

                        'product_ids'    => array(
	                        'title'       => __( 'Allowed Products', 'ccfc' ),
	                        'type'        => 'text',
	                        'default'     => '0',
	                        'description' => __( 'Enter comma-separated Product IDs to allow to purchased with Coinbase, keep 0 or set empty for default behaviour. (Pro)', 'ccfc' ),
                        ),
                        'total_price_to_checkout' => array(
	                        'title'       => __( 'Minimum Checkout Amount', 'ccfc' ),
	                        'type'        => 'text',
	                        'default'     => '0',
	                        'description' => __( 'Minimum checkout amount to purchase with Coinbase, keep 0 or set empty for default behaviour. (Pro)', 'ccfc' ),
                        ),
                        'order_status' => array(
	                        'title'       => __( 'Order Status When Payment Done', 'ccfc' ),
	                        'type'        => 'text',
	                        'default'     => '',
	                        'description' => __( 'Order status to set when payment is done, keep empty for default behaviour. (Pro)', 'ccfc' ),
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
                        wc_add_notice( __( 'Coinbase Business requires the store currency to be USD.', 'ccfc' ), 'error' );
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

                    $result = Coinbase_API_Handler::create_checkout(
                        $order->get_total(),
                        $metadata,
                        site_url(),
                        $order->get_cancel_order_url(),
                        'WooCommerce Order ID: ' . $order_id
                    );

                    if ( ! $result[0] ) {
                        wc_add_notice( __( 'Unable to create Coinbase checkout. Please try again or contact support.', 'ccfc' ), 'error' );
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
                        $order->update_status( 'processing', __( 'Coinbase payment was successfully processed.', 'ccfc' ) );
                    } elseif ( Coinbase_Constants::EVENT_PAYMENT_FAILED === $event_type ) {
                        $order->update_status( 'failed', __( 'Coinbase payment failed.', 'ccfc' ) );
                    } elseif ( Coinbase_Constants::EVENT_PAYMENT_EXPIRED === $event_type ) {
                        if ( 'pending' === $order->get_status() ) {
                            $order->update_status( 'cancelled', __( 'Coinbase payment expired.', 'ccfc' ) );
                        }
                    }
                    $order->save();
                }

                /**
                 * @return bool|void
                 */
                public function process_admin_options()
                {
                    parent::process_admin_options();

                    if ( empty( $_POST['woocommerce_coinbase_commerce_gateway_cdp_key_name'] ) ) {
                        WC_Admin_Settings::add_error( 'Error: CDP Key Name is required.' );
                        return false;
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

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'plugins_loaded', 'init_coinbase_commerce_wc' );
} else {
    add_action( 'admin_notices', 'om_woocommerce_requirements' );
}

if ( !function_exists( 'add_coinbase_to_wc' ) ):
    function add_coinbase_to_wc( $gateways )
    {
        $gateways[] = 'CoinbaseCommerceWC';
        return $gateways;
    }
endif;

add_filter( 'woocommerce_payment_gateways', 'add_coinbase_to_wc' );

require_once plugin_dir_path(__FILE__) . 'includes/webhook.php';


//Notice
add_action( 'admin_enqueue_scripts', 'ccfwc_enqueue_scripts' );

if( !function_exists( 'ccfwc_enqueue_scripts' ) ):
function ccfwc_enqueue_scripts()
{
    wp_enqueue_style( 'ccfwc-css', CCFWC_PLUGIN_DIR_URL . 'assets/css/style.css', '', CGFWC_VERSION);
    wp_enqueue_script( 'ccfwc-custom-js', CCFWC_PLUGIN_DIR_URL . 'assets/js/custom.js', array( 'jquery' ), CGFWC_VERSION);

    wp_localize_script( 'ccfwc-custom-js', 'ccfwc', 
        array( 
            'images' => CCFWC_PLUGIN_DIR_URL . 'assets/images/',
        ) 
    );

}
endif;


add_filter( 'plugin_row_meta', 'ccfwc_plugin_row', 10, 5 );

if( !function_exists( 'ccfwc_plugin_row' ) ):
function ccfwc_plugin_row( $plugin_meta, $plugin_file, $plugin_data, $status ) {

    if( isset( $plugin_data['slug'] ) && $plugin_data['slug'] == 'commerce-coinbase-for-woocommerce' ) {
        $plugin_meta[] = sprintf(
            '<a href="%s" style="color: green; font-weight: bold" target="_blank">%s</a>',
            esc_url( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=plugins-go-pro' ),
            __( 'GO PRO🚀' )
        );

        $plugin_meta[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( 'https://coinbase.coderpress.co/shop/' ),
            __( 'Demo PRO' )
        );
    }

    return $plugin_meta;

}
endif;

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