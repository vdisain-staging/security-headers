<?php
/**
 * Plugin settings stored in one WordPress option.
 *
 * @package VDisain\SecurityHeaders
 */

namespace VDisain\SecurityHeaders;

defined( 'ABSPATH' ) || exit;

/**
 * Reads and sanitizes the five header checkboxes.
 */
class Settings {

	public const OPTION_NAME = 'vsh_settings';

	/**
	 * Save the default checkboxes on first activation.
	 */
	public static function activate(): void {
		if ( false === get_option( self::OPTION_NAME, false ) ) {
			add_option( self::OPTION_NAME, self::defaults() );
		}
	}

	/**
	 * Keep stored checkboxes. Newly introduced header keys stay off.
	 */
	public static function maybe_upgrade(): void {
		$stored = get_option( self::OPTION_NAME, null );

		if ( ! is_array( $stored ) ) {
			return;
		}

		$normalized = self::normalize( $stored );

		if ( $normalized != $stored ) {
			update_option( self::OPTION_NAME, $normalized );
		}
	}

	/**
	 * @return array<string, bool|string>
	 */
	public static function get(): array {
		$stored = get_option( self::OPTION_NAME, null );

		if ( ! is_array( $stored ) ) {
			return self::defaults();
		}

		return self::normalize( $stored );
	}

	/**
	 * @param mixed $input Raw option value.
	 * @return array<string, bool|string>
	 */
	public static function sanitize( $input ): array {
		$settings = self::all_off();

		if ( is_array( $input ) ) {
			foreach ( self::keys() as $key ) {
				$settings[ $key ] = ! empty( $input[ $key ] );
			}
		}

		$settings['version'] = VSH_VERSION;

		return $settings;
	}

	/**
	 * Defaults for a new installation.
	 *
	 * @return array<string, bool|string>
	 */
	public static function defaults(): array {
		$settings = self::all_off();

		$settings['x_content_type_options'] = true;
		$settings['referrer_policy']        = true;
		$settings['x_frame_options']        = true;
		$settings['hsts']                   = true;

		return $settings;
	}

	/**
	 * @return string[]
	 */
	public static function keys(): array {
		return array(
			'x_content_type_options',
			'referrer_policy',
			'x_frame_options',
			'hsts',
			'permissions_policy',
		);
	}

	/**
	 * @return array<string, bool|string>
	 */
	private static function all_off(): array {
		return array(
			'version'                => VSH_VERSION,
			'x_content_type_options' => false,
			'referrer_policy'        => false,
			'x_frame_options'        => false,
			'hsts'                   => false,
			'permissions_policy'     => false,
		);
	}

	/**
	 * Copy only known checkboxes. Missing ones stay disabled.
	 *
	 * @param array<string, mixed> $stored Stored option.
	 * @return array<string, bool|string>
	 */
	private static function normalize( array $stored ): array {
		$settings = self::all_off();

		foreach ( self::keys() as $key ) {
			if ( array_key_exists( $key, $stored ) ) {
				$settings[ $key ] = (bool) $stored[ $key ];
			}
		}

		return $settings;
	}
}
