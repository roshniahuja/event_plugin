<?php

/**
 * Registers the event taxonomies: event-venue, event-category and optinally event-tag
 * Hooked onto init
 *
 * @ignore
 * @access private
 * @since 1.0
 */
/**
 * Registers the event post type.
 */
function wpt_event_post_type() {

	$labels = array(
		'name'               => __( 'Events','event' ),
		'singular_name'      => __( 'Event','event' ),
		'add_new'            => __( 'Add New Event','event' ),
		'add_new_item'       => __( 'Add New Event','event' ),
		'edit_item'          => __( 'Edit Event','event' ),
		'new_item'           => __( 'Add New Event','event' ),
		'view_item'          => __( 'View Event','event' ),
		'search_items'       => __( 'Search Event','event' ),
		'not_found'          => __( 'No events found','event' ),
		'not_found_in_trash' => __( 'No events found in trash','event' )
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

/**
 * Registers the event type taxonomy.
 */
function event_type_taxo() {
	 //Add new taxonomy, make it hierarchical like categories
	  $labels = array(
	    'name' => _x( 'Event Type','event' ),
	    'singular_name' => _x( 'Event Type','event' ),
	    'search_items' =>  __( 'Search Event Types','event' ),
	    'all_items' => __( 'All Event Types','event' ),
	    'parent_item' => __( 'Parent Event Type','event' ),
	    'parent_item_colon' => __( 'Parent Event Type:','event' ),
	    'edit_item' => __( 'Edit Event Type','event' ),
	    'update_item' => __( 'Update Event Type','event' ),
	    'add_new_item' => __( 'Add New Event Type','event' ),
	    'new_item_name' => __( 'New Event Type Name','event' ),
	    'menu_name' => __( 'Event Types','event' ),
	  );    
	  //Now register the taxonomy
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
 * Adds a metabox to the right side of the screen
 */
function wpt_add_event_metaboxes() {
	add_meta_box(
		'wpt_events_location',
		'Event Details',
		'wpt_events_location',
		'events',
		'normal',
		'high'
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
	?>
	<div class="inside">
	<div class="egrid onetime">
		<div class="e-grid-row">
	 		<div class="e-grid-4">
				<label for="start_date"> <?php esc_html_e( 'Start Date','event' ); ?></label>
			</div>
			<div class="e-grid-8">
				<input type="date" id="startDate" placeholder="Start Date" name="start_date" value="<?php echo esc_html( $start_date );?>" class="start_date">
			</div>
		</div>
		<div class="e-grid-row">
	 		<div class="e-grid-4">
				<label for="end_date"><?php esc_html_e( 'End Date','event' ); ?></label>
			</div>
			<div class="e-grid-8">
				<input type="date" id="endDate" placeholder="End Date" name="end_date" value="<?php echo esc_html( $end_date );?>" class="end_date">
			</div>
		</div>
		<div class="e-grid-row">
			<div class="e-grid-4">
				<label for="event_venue"><?php esc_html_e( 'Event Venue','event' ); ?></label>
			</div>
			<div class="e-grid-8">
				<input type="text" placeholder="Event Venue" name="event_venue" value="<?php echo esc_html( $event_venue );?>" class="event_venue">
			</div>
		</div>
		<div class="e-grid-row">
			<div class="e-grid-4">
				<label for="location"><?php esc_html_e( 'Location','event' ); ?></label>
			</div>
			<div class="e-grid-8">
				<input type="text" placeholder="Location" name="location" value="<?php echo esc_html( $location );?>" class="location">
			</div>
		</div>
	</div>
	</div>
		
		<?php
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
	$events_meta['start_date'] = esc_html($_POST['start_date']);
	$events_meta['end_date'] = esc_html($_POST['end_date']);
	$events_meta['event_venue'] = sanitize_text_field( $_POST['event_venue'] );
	$events_meta['location'] = sanitize_text_field( $_POST['location'] );

	// Cycle through the $events_meta array.
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

/**
 * Call to action for export events
 */
add_action( 'manage_posts_extra_tablenav', 'admin_post_list_top_export_button', 20, 1 );
function admin_post_list_top_export_button( $which ) {
    global $typenow;
    if ( 'events' === $typenow && 'top' === $which ) {
    ?>
    		<div class="alignleft actions">
        		<input type="submit" name="export_past_events" id="export_past_events" class="button button-primary" value="Export Past Events" />
        		<input type="submit" name="export_upcoming_events" id="export_upcoming_events" class="button button-primary" value="Export Upcoming Events" />
        	</div>
    <?php
    }
}

/**
 * Action to export past events
 */
add_action( 'init', 'func_export_all_past_events' );
function func_export_all_past_events() {
	if ( isset ( $_GET['export_past_events'] ) ) {
	//Run the query.
	 $todays_date = gmdate('Y-m-d');
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
	                                    'compare' => '<'
	                                ),
	                                array(
	                                    'key' => 'end_date',
	                                    'value' => $todays_date,
	                                    'compare' => '<'
	                                )
	        					)
	    	));
	 		// The Loop
	 		$date = gmdate('ymdhs');
	 		$delimiter = ',';
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="pastevents_'.$date.'.csv"');
			header('Pragma: no-cache');
			header('Expires: 0');

			$file = fopen( 'php://output', 'w' );
			//set column headers
		    $fields = array( 'ID', 'Event Name', 'Content', 'Star Date', 'End Date', 'Event Venue', 'Event Location' );
		    fputcsv( $file, $fields, $delimiter );
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
		        fputcsv( $file, array($post_id, $title, utf8_encode($content),$start_date,$end_date,$event_venue,$location) );
		    }
		    exit();
		} 
		/* Restore original Post Data */
		wp_reset_postdata();
		
	}
}
/**
 * Action to export Upcoming events
 */
add_action( 'init', 'export_upcoming_events' );
function export_upcoming_events() { 

	if ( isset ( $_GET['export_upcoming_events'] ) ) {
	//Run the query.
	 $todays_date = gmdate('Y-m-d');
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
	 		$date = gmdate('ymdhs');
	 		$delimiter = ',';
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="upcomingevents_'.$date.'.csv"');
			header('Pragma: no-cache');
			header('Expires: 0');
			$file = fopen('php://output', 'w');
			//set column headers
		    $fields = array( 'ID', 'Event Name', 'Content', 'Star Date', 'End Date', 'Event Venue', 'Event Location' );
		    fputcsv( $file, $fields, $delimiter );
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
		        fputcsv( $file, array($post_id, $title, utf8_encode($content),$start_date,$end_date,$event_venue,$location) );
		    }
		    exit();
		} 
		/* Restore original Post Data */
		wp_reset_postdata();
		
	}
}