=== Brand Master - Customize Login and User Frontend Dashboard ===
Contributors: patternswp, codersantosh
Tags: login customization, frontend dashboard, white label, branding
Requires at least: 5.6
Tested up to: 7.1
Requires PHP: 7.0
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customize your WordPress login page and provide a sophisticated frontend dashboard for your users with Brand Master.

== Description ==

Brand Master is a powerful plugin designed to provide you complete control over the appearance and behaviour of your WordPress login pages, along with a seamless experience for users through a customized frontend dashboard. Elevate your brand by creating a unique and user-friendly login and frontend dashboard environment.

== Available shortcodes == 

- [brand_master_dashboard /]
- [brand_master_login /]

== Inbuilt Patterns for Quick Setup ==

The 'Brand Master' plugin comes with a collection of inbuilt Gutenberg Block Patterns that users can easily apply for a quick setup. It includes patters for dashboard main page and support page.

= Locating and Inserting Patterns =

- Access the Block Editor: Open the page or post where you want to use a pattern. Click the "Add Block" button (+) or the "+" sign in the top left corner of the block editor.

- Switch to the Patterns Tab: In the block inserter window, click the "Patterns" tab. This displays all available patterns.

- Dashboard : Within the Patterns tab, find the category labeled "Dashboard." This categorization ensures a quick and efficient search for the patterns tailored to your needs.

- **Insert a Pattern:** Click on one of the pattern to insert it directly into your edit content area.

- **Modify as Needed:** The inserted pattern serves as a starter template. Customize it effortlessly to align with your specific requirements. Modify text, colors, and any other elements to ensure your Dashboard page reflects your unique style.


= Features =

1. **Login Page Customization:**
   - Personalize the login, registration, and password reset pages with your brand colors, logo, and background images.

2. **Frontend Dashboard:**
   - Integrate a stylish frontend dashboard for users with customizable dashboard and menu content from selected pages.

3. **Branding Options:**
   - Customize the WordPress logo on the login page and dashboard, reinforcing your brand identity.

4. **Responsive Design:**
   - Ensure a consistent and responsive design across devices, providing a seamless experience for users on both desktop and mobile.

5. **Additional Features:**
   - Display login user info with avatar, advanced menu options with menu title, slug, and page selection, social links with headings, and a logout link.

There are two ways to install the plugin:

1. Upload the plugin's zip file via Dashboard -> Plugins -> Add New -> "Upload Plugin".
2. Extract the plugin folder and place it in the "/wp-content/plugins/" directory.

After installation, activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How can I customize the login pages? =

Navigate to the Brand Master settings page in the WordPress admin panel. Here, you can easily customize the login, registration, and password reset pages with your brand elements.

= What options are available for the frontend dashboard? =

The frontend dashboard offers user info with user avatar, advanced menu options with menu title, slug, and page selection, social links with headings, and a logout link. You can configure these options in the Brand Master settings.

= Can I add my own branding elements? =

Yes, you can upload your brand logo, set brand colors, and customize various elements to align with your brand identity.

= Does this change the WordPress backend dashboard? =

No, Brand Master does not alter the WordPress backend dashboard. It facilitates the creation of a customized frontend dashboard for users while keeping the backend dashboard unchanged.

= Does this work on multisite? =

Yes. Settings are stored per site. Network administrators can reach each site's Brand Master settings (the settings page and REST endpoint accept network admins in addition to per-site admins). Uninstalling removes the current site's settings; a network-wide uninstall cleans each site in turn.

== Screenshots ==

1. Dashboard - Getting started
2. Dashboard - Login Settings => Security and URL
3. Dashboard - Login Settings => Logo
4. Dashboard - Login Settings => Page Title
5. Dashboard - Login Settings => Utilities
6. Dashboard - Login Settings => Advanced
7. Dashboard - Frontend Dashboard Settings => Non-logged-in visitor page
8. Dashboard - Frontend Dashboard => Sidebar content
9. Dashboard - Frontend Dashboard => Header content
10. Dashboard - Frontend Dashboard => Elements
11. Dashboard - Settings => Advanced
12. Frontend - User Dashboard
13. Frontend - Login page with own logo

== Changelog ==

= 1.0.6 =
* Added: Tested up to WordPress 7.1
* Added: Deep recursive settings sanitization with unfiltered_html gate for custom CSS/JS
* Added: Login/redirect slug conflict validation (reserved slugs, page collisions)
* Added: uninstall.php for clean data deletion
* Added: PHPUnit test suite and GitHub Actions CI
* Security: Redirect URL validation via wp_validate_redirect (internal-only by default)
* Security: Closed /wp/v2/settings REST endpoint exposure
* Fix: Scoped site_url filter to wp-login.php paths only
* Fix: Social link target/rel attributes use whitelist-built values
* Accessibility: aria-current on active menu item; heading hierarchy fix
* i18n: Default labels translated at render time
* Security: Partial settings updates now deep-merge instead of overwriting the whole options row (no more silent sibling-data loss via the REST API)
* Security: Login-page CSS/JS render output neutralizes tag breakouts even if a stored value bypasses sanitization
* Fix: Legacy login globals ($error, $user_login) are only initialized when unset, preserving prior-handler values on lost-password/reset/registration flows
* Fix: Settings page and REST endpoint accept network admins on network-active multisite installs
* Fix: dashboard.userInfo.sort schema corrected; siteIdentity/userInfo logo fields constrained to their valid values

= 1.0.5 =
* Added: WordPress latest compatibility
* Fixed: Resolved issue with translation function preload

= 1.0.4 =
* Added: Tested with the latest WordPress
* Added: RTL support
* Updated: [Atomic CSS](https://github.com/codersantosh/atomic-css)
* Updated: npm packages
* Fixed: Admin Notices Hidden by Sticky Header on Plugin Settings Page #14

= 1.0.3 =
* Updated: Plugin URL
* Updated: Author links
* Updated: Contributors links

= 1.0.2 =
* Updated: Author
* Updated: Contributors

= 1.0.1 =
* Added: Tested with the latest WordPress
* Added: Plugin link to Settings.

= 1.0.0 =
* Initial release