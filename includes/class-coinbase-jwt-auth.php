<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ccfwc_jwt_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( is_readable( $ccfwc_jwt_autoload ) ) {
	require_once $ccfwc_jwt_autoload;
}

use Firebase\JWT\JWT;

/**
 * JWT Authentication for Coinbase CDP API.
 *
 * Generates ES256-signed JWTs for authenticating with the Checkouts API.
 */
class Coinbase_JWT_Auth {

	/**
	 * CDP API key name/identifier.
	 *
	 * @var string
	 */
	private $key_name;

	/**
	 * EC private key in PEM format.
	 *
	 * @var string
	 */
	private $private_key;

	/**
	 * @param string $key_name        CDP API key name.
	 * @param string $private_key_pem EC private key in PEM format.
	 */
	public function __construct( $key_name, $private_key_pem ) {
		$this->key_name    = $key_name;
		$this->private_key = str_replace( '\\n', "\n", $private_key_pem );
	}

	/**
	 * Generate a JWT token for API authentication.
	 *
	 * @param string $method HTTP method (GET, POST, etc.).
	 * @param string $path   API path (e.g., /api/v1/checkouts).
	 * @return string Signed JWT token.
	 * @throws Exception If private key is invalid.
	 */
	public function generate_token( $method, $path ) {
		if ( ! class_exists( '\Firebase\JWT\JWT' ) ) {
			throw new Exception( 'firebase/php-jwt is missing. Run composer install in the plugin directory.' );
		}

		$private_key_resource = openssl_pkey_get_private( $this->private_key );
		if ( ! $private_key_resource ) {
			throw new Exception( 'Invalid private key: ' . (string) openssl_error_string() );
		}

		$time  = time();
		$nonce = bin2hex( random_bytes( 16 ) );

		$uri = $method . ' business.coinbase.com' . $path;

		$payload = array(
			'sub' => $this->key_name,
			'iss' => Coinbase_Constants::JWT_ISSUER,
			'nbf' => $time,
			'exp' => $time + Coinbase_Constants::JWT_EXPIRY_SECONDS,
			'uri' => $uri,
		);

		$headers = array(
			'typ'   => 'JWT',
			'nonce' => $nonce,
		);

		return JWT::encode( $payload, $private_key_resource, 'ES256', $this->key_name, $headers );
	}
}
