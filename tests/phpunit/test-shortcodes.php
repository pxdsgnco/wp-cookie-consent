<?php
/**
 * Tests for CR_Shortcodes class.
 *
 * @package Consent_Raven
 */

/**
 * Test case for shortcode functionality.
 */
class Test_CR_Shortcodes extends WP_UnitTestCase {

	/**
	 * Shortcodes instance.
	 *
	 * @var CR_Shortcodes
	 */
	private $shortcodes;

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		// Reset options.
		delete_option( 'consent_raven_settings' );
		delete_option( 'consent_raven_categories' );
		delete_option( 'consent_raven_cookies' );

		// Create shortcodes instance.
		$this->shortcodes = new CR_Shortcodes( 'consent-raven', '1.0.0' );

		// Register shortcodes.
		$this->shortcodes->register_shortcodes();
	}

	/**
	 * Test shortcode is registered.
	 */
	public function test_shortcode_registered() {
		global $shortcode_tags;

		$this->assertArrayHasKey( 'consent_raven_policy_table', $shortcode_tags );
	}

	/**
	 * Test render_policy_table returns empty message when no cookies.
	 */
	public function test_render_policy_table_empty() {
		$output = do_shortcode( '[consent_raven_policy_table]' );

		$this->assertStringContainsString( 'cr-policy-table-empty', $output );
		$this->assertStringContainsString( 'No cookies have been configured', $output );
	}

	/**
	 * Test render_policy_table displays cookies.
	 */
	public function test_render_policy_table_with_cookies() {
		// Set up categories.
		CR_Consent::update_categories(
			array(
				array(
					'id'          => 'analytics',
					'slug'        => 'analytics',
					'name'        => 'Analytics',
					'description' => 'Analytics cookies',
					'essential'   => false,
				),
			)
		);

		// Set up cookies.
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => '_ga',
					'category_id' => 'analytics',
					'provider'    => 'Google Analytics',
					'purpose'     => 'Distinguish users',
					'expiration'  => '2 years',
					'host'        => '.example.com',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table]' );

		$this->assertStringContainsString( 'cr-policy-table', $output );
		$this->assertStringContainsString( '_ga', $output );
		$this->assertStringContainsString( 'Google Analytics', $output );
		$this->assertStringContainsString( 'Distinguish users', $output );
		$this->assertStringContainsString( '2 years', $output );
	}

	/**
	 * Test render_policy_table hides category column.
	 */
	public function test_render_policy_table_hide_category() {
		// Set up cookies.
		CR_Consent::update_categories(
			array(
				array(
					'id'          => 'essential',
					'slug'        => 'essential',
					'name'        => 'Essential',
					'description' => 'Required',
					'essential'   => true,
				),
			)
		);

		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'session_id',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Session management',
					'expiration'  => 'Session',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table show_category="false"]' );

		$this->assertStringContainsString( 'session_id', $output );
		// Category column should not be present.
		$this->assertStringNotContainsString( 'cr-policy-table__category', $output );
	}

	/**
	 * Test render_policy_table hides provider column.
	 */
	public function test_render_policy_table_hide_provider() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'test_cookie',
					'category_id' => 'essential',
					'provider'    => 'Test Provider',
					'purpose'     => 'Testing',
					'expiration'  => '1 hour',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table show_provider="false"]' );

		$this->assertStringContainsString( 'test_cookie', $output );
		// Provider should not show.
		$this->assertStringNotContainsString( 'Test Provider', $output );
	}

	/**
	 * Test render_policy_table hides expiration column.
	 */
	public function test_render_policy_table_hide_expiration() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'test_cookie',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Testing',
					'expiration'  => '30 days',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table show_expiration="false"]' );

		$this->assertStringContainsString( 'test_cookie', $output );
		// Expiration column should not be present.
		$this->assertStringNotContainsString( 'cr-policy-table__expiration', $output );
	}

	/**
	 * Test render_policy_table shows host column.
	 */
	public function test_render_policy_table_show_host() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'test_cookie',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Testing',
					'expiration'  => '1 day',
					'host'        => '.example.com',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table show_host="true"]' );

		$this->assertStringContainsString( 'cr-policy-table__host', $output );
		$this->assertStringContainsString( '.example.com', $output );
	}

	/**
	 * Test render_policy_table filters by category.
	 */
	public function test_render_policy_table_filter_category() {
		CR_Consent::update_categories(
			array(
				array(
					'id'          => 'essential',
					'slug'        => 'essential',
					'name'        => 'Essential',
					'description' => 'Required',
					'essential'   => true,
				),
				array(
					'id'          => 'analytics',
					'slug'        => 'analytics',
					'name'        => 'Analytics',
					'description' => 'Analytics',
					'essential'   => false,
				),
			)
		);

		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'session_id',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Session',
					'expiration'  => 'Session',
				),
				array(
					'name'        => '_ga',
					'category_id' => 'analytics',
					'provider'    => 'Google',
					'purpose'     => 'Analytics',
					'expiration'  => '2 years',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table category="analytics"]' );

		$this->assertStringContainsString( '_ga', $output );
		$this->assertStringNotContainsString( 'session_id', $output );
	}

	/**
	 * Test consent_raven_policy_table_cookies filter.
	 */
	public function test_policy_table_cookies_filter() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'original_cookie',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Original',
					'expiration'  => '1 day',
				),
			)
		);

		add_filter(
			'consent_raven_policy_table_cookies',
			function ( $cookies, $filter_category ) {
				$cookies[] = array(
					'name'        => 'filtered_cookie',
					'category_id' => 'essential',
					'provider'    => 'Filter',
					'purpose'     => 'Added via filter',
					'expiration'  => '1 hour',
				);
				return $cookies;
			},
			10,
			2
		);

		$output = do_shortcode( '[consent_raven_policy_table]' );

		$this->assertStringContainsString( 'original_cookie', $output );
		$this->assertStringContainsString( 'filtered_cookie', $output );
	}

	/**
	 * Test consent_raven_policy_table_html filter.
	 */
	public function test_policy_table_html_filter() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'test_cookie',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Testing',
					'expiration'  => '1 day',
				),
			)
		);

		add_filter(
			'consent_raven_policy_table_html',
			function ( $html, $cookies ) {
				return '<div class="custom-wrapper">' . $html . '</div>';
			},
			10,
			2
		);

		$output = do_shortcode( '[consent_raven_policy_table]' );

		$this->assertStringContainsString( 'custom-wrapper', $output );
	}

	/**
	 * Test render_block method.
	 */
	public function test_render_block() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'block_test',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Block test',
					'expiration'  => '1 day',
				),
			)
		);

		$output = $this->shortcodes->render_block(
			array(
				'showCategory'   => true,
				'showProvider'   => true,
				'showExpiration' => true,
				'showHost'       => false,
				'filterCategory' => '',
			)
		);

		$this->assertStringContainsString( 'cr-policy-table-block', $output );
		$this->assertStringContainsString( 'block_test', $output );
	}

	/**
	 * Test render_block with filter.
	 */
	public function test_render_block_with_filter() {
		CR_Consent::update_categories(
			array(
				array(
					'id'          => 'marketing',
					'slug'        => 'marketing',
					'name'        => 'Marketing',
					'description' => 'Marketing cookies',
					'essential'   => false,
				),
			)
		);

		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'fbp',
					'category_id' => 'marketing',
					'provider'    => 'Facebook',
					'purpose'     => 'Tracking',
					'expiration'  => '90 days',
				),
				array(
					'name'        => 'session',
					'category_id' => 'essential',
					'provider'    => 'Website',
					'purpose'     => 'Session',
					'expiration'  => 'Session',
				),
			)
		);

		$output = $this->shortcodes->render_block(
			array(
				'showCategory'   => true,
				'showProvider'   => true,
				'showExpiration' => true,
				'showHost'       => false,
				'filterCategory' => 'marketing',
			)
		);

		$this->assertStringContainsString( 'fbp', $output );
		$this->assertStringNotContainsString( '>session<', $output );
	}

	/**
	 * Test table includes accessible data-label attributes.
	 */
	public function test_table_has_data_labels() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => 'test',
					'category_id' => 'essential',
					'provider'    => 'Test',
					'purpose'     => 'Testing',
					'expiration'  => '1 day',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table]' );

		$this->assertStringContainsString( 'data-label=', $output );
	}

	/**
	 * Test table escapes cookie names properly.
	 */
	public function test_table_escapes_output() {
		CR_Consent::update_cookies(
			array(
				array(
					'name'        => '<script>alert("xss")</script>',
					'category_id' => 'essential',
					'provider'    => '<b>Bold</b>',
					'purpose'     => 'Testing "quotes"',
					'expiration'  => '1 day',
				),
			)
		);

		$output = do_shortcode( '[consent_raven_policy_table]' );

		// Script tags should be escaped.
		$this->assertStringNotContainsString( '<script>', $output );
		$this->assertStringNotContainsString( '<b>', $output );
	}
}
