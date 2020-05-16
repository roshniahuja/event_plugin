<?php 
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://about.me/roshniahuja
 * @since             1.0.0
 * @package           Events
 *
 * @wordpress-plugin
 * Plugin Name:       Events
 * Plugin URI:        events
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Roshni Ahuja
 * Author URI:        https://about.me/roshniahuja
 * Text Domain:       events
 * Domain Path:       /languages
 */
//Activation hook
function events_activation() {
}
register_activation_hook(__FILE__, 'events_activation');

//Deactivation hook
function events_deactivation() {
	// Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'events' );
    // Unregister the custom taxonomy, so the rules are no longer in memory.
    unregister_taxonomy( 'event_types' );
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'events_deactivation');

/**
 * Registers the event post type.
 */
function wpt_event_post_type() {

	$labels = array(
		'name'               => __( 'Events' ),
		'singular_name'      => __( 'Event' ),
		'add_new'            => __( 'Add New Event' ),
		'add_new_item'       => __( 'Add New Event' ),
		'edit_item'          => __( 'Edit Event' ),
		'new_item'           => __( 'Add New Event' ),
		'view_item'          => __( 'View Event' ),
		'search_items'       => __( 'Search Event' ),
		'not_found'          => __( 'No events found' ),
		'not_found_in_trash' => __( 'No events found in trash' )
	);

	$supports = array(
		'title',
		'editor',
		'thumbnail',
		'comments',
		'revisions',
	);

	$args = array(
		'labels'               => $labels,
		'supports'             => $supports,
		'public'               => true,
		'capability_type'      => 'post',
		'rewrite'              => array( 'slug' => 'events' ),
		'has_archive'          => true,
		'menu_position'        => 30,
		'menu_icon'            => 'dashicons-calendar-alt',
		'register_meta_box_cb' => 'wpt_add_event_metaboxes',
	);

	register_post_type( 'events', $args );

}
add_action( 'init', 'wpt_event_post_type' );  

function event_type_taxo() {
 
	// Add new taxonomy, make it hierarchical like categories
	//first do the translations part for GUI
	 
	  $labels = array(
	    'name' => _x( 'Event Type', 'event type' ),
	    'singular_name' => _x( 'Event Type', 'event type' ),
	    'search_items' =>  __( 'Search Event Types' ),
	    'all_items' => __( 'All Event Types' ),
	    'parent_item' => __( 'Parent Event Type' ),
	    'parent_item_colon' => __( 'Parent Event Type:' ),
	    'edit_item' => __( 'Edit Event Type' ), 
	    'update_item' => __( 'Update Event Type' ),
	    'add_new_item' => __( 'Add New Event Type' ),
	    'new_item_name' => __( 'New Event Type Name' ),
	    'menu_name' => __( 'Event Types' ),
	  );    
	 
	// Now register the taxonomy
	 
	  register_taxonomy('event_types',array('events'), array(
	    'hierarchical' => true,
	    'labels' => $labels,
	    'show_ui' => true,
	    'show_admin_column' => true,
	    'query_var' => true,
	    'rewrite' => array( 'slug' => 'event_type' ),
	  ));
	 
	}
add_action( 'init', 'event_type_taxo' ); 	
/**
 * Adds a metabox to the right side of the screen under the â€œPublishâ€ box
 */
function wpt_add_event_metaboxes() {
	add_meta_box(
		'wpt_events_location',
		'Event Location',
		'wpt_events_location',
		'events',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'wpt_add_event_metaboxes' );
/**
 * Output the HTML for the metabox.
 */
function wpt_events_location() {
	global $post;

	// Nonce field to validate form request came from current site
	wp_nonce_field( basename( __FILE__ ), 'event_fields' );

	// Get the location data if it's already been entered
	$start_date = get_post_meta( $post->ID, 'start_date', true );
	$end_date = get_post_meta( $post->ID, 'end_date', true );
	$event_venue = get_post_meta( $post->ID, 'event_venue', true );
	$location = get_post_meta( $post->ID, 'location', true );

	// Output the field
	echo '<label for="start_date">Start Date : </label>';
	echo '<input type="date" placeholder="Start Date" name="start_date" value="' . $start_date. '" class="start_date">';
	echo '<label for="end_date">End Date : </label>';
	echo '<input type="date" placeholder="End Date" name="end_date" value="' . $end_date. '" class="end_date">';
	echo '<label for="event_venue">Event Venue : </label>';
	echo '<input type="text" placeholder="Event Venue" name="event_venue" value="' . esc_textarea( $event_venue )  . '" class="event_venue">';
	echo '<label for="location">Location : </label>';
	echo '<input type="text" placeholder="Location" name="location" value="' . esc_textarea( $location )  . '" class="location">';

}
/**
 * Save the metabox data
 */
function wpt_save_events_meta( $post_id, $post ) {

	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['start_date'] ) || ! isset( $_POST['end_date'] ) || ! isset( $_POST['event_venue'] )  || ! isset( $_POST['location'] )  || ! wp_verify_nonce( $_POST['event_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}

	// Now that we're authenticated, time to save the data.
	// This sanitizes the data from the field and saves it into an array $events_meta.
	$events_meta['start_date'] = $_POST['start_date'];
	$events_meta['end_date'] = $_POST['end_date'];
	$events_meta['event_venue'] = esc_textarea( $_POST['event_venue'] );
	$events_meta['location'] = esc_textarea( $_POST['location'] );

	// Cycle through the $events_meta array.
	// Note, in this example we just have one item, but this is helpful if you have multiple.
	foreach ( $events_meta as $key => $value ) :

		// Don't store custom data twice
		if ( 'revision' === $post->post_type ) {
			return;
		}

		if ( get_post_meta( $post_id, $key, false ) ) {
			// If the custom field already has a value, update it.
			update_post_meta( $post_id, $key, $value );
		} else {
			// If the custom field doesn't have a value, add it.
			add_post_meta( $post_id, $key, $value);
		}

		if ( ! $value ) {
			// Delete the meta key if there's no value
			delete_post_meta( $post_id, $key );
		}

	endforeach;

}
add_action( 'save_post', 'wpt_save_events_meta', 1, 2 );

add_action( 'manage_posts_extra_tablenav', 'admin_post_list_top_export_button', 20, 1 );
function admin_post_list_top_export_button( $which ) {
    global $typenow;
 
    if ( 'events' === $typenow && 'top' === $which ) {

        ?>
        
        <input type="submit" name="export_past_events" id="export_past_events" class="button button-primary" value="Export Past Events" />
        <input type="submit" name="export_upcoming_events" id="export_upcoming_events" class="button button-primary" value="Export Upcoming Events" />
        <?php
    }
}
//Function to export past events
add_action( 'init', 'func_export_all_past_events' );
function func_export_all_past_events() {
	if(isset($_GET['export_past_events'])) {
	// Run the query.
	 $todays_date = date('Y-m-d');
	 $past_events = new WP_Query( array( 
	        'post_type'      => 'events', 
	        'posts_per_page' => -1,
	        'orderby'        => 'meta_value',
	        'order'          => 'ASC',
	        'meta_query'     => array(
	                                'relation' => 'AND',
	                                array(
	                                    'key' => 'start_date',
	                                    'value' => $todays_date,
	                                    'compare' => '<='
	                                ),
	                                array(
	                                    'key' => 'end_date',
	                                    'value' => $todays_date,
	                                    'compare' => '<='
	                                )
	        )
	    ));
	
	 	// The Loop
	 		$date = date('ymdhs');
	 		$delimiter = ",";
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="pastevents_'.$date.'.csv"');
			header('Pragma: no-cache');
			header('Expires: 0');

			$file = fopen('php://output', 'w');
			//set column headers
		    $fields = array('ID', 'Event Name', 'Content', 'Star Date', 'End Date', 'Event Venue', 'Event Location');
		    fputcsv($file, $fields, $delimiter);
		if ( $past_events->have_posts() ) {
		    while ( $past_events->have_posts() ) {
		        $past_events->the_post();
		        
		        $post_id = get_the_ID();
		        $title = get_the_title();
		        $content = get_The_content();
		        $start_date = get_post_meta( $post_id, 'start_date', true );
				$end_date = get_post_meta( $post_id, 'end_date', true );
				$event_venue = get_post_meta( $post_id, 'event_venue', true );
				$location = get_post_meta( $post_id, 'location', true );


		        fputcsv($file, array($post_id, $title, utf8_encode($content),$start_date,$end_date,$event_venue,$location));
		    }
		    exit();
		} 
		/* Restore original Post Data */
		wp_reset_postdata();
		
	}
}
//Function to export Upcoming events
add_action( 'init', 'export_upcoming_events' );
function export_upcoming_events() {
	if(isset($_GET['export_upcoming_events'])) {
	// Run the query.
	 $todays_date = date('Y-m-d');
	 $past_events = new WP_Query( array( 
	        'post_type'      => 'events', 
	        'posts_per_page' => -1,
	        'orderby'        => 'meta_value',
	        'order'          => 'ASC',
	        'meta_query'     => array(
	                                'relation' => 'AND',
	                                array(
	                                    'key' => 'start_date',
	                                    'value' => $todays_date,
	                                    'compare' => '>='
	                                ),
	                                array(
	                                    'key' => 'end_date',
	                                    'value' => $todays_date,
	                                    'compare' => '>='
	                                )
	        					)
	    ));
	
	 	// The Loop
	 		$date = date('ymdhs');
	 		$delimiter = ",";
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="upcomingevents_'.$date.'.csv"');
			header('Pragma: no-cache');
			header('Expires: 0');

			$file = fopen('php://output', 'w');
			//set column headers
		    $fields = array('ID', 'Event Name', 'Content', 'Star Date', 'End Date', 'Event Venue', 'Event Location');
		    fputcsv($file, $fields, $delimiter);
		if ( $past_events->have_posts() ) {
		    while ( $past_events->have_posts() ) {
		        $past_events->the_post();
		        
		        $post_id = get_the_ID();
		        $title = get_the_title();
		        $content = get_the_content();
		        $start_date = get_post_meta( $post_id, 'start_date', true );
				$end_date = get_post_meta( $post_id, 'end_date', true );
				$event_venue = get_post_meta( $post_id, 'event_venue', true );
				$location = get_post_meta( $post_id, 'location', true );


		        fputcsv($file, array($post_id, $title, utf8_encode($content),$start_date,$end_date,$event_venue,$location));
		    }
		    exit();
		} 
		/* Restore original Post Data */
		wp_reset_postdata();
		
	}
}
// Shortcode to display events and filters.
function events_list_filter($atts) {
	$args = array(
               'taxonomy' => 'event_types',
               'orderby' => 'name',
               'order'   => 'ASC'
           );

   $cats = get_categories($args);

   $content = '<div id="tabs-1">
					<form  id="eventsearch" name="eventsearch" method="get" action="">
						<div class="search-table">';
						if($cats){
							$content .= "<span>Event Category</span>";
						   	foreach($cats as $cat) {
					
								$content .= '<div class="search-field">';	
									if( isset( $_GET["event_category"] ) )
										{
											$select = "checked = 'checked'";
										}
				                        
				                        $content .= '<label class="check_content">
				    						<input type="checkbox" name="event_category" value="'.$cat->term_id.'" '.$select.' /> '. $cat->name.'
				                        </label></div>';
				            }
			            }
			     	$content .= '<span>Start Date</span>';
			     	$content .= '<div class="search-field">';	
			     	$content .= '<input type="Date" name="start_date">';
			     	$content .= '</div>';
			     	$content .= '<span>End Date</span>';
			     	$content .= '<div class="search-field">';	
			     	$content .= '<input type="Date" name="end_date">';
			     	$content .= '</div>';	
			     	$content .= '<button type="submit" class="filter">Filter</button>';
	$content .= '</div></form></div>';
	if (isset($_GET['event_category']))
		    {
	        	$tax_query[] = array(
			        'taxonomy' => 'event_types',
					'field'    => 'term_id',
					'terms'    => $_GET['event_category'],
					'operator' => 'IN',
			    );
			   
		    }
	if (isset($_GET['start_date']) && isset($_GET['end_date']))
		    {
	        	$meta_query[] = array(
			        array(
                        'relation' => 'AND',
                        array(
                            'key' => 'start_date',
                            'value' => $_GET['start_date'],
                            'compare' => '>='
                        ),
                        array(
                            'key' => 'end_date',
                            'value' => $_GET['end_date'],
                            'compare' => '<='
                        )
					)
			    );
		    }	    
	$args_address = array(
		'post_type' => 'events',
		'posts_per_page' => -1,
		'tax_query' =>  $tax_query,
		'meta_query' => $meta_query
	);

		$query = new WP_Query($args_address);
					
		 if ( $query->have_posts() ) {
			    while ( $query->have_posts() ) {
			        $query->the_post();
			        
			        $post_id = get_the_ID();
			        $title = get_the_title();
			        $cont = get_the_content();
			        $start_date = get_post_meta( $post_id, 'start_date', true );
					$end_date = get_post_meta( $post_id, 'end_date', true );
					$event_venue = get_post_meta( $post_id, 'event_venue', true );
					$location = get_post_meta( $post_id, 'location', true );
					$content .= '<div class="events_list">';
					$content .= '<h3><a href="'.get_permalink().'">'.$title.'</a></h3>';
					$content .= $cont;
					$content .= '<span> Location : '.$location.' </span>';
					$content .= '</div>';
			}
		}
    return $content;
}

add_shortcode('events', 'events_list_filter');
add_action('wp_enqueue_scripts', 'load_my_scripts');
function load_my_scripts() {
        wp_enqueue_script( 'script', plugins_url( '/js/custom.js', __FILE__ ));
}
?>