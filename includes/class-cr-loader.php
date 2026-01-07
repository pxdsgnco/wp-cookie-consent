<?php
/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/includes
 */
class CR_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array $actions The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array $filters The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'consent-raven';
		$this->version     = CONSENT_RAVEN_VERSION;
		$this->actions     = array();
		$this->filters     = array();

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		// Dependencies are loaded in the main plugin file.
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new CR_i18n();
		$this->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin    = new CR_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_settings = new CR_Settings( $this->get_plugin_name(), $this->get_version() );
		$plugin_rest_api = new CR_Rest_API( $this->get_plugin_name(), $this->get_version() );

		// Admin scripts and styles.
		$this->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Admin menu.
		$this->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );

		// REST API.
		$this->add_action( 'rest_api_init', $plugin_rest_api, 'register_routes' );

		// Plugin action links.
		$this->add_filter(
			'plugin_action_links_' . CONSENT_RAVEN_PLUGIN_BASENAME,
			$plugin_admin,
			'add_action_links'
		);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public         = new CR_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_banner         = new CR_Banner( $this->get_plugin_name(), $this->get_version() );
		$plugin_script_blocker = new CR_Script_Blocker( $this->get_plugin_name(), $this->get_version() );
		$plugin_shortcodes     = new CR_Shortcodes( $this->get_plugin_name(), $this->get_version() );

		// Public scripts and styles.
		$this->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Add defer attribute to frontend script for better performance.
		$this->add_filter( 'script_loader_tag', $plugin_public, 'add_defer_attribute', 10, 3 );

		// Banner output.
		$this->add_action( 'wp_footer', $plugin_banner, 'render_banner' );

		// Script blocking via script_loader_tag filter.
		$this->add_filter( 'script_loader_tag', $plugin_script_blocker, 'maybe_block_script', 10, 3 );

		// Output buffering for inline script blocking.
		$this->add_action( 'template_redirect', $plugin_script_blocker, 'start_buffer' );
		$this->add_action( 'shutdown', $plugin_script_blocker, 'end_buffer', 0 );

		// Early consent check script in wp_head.
		$this->add_action( 'wp_head', $plugin_script_blocker, 'output_early_script', 1 );

		// Shortcodes.
		$this->add_action( 'init', $plugin_shortcodes, 'register_shortcodes' );

		// Gutenberg block.
		$this->add_action( 'init', $plugin_shortcodes, 'register_block' );
		$this->add_action( 'enqueue_block_editor_assets', $plugin_shortcodes, 'enqueue_block_editor_assets' );
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 * @param string $hook          The name of the WordPress action that is being registered.
	 * @param object $component     A reference to the instance of the object on which the action is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 * @param string $hook          The name of the WordPress filter that is being registered.
	 * @param object $component     A reference to the instance of the object on which the filter is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  array  $hooks         The collection of hooks that is being registered (that is, actions or filters).
	 * @param  string $hook          The name of the WordPress filter that is being registered.
	 * @param  object $component     A reference to the instance of the object on which the filter is defined.
	 * @param  string $callback      The name of the function definition on the $component.
	 * @param  int    $priority      The priority at which the function should be fired.
	 * @param  int    $accepted_args The number of arguments that should be passed to the $callback.
	 * @return array                 The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
