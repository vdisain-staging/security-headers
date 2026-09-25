<?php
/**
 * HTTP security header output.
 *
 * @package VDisain\SecurityHeaders
 */

namespace VDisain\SecurityHeaders;

defined( 'ABSPATH' ) || exit;

/**
 * Adds response headers without changing the response body or routing.
 */
class Headers {

	/**
	 * Register response-header hooks.
	 */
	public static function init(): void {
		add_filter( 'wp_headers', array( self::class, 'filter_wp_headers' ) );
		add_filter( 'rest_pre_serve_request', array( self::class, 'filter_rest_headers' ), 10, 4 );
	}

	/**
	 * @return array<string, string>
	 */
	public static function collect(): array {
		if ( defined( 'VDISAIN_SECURITY_HEADERS_DISABLED' ) && VDISAIN_SECURITY_HEADERS_DISABLED ) {
			return array();
		}

		if ( ! apply_filters( 'vdisain_security_headers_enabled', true ) ) {
			return array();
		}

		$settings = Settings::get();
		$headers  = array();

		if ( ! empty( $settings['x_content_type_options'] ) ) {
			$headers['X-Content-Type-Options'] = 'nosniff';
		}

		if ( ! empty( $settings['referrer_policy'] ) ) {
			$headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
		}

		if ( ! empty( $settings['x_frame_options'] ) ) {
			$headers['X-Frame-Options'] = 'SAMEORIGIN';
		}

		if ( ! empty( $settings['hsts'] ) && is_ssl() ) {
			$headers['Strict-Transport-Security'] = 'max-age=31536000';
		}

		if ( ! empty( $settings['permissions_policy'] ) ) {
			$headers['Permissions-Policy'] = 'camera=(), microphone=()';
		}

		$filtered = apply_filters( 'vdisain_security_headers', $headers );

		if ( ! is_array( $filtered ) ) {
			return array();
		}

		$clean = array();

		foreach ( $filtered as $name => $value ) {
			if ( ! is_string( $name ) || ! preg_match( '/^[A-Za-z0-9-]+$/', $name ) || ! is_scalar( $value ) ) {
				continue;
			}

			$scalar = trim( str_replace( array( "\r", "\n", "\0" ), '', (string) $value ) );

			if ( '' !== $scalar && strlen( $scalar ) <= 500 ) {
				$clean[ $name ] = $scalar;
			}
		}

		return $clean;
	}

	/**
	 * @param mixed $headers Header map from WordPress.
	 * @return array<string, string>
	 */
	public static function filter_wp_headers( $headers ) {
		if ( ! is_array( $headers ) ) {
			$headers = array();
		}

		if ( self::is_excluded_request() ) {
			return $headers;
		}

		foreach ( self::collect() as $name => $value ) {
			if ( self::map_has_header( $headers, $name ) || self::already_sent( $name ) ) {
				continue;
			}

			$headers[ $name ] = $value;
		}

		return $headers;
	}

	/**
	 * @param mixed $served Whether the request was already served.
	 * @param mixed $result Response data.
	 * @param mixed $request Request object.
	 * @param mixed $server REST server.
	 * @return mixed
	 */
	public static function filter_rest_headers( $served, $result, $request, $server ) {
		unset( $result, $request );

		if ( self::is_excluded_request() || ! is_object( $server ) || ! method_exists( $server, 'send_header' ) ) {
			return $served;
		}

		foreach ( self::collect() as $name => $value ) {
			if ( self::already_sent( $name ) ) {
				continue;
			}

			$server->send_header( $name, $value );
		}

		return $served;
	}

	/**
	 * Skip admin, login, ajax, cron, and CLI responses.
	 */
	private static function is_excluded_request(): bool {
		if ( ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() || wp_doing_ajax() || is_admin() ) {
			return true;
		}

		if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
			return true;
		}

		$script = isset( $_SERVER['SCRIPT_NAME'] ) ? (string) wp_unslash( $_SERVER['SCRIPT_NAME'] ) : '';

		return '' !== $script && str_ends_with( $script, '/wp-login.php' );
	}

	/**
	 * @param array<mixed, mixed> $headers Header map.
	 */
	private static function map_has_header( array $headers, string $name ): bool {
		foreach ( array_keys( $headers ) as $existing ) {
			if ( is_string( $existing ) && strtolower( $existing ) === strtolower( $name ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * True when PHP has already queued this header.
	 */
	private static function already_sent( string $name ): bool {
		$needle = strtolower( $name );

		foreach ( headers_list() as $header ) {
			$parts = explode( ':', $header, 2 );

			if ( strtolower( trim( $parts[0] ) ) === $needle ) {
				return true;
			}
		}

		return false;
	}
}
