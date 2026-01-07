<?php
/**
 * Script blocking functionality.
 *
 * @link       https://github.com/pxdsgnco/wp-cookie-consent
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */

/**
 * Script blocking class.
 *
 * @since      1.0.0
 * @package    Consent_Raven
 * @subpackage Consent_Raven/public
 */
class CR_Script_Blocker {

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
	 * Registered scripts for blocking.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array $registered_scripts Scripts to potentially block.
	 */
	private $registered_scripts;

	/**
	 * Whether output buffering is active.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    bool $buffer_active Whether buffering is active.
	 */
	private $buffer_active = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name        = $plugin_name;
		$this->version            = $version;
		$this->registered_scripts = null;
	}

	/**
	 * Start output buffering to capture and modify inline scripts.
	 *
	 * @since 1.0.0
	 */
	public function start_buffer() {
		if ( ! CR_Consent::should_show_banner() ) {
			return;
		}

		if ( $this->buffer_active ) {
			return;
		}

		$this->buffer_active = true;
		ob_start( array( $this, 'process_buffer' ) );
	}

	/**
	 * End output buffering.
	 *
	 * @since 1.0.0
	 */
	public function end_buffer() {
		if ( ! $this->buffer_active ) {
			return;
		}

		if ( ob_get_level() > 0 ) {
			ob_end_flush();
		}

		$this->buffer_active = false;
	}

	/**
	 * Process the output buffer to block inline scripts.
	 *
	 * @since  1.0.0
	 * @param  string $buffer The output buffer content.
	 * @return string Modified buffer.
	 */
	public function process_buffer( $buffer ) {
		if ( empty( $buffer ) ) {
			return $buffer;
		}

		$scripts = $this->get_registered_scripts();

		// Early exit if no scripts registered.
		if ( empty( $scripts ) ) {
			return $buffer;
		}

		// Early exit if buffer contains no script tags.
		if ( stripos( $buffer, '<script' ) === false ) {
			return $buffer;
		}

		// Find inline scripts that should be blocked.
		foreach ( $scripts as $script ) {
			if ( 'inline' !== $script['method'] || empty( $script['pattern'] ) ) {
				continue;
			}

			// Match script tags containing the pattern.
			$pattern = '/<script([^>]*)>([^<]*' . preg_quote( $script['pattern'], '/' ) . '[^<]*)<\/script>/is';

			$buffer = preg_replace_callback(
				$pattern,
				function ( $matches ) use ( $script ) {
					$attributes = $matches[1];
					$content    = $matches[2];

					// Skip if already blocked.
					if ( strpos( $attributes, 'data-cookie-category' ) !== false ) {
						return $matches[0];
					}

					// Skip if type is already text/plain.
					if ( preg_match( '/type\s*=\s*["\']text\/plain["\']/', $attributes ) ) {
						return $matches[0];
					}

					// Remove existing type attribute.
					$attributes = preg_replace( '/\s*type\s*=\s*["\'][^"\']*["\']/i', '', $attributes );

					// Add blocking attributes.
					return sprintf(
						'<script type="text/plain" data-cookie-category="%s"%s>%s</script>',
						esc_attr( $script['category_id'] ),
						$attributes,
						$content
					);
				},
				$buffer
			);
		}

		return $buffer;
	}

	/**
	 * Get registered scripts.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return array Registered scripts.
	 */
	private function get_registered_scripts() {
		if ( null === $this->registered_scripts ) {
			$this->registered_scripts = CR_Consent::get_scripts();
		}

		return $this->registered_scripts;
	}

	/**
	 * Maybe block a script based on consent.
	 *
	 * @since  1.0.0
	 * @param  string $tag    The script tag HTML.
	 * @param  string $handle The script handle.
	 * @param  string $src    The script source URL.
	 * @return string Modified script tag.
	 */
	public function maybe_block_script( $tag, $handle, $src ) {
		$scripts = $this->get_registered_scripts();

		// Find if this script should be blocked.
		$script_config = $this->find_script_config( $handle, $src, $scripts );

		if ( ! $script_config ) {
			return $tag;
		}

		$category_id = $script_config['category_id'];
		$method      = $script_config['method'];

		/**
		 * Filter whether to block a specific script.
		 *
		 * @since 1.0.0
		 * @param bool   $should_block Whether to block the script.
		 * @param string $handle       The script handle.
		 * @param string $category_id  The category ID.
		 */
		$should_block = apply_filters( 'consent_raven_should_block_script', true, $handle, $category_id );

		if ( ! $should_block ) {
			return $tag;
		}

		// Apply blocking method.
		switch ( $method ) {
			case 'type-swap':
				$tag = $this->apply_type_swap( $tag, $category_id );
				break;

			case 'data-attribute':
				$tag = $this->apply_data_attribute( $tag, $category_id );
				break;

			default:
				$tag = $this->apply_type_swap( $tag, $category_id );
				break;
		}

		return $tag;
	}

	/**
	 * Find script configuration by handle or source pattern.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $handle  The script handle.
	 * @param  string $src     The script source URL.
	 * @param  array  $scripts Registered scripts.
	 * @return array|false Script configuration or false if not found.
	 */
	private function find_script_config( $handle, $src, $scripts ) {
		foreach ( $scripts as $script ) {
			// Match by handle.
			if ( ! empty( $script['handle'] ) && $script['handle'] === $handle ) {
				return $script;
			}

			// Match by source pattern.
			if ( ! empty( $script['pattern'] ) && preg_match( '/' . $script['pattern'] . '/i', $src ) ) {
				return $script;
			}
		}

		return false;
	}

	/**
	 * Apply type swap blocking method.
	 *
	 * Changes type="text/javascript" to type="text/plain" with a data attribute.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $tag         The script tag HTML.
	 * @param  string $category_id The category ID.
	 * @return string Modified script tag.
	 */
	private function apply_type_swap( $tag, $category_id ) {
		// Remove existing type attribute.
		$tag = preg_replace( '/\s*type\s*=\s*["\'][^"\']*["\']/i', '', $tag );

		// Add blocked type and category attribute.
		$tag = str_replace(
			'<script',
			'<script type="text/plain" data-cookie-category="' . esc_attr( $category_id ) . '"',
			$tag
		);

		return $tag;
	}

	/**
	 * Apply data attribute blocking method.
	 *
	 * Adds data-cookie-consent="false" attribute that JS will check.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $tag         The script tag HTML.
	 * @param  string $category_id The category ID.
	 * @return string Modified script tag.
	 */
	private function apply_data_attribute( $tag, $category_id ) {
		$tag = str_replace(
			'<script',
			'<script data-cookie-category="' . esc_attr( $category_id ) . '" data-cookie-consent="pending"',
			$tag
		);

		return $tag;
	}

	/**
	 * Register a script for blocking.
	 *
	 * Helper method for developers to programmatically register scripts.
	 *
	 * @since 1.0.0
	 * @param array $script Script configuration.
	 *                      - handle: Script handle (optional).
	 *                      - pattern: URL pattern to match (optional).
	 *                      - category_id: Category ID for consent.
	 *                      - method: Blocking method (type-swap or data-attribute).
	 * @return bool Whether the script was registered.
	 */
	public static function register_script( $script ) {
		if ( empty( $script['category_id'] ) ) {
			return false;
		}

		if ( empty( $script['handle'] ) && empty( $script['pattern'] ) ) {
			return false;
		}

		$scripts   = CR_Consent::get_scripts();
		$scripts[] = CR_Consent::sanitize_script( $script );

		return CR_Consent::update_scripts( $scripts );
	}

	/**
	 * Unregister a script from blocking.
	 *
	 * @since 1.0.0
	 * @param string $id Script ID to unregister.
	 * @return bool Whether the script was unregistered.
	 */
	public static function unregister_script( $id ) {
		$scripts = CR_Consent::get_scripts();

		$scripts = array_filter(
			$scripts,
			function ( $script ) use ( $id ) {
				return $script['id'] !== $id;
			}
		);

		return CR_Consent::update_scripts( array_values( $scripts ) );
	}

	/**
	 * Get inline script for blocked script activation.
	 *
	 * This script runs when consent is given and activates blocked scripts.
	 *
	 * @since  1.0.0
	 * @return string JavaScript code.
	 */
	public static function get_activation_script() {
		return "
			(function() {
				window.consentRavenActivateScripts = function(categories) {
					var scripts = document.querySelectorAll('script[data-cookie-category]');
					scripts.forEach(function(script) {
						var category = script.getAttribute('data-cookie-category');
						if (categories.indexOf(category) !== -1) {
							var newScript = document.createElement('script');

							// Copy attributes except type and data-cookie-*
							Array.from(script.attributes).forEach(function(attr) {
								if (attr.name !== 'type' && !attr.name.startsWith('data-cookie-')) {
									newScript.setAttribute(attr.name, attr.value);
								}
							});

							// Copy inline content if present
							if (script.innerHTML) {
								newScript.innerHTML = script.innerHTML;
							}

							// Replace blocked script with active one
							script.parentNode.replaceChild(newScript, script);
						}
					});
				};
			})();
		";
	}

	/**
	 * Output early consent check script in wp_head.
	 *
	 * This script checks for existing consent and makes it available
	 * before other scripts run, enabling early script activation.
	 *
	 * @since 1.0.0
	 */
	public function output_early_script() {
		if ( ! CR_Consent::should_show_banner() ) {
			return;
		}

		$settings = CR_Consent::get_settings();
		?>
		<script id="consent-raven-early">
		(function() {
			'use strict';

			// Parse consent cookie
			function getConsentCookie() {
				var name = 'consent_raven=';
				var ca = document.cookie.split(';');
				for (var i = 0; i < ca.length; i++) {
					var c = ca[i].trim();
					if (c.indexOf(name) === 0) {
						try {
							return JSON.parse(decodeURIComponent(c.substring(name.length)));
						} catch (e) {
							return null;
						}
					}
				}
				return null;
			}

			// Get consent and store in global for later use
			var consent = getConsentCookie();
			var version = <?php echo wp_json_encode( $settings['consent_version'] ); ?>;

			// Validate consent version
			if (consent && consent.version !== version) {
				consent = null;
			}

			// Store consent for later access
			window.consentRavenConsent = consent;

			// If consent exists, prepare enabled categories
			if (consent && consent.categories) {
				window.consentRavenEnabledCategories = Object.keys(consent.categories).filter(function(key) {
					return consent.categories[key] === true;
				});
			} else {
				window.consentRavenEnabledCategories = [];
			}
		})();
		</script>
		<?php
	}
}
