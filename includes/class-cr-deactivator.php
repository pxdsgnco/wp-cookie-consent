<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */
class CR_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Cleans up temporary data but preserves settings.
	 * Full cleanup happens on uninstall.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Clear any transients.
		delete_transient( 'consent_raven_cache' );

		// Clear any scheduled hooks.
		wp_clear_scheduled_hook( 'consent_raven_cleanup' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
