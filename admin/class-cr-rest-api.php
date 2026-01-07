<?php
/**
 * REST API functionality for the plugin.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/admin
 */

/**
 * REST API class for the plugin.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/admin
 */
class CR_Rest_API {

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
	 * REST API namespace.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $namespace The REST API namespace.
	 */
	private $namespace = 'consent-raven/v1';

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
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		// Settings endpoint.
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => $this->get_settings_args(),
				),
			)
		);

		// Categories endpoint.
		register_rest_route(
			$this->namespace,
			'/categories',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_categories' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_categories' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Cookies endpoint.
		register_rest_route(
			$this->namespace,
			'/cookies',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cookies' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_cookies' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Scripts endpoint.
		register_rest_route(
			$this->namespace,
			'/scripts',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_scripts' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_scripts' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Export endpoint.
		register_rest_route(
			$this->namespace,
			'/export',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'export_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Import endpoint.
		register_rest_route(
			$this->namespace,
			'/import',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'import_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Reset endpoint.
		register_rest_route(
			$this->namespace,
			'/reset',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'reset_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Check permissions for REST API requests.
	 *
	 * @since  1.0.0
	 * @return bool|WP_Error True if allowed, error otherwise.
	 */
	public function permissions_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this resource.', 'consent-raven' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Get settings.
	 *
	 * @since  1.0.0
	 * @return WP_REST_Response Settings response.
	 */
	public function get_settings() {
		return rest_ensure_response( CR_Consent::get_settings() );
	}

	/**
	 * Update settings.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_settings( $request ) {
		$settings = $request->get_json_params();

		// Validate settings.
		$validation = CR_Settings::validate( $settings );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		// Update settings.
		$result = CR_Consent::update_settings( $settings );

		if ( $result ) {
			return rest_ensure_response(
				array(
					'success'  => true,
					'message'  => __( 'Settings updated successfully.', 'consent-raven' ),
					'settings' => CR_Consent::get_settings(),
				)
			);
		}

		return new WP_Error(
			'update_failed',
			__( 'Failed to update settings.', 'consent-raven' ),
			array( 'status' => 500 )
		);
	}

	/**
	 * Get categories.
	 *
	 * @since  1.0.0
	 * @return WP_REST_Response Categories response.
	 */
	public function get_categories() {
		return rest_ensure_response( CR_Consent::get_categories() );
	}

	/**
	 * Update categories.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_categories( $request ) {
		$categories = $request->get_json_params();

		if ( ! is_array( $categories ) ) {
			return new WP_Error(
				'invalid_data',
				__( 'Invalid categories data.', 'consent-raven' ),
				array( 'status' => 400 )
			);
		}

		$result = CR_Consent::update_categories( $categories );

		if ( $result ) {
			return rest_ensure_response(
				array(
					'success'    => true,
					'message'    => __( 'Categories updated successfully.', 'consent-raven' ),
					'categories' => CR_Consent::get_categories(),
				)
			);
		}

		return new WP_Error(
			'update_failed',
			__( 'Failed to update categories.', 'consent-raven' ),
			array( 'status' => 500 )
		);
	}

	/**
	 * Get cookies.
	 *
	 * @since  1.0.0
	 * @return WP_REST_Response Cookies response.
	 */
	public function get_cookies() {
		return rest_ensure_response( CR_Consent::get_cookies() );
	}

	/**
	 * Update cookies.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_cookies( $request ) {
		$cookies = $request->get_json_params();

		if ( ! is_array( $cookies ) ) {
			return new WP_Error(
				'invalid_data',
				__( 'Invalid cookies data.', 'consent-raven' ),
				array( 'status' => 400 )
			);
		}

		$result = CR_Consent::update_cookies( $cookies );

		if ( $result ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'message' => __( 'Cookies updated successfully.', 'consent-raven' ),
					'cookies' => CR_Consent::get_cookies(),
				)
			);
		}

		return new WP_Error(
			'update_failed',
			__( 'Failed to update cookies.', 'consent-raven' ),
			array( 'status' => 500 )
		);
	}

	/**
	 * Get scripts.
	 *
	 * @since  1.0.0
	 * @return WP_REST_Response Scripts response.
	 */
	public function get_scripts() {
		return rest_ensure_response( CR_Consent::get_scripts() );
	}

	/**
	 * Update scripts.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_scripts( $request ) {
		$scripts = $request->get_json_params();

		if ( ! is_array( $scripts ) ) {
			return new WP_Error(
				'invalid_data',
				__( 'Invalid scripts data.', 'consent-raven' ),
				array( 'status' => 400 )
			);
		}

		$result = CR_Consent::update_scripts( $scripts );

		if ( $result ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'message' => __( 'Scripts updated successfully.', 'consent-raven' ),
					'scripts' => CR_Consent::get_scripts(),
				)
			);
		}

		return new WP_Error(
			'update_failed',
			__( 'Failed to update scripts.', 'consent-raven' ),
			array( 'status' => 500 )
		);
	}

	/**
	 * Export settings.
	 *
	 * @since  1.0.0
	 * @return WP_REST_Response Export response.
	 */
	public function export_settings() {
		$export = CR_Settings::export_settings();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $export,
			)
		);
	}

	/**
	 * Import settings.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function import_settings( $request ) {
		$data = $request->get_json_params();

		if ( empty( $data ) ) {
			return new WP_Error(
				'missing_data',
				__( 'No import data provided.', 'consent-raven' ),
				array( 'status' => 400 )
			);
		}

		$result = CR_Settings::import_settings( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Settings imported successfully.', 'consent-raven' ),
			)
		);
	}

	/**
	 * Reset settings to defaults.
	 *
	 * @since  1.1.0
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function reset_settings() {
		$result = CR_Settings::reset_to_defaults();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Settings reset to defaults.', 'consent-raven' ),
			)
		);
	}

	/**
	 * Get settings endpoint arguments.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return array Endpoint arguments.
	 */
	private function get_settings_args() {
		return array(
			'enabled'         => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			),
			'position'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'policy_page_id'  => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'consent_version' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'appearance'      => array(
				'type' => 'object',
			),
			'content'         => array(
				'type' => 'object',
			),
		);
	}
}
