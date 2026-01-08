<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */

/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */
class CR_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Sets up default options and initializes plugin data.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		self::set_default_options();
		self::set_default_categories();
		self::set_default_cookies();

		// Create consent logs table.
		CR_Consent_Log::create_table();

		// Schedule daily cleanup cron job for consent logs.
		if ( ! wp_next_scheduled( 'consent_raven_log_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'consent_raven_log_cleanup' );
		}

		// Store the plugin version.
		update_option( 'consent_raven_version', CONSENT_RAVEN_VERSION );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin options.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private static function set_default_options() {
		$default_settings = array(
			'enabled'         => true,
			'position'        => 'bottom-right',
			'policy_page_id'  => 0,
			'consent_version' => '1.0',
			'appearance'      => array(
				'theme'            => 'dark',
				'background_color' => '#1a1a1a',
				'text_color'       => '#ffffff',
				'secondary_color'  => '#b3b3b3',
				'button_bg'        => '#ffffff',
				'button_text'      => '#1a1a1a',
				'button_radius'    => '8px',
				'dialog_radius'    => '16px',
			),
			'content'         => array(
				'title'            => __( 'Cookie settings', 'consent-raven' ),
				'description'      => __( 'We use cookies to deliver and improve our services, analyze site usage, and if you agree, to customize or personalize your experience and market our services to you. You can read our Cookie Policy here.', 'consent-raven' ),
				'accept_button'    => __( 'Accept All Cookies', 'consent-raven' ),
				'reject_button'    => __( 'Reject All Cookies', 'consent-raven' ),
				'customize_button' => __( 'Customize Cookie Settings', 'consent-raven' ),
				'save_button'      => __( 'Save Preferences', 'consent-raven' ),
				'policy_link_text' => __( 'here', 'consent-raven' ),
			),
		);

		// Only add default if option doesn't exist.
		if ( false === get_option( 'consent_raven_settings' ) ) {
			add_option( 'consent_raven_settings', $default_settings );
		}
	}

	/**
	 * Set default cookie categories.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private static function set_default_categories() {
		$default_categories = array(
			array(
				'id'          => 'essential',
				'slug'        => 'essential',
				'name'        => __( 'Essential', 'consent-raven' ),
				'description' => __( 'Necessary cookies for the website to function properly. These cannot be disabled.', 'consent-raven' ),
				'essential'   => true,
			),
			array(
				'id'          => 'analytics',
				'slug'        => 'analytics',
				'name'        => __( 'Analytics', 'consent-raven' ),
				'description' => __( 'Cookies that help us understand how visitors interact with our website.', 'consent-raven' ),
				'essential'   => false,
			),
			array(
				'id'          => 'marketing',
				'slug'        => 'marketing',
				'name'        => __( 'Marketing', 'consent-raven' ),
				'description' => __( 'Cookies used to deliver personalized advertisements and track campaign performance.', 'consent-raven' ),
				'essential'   => false,
			),
		);

		// Only add default if option doesn't exist.
		if ( false === get_option( 'consent_raven_categories' ) ) {
			add_option( 'consent_raven_categories', $default_categories );
		}
	}

	/**
	 * Set default cookie definitions.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private static function set_default_cookies() {
		$default_cookies = array(
			// Essential cookies.
			array(
				'name'        => 'wordpress_logged_in_*',
				'category_id' => 'essential',
				'provider'    => 'WordPress',
				'purpose'     => __( 'User authentication', 'consent-raven' ),
				'expiration'  => __( 'Session', 'consent-raven' ),
				'host'        => '',
			),
			array(
				'name'        => 'wordpress_sec_*',
				'category_id' => 'essential',
				'provider'    => 'WordPress',
				'purpose'     => __( 'Secure authentication', 'consent-raven' ),
				'expiration'  => __( 'Session', 'consent-raven' ),
				'host'        => '',
			),
			array(
				'name'        => 'wp-settings-*',
				'category_id' => 'essential',
				'provider'    => 'WordPress',
				'purpose'     => __( 'User preferences', 'consent-raven' ),
				'expiration'  => __( '1 year', 'consent-raven' ),
				'host'        => '',
			),
			array(
				'name'        => 'consent_raven',
				'category_id' => 'essential',
				'provider'    => 'Consent Raven',
				'purpose'     => __( 'Stores cookie consent preferences', 'consent-raven' ),
				'expiration'  => __( '1 year', 'consent-raven' ),
				'host'        => '',
			),
			// Analytics cookies.
			array(
				'name'        => '_ga',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics',
				'purpose'     => __( 'Distinguishes unique users', 'consent-raven' ),
				'expiration'  => __( '2 years', 'consent-raven' ),
				'host'        => '.google.com',
			),
			array(
				'name'        => '_ga_*',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics 4',
				'purpose'     => __( 'Maintains session state', 'consent-raven' ),
				'expiration'  => __( '2 years', 'consent-raven' ),
				'host'        => '.google.com',
			),
			array(
				'name'        => '_gid',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics',
				'purpose'     => __( 'Distinguishes unique users', 'consent-raven' ),
				'expiration'  => __( '24 hours', 'consent-raven' ),
				'host'        => '.google.com',
			),
			array(
				'name'        => '_gat',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics',
				'purpose'     => __( 'Throttle request rate', 'consent-raven' ),
				'expiration'  => __( '1 minute', 'consent-raven' ),
				'host'        => '.google.com',
			),
			// Marketing cookies.
			array(
				'name'        => '_fbp',
				'category_id' => 'marketing',
				'provider'    => 'Facebook Pixel',
				'purpose'     => __( 'Tracks visits across websites', 'consent-raven' ),
				'expiration'  => __( '3 months', 'consent-raven' ),
				'host'        => '.facebook.com',
			),
			array(
				'name'        => '_fbc',
				'category_id' => 'marketing',
				'provider'    => 'Facebook Pixel',
				'purpose'     => __( 'Stores last visit', 'consent-raven' ),
				'expiration'  => __( '3 months', 'consent-raven' ),
				'host'        => '.facebook.com',
			),
			array(
				'name'        => 'fr',
				'category_id' => 'marketing',
				'provider'    => 'Facebook',
				'purpose'     => __( 'Ad delivery and measurement', 'consent-raven' ),
				'expiration'  => __( '3 months', 'consent-raven' ),
				'host'        => '.facebook.com',
			),
			array(
				'name'        => 'IDE',
				'category_id' => 'marketing',
				'provider'    => 'Google DoubleClick',
				'purpose'     => __( 'Ad targeting', 'consent-raven' ),
				'expiration'  => __( '1 year', 'consent-raven' ),
				'host'        => '.doubleclick.net',
			),
			array(
				'name'        => 'test_cookie',
				'category_id' => 'marketing',
				'provider'    => 'Google DoubleClick',
				'purpose'     => __( 'Check if browser accepts cookies', 'consent-raven' ),
				'expiration'  => __( '15 minutes', 'consent-raven' ),
				'host'        => '.doubleclick.net',
			),
		);

		// Only add default if option doesn't exist.
		if ( false === get_option( 'consent_raven_cookies' ) ) {
			add_option( 'consent_raven_cookies', $default_cookies );
		}

		// Initialize empty scripts array.
		if ( false === get_option( 'consent_raven_scripts' ) ) {
			add_option( 'consent_raven_scripts', array() );
		}
	}
}
