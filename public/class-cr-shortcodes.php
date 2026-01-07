<?php
/**
 * Shortcodes functionality for the plugin.
 *
 * Handles cookie policy table shortcode and Gutenberg block rendering.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */

/**
 * Shortcodes class for the plugin.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */
class CR_Shortcodes {

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
	 * Register shortcodes.
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'consent_raven_policy_table', array( $this, 'render_policy_table' ) );
	}

	/**
	 * Register Gutenberg block.
	 *
	 * @since 1.0.0
	 */
	public function register_block() {
		// Check if block registration function exists.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			CONSENT_RAVEN_PLUGIN_DIR . 'blocks/policy-table',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			$this->plugin_name . '-block-policy-table',
			CONSENT_RAVEN_PLUGIN_URL . 'assets/js/block-policy-table.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			$this->version,
			true
		);

		// Localize script with categories data for the block editor.
		wp_localize_script(
			$this->plugin_name . '-block-policy-table',
			'consentRavenAdmin',
			array(
				'categories' => CR_Consent::get_categories(),
			)
		);

		wp_enqueue_style(
			$this->plugin_name . '-block-editor',
			CONSENT_RAVEN_PLUGIN_URL . 'assets/css/block-editor.css',
			array( 'wp-edit-blocks' ),
			$this->version
		);
	}

	/**
	 * Render policy table shortcode.
	 *
	 * @since  1.0.0
	 * @param  array  $atts    Shortcode attributes.
	 * @param  string $content Shortcode content.
	 * @return string Rendered policy table.
	 */
	public function render_policy_table( $atts = array(), $content = '' ) {
		$atts = shortcode_atts(
			array(
				'show_category'   => 'true',
				'show_provider'   => 'true',
				'show_expiration' => 'true',
				'show_host'       => 'false',
				'category'        => '',
			),
			$atts,
			'consent_raven_policy_table'
		);

		// Convert string booleans to actual booleans.
		$show_category   = filter_var( $atts['show_category'], FILTER_VALIDATE_BOOLEAN );
		$show_provider   = filter_var( $atts['show_provider'], FILTER_VALIDATE_BOOLEAN );
		$show_expiration = filter_var( $atts['show_expiration'], FILTER_VALIDATE_BOOLEAN );
		$show_host       = filter_var( $atts['show_host'], FILTER_VALIDATE_BOOLEAN );
		$filter_category = sanitize_text_field( $atts['category'] );

		return $this->generate_policy_table_html(
			$show_category,
			$show_provider,
			$show_expiration,
			$show_host,
			$filter_category
		);
	}

	/**
	 * Render Gutenberg block.
	 *
	 * @since  1.0.0
	 * @param  array $attributes Block attributes.
	 * @return string Rendered block.
	 */
	public function render_block( $attributes ) {
		$show_category   = isset( $attributes['showCategory'] ) ? $attributes['showCategory'] : true;
		$show_provider   = isset( $attributes['showProvider'] ) ? $attributes['showProvider'] : true;
		$show_expiration = isset( $attributes['showExpiration'] ) ? $attributes['showExpiration'] : true;
		$show_host       = isset( $attributes['showHost'] ) ? $attributes['showHost'] : false;
		$filter_category = isset( $attributes['filterCategory'] ) ? $attributes['filterCategory'] : '';

		$block_wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'cr-policy-table-block' ) );

		return sprintf(
			'<div %s>%s</div>',
			$block_wrapper_attributes,
			$this->generate_policy_table_html(
				$show_category,
				$show_provider,
				$show_expiration,
				$show_host,
				$filter_category
			)
		);
	}

	/**
	 * Generate policy table HTML.
	 *
	 * @since  1.0.0
	 * @param  bool   $show_category   Whether to show category column.
	 * @param  bool   $show_provider   Whether to show provider column.
	 * @param  bool   $show_expiration Whether to show expiration column.
	 * @param  bool   $show_host       Whether to show host column.
	 * @param  string $filter_category Category to filter by.
	 * @return string Policy table HTML.
	 */
	private function generate_policy_table_html( $show_category, $show_provider, $show_expiration, $show_host, $filter_category ) {
		$cookies    = CR_Consent::get_cookies();
		$categories = CR_Consent::get_categories();

		// Build category lookup.
		$category_lookup = array();
		foreach ( $categories as $category ) {
			$category_lookup[ $category['id'] ] = $category['name'];
		}

		// Filter cookies by category if specified.
		if ( ! empty( $filter_category ) ) {
			$cookies = array_filter(
				$cookies,
				function ( $cookie ) use ( $filter_category ) {
					return isset( $cookie['category_id'] ) && $cookie['category_id'] === $filter_category;
				}
			);
		}

		/**
		 * Filter the cookies before rendering the policy table.
		 *
		 * @since 1.0.0
		 * @param array  $cookies         The cookies to display.
		 * @param string $filter_category The category filter applied.
		 */
		$cookies = apply_filters( 'consent_raven_policy_table_cookies', $cookies, $filter_category );

		if ( empty( $cookies ) ) {
			return sprintf(
				'<div class="cr-policy-table-empty"><p>%s</p></div>',
				esc_html__( 'No cookies have been configured yet.', 'consent-raven' )
			);
		}

		// Start building the table.
		$html = '<div class="cr-policy-table-wrapper">';
		$html .= '<table class="cr-policy-table">';
		$html .= '<thead><tr>';
		$html .= '<th class="cr-policy-table__cookie">' . esc_html__( 'Cookie', 'consent-raven' ) . '</th>';

		if ( $show_category ) {
			$html .= '<th class="cr-policy-table__category">' . esc_html__( 'Category', 'consent-raven' ) . '</th>';
		}

		if ( $show_provider ) {
			$html .= '<th class="cr-policy-table__provider">' . esc_html__( 'Provider', 'consent-raven' ) . '</th>';
		}

		$html .= '<th class="cr-policy-table__purpose">' . esc_html__( 'Purpose', 'consent-raven' ) . '</th>';

		if ( $show_expiration ) {
			$html .= '<th class="cr-policy-table__expiration">' . esc_html__( 'Expiration', 'consent-raven' ) . '</th>';
		}

		if ( $show_host ) {
			$html .= '<th class="cr-policy-table__host">' . esc_html__( 'Host', 'consent-raven' ) . '</th>';
		}

		$html .= '</tr></thead>';
		$html .= '<tbody>';

		foreach ( $cookies as $cookie ) {
			$html .= '<tr>';
			$html .= '<td class="cr-policy-table__cookie" data-label="' . esc_attr__( 'Cookie', 'consent-raven' ) . '">';
			$html .= '<code>' . esc_html( $cookie['name'] ) . '</code>';
			$html .= '</td>';

			if ( $show_category ) {
				$category_name = isset( $cookie['category_id'], $category_lookup[ $cookie['category_id'] ] )
					? $category_lookup[ $cookie['category_id'] ]
					: __( 'Unknown', 'consent-raven' );
				$html .= '<td class="cr-policy-table__category" data-label="' . esc_attr__( 'Category', 'consent-raven' ) . '">';
				$html .= esc_html( $category_name );
				$html .= '</td>';
			}

			if ( $show_provider ) {
				$html .= '<td class="cr-policy-table__provider" data-label="' . esc_attr__( 'Provider', 'consent-raven' ) . '">';
				$html .= esc_html( $cookie['provider'] ?? '' );
				$html .= '</td>';
			}

			$html .= '<td class="cr-policy-table__purpose" data-label="' . esc_attr__( 'Purpose', 'consent-raven' ) . '">';
			$html .= esc_html( $cookie['purpose'] ?? '' );
			$html .= '</td>';

			if ( $show_expiration ) {
				$html .= '<td class="cr-policy-table__expiration" data-label="' . esc_attr__( 'Expiration', 'consent-raven' ) . '">';
				$html .= esc_html( $cookie['expiration'] ?? '' );
				$html .= '</td>';
			}

			if ( $show_host ) {
				$html .= '<td class="cr-policy-table__host" data-label="' . esc_attr__( 'Host', 'consent-raven' ) . '">';
				$html .= esc_html( $cookie['host'] ?? '' );
				$html .= '</td>';
			}

			$html .= '</tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div>';

		/**
		 * Filter the policy table HTML.
		 *
		 * @since 1.0.0
		 * @param string $html    The generated HTML.
		 * @param array  $cookies The cookies displayed.
		 */
		return apply_filters( 'consent_raven_policy_table_html', $html, $cookies );
	}
}
