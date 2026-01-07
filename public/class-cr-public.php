<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */
class CR_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		if ( ! CR_Consent::should_show_banner() ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			CONSENT_RAVEN_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			$this->version,
			'all'
		);

		// Add inline styles for custom colors.
		$this->add_custom_styles();
	}

	/**
	 * Register the JavaScript for the public-facing side.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		if ( ! CR_Consent::should_show_banner() ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			CONSENT_RAVEN_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			$this->version,
			true
		);

		// Add script activation inline script before the main script.
		wp_add_inline_script(
			$this->plugin_name,
			CR_Script_Blocker::get_activation_script(),
			'before'
		);

		// Pass data to JavaScript.
		$settings   = CR_Consent::get_settings();
		$categories = CR_Consent::get_categories();

		wp_localize_script(
			$this->plugin_name,
			'consentRaven',
			array(
				'settings'      => array(
					'position'       => $settings['position'],
					'consentVersion' => $settings['consent_version'],
					'policyPageUrl'  => CR_Consent::get_policy_page_url(),
				),
				'categories'    => $categories,
				'content'       => $settings['content'],
				'cookieName'    => 'consent_raven',
				'cookieExpiry'  => 365, // Days.
				'i18n'          => array(
					'showDetails'      => __( 'Show details', 'consent-raven' ),
					'hideDetails'      => __( 'Hide details', 'consent-raven' ),
					'on'               => __( 'On', 'consent-raven' ),
					'off'              => __( 'Off', 'consent-raven' ),
					'acceptedAll'      => __( 'All cookies have been accepted. Your preferences have been saved.', 'consent-raven' ),
					'rejectedAll'      => __( 'Non-essential cookies have been rejected. Your preferences have been saved.', 'consent-raven' ),
					'preferencesSaved' => __( 'Your cookie preferences have been saved.', 'consent-raven' ),
				),
			)
		);
	}

	/**
	 * Add custom inline styles based on settings.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function add_custom_styles() {
		$settings   = CR_Consent::get_settings();
		$appearance = $settings['appearance'];

		$css = ':root {
			--cr-bg-color: ' . esc_attr( $appearance['background_color'] ) . ';
			--cr-text-color: ' . esc_attr( $appearance['text_color'] ) . ';
			--cr-secondary-color: ' . esc_attr( $appearance['secondary_color'] ) . ';
			--cr-button-bg: ' . esc_attr( $appearance['button_bg'] ) . ';
			--cr-button-text: ' . esc_attr( $appearance['button_text'] ) . ';
			--cr-button-radius: ' . esc_attr( $appearance['button_radius'] ) . ';
			--cr-dialog-radius: ' . esc_attr( $appearance['dialog_radius'] ) . ';
		}';

		wp_add_inline_style( $this->plugin_name, $css );
	}

	/**
	 * Add defer attribute to frontend script for performance.
	 *
	 * @since  1.0.0
	 * @param  string $tag    The script tag.
	 * @param  string $handle The script handle.
	 * @param  string $src    The script source URL.
	 * @return string Modified script tag.
	 */
	public function add_defer_attribute( $tag, $handle, $src ) {
		if ( $this->plugin_name !== $handle ) {
			return $tag;
		}

		// Add defer attribute for non-blocking loading.
		return str_replace( ' src', ' defer src', $tag );
	}
}
