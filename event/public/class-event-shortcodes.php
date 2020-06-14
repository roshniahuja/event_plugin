<?php
/**
 * Shortcode to display events and filters.
 */
function events_list_filter( $atts ) {
	wp_nonce_field( basename( __FILE__ ), 'event_filter_fields' );
	$args = array(
               'taxonomy' => 'event_types',
               'orderby' => 'name',
               'order'   => 'ASC'
           );

   $cats = get_categories($args);

   $content = '<div id="container">
					<form  id="eventsearch" name="eventsearch" method="post" action="">
						';
						if ( $cats ) {
							$content .= '<div class="search-table"><span>'.__('Event Category','event').'</span>';
						   	foreach ( $cats as $cat ) {
				                        $content .= '<label class="check_content">
				    						<input type="checkbox" name="event_category" value="'.$cat->term_id.'"';
				    					if(isset( $_POST['event_category'] )){
											if ( $_POST['event_category'] == $cat->term_id ) {
												$content .= 'checked';
											}
										}
				    					$content .= '/> '. $cat->name.'
				                        </label>';
				            }
				            $content .= '</div>';
			            }
			            if( isset( $_POST['start_date'] ) ){
							$start_date = $_POST['start_date'];
						}else {
							$start_date = '';
						}
						if( isset( $_POST['end_date'] ) ){
							$end_date = $_POST['end_date'];
						}else {
							$end_date = '';
						}
			     	$content .= '<div class="search-table"><span>'.__('Start Date','event').'</span>';
			     	$content .= '<div class="search-field">';	
			     	$content .= '<input type="Date" name="start_date" value="'.$start_date.'" id="start">';
			     	$content .= '</div></div>';
			     	$content .= '<div class="search-table"><span>'.__('End Date','event').'</span>';
			     	$content .= '<div class="search-field">';	
			     	$content .= '<input type="Date" name="end_date" id="end" value="'.$end_date.'">';
			     	$content .= '</div></div>';	
			     	$content .= '<input type="submit" class="filter" value="Filter"></button>';
	$content .= '</div></form></div>';
	$tax_query = array();
	$meta_query = array();
	// Verify this came from the our screen and with proper authorization,
	if ( isset ( $_POST['event_category'] ) ) {
		$tax_query[] = array(
			        'taxonomy' => 'event_types',
					'field'    => 'term_id',
					'terms'    => $_POST['event_category'],
					'operator' => 'IN',
		);
	}
	if ( !empty ( $_POST['start_date'] ) ) {
	        	$meta_query[] = array(
			        array(
                        'relation' => 'OR',
                        array(
                            'key' => 'start_date',
                            'value' => $_POST['start_date'],
                            'compare' => '>='
                        ),
                        array(
                            'key' => 'end_date',
                            'value' => $_POST['end_date'],
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
		 	$content .= '<div class="row mb-3 events_container">';
			    while ( $query->have_posts() ) {
			        $query->the_post();
			        $post_id = get_the_ID();
			        $title = get_the_title();
			        $cont = get_the_content();
			        $start_date = get_post_meta( $post_id, 'start_date', true );
					$end_date = get_post_meta( $post_id, 'end_date', true );
					$event_venue = get_post_meta( $post_id, 'event_venue', true );
					$location = get_post_meta( $post_id, 'location', true );
					$event_img = get_the_post_thumbnail_url($post_id);
					if( $event_img ) {
						$event_img = $event_img;
					}else{
						$event_img = plugin_dir_url( __FILE__ ) . 'images/SD-default-image.png';
					}
					$content .= '<div class="col-4 themed-grid-col">';
					$content .= '<a href="'.get_permalink().'"><img src="'.$event_img.'"></a>';
					$content .= '<h3><a href="'.get_permalink().'">'.strtoupper( $title ).'</a></h3>';
					$content .= $cont;
					if( $location ){
						$content .= '<span> '.__('Location','event').' : '.$location.'</span>';
					}
					$content .= '</div>';
				}
			$content .= '</div>';
		} else {
			$content .= '<div class="row mb-3">';
			$content .= __('No events are available','event');
			$content .= '</div>';
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	$content .= '</div>';
    return $content;
}
add_shortcode('events', 'events_list_filter');
?>