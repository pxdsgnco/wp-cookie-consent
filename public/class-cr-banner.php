<?php
/**
 * Banner rendering functionality.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */

/**
 * Banner rendering class.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */
class CR_Banner {

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
	 * Render the consent banner.
	 *
	 * @since 1.0.0
	 */
	public function render_banner() {
		if ( ! CR_Consent::should_show_banner() ) {
			return;
		}

		$settings   = CR_Consent::get_settings();
		$categories = CR_Consent::get_categories();
		$content    = $settings['content'];
		$position   = $settings['position'];
		$policy_url = CR_Consent::get_policy_page_url();

		/**
		 * Fires before the banner is rendered.
		 *
		 * @since 1.0.0
		 * @param array $settings The plugin settings.
		 */
		do_action( 'consent_raven_before_banner', $settings );

		// Build the description with policy link.
		$description = $content['description'];
		if ( $policy_url ) {
			$description = str_replace(
				$content['policy_link_text'],
				'<a href="' . esc_url( $policy_url ) . '" class="cr-policy-link">' . esc_html( $content['policy_link_text'] ) . '</a>',
				$description
			);
		}

		// Start output buffering.
		ob_start();

		// Load template - allow theme override.
		$template = $this->locate_template( 'banner-template.php' );
		include $template;

		$html = ob_get_clean();

		/**
		 * Filter the banner HTML.
		 *
		 * @since 1.0.0
		 * @param string $html     The banner HTML.
		 * @param array  $settings The plugin settings.
		 */
		$html = apply_filters( 'consent_raven_banner_html', $html, $settings );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		/**
		 * Fires after the banner is rendered.
		 *
		 * @since 1.0.0
		 * @param array $settings The plugin settings.
		 */
		do_action( 'consent_raven_after_banner', $settings );
	}

	/**
	 * Locate a template file.
	 *
	 * Look for template in theme first, then fall back to plugin.
	 *
	 * @since  1.0.0
	 * @param  string $template_name Template file name.
	 * @return string Template file path.
	 */
	private function locate_template( $template_name ) {
		// Look in theme first.
		$theme_template = locate_template(
			array(
				'consent-raven/' . $template_name,
				$template_name,
			)
		);

		if ( $theme_template ) {
			/**
			 * Filter the located theme template.
			 *
			 * @since 1.0.0
			 * @param string $theme_template  The theme template path.
			 * @param string $template_name   The template file name.
			 */
			return apply_filters( 'consent_raven_theme_template', $theme_template, $template_name );
		}

		// Fall back to plugin template.
		$plugin_template = CONSENT_RAVEN_PLUGIN_DIR . 'public/partials/' . $template_name;

		/**
		 * Filter the located plugin template.
		 *
		 * @since 1.0.0
		 * @param string $plugin_template The plugin template path.
		 * @param string $template_name   The template file name.
		 */
		return apply_filters( 'consent_raven_plugin_template', $plugin_template, $template_name );
	}
}
