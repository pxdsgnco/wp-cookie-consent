<?php
/**
 * Consent Raven
 *
 * A lightweight, customizable WordPress plugin that displays a cookie consent
 * dialog and manages cookie preferences in line with GDPR, UK GDPR, NDPR,
 * and similar privacy frameworks.
 *
 * @link              https://github.com/pxdsgnco/wp-cookie-consent
 * @since             1.0.0
 * @package           Consent_Raven
 *
 * @wordpress-plugin
 * Plugin Name:       Consent Raven
 * Plugin URI:        https://github.com/pxdsgnco/wp-cookie-consent
 * Description:       A lightweight cookie consent plugin with customizable banners, granular category control, and GDPR/NDPR compliance features.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Consent Raven
 * Author URI:        https://github.com/pxdsgnco
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       consent-raven
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'CONSENT_RAVEN_VERSION', '1.0.0' );

/**
 * Plugin base path.
 */
define( 'CONSENT_RAVEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin base URL.
 */
define( 'CONSENT_RAVEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'CONSENT_RAVEN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cr-activator.php
 */
function consent_raven_activate() {
	require_once CONSENT_RAVEN_PLUGIN_DIR . 'includes/class-cr-activator.php';
	CR_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cr-deactivator.php
 */
function consent_raven_deactivate() {
	require_once CONSENT_RAVEN_PLUGIN_DIR . 'includes/class-cr-deactivator.php';
	CR_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'consent_raven_activate' );
register_deactivation_hook( __FILE__, 'consent_raven_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require CONSENT_RAVEN_PLUGIN_DIR . 'includes/class-cr-loader.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'includes/class-cr-i18n.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'includes/class-cr-consent.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'admin/class-cr-admin.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'admin/class-cr-settings.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'admin/class-cr-rest-api.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'public/class-cr-public.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'public/class-cr-banner.php';
require CONSENT_RAVEN_PLUGIN_DIR . 'public/class-cr-script-blocker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function consent_raven_run() {
	$plugin = new CR_Loader();
	$plugin->run();
}

consent_raven_run();
