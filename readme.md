# Brand Master - Customize Login and User Frontend Dashboard

> Elevate your brand by customizing WordPress login pages and introducing a sleek frontend dashboard for users with Brand Master.

**Version:** 1.0.6
**Requires at least:** WordPress 5.6 / PHP 7.0
**Tested up to:** WordPress 7.1
**License:** GPLv2 or later

## Description

Brand Master gives you complete control over your WordPress login pages and adds a personalized frontend dashboard for your users. Replace the default `wp-login.php` URL with a custom brandable slug, apply your branding (logo, colors, background, custom CSS/JS), and serve a fully configurable dashboard — all from a React-powered admin interface.

Whether you are building a client site, a membership platform, or simply want to white-label the WordPress login experience, Brand Master provides the tools without the bloat.

## Requirements

- WordPress 5.6 or higher
- PHP 7.0 or higher
- Pretty permalinks enabled (recommended for clean login URLs)

## Installation

1. Upload the plugin zip via **Dashboard &rarr; Plugins &rarr; Add New &rarr; Upload Plugin**, or extract the folder into `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu.
3. Go to **Brand Master** in the admin menu to configure login branding, the custom login slug, and dashboard blocks.

### Build from source

```sh
npm install
npm run build      # produces build/admin and build/public
npm run deploy     # build + i18n pot + copy step
```

## Features

- **Login page customization** — logo, colors, background image, custom CSS/JS, title, and body text.
- **Custom login slug** — replace `wp-login.php` with a brandable URL (e.g. `/sign-in/`), with reserved-slug and page-collision validation.
- **Redirect control** — independent redirects after login, logout, lost password, and registration.
- **Frontend dashboard** — user info (avatar, display name, bio), navigation menu, social links, and logout link, all configurable from the admin.
- **Responsive design** — consistent experience across desktop and mobile.
- **Accessibility** — semantic heading hierarchy, `aria-current` on the active menu item, `rel="noopener noreferrer"` on external social links.
- **Block patterns** — pre-built Gutenberg patterns for quick dashboard setup.

## Security

- All settings are recursively sanitized before storage.
- Custom CSS/JS fields require the `unfiltered_html` capability.
- Login/redirect slugs are validated against reserved words and existing pages.
- Redirect URLs are validated through `wp_validate_redirect` (internal-only by default).
- REST API endpoints require `manage_options` capability and a valid REST nonce.
- The plugin option is not exposed on the standard `/wp/v2/settings` endpoint.

## Shortcodes

| Shortcode | Purpose |
|-----------|--------|
| `[brand_master_dashboard /]` | Renders the frontend user dashboard. |
| `[brand_master_login /]` | Renders the frontend login form. |

## Block Patterns

Brand Master ships Gutenberg block patterns for a quick dashboard setup (main page and support page). Find them under the **Dashboard** category in the block inserter's **Patterns** tab.

## Developer Reference

All hooks use the `brand_master` prefix. Reference the [REST API schema](admin/class-admin.php) (`get_settings_schema()`) for the full settings shape.

### Filters

| Filter | Location | Signature | Purpose |
|--------|----------|-----------|----------|
| `brand_master_default_options` | `includes/functions.php:145` | `(array $defaults) → array` | Modify or extend the default settings array. Runs on every request; return the full default tree. |
| `brand_master_get_login_url` | `public/class-login.php:147` | `(string $url) → string` | Filter the computed custom login URL. |
| `brand_master_get_redirect_url` | `public/class-login.php:339` | `(string $url) → string` | Filter the URL used when the custom login slug is not in the request. |
| `brand_master_has_wp_admin_access` | `public/class-login.php:364` | `(bool $has_access) → bool` | Decide whether the current user may access `wp-admin` when the hide-login feature is active. |
| `brand_master_dashboard` | `public/class-dashboard.php:308` | `(string $html) → string` | Filter the fully-rendered dashboard HTML before it is returned. |
| `brand_master_add_dashboard_login` | `public/class-dashboard.php:329` | `(string $html) → string` | Filter the "login required" notice HTML shown to logged-out visitors. |
| `brand_master_patterns` | `includes/class-patterns.php:101` | `(array $patterns) → array` | Add, remove, or modify registered block patterns. Each pattern must include `slug`, `title.rendered`, and `pattern_content`. |
| `brand_master_changelog_file` | `includes/functions.php:234` | `(string $path) → string` | Change the changelog file path used by the admin "What's New" panel. |
| `brand_master_setting_properties` | `admin/class-admin.php:222` | `(array $props) → array` | Add or modify React-admin localized data. |
| `brand_master_localize_data` | `admin/class-admin.php:189` | `(array $data) → array` | Filter the entire `brand_master` JS localization object passed to the React admin. |
| `brand_master_validate_redirect` | `includes/functions.php` | `(string $url, string $fallback) → string` | Customize redirect validation behavior (internal-only by default). |
| `rest_{type}_item_schema` | `includes/api/class-api-settings.php:316` | `(array $schema) → array` | WP core convention — filter the REST schema for the settings endpoint. |

### Actions

| Action | Location | Signature | Purpose |
|--------|----------|-----------|----------|
| `brand_master_before_dashboard` | `public/templates/dashboard.php:19` | `() → void` | Fires before the dashboard HTML is rendered. |
| `brand_master_after_dashboard` | `public/templates/dashboard.php:86` | `() → void` | Fires after the dashboard HTML is rendered. |

### REST API

Settings are managed exclusively through the custom endpoint:

```
PUT /wp-json/brand-master/v1/settings
```

The request body is the full settings object. Partial updates are merged with existing values. The endpoint requires `manage_options` capability and a valid REST nonce. Custom CSS/JS fields additionally require the `unfiltered_html` capability.

The option is **not** exposed on the standard `/wp/v2/settings` endpoint.

## Frequently Asked Questions

**What happens if my login slug conflicts with an existing page?**

The plugin validates slugs on save and rejects conflicts with existing pages, reserved words (`wp-admin`, `wp-login.php`, `wp-json`, etc.), and WordPress routes (`feed`, `cgi-bin`).

**Can I use custom CSS and JavaScript on the login page?**

Yes. The admin provides dedicated CSS and JS fields. Note that saving custom code requires the `unfiltered_html` capability (typically administrators).

**What happens to my settings when I delete the plugin?**

The `uninstall.php` file removes all plugin options from the database on uninstall. Deactivation alone preserves your settings unless the "Remove all plugin settings when deactivating" option is enabled.

**Does this plugin collect or transmit any data?**

No. Brand Master does not collect, transmit, or store any data externally. All settings are stored in your WordPress database.

**Can I translate the plugin into my language?**

Yes. The plugin is fully translation-ready. All strings use the `brand-master` text domain. A `.pot` template is provided in the `languages/` directory for use with tools like Poedit or `wp i18n make-pot`.

## Privacy

Brand Master does not collect, transmit, or store any personal data externally. All plugin settings are stored in your WordPress database. No data is sent to third-party services.

## Contributing

1. Fork the repository.
2. Create a feature branch: `git checkout -b my-new-feature`.
3. Run `composer lint` and `composer test` before committing.
4. Push and open a Pull Request.

Coding standards: `vendor/bin/phpcs` (WordPress standard). Tests: `vendor/bin/phpunit`.

## License

GPLv2 or later © [PatternsWP](https://patternswp.com/). Built with [WP React Plugin Boilerplate](https://patternswp.com/wp-react-plugin-boilerplate) and [Atrc](https://www.npmjs.com/package/atrc).
