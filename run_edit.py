import re

file_path = "c:/Users/CoreCom/Local Sites/coinbase/app/public/wp-content/plugins/commerce-coinbase-for-woocommerce/coinbase-gateway-for-woocommerce.php"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# Replace top exit with autoload require
content = content.replace("if ( ! defined( 'ABSPATH' ) ) exit;", "if ( ! defined( 'ABSPATH' ) ) exit;\n\nif ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {\n    require_once dirname( __FILE__ ) . '/vendor/autoload.php';\n}")

# Fix __construct
content = content.replace(
'''->api_key = ->get_option( 'api_key' );

                    add_action( 'woocommerce_update_options_payment_gateways_' . ->id, array( , 'process_admin_options' ) );
                    add_action( 'wp_enqueue_scripts', array( , 'payment_scripts' ) );''',
'''add_action( 'woocommerce_update_options_payment_gateways_' . ->id, array( , 'process_admin_options' ) );
                    add_action( 'wp_enqueue_scripts', array( , 'payment_scripts' ) );
                    add_action( 'woocommerce_api_coinbasecommercewc', array( , 'handle_webhook' ) );'''
)

# Add init_api()
content = content.replace(
'''public function constants()''',
'''protected function init_api() {
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-constants.php';
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-jwt-auth.php';
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-api-handler.php';

                    Coinbase_API_Handler::    = ->get_option( 'cdp_key_name' );
                    Coinbase_API_Handler:: = ->get_option( 'cdp_private_key' );
                    Coinbase_API_Handler::    = 'yes' === ->get_option( 'sandbox_mode' );
                }

                public function constants()'''
)

# Replace fields inside init_form_fields up to webhook_url
import textwrap

old_fields = '''                        'enabled'   =>  array(
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
                        ),'''

new_fields = """                        'enabled'   =>  array(
                             'title'     =>  __( 'Enabled/ Disabled', 'cgfwc' ),
                             'type'      =>  'checkbox',
                             'label'     =>  __( 'Enable or Disable Coinbase Payments', 'cgfwc' ),
                             'default'   =>  'yes'
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
                        ),"""

content = content.replace(old_fields, new_fields)

# Process process_payment replacement
import re
start_match = re.search(r'public function process_payment\( \ \)', content)
end_match = re.search(r'public function cc_through_error', content)

if start_match and end_match:
    old_process_payment = content[start_match.start():end_match.start()]
    new_process_payment = '''public function process_payment(  )
                {
                    global ;
                     = new WC_Order(  );

                    if ( get_woocommerce_currency() !== 'USD' ) {
                        wc_add_notice( __( 'Coinbase Business requires the store currency to be USD.', 'ccfc' ), 'error' );
                        return array( 'result' => 'fail' );
                    }

                    // Mark as on-hold (we're awaiting the cheque)
                    ->update_status('on-hold', __( 'Awaiting payment', 'woocommerce' ));

                    ->init_api();

                     = array(
                        'order_id'  => (string) ->get_id(),
                        'order_key' => ->get_order_key(),
                        'source'    => 'woocommerce',
                    );

                     = Coinbase_API_Handler::create_checkout(
                        ->get_total(),
                        ,
                        site_url(),
                        ->get_cancel_order_url(),
                        'WooCommerce Order ID: ' . 
                    );

                    if ( ! [0] ) {
                        wc_add_notice( __( 'Unable to create Coinbase checkout. Please try again or contact support.', 'ccfc' ), 'error' );
                        return array( 'result' => 'fail' );
                    }

                     = [1];

                    ->update_meta_data( '_coinbase_checkout_id', ['id'] );
                    ->update_meta_data( '_coinbase_checkout_url', ['url'] );
                    ->save();

                    // Remove cart
                    ->cart->empty_cart();

                    return array(
                        'result' => 'success',
                        'redirect' => ['url']
                    );
                }

                '''
    content = content[:start_match.start()] + new_process_payment + content[end_match.start():]

# Add webhook handler before cc_through_error
new_webhook_methods = '''public function handle_webhook() {
                    include_once dirname( __FILE__ ) . '/includes/class-coinbase-constants.php';

                     = file_get_contents( 'php://input' );
                    if ( ! empty(  ) && ->validate_webhook(  ) ) {
                         = json_decode( , true );

                         = isset( ['eventType'] ) ? ['eventType'] : null;
                             =  ? Coinbase_Constants::normalize_event_type(  ) : null;
                               = isset( ['metadata'] ) ? ['metadata'] : array();

                        if ( ! isset( ['source'] ) || ['source'] !== 'woocommerce' ) {
                            exit;
                        }

                        if ( ! isset( ['order_id'] ) ) {
                            exit;
                        }

                         = ['order_id'];
                            = wc_get_order(  );

                        if ( !  ) {
                            exit;
                        }

                        if ( isset( ['order_key'] ) && ->get_order_key() !== ['order_key'] ) {
                            exit;
                        }

                        ->_update_order_status( , ,  );
                        exit;
                    }
                    status_header( 200 );
                    exit;
                }

                public function validate_webhook(  ) {
                     = ->get_webhook_signature_header();
                    if ( '' ===  ) {
                        return false;
                    }

                     = 'yes' === ->get_option( 'sandbox_mode' )
                        ? ->get_option( 'sandbox_webhook_secret' )
                        : ->get_option( 'webhook_secret' );

                     = array();
                    foreach ( explode( ',',  ) as  ) {
                         = explode( '=', , 2 );
                        if ( count(  ) === 2 ) {
                            [ [0] ] = [1];
                        }
                    }

                     = isset( ['t'] ) ? ['t'] : null;
                     = isset( ['v0'] ) ? ['v0'] : null;

                    if ( !  || !  ) {
                        return false;
                    }

                    if ( abs( time() - (int)  ) > 300 ) {
                        return false;
                    }

                         =  . '.' . ;
                     = hash_hmac( 'sha256', ,  );

                    return hash_equals( ,  );
                }

                protected function get_webhook_signature_header() {
                    if ( function_exists( 'wp_get_request_header' ) ) {
                         = wp_get_request_header( 'x-hook0-signature' );
                        if ( is_string(  ) &&  !== '' ) {
                            return ;
                        }
                    }
                    if ( ! empty( ['HTTP_X_HOOK0_SIGNATURE'] ) ) {
                        return sanitize_text_field( wp_unslash( ['HTTP_X_HOOK0_SIGNATURE'] ) );
                    }
                    return '';
                }

                public function _update_order_status( , ,  = array() ) {
                    if ( !  ) {
                        return;
                    }
                     = ->get_meta( '_coinbase_status' );
                    if (  ===  ) {
                        return;
                    }
                    ->update_meta_data( '_coinbase_status',  );

                    if ( Coinbase_Constants::EVENT_PAYMENT_SUCCESS ===  ) {
                         = ! empty( ['url'] ) ? basename( ['url'] ) : '';
                        ->payment_complete(  );
                        ->update_status( 'processing', __( 'Coinbase payment was successfully processed.', 'ccfc' ) );
                    } elseif ( Coinbase_Constants::EVENT_PAYMENT_FAILED ===  ) {
                        ->update_status( 'failed', __( 'Coinbase payment failed.', 'ccfc' ) );
                    } elseif ( Coinbase_Constants::EVENT_PAYMENT_EXPIRED ===  ) {
                        if ( 'pending' === ->get_status() ) {
                            ->update_status( 'cancelled', __( 'Coinbase payment expired.', 'ccfc' ) );
                        }
                    }
                    ->save();
                }

                '''
content = content.replace("public function cc_through_error", new_webhook_methods + "public function cc_through_error")

# Fix process_admin_options api_key check
content = content.replace("if ( empty( ['woocommerce_coinbase_commerce_gateway_api_key'] ) ) {", "if ( empty( ['woocommerce_coinbase_commerce_gateway_cdp_key_name'] ) ) {")
content = content.replace("Error: API Key is required.", "Error: CDP API Key Name is required.")


with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("done")
