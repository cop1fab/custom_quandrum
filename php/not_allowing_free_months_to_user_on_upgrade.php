<?php

function my_pmpro_get_previous_paid_levels( $user_id = null ) {
	// Is PMPro active?
	if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
		return false;
	}

	global $wpdb, $current_user, $pmpro_levels;

	// Get user id
	if ( empty( $user_id ) ) {
		$user_id = $current_user->ID;
	}

	if ( ! $user_id ) {
		return false;
	}

	$all_levels_ids  = array_keys( $pmpro_levels );
	$free_levels_ids = array( 1,3,4 ); // set your free levels ids here.
	$paid_levels_ids = $all_levels_ids;

	// remove free levels
	if ( ! empty( $free_levels_ids ) && is_array( $free_levels_ids ) ) {
		$paid_levels_ids = array_diff( $all_levels_ids, $free_levels_ids );
	}

	$member_previous_levels = $wpdb->get_results( "SELECT membership_id FROM $wpdb->pmpro_memberships_users WHERE user_id = '$user_id' ORDER BY id DESC", ARRAY_N );

	// Bail if new member
	if ( empty( $member_previous_levels ) ) {
		return false;
	}

	$member_previous_levels = array_merge( ...$member_previous_levels );

	$member_previous_levels_paid = array_intersect( $paid_levels_ids, $member_previous_levels );
	$member_previous_levels_paid = array_values( $member_previous_levels_paid );

	if ( empty( $member_previous_levels_paid ) ) {
		return false;
	}

	return $member_previous_levels_paid;
}

function my_pmpro_checkout_level( $level ) {
	if ( ! function_exists( 'my_pmpro_get_previous_paid_levels' ) ) {
		return $level;
	}

	global $current_user;
	$member_previous_levels_paid = my_pmpro_get_previous_paid_levels( $current_user->ID );

	if ( ! empty( $member_previous_levels_paid ) && 0 < count( $member_previous_levels_paid ) && 0 === intval( $level->initial_payment ) && ! empty( trim( $level->cycle_period ) ) ) {
		$level->initial_payment = $level->billing_amount;
	}

	return $level;
}
add_filter( 'pmpro_checkout_level', 'my_pmpro_checkout_level' );

function change_my_cost_text_discount_code( $r, $level, $tags, $short ) {

	if ( ! function_exists( 'my_pmpro_get_previous_paid_levels' ) ) {
		return $r;
	}

	global $current_user;
	$member_previous_levels_paid = my_pmpro_get_previous_paid_levels( $current_user->ID );

	if ( ! empty( $member_previous_levels_paid ) && 0 < count( $member_previous_levels_paid ) && 0 === intval( $level->initial_payment ) && ! empty( trim( $level->cycle_period ) ) ) {
		$r = '<strong>' . pmpro_formatPrice( $level->billing_amount ) . '</strong> per ' . $level->cycle_period;
	}

	return $r;

}
add_filter( 'pmpro_level_cost_text', 'change_my_cost_text_discount_code', 10, 4 );