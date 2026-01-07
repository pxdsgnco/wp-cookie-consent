<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best if the expected output is fixed.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'consent_raven_settings' );
delete_option( 'consent_raven_categories' );
delete_option( 'consent_raven_cookies' );
delete_option( 'consent_raven_scripts' );
delete_option( 'consent_raven_version' );

// Delete any transients.
delete_transient( 'consent_raven_cache' );

// Clear any scheduled hooks.
wp_clear_scheduled_hook( 'consent_raven_cleanup' );
