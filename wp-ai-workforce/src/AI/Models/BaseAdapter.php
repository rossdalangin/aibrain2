<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Basic adapter with common functionality for AI providers.
 */
abstract class BaseAdapter implements AIModelInterface {

	/**
	 * @var string
	 */
	protected $api_key;

	/**
	 * Constructor.
	 *
	 * @param string $api_key Encrypted or raw API key.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Log API errors.
	 */
	protected function log_error( string $message, array $context = [] ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[Nexus AI] %s', $message ) );
		}
	}

	/**
	 * Get the base URL for the API.
	 */
	abstract protected function get_base_url(): string;

	/**
	 * Perform a remote request to the AI provider.
	 */
	protected function request( string $endpoint, array $payload, array $extra_headers = [] ) {
		$headers = array_merge( [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $this->api_key,
		], $extra_headers );

		return wp_remote_post( $this->get_base_url() . $endpoint, [
			'headers' => $headers,
			'body'    => wp_json_encode( $payload ),
			'timeout' => 60,
		] );
	}
}
