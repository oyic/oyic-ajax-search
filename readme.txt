=== Oyic - AJAX Full-Screen Search ===
Contributors: oyicteam
Donate link: https://oyic.dev/donate
Tags: search, ajax, full-screen, overlay, custom-post-types
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, accessible full-screen AJAX search with custom post type filtering, pagination, and customizable appearance.

== Description ==

Oyic - AJAX Full-Screen Search provides a modern, full-screen search overlay experience for your WordPress site. The plugin uses the WordPress REST API for fast, reliable search results without page reloads.

**Key Features:**

* **Full-Screen Search Overlay** - Beautiful, distraction-free search experience
* **AJAX-Powered** - Fast search results using WordPress REST API
* **Custom Post Type Support** - Search across any registered post types
* **Customizable Appearance** - Adjust background color, opacity, and search icon
* **Navigation Menu Integration** - Add search icon directly to your menus
* **Pagination Support** - Browse through multiple pages of results
* **Accessibility Ready** - Proper ARIA labels and keyboard navigation
* **Mobile Responsive** - Works perfectly on all devices
* **Translation Ready** - Full internationalization support

**How to Use:**

1. Use the shortcode `[oyic_search_button]` anywhere on your site
2. Or add to navigation menus via Appearance > Menus (see FAQ)
3. Customize appearance in Settings > Oyic Search

**Perfect For:**

* Blogs and news sites
* E-commerce stores
* Corporate websites
* Portfolio sites
* Any WordPress site needing better search

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/oyic-ajax-search` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings > Oyic Search screen to configure the plugin
4. Add the shortcode `[oyic_search_button]` to any page, post, or widget area

== Frequently Asked Questions ==

= How do I add the search icon to my navigation menu? =

1. Go to Appearance > Menus
2. Add a "Custom Link" with URL: `#search`
3. Set the link text to "Search"
4. The plugin will automatically replace the link with your search icon

= Can I customize the search icon? =

Yes! In Settings > Oyic Search, you can:
* Upload a custom image (recommended size: 24x24 pixels)
* Add custom SVG code
* SVG takes priority over images if both are provided

= Which post types can be searched? =

You can select any registered public post types in the plugin settings. By default, it searches Posts and Pages.

= Does this work with custom themes? =

Yes! The plugin is designed to work with any properly coded WordPress theme. The search overlay uses its own styling that won't conflict with your theme.

= Is this plugin GDPR compliant? =

Yes, the plugin doesn't track users or store personal data. All searches are performed using WordPress's built-in REST API.

= Can I translate this plugin? =

Absolutely! The plugin is fully translation-ready. You can translate it using tools like Loco Translate or by creating .po files.

== Screenshots ==

1. Full-screen search overlay with results
2. Admin settings page with customization options
3. Search icon in navigation menu
4. Mobile responsive design

== Changelog ==

= 1.0.0 =
* Initial release
* Full-screen AJAX search overlay
* Custom post type filtering
* Customizable appearance options
* Navigation menu integration
* REST API powered search
* Accessibility features
* Mobile responsive design
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of Oyic - AJAX Full-Screen Search.

== Developer Notes ==

This plugin follows WordPress coding standards and uses:
* WordPress REST API for search functionality
* Modern JavaScript (ES6+)
* Tailwind CSS for styling
* Vite for asset building
* PSR-4 autoloading

The plugin is built with performance and accessibility in mind, ensuring a great experience for all users.

== Support ==

For support, feature requests, or bug reports, please visit our [support forum](https://wordpress.org/support/plugin/oyic-ajax-search/) or contact us at oyic@outlook.com.
