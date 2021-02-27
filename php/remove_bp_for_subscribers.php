<?php 

/*
 * Remove certain tabs from user profile based on member role.
 */
function buddydev_remove_tabs_based_on_member_roles() {
	if ( ! bp_is_user() ) {
		return;
	}
    
    if ( pmpro_hasMembershipLevel( 4 ) ) {
        bp_core_remove_nav_item( 'friends' ); //removes the tab friends
        bp_core_remove_nav_item( 'messages' ); //removes the tab messages
        bp_core_remove_nav_item( 'forums' );
		bp_core_remove_nav_item( 'activity' );
    }

}

add_action( 'bp_setup_nav', 'buddydev_remove_tabs_based_on_member_roles', 1001 );