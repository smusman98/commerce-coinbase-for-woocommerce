<?php
/**
 * Plugin Name: Commerce Coinbase For WooCommerce
 * Plugin URI: https://www.scintelligencia.com/
 * Author: SCI Intelligencia
 * Description: Commerce Coinbase For WooCommerce, Let user checkout with well known payment gateway.
 * Version: 1.0
 * Author: Syed Muhammad Usman
 * Author URI: https://www.linkedin.com/in/syed-muhammad-usman/
 * License: GPL v2 or later
 * Stable tag: 1.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: WooCommerce, posts, products, Statistics, counter, track
 * @author Syed Muhammad Usman
 * @url https://www.fiverr.com/mr_ussi
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !function_exists( 'init_coinbase_commerce_wc' ) )
{
    function init_coinbase_commerce_wc()
    {
        if( !class_exists('CoinbaseCommerceWC') ) {
            class CoinbaseCommerceWC extends WC_Payment_Gateway
            {
                /**
                 * CoinbaseCommerceWC constructor.
                 * @since 1.0
                 * @version 1.0
                 */
                public function __construct()
                {
                    $this->run();
                    $this->id = 'coinbase_commerce_gateway';
                    $this->title = __( 'Commerce Coinbase', 'cgfwc' );
                    $this->icon = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
                    $this->has_fields = false;
                    $this->method_title = __( 'Commerce Coinbase Gateway', 'cgfwc' );
                    $this->method_description = __( 'Commerce Coinbase Redirects to Coinbase Off-Site Checkout', 'cgfwc' );
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
                    $this->define('CGFWC_VERSION', '1.0');
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
                 * @version 1.0
                 */
                public function includes()
                {
                    $this->file(CGFWC_PLUGIN_DIR_PATH. 'includes/coinbase-gateway-for-woocommerce-functions.php');
                }

                /**
                 * Enqueue Styles and Scripts
                 * @since 1.0
                 * @version 1.0
                 */
                public function enqueue_scripts()
                {
                    add_action("wp_ajax_custom_ajax", [$this, 'custom_ajax']);
                    add_action("wp_ajax_nopriv_custom_ajax", [$this, 'custom_ajax']);
                    wp_enqueue_style(CGFWC_TEXT_DOMAIN . '-css', CGFWC_PLUGIN_DIR_URL . 'assets/css/style.css', '', CGFWC_VERSION);
                    wp_enqueue_script(CGFWC_TEXT_DOMAIN . '-custom-js', CGFWC_PLUGIN_DIR_URL . 'assets/js/custom.js', '', CGFWC_VERSION);
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
                    add_action('init', [$this, 'enqueue_scripts']);
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
                 *
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
                            'title'         =>  __( 'Commerce Coinbase Payment Gateway', 'cgfwc' ),
                            'type'          =>  'text',
                            'default'       =>  __( 'Commerce Coinbase Payment Gateway', 'cgfwc' ),
                            'desc_tip'      => true,
                            'description'   =>  __( 'Add a new title for Commerce Coinbase Payment Gateway, Customers will se at checkout', 'cgfwc' ),
                        ),
                        'description'   =>  array(
                            'title'         =>  __( 'Pay with Commerce Coinbase Payment Gateway', 'cgfwc' ),
                            'type'          =>  'textarea',
                            'default'       =>  __( 'Please checkout with Commerce Coinbase to place order', 'cgfwc' ),
                            'desc_tip'      => true,
                            'description'   =>  __( 'Add a new description for Commerce Coinbase Payment Gateway, Customers will se at checkout', 'cgfwc' ),
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
                        )
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
                    $cart_total = (int)$amount2 = floatval( preg_replace( '#[^\d.]#', '', $woocommerce->cart->get_cart_total() ) );
                    ;

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
                    );

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

                        update_user_meta( get_current_user_id(), 'cgfwc' ,$response->data->id );

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

add_action( 'plugins_loaded', 'init_coinbase_commerce_wc' );

if ( !function_exists( 'add_coinbase_to_wc' ) ):
    function add_coinbase_to_wc( $gateways )
    {
        $gateways[] = 'CoinbaseCommerceWC';
        return $gateways;
    }
endif;

add_filter( 'woocommerce_payment_gateways', 'add_coinbase_to_wc' );

