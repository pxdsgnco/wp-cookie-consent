<?php
/**
 * Tests for CR_Rest_API class.
 *
 * @package Consent_Raven
 */

/**
 * Test case for REST API functionality.
 */
class Test_CR_Rest_API extends WP_UnitTestCase {

	/**
	 * REST API instance.
	 *
	 * @var CR_Rest_API
	 */
	private $rest_api;

	/**
	 * REST server instance.
	 *
	 * @var WP_REST_Server
	 */
	private $server;

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	private $admin_user;

	/**
	 * Subscriber user ID.
	 *
	 * @var int
	 */
	private $subscriber_user;

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		// Reset options.
		delete_option( 'consent_raven_settings' );
		delete_option( 'consent_raven_categories' );
		delete_option( 'consent_raven_cookies' );
		delete_option( 'consent_raven_scripts' );

		// Create REST API instance.
		$this->rest_api = new CR_Rest_API( 'consent-raven', '1.0.0' );

		// Initialize REST server.
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );

		// Register routes.
		$this->rest_api->register_routes();

		// Create test users.
		$this->admin_user = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);

		$this->subscriber_user = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);
	}

	/**
	 * Tear down after each test.
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tear_down();
	}

	/**
	 * Test permissions_check returns true for admin.
	 */
	public function test_permissions_check_allows_admin() {
		wp_set_current_user( $this->admin_user );

		$result = $this->rest_api->permissions_check();

		$this->assertTrue( $result );
	}

	/**
	 * Test permissions_check returns error for non-admin.
	 */
	public function test_permissions_check_denies_non_admin() {
		wp_set_current_user( $this->subscriber_user );

		$result = $this->rest_api->permissions_check();

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'rest_forbidden', $result->get_error_code() );
	}

	/**
	 * Test permissions_check returns error for logged out user.
	 */
	public function test_permissions_check_denies_logged_out() {
		wp_set_current_user( 0 );

		$result = $this->rest_api->permissions_check();

		$this->assertInstanceOf( 'WP_Error', $result );
	}

	/**
	 * Test get_settings returns default settings.
	 */
	public function test_get_settings_returns_defaults() {
		wp_set_current_user( $this->admin_user );

		$response = $this->rest_api->get_settings();

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'enabled', $data );
		$this->assertArrayHasKey( 'position', $data );
		$this->assertArrayHasKey( 'appearance', $data );
		$this->assertArrayHasKey( 'content', $data );
	}

	/**
	 * Test update_settings updates values.
	 */
	public function test_update_settings() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/settings' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'enabled'  => false,
					'position' => 'top-bar',
				)
			)
		);

		$response = $this->rest_api->update_settings( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );

		$settings = $data['settings'];
		$this->assertFalse( $settings['enabled'] );
		$this->assertEquals( 'top-bar', $settings['position'] );
	}

	/**
	 * Test get_categories returns array.
	 */
	public function test_get_categories() {
		wp_set_current_user( $this->admin_user );

		$response = $this->rest_api->get_categories();

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertIsArray( $response->get_data() );
	}

	/**
	 * Test update_categories updates values.
	 */
	public function test_update_categories() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/categories' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
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
			)
		);

		$response = $this->rest_api->update_categories( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertCount( 2, $data['categories'] );
	}

	/**
	 * Test update_categories returns error for invalid data.
	 */
	public function test_update_categories_invalid_data() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/categories' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( '"not an array"' );

		$response = $this->rest_api->update_categories( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'invalid_data', $response->get_error_code() );
	}

	/**
	 * Test get_cookies returns array.
	 */
	public function test_get_cookies() {
		wp_set_current_user( $this->admin_user );

		$response = $this->rest_api->get_cookies();

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertIsArray( $response->get_data() );
	}

	/**
	 * Test update_cookies updates values.
	 */
	public function test_update_cookies() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/cookies' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
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
			)
		);

		$response = $this->rest_api->update_cookies( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertCount( 1, $data['cookies'] );
	}

	/**
	 * Test get_scripts returns array.
	 */
	public function test_get_scripts() {
		wp_set_current_user( $this->admin_user );

		$response = $this->rest_api->get_scripts();

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertIsArray( $response->get_data() );
	}

	/**
	 * Test update_scripts updates values.
	 */
	public function test_update_scripts() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/scripts' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					array(
						'id'          => 'ga-script',
						'handle'      => 'google-analytics',
						'category_id' => 'analytics',
						'method'      => 'type-swap',
						'pattern'     => '',
					),
				)
			)
		);

		$response = $this->rest_api->update_scripts( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertCount( 1, $data['scripts'] );
	}

	/**
	 * Test export_settings returns all settings.
	 */
	public function test_export_settings() {
		wp_set_current_user( $this->admin_user );

		$response = $this->rest_api->export_settings();

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'settings', $data['data'] );
		$this->assertArrayHasKey( 'categories', $data['data'] );
		$this->assertArrayHasKey( 'cookies', $data['data'] );
		$this->assertArrayHasKey( 'scripts', $data['data'] );
	}

	/**
	 * Test import_settings imports data.
	 */
	public function test_import_settings() {
		wp_set_current_user( $this->admin_user );

		$import_data = array(
			'settings'   => array(
				'enabled'  => false,
				'position' => 'modal',
			),
			'categories' => array(
				array(
					'id'          => 'essential',
					'slug'        => 'essential',
					'name'        => 'Essential',
					'description' => 'Required',
					'essential'   => true,
				),
			),
			'cookies'    => array(),
			'scripts'    => array(),
		);

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/import' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $import_data ) );

		$response = $this->rest_api->import_settings( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
	}

	/**
	 * Test import_settings returns error for empty data.
	 */
	public function test_import_settings_empty_data() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/import' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( '{}' );

		// Empty object becomes empty array after JSON parsing.
		$response = $this->rest_api->import_settings( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'missing_data', $response->get_error_code() );
	}

	/**
	 * Test reset_settings resets to defaults.
	 */
	public function test_reset_settings() {
		wp_set_current_user( $this->admin_user );

		// First modify settings.
		CR_Consent::update_settings( array( 'enabled' => false, 'position' => 'modal' ) );

		$response = $this->rest_api->reset_settings();

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );

		// Check settings are reset.
		$settings = CR_Consent::get_settings();
		$this->assertTrue( $settings['enabled'] );
		$this->assertEquals( 'bottom-right', $settings['position'] );
	}

	/**
	 * Test create_policy_page creates a page.
	 */
	public function test_create_policy_page() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/create-policy-page' );
		$request->set_param( 'title', 'Cookie Policy' );
		$request->set_param( 'content', '<p>This is our cookie policy.</p>' );
		$request->set_param( 'set_as_policy_page', true );

		$response = $this->rest_api->create_policy_page( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'page_id', $data );
		$this->assertArrayHasKey( 'edit_url', $data );
		$this->assertArrayHasKey( 'view_url', $data );

		// Verify page was created.
		$page = get_post( $data['page_id'] );
		$this->assertNotNull( $page );
		$this->assertEquals( 'Cookie Policy', $page->post_title );
		$this->assertEquals( 'draft', $page->post_status );

		// Verify settings updated.
		$settings = CR_Consent::get_settings();
		$this->assertEquals( $data['page_id'], $settings['policy_page_id'] );
	}

	/**
	 * Test create_policy_page without setting as policy page.
	 */
	public function test_create_policy_page_without_linking() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'POST', '/consent-raven/v1/create-policy-page' );
		$request->set_param( 'title', 'Cookie Policy' );
		$request->set_param( 'content', '<p>Policy content.</p>' );
		$request->set_param( 'set_as_policy_page', false );

		$response = $this->rest_api->create_policy_page( $request );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );

		// Verify settings NOT updated.
		$settings = CR_Consent::get_settings();
		$this->assertNotEquals( $data['page_id'], $settings['policy_page_id'] );
	}

	/**
	 * Test REST routes are registered.
	 */
	public function test_routes_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/consent-raven/v1/settings', $routes );
		$this->assertArrayHasKey( '/consent-raven/v1/categories', $routes );
		$this->assertArrayHasKey( '/consent-raven/v1/cookies', $routes );
		$this->assertArrayHasKey( '/consent-raven/v1/scripts', $routes );
		$this->assertArrayHasKey( '/consent-raven/v1/export', $routes );
		$this->assertArrayHasKey( '/consent-raven/v1/import', $routes );
		$this->assertArrayHasKey( '/consent-raven/v1/reset', $routes );
		$this->assertArrayHasKey( '/consent-raven/v1/create-policy-page', $routes );
	}
}
