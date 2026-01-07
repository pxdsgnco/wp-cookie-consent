/**
 * Frontend JavaScript Tests
 *
 * @package Consent_Raven
 */

import './setup';

// Load the frontend script after setup
require('../../assets/js/frontend.js');

describe('ConsentRaven', () => {
	beforeEach(() => {
		// Clear cookies before each test
		global.clearCookies();

		// Reset DOM state
		document.getElementById('consent-raven-banner').style.display = 'none';
		document.getElementById('consent-raven-preferences').style.display = 'none';
		document.getElementById('consent-raven-settings-button').style.display = 'none';

		// Reset ConsentRaven state
		if (window.ConsentRaven) {
			window.ConsentRaven.state.consent = null;
			window.ConsentRaven.state.isInitialized = false;
		}
	});

	describe('Initialization', () => {
		test('ConsentRaven is defined on window', () => {
			expect(window.ConsentRaven).toBeDefined();
		});

		test('ConsentRaven has required methods', () => {
			expect(typeof window.ConsentRaven.init).toBe('function');
			expect(typeof window.ConsentRaven.acceptAll).toBe('function');
			expect(typeof window.ConsentRaven.rejectAll).toBe('function');
			expect(typeof window.ConsentRaven.savePreferences).toBe('function');
			expect(typeof window.ConsentRaven.getConsent).toBe('function');
			expect(typeof window.ConsentRaven.hasConsent).toBe('function');
		});

		test('ConsentRaven has configuration', () => {
			expect(window.ConsentRaven.config.cookieName).toBe('consent_raven');
			expect(window.ConsentRaven.config.cookieExpiry).toBe(365);
			expect(window.ConsentRaven.config.consentVersion).toBe('1.0');
		});
	});

	describe('Cookie Management', () => {
		test('getCookie returns null for non-existent cookie', () => {
			const value = window.ConsentRaven.getCookie('nonexistent');
			expect(value).toBeNull();
		});

		test('setCookie and getCookie work together', () => {
			window.ConsentRaven.setCookie('test_cookie', 'test_value', 1);
			const value = window.ConsentRaven.getCookie('test_cookie');
			expect(value).toBe('test_value');
		});

		test('deleteCookie removes cookie', () => {
			window.ConsentRaven.setCookie('to_delete', 'value', 1);
			window.ConsentRaven.deleteCookie('to_delete');
			const value = window.ConsentRaven.getCookie('to_delete');
			expect(value).toBeNull();
		});

		test('setCookie encodes values properly', () => {
			window.ConsentRaven.setCookie('encoded', '{"key": "value"}', 1);
			const value = window.ConsentRaven.getCookie('encoded');
			expect(value).toBe('{"key": "value"}');
		});
	});

	describe('Consent State', () => {
		test('getConsent returns null when no consent', () => {
			expect(window.ConsentRaven.getConsent()).toBeNull();
		});

		test('hasConsent returns false when no consent', () => {
			expect(window.ConsentRaven.hasConsent('analytics')).toBe(false);
		});

		test('setConsent saves consent to cookie', () => {
			const categories = { essential: true, analytics: true };
			window.ConsentRaven.setConsent(categories);

			const consent = window.ConsentRaven.getConsent();
			expect(consent).not.toBeNull();
			expect(consent.categories).toEqual(categories);
			expect(consent.version).toBe('1.0');
		});

		test('hasConsent returns true for enabled category', () => {
			window.ConsentRaven.setConsent({ essential: true, analytics: true });
			expect(window.ConsentRaven.hasConsent('analytics')).toBe(true);
		});

		test('hasConsent returns false for disabled category', () => {
			window.ConsentRaven.setConsent({ essential: true, analytics: false });
			expect(window.ConsentRaven.hasConsent('analytics')).toBe(false);
		});
	});

	describe('Category Management', () => {
		test('getAllCategories returns all categories enabled', () => {
			const categories = window.ConsentRaven.getAllCategories();
			expect(categories.essential).toBe(true);
			expect(categories.analytics).toBe(true);
			expect(categories.marketing).toBe(true);
		});

		test('getEssentialCategories returns only essential enabled', () => {
			const categories = window.ConsentRaven.getEssentialCategories();
			expect(categories.essential).toBe(true);
			expect(categories.analytics).toBe(false);
			expect(categories.marketing).toBe(false);
		});
	});

	describe('Accept/Reject Actions', () => {
		test('acceptAll enables all categories', () => {
			window.ConsentRaven.acceptAll();

			const consent = window.ConsentRaven.getConsent();
			expect(consent.categories.essential).toBe(true);
			expect(consent.categories.analytics).toBe(true);
			expect(consent.categories.marketing).toBe(true);
		});

		test('rejectAll enables only essential categories', () => {
			window.ConsentRaven.rejectAll();

			const consent = window.ConsentRaven.getConsent();
			expect(consent.categories.essential).toBe(true);
			expect(consent.categories.analytics).toBe(false);
			expect(consent.categories.marketing).toBe(false);
		});
	});

	describe('UI State', () => {
		test('showBanner displays banner element', () => {
			window.ConsentRaven.showBanner();
			const banner = document.getElementById('consent-raven-banner');
			expect(banner.style.display).not.toBe('none');
		});

		test('hideBanner hides banner element', () => {
			window.ConsentRaven.showBanner();
			window.ConsentRaven.hideBanner();
			const banner = document.getElementById('consent-raven-banner');
			expect(banner.style.display).toBe('none');
		});

		test('showPreferences displays preferences modal', () => {
			window.ConsentRaven.showPreferences();
			const prefs = document.getElementById('consent-raven-preferences');
			expect(prefs.style.display).not.toBe('none');
		});

		test('hidePreferences hides preferences modal', () => {
			window.ConsentRaven.showPreferences();
			window.ConsentRaven.hidePreferences();
			const prefs = document.getElementById('consent-raven-preferences');
			expect(prefs.style.display).toBe('none');
		});

		test('showSettingsButton displays settings button', () => {
			window.ConsentRaven.showSettingsButton();
			const button = document.getElementById('consent-raven-settings-button');
			expect(button.style.display).not.toBe('none');
		});

		test('hideSettingsButton hides settings button', () => {
			window.ConsentRaven.showSettingsButton();
			window.ConsentRaven.hideSettingsButton();
			const button = document.getElementById('consent-raven-settings-button');
			expect(button.style.display).toBe('none');
		});
	});

	describe('ARIA State Management', () => {
		test('showPreferences sets aria-expanded to true', () => {
			window.ConsentRaven.showPreferences();
			const customizeBtn = document.querySelector('[data-action="customize"]');
			expect(customizeBtn.getAttribute('aria-expanded')).toBe('true');
		});

		test('hidePreferences sets aria-expanded to false', () => {
			window.ConsentRaven.showPreferences();
			window.ConsentRaven.hidePreferences();
			const customizeBtn = document.querySelector('[data-action="customize"]');
			expect(customizeBtn.getAttribute('aria-expanded')).toBe('false');
		});
	});

	describe('Consent Version', () => {
		test('loadConsent invalidates old consent version', () => {
			// Set consent with old version
			const oldConsent = {
				version: '0.9',
				timestamp: Date.now(),
				categories: { essential: true, analytics: true },
			};
			window.ConsentRaven.setCookie('consent_raven', JSON.stringify(oldConsent), 365);

			// Reload consent
			window.ConsentRaven.loadConsent();

			// Should be null due to version mismatch
			expect(window.ConsentRaven.state.consent).toBeNull();
		});

		test('loadConsent accepts matching consent version', () => {
			// Set consent with matching version
			const consent = {
				version: '1.0',
				timestamp: Date.now(),
				categories: { essential: true, analytics: true },
			};
			window.ConsentRaven.setCookie('consent_raven', JSON.stringify(consent), 365);

			// Reload consent
			window.ConsentRaven.loadConsent();

			// Should have consent
			expect(window.ConsentRaven.state.consent).not.toBeNull();
			expect(window.ConsentRaven.state.consent.categories.analytics).toBe(true);
		});
	});

	describe('Reset Consent', () => {
		test('resetConsent clears consent and cookie', () => {
			window.ConsentRaven.setConsent({ essential: true, analytics: true });
			expect(window.ConsentRaven.getConsent()).not.toBeNull();

			window.ConsentRaven.resetConsent();

			expect(window.ConsentRaven.state.consent).toBeNull();
			expect(window.ConsentRaven.getCookie('consent_raven')).toBeNull();
		});
	});

	describe('Screen Reader Announcements', () => {
		test('announce sets announcer text', (done) => {
			window.ConsentRaven.announce('Test announcement');

			// Wait for the setTimeout in announce()
			setTimeout(() => {
				const announcer = document.getElementById('consent-raven-announcer');
				expect(announcer.textContent).toBe('Test announcement');
				done();
			}, 150);
		});

		test('getAnnouncement returns correct messages', () => {
			expect(window.ConsentRaven.getAnnouncement('accept')).toBe('All cookies accepted.');
			expect(window.ConsentRaven.getAnnouncement('reject')).toBe('Non-essential cookies rejected.');
			expect(window.ConsentRaven.getAnnouncement('save')).toBe('Preferences saved.');
		});
	});

	describe('Custom Events', () => {
		test('dispatchEvent fires custom events', () => {
			const handler = jest.fn();
			document.addEventListener('consent_raven_test', handler);

			window.ConsentRaven.dispatchEvent('consent_raven_test', { foo: 'bar' });

			expect(handler).toHaveBeenCalled();
			expect(handler.mock.calls[0][0].detail).toEqual({ foo: 'bar' });
		});

		test('acceptAll fires consent_raven_accept_all event', () => {
			const handler = jest.fn();
			document.addEventListener('consent_raven_accept_all', handler);

			window.ConsentRaven.acceptAll();

			expect(handler).toHaveBeenCalled();
		});

		test('rejectAll fires consent_raven_reject_all event', () => {
			const handler = jest.fn();
			document.addEventListener('consent_raven_reject_all', handler);

			window.ConsentRaven.rejectAll();

			expect(handler).toHaveBeenCalled();
		});

		test('setConsent fires consent_raven_consent_updated event', () => {
			const handler = jest.fn();
			document.addEventListener('consent_raven_consent_updated', handler);

			window.ConsentRaven.setConsent({ essential: true });

			expect(handler).toHaveBeenCalled();
		});
	});

	describe('Action Handling', () => {
		test('handleAction accept calls acceptAll', () => {
			const spy = jest.spyOn(window.ConsentRaven, 'acceptAll');
			window.ConsentRaven.handleAction('accept');
			expect(spy).toHaveBeenCalled();
			spy.mockRestore();
		});

		test('handleAction reject calls rejectAll', () => {
			const spy = jest.spyOn(window.ConsentRaven, 'rejectAll');
			window.ConsentRaven.handleAction('reject');
			expect(spy).toHaveBeenCalled();
			spy.mockRestore();
		});

		test('handleAction customize calls showPreferences', () => {
			const spy = jest.spyOn(window.ConsentRaven, 'showPreferences');
			window.ConsentRaven.handleAction('customize');
			expect(spy).toHaveBeenCalled();
			spy.mockRestore();
		});

		test('handleAction save-preferences calls savePreferences', () => {
			const spy = jest.spyOn(window.ConsentRaven, 'savePreferences');
			window.ConsentRaven.handleAction('save-preferences');
			expect(spy).toHaveBeenCalled();
			spy.mockRestore();
		});

		test('handleAction close-preferences calls hidePreferences', () => {
			const spy = jest.spyOn(window.ConsentRaven, 'hidePreferences');
			window.ConsentRaven.handleAction('close-preferences');
			expect(spy).toHaveBeenCalled();
			spy.mockRestore();
		});
	});
});
