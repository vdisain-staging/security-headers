<?php
/**
 * GitHub release updates.
 *
 * @package VDisain\SecurityHeaders
 */

namespace VDisain\SecurityHeaders;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

defined( 'ABSPATH' ) || exit;

/**
 * Checks the public GitHub repository for new releases.
 */
class Updater {

	/**
	 * Boot Plugin Update Checker when the bundled library is present.
	 */
	public static function init(): void {
		$library = VSH_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

		if ( ! is_readable( $library ) ) {
			return;
		}

		require_once $library;

		$checker = PucFactory::buildUpdateChecker(
			'https://github.com/vdisain-staging/security-headers',
			VSH_PLUGIN_FILE,
			'vdisain-security-headers'
		);

		$checker->getVcsApi()->enableReleaseAssets( '/^vdisain-security-headers\.zip$/' );
	}
}
