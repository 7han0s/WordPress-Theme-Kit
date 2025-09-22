<?php
/**
 * Plugin Name:       WordPress Theme Kit
 * Plugin URI:        https://github.com/your-repo/wp-theme-kit
 * Description:       A feature plugin to add advanced color palette management to the WordPress Site Editor.
 * Version:           0.1.0
 * Author:            Jules
 * Author URI:        https://your-website.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-theme-kit
 * Domain Path:       /languages
 */

namespace ThemeKit;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require the Composer autoloader.
$autoloader = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
} else {
	// Optionally, add an admin notice to inform the user to run `composer install`.
	// For now, we just exit gracefully if the dependencies are not installed.
	return;
}

/**
 * Initializes the plugin by instantiating the main classes.
 *
 * This function is hooked to `plugins_loaded` to ensure that all dependent
 * plugins are loaded first.
 */
function initialize() {
	new Theme_Support();
	new Assets_Loader();
	new Site_Editor();
	new Rest_Api();
	// The other classes will be instantiated here as they are developed.
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\initialize' );
