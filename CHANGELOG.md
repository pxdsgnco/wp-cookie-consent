# Changelog

All notable changes to Consent Raven will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-07

### Added
- Cookie consent banner with multiple positions (bottom-right dialog, bottom bar, top bar, modal)
- Granular cookie categories: Essential (always on), Analytics, and Marketing
- Script blocking with type swap and data attribute methods
- Output buffering for inline script blocking
- React-based admin interface with tabbed navigation
- Live banner preview with position and device switching
- 7 theme presets: Dark, Light, Ocean, Forest, Sunset, Minimal, Royal
- WCAG color contrast checker in admin appearance settings
- Import/export and reset functionality in Tools panel
- Search and filter functionality for admin tables
- Bulk actions for cookie and script management
- Field-level validation in admin forms
- Screen reader live region announcements (WCAG 2.1 AA compliant)
- Cookie policy shortcode `[consent_raven_policy_table]`
- Gutenberg block for cookie policy table
- Policy page generator wizard in admin Tools
- Consent logging with admin UI including stats, filters, and pagination
- Configurable log retention period (3, 6, or 12 months)
- REST API endpoints for settings, categories, cookies, scripts, and logs
- Developer hooks: `consent_raven_consent_updated`, `consent_raven_before_banner`, `consent_raven_after_banner`, `consent_raven_category_enabled`
- Developer filters: `consent_raven_banner_html`, `consent_raven_categories`, `consent_raven_cookie_definitions`, `consent_raven_should_block_script`
- Pre-populated cookie definitions for WordPress, Google Analytics, Facebook Pixel, Google DoubleClick
- PHPUnit tests (68 tests across 4 test files)
- Jest tests (40+ frontend tests)
- Developer documentation in docs/ folder

### Fixed
- Admin script loading with proper wp-scripts dependencies
- REST API false-failure reporting for unchanged option values
- Modal close button styling and functionality
- "Always on" badge styling in category toggles
