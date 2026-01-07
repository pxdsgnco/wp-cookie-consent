<?php
/**
 * Consent handling functionality.
 *
 * Manages consent logic, categories, and cookie definitions.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */

/**
 * Consent handling class.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */
class CR_Consent {

	/**
	 * Get plugin settings.
	 *
	 * @since  1.0.0
	 * @return array Plugin settings.
	 */
	public static function get_settings() {
		$defaults = array(
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

		$settings = get_option( 'consent_raven_settings', array() );

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Update plugin settings.
	 *
	 * @since  1.0.0
	 * @param  array $settings New settings.
	 * @return bool Whether the settings were updated.
	 */
	public static function update_settings( $settings ) {
		$sanitized = self::sanitize_settings( $settings );

		/**
		 * Fires before settings are updated.
		 *
		 * @since 1.0.0
		 * @param array $sanitized The sanitized settings.
		 * @param array $settings  The original settings.
		 */
		do_action( 'consent_raven_before_settings_update', $sanitized, $settings );

		$result = update_option( 'consent_raven_settings', $sanitized );

		/**
		 * Fires after settings are updated.
		 *
		 * @since 1.0.0
		 * @param array $sanitized The sanitized settings.
		 * @param bool  $result    Whether the update was successful.
		 */
		do_action( 'consent_raven_after_settings_update', $sanitized, $result );

		return $result;
	}

	/**
	 * Sanitize plugin settings.
	 *
	 * @since  1.0.0
	 * @param  array $settings Settings to sanitize.
	 * @return array Sanitized settings.
	 */
	public static function sanitize_settings( $settings ) {
		$sanitized = array();

		// Boolean fields.
		$sanitized['enabled'] = isset( $settings['enabled'] ) ? (bool) $settings['enabled'] : true;

		// Position field.
		$valid_positions       = array( 'bottom-right', 'bottom-bar', 'top-bar', 'modal' );
		$sanitized['position'] = isset( $settings['position'] ) && in_array( $settings['position'], $valid_positions, true )
			? $settings['position']
			: 'bottom-right';

		// Policy page ID.
		$sanitized['policy_page_id'] = isset( $settings['policy_page_id'] )
			? absint( $settings['policy_page_id'] )
			: 0;

		// Consent version.
		$sanitized['consent_version'] = isset( $settings['consent_version'] )
			? sanitize_text_field( $settings['consent_version'] )
			: '1.0';

		// Appearance settings.
		$sanitized['appearance'] = array(
			'theme'            => isset( $settings['appearance']['theme'] )
				? sanitize_text_field( $settings['appearance']['theme'] )
				: 'dark',
			'background_color' => isset( $settings['appearance']['background_color'] )
				? sanitize_hex_color( $settings['appearance']['background_color'] )
				: '#1a1a1a',
			'text_color'       => isset( $settings['appearance']['text_color'] )
				? sanitize_hex_color( $settings['appearance']['text_color'] )
				: '#ffffff',
			'secondary_color'  => isset( $settings['appearance']['secondary_color'] )
				? sanitize_hex_color( $settings['appearance']['secondary_color'] )
				: '#b3b3b3',
			'button_bg'        => isset( $settings['appearance']['button_bg'] )
				? sanitize_hex_color( $settings['appearance']['button_bg'] )
				: '#ffffff',
			'button_text'      => isset( $settings['appearance']['button_text'] )
				? sanitize_hex_color( $settings['appearance']['button_text'] )
				: '#1a1a1a',
			'button_radius'    => isset( $settings['appearance']['button_radius'] )
				? sanitize_text_field( $settings['appearance']['button_radius'] )
				: '8px',
			'dialog_radius'    => isset( $settings['appearance']['dialog_radius'] )
				? sanitize_text_field( $settings['appearance']['dialog_radius'] )
				: '16px',
		);

		// Content settings.
		$sanitized['content'] = array(
			'title'            => isset( $settings['content']['title'] )
				? sanitize_text_field( $settings['content']['title'] )
				: __( 'Cookie settings', 'consent-raven' ),
			'description'      => isset( $settings['content']['description'] )
				? wp_kses_post( $settings['content']['description'] )
				: '',
			'accept_button'    => isset( $settings['content']['accept_button'] )
				? sanitize_text_field( $settings['content']['accept_button'] )
				: __( 'Accept All Cookies', 'consent-raven' ),
			'reject_button'    => isset( $settings['content']['reject_button'] )
				? sanitize_text_field( $settings['content']['reject_button'] )
				: __( 'Reject All Cookies', 'consent-raven' ),
			'customize_button' => isset( $settings['content']['customize_button'] )
				? sanitize_text_field( $settings['content']['customize_button'] )
				: __( 'Customize Cookie Settings', 'consent-raven' ),
			'save_button'      => isset( $settings['content']['save_button'] )
				? sanitize_text_field( $settings['content']['save_button'] )
				: __( 'Save Preferences', 'consent-raven' ),
			'policy_link_text' => isset( $settings['content']['policy_link_text'] )
				? sanitize_text_field( $settings['content']['policy_link_text'] )
				: __( 'here', 'consent-raven' ),
		);

		/**
		 * Filter the sanitized settings.
		 *
		 * @since 1.0.0
		 * @param array $sanitized The sanitized settings.
		 * @param array $settings  The original settings.
		 */
		return apply_filters( 'consent_raven_sanitize_settings', $sanitized, $settings );
	}

	/**
	 * Get cookie categories.
	 *
	 * @since  1.0.0
	 * @return array Cookie categories.
	 */
	public static function get_categories() {
		$categories = get_option( 'consent_raven_categories', array() );

		/**
		 * Filter the cookie categories.
		 *
		 * @since 1.0.0
		 * @param array $categories The cookie categories.
		 */
		return apply_filters( 'consent_raven_categories', $categories );
	}

	/**
	 * Update cookie categories.
	 *
	 * @since  1.0.0
	 * @param  array $categories New categories.
	 * @return bool Whether the categories were updated.
	 */
	public static function update_categories( $categories ) {
		$sanitized = array_map( array( __CLASS__, 'sanitize_category' ), $categories );
		return update_option( 'consent_raven_categories', $sanitized );
	}

	/**
	 * Sanitize a cookie category.
	 *
	 * @since  1.0.0
	 * @param  array $category Category to sanitize.
	 * @return array Sanitized category.
	 */
	public static function sanitize_category( $category ) {
		return array(
			'id'          => isset( $category['id'] )
				? sanitize_key( $category['id'] )
				: '',
			'slug'        => isset( $category['slug'] )
				? sanitize_title( $category['slug'] )
				: '',
			'name'        => isset( $category['name'] )
				? sanitize_text_field( $category['name'] )
				: '',
			'description' => isset( $category['description'] )
				? wp_kses_post( $category['description'] )
				: '',
			'essential'   => isset( $category['essential'] )
				? (bool) $category['essential']
				: false,
		);
	}

	/**
	 * Get cookie definitions.
	 *
	 * @since  1.0.0
	 * @return array Cookie definitions.
	 */
	public static function get_cookies() {
		$cookies = get_option( 'consent_raven_cookies', array() );

		/**
		 * Filter the cookie definitions.
		 *
		 * @since 1.0.0
		 * @param array $cookies The cookie definitions.
		 */
		return apply_filters( 'consent_raven_cookie_definitions', $cookies );
	}

	/**
	 * Update cookie definitions.
	 *
	 * @since  1.0.0
	 * @param  array $cookies New cookie definitions.
	 * @return bool Whether the cookies were updated.
	 */
	public static function update_cookies( $cookies ) {
		$sanitized = array_map( array( __CLASS__, 'sanitize_cookie' ), $cookies );
		return update_option( 'consent_raven_cookies', $sanitized );
	}

	/**
	 * Sanitize a cookie definition.
	 *
	 * @since  1.0.0
	 * @param  array $cookie Cookie to sanitize.
	 * @return array Sanitized cookie.
	 */
	public static function sanitize_cookie( $cookie ) {
		return array(
			'name'        => isset( $cookie['name'] )
				? sanitize_text_field( $cookie['name'] )
				: '',
			'category_id' => isset( $cookie['category_id'] )
				? sanitize_key( $cookie['category_id'] )
				: '',
			'provider'    => isset( $cookie['provider'] )
				? sanitize_text_field( $cookie['provider'] )
				: '',
			'purpose'     => isset( $cookie['purpose'] )
				? sanitize_text_field( $cookie['purpose'] )
				: '',
			'expiration'  => isset( $cookie['expiration'] )
				? sanitize_text_field( $cookie['expiration'] )
				: '',
			'host'        => isset( $cookie['host'] )
				? sanitize_text_field( $cookie['host'] )
				: '',
		);
	}

	/**
	 * Get registered scripts for blocking.
	 *
	 * @since  1.0.0
	 * @return array Registered scripts.
	 */
	public static function get_scripts() {
		$scripts = get_option( 'consent_raven_scripts', array() );

		/**
		 * Filter the registered scripts.
		 *
		 * @since 1.0.0
		 * @param array $scripts The registered scripts.
		 */
		return apply_filters( 'consent_raven_scripts', $scripts );
	}

	/**
	 * Update registered scripts.
	 *
	 * @since  1.0.0
	 * @param  array $scripts New scripts.
	 * @return bool Whether the scripts were updated.
	 */
	public static function update_scripts( $scripts ) {
		$sanitized = array_map( array( __CLASS__, 'sanitize_script' ), $scripts );
		return update_option( 'consent_raven_scripts', $sanitized );
	}

	/**
	 * Sanitize a script registration.
	 *
	 * @since  1.0.0
	 * @param  array $script Script to sanitize.
	 * @return array Sanitized script.
	 */
	public static function sanitize_script( $script ) {
		$valid_methods = array( 'type-swap', 'data-attribute', 'inline' );

		return array(
			'id'          => isset( $script['id'] )
				? sanitize_key( $script['id'] )
				: wp_generate_uuid4(),
			'category_id' => isset( $script['category_id'] )
				? sanitize_key( $script['category_id'] )
				: '',
			'handle'      => isset( $script['handle'] )
				? sanitize_key( $script['handle'] )
				: '',
			'pattern'     => isset( $script['pattern'] )
				? sanitize_text_field( $script['pattern'] )
				: '',
			'method'      => isset( $script['method'] ) && in_array( $script['method'], $valid_methods, true )
				? $script['method']
				: 'type-swap',
			'script'      => isset( $script['script'] )
				? $script['script'] // Not sanitized - contains JS code.
				: '',
		);
	}

	/**
	 * Check if consent banner should be displayed.
	 *
	 * @since  1.0.0
	 * @return bool Whether to show the banner.
	 */
	public static function should_show_banner() {
		$settings = self::get_settings();

		// Check if plugin is enabled.
		if ( ! $settings['enabled'] ) {
			return false;
		}

		// Don't show in admin.
		if ( is_admin() ) {
			return false;
		}

		// Don't show on login page.
		if ( function_exists( 'is_login' ) && is_login() ) {
			return false;
		}

		/**
		 * Filter whether to show the consent banner.
		 *
		 * @since 1.0.0
		 * @param bool  $show     Whether to show the banner.
		 * @param array $settings The plugin settings.
		 */
		return apply_filters( 'consent_raven_should_show_banner', true, $settings );
	}

	/**
	 * Get the cookie policy page URL.
	 *
	 * @since  1.0.0
	 * @return string|false The policy page URL or false if not set.
	 */
	public static function get_policy_page_url() {
		$settings = self::get_settings();

		if ( empty( $settings['policy_page_id'] ) ) {
			return false;
		}

		$url = get_permalink( $settings['policy_page_id'] );

		/**
		 * Filter the policy page URL.
		 *
		 * @since 1.0.0
		 * @param string|false $url      The policy page URL.
		 * @param int          $page_id  The policy page ID.
		 */
		return apply_filters( 'consent_raven_policy_page_url', $url, $settings['policy_page_id'] );
	}
}
