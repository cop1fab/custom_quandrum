<?php

//Restrict access to the BuddyPress directory and profiles based on membership level.

function my_page_template_redirect()
{
	global $pmpro_pages;
	
    	if(in_array(bp_current_component(), array('activity', 'diary','mec-events', 'friends', 'groups')))
    	{
		if ( pmpro_hasMembershipLevel( 4 ) ) {
			
		wp_redirect('/membership-account/upgrade/');
		exit();
	}}
}
add_action( 'template_redirect', 'my_page_template_redirect' );