<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://about.me/roshniahuja
 * @since      1.0.0
 *
 * @package    Event
 * @subpackage Event/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Event
 * @subpackage Event/includes
 * @author     Roshni Ahuja <roshniahuja14@gmail.com>
 */
class Event_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Unregister the post type, so the rules are no longer in memory.
	    unregister_post_type( 'events' );
	    // Unregister the custom taxonomy, so the rules are no longer in memory.
	    unregister_taxonomy( 'event_types' );
	    // Clear the permalinks to remove our post type's rules from the database.
	    flush_rewrite_rules();
	}

}
