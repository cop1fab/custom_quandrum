
<?php
function my_pmpro_levels_array( $levels ) {
	// Get all the levels
	$levels = pmpro_getAllLevels( false, true );

	// Set which levels to hide from the levels page.
	$hidden_levels = array( 7, 8 ); // Level IDs of  the levels to exclude/hide.

	foreach ( $hidden_levels as $hidden_level ) {
		if ( array_key_exists( $hidden_level, $levels ) ) {
			unset( $levels[ $hidden_level ] );
		}
	}

	return $levels;
}
add_filter( 'pmpro_levels_array', 'my_pmpro_levels_array', 10 );