/**
 * Consent Raven - Frontend JavaScript
 *
 * Vanilla JavaScript implementation for cookie consent management.
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

(function() {
	'use strict';

	// Exit if already initialized or data not available
	if (window.ConsentRaven || typeof consentRaven === 'undefined') {
		return;
	}

	/**
	 * Consent Raven main object
	 */
	var ConsentRaven = {
		// Configuration
		config: {
			cookieName: consentRaven.cookieName || 'consent_raven',
			cookieExpiry: consentRaven.cookieExpiry || 365,
			consentVersion: consentRaven.settings.consentVersion || '1.0'
		},

		// State
		state: {
			consent: null,
			isInitialized: false
		},

		// DOM Elements
		elements: {
			banner: null,
			preferences: null,
			settingsButton: null
		},

		/**
		 * Initialize the consent manager
		 */
		init: function() {
			if (this.state.isInitialized) {
				return;
			}

			this.cacheElements();
			this.loadConsent();
			this.bindEvents();
			this.checkConsent();

			this.state.isInitialized = true;

			// Dispatch init event
			this.dispatchEvent('consent_raven_init');
		},

		/**
		 * Cache DOM elements
		 */
		cacheElements: function() {
			this.elements.banner = document.getElementById('consent-raven-banner');
			this.elements.preferences = document.getElementById('consent-raven-preferences');
			this.elements.settingsButton = document.getElementById('consent-raven-settings-button');
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function() {
			var self = this;

			// Banner buttons
			if (this.elements.banner) {
				this.elements.banner.addEventListener('click', function(e) {
					var action = e.target.getAttribute('data-action');
					if (action) {
						self.handleAction(action);
					}
				});
			}

			// Preferences modal
			if (this.elements.preferences) {
				this.elements.preferences.addEventListener('click', function(e) {
					var action = e.target.getAttribute('data-action');
					if (action) {
						self.handleAction(action);
					}

					// Close on overlay click
					if (e.target.classList.contains('cr-overlay')) {
						self.hidePreferences();
					}
				});
			}

			// Settings button
			if (this.elements.settingsButton) {
				this.elements.settingsButton.addEventListener('click', function() {
					self.showPreferences();
				});
			}

			// Keyboard handling
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape') {
					if (self.elements.preferences && self.elements.preferences.style.display !== 'none') {
						self.hidePreferences();
					}
				}
			});
		},

		/**
		 * Handle button actions
		 */
		handleAction: function(action) {
			switch (action) {
				case 'accept':
					this.acceptAll();
					break;
				case 'reject':
					this.rejectAll();
					break;
				case 'customize':
					this.showPreferences();
					break;
				case 'save-preferences':
					this.savePreferences();
					break;
				case 'close-preferences':
					this.hidePreferences();
					break;
			}
		},

		/**
		 * Load consent from cookie
		 */
		loadConsent: function() {
			var cookie = this.getCookie(this.config.cookieName);

			if (cookie) {
				try {
					this.state.consent = JSON.parse(cookie);

					// Check if consent version matches
					if (this.state.consent.version !== this.config.consentVersion) {
						this.state.consent = null;
					}
				} catch (e) {
					this.state.consent = null;
				}
			}
		},

		/**
		 * Check consent state and show/hide banner
		 */
		checkConsent: function() {
			if (this.state.consent) {
				// Has valid consent - hide banner, show settings button
				this.hideBanner();
				this.showSettingsButton();
				this.applyConsent();
			} else {
				// No consent - show banner
				this.showBanner();
				this.hideSettingsButton();
			}
		},

		/**
		 * Accept all cookies
		 */
		acceptAll: function() {
			var categories = this.getAllCategories();
			this.setConsent(categories);
			this.hideBanner();
			this.showSettingsButton();
			this.applyConsent();

			this.dispatchEvent('consent_raven_accept_all', { categories: categories });
		},

		/**
		 * Reject all non-essential cookies
		 */
		rejectAll: function() {
			var categories = this.getEssentialCategories();
			this.setConsent(categories);
			this.hideBanner();
			this.showSettingsButton();
			this.applyConsent();

			this.dispatchEvent('consent_raven_reject_all', { categories: categories });
		},

		/**
		 * Save preferences from modal
		 */
		savePreferences: function() {
			var categories = this.getSelectedCategories();
			this.setConsent(categories);
			this.hidePreferences();
			this.hideBanner();
			this.showSettingsButton();
			this.applyConsent();

			this.dispatchEvent('consent_raven_save_preferences', { categories: categories });
		},

		/**
		 * Set consent and save to cookie
		 */
		setConsent: function(categories) {
			this.state.consent = {
				version: this.config.consentVersion,
				timestamp: Math.floor(Date.now() / 1000),
				categories: categories
			};

			this.setCookie(
				this.config.cookieName,
				JSON.stringify(this.state.consent),
				this.config.cookieExpiry
			);

			this.dispatchEvent('consent_raven_consent_updated', {
				categories: categories,
				version: this.config.consentVersion
			});
		},

		/**
		 * Apply consent settings
		 */
		applyConsent: function() {
			if (!this.state.consent) {
				return;
			}

			var enabledCategories = [];
			var categories = this.state.consent.categories;

			for (var category in categories) {
				if (categories[category]) {
					enabledCategories.push(category);
				}
			}

			// Activate blocked scripts for enabled categories
			if (typeof window.consentRavenActivateScripts === 'function') {
				window.consentRavenActivateScripts(enabledCategories);
			}

			// Fire category enabled events
			enabledCategories.forEach(function(category) {
				this.dispatchEvent('consent_raven_category_enabled', { category: category });
			}, this);
		},

		/**
		 * Get all category slugs
		 */
		getAllCategories: function() {
			var categories = {};
			consentRaven.categories.forEach(function(cat) {
				categories[cat.slug] = true;
			});
			return categories;
		},

		/**
		 * Get essential category slugs only
		 */
		getEssentialCategories: function() {
			var categories = {};
			consentRaven.categories.forEach(function(cat) {
				categories[cat.slug] = cat.essential;
			});
			return categories;
		},

		/**
		 * Get selected categories from preferences modal
		 */
		getSelectedCategories: function() {
			var categories = {};
			var checkboxes = this.elements.preferences.querySelectorAll('.cr-toggle__input');

			consentRaven.categories.forEach(function(cat) {
				if (cat.essential) {
					categories[cat.slug] = true;
				} else {
					var checkbox = document.querySelector('[data-category="' + cat.slug + '"].cr-toggle__input');
					categories[cat.slug] = checkbox ? checkbox.checked : false;
				}
			});

			return categories;
		},

		/**
		 * Update preferences modal checkboxes
		 */
		updatePreferencesUI: function() {
			var consent = this.state.consent;

			consentRaven.categories.forEach(function(cat) {
				if (!cat.essential) {
					var checkbox = document.querySelector('.cr-toggle__input[data-category="' + cat.slug + '"]');
					if (checkbox && consent && consent.categories) {
						checkbox.checked = consent.categories[cat.slug] || false;
					}
				}
			});
		},

		/**
		 * Show banner
		 */
		showBanner: function() {
			if (this.elements.banner) {
				this.elements.banner.style.display = '';
				this.elements.banner.setAttribute('data-animating', 'in');
				this.trapFocus(this.elements.banner);

				setTimeout(function() {
					this.elements.banner.removeAttribute('data-animating');
				}.bind(this), 300);
			}
		},

		/**
		 * Hide banner
		 */
		hideBanner: function() {
			if (this.elements.banner) {
				this.elements.banner.style.display = 'none';
				this.releaseFocus();
			}
		},

		/**
		 * Show preferences modal
		 */
		showPreferences: function() {
			this.updatePreferencesUI();

			if (this.elements.preferences) {
				this.elements.preferences.style.display = '';
				this.elements.preferences.setAttribute('data-animating', 'in');
				this.trapFocus(this.elements.preferences);

				// Focus first interactive element
				var firstInput = this.elements.preferences.querySelector('.cr-toggle__input, .cr-button');
				if (firstInput) {
					firstInput.focus();
				}

				setTimeout(function() {
					this.elements.preferences.removeAttribute('data-animating');
				}.bind(this), 300);
			}
		},

		/**
		 * Hide preferences modal
		 */
		hidePreferences: function() {
			if (this.elements.preferences) {
				this.elements.preferences.style.display = 'none';
				this.releaseFocus();

				// Return focus to settings button if visible
				if (this.elements.settingsButton && this.elements.settingsButton.style.display !== 'none') {
					this.elements.settingsButton.focus();
				}
			}
		},

		/**
		 * Show settings button
		 */
		showSettingsButton: function() {
			if (this.elements.settingsButton) {
				this.elements.settingsButton.style.display = '';
			}
		},

		/**
		 * Hide settings button
		 */
		hideSettingsButton: function() {
			if (this.elements.settingsButton) {
				this.elements.settingsButton.style.display = 'none';
			}
		},

		/**
		 * Trap focus within an element (accessibility)
		 */
		trapFocus: function(element) {
			var focusableElements = element.querySelectorAll(
				'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
			);

			if (focusableElements.length === 0) {
				return;
			}

			var firstElement = focusableElements[0];
			var lastElement = focusableElements[focusableElements.length - 1];

			this._focusTrapHandler = function(e) {
				if (e.key !== 'Tab') {
					return;
				}

				if (e.shiftKey) {
					if (document.activeElement === firstElement) {
						lastElement.focus();
						e.preventDefault();
					}
				} else {
					if (document.activeElement === lastElement) {
						firstElement.focus();
						e.preventDefault();
					}
				}
			};

			element.addEventListener('keydown', this._focusTrapHandler);
			firstElement.focus();
		},

		/**
		 * Release focus trap
		 */
		releaseFocus: function() {
			if (this._focusTrapHandler) {
				if (this.elements.banner) {
					this.elements.banner.removeEventListener('keydown', this._focusTrapHandler);
				}
				if (this.elements.preferences) {
					this.elements.preferences.removeEventListener('keydown', this._focusTrapHandler);
				}
				this._focusTrapHandler = null;
			}
		},

		/**
		 * Get cookie value
		 */
		getCookie: function(name) {
			var nameEQ = name + '=';
			var ca = document.cookie.split(';');

			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) === ' ') {
					c = c.substring(1, c.length);
				}
				if (c.indexOf(nameEQ) === 0) {
					return decodeURIComponent(c.substring(nameEQ.length, c.length));
				}
			}

			return null;
		},

		/**
		 * Set cookie
		 */
		setCookie: function(name, value, days) {
			var expires = '';

			if (days) {
				var date = new Date();
				date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
				expires = '; expires=' + date.toUTCString();
			}

			document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
		},

		/**
		 * Delete cookie
		 */
		deleteCookie: function(name) {
			document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
		},

		/**
		 * Dispatch custom event
		 */
		dispatchEvent: function(name, detail) {
			var event;

			if (typeof CustomEvent === 'function') {
				event = new CustomEvent(name, { detail: detail || {} });
			} else {
				event = document.createEvent('CustomEvent');
				event.initCustomEvent(name, true, true, detail || {});
			}

			document.dispatchEvent(event);
		},

		/**
		 * Check if a category is enabled
		 */
		hasConsent: function(category) {
			if (!this.state.consent || !this.state.consent.categories) {
				return false;
			}

			return this.state.consent.categories[category] === true;
		},

		/**
		 * Get current consent state
		 */
		getConsent: function() {
			return this.state.consent;
		},

		/**
		 * Reset consent (for testing/debugging)
		 */
		resetConsent: function() {
			this.deleteCookie(this.config.cookieName);
			this.state.consent = null;
			this.checkConsent();
		}
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			ConsentRaven.init();
		});
	} else {
		ConsentRaven.init();
	}

	// Expose to global scope
	window.ConsentRaven = ConsentRaven;

})();
