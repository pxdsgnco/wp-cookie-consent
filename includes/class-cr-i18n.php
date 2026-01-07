<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */

/**
 * Define the internationalization functionality.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */
class CR_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'consent-raven',
			false,
			dirname( CONSENT_RAVEN_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
