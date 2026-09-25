<?php
/**
 * Settings screen.
 *
 * @package VDisain\SecurityHeaders
 */

namespace VDisain\SecurityHeaders;

defined( 'ABSPATH' ) || exit;

/**
 * One settings page with five checkboxes.
 */
class Admin {

	private const PAGE = 'vdisain-security-headers';

	/**
	 * Register admin hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'register_menu' ) );
		add_action( 'admin_init', array( self::class, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( VSH_PLUGIN_FILE ), array( self::class, 'action_links' ) );
	}

	/**
	 * Add the settings page under Settings.
	 */
	public static function register_menu(): void {
		add_options_page(
			__( 'vDisain Security Headers', 'vdisain-security-headers' ),
			__( 'vDisain Security Headers', 'vdisain-security-headers' ),
			'manage_options',
			self::PAGE,
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Register the option and checkbox fields.
	 */
	public static function register_settings(): void {
		register_setting(
			'vsh_settings_group',
			Settings::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Settings::class, 'sanitize' ),
				'default'           => Settings::defaults(),
				'show_in_rest'      => false,
			)
		);

	}

	/**
	 * Load the settings stylesheet only on this screen.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		if ( 'settings_page_' . self::PAGE !== $hook_suffix ) {
			return;
		}

		$css_path = VSH_PLUGIN_DIR . 'assets/css/admin.css';

		wp_enqueue_style(
			'vsh-admin',
			VSH_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			VSH_VERSION . '.' . (string) filemtime( $css_path )
		);
	}

	/**
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public static function action_links( array $links ): array {
		$url = admin_url( 'options-general.php?page=' . self::PAGE );

		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'vdisain-security-headers' ) . '</a>'
		);

		return $links;
	}

	/**
	 * Render the settings page.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$fields = array(
			'x_content_type_options' => array( 'X-Content-Type-Options', 'nosniff', '' ),
			'referrer_policy'        => array( 'Referrer-Policy', 'strict-origin-when-cross-origin', '' ),
			'x_frame_options'        => array( 'X-Frame-Options', 'SAMEORIGIN', '' ),
			'hsts'                   => array( 'HSTS', 'max-age=31536000', __( 'Sent only on HTTPS.', 'vdisain-security-headers' ) ),
			'permissions_policy'     => array( 'Permissions-Policy', 'camera=(), microphone=()', '' ),
		);
		$settings = Settings::get();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'vDisain Security Headers', 'vdisain-security-headers' ) . '</h1>';
		settings_errors();
		echo '<form class="vsh-settings" action="options.php" method="post">';
		settings_fields( 'vsh_settings_group' );

		foreach ( $fields as $key => $field ) {
			echo '<label class="vsh-settings__row">';
			echo '<input type="hidden" name="vsh_settings[' . esc_attr( $key ) . ']" value="0" />';
			echo '<input type="checkbox" name="vsh_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( ! empty( $settings[ $key ] ), true, false ) . ' />';
			echo '<span class="vsh-settings__text"><span class="vsh-settings__title">' . esc_html( $field[0] ) . '</span>';

			if ( '' !== $field[2] ) {
				echo '<span class="vsh-settings__note">' . esc_html( $field[2] ) . '</span>';
			}

			echo '</span><code class="vsh-settings__value">' . esc_html( $field[1] ) . '</code></label>';
		}

		echo '<div class="vsh-settings__footer">';
		submit_button( null, 'primary', 'submit', false );
		echo '<p class="vsh-settings__version">' . esc_html__( 'Plugin version:', 'vdisain-security-headers' ) . ' ' . esc_html( VSH_VERSION ) . '</p>';
		echo '</div></form></div>';
	}
}
