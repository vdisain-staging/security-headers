<?php
/**
 * Plugin Name: vDisain Security Headers
 * Description: Adds a few common HTTP security headers. Does not change permalinks, redirects, or server configuration.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: vDisain | Denis Melamed
 * Author URI: https://vdisain.ee
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vdisain-security-headers
 *
 * @package VDisain\SecurityHeaders
 */

defined( 'ABSPATH' ) || exit;

define( 'VSH_VERSION', '1.0.0' );
define( 'VSH_PLUGIN_FILE', __FILE__ );
define( 'VSH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VSH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once VSH_PLUGIN_DIR . 'includes/class-vsh-settings.php';
require_once VSH_PLUGIN_DIR . 'includes/class-vsh-headers.php';
require_once VSH_PLUGIN_DIR . 'includes/class-vsh-admin.php';
require_once VSH_PLUGIN_DIR . 'includes/class-vsh-updater.php';

register_activation_hook( VSH_PLUGIN_FILE, array( '\VDisain\SecurityHeaders\Settings', 'activate' ) );

add_action(
	'plugins_loaded',
	static function () {
		\VDisain\SecurityHeaders\Settings::maybe_upgrade();
		\VDisain\SecurityHeaders\Headers::init();
		\VDisain\SecurityHeaders\Updater::init();

		if ( is_admin() ) {
			\VDisain\SecurityHeaders\Admin::init();
		}
	}
);
