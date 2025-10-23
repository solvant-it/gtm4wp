<?php
/**
 * Google Tag Manager for WordPress
 *
 * @package GTM4WP
 * @author Thomas Geiger
 * @copyright 2013- Geiger Tamás e.v. (Thomas Geiger s.e.)
 * @license GNU General Public License, version 3
 *
 * @wordpress-plugin
 * Plugin Name: GTM4WP - A Google Tag Manager (GTM) plugin for WordPress
 * Plugin URI: https://gtm4wp.com/
 * Description: The first Google Tag Manager plugin for WordPress with business goals in mind
 * Version: 1.22.1-solvant-0.2
 * Requires at least: 3.4.0
 * Requires PHP: 7.4
 * Author: Thomas Geiger
 * Author URI: https://gtm4wp.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: duracelltomi-google-tag-manager
 * Domain Path: /languages

 * Update URI: false

 * WC requires at least: 5.0
 * WC tested up to: 9.8
 */

define( 'GTM4WP_VERSION', '1.22.1-solvant-0.2' );
define( 'GTM4WP_PATH', plugin_dir_path( __FILE__ ) );

global $gtp4wp_plugin_url, $gtp4wp_plugin_basename, $gtp4wp_script_path;
$gtp4wp_plugin_url      = plugin_dir_url( __FILE__ );
$gtp4wp_plugin_basename = plugin_basename( __FILE__ );
$gtp4wp_script_path     = $gtp4wp_plugin_url . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : 'dist/' ) . 'js/';
require_once GTM4WP_PATH . '/common/readoptions.php';

/**
 * WordPress hook function to load translations
 *
 * @see https://developer.wordpress.org/reference/hooks/init/
 *
 * @return void
 */
function gtm4wp_init() {
	load_plugin_textdomain( 'duracelltomi-google-tag-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'gtm4wp_init' );

/**
 * WordPress hook function run after plugins have been loaded.
 *
 * @see https://developer.wordpress.org/reference/hooks/plugins_loaded/
 *
 * @return void
 */
function gtm4wp_plugins_loaded() {
	if ( is_admin() ) {
		require_once GTM4WP_PATH . '/admin/admin.php';
	} else {
		require_once GTM4WP_PATH . '/public/frontend.php';
	}
}
add_action( 'plugins_loaded', 'gtm4wp_plugins_loaded' );

/**
 * Adds an action to declare compatibility with High Performance Order Storage (HPOS)
 * before WooCommerce initialization.
 *
 * @since 1.17
 *
 * @param string   $hook_name  The name of the action to which the callback function is hooked.
 * @param callable $callback   The callback function to be executed when the action is run.
 * @param int      $priority   Optional. The order in which the callback functions are executed. Default is 10.
 * @param int      $args_count Optional. The number of arguments the callback accepts. Default is 1.
 *
 * @return void
 */
add_action(
	'before_woocommerce_init',
	function() {
		// Check if the FeaturesUtil class exists in the \Automattic\WooCommerce\Utilities namespace.
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			// Declare compatibility with custom order tables using the FeaturesUtil class.
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

// --- Optional: Plugin Update Checker (PUC) integration for GitHub releases ---
// If you want updates to come from your GitHub repo, add the PUC library under
// includes/plugin-update-checker/ (see https://github.com/YahnisElsts/plugin-update-checker)
// and configure the repo URL and branch below.
if ( ! function_exists( 'gtm4wp_setup_github_updater' ) ) {
	function gtm4wp_setup_github_updater() {
		// path to the PUC library
		$puc_path = GTM4WP_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';

		if ( file_exists( $puc_path ) ) {
			require_once $puc_path;

			// GitHub repo URL for your fork
			$repo_url = 'https://github.com/solvant-it/gtm4wp/';

			// Build the update checker object
			try {
				$update_checker = Puc_v4_Factory::buildUpdateChecker(
					$repo_url,
					__FILE__, // main plugin file
					'duracelltomi-google-tag-manager-for-wordpress'
				);

				// track master branch (current repo branch)
				$update_checker->setBranch('master');

				// If you'd like to use a GitHub token (private repos), call:
				// $update_checker->setAuthentication('YOUR_GITHUB_TOKEN');
			} catch ( Exception $e ) {
				// don't break plugin if updater fails
			}
		}
	}
	// call on plugins_loaded so constants are available
	add_action( 'plugins_loaded', 'gtm4wp_setup_github_updater' );
}
