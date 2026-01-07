<?php
/**
 * Consent Log functionality for the plugin.
 *
 * Handles logging of user consent decisions for compliance records.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.2.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */

/**
 * Consent Log class for the plugin.
 *
 * @since      1.2.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */
class CR_Consent_Log {

	/**
	 * Table name without prefix.
	 *
	 * @since  1.2.0
	 * @access public
	 * @var    string
	 */
	const TABLE_NAME = 'consent_raven_logs';

	/**
	 * Get the full table name with WordPress prefix.
	 *
	 * @since  1.2.0
	 * @return string Full table name.
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Create the consent logs database table.
	 *
	 * @since  1.2.0
	 * @return bool True if table was created, false otherwise.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			ip_hash VARCHAR(64) NOT NULL,
			user_agent_hash VARCHAR(64) NOT NULL,
			consent_action VARCHAR(20) NOT NULL,
			categories TEXT NOT NULL,
			consent_version VARCHAR(20) NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			INDEX idx_created_at (created_at),
			INDEX idx_ip_hash (ip_hash),
			INDEX idx_consent_action (consent_action)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return true;
	}

	/**
	 * Drop the consent logs database table.
	 *
	 * @since  1.2.0
	 * @return bool True if table was dropped.
	 */
	public static function drop_table() {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

		return true;
	}

	/**
	 * Hash an IP address using SHA-256 with WordPress salt.
	 *
	 * This creates an irreversible hash for GDPR compliance.
	 *
	 * @since  1.2.0
	 * @param  string $ip The IP address to hash.
	 * @return string The hashed IP address.
	 */
	public static function hash_ip( $ip ) {
		$salt = wp_salt( 'auth' );
		return hash( 'sha256', $ip . $salt );
	}

	/**
	 * Hash a User-Agent string using SHA-256.
	 *
	 * @since  1.2.0
	 * @param  string $user_agent The User-Agent string to hash.
	 * @return string The hashed User-Agent.
	 */
	public static function hash_user_agent( $user_agent ) {
		$salt = wp_salt( 'auth' );
		return hash( 'sha256', $user_agent . $salt );
	}

	/**
	 * Log a consent record.
	 *
	 * @since  1.2.0
	 * @param  array $data {
	 *     Consent data to log.
	 *
	 *     @type string $ip              The visitor's IP address.
	 *     @type string $user_agent      The visitor's User-Agent.
	 *     @type string $action          The consent action (accept_all, reject_all, custom).
	 *     @type array  $categories      The consent categories.
	 *     @type string $consent_version The consent version.
	 * }
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public static function log_consent( $data ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// Validate consent action.
		$valid_actions = array( 'accept_all', 'reject_all', 'custom' );
		$action        = isset( $data['action'] ) ? sanitize_text_field( $data['action'] ) : '';

		if ( ! in_array( $action, $valid_actions, true ) ) {
			return false;
		}

		$result = $wpdb->insert(
			$table_name,
			array(
				'ip_hash'         => self::hash_ip( isset( $data['ip'] ) ? $data['ip'] : '' ),
				'user_agent_hash' => self::hash_user_agent( isset( $data['user_agent'] ) ? $data['user_agent'] : '' ),
				'consent_action'  => $action,
				'categories'      => wp_json_encode( isset( $data['categories'] ) ? $data['categories'] : array() ),
				'consent_version' => sanitize_text_field( isset( $data['consent_version'] ) ? $data['consent_version'] : '1.0' ),
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $result;
	}

	/**
	 * Get consent logs with pagination and filtering.
	 *
	 * @since  1.2.0
	 * @param  array $args {
	 *     Optional. Arguments for retrieving logs.
	 *
	 *     @type int    $per_page  Number of logs per page. Default 20.
	 *     @type int    $page      Current page number. Default 1.
	 *     @type string $orderby   Column to order by. Default 'created_at'.
	 *     @type string $order     Order direction (ASC or DESC). Default 'DESC'.
	 *     @type string $action    Filter by consent action.
	 *     @type string $date_from Filter by date from (Y-m-d format).
	 *     @type string $date_to   Filter by date to (Y-m-d format).
	 * }
	 * @return array Array of log objects.
	 */
	public static function get_logs( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'per_page'  => 20,
			'page'      => 1,
			'orderby'   => 'created_at',
			'order'     => 'DESC',
			'action'    => '',
			'date_from' => '',
			'date_to'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$table_name = self::get_table_name();
		$where      = array();
		$values     = array();

		// Filter by action.
		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'consent_action = %s';
			$values[] = sanitize_text_field( $args['action'] );
		}

		// Filter by date from.
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}

		// Filter by date to.
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		$where_clause = '';
		if ( ! empty( $where ) ) {
			$where_clause = 'WHERE ' . implode( ' AND ', $where );
		}

		// Sanitize orderby and order.
		$valid_orderby = array( 'id', 'consent_action', 'consent_version', 'created_at' );
		$orderby       = in_array( $args['orderby'], $valid_orderby, true ) ? $args['orderby'] : 'created_at';
		$order         = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		// Calculate offset.
		$per_page = absint( $args['per_page'] );
		$page     = absint( $args['page'] );
		$offset   = ( $page - 1 ) * $per_page;

		// Build query.
		$sql = "SELECT * FROM {$table_name} {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

		$values[] = $per_page;
		$values[] = $offset;

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared = $wpdb->prepare( $sql, $values );
		} else {
			$prepared = $sql;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $prepared );
	}

	/**
	 * Get total count of consent logs.
	 *
	 * @since  1.2.0
	 * @param  array $args Optional. Same filters as get_logs().
	 * @return int Total count of logs.
	 */
	public static function get_logs_count( $args = array() ) {
		global $wpdb;

		$table_name = self::get_table_name();
		$where      = array();
		$values     = array();

		// Filter by action.
		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'consent_action = %s';
			$values[] = sanitize_text_field( $args['action'] );
		}

		// Filter by date from.
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}

		// Filter by date to.
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		$where_clause = '';
		if ( ! empty( $where ) ) {
			$where_clause = 'WHERE ' . implode( ' AND ', $where );
		}

		$sql = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared = $wpdb->prepare( $sql, $values );
		} else {
			$prepared = $sql;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $prepared );
	}

	/**
	 * Get all logs for CSV export.
	 *
	 * @since  1.2.0
	 * @param  array $args Optional. Same filters as get_logs() but no pagination.
	 * @return array Array of log objects.
	 */
	public static function get_logs_for_export( $args = array() ) {
		global $wpdb;

		$table_name = self::get_table_name();
		$where      = array();
		$values     = array();

		// Filter by action.
		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'consent_action = %s';
			$values[] = sanitize_text_field( $args['action'] );
		}

		// Filter by date from.
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}

		// Filter by date to.
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		$where_clause = '';
		if ( ! empty( $where ) ) {
			$where_clause = 'WHERE ' . implode( ' AND ', $where );
		}

		$sql = "SELECT * FROM {$table_name} {$where_clause} ORDER BY created_at DESC";

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared = $wpdb->prepare( $sql, $values );
		} else {
			$prepared = $sql;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $prepared );
	}

	/**
	 * Get consent statistics.
	 *
	 * @since  1.2.0
	 * @return array Statistics array with total, accept_all, reject_all, custom counts.
	 */
	public static function get_stats() {
		global $wpdb;

		$table_name = self::get_table_name();

		// Get total count.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

		// Get counts by action.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$accept_all = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE consent_action = %s",
				'accept_all'
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$reject_all = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE consent_action = %s",
				'reject_all'
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$custom = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE consent_action = %s",
				'custom'
			)
		);

		return array(
			'total'      => $total,
			'accept_all' => $accept_all,
			'reject_all' => $reject_all,
			'custom'     => $custom,
		);
	}

	/**
	 * Delete logs older than the retention period.
	 *
	 * @since  1.2.0
	 * @param  int|null $days Number of days to retain logs. If null, reads from settings.
	 * @return int Number of deleted rows.
	 */
	public static function cleanup_old_logs( $days = null ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// If days not provided, get from settings (months * 30).
		if ( null === $days ) {
			$settings = get_option( 'consent_raven_settings', array() );
			$months   = isset( $settings['log_retention_months'] ) ? (int) $settings['log_retention_months'] : 12;
			$days     = $months * 30;
		}

		$days = absint( $days );

		if ( $days < 1 ) {
			$days = 365;
		}

		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE created_at < %s",
				$cutoff_date
			)
		);

		return $deleted;
	}

	/**
	 * Clear all consent logs.
	 *
	 * @since  1.2.0
	 * @return int|false Number of deleted rows, or false on error.
	 */
	public static function clear_all_logs() {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( "TRUNCATE TABLE {$table_name}" );
	}

	/**
	 * Check if the consent logs table exists.
	 *
	 * @since  1.2.0
	 * @return bool True if table exists.
	 */
	public static function table_exists() {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;
	}
}
