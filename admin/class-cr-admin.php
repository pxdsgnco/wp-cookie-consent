<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/admin
 */
class CR_Admin {

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_styles( $hook_suffix ) {
		// Only load on our plugin pages.
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name . '-admin',
			CONSENT_RAVEN_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		// Only load on our plugin pages.
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		// Enqueue React and ReactDOM from WordPress.
		wp_enqueue_script( 'wp-element' );
		wp_enqueue_script( 'wp-components' );
		wp_enqueue_style( 'wp-components' );

		// Enqueue our admin React app.
		wp_enqueue_script(
			$this->plugin_name . '-admin',
			CONSENT_RAVEN_PLUGIN_URL . 'assets/js/admin.js',
			array( 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ),
			$this->version,
			true
		);

		// Localize script with data.
		wp_localize_script(
			$this->plugin_name . '-admin',
			'consentRavenAdmin',
			array(
				'apiUrl'     => rest_url( 'consent-raven/v1/' ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'settings'   => CR_Consent::get_settings(),
				'categories' => CR_Consent::get_categories(),
				'cookies'    => CR_Consent::get_cookies(),
				'scripts'    => CR_Consent::get_scripts(),
				'pages'      => $this->get_pages_list(),
				'i18n'       => array(
					'save'           => __( 'Save Changes', 'consent-raven' ),
					'saving'         => __( 'Saving...', 'consent-raven' ),
					'saved'          => __( 'Settings saved!', 'consent-raven' ),
					'error'          => __( 'Error saving settings', 'consent-raven' ),
					'general'        => __( 'General', 'consent-raven' ),
					'appearance'     => __( 'Appearance', 'consent-raven' ),
					'content'        => __( 'Content', 'consent-raven' ),
					'categories'     => __( 'Categories', 'consent-raven' ),
					'cookies'        => __( 'Cookies', 'consent-raven' ),
					'scripts'        => __( 'Scripts', 'consent-raven' ),
					'preview'        => __( 'Preview', 'consent-raven' ),
				),
			)
		);

		// Set translations for JavaScript.
		wp_set_script_translations( $this->plugin_name . '-admin', 'consent-raven' );
	}

	/**
	 * Add admin menu pages.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		// Main menu page.
		add_menu_page(
			__( 'Consent Raven', 'consent-raven' ),
			__( 'Consent Raven', 'consent-raven' ),
			'manage_options',
			'consent-raven',
			array( $this, 'render_admin_page' ),
			'dashicons-shield',
			80
		);

		// Settings submenu (same as main).
		add_submenu_page(
			'consent-raven',
			__( 'Settings', 'consent-raven' ),
			__( 'Settings', 'consent-raven' ),
			'manage_options',
			'consent-raven',
			array( $this, 'render_admin_page' )
		);

		// Cookie Categories submenu.
		add_submenu_page(
			'consent-raven',
			__( 'Cookie Categories', 'consent-raven' ),
			__( 'Categories', 'consent-raven' ),
			'manage_options',
			'consent-raven-categories',
			array( $this, 'render_admin_page' )
		);

		// Cookie Definitions submenu.
		add_submenu_page(
			'consent-raven',
			__( 'Cookie Definitions', 'consent-raven' ),
			__( 'Cookies', 'consent-raven' ),
			'manage_options',
			'consent-raven-cookies',
			array( $this, 'render_admin_page' )
		);

		// Script Blocking submenu.
		add_submenu_page(
			'consent-raven',
			__( 'Script Blocking', 'consent-raven' ),
			__( 'Scripts', 'consent-raven' ),
			'manage_options',
			'consent-raven-scripts',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		// Get current page/tab.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'consent-raven';
		$tab  = str_replace( 'consent-raven-', '', $page );
		$tab  = 'consent-raven' === $page ? 'settings' : $tab;

		echo '<div id="consent-raven-admin" data-tab="' . esc_attr( $tab ) . '"></div>';
	}

	/**
	 * Add plugin action links.
	 *
	 * @since  1.0.0
	 * @param  array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=consent-raven' ) . '">' . __( 'Settings', 'consent-raven' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Check if current page is a plugin page.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $hook_suffix The current admin page hook suffix.
	 * @return bool Whether current page is a plugin page.
	 */
	private function is_plugin_page( $hook_suffix ) {
		$plugin_pages = array(
			'toplevel_page_consent-raven',
			'consent-raven_page_consent-raven-categories',
			'consent-raven_page_consent-raven-cookies',
			'consent-raven_page_consent-raven-scripts',
		);

		return in_array( $hook_suffix, $plugin_pages, true );
	}

	/**
	 * Get list of pages for dropdown.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return array List of pages.
	 */
	private function get_pages_list() {
		$pages = get_pages( array(
			'post_status' => 'publish',
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
		) );

		$pages_list = array(
			array(
				'value' => 0,
				'label' => __( '— Select Page —', 'consent-raven' ),
			),
		);

		foreach ( $pages as $page ) {
			$pages_list[] = array(
				'value' => $page->ID,
				'label' => $page->post_title,
			);
		}

		return $pages_list;
	}
}
