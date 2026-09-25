# vDisain Security Headers

Small WordPress plugin that adds a few common HTTP security headers.

A high score on SecurityHeaders.com does not mean a WordPress website is secure. This plugin only adds browser response headers. It does not replace updates, strong passwords, MFA, a firewall, backups, malware scanning, or server hardening.

## Headers

| Header | Default | Value |
| --- | --- | --- |
| `X-Content-Type-Options` | On | `nosniff` |
| `Referrer-Policy` | On | `strict-origin-when-cross-origin` |
| `X-Frame-Options` | On | `SAMEORIGIN` |
| `Strict-Transport-Security` | On | `max-age=31536000` |
| `Permissions-Policy` | Off | `camera=(), microphone=()` |

HSTS is sent only when `is_ssl()` is true. It does not include `includeSubDomains` or `preload`. Turn it off if the server or CDN already sends HSTS.

Header values cannot be edited. Future versions will not enable a new header on existing sites.

## What it does not do

It does not modify `.htaccess`, nginx, permalinks, rewrite rules, redirects, WPML or Polylang URLs, cookies, authentication, or HTML. It does not use output buffering.

Headers are not sent for `wp-admin`, `wp-login.php`, `admin-ajax.php`, cron, or WP-CLI.

Full-page caches that skip PHP will not include these headers until WordPress generates the response again.

## Installation

1. Copy `vdisain-security-headers` into `wp-content/plugins/`.
2. Activate **vDisain Security Headers**.
3. Open **Settings → vDisain Security Headers**.

Requires WordPress 6.0+ and PHP 8.0+. Settings are stored in the `vsh_settings` option. Deleting the plugin does not delete that option.

## Emergency disable

```php
define( 'VDISAIN_SECURITY_HEADERS_DISABLED', true );
```

The plugin stays installed and settings stay stored, but it sends no custom headers. There is no admin switch for this constant.

## Developer hooks

`vdisain_security_headers_enabled` returns a boolean. The emergency constant cannot be overridden.

`vdisain_security_headers` receives the final header name => value map. Newlines in values are discarded.

## Updates

The plugin bundles Plugin Update Checker and checks https://github.com/vdisain-staging/security-headers. No license key is required.

Release process:

1. Update `VSH_VERSION` and the changelog.
2. Commit and tag, for example `v1.0.1`.
3. Push the tag. GitHub Actions attaches `vdisain-security-headers.zip` with that folder at the root of the archive.
4. WordPress shows **Update now** on the Plugins screen.

## Changelog

### 1.0.0

- Initial release with five optional security headers and GitHub updates.
