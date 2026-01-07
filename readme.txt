=== Consent Raven ===
Contributors: consentraven
Tags: cookie consent, gdpr, privacy, cookie banner, cookie policy
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A lightweight cookie consent plugin with customizable banners, granular category control, and GDPR/NDPR compliance features.

== Description ==

Consent Raven is a lightweight, customizable WordPress plugin that displays a cookie consent dialog and manages cookie preferences in line with major privacy regimes such as GDPR, UK GDPR, NDPR, and similar frameworks.

= Key Features =

* **Customizable Banner Positions** - Choose from bottom-right floating dialog (default), bottom bar, top bar, or centered modal
* **Granular Cookie Categories** - Pre-configured Essential, Analytics, and Marketing categories with easy customization
* **Script Blocking** - Block third-party scripts until user consent is given
* **Modern Admin Interface** - React-based settings panel for easy configuration
* **Fully Responsive** - Works beautifully on desktop, tablet, and mobile devices
* **Accessibility Ready** - WCAG 2.1 AA compliant with full keyboard navigation support
* **Developer Friendly** - Extensive hooks and filters for customization
* **Translation Ready** - Fully internationalized and ready for translation

= Cookie Categories =

* **Essential** - Necessary cookies that cannot be disabled
* **Analytics** - Cookies for tracking site usage and performance
* **Marketing** - Cookies for advertising and personalization

= Pre-populated Cookie Definitions =

Consent Raven comes with pre-configured definitions for common cookies including:

* WordPress authentication cookies
* Google Analytics (_ga, _gid, _gat)
* Facebook Pixel (_fbp, _fbc, fr)
* Google DoubleClick (IDE, test_cookie)

= Script Blocking Methods =

* **Type Swap** - Changes script type to text/plain until consent
* **Data Attribute** - Adds data-cookie-category for JavaScript handling

= For Developers =

Consent Raven provides extensive hooks and filters:

**Actions:**
* `consent_raven_consent_updated` - Fired when consent is given/updated
* `consent_raven_before_banner` - Fired before banner renders
* `consent_raven_after_banner` - Fired after banner renders
* `consent_raven_category_enabled` - Fired when a category is enabled

**Filters:**
* `consent_raven_banner_html` - Modify banner HTML
* `consent_raven_categories` - Modify cookie categories
* `consent_raven_cookie_definitions` - Modify cookie definitions
* `consent_raven_should_block_script` - Control script blocking

= REST API =

Full REST API available at `/wp-json/consent-raven/v1/` for:

* Settings management
* Cookie categories
* Cookie definitions
* Script blocking configuration

== Installation ==

1. Upload the `consent-raven` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Consent Raven' in the admin menu to configure settings
4. Customize the banner appearance, text, and cookie categories
5. The consent banner will automatically appear on your site

== Frequently Asked Questions ==

= Is Consent Raven GDPR compliant? =

Consent Raven provides the tools needed for GDPR compliance, including granular consent categories, consent storage, and script blocking. However, compliance also depends on how you configure and use the plugin. We recommend consulting with a legal professional for your specific situation.

= Can I customize the banner appearance? =

Yes! Consent Raven offers extensive customization options including colors, border radius, position, and all text content through the admin interface.

= Does it work with caching plugins? =

Yes, Consent Raven is designed to work with caching plugins. All consent logic runs client-side via JavaScript to avoid cache conflicts.

= Can I add custom cookie categories? =

Yes, you can add, edit, and remove cookie categories through the admin interface or programmatically using filters.

= How do I block a specific script until consent? =

You can register scripts for blocking through the admin interface under 'Scripts', or programmatically using the `CR_Script_Blocker::register_script()` method.

== Screenshots ==

1. Cookie consent banner (bottom-right position)
2. Preferences modal for granular control
3. Admin settings - General options
4. Admin settings - Appearance customization
5. Admin settings - Cookie categories
6. Admin settings - Script blocking

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Consent Raven.
