=== vDisain Security Headers ===
Contributors: vdisain
Tags: security, headers
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a few common HTTP security headers. It does not change permalinks, redirects, or server configuration.

== Description ==

vDisain Security Headers sends a small set of browser security headers from WordPress.

Enabled by default:

* X-Content-Type-Options: nosniff
* Referrer-Policy: strict-origin-when-cross-origin
* X-Frame-Options: SAMEORIGIN
* Strict-Transport-Security: max-age=31536000 (HTTPS only, without includeSubDomains or preload)

Permissions-Policy is off until an administrator enables it. The only value is camera=(), microphone=().

A high score on SecurityHeaders.com does not mean a WordPress site is secure. This plugin only adds HTTP response headers.

The plugin does not modify .htaccess, nginx, permalinks, rewrite rules, redirects, or response HTML.

== Installation ==

1. Upload the `vdisain-security-headers` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Review Settings → vDisain Security Headers.

Updates come from GitHub releases of https://github.com/vdisain-staging/security-headers.

To stop header output without deleting settings, add this to `wp-config.php`:

`define( 'VDISAIN_SECURITY_HEADERS_DISABLED', true );`

== Changelog ==

= 1.0.0 =
* Initial release with five optional security headers and GitHub updates.
