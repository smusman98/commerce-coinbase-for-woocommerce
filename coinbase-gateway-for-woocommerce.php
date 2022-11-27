<?php
/**
 * Plugin Name: Coinbase Commerce For WooCommerce
 * Plugin URI: https://www.scintelligencia.com/
 * Author: SCI Intelligencia
 * Description: Coinbase Commerce For WooCommerce, Let your customer checkout with well known payment gateway.
 * Version: 1.4.11
 * Author: Syed Muhammad Usman
 * Author URI: https://www.linkedin.com/in/syed-muhammad-usman/
 * License: GPL v2 or later
 * Stable tag: 1.4.11
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: woocommerce, coinbase commerce, payment, payment gateway, commerce, product
 * @author Syed Muhammad Usman
 * @url https://www.fiverr.com/mr_ussi
 * @version 1.4.11
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

define( 'CCFWC_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CGFWC_VERSION', '1.4.11' );

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
                    $this->api_key = $this->get_option( 'api_key' );

                    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                    add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
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

                /**
                 * Defines Constants
                 * @since 1.0
                 * @version 1.0
                 */
                public function constants()
                {
                    $this->define('CGFWC_VERSION', '1.4.1');
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
                        'title' =>  array(
                            'title'         =>  __( 'Coinbase Commerce', 'cgfwc' ),
                            'type'          =>  'text',
                            'default'       =>  __( 'Coinbase Commerce Payment Gateway', 'cgfwc' ),
                            'desc_tip'      => true,
                            'description'   =>  __( 'Add a new title for Coinbase Commerce Payment Gateway, Customers will se at checkout', 'cgfwc' ),
                        ),
                        'description'   =>  array(
                            'title'         =>  __( 'Pay with Coinbase Commerce Payment Gateway', 'cgfwc' ),
                            'type'          =>  'textarea',
                            'default'       =>  __( 'Please checkout with Coinbase Commerce to place order', 'cgfwc' ),
                            'desc_tip'      => true,
                            'description'   =>  __( 'Add a new description for Coinbase Commerce Payment Gateway, Customers will se at checkout', 'cgfwc' ),
                        ),
                        'instructions'   =>  array(
                            'title'         =>  __( 'Instructions', 'cgfwc' ),
                            'type'          =>  'textarea',
                            'default'       =>  __( 'Instructions', 'cgfwc' ),
                            'desc_tip'      => true,
                            'description'   =>  __( 'Instructions will be added to the thank you page and order email, Customers will se at checkout', 'cgfwc' ),
                        ),
                        'api_key' => array(
                            'title'       => 'API Key',
                            'type'        => 'password'
                        ),
                        'webhook_url' => array(
                            'title'             =>  'Webhook URL',
                            'type'              =>  'text',
                            'default'           =>  site_url() . '?rest_route=/ccfwc/v1/complete-payment',
                            'custom_attributes' =>  array( 'readonly' => 'readonly' )
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
                    if ( empty( $this->api_key ) ) {
                        return;
                    }
                }

                public function process_payment( $order_id )
                {
                    global $woocommerce;
                    $order = new WC_Order( $order_id );
                    $cart_total = $woocommerce->cart->total;

                    $site_url = site_url();

                    $currency = $order->get_currency();

                    // Mark as on-hold (we're awaiting the cheque)
                    $order->update_status('on-hold', __( 'Awaiting cheque payment', 'woocommerce' ));

                    $body = array (
                        'name' => $order_id,
                        'description' => 'WooCommerce Order ID: ' . $order_id,
                        'local_price' =>
                            array (
                                'amount' => $cart_total,
                                'currency' => $currency,
                            ),
                        'pricing_type' => 'fixed_price',
                        'requested_info' =>
                            array (
                                0 => 'email',
                            ),
                        "redirect_url"  =>  $site_url
                    );

                    $body = apply_filters( 'ccfwc_filter_checkout_body', $body );

                    $headers = array(
                        'Content-Type'  =>  'application/json',
                        'X-Cc-Api-Key'  =>  $this->api_key,
                        'X-Cc-Version'  =>  '2018-03-22'
                    );

                    $endpoint = 'https://api.commerce.coinbase.com/checkouts';

                    $body = wp_json_encode( $body );

                    $options = array(
                        'body'          =>  $body,
                        'headers'       =>  $headers,
                        'method'        =>  'POST',
                        'timeout'       =>  45,
                        'redirection'   =>  5,
                        'httpversion'   =>  '1.0',
                        'sslverify'     =>  false,
                        'data_format'   => 'body'
                    );

                    $response = wp_remote_post(
                        $endpoint,
                        $options
                    );

                    $response_code = wp_remote_retrieve_response_code( $response );

                    $response_msg = wp_remote_retrieve_response_message( $response );

                    if(  $response_code == 201 )
                    {
                        $response = json_decode( $response['body'] );

                        update_option( $response->data->id, $order_id );

                        $cc_redirect = 'https://commerce.coinbase.com/checkout/' . $response->data->id ;

                        // Remove cart
                        $woocommerce->cart->empty_cart();

                        // Return thankyou redirect
                        return array(
                            'result' => 'success',
                            'redirect' => $cc_redirect
                        );
                    }
                    else
                    {
                        wc_add_notice( sprintf( 'Error Code: %u Message: %s', $response_code, $response_msg ), 'error' );
                    }
                }

                public function cc_through_error()
                {
                    return 'YAYY';
                }

                /**
                 * @return bool|void
                 */
                public function process_admin_options()
                {
                    parent::process_admin_options();

                    if ( empty( $_POST['woocommerce_coinbase_commerce_gateway_api_key'] ) ) {
                        WC_Admin_Settings::add_error( 'Error: API Key is required.' );
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
add_action( "wp_ajax_ccfwc-exit-pro-notice", 'ccfwc_exit_pro_notice' );

if( !function_exists( 'ccfwc_enqueue_scripts' ) ):
function ccfwc_enqueue_scripts()
{
    wp_enqueue_style( 'ccfwc-css', CCFWC_PLUGIN_DIR_URL . 'assets/css/style.css', '', CGFWC_VERSION);
    wp_enqueue_script( 'ccfwc-custom-js', CCFWC_PLUGIN_DIR_URL . 'assets/js/custom.js', array( 'jquery' ), CGFWC_VERSION);
    wp_enqueue_script( 'ccfwc-coinbase-commerce', 'https://commerce.coinbase.com/v1/checkout.js?onload=CoinbaceCommerceCallBack', array( '-custom-js' ), CGFWC_VERSION);
}
endif;


add_filter( 'plugin_row_meta', 'ccfwc_plugin_row', 10, 5 );

if( !function_exists( 'ccfwc_plugin_row' ) ):
function ccfwc_plugin_row( $plugin_meta, $plugin_file, $plugin_data, $status ) {

    if( $plugin_data['slug'] == 'commerce-coinbase-for-woocommerce' ) {
        $plugin_meta[] = sprintf(
            '<a href="%s" style="color: green; font-weight: bold" target="_blank">%s</a>',
            esc_url( 'https://coderpress.co/products/coinbase-commerce-for-woocommerce/' ),
            __( 'Go PRO' )
        );

        $plugin_meta[] = sprintf(
            '<a href="%s" style="color: green; font-weight: bold" target="_blank">%s</a>',
            esc_url( 'https://coinbase.coderpress.co/shop/' ),
            __( 'Demo PRO' )
        );
    }

    return $plugin_meta;

}
endif;
