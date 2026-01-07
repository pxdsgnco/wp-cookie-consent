<?php
/**
 * Tests for CR_Script_Blocker class.
 *
 * @package Consent_Raven
 */

/**
 * Test case for script blocking functionality.
 */
class Test_CR_Script_Blocker extends WP_UnitTestCase {

	/**
	 * Script blocker instance.
	 *
	 * @var CR_Script_Blocker
	 */
	private $script_blocker;

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		// Reset options before each test.
		delete_option( 'consent_raven_settings' );
		delete_option( 'consent_raven_scripts' );

		// Create script blocker instance.
		$this->script_blocker = new CR_Script_Blocker( 'consent-raven', '1.0.0' );
	}

	/**
	 * Test process_buffer returns empty string when buffer is empty.
	 */
	public function test_process_buffer_returns_empty_for_empty_buffer() {
		$result = $this->script_blocker->process_buffer( '' );

		$this->assertEquals( '', $result );
	}

	/**
	 * Test process_buffer returns unchanged buffer when no scripts registered.
	 */
	public function test_process_buffer_unchanged_when_no_scripts() {
		$buffer = '<html><body><script>console.log("test");</script></body></html>';

		$result = $this->script_blocker->process_buffer( $buffer );

		$this->assertEquals( $buffer, $result );
	}

	/**
	 * Test process_buffer returns unchanged buffer when no script tags present.
	 */
	public function test_process_buffer_unchanged_when_no_script_tags() {
		// Register a script.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'test-script',
					'category_id' => 'analytics',
					'method'      => 'inline',
					'pattern'     => 'googleAnalytics',
				),
			)
		);

		$buffer = '<html><body><p>No scripts here</p></body></html>';

		$result = $this->script_blocker->process_buffer( $buffer );

		$this->assertEquals( $buffer, $result );
	}

	/**
	 * Test process_buffer blocks inline scripts matching pattern.
	 */
	public function test_process_buffer_blocks_matching_inline_scripts() {
		// Register a script.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'ga-script',
					'category_id' => 'analytics',
					'method'      => 'inline',
					'pattern'     => 'ga\(',
				),
			)
		);

		$buffer = '<html><body><script>ga("create", "UA-XXXXX-Y");</script></body></html>';

		$result = $this->script_blocker->process_buffer( $buffer );

		$this->assertStringContainsString( 'type="text/plain"', $result );
		$this->assertStringContainsString( 'data-cookie-category="analytics"', $result );
	}

	/**
	 * Test process_buffer skips already blocked scripts.
	 */
	public function test_process_buffer_skips_already_blocked_scripts() {
		// Register a script.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'ga-script',
					'category_id' => 'analytics',
					'method'      => 'inline',
					'pattern'     => 'ga\(',
				),
			)
		);

		$buffer = '<html><body><script data-cookie-category="analytics">ga("create");</script></body></html>';

		$result = $this->script_blocker->process_buffer( $buffer );

		// Should not double-add attributes.
		$this->assertEquals( substr_count( $result, 'data-cookie-category' ), 1 );
	}

	/**
	 * Test process_buffer skips scripts with type=text/plain.
	 */
	public function test_process_buffer_skips_text_plain_scripts() {
		// Register a script.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'ga-script',
					'category_id' => 'analytics',
					'method'      => 'inline',
					'pattern'     => 'ga\(',
				),
			)
		);

		$buffer = '<html><body><script type="text/plain">ga("create");</script></body></html>';

		$result = $this->script_blocker->process_buffer( $buffer );

		// Should not modify already text/plain scripts.
		$this->assertStringNotContainsString( 'data-cookie-category', $result );
	}

	/**
	 * Test maybe_block_script returns unchanged tag when no match.
	 */
	public function test_maybe_block_script_unchanged_when_no_match() {
		$tag    = '<script src="https://example.com/script.js"></script>';
		$handle = 'example-script';
		$src    = 'https://example.com/script.js';

		$result = $this->script_blocker->maybe_block_script( $tag, $handle, $src );

		$this->assertEquals( $tag, $result );
	}

	/**
	 * Test maybe_block_script blocks by handle match.
	 */
	public function test_maybe_block_script_blocks_by_handle() {
		// Register a script by handle.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'google-analytics',
					'category_id' => 'analytics',
					'method'      => 'type-swap',
					'handle'      => 'google-analytics',
					'pattern'     => '',
				),
			)
		);

		// Recreate blocker to pick up new scripts.
		$this->script_blocker = new CR_Script_Blocker( 'consent-raven', '1.0.0' );

		$tag    = '<script src="https://www.google-analytics.com/analytics.js"></script>';
		$handle = 'google-analytics';
		$src    = 'https://www.google-analytics.com/analytics.js';

		$result = $this->script_blocker->maybe_block_script( $tag, $handle, $src );

		$this->assertStringContainsString( 'type="text/plain"', $result );
		$this->assertStringContainsString( 'data-cookie-category="analytics"', $result );
	}

	/**
	 * Test maybe_block_script blocks by URL pattern match.
	 */
	public function test_maybe_block_script_blocks_by_pattern() {
		// Register a script by pattern.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'fbpixel',
					'category_id' => 'marketing',
					'method'      => 'type-swap',
					'handle'      => '',
					'pattern'     => 'connect\.facebook\.net',
				),
			)
		);

		// Recreate blocker to pick up new scripts.
		$this->script_blocker = new CR_Script_Blocker( 'consent-raven', '1.0.0' );

		$tag    = '<script src="https://connect.facebook.net/en_US/fbevents.js"></script>';
		$handle = 'fb-pixel';
		$src    = 'https://connect.facebook.net/en_US/fbevents.js';

		$result = $this->script_blocker->maybe_block_script( $tag, $handle, $src );

		$this->assertStringContainsString( 'type="text/plain"', $result );
		$this->assertStringContainsString( 'data-cookie-category="marketing"', $result );
	}

	/**
	 * Test maybe_block_script applies data-attribute method.
	 */
	public function test_maybe_block_script_applies_data_attribute_method() {
		// Register a script with data-attribute method.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'test-script',
					'category_id' => 'analytics',
					'method'      => 'data-attribute',
					'handle'      => 'test-script',
					'pattern'     => '',
				),
			)
		);

		// Recreate blocker to pick up new scripts.
		$this->script_blocker = new CR_Script_Blocker( 'consent-raven', '1.0.0' );

		$tag    = '<script src="https://example.com/test.js"></script>';
		$handle = 'test-script';
		$src    = 'https://example.com/test.js';

		$result = $this->script_blocker->maybe_block_script( $tag, $handle, $src );

		$this->assertStringContainsString( 'data-cookie-category="analytics"', $result );
		$this->assertStringContainsString( 'data-cookie-consent="pending"', $result );
	}

	/**
	 * Test filter consent_raven_should_block_script.
	 */
	public function test_should_block_script_filter() {
		// Register a script.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'test-script',
					'category_id' => 'analytics',
					'method'      => 'type-swap',
					'handle'      => 'test-script',
					'pattern'     => '',
				),
			)
		);

		// Recreate blocker to pick up new scripts.
		$this->script_blocker = new CR_Script_Blocker( 'consent-raven', '1.0.0' );

		// Add filter to prevent blocking.
		add_filter(
			'consent_raven_should_block_script',
			function ( $should_block, $handle, $category_id ) {
				if ( 'test-script' === $handle ) {
					return false;
				}
				return $should_block;
			},
			10,
			3
		);

		$tag    = '<script src="https://example.com/test.js"></script>';
		$handle = 'test-script';
		$src    = 'https://example.com/test.js';

		$result = $this->script_blocker->maybe_block_script( $tag, $handle, $src );

		// Should not be blocked due to filter.
		$this->assertStringNotContainsString( 'type="text/plain"', $result );
	}

	/**
	 * Test register_script static method.
	 */
	public function test_register_script_static_method() {
		$result = CR_Script_Blocker::register_script(
			array(
				'handle'      => 'my-script',
				'category_id' => 'analytics',
				'method'      => 'type-swap',
			)
		);

		$this->assertTrue( $result );

		$scripts = CR_Consent::get_scripts();
		$found   = false;
		foreach ( $scripts as $script ) {
			if ( 'my-script' === $script['handle'] ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found );
	}

	/**
	 * Test register_script fails without category_id.
	 */
	public function test_register_script_fails_without_category() {
		$result = CR_Script_Blocker::register_script(
			array(
				'handle' => 'my-script',
				'method' => 'type-swap',
			)
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test register_script fails without handle or pattern.
	 */
	public function test_register_script_fails_without_handle_or_pattern() {
		$result = CR_Script_Blocker::register_script(
			array(
				'category_id' => 'analytics',
				'method'      => 'type-swap',
			)
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test unregister_script static method.
	 */
	public function test_unregister_script_static_method() {
		// First register a script.
		CR_Consent::update_scripts(
			array(
				array(
					'id'          => 'to-remove',
					'handle'      => 'my-script',
					'category_id' => 'analytics',
					'method'      => 'type-swap',
				),
			)
		);

		$result = CR_Script_Blocker::unregister_script( 'to-remove' );

		$this->assertTrue( $result );

		$scripts = CR_Consent::get_scripts();
		$found   = false;
		foreach ( $scripts as $script ) {
			if ( 'to-remove' === $script['id'] ) {
				$found = true;
				break;
			}
		}

		$this->assertFalse( $found );
	}

	/**
	 * Test get_activation_script returns valid JavaScript.
	 */
	public function test_get_activation_script_returns_javascript() {
		$script = CR_Script_Blocker::get_activation_script();

		$this->assertStringContainsString( 'consentRavenActivateScripts', $script );
		$this->assertStringContainsString( 'function', $script );
		$this->assertStringContainsString( 'categories', $script );
	}

	/**
	 * Test output_early_script outputs script tag.
	 */
	public function test_output_early_script() {
		// Enable banner.
		CR_Consent::update_settings( array( 'enabled' => true ) );

		// Capture output.
		ob_start();
		$this->script_blocker->output_early_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<script id="consent-raven-early">', $output );
		$this->assertStringContainsString( 'getConsentCookie', $output );
		$this->assertStringContainsString( 'consentRavenConsent', $output );
	}

	/**
	 * Test buffer management methods.
	 */
	public function test_buffer_active_state() {
		// Enable banner.
		CR_Consent::update_settings( array( 'enabled' => true ) );

		// Start should work.
		$this->script_blocker->start_buffer();

		// Calling start again should not start another buffer.
		$this->script_blocker->start_buffer();

		// End buffer.
		$this->script_blocker->end_buffer();
	}
}
