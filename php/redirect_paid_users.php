<?php

//Restrict access to the BuddyPress directory and profiles based on membership level.

function redirect_paid_user_event()
{
	global $pmpro_pages;
	
    	if(in_array(bp_current_component(), array('mec-events')))
    	{
		if ( pmpro_hasMembershipLevel( array(5, 6, 7, 8, 9, 10) ) ) {
			
		wp_redirect('/events/');
		exit();
	}}
}
add_action( 'template_redirect', 'redirect_paid_user_event' );