<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Consent_Raven
 */

// Define test constants.
define( 'CONSENT_RAVEN_TESTING', true );

// Load Composer autoloader.
$autoloader = dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

// Try to get WordPress test lib path from environment or use default.
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $wp_tests_dir ) {
	$wp_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Check if WordPress test library exists.
if ( ! file_exists( $wp_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find WordPress test library at: {$wp_tests_dir}\n";
	echo "Please set the WP_TESTS_DIR environment variable.\n";
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $wp_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __DIR__ ) ) . '/consent-raven.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $wp_tests_dir . '/includes/bootstrap.php';
