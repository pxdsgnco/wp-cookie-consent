# Consent Raven - Developer Guide

This guide covers how to extend and customize Consent Raven for developers.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Hooks and Filters](#hooks-and-filters)
3. [REST API](#rest-api)
4. [JavaScript API](#javascript-api)
5. [Script Blocking](#script-blocking)
6. [Theme Customization](#theme-customization)
7. [Template Overrides](#template-overrides)

---

## Architecture Overview

### File Structure

```
consent-raven/
├── consent-raven.php          # Main plugin file
├── includes/
│   ├── class-cr-consent.php   # Core consent management
│   ├── class-cr-loader.php    # Hook registration
│   ├── class-cr-activator.php # Activation logic
│   └── class-cr-i18n.php      # Internationalization
├── admin/
│   ├── class-cr-admin.php     # Admin UI
│   ├── class-cr-rest-api.php  # REST endpoints
│   ├── class-cr-settings.php  # Settings validation
│   └── src/                   # React admin components
├── public/
│   ├── class-cr-public.php    # Frontend assets
│   ├── class-cr-banner.php    # Banner rendering
│   ├── class-cr-script-blocker.php # Script blocking
│   ├── class-cr-shortcodes.php # Shortcode/block
│   └── partials/
│       └── banner-template.php # Banner HTML template
└── assets/
    ├── css/
    │   └── frontend.css       # Banner styles
    └── js/
        └── frontend.js        # Consent JavaScript
```

### Key Classes

- **CR_Consent**: Central consent management (settings, categories, cookies, scripts CRUD)
- **CR_Banner**: Renders the consent banner HTML
- **CR_Script_Blocker**: Blocks scripts until consent is given
- **CR_Rest_API**: WordPress REST API endpoints
- **CR_Shortcodes**: Policy table shortcode and Gutenberg block

---

## Hooks and Filters

### Actions

#### `consent_raven_before_banner`

Fires before the banner HTML is output.

```php
add_action( 'consent_raven_before_banner', function() {
    echo '<!-- Custom content before banner -->';
});
```

#### `consent_raven_after_banner`

Fires after the banner HTML is output.

```php
add_action( 'consent_raven_after_banner', function() {
    echo '<!-- Custom content after banner -->';
});
```

#### `consent_raven_before_settings_update`

Fires before settings are updated.

```php
add_action( 'consent_raven_before_settings_update', function( $new_settings, $old_settings ) {
    // Log or validate settings changes
}, 10, 2 );
```

#### `consent_raven_after_settings_update`

Fires after settings are updated.

```php
add_action( 'consent_raven_after_settings_update', function( $settings ) {
    // Clear caches, notify services, etc.
});
```

### Filters

#### `consent_raven_banner_html`

Modify the banner HTML output.

```php
add_filter( 'consent_raven_banner_html', function( $html, $settings ) {
    // Wrap banner in custom container
    return '<div class="my-wrapper">' . $html . '</div>';
}, 10, 2 );
```

#### `consent_raven_categories`

Modify available cookie categories.

```php
add_filter( 'consent_raven_categories', function( $categories ) {
    // Add a custom category
    $categories[] = array(
        'id'          => 'custom',
        'slug'        => 'custom',
        'name'        => 'Custom Cookies',
        'description' => 'Custom tracking cookies',
        'essential'   => false,
    );
    return $categories;
});
```

#### `consent_raven_cookie_definitions`

Modify cookie definitions for the policy table.

```php
add_filter( 'consent_raven_cookie_definitions', function( $cookies ) {
    // Add custom cookie
    $cookies[] = array(
        'name'        => 'my_cookie',
        'category_id' => 'analytics',
        'provider'    => 'My Service',
        'purpose'     => 'Track page views',
        'expiration'  => '1 year',
    );
    return $cookies;
});
```

#### `consent_raven_should_block_script`

Control whether a specific script should be blocked.

```php
add_filter( 'consent_raven_should_block_script', function( $should_block, $handle, $category_id ) {
    // Never block scripts on specific pages
    if ( is_page( 'special-page' ) ) {
        return false;
    }
    return $should_block;
}, 10, 3 );
```

#### `consent_raven_policy_table_cookies`

Modify cookies displayed in the policy table.

```php
add_filter( 'consent_raven_policy_table_cookies', function( $cookies, $filter_category ) {
    // Filter out certain cookies from display
    return array_filter( $cookies, function( $cookie ) {
        return $cookie['name'] !== 'internal_cookie';
    });
}, 10, 2 );
```

#### `consent_raven_policy_table_html`

Modify the policy table HTML.

```php
add_filter( 'consent_raven_policy_table_html', function( $html, $cookies ) {
    // Add custom wrapper
    return '<div class="policy-container">' . $html . '</div>';
}, 10, 2 );
```

---

## REST API

All endpoints require `manage_options` capability.

### Base URL

```
/wp-json/consent-raven/v1/
```

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/settings` | Get all settings |
| POST | `/settings` | Update settings |
| GET | `/categories` | Get cookie categories |
| POST | `/categories` | Update categories |
| GET | `/cookies` | Get cookie definitions |
| POST | `/cookies` | Update cookies |
| GET | `/scripts` | Get registered scripts |
| POST | `/scripts` | Update scripts |
| GET | `/export` | Export all settings |
| POST | `/import` | Import settings |
| POST | `/reset` | Reset to defaults |
| POST | `/create-policy-page` | Create policy page |

### Example: Get Settings

```javascript
fetch( '/wp-json/consent-raven/v1/settings', {
    headers: {
        'X-WP-Nonce': wpApiSettings.nonce
    }
})
.then( response => response.json() )
.then( settings => console.log( settings ) );
```

### Example: Update Settings

```javascript
fetch( '/wp-json/consent-raven/v1/settings', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify({
        enabled: true,
        position: 'modal'
    })
});
```

---

## JavaScript API

The frontend exposes a global `ConsentRaven` object.

### Methods

#### `ConsentRaven.hasConsent( category )`

Check if consent is given for a category.

```javascript
if ( ConsentRaven.hasConsent( 'analytics' ) ) {
    // Load analytics
}
```

#### `ConsentRaven.getConsent()`

Get the full consent object.

```javascript
const consent = ConsentRaven.getConsent();
console.log( consent.categories );
```

#### `ConsentRaven.acceptAll()`

Accept all cookies programmatically.

```javascript
ConsentRaven.acceptAll();
```

#### `ConsentRaven.rejectAll()`

Reject non-essential cookies.

```javascript
ConsentRaven.rejectAll();
```

#### `ConsentRaven.resetConsent()`

Clear consent and show banner again.

```javascript
ConsentRaven.resetConsent();
```

### Events

Listen for consent changes:

```javascript
document.addEventListener( 'consent_raven_consent_updated', function( e ) {
    console.log( 'Categories:', e.detail.categories );
});

document.addEventListener( 'consent_raven_accept_all', function( e ) {
    console.log( 'User accepted all cookies' );
});

document.addEventListener( 'consent_raven_reject_all', function( e ) {
    console.log( 'User rejected non-essential cookies' );
});

document.addEventListener( 'consent_raven_category_enabled', function( e ) {
    console.log( 'Category enabled:', e.detail.category );
});
```

---

## Script Blocking

### Method 1: Type Swap

Changes `type="text/javascript"` to `type="text/plain"`. The script won't execute until consent is given.

```html
<!-- Before consent -->
<script type="text/plain" data-cookie-category="analytics" src="analytics.js"></script>

<!-- After consent, script is activated -->
```

### Method 2: Data Attribute

Adds `data-cookie-consent="pending"` attribute. Your code checks this before executing.

### Programmatic Registration

Register scripts for blocking in PHP:

```php
CR_Script_Blocker::register_script( array(
    'handle'      => 'google-analytics',
    'category_id' => 'analytics',
    'method'      => 'type-swap',
));

// Or by URL pattern
CR_Script_Blocker::register_script( array(
    'pattern'     => 'google-analytics\.com',
    'category_id' => 'analytics',
    'method'      => 'type-swap',
));
```

### Inline Script Blocking

For inline scripts, use a pattern:

```php
CR_Script_Blocker::register_script( array(
    'pattern'     => 'ga\(.*create',
    'category_id' => 'analytics',
    'method'      => 'inline',
));
```

---

## Theme Customization

### CSS Custom Properties

Override CSS variables to customize appearance:

```css
:root {
    --cr-bg-color: #ffffff;
    --cr-text-color: #333333;
    --cr-secondary-color: #666666;
    --cr-button-bg: #0073aa;
    --cr-button-text: #ffffff;
    --cr-button-radius: 4px;
    --cr-dialog-radius: 8px;
}
```

### Programmatic Styling

```php
add_filter( 'consent_raven_banner_html', function( $html ) {
    return '<style>.cr-banner { /* custom styles */ }</style>' . $html;
});
```

---

## Template Overrides

Copy the banner template to your theme to customize:

```
your-theme/
└── consent-raven/
    └── banner-template.php
```

The template receives these variables:

- `$settings` - Plugin settings array
- `$categories` - Cookie categories array
- `$content` - Content strings (title, description, buttons)
- `$position` - Banner position
- `$policy_url` - Policy page URL
- `$description` - Description with policy link inserted

---

## Testing

### PHPUnit

```bash
./vendor/bin/phpunit
```

### Jest

```bash
npm run test:js
```

---

## Support

- GitHub Issues: https://github.com/pxdsgnco/wp-cookie-consent/issues
- Documentation: See `/docs` directory
