<?php
/**
 * Main Plugin Class
 *
 * @package Website_FAQ_Maker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
final class WFM_Plugin {

	/**
	 * The single instance of the class.
	 *
	 * @var WFM_Plugin
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Website FAQ Maker Instance.
	 *
	 * Ensures only one instance of Website FAQ Maker is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return WFM_Plugin - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		// We will include other classes here as we create them.
		require_once WFM_PLUGIN_DIR . 'includes/class-faq-cpt.php';
		
		new WFM_FAQ_CPT();

		require_once WFM_PLUGIN_DIR . 'includes/class-faq-settings.php';
		new WFM_FAQ_Settings();

		require_once WFM_PLUGIN_DIR . 'includes/class-faq-generator.php';
		new WFM_FAQ_Generator();

		require_once WFM_PLUGIN_DIR . 'includes/class-faq-shortcode.php';
		new WFM_FAQ_Shortcode();
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( WFM_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( WFM_PLUGIN_FILE, array( $this, 'deactivate' ) );
		
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
	}

	/**
	 * Activation hook.
	 */
	public function activate() {
		// Flush rewrite rules if CPT is registered on activation.
		// This will be handled in the CPT class usually, or we can trigger it here.
	}

	/**
	 * Deactivation hook.
	 */
	public function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * On plugins loaded.
	 */
	public function on_plugins_loaded() {
		// Initialize other classes here if they need to wait for plugins_loaded.
	}
}
