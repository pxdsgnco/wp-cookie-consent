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

		// Create policy page endpoint.
		register_rest_route(
			$this->namespace,
			'/create-policy-page',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_policy_page' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'title'              => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'content'            => array(
							'type'     => 'string',
							'required' => true,
						),
						'set_as_policy_page' => array(
							'type'              => 'boolean',
							'default'           => true,
							'sanitize_callback' => 'rest_sanitize_boolean',
						),
					),
				),
			)
		);

		// Log consent endpoint (public - no auth required).
		register_rest_route(
			$this->namespace,
			'/log-consent',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'log_consent' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'action'          => array(
							'type'              => 'string',
							'required'          => true,
							'enum'              => array( 'accept_all', 'reject_all', 'custom' ),
							'sanitize_callback' => 'sanitize_text_field',
						),
						'categories'      => array(
							'type'     => 'object',
							'required' => true,
						),
						'consent_version' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);

		// Get consent logs endpoint (admin only).
		register_rest_route(
			$this->namespace,
			'/consent-logs',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_consent_logs' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'page'      => array(
							'type'              => 'integer',
							'default'           => 1,
							'sanitize_callback' => 'absint',
						),
						'per_page'  => array(
							'type'              => 'integer',
							'default'           => 20,
							'sanitize_callback' => 'absint',
						),
						'action'    => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'date_from' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'date_to'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'clear_consent_logs' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Export consent logs endpoint (admin only).
		register_rest_route(
			$this->namespace,
			'/consent-logs/export',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'export_consent_logs' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'action'    => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'date_from' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'date_to'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);

		// Consent logs stats endpoint (admin only).
		register_rest_route(
			$this->namespace,
			'/consent-logs/stats',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_consent_stats' ),
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
		// Note: update_option returns false when value is unchanged, which is not an error.
		CR_Consent::update_settings( $settings );

		return rest_ensure_response(
			array(
				'success'  => true,
				'message'  => __( 'Settings updated successfully.', 'consent-raven' ),
				'settings' => CR_Consent::get_settings(),
			)
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

		// Note: update_option returns false when value is unchanged, which is not an error.
		CR_Consent::update_categories( $categories );

		return rest_ensure_response(
			array(
				'success'    => true,
				'message'    => __( 'Categories updated successfully.', 'consent-raven' ),
				'categories' => CR_Consent::get_categories(),
			)
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

		// Note: update_option returns false when value is unchanged, which is not an error.
		CR_Consent::update_cookies( $cookies );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Cookies updated successfully.', 'consent-raven' ),
				'cookies' => CR_Consent::get_cookies(),
			)
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

		// Note: update_option returns false when value is unchanged, which is not an error.
		CR_Consent::update_scripts( $scripts );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Scripts updated successfully.', 'consent-raven' ),
				'scripts' => CR_Consent::get_scripts(),
			)
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
	 * Create a cookie policy page.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function create_policy_page( $request ) {
		$title              = $request->get_param( 'title' );
		$content            = $request->get_param( 'content' );
		$set_as_policy_page = $request->get_param( 'set_as_policy_page' );

		// Create the page.
		$page_data = array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'draft',
			'post_type'    => 'page',
		);

		$page_id = wp_insert_post( $page_data, true );

		if ( is_wp_error( $page_id ) ) {
			return new WP_Error(
				'page_creation_failed',
				$page_id->get_error_message(),
				array( 'status' => 500 )
			);
		}

		// Set as policy page in settings if requested.
		if ( $set_as_policy_page ) {
			$settings                  = CR_Consent::get_settings();
			$settings['policy_page_id'] = $page_id;
			CR_Consent::update_settings( $settings );
		}

		return rest_ensure_response(
			array(
				'success'  => true,
				'message'  => __( 'Cookie policy page created successfully.', 'consent-raven' ),
				'page_id'  => $page_id,
				'edit_url' => admin_url( 'post.php?post=' . $page_id . '&action=edit' ),
				'view_url' => get_permalink( $page_id ),
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

	/**
	 * Log consent from frontend (public endpoint).
	 *
	 * @since  1.2.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function log_consent( $request ) {
		// Get visitor IP address.
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// Rate limiting - 1 request per 5 seconds per IP.
		$ip_hash        = CR_Consent_Log::hash_ip( $ip );
		$rate_limit_key = 'cr_consent_rate_' . substr( $ip_hash, 0, 32 );

		if ( get_transient( $rate_limit_key ) ) {
			return new WP_Error(
				'rate_limited',
				__( 'Too many requests. Please wait.', 'consent-raven' ),
				array( 'status' => 429 )
			);
		}

		// Set rate limit transient.
		set_transient( $rate_limit_key, true, 5 );

		// Get user agent.
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
			: '';

		// Log the consent.
		$result = CR_Consent_Log::log_consent(
			array(
				'ip'              => $ip,
				'user_agent'      => $user_agent,
				'action'          => $request->get_param( 'action' ),
				'categories'      => $request->get_param( 'categories' ),
				'consent_version' => $request->get_param( 'consent_version' ),
			)
		);

		if ( $result ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'message' => __( 'Consent logged successfully.', 'consent-raven' ),
				)
			);
		}

		return new WP_Error(
			'log_failed',
			__( 'Failed to log consent.', 'consent-raven' ),
			array( 'status' => 500 )
		);
	}

	/**
	 * Get consent logs with pagination.
	 *
	 * @since  1.2.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response Response with logs.
	 */
	public function get_consent_logs( $request ) {
		$args = array(
			'page'      => $request->get_param( 'page' ),
			'per_page'  => $request->get_param( 'per_page' ),
			'action'    => $request->get_param( 'action' ),
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
		);

		$logs  = CR_Consent_Log::get_logs( $args );
		$total = CR_Consent_Log::get_logs_count( $args );

		$per_page    = absint( $args['per_page'] ) ?: 20;
		$total_pages = ceil( $total / $per_page );

		return rest_ensure_response(
			array(
				'success'     => true,
				'logs'        => $logs,
				'total'       => $total,
				'total_pages' => $total_pages,
				'page'        => absint( $args['page'] ) ?: 1,
			)
		);
	}

	/**
	 * Export consent logs as CSV data.
	 *
	 * @since  1.2.0
	 * @param  WP_REST_Request $request The request object.
	 * @return WP_REST_Response Response with CSV data.
	 */
	public function export_consent_logs( $request ) {
		$args = array(
			'action'    => $request->get_param( 'action' ),
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
		);

		$logs = CR_Consent_Log::get_logs_for_export( $args );

		// Build CSV data array.
		$csv_data = array();

		// Header row.
		$csv_data[] = array(
			__( 'ID', 'consent-raven' ),
			__( 'IP Hash (Anonymized)', 'consent-raven' ),
			__( 'Consent Action', 'consent-raven' ),
			__( 'Categories', 'consent-raven' ),
			__( 'Consent Version', 'consent-raven' ),
			__( 'Date/Time', 'consent-raven' ),
		);

		// Data rows.
		foreach ( $logs as $log ) {
			$csv_data[] = array(
				$log->id,
				$log->ip_hash,
				$log->consent_action,
				$log->categories,
				$log->consent_version,
				$log->created_at,
			);
		}

		return rest_ensure_response(
			array(
				'success'  => true,
				'data'     => $csv_data,
				'filename' => 'consent-logs-' . gmdate( 'Y-m-d' ) . '.csv',
			)
		);
	}

	/**
	 * Get consent statistics.
	 *
	 * @since  1.2.0
	 * @return WP_REST_Response Response with stats.
	 */
	public function get_consent_stats() {
		$stats = CR_Consent_Log::get_stats();

		return rest_ensure_response(
			array(
				'success' => true,
				'stats'   => $stats,
			)
		);
	}

	/**
	 * Clear all consent logs.
	 *
	 * @since  1.2.0
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function clear_consent_logs() {
		$result = CR_Consent_Log::clear_all_logs();

		if ( false !== $result ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'message' => __( 'All consent logs cleared.', 'consent-raven' ),
				)
			);
		}

		return new WP_Error(
			'clear_failed',
			__( 'Failed to clear consent logs.', 'consent-raven' ),
			array( 'status' => 500 )
		);
	}
}
