<?php
/**
 * Plugin Name: Website FAQ Maker
 * Plugin URI:  https://dnnengineer.com/website-faq-maker
 * Description: Generates FAQs from your content using AI (OpenAI or Gemini) and displays them via shortcode.
 * Version:     1.1.0
 * Author:      Saad
 * Author URI:  https://dnnengineer.com
 * Text Domain: website-faq-maker
 * Domain Path: /languages
 * License:     GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WFM_VERSION', '1.1.0' );
define( 'WFM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WFM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WFM_PLUGIN_FILE', __FILE__ );

// Include the main plugin class.
require_once WFM_PLUGIN_DIR . 'includes/class-faq-plugin.php';

// Initialize Plugin Update Checker.
require_once WFM_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/m-saad125/website-faq-maker', // TODO: User must update this URL
	__FILE__,
	'website-faq-maker'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

/**
 * Main instance of Website FAQ Maker.
 *
 * Returns the main instance of WFM_Plugin to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return WFM_Plugin
 */
function wfm_plugin() {
	return WFM_Plugin::instance();
}

// Global for backwards compatibility.
$GLOBALS['wfm_plugin'] = wfm_plugin();
