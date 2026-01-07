# Consent Raven - Testing Checklist

Use this checklist for manual QA testing before releases.

---

## Banner Functionality

### Initial Display

- [ ] Banner displays on first visit (no existing consent)
- [ ] Banner respects enabled/disabled setting
- [ ] Banner position matches selected option:
  - [ ] Bottom-right (floating)
  - [ ] Bottom bar (full-width)
  - [ ] Top bar (full-width)
  - [ ] Centered modal (with overlay)

### Button Actions

- [ ] "Accept All" accepts all categories
- [ ] "Accept All" hides banner and shows settings button
- [ ] "Reject All" accepts only essential categories
- [ ] "Reject All" hides banner and shows settings button
- [ ] "Customize" opens preferences modal

### Preferences Modal

- [ ] Modal opens correctly
- [ ] All categories display with correct names and descriptions
- [ ] Essential categories show "Always on" badge
- [ ] Non-essential categories have toggle switches
- [ ] Toggle switches work correctly
- [ ] "Save" button saves preferences
- [ ] Close button (×) closes modal
- [ ] Clicking overlay closes modal
- [ ] Escape key closes modal

### Settings Button

- [ ] Settings button appears after consent given
- [ ] Settings button opens preferences modal
- [ ] Settings button positioned correctly (bottom-left)

### Consent Persistence

- [ ] Consent saved to cookie
- [ ] Consent persists across page loads
- [ ] Consent persists across browser sessions
- [ ] Banner does not show on return visit with valid consent
- [ ] Banner shows again when consent version changes

---

## Accessibility (WCAG 2.1 AA)

### Keyboard Navigation

- [ ] Can Tab through all interactive elements
- [ ] Tab order is logical
- [ ] Focus trap works in banner modal
- [ ] Focus trap works in preferences modal
- [ ] Escape key closes modals
- [ ] Focus returns to trigger element on modal close
- [ ] Skip link allows bypassing banner

### Screen Readers

- [ ] Banner announced as dialog
- [ ] Title and description announced
- [ ] Button labels announced correctly
- [ ] Toggle switch states announced
- [ ] Consent actions announced via live region
- [ ] Essential categories announced as "Always on"

### Visual Accessibility

- [ ] Focus indicators visible on all interactive elements
- [ ] Focus indicators have sufficient contrast (3:1)
- [ ] Color contrast meets AA standards (4.5:1 for text)
- [ ] Text is readable at 200% zoom
- [ ] Animations respect `prefers-reduced-motion`

### ARIA

- [ ] `role="dialog"` on banner and preferences
- [ ] `aria-modal="true"` on modals
- [ ] `aria-labelledby` references title
- [ ] `aria-describedby` references description
- [ ] `aria-expanded` on customize button updates
- [ ] `aria-checked` on toggle switches updates
- [ ] `role="switch"` on toggle inputs
- [ ] `aria-live="polite"` on announcer region

---

## Responsive Design

### Mobile (< 600px)

- [ ] Banner fills full width
- [ ] Buttons stack vertically
- [ ] Text is readable
- [ ] Touch targets are at least 44×44px
- [ ] Preferences modal scrollable if content overflows
- [ ] No horizontal scrolling

### Tablet (600-768px)

- [ ] Layout adapts appropriately
- [ ] All elements accessible
- [ ] Touch-friendly sizing

### Desktop (> 768px)

- [ ] Banner displays in correct position
- [ ] Buttons arranged horizontally
- [ ] Appropriate whitespace

---

## Browser Compatibility

Test all functionality in:

### Desktop Browsers

- [ ] Chrome (latest 2 versions)
- [ ] Firefox (latest 2 versions)
- [ ] Safari (latest 2 versions)
- [ ] Edge (latest 2 versions)

### Mobile Browsers

- [ ] iOS Safari
- [ ] Chrome Android
- [ ] Samsung Internet

### Browser-Specific Checks

- [ ] Animations work correctly
- [ ] CSS custom properties applied
- [ ] Cookie read/write works
- [ ] LocalStorage fallback works (if implemented)

---

## Script Blocking

### Type Swap Method

- [ ] Scripts with `type="text/plain"` do not execute
- [ ] Scripts activate after consent given
- [ ] `data-cookie-category` attribute added correctly

### Data Attribute Method

- [ ] `data-cookie-consent="pending"` added
- [ ] Scripts can check attribute before executing

### Inline Script Blocking

- [ ] Matching inline scripts blocked
- [ ] Pattern matching works correctly
- [ ] Already-blocked scripts not double-blocked

---

## Admin Panel

### Settings Tab

- [ ] Enable/disable toggle works
- [ ] Position selector works
- [ ] Policy page selector populates with pages
- [ ] Consent version field works
- [ ] Save button saves settings
- [ ] Success notification appears

### Appearance Tab

- [ ] Theme selector (dark/light/custom) works
- [ ] Color pickers work
- [ ] Border radius inputs work
- [ ] Live preview updates

### Content Tab

- [ ] All text fields editable
- [ ] Changes reflect in banner preview
- [ ] HTML allowed where appropriate

### Categories Tab

- [ ] Add new category works
- [ ] Edit category works
- [ ] Delete category works (with confirmation)
- [ ] Essential flag toggle works
- [ ] Essential category cannot be deleted

### Cookies Tab

- [ ] Add cookie definition works
- [ ] Edit cookie works
- [ ] Delete cookie works
- [ ] Category assignment works
- [ ] All fields save correctly

### Scripts Tab

- [ ] Add script rule works
- [ ] Handle and pattern fields work
- [ ] Category assignment works
- [ ] Blocking method selector works
- [ ] Delete script rule works

### Tools Tab

- [ ] Export downloads JSON file
- [ ] Import accepts valid JSON
- [ ] Import rejects invalid data
- [ ] Reset to defaults works (with confirmation)
- [ ] Policy page wizard works

---

## Shortcode & Block

### Shortcode `[consent_raven_policy_table]`

- [ ] Renders table with cookies
- [ ] `show_category` attribute works
- [ ] `show_provider` attribute works
- [ ] `show_expiration` attribute works
- [ ] `show_host` attribute works
- [ ] `category` filter attribute works
- [ ] Empty state shows when no cookies

### Gutenberg Block

- [ ] Block appears in inserter
- [ ] Block renders in editor
- [ ] Block settings in sidebar work
- [ ] Block preview updates
- [ ] Block renders correctly on frontend

---

## Cache Compatibility

Test with popular caching plugins:

- [ ] WP Super Cache
- [ ] W3 Total Cache
- [ ] WP Rocket
- [ ] LiteSpeed Cache

### Caching Checks

- [ ] Banner shows correctly on cached pages
- [ ] Consent state not affected by cache
- [ ] Settings changes reflect after cache clear

---

## Performance

### Loading

- [ ] Scripts load with `defer` attribute
- [ ] No render-blocking resources
- [ ] Reasonable file sizes (< 50KB combined)

### Runtime

- [ ] No console errors
- [ ] Smooth animations (60fps)
- [ ] No memory leaks (extended session)

---

## Security

### XSS Prevention

- [ ] Script tags escaped in content fields
- [ ] HTML entities properly escaped
- [ ] User input sanitized

### CSRF Protection

- [ ] REST API requires nonce
- [ ] Settings cannot be modified without auth

### Permissions

- [ ] Only administrators can access settings
- [ ] REST API checks `manage_options` capability

---

## Internationalization

- [ ] All user-facing strings translatable
- [ ] RTL languages display correctly
- [ ] Date/number formats localized
- [ ] Translation files load correctly

---

## Test Sign-off

| Tester | Date | Browser | Result |
|--------|------|---------|--------|
|        |      |         |        |
|        |      |         |        |
|        |      |         |        |

---

## Notes

<!-- Add any test notes or issues found here -->
