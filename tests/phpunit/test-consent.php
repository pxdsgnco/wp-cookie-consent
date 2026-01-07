<?php
/**
 * Tests for CR_Consent class.
 *
 * @package Consent_Raven
 */

/**
 * Test case for consent functionality.
 */
class Test_CR_Consent extends WP_UnitTestCase {

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		// Reset options before each test.
		delete_option( 'consent_raven_settings' );
		delete_option( 'consent_raven_categories' );
		delete_option( 'consent_raven_cookies' );
		delete_option( 'consent_raven_scripts' );
	}

	/**
	 * Test getting default settings.
	 */
	public function test_get_settings_returns_defaults() {
		$settings = CR_Consent::get_settings();

		$this->assertIsArray( $settings );
		$this->assertTrue( $settings['enabled'] );
		$this->assertEquals( 'bottom-right', $settings['position'] );
		$this->assertEquals( '1.0', $settings['consent_version'] );
	}

	/**
	 * Test updating settings.
	 */
	public function test_update_settings() {
		$new_settings = array(
			'enabled'  => false,
			'position' => 'top-bar',
		);

		$result = CR_Consent::update_settings( $new_settings );

		$this->assertTrue( $result );

		$settings = CR_Consent::get_settings();
		$this->assertFalse( $settings['enabled'] );
		$this->assertEquals( 'top-bar', $settings['position'] );
	}

	/**
	 * Test settings sanitization.
	 */
	public function test_sanitize_settings() {
		$dirty_settings = array(
			'enabled'  => 'yes',
			'position' => 'invalid-position',
			'content'  => array(
				'title' => '<script>alert("xss")</script>Cookie Settings',
			),
		);

		$sanitized = CR_Consent::sanitize_settings( $dirty_settings );

		// String 'yes' should be cast to boolean true.
		$this->assertTrue( $sanitized['enabled'] );

		// Invalid position should default to bottom-right.
		$this->assertEquals( 'bottom-right', $sanitized['position'] );

		// Script tags should be removed from title.
		$this->assertStringNotContainsString( '<script>', $sanitized['content']['title'] );
	}

	/**
	 * Test getting default categories.
	 */
	public function test_get_categories_returns_empty_when_not_set() {
		$categories = CR_Consent::get_categories();

		$this->assertIsArray( $categories );
	}

	/**
	 * Test updating categories.
	 */
	public function test_update_categories() {
		$categories = array(
			array(
				'id'          => 'test',
				'slug'        => 'test',
				'name'        => 'Test Category',
				'description' => 'A test category',
				'essential'   => false,
			),
		);

		$result = CR_Consent::update_categories( $categories );

		$this->assertTrue( $result );

		$saved_categories = CR_Consent::get_categories();
		$this->assertCount( 1, $saved_categories );
		$this->assertEquals( 'test', $saved_categories[0]['slug'] );
	}

	/**
	 * Test category sanitization.
	 */
	public function test_sanitize_category() {
		$dirty_category = array(
			'id'          => 'Test Category!@#',
			'slug'        => 'Test Slug With Spaces',
			'name'        => '<b>Bold Name</b>',
			'description' => '<script>alert("xss")</script>Description',
			'essential'   => 'yes',
		);

		$sanitized = CR_Consent::sanitize_category( $dirty_category );

		// ID should be sanitized as key.
		$this->assertEquals( 'test-category', $sanitized['id'] );

		// Slug should be sanitized as title.
		$this->assertStringNotContainsString( ' ', $sanitized['slug'] );

		// Name should have tags stripped.
		$this->assertStringNotContainsString( '<b>', $sanitized['name'] );

		// Description allows some HTML but not script tags.
		$this->assertStringNotContainsString( '<script>', $sanitized['description'] );

		// Essential should be boolean.
		$this->assertTrue( $sanitized['essential'] );
	}

	/**
	 * Test getting cookies.
	 */
	public function test_get_cookies() {
		$cookies = CR_Consent::get_cookies();

		$this->assertIsArray( $cookies );
	}

	/**
	 * Test updating cookies.
	 */
	public function test_update_cookies() {
		$cookies = array(
			array(
				'name'        => '_test_cookie',
				'category_id' => 'analytics',
				'provider'    => 'Test Provider',
				'purpose'     => 'Testing',
				'expiration'  => '1 year',
				'host'        => '.example.com',
			),
		);

		$result = CR_Consent::update_cookies( $cookies );

		$this->assertTrue( $result );

		$saved_cookies = CR_Consent::get_cookies();
		$this->assertCount( 1, $saved_cookies );
		$this->assertEquals( '_test_cookie', $saved_cookies[0]['name'] );
	}

	/**
	 * Test should_show_banner in admin.
	 */
	public function test_should_show_banner_false_in_admin() {
		// Set admin context.
		set_current_screen( 'dashboard' );

		$this->assertFalse( CR_Consent::should_show_banner() );
	}

	/**
	 * Test should_show_banner when disabled.
	 */
	public function test_should_show_banner_false_when_disabled() {
		CR_Consent::update_settings( array( 'enabled' => false ) );

		// We need to be on frontend, not admin.
		// Reset current screen.
		$GLOBALS['current_screen'] = null;

		$this->assertFalse( CR_Consent::should_show_banner() );
	}

	/**
	 * Test get_policy_page_url returns false when not set.
	 */
	public function test_get_policy_page_url_returns_false_when_not_set() {
		$url = CR_Consent::get_policy_page_url();

		$this->assertFalse( $url );
	}

	/**
	 * Test get_policy_page_url returns URL when set.
	 */
	public function test_get_policy_page_url_returns_url() {
		// Create a test page.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Cookie Policy',
				'post_status' => 'publish',
			)
		);

		CR_Consent::update_settings( array( 'policy_page_id' => $page_id ) );

		$url = CR_Consent::get_policy_page_url();

		$this->assertNotFalse( $url );
		$this->assertStringContainsString( 'cookie-policy', $url );
	}

	/**
	 * Test categories filter.
	 */
	public function test_categories_filter() {
		$categories = array(
			array(
				'id'          => 'test',
				'slug'        => 'test',
				'name'        => 'Test',
				'description' => 'Test',
				'essential'   => false,
			),
		);

		CR_Consent::update_categories( $categories );

		// Add filter.
		add_filter(
			'consent_raven_categories',
			function ( $cats ) {
				$cats[] = array(
					'id'          => 'filtered',
					'slug'        => 'filtered',
					'name'        => 'Filtered Category',
					'description' => 'Added via filter',
					'essential'   => false,
				);
				return $cats;
			}
		);

		$filtered_categories = CR_Consent::get_categories();

		$this->assertCount( 2, $filtered_categories );
		$this->assertEquals( 'filtered', $filtered_categories[1]['slug'] );
	}
}
