# Consent Raven - Project Specification

> Finalized specification based on product brief review and stakeholder Q&A.

---

## 1. Project Identity

| Attribute | Value |
|-----------|-------|
| **Plugin Name** | Consent Raven |
| **Plugin Slug** | `consent-raven` |
| **Text Domain** | `consent-raven` |
| **Function Prefix** | `consent_raven_` |
| **Class Prefix** | `CR_` |
| **Version** | 1.0.0 (MVP) |

---

## 2. Technical Requirements

### Environment
| Requirement | Version |
|-------------|---------|
| WordPress | 5.8+ |
| PHP | 7.2+ |
| Browsers | Last 2 versions of major browsers (Chrome, Firefox, Safari, Edge) |

### Technology Stack
| Component | Technology |
|-----------|------------|
| **Frontend (Public)** | Pure Vanilla JavaScript (no dependencies) |
| **Frontend (Admin)** | React-based modern UI |
| **Editor Support** | Gutenberg Blocks + Classic Shortcodes |
| **Styling** | CSS (BEM methodology recommended) |
| **Testing (PHP)** | PHPUnit |
| **Testing (JS)** | Jest |
| **Coding Standards** | WordPress Coding Standards (PHPCS) |

---

## 3. Design Specification

### Default Theme (Based on Claude/Anthropic Cookie Dialog)

#### Colors
| Element | Value |
|---------|-------|
| Dialog Background | `#1a1a1a` (Dark charcoal) |
| Text Color | `#ffffff` (White) |
| Secondary Text | `#b3b3b3` (Light gray) |
| Primary Button BG | `#ffffff` (White) |
| Primary Button Text | `#1a1a1a` (Dark) |
| Secondary Button BG | `transparent` |
| Secondary Button Border | `#ffffff` |
| Secondary Button Text | `#ffffff` |
| Link Color | `#ffffff` (underlined) |

#### Typography
| Element | Style |
|---------|-------|
| Title | Bold, 18-20px |
| Body Text | Regular, 14px, 1.5 line-height |
| Button Text | Medium, 14px |

#### Layout
| Property | Value |
|----------|-------|
| Border Radius | 16-20px |
| Padding | 24px |
| Max Width | 400px |
| Button Border Radius | 8px |

### Banner Positions (All Available in MVP)
1. **Bottom-right floating** (DEFAULT)
2. Bottom bar (full-width)
3. Top bar (full-width)
4. Centered modal (with overlay)

### Button Layout
```
┌─────────────────────────────────┐
│ Cookie settings                 │
│                                 │
│ [Description text...]           │
│                                 │
│ ┌─────────────────────────────┐ │
│ │  Customize Cookie Settings  │ │ ← Outlined, full-width
│ └─────────────────────────────┘ │
│                                 │
│ ┌─────────────┐ ┌─────────────┐ │
│ │Reject All   │ │Accept All   │ │ ← Side by side
│ │(outlined)   │ │(filled)     │ │
│ └─────────────┘ └─────────────┘ │
└─────────────────────────────────┘
```

---

## 4. MVP Feature Scope

### Included in MVP

#### End-User Features
- [x] Cookie consent banner/dialog display
- [x] Configurable positions (bottom-right, bottom bar, top bar, centered modal)
- [x] Responsive layout (mobile, tablet, desktop)
- [x] "Accept All Cookies" CTA
- [x] "Reject All Cookies" CTA
- [x] "Customize Cookie Settings" opening preferences modal
- [x] Cookie categories with toggle controls (Essential, Analytics, Marketing)
- [x] Persistent "Cookie settings" link/button to revisit preferences
- [x] Link to cookie policy page
- [x] Consent stored client-side (cookie/localStorage)

#### Admin Features
- [x] Modern React-based settings UI
- [x] Appearance customization (colors, border radius, button styles)
- [x] Content customization (all text strings editable)
- [x] Cookie categories management (name, description, essential flag)
- [x] Cookie definitions management (for policy table)
- [x] Pre-populated cookie definitions for common services
- [x] Cookie policy page generator wizard
- [x] Gutenberg block: `[consent-raven-policy-table]`
- [x] Shortcode: `[consent_raven_policy_table]`
- [x] Script blocking configuration

#### Technical Features
- [x] Script blocking via `type="text/plain"` attribute swapping
- [x] Script blocking via `data-cookie-category` attribute
- [x] Consent state machine (no consent → accept/reject → manage)
- [x] Consent versioning (re-prompt on policy changes)
- [x] WordPress internationalization (i18n) ready
- [x] Accessibility (ARIA, keyboard navigation, focus trapping)
- [x] Developer hooks and filters

### Excluded from MVP
- [ ] Custom logo/icon in banner
- [ ] Cookie scanner (auto-detect cookies)
- [ ] Geolocation-based behavior
- [ ] Server-side consent logging
- [ ] Consent export (CSV)
- [ ] Multi-language text variants (per-jurisdiction)
- [ ] Configuration import/export

---

## 5. Cookie Categories (Default)

| ID | Slug | Name | Essential | Description |
|----|------|------|-----------|-------------|
| 1 | `essential` | Essential | Yes | Necessary cookies for the website to function properly. These cannot be disabled. |
| 2 | `analytics` | Analytics | No | Cookies that help us understand how visitors interact with our website. |
| 3 | `marketing` | Marketing | No | Cookies used to deliver personalized advertisements and track campaign performance. |

---

## 6. Pre-Populated Cookie Definitions

### Essential Cookies
| Cookie Name | Provider | Purpose | Expiration |
|-------------|----------|---------|------------|
| `wordpress_logged_in_*` | WordPress | User authentication | Session |
| `wordpress_sec_*` | WordPress | Secure authentication | Session |
| `wp-settings-*` | WordPress | User preferences | 1 year |
| `consent_raven` | Consent Raven | Stores cookie consent preferences | 1 year |

### Analytics Cookies
| Cookie Name | Provider | Purpose | Expiration |
|-------------|----------|---------|------------|
| `_ga` | Google Analytics | Distinguishes unique users | 2 years |
| `_ga_*` | Google Analytics 4 | Maintains session state | 2 years |
| `_gid` | Google Analytics | Distinguishes unique users | 24 hours |
| `_gat` | Google Analytics | Throttle request rate | 1 minute |

### Marketing Cookies
| Cookie Name | Provider | Purpose | Expiration |
|-------------|----------|---------|------------|
| `_fbp` | Facebook Pixel | Tracks visits across websites | 3 months |
| `_fbc` | Facebook Pixel | Stores last visit | 3 months |
| `fr` | Facebook | Ad delivery and measurement | 3 months |
| `IDE` | Google DoubleClick | Ad targeting | 1 year |
| `test_cookie` | Google DoubleClick | Check if browser accepts cookies | 15 minutes |

---

## 7. Data Storage

### WordPress Options (wp_options)
```php
// Main settings
'consent_raven_settings' => [
    'enabled' => true,
    'position' => 'bottom-right', // bottom-right, bottom-bar, top-bar, modal
    'policy_page_id' => 0,
    'consent_version' => '1.0',
    'appearance' => [
        'theme' => 'dark', // dark, light, custom
        'background_color' => '#1a1a1a',
        'text_color' => '#ffffff',
        'button_radius' => '8px',
        'dialog_radius' => '16px',
    ],
    'content' => [
        'title' => 'Cookie settings',
        'description' => 'We use cookies to deliver and improve our services...',
        'accept_button' => 'Accept All Cookies',
        'reject_button' => 'Reject All Cookies',
        'customize_button' => 'Customize Cookie Settings',
        'policy_link_text' => 'Cookie Policy',
    ],
]

// Cookie categories
'consent_raven_categories' => [
    ['id' => 1, 'slug' => 'essential', 'name' => 'Essential', ...],
    ['id' => 2, 'slug' => 'analytics', 'name' => 'Analytics', ...],
    ['id' => 3, 'slug' => 'marketing', 'name' => 'Marketing', ...],
]

// Cookie definitions
'consent_raven_cookies' => [
    ['name' => '_ga', 'category_id' => 2, 'provider' => 'Google Analytics', ...],
    // ...
]

// Registered scripts for blocking
'consent_raven_scripts' => [
    ['id' => 1, 'category_id' => 2, 'script' => '...', 'method' => 'inline'],
    // ...
]
```

### Client-Side Storage (Cookie)
```javascript
// Cookie name: consent_raven
// Value (JSON encoded):
{
    "version": "1.0",
    "timestamp": 1704067200,
    "categories": {
        "essential": true,
        "analytics": false,
        "marketing": false
    }
}
```

---

## 8. File Structure

```
consent-raven/
├── consent-raven.php              # Main plugin file
├── uninstall.php                  # Cleanup on uninstall
├── readme.txt                     # WordPress.org readme
├── package.json                   # NPM dependencies
├── composer.json                  # PHP dependencies
├── webpack.config.js              # Build configuration
├── phpcs.xml                      # PHPCS configuration
├── phpunit.xml                    # PHPUnit configuration
│
├── assets/
│   ├── css/
│   │   ├── frontend.css           # Public-facing styles
│   │   └── admin.css              # Admin styles (compiled)
│   ├── js/
│   │   ├── frontend.js            # Vanilla JS for banner
│   │   └── admin.js               # React admin (compiled)
│   └── images/
│
├── includes/
│   ├── class-cr-loader.php        # Plugin loader
│   ├── class-cr-activator.php     # Activation hooks
│   ├── class-cr-deactivator.php   # Deactivation hooks
│   ├── class-cr-i18n.php          # Internationalization
│   └── class-cr-consent.php       # Consent logic
│
├── admin/
│   ├── class-cr-admin.php         # Admin functionality
│   ├── class-cr-settings.php      # Settings registration
│   ├── class-cr-rest-api.php      # REST API endpoints
│   └── src/                       # React source files
│       ├── index.js
│       ├── App.js
│       └── components/
│
├── public/
│   ├── class-cr-public.php        # Public functionality
│   ├── class-cr-banner.php        # Banner rendering
│   ├── class-cr-script-blocker.php # Script blocking
│   └── partials/
│       └── banner-template.php
│
├── blocks/
│   ├── policy-table/
│   │   ├── block.json
│   │   ├── index.js
│   │   └── edit.js
│   └── build/
│
├── languages/
│   └── consent-raven.pot          # Translation template
│
└── tests/
    ├── phpunit/
    │   └── test-consent.php
    └── jest/
        └── admin.test.js
```

---

## 9. Hooks & Filters (Developer API)

### Actions
```php
// Fired when consent is given/updated
do_action('consent_raven_consent_updated', $categories, $version);

// Fired before banner renders
do_action('consent_raven_before_banner');

// Fired after banner renders
do_action('consent_raven_after_banner');

// Fired when a category is enabled
do_action('consent_raven_category_enabled', $category_slug);
```

### Filters
```php
// Modify banner HTML
apply_filters('consent_raven_banner_html', $html, $settings);

// Modify categories
apply_filters('consent_raven_categories', $categories);

// Modify cookie definitions
apply_filters('consent_raven_cookie_definitions', $cookies);

// Modify consent cookie settings
apply_filters('consent_raven_cookie_settings', $cookie_settings);

// Modify script blocking behavior
apply_filters('consent_raven_should_block_script', $should_block, $script, $category);
```

---

## 10. REST API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/consent-raven/v1/settings` | GET | Get all settings |
| `/wp-json/consent-raven/v1/settings` | POST | Update settings |
| `/wp-json/consent-raven/v1/categories` | GET | Get cookie categories |
| `/wp-json/consent-raven/v1/categories` | POST | Update categories |
| `/wp-json/consent-raven/v1/cookies` | GET | Get cookie definitions |
| `/wp-json/consent-raven/v1/cookies` | POST | Update cookie definitions |
| `/wp-json/consent-raven/v1/scripts` | GET | Get registered scripts |
| `/wp-json/consent-raven/v1/scripts` | POST | Update scripts |

---

## 11. Accessibility Requirements

- WCAG 2.1 AA compliance target
- ARIA roles: `dialog`, `alertdialog` for modal
- ARIA labels for all interactive elements
- Keyboard navigation support (Tab, Shift+Tab, Enter, Escape)
- Focus trapping inside modal when open
- Focus returns to trigger element on close
- Color contrast ratio minimum 4.5:1
- Screen reader announcements for consent updates
- Reduced motion support (`prefers-reduced-motion`)

---

## 12. Browser Support

| Browser | Versions |
|---------|----------|
| Chrome | Last 2 |
| Firefox | Last 2 |
| Safari | Last 2 |
| Edge | Last 2 |
| iOS Safari | Last 2 |
| Chrome Android | Last 2 |

---

## 13. Testing Strategy

### PHP (PHPUnit)
- Unit tests for consent logic
- Unit tests for settings sanitization
- Integration tests for REST API endpoints
- Integration tests for shortcode output

### JavaScript (Jest)
- Unit tests for React admin components
- Unit tests for consent state management
- Unit tests for cookie handling

### Manual QA
- Cross-browser testing
- Responsive design testing
- Accessibility testing (keyboard, screen reader)
- Cache plugin compatibility testing

---

## 14. Caching Compatibility

The plugin will be tested with major caching plugins:
- WP Super Cache
- W3 Total Cache
- WP Rocket
- LiteSpeed Cache

**Strategy**: All consent logic runs client-side via JavaScript to avoid cache conflicts. The banner markup is included in the page but hidden by default, then shown/hidden via JS based on consent state.

---

## 15. Development Workflow

### Branch Naming Convention
- `main` - Production-ready code
- `develop` - Integration branch
- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `hotfix/urgent-fix` - Production hotfixes
- `release/x.x.x` - Release preparation

### Commit Message Convention
```
type(scope): description

Types: feat, fix, docs, style, refactor, test, chore
Example: feat(banner): add bottom-right position option
```

### PR Requirements
- Code passes PHPCS (WordPress standards)
- Code passes ESLint
- All tests pass
- PR description includes:
  - Summary of changes
  - Testing instructions
  - Screenshots (for UI changes)

---

## 16. Milestones

### Phase 1: Foundation
- [ ] Plugin scaffold and file structure
- [ ] Activation/deactivation hooks
- [ ] Settings data model
- [ ] REST API endpoints
- [ ] Admin React app setup

### Phase 2: Admin Interface
- [ ] General settings page
- [ ] Appearance settings
- [ ] Content settings
- [ ] Cookie categories management
- [ ] Cookie definitions management
- [ ] Script registration

### Phase 3: Frontend Banner
- [ ] Banner HTML/CSS (all positions)
- [ ] Vanilla JS consent logic
- [ ] Preferences modal
- [ ] Script blocking implementation
- [ ] Cookie storage

### Phase 4: Content Features
- [ ] Cookie policy shortcode
- [ ] Cookie policy Gutenberg block
- [ ] Policy page generator wizard

### Phase 5: Polish & Testing
- [ ] Accessibility audit
- [ ] Cross-browser testing
- [ ] Performance optimization
- [ ] Documentation
- [ ] PHPUnit tests
- [ ] Jest tests

---

*Document Version: 1.0*
*Last Updated: 2026-01-07*
