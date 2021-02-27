<?php
/**
 * Removes tabs from the BuddyPress navigation bar based on PMPro level.
 */
function pmprobp_remove_nav_items() {
	global $bp;

	if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
		return;
	}

	if ( pmpro_hasMembershipLevel( 4 ) ) {
		bp_core_remove_nav_item( 'media' );
		bp_core_remove_nav_item( 'notifications' );
		bp_core_remove_nav_item( 'forum' );
		bp_core_remove_nav_item( 'diary' );
		bp_core_remove_nav_item( 'mec-events' );
	}
}
add_action( 'bp_setup_nav', 'pmprobp_remove_nav_items', 11 );