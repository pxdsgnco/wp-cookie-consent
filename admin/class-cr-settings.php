<?php
/**
 * Settings functionality for the plugin.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/admin
 */

/**
 * Settings class for the plugin.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/admin
 */
class CR_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Get default settings.
	 *
	 * @since  1.0.0
	 * @return array Default settings.
	 */
	public static function get_defaults() {
		return array(
			'enabled'              => true,
			'position'             => 'bottom-right',
			'policy_page_id'       => 0,
			'consent_version'      => '1.0',
			'log_retention_months' => 12,
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
	}

	/**
	 * Get valid positions.
	 *
	 * @since  1.0.0
	 * @return array Valid banner positions.
	 */
	public static function get_valid_positions() {
		return array(
			'bottom-right' => __( 'Bottom Right (Floating)', 'consent-raven' ),
			'bottom-bar'   => __( 'Bottom Bar (Full Width)', 'consent-raven' ),
			'top-bar'      => __( 'Top Bar (Full Width)', 'consent-raven' ),
			'modal'        => __( 'Centered Modal', 'consent-raven' ),
		);
	}

	/**
	 * Get valid themes.
	 *
	 * @since  1.0.0
	 * @return array Valid themes.
	 */
	public static function get_valid_themes() {
		return array(
			'dark'   => __( 'Dark', 'consent-raven' ),
			'light'  => __( 'Light', 'consent-raven' ),
			'custom' => __( 'Custom', 'consent-raven' ),
		);
	}

	/**
	 * Get theme presets.
	 *
	 * @since  1.0.0
	 * @return array Theme presets.
	 */
	public static function get_theme_presets() {
		return array(
			'dark'  => array(
				'background_color' => '#1a1a1a',
				'text_color'       => '#ffffff',
				'secondary_color'  => '#b3b3b3',
				'button_bg'        => '#ffffff',
				'button_text'      => '#1a1a1a',
			),
			'light' => array(
				'background_color' => '#ffffff',
				'text_color'       => '#1a1a1a',
				'secondary_color'  => '#666666',
				'button_bg'        => '#1a1a1a',
				'button_text'      => '#ffffff',
			),
		);
	}

	/**
	 * Validate settings.
	 *
	 * @since  1.0.0
	 * @param  array $settings Settings to validate.
	 * @return array|WP_Error Validated settings or error.
	 */
	public static function validate( $settings ) {
		$errors = array();

		// Validate position.
		if ( isset( $settings['position'] ) ) {
			$valid_positions = array_keys( self::get_valid_positions() );
			if ( ! in_array( $settings['position'], $valid_positions, true ) ) {
				$errors[] = __( 'Invalid banner position.', 'consent-raven' );
			}
		}

		// Validate appearance colors.
		if ( isset( $settings['appearance'] ) ) {
			$color_fields = array( 'background_color', 'text_color', 'secondary_color', 'button_bg', 'button_text' );
			foreach ( $color_fields as $field ) {
				if ( isset( $settings['appearance'][ $field ] ) ) {
					$color = sanitize_hex_color( $settings['appearance'][ $field ] );
					if ( empty( $color ) && ! empty( $settings['appearance'][ $field ] ) ) {
						/* translators: %s: field name */
						$errors[] = sprintf( __( 'Invalid color value for %s.', 'consent-raven' ), $field );
					}
				}
			}
		}

		// Validate consent version format.
		if ( isset( $settings['consent_version'] ) ) {
			if ( ! preg_match( '/^[\d.]+$/', $settings['consent_version'] ) ) {
				$errors[] = __( 'Consent version must contain only numbers and dots.', 'consent-raven' );
			}
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', implode( ' ', $errors ) );
		}

		return $settings;
	}

	/**
	 * Export settings to JSON.
	 *
	 * @since  1.0.0
	 * @return string JSON encoded settings.
	 */
	public static function export_settings() {
		$export = array(
			'version'    => CONSENT_RAVEN_VERSION,
			'settings'   => CR_Consent::get_settings(),
			'categories' => CR_Consent::get_categories(),
			'cookies'    => CR_Consent::get_cookies(),
			'scripts'    => CR_Consent::get_scripts(),
		);

		return wp_json_encode( $export, JSON_PRETTY_PRINT );
	}

	/**
	 * Import settings from JSON or array.
	 *
	 * @since  1.0.0
	 * @param  string|array $data JSON encoded settings or array.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function import_settings( $data ) {
		// Handle both JSON string and array input.
		if ( is_string( $data ) ) {
			$import = json_decode( $data, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', __( 'Invalid JSON format.', 'consent-raven' ) );
			}
		} else {
			$import = $data;
		}

		if ( ! is_array( $import ) ) {
			return new WP_Error( 'invalid_data', __( 'Invalid import data format.', 'consent-raven' ) );
		}

		if ( isset( $import['settings'] ) ) {
			CR_Consent::update_settings( $import['settings'] );
		}

		if ( isset( $import['categories'] ) ) {
			CR_Consent::update_categories( $import['categories'] );
		}

		if ( isset( $import['cookies'] ) ) {
			CR_Consent::update_cookies( $import['cookies'] );
		}

		if ( isset( $import['scripts'] ) ) {
			CR_Consent::update_scripts( $import['scripts'] );
		}

		return true;
	}

	/**
	 * Reset all settings to defaults.
	 *
	 * @since  1.1.0
	 * @return bool|WP_Error True on success or error.
	 */
	public static function reset_to_defaults() {
		// Reset settings to defaults.
		$default_settings = self::get_defaults();
		CR_Consent::update_settings( $default_settings );

		// Reset categories to defaults.
		$default_categories = array(
			array(
				'id'          => 'essential',
				'slug'        => 'essential',
				'name'        => __( 'Essential', 'consent-raven' ),
				'description' => __( 'These cookies are necessary for the website to function and cannot be switched off.', 'consent-raven' ),
				'essential'   => true,
			),
			array(
				'id'          => 'analytics',
				'slug'        => 'analytics',
				'name'        => __( 'Analytics', 'consent-raven' ),
				'description' => __( 'These cookies help us understand how visitors interact with the website.', 'consent-raven' ),
				'essential'   => false,
			),
			array(
				'id'          => 'marketing',
				'slug'        => 'marketing',
				'name'        => __( 'Marketing', 'consent-raven' ),
				'description' => __( 'These cookies are used to deliver personalized ads and track ad campaigns.', 'consent-raven' ),
				'essential'   => false,
			),
		);
		CR_Consent::update_categories( $default_categories );

		// Reset cookies to defaults.
		$default_cookies = array(
			array(
				'name'        => 'wordpress_logged_in_*',
				'category_id' => 'essential',
				'provider'    => 'WordPress',
				'purpose'     => 'Indicates when a user is logged in',
				'expiration'  => 'Session',
				'host'        => '',
			),
			array(
				'name'        => 'wp-settings-*',
				'category_id' => 'essential',
				'provider'    => 'WordPress',
				'purpose'     => 'Stores user preferences',
				'expiration'  => '1 year',
				'host'        => '',
			),
			array(
				'name'        => 'consent_raven',
				'category_id' => 'essential',
				'provider'    => 'Consent Raven',
				'purpose'     => 'Stores cookie consent preferences',
				'expiration'  => '1 year',
				'host'        => '',
			),
			array(
				'name'        => '_ga',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics',
				'purpose'     => 'Distinguishes unique users',
				'expiration'  => '2 years',
				'host'        => '.google.com',
			),
			array(
				'name'        => '_gid',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics',
				'purpose'     => 'Distinguishes unique users',
				'expiration'  => '24 hours',
				'host'        => '.google.com',
			),
			array(
				'name'        => '_gat',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics',
				'purpose'     => 'Throttles request rate',
				'expiration'  => '1 minute',
				'host'        => '.google.com',
			),
			array(
				'name'        => '_ga_*',
				'category_id' => 'analytics',
				'provider'    => 'Google Analytics 4',
				'purpose'     => 'Maintains session state',
				'expiration'  => '2 years',
				'host'        => '.google.com',
			),
			array(
				'name'        => '_fbp',
				'category_id' => 'marketing',
				'provider'    => 'Facebook',
				'purpose'     => 'Tracks visits across websites',
				'expiration'  => '3 months',
				'host'        => '.facebook.com',
			),
			array(
				'name'        => 'fr',
				'category_id' => 'marketing',
				'provider'    => 'Facebook',
				'purpose'     => 'Enables ad delivery',
				'expiration'  => '3 months',
				'host'        => '.facebook.com',
			),
			array(
				'name'        => 'IDE',
				'category_id' => 'marketing',
				'provider'    => 'Google DoubleClick',
				'purpose'     => 'Ad targeting and retargeting',
				'expiration'  => '1 year',
				'host'        => '.doubleclick.net',
			),
			array(
				'name'        => 'test_cookie',
				'category_id' => 'marketing',
				'provider'    => 'Google DoubleClick',
				'purpose'     => 'Checks if cookies are enabled',
				'expiration'  => '15 minutes',
				'host'        => '.doubleclick.net',
			),
		);
		CR_Consent::update_cookies( $default_cookies );

		// Reset scripts to empty.
		CR_Consent::update_scripts( array() );

		return true;
	}
}
