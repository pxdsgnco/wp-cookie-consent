<?php
/**
 * Tests for CR_Banner class.
 *
 * @package Consent_Raven
 */

/**
 * Test case for banner functionality.
 */
class Test_CR_Banner extends WP_UnitTestCase {

	/**
	 * Banner instance.
	 *
	 * @var CR_Banner
	 */
	private $banner;

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		// Reset options.
		delete_option( 'consent_raven_settings' );
		delete_option( 'consent_raven_categories' );

		// Set default categories.
		CR_Consent::update_categories(
			array(
				array(
					'id'          => 'essential',
					'slug'        => 'essential',
					'name'        => 'Essential',
					'description' => 'Required cookies',
					'essential'   => true,
				),
				array(
					'id'          => 'analytics',
					'slug'        => 'analytics',
					'name'        => 'Analytics',
					'description' => 'Analytics cookies',
					'essential'   => false,
				),
			)
		);

		// Create banner instance.
		$this->banner = new CR_Banner( 'consent-raven', '1.0.0' );
	}

	/**
	 * Test render_banner outputs banner HTML.
	 */
	public function test_render_banner_outputs_html() {
		// Enable banner.
		CR_Consent::update_settings( array( 'enabled' => true ) );

		// Not in admin.
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="consent-raven-banner"', $output );
		$this->assertStringContainsString( 'role="dialog"', $output );
	}

	/**
	 * Test render_banner includes preferences modal.
	 */
	public function test_render_banner_includes_preferences() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="consent-raven-preferences"', $output );
		$this->assertStringContainsString( 'cr-preferences', $output );
	}

	/**
	 * Test render_banner includes settings button.
	 */
	public function test_render_banner_includes_settings_button() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="consent-raven-settings-button"', $output );
		$this->assertStringContainsString( 'cr-settings-button', $output );
	}

	/**
	 * Test render_banner includes screen reader announcer.
	 */
	public function test_render_banner_includes_announcer() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="consent-raven-announcer"', $output );
		$this->assertStringContainsString( 'aria-live="polite"', $output );
	}

	/**
	 * Test render_banner respects position setting.
	 */
	public function test_render_banner_respects_position() {
		CR_Consent::update_settings(
			array(
				'enabled'  => true,
				'position' => 'top-bar',
			)
		);
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'cr-banner--top-bar', $output );
		$this->assertStringContainsString( 'data-position="top-bar"', $output );
	}

	/**
	 * Test render_banner includes overlay for modal position.
	 */
	public function test_render_banner_includes_overlay_for_modal() {
		CR_Consent::update_settings(
			array(
				'enabled'  => true,
				'position' => 'modal',
			)
		);
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'cr-banner--modal', $output );
		// Modal has overlay in banner.
		$this->assertStringContainsString( 'cr-overlay', $output );
	}

	/**
	 * Test render_banner displays categories.
	 */
	public function test_render_banner_displays_categories() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Essential', $output );
		$this->assertStringContainsString( 'Analytics', $output );
	}

	/**
	 * Test render_banner marks essential category as always on.
	 */
	public function test_render_banner_essential_always_on() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'cr-toggle--always-on', $output );
		$this->assertStringContainsString( 'Always on', $output );
	}

	/**
	 * Test render_banner includes toggle for non-essential categories.
	 */
	public function test_render_banner_includes_toggles() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'cr-toggle__input', $output );
		$this->assertStringContainsString( 'data-category="analytics"', $output );
	}

	/**
	 * Test render_banner uses custom content.
	 */
	public function test_render_banner_uses_custom_content() {
		CR_Consent::update_settings(
			array(
				'enabled' => true,
				'content' => array(
					'title'            => 'Custom Title',
					'description'      => 'Custom description text.',
					'accept_button'    => 'Accept Cookies',
					'reject_button'    => 'Decline All',
					'customize_button' => 'Manage Preferences',
					'save_button'      => 'Save Settings',
					'policy_link_text' => 'View Policy',
				),
			)
		);
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Custom Title', $output );
		$this->assertStringContainsString( 'Custom description text', $output );
		$this->assertStringContainsString( 'Accept Cookies', $output );
		$this->assertStringContainsString( 'Decline All', $output );
		$this->assertStringContainsString( 'Manage Preferences', $output );
	}

	/**
	 * Test render_banner includes policy link when page set.
	 */
	public function test_render_banner_includes_policy_link() {
		// Create a policy page.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Cookie Policy',
				'post_status' => 'publish',
			)
		);

		CR_Consent::update_settings(
			array(
				'enabled'        => true,
				'policy_page_id' => $page_id,
			)
		);
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'cr-policy-link', $output );
		$this->assertStringContainsString( 'cookie-policy', $output );
	}

	/**
	 * Test consent_raven_before_banner hook fires.
	 */
	public function test_before_banner_hook_fires() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		$hook_fired = false;
		add_action(
			'consent_raven_before_banner',
			function () use ( &$hook_fired ) {
				$hook_fired = true;
			}
		);

		ob_start();
		$this->banner->render_banner();
		ob_get_clean();

		$this->assertTrue( $hook_fired );
	}

	/**
	 * Test consent_raven_after_banner hook fires.
	 */
	public function test_after_banner_hook_fires() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		$hook_fired = false;
		add_action(
			'consent_raven_after_banner',
			function () use ( &$hook_fired ) {
				$hook_fired = true;
			}
		);

		ob_start();
		$this->banner->render_banner();
		ob_get_clean();

		$this->assertTrue( $hook_fired );
	}

	/**
	 * Test consent_raven_banner_html filter.
	 */
	public function test_banner_html_filter() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		add_filter(
			'consent_raven_banner_html',
			function ( $html, $settings ) {
				return '<!-- FILTERED -->' . $html;
			},
			10,
			2
		);

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<!-- FILTERED -->', $output );
	}

	/**
	 * Test render_banner does not output when disabled.
	 */
	public function test_render_banner_silent_when_disabled() {
		CR_Consent::update_settings( array( 'enabled' => false ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test banner has proper ARIA attributes.
	 */
	public function test_banner_has_aria_attributes() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		// Main banner.
		$this->assertStringContainsString( 'role="dialog"', $output );
		$this->assertStringContainsString( 'aria-modal="true"', $output );
		$this->assertStringContainsString( 'aria-labelledby="cr-banner-title"', $output );
		$this->assertStringContainsString( 'aria-describedby="cr-banner-description"', $output );

		// Preferences modal.
		$this->assertStringContainsString( 'aria-labelledby="cr-preferences-title"', $output );
	}

	/**
	 * Test banner has skip link.
	 */
	public function test_banner_has_skip_link() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'cr-skip-link', $output );
		$this->assertStringContainsString( 'Skip cookie banner', $output );
	}

	/**
	 * Test banner buttons have proper attributes.
	 */
	public function test_banner_buttons_have_attributes() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		// Customize button.
		$this->assertStringContainsString( 'data-action="customize"', $output );
		$this->assertStringContainsString( 'aria-controls="consent-raven-preferences"', $output );
		$this->assertStringContainsString( 'aria-expanded="false"', $output );

		// Accept/reject buttons.
		$this->assertStringContainsString( 'data-action="accept"', $output );
		$this->assertStringContainsString( 'data-action="reject"', $output );
	}

	/**
	 * Test toggle switches have role="switch".
	 */
	public function test_toggles_have_switch_role() {
		CR_Consent::update_settings( array( 'enabled' => true ) );
		$GLOBALS['current_screen'] = null;

		ob_start();
		$this->banner->render_banner();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'role="switch"', $output );
		$this->assertStringContainsString( 'aria-checked=', $output );
	}
}
