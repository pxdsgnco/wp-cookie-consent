# Consent Raven

A lightweight WordPress cookie consent plugin with customizable banners, granular category control, and GDPR/NDPR compliance features.

![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple)
![License](https://img.shields.io/badge/License-GPL--2.0%2B-green)

## Description

Consent Raven is a lightweight, customizable WordPress plugin that displays a cookie consent dialog and manages cookie preferences in line with major privacy regimes such as GDPR, UK GDPR, NDPR, and similar frameworks.

The plugin provides a modern, accessible interface for both site visitors and administrators, with comprehensive options for customizing appearance, managing cookie categories, and blocking scripts until consent is given.

## Features

### Banner & Display
- **Multiple Banner Positions** - Bottom-right floating dialog (default), bottom bar, top bar, or centered modal
- **Live Preview** - See changes in real-time as you customize in the admin interface
- **7 Theme Presets** - Dark, Light, Ocean, Forest, Sunset, Minimal, and Royal themes
- **Fully Responsive** - Works beautifully on desktop, tablet, and mobile devices

### Cookie Management
- **Granular Categories** - Pre-configured Essential (always on), Analytics, and Marketing categories
- **Custom Categories** - Add, edit, and remove categories through the admin interface or programmatically
- **Pre-populated Definitions** - Common cookies already defined (WordPress, Google Analytics, Facebook Pixel, Google DoubleClick)

### Script Blocking
- **Type Swap Method** - Changes script type to `text/plain` until consent is given
- **Data Attribute Method** - Adds `data-cookie-category` for JavaScript handling
- **Inline Script Blocking** - Output buffering captures and modifies inline scripts

### Admin Interface
- **React-based Settings** - Modern, tabbed admin interface for easy configuration
- **WCAG Color Contrast Checker** - Ensure your banner meets accessibility standards
- **Import/Export** - Backup and restore your settings
- **Bulk Actions** - Manage multiple cookies and scripts at once
- **Search & Filter** - Quickly find what you need in large lists

### Compliance & Logging
- **Consent Logging** - Track user consent decisions with admin dashboard
- **Configurable Retention** - Keep logs for 3, 6, or 12 months
- **Stats & Filters** - View consent statistics and filter logs by date, status, and more
- **Automatic Cleanup** - Scheduled cleanup of old logs via WordPress cron

### Content Features
- **Policy Table Shortcode** - Display cookie information with `[consent_raven_policy_table]`
- **Gutenberg Block** - Add cookie policy table in the block editor
- **Policy Page Generator** - Wizard to create a complete privacy policy page

### Accessibility
- **WCAG 2.1 AA Compliant** - Full keyboard navigation and screen reader support
- **Focus Management** - Proper focus indicators and tab order
- **ARIA Attributes** - Correct roles and states for assistive technology
- **Skip Link** - Keyboard users can bypass the banner

### Developer Features
- **Full REST API** - Manage all settings programmatically
- **Extensive Hooks** - Actions and filters for customization
- **Translation Ready** - Fully internationalized (i18n)
- **Tested** - PHPUnit and Jest test suites included

## Pre-populated Cookie Definitions

Consent Raven comes with pre-configured definitions for common cookies:

| Service | Cookies | Category |
|---------|---------|----------|
| WordPress | `wordpress_*`, `wp-settings-*` | Essential |
| Google Analytics | `_ga`, `_gid`, `_gat` | Analytics |
| Facebook Pixel | `_fbp`, `_fbc`, `fr` | Marketing |
| Google DoubleClick | `IDE`, `test_cookie` | Marketing |

## Installation

### Manual Installation
1. Download the plugin and extract the `consent-raven` folder
2. Upload the folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to 'Consent Raven' in the admin menu to configure settings

### WordPress Admin
1. Go to Plugins > Add New in your WordPress admin
2. Search for "Consent Raven"
3. Click "Install Now" and then "Activate"
4. Go to 'Consent Raven' to configure settings

## Configuration

### General Settings
- Enable or disable the consent banner
- Select banner position (dialog, bottom bar, top bar, modal)
- Link to your privacy/cookie policy page
- Configure log retention period

### Appearance
- Choose a theme preset or customize colors
- Set background, text, and button colors
- Adjust border radius for buttons and dialog
- Use the color contrast checker to ensure accessibility

### Cookie Categories
- View and edit existing categories
- Add custom categories with name, description, and default state
- Mark categories as required (cannot be disabled by users)

### Script Blocking
- Register scripts to be blocked until consent
- Choose blocking method (type swap or data attribute)
- Assign scripts to specific cookie categories

## Developer Documentation

### Actions

```php
// Fired when consent is given or updated
do_action( 'consent_raven_consent_updated', $consent_data );

// Fired before banner renders
do_action( 'consent_raven_before_banner' );

// Fired after banner renders
do_action( 'consent_raven_after_banner' );

// Fired when a specific category is enabled
do_action( 'consent_raven_category_enabled', $category_id );
```

### Filters

```php
// Modify banner HTML output
$html = apply_filters( 'consent_raven_banner_html', $html );

// Modify available cookie categories
$categories = apply_filters( 'consent_raven_categories', $categories );

// Modify cookie definitions
$cookies = apply_filters( 'consent_raven_cookie_definitions', $cookies );

// Control whether a specific script should be blocked
$should_block = apply_filters( 'consent_raven_should_block_script', $should_block, $script_handle );
```

### REST API Endpoints

All endpoints are available at `/wp-json/consent-raven/v1/`:

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/settings` | GET, POST | Manage plugin settings |
| `/categories` | GET, POST, DELETE | Manage cookie categories |
| `/cookies` | GET, POST, DELETE | Manage cookie definitions |
| `/scripts` | GET, POST, DELETE | Manage blocked scripts |
| `/logs` | GET, DELETE | View and manage consent logs |
| `/export` | GET | Export all settings |
| `/import` | POST | Import settings |

### Programmatic Script Registration

```php
// Register a script for blocking
CR_Script_Blocker::register_script(
    'my-tracking-script',
    'analytics',      // category
    'type_swap'       // blocking method
);
```

For more detailed developer documentation, see [docs/developer-guide.md](docs/developer-guide.md).

## Frequently Asked Questions

### Is Consent Raven GDPR compliant?

Consent Raven provides the tools needed for GDPR compliance, including granular consent categories, consent storage, and script blocking. However, compliance also depends on how you configure and use the plugin. We recommend consulting with a legal professional for your specific situation.

### Can I customize the banner appearance?

Yes! Consent Raven offers extensive customization options including colors, border radius, position, and all text content through the admin interface. You can also choose from 7 pre-built theme presets.

### Does it work with caching plugins?

Yes, Consent Raven is designed to work with caching plugins. All consent logic runs client-side via JavaScript to avoid cache conflicts.

### Can I add custom cookie categories?

Yes, you can add, edit, and remove cookie categories through the admin interface or programmatically using the `consent_raven_categories` filter.

### How do I block a specific script until consent?

You can register scripts for blocking through the admin interface under 'Scripts', or programmatically using the `CR_Script_Blocker::register_script()` method.

### How long are consent logs stored?

You can configure the log retention period in the General settings. Options are 3 months, 6 months, or 12 months. Logs older than the retention period are automatically cleaned up via WordPress cron.

## Requirements

- WordPress 5.8 or higher
- PHP 7.2 or higher
- Modern browser (last 2 versions of Chrome, Firefox, Safari, Edge)

## Documentation

- [Developer Guide](docs/developer-guide.md) - Hooks, filters, and API reference
- [Project Specification](docs/project-specification.md) - Technical architecture
- [Testing Checklist](docs/testing-checklist.md) - QA procedures

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

Consent Raven is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```
