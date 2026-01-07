/**
 * Jest Setup File
 *
 * @package Consent_Raven
 */

// Mock DOM elements
document.body.innerHTML = `
	<div id="consent-raven-banner" style="display: none;"></div>
	<div id="consent-raven-preferences" style="display: none;">
		<input type="checkbox" class="cr-toggle__input" data-category="analytics">
		<input type="checkbox" class="cr-toggle__input" data-category="marketing">
		<button data-action="save-preferences">Save</button>
	</div>
	<button id="consent-raven-settings-button" style="display: none;"></button>
	<div id="consent-raven-announcer"></div>
	<button data-action="customize" aria-expanded="false"></button>
`;

// Mock consentRaven global object
global.consentRaven = {
	settings: {
		position: 'bottom-right',
		consentVersion: '1.0',
		policyPageUrl: '/cookie-policy',
	},
	categories: [
		{
			id: 'essential',
			slug: 'essential',
			name: 'Essential',
			description: 'Required cookies',
			essential: true,
		},
		{
			id: 'analytics',
			slug: 'analytics',
			name: 'Analytics',
			description: 'Analytics cookies',
			essential: false,
		},
		{
			id: 'marketing',
			slug: 'marketing',
			name: 'Marketing',
			description: 'Marketing cookies',
			essential: false,
		},
	],
	content: {
		title: 'Cookie Settings',
		description: 'We use cookies...',
		accept_button: 'Accept All',
		reject_button: 'Reject All',
		customize_button: 'Customize',
		save_button: 'Save',
	},
	cookieName: 'consent_raven',
	cookieExpiry: 365,
	i18n: {
		acceptedAll: 'All cookies accepted.',
		rejectedAll: 'Non-essential cookies rejected.',
		preferencesSaved: 'Preferences saved.',
	},
};

// Mock document.cookie
let cookieStore = {};

Object.defineProperty(document, 'cookie', {
	get: function () {
		return Object.entries(cookieStore)
			.map(([key, value]) => `${key}=${value}`)
			.join('; ');
	},
	set: function (value) {
		const [nameValue] = value.split(';');
		const [name, val] = nameValue.split('=');
		if (val === '' || value.includes('expires=Thu, 01 Jan 1970')) {
			delete cookieStore[name];
		} else {
			cookieStore[name] = val;
		}
	},
});

// Helper to clear cookies between tests
global.clearCookies = function () {
	cookieStore = {};
};

// Mock CustomEvent for older browsers
if (typeof CustomEvent !== 'function') {
	global.CustomEvent = function (event, params) {
		params = params || { bubbles: false, cancelable: false, detail: null };
		const evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
		return evt;
	};
}
