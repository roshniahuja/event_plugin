<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://about.me/roshniahuja
 * @since             1.0.0
 * @package           Event
 *
 * @wordpress-plugin
 * Plugin Name:       Event
 * Plugin URI:        event
 * Description:       Creates a custom post type with features to create events by adding venue, location, start and end date. It will allow to export past and upcoming event.
 * Version:           1.0.0
 * Author:            Roshni Ahuja
 * Author URI:        https://about.me/roshniahuja
 * Text Domain:       event
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EVENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-event-activator.php
 */
function activate_event() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-activator.php';
	Event_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-event-deactivator.php
 */
function deactivate_event() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-deactivator.php';
	Event_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_event' );
register_deactivation_hook( __FILE__, 'deactivate_event' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-event.php';
/****** Register event post type and event taxonomy******/
require plugin_dir_path( __FILE__ ) . 'includes/event-cpt.php';
/****** Shortcodes ******/
require plugin_dir_path( __FILE__ ) . 'public/class-event-shortcodes.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_event() {

	$plugin = new Event();
	$plugin->run();

}
run_event();
