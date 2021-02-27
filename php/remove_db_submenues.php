<?php

/**
 * Removes tabs from the BuddyPress navigation bar based on PMPro level.
 */
function pmprobp_remove_nav_items_social_article_level2() {
	global $bp;

	if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
	//if ( pmpro_hasMembershipLevel( 2 ) ||  pmpro_hasMembershipLevel( 5 ))  {
		return;
	}

	if ( pmpro_hasMembershipLevel( 2 ) ) {
		
	bp_core_remove_subnav_item( 'articles', 'articles' );
	bp_core_remove_subnav_item( 'articles', 'under-review' );
	bp_core_remove_subnav_item( 'articles', 'draft' );
	bp_core_remove_subnav_item( 'articles', 'new' );
	}
}
add_action( 'bp_setup_nav', 'pmprobp_remove_nav_items_social_article_level2', 11 );