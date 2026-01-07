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
		?>
		<!-- Consent Raven Cookie Banner -->
		<div id="consent-raven-banner"
			class="cr-banner cr-banner--<?php echo esc_attr( $position ); ?>"
			role="dialog"
			aria-modal="true"
			aria-labelledby="cr-banner-title"
			aria-describedby="cr-banner-description"
			data-position="<?php echo esc_attr( $position ); ?>"
			style="display: none;">

			<?php if ( 'modal' === $position ) : ?>
			<div class="cr-overlay"></div>
			<?php endif; ?>

			<div class="cr-dialog">
				<div class="cr-dialog__content">
					<h2 id="cr-banner-title" class="cr-dialog__title">
						<?php echo esc_html( $content['title'] ); ?>
					</h2>

					<p id="cr-banner-description" class="cr-dialog__description">
						<?php echo wp_kses_post( $description ); ?>
					</p>

					<div class="cr-dialog__actions">
						<button type="button" class="cr-button cr-button--customize" data-action="customize">
							<?php echo esc_html( $content['customize_button'] ); ?>
						</button>

						<div class="cr-dialog__buttons">
							<button type="button" class="cr-button cr-button--reject" data-action="reject">
								<?php echo esc_html( $content['reject_button'] ); ?>
							</button>
							<button type="button" class="cr-button cr-button--accept" data-action="accept">
								<?php echo esc_html( $content['accept_button'] ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Consent Raven Preferences Modal -->
		<div id="consent-raven-preferences"
			class="cr-preferences"
			role="dialog"
			aria-modal="true"
			aria-labelledby="cr-preferences-title"
			style="display: none;">

			<div class="cr-overlay"></div>

			<div class="cr-preferences__dialog">
				<div class="cr-preferences__header">
					<h2 id="cr-preferences-title" class="cr-preferences__title">
						<?php echo esc_html( $content['customize_button'] ); ?>
					</h2>
					<button type="button" class="cr-preferences__close" data-action="close-preferences" aria-label="<?php esc_attr_e( 'Close', 'consent-raven' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="cr-preferences__content">
					<div class="cr-preferences__categories">
						<?php foreach ( $categories as $category ) : ?>
						<div class="cr-category" data-category="<?php echo esc_attr( $category['slug'] ); ?>">
							<div class="cr-category__header">
								<div class="cr-category__info">
									<h3 class="cr-category__name"><?php echo esc_html( $category['name'] ); ?></h3>
									<p class="cr-category__description"><?php echo esc_html( $category['description'] ); ?></p>
								</div>
								<div class="cr-category__toggle">
									<?php if ( $category['essential'] ) : ?>
									<span class="cr-toggle cr-toggle--always-on">
										<?php esc_html_e( 'Always on', 'consent-raven' ); ?>
									</span>
									<?php else : ?>
									<label class="cr-toggle">
										<input type="checkbox"
											class="cr-toggle__input"
											name="consent_category_<?php echo esc_attr( $category['slug'] ); ?>"
											data-category="<?php echo esc_attr( $category['slug'] ); ?>">
										<span class="cr-toggle__slider"></span>
										<span class="sr-only"><?php echo esc_html( $category['name'] ); ?></span>
									</label>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="cr-preferences__footer">
					<button type="button" class="cr-button cr-button--save" data-action="save-preferences">
						<?php echo esc_html( $content['save_button'] ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Consent Raven Settings Button (shown after consent) -->
		<button id="consent-raven-settings-button"
			class="cr-settings-button"
			type="button"
			aria-label="<?php esc_attr_e( 'Cookie Settings', 'consent-raven' ); ?>"
			style="display: none;">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
				<path d="M12 2C11.5 2 11 2.19 10.59 2.59L9.17 4H7C5.9 4 5 4.9 5 6V8.17L3.59 9.59C2.8 10.37 2.8 11.63 3.59 12.41L5 13.83V16C5 17.1 5.9 18 7 18H9.17L10.59 19.41C11.37 20.2 12.63 20.2 13.41 19.41L14.83 18H17C18.1 18 19 17.1 19 16V13.83L20.41 12.41C21.2 11.63 21.2 10.37 20.41 9.59L19 8.17V6C19 4.9 18.1 4 17 4H14.83L13.41 2.59C13 2.19 12.5 2 12 2M12 8C14.21 8 16 9.79 16 12S14.21 16 12 16 8 14.21 8 12 9.79 8 12 8Z"/>
			</svg>
		</button>
		<?php
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
}
