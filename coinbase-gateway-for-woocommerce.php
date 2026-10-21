<?php
/**
 * Plugin Name: Coinbase Commerce For WooCommerce
 * Plugin URI: https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=plugin-uri
 * Author: CoderPress
 * Description: Coinbase Commerce For WooCommerce, Let your customer checkout with well known payment gateway.
 * Version: 2.0.0
 * Author: CoderPress
 * Author URI: https://coderpress.co/products/coinbase-commerce-for-woocommerce/?utm_source=ccwc&utm_medium=author-uri
 * License: GPL v2 or later
 * Stable tag: 2.0.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: woocommerce, coinbase commerce, payment, payment gateway, commerce, product
 * Requires Plugins: woocommerce
 * @url https://www.fiverr.com/mr_ussi
 * @version 2.0.0
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
define( 'CGFWC_VERSION', '2.0.0' );

if ( ! defined( 'CCFWC_PLUGIN_FILE' ) ) {
	define( 'CCFWC_PLUGIN_FILE', __FILE__ );
}

/**
 * Whether WooCommerce is installed and active (site or network).
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

	if ( is_multisite() && function_exists( 'is_plugin_active_for_network' ) ) {
		return is_plugin_active_for_network( 'woocommerce/woocommerce.php' );
	}

	return false;
}

/**
 * True when WooCommerce is in the same activate / bulk-activate request (not yet in active_plugins).
 *
 * @return bool
 */
function ccfwc_is_woocommerce_being_activated() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- activation request shape only.
	$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

	if ( ! in_array( $action, array( 'activate', 'activate-selected' ), true ) ) {
		return false;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_REQUEST['plugin'] ) && 'woocommerce/woocommerce.php' === wp_unslash( $_REQUEST['plugin'] ) ) {
		return true;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_REQUEST['checked'] ) && is_array( $_REQUEST['checked'] ) ) {
		$checked = wp_unslash( $_REQUEST['checked'] );
		return in_array( 'woocommerce/woocommerce.php', $checked, true );
	}

	return false;
}

/**
 * Block activation when WooCommerce is missing.
 */
function ccfwc_activation_requires_woocommerce() {
	if ( ccfwc_is_woocommerce_active() || ccfwc_is_woocommerce_being_activated() ) {
		return;
	}

	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	deactivate_plugins( plugin_basename( CCFWC_PLUGIN_FILE ) );

	if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	wp_die(
		wp_kses_post(
			sprintf(
				/* translators: %s: WooCommerce plugin name. */
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
 * Deactivate this plugin if WooCommerce was turned off while it remained active.
 */
function ccfwc_deactivate_if_woocommerce_inactive() {
	if ( ccfwc_is_woocommerce_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	deactivate_plugins( plugin_basename( CCFWC_PLUGIN_FILE ) );
}
add_action( 'admin_init', 'ccfwc_deactivate_if_woocommerce_inactive' );

/**
 * WooCommerce → Settings → Payments → Coinbase gateway section URL.
 *
 * @return string
 */
function ccfwc_get_gateway_settings_admin_url() {
	return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=coinbase_commerce_gateway' );
}

/**
 * Detect upgrade to 2.0.0+ (Coinbase Business) and flag the admin migration notice.
 */
function ccfwc_handle_plugin_version_upgrade() {
	$stored_version = get_option( 'ccfwc_plugin_version', '' );

	if ( version_compare( $stored_version, CGFWC_VERSION, '>=' ) ) {
		return;
	}

	$show_migration_notice = false;

	if ( '' !== $stored_version && version_compare( $stored_version, '2.0.0', '<' ) && version_compare( CGFWC_VERSION, '2.0.0', '>=' ) ) {
		$show_migration_notice = true;
	}

	// Sites on 1.x before we stored version: show notice if gateway settings already exist.
	if ( ! $show_migration_notice && '' === $stored_version && version_compare( CGFWC_VERSION, '2.0.0', '>=' ) ) {
		$gateway_settings = get_option( 'woocommerce_coinbase_commerce_gateway_settings', array() );
		if ( is_array( $gateway_settings ) && ! empty( $gateway_settings ) ) {
			$show_migration_notice = true;
		}
	}

	if ( $show_migration_notice ) {
		update_option( 'ccfwc_show_business_migration_notice', '1' );
	}

	update_option( 'ccfwc_plugin_version', CGFWC_VERSION );
}
add_action( 'plugins_loaded', 'ccfwc_handle_plugin_version_upgrade', 1 );

/**
 * Whether the Coinbase Business migration notice should display for the current user.
 *
 * @return bool
 */
function ccfwc_should_show_business_migration_notice() {
	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	if ( '1' !== get_option( 'ccfwc_show_business_migration_notice' ) ) {
		return false;
	}

	if ( get_user_meta( get_current_user_id(), 'ccfwc_business_migration_notice_dismissed', true ) ) {
		return false;
	}

	return true;
}

/**
 * Admin notice after upgrading from Coinbase Commerce (1.x) to Coinbase Business (2.0).
 */
function ccfwc_business_migration_admin_notice() {
	if ( ! ccfwc_should_show_business_migration_notice() ) {
		return;
	}

	$settings_url = ccfwc_get_gateway_settings_admin_url();
	?>
	<div class="notice notice-warning is-dismissible ccfwc-business-migration-notice">
		<p><strong><?php esc_html_e( 'Coinbase Commerce is now Coinbase Business', 'cgfwc' ); ?></strong></p>
		<p><?php esc_html_e( 'This plugin was updated to the new Coinbase Business checkout APIs. Please review your payment settings so checkout keeps working.', 'cgfwc' ); ?></p>
		<ul style="list-style: disc; margin-left: 1.5em;">
			<li><?php esc_html_e( 'Add your CDP API Key Name and CDP Private Key from coinbase.com/business (legacy Commerce API keys no longer work).', 'cgfwc' ); ?></li>
			<li><?php esc_html_e( 'Create or update your webhook in Coinbase and paste the new webhook secret in gateway settings.', 'cgfwc' ); ?></li>
			<li><?php esc_html_e( 'Set your WooCommerce store currency to USD. Customers will pay with USDC on the Base network.', 'cgfwc' ); ?></li>
		</ul>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Open payment settings', 'cgfwc' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ccfwc_business_migration_admin_notice' );

add_action(
	'wp_ajax_ccfwc_dismiss_business_migration_notice',
	function () {
		check_ajax_referer( 'ccfwc_dismiss_business_migration_notice', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		update_user_meta( get_current_user_id(), 'ccfwc_business_migration_notice_dismissed', true );
		wp_send_json_success();
	}
);

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
                    if ( empty( $payload ) ) {
                        status_header( 400 );
                        exit;
                    }

                    if ( ! $this->validate_webhook( $payload ) ) {
                        status_header( 401 );
                        exit;
                    }

                    $data = json_decode( $payload, true );
                    if ( ! is_array( $data ) ) {
                        status_header( 400 );
                        exit;
                    }

                    $raw_event_type = isset( $data['eventType'] ) ? $data['eventType'] : null;
                    $event_type     = $raw_event_type ? Coinbase_Constants::normalize_event_type( $raw_event_type ) : null;
                    $metadata       = isset( $data['metadata'] ) && is_array( $data['metadata'] ) ? $data['metadata'] : array();

                    if ( empty( $metadata['source'] ) || 'woocommerce' !== $metadata['source'] ) {
                        status_header( 400 );
                        exit;
                    }

                    if ( empty( $metadata['order_id'] ) || empty( $metadata['order_key'] ) ) {
                        status_header( 400 );
                        exit;
                    }

                    $order = wc_get_order( absint( $metadata['order_id'] ) );
                    if ( ! $order ) {
                        status_header( 404 );
                        exit;
                    }

                    if ( ! hash_equals( (string) $order->get_order_key(), (string) $metadata['order_key'] ) ) {
                        status_header( 403 );
                        exit;
                    }

                    if ( ! $this->webhook_matches_order( $order, $data ) ) {
                        status_header( 403 );
                        exit;
                    }

                    $this->_update_order_status( $order, $event_type, $data );
                    status_header( 200 );
                    exit;
                }

                /**
                 * Verify amount, currency, network, and checkout ID against the local order.
                 *
                 * @param WC_Order $order Order.
                 * @param array    $data  Decoded webhook payload.
                 * @return bool
                 */
                protected function webhook_matches_order( $order, $data ) {
                    if ( empty( $data['amount'] ) || empty( $data['currency'] ) ) {
                        return false;
                    }

                    if ( strtoupper( (string) $data['currency'] ) !== Coinbase_Constants::PAYMENT_CURRENCY ) {
                        return false;
                    }

                    if ( empty( $data['network'] ) || strtolower( (string) $data['network'] ) !== Coinbase_Constants::PAYMENT_NETWORK ) {
                        return false;
                    }

                    $expected_amount = wc_format_decimal( $order->get_total(), 2 );
                    $payload_amount  = wc_format_decimal( $data['amount'], 2 );
                    if ( $expected_amount !== $payload_amount ) {
                        return false;
                    }

                    $stored_checkout_id  = (string) $order->get_meta( '_coinbase_checkout_id' );
                    $payload_checkout_id = isset( $data['id'] ) ? (string) $data['id'] : '';
                    if ( '' === $stored_checkout_id || '' === $payload_checkout_id ) {
                        return false;
                    }
                    if ( ! hash_equals( $stored_checkout_id, $payload_checkout_id ) ) {
                        return false;
                    }

                    return true;
                }

                /**
                 * Verify X-Hook0-Signature.
                 *
                 * Accepts CDP general format (t,v0,h,v1) and Checkout docs format (t,h,v1):
                 * - v0 = HMAC-SHA256 over "{t}.{body}"
                 * - v1 = HMAC-SHA256 over "{t}.{h}.{headerValues}.{body}"
                 *
                 * @param string $payload Raw request body.
                 * @return bool
                 */
                public function validate_webhook( $payload ) {
                    $signature_header = $this->get_webhook_signature_header();
                    if ( '' === $signature_header ) {
                        return false;
                    }

                    $secret = 'yes' === $this->get_option( 'sandbox_mode' )
                        ? $this->get_option( 'sandbox_webhook_secret' )
                        : $this->get_option( 'webhook_secret' );

                    if ( ! is_string( $secret ) || '' === $secret ) {
                        return false;
                    }

                    $parts = array();
                    foreach ( explode( ',', $signature_header ) as $part ) {
                        $split = explode( '=', $part, 2 );
                        if ( count( $split ) === 2 ) {
                            $parts[ trim( $split[0] ) ] = trim( $split[1] );
                        }
                    }

                    $timestamp = isset( $parts['t'] ) ? $parts['t'] : null;
                    if ( ! $timestamp || ! ctype_digit( (string) $timestamp ) ) {
                        return false;
                    }

                    if ( abs( time() - (int) $timestamp ) > 300 ) {
                        return false;
                    }

                    if ( ! empty( $parts['v0'] ) ) {
                        $expected_v0 = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );
                        if ( hash_equals( $expected_v0, $parts['v0'] ) ) {
                            return true;
                        }
                    }

                    if ( ! empty( $parts['v1'] ) && isset( $parts['h'] ) && '' !== $parts['h'] ) {
                        $header_names  = $parts['h'];
                        $header_values = array();
                        foreach ( explode( ' ', $header_names ) as $header_name ) {
                            $header_values[] = $this->get_request_header_value( $header_name );
                        }
                        $signed_v1   = $timestamp . '.' . $header_names . '.' . implode( '.', $header_values ) . '.' . $payload;
                        $expected_v1 = hash_hmac( 'sha256', $signed_v1, $secret );
                        if ( hash_equals( $expected_v1, $parts['v1'] ) ) {
                            return true;
                        }
                    }

                    return false;
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

                /**
                 * Read a request header for v1 signature binding (case-insensitive).
                 *
                 * @param string $name Header name from the signature `h` field.
                 * @return string
                 */
                protected function get_request_header_value( $name ) {
                    if ( function_exists( 'wp_get_request_header' ) ) {
                        $value = wp_get_request_header( $name );
                        if ( is_string( $value ) ) {
                            return $value;
                        }
                    }

                    $normalized = strtoupper( str_replace( '-', '_', $name ) );
                    if ( 'CONTENT_TYPE' === $normalized && isset( $_SERVER['CONTENT_TYPE'] ) ) {
                        return (string) wp_unslash( $_SERVER['CONTENT_TYPE'] );
                    }
                    if ( 'CONTENT_LENGTH' === $normalized && isset( $_SERVER['CONTENT_LENGTH'] ) ) {
                        return (string) wp_unslash( $_SERVER['CONTENT_LENGTH'] );
                    }

                    $server_key = 'HTTP_' . $normalized;
                    if ( isset( $_SERVER[ $server_key ] ) ) {
                        return (string) wp_unslash( $_SERVER[ $server_key ] );
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

                        $sandbox_on     = ! empty( $_POST[ $this->get_field_key( 'sandbox_mode' ) ] );
                        $webhook_field  = $sandbox_on ? 'sandbox_webhook_secret' : 'webhook_secret';
                        $webhook_secret = isset( $_POST[ $this->get_field_key( $webhook_field ) ] )
                            ? trim( wp_unslash( $_POST[ $this->get_field_key( $webhook_field ) ] ) )
                            : '';
                        if ( '' === $webhook_secret ) {
                            WC_Admin_Settings::add_error(
                                $sandbox_on
                                    ? __( 'Coinbase Business: Sandbox webhook secret is required when the gateway is enabled in sandbox mode.', 'cgfwc' )
                                    : __( 'Coinbase Business: Webhook secret is required when the gateway is enabled.', 'cgfwc' )
                            );
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
if ( ! function_exists( 'om_woocommerce_requirements' ) ) {
	function om_woocommerce_requirements() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: WooCommerce plugin name. */
						__( 'Coinbase Commerce For WooCommerce requires %s to be installed and active.', 'cgfwc' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ) . '">WooCommerce</a>'
					)
				);
				?>
			</p>
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

	add_action( 'woocommerce_blocks_payment_method_type_registration', 'cb_register_payment_method_block_integrations', 5, 1 );
} else {
	add_action( 'admin_notices', 'om_woocommerce_requirements' );
}

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

    if ( ccfwc_should_show_business_migration_notice() ) {
        wp_enqueue_script(
            'ccfwc-migration-notice',
            CCFWC_PLUGIN_DIR_URL . 'assets/js/ccfwc-migration-notice.js',
            array( 'jquery' ),
            CGFWC_VERSION,
            true
        );
        wp_localize_script(
            'ccfwc-migration-notice',
            'ccfwcMigrationNotice',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ccfwc_dismiss_business_migration_notice' ),
            )
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

/**
 * Register the Coinbase checkout block integration class.
 *
 * @since 2.5.0
 *
 * @param mixed $payment_method_registry Blocks payment method registry.
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