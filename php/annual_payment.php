<?php

/**
 * Enqueue additional stylesheet.
 *
 * @return void
 */
function pmproc_preheader() {
    if ( ! is_admin() ) {
        wp_enqueue_style( 'pmproc_stylesheet', plugins_url( 'css/pmpro-customizations.css', __FILE__ ),
        array(), '1.0', 'all' );
    }
}
add_action( 'wp_enqueue_scripts', 'pmproc_preheader', 1 );

//Now start placing your customization code below this line

// Define the groups of levels. array(1,2) means that levels 1 and 2 are in a group and options will be shown for both levels at checkout for those levels.
global $pmpro_level_groups;
$pmpro_level_groups = array( array( 5,7 ), array( 6,8 ) );
// Show the "Select a Payment Plan" box with options at checkout.
function pmpro_level_groups_pmpro_checkout_boxes() {
	global $pmpro_level_groups, $pmpro_level, $discount_code, $wpdb;
	// No groups found? return.
	if ( empty( $pmpro_level_groups) || empty( $pmpro_level ) ) {
		return;
	}
	// Get the id for the discount code if available.
    if ( ! empty( $discount_code ) ) {
		$discount_code_id = $wpdb->get_var("SELECT id FROM $wpdb->pmpro_discount_codes WHERE code = '" . $discount_code . "' LIMIT 1");
	}
	// Get first group this level is in.
	foreach( $pmpro_level_groups as $group ) {
		if ( in_array( $pmpro_level->id, $group ) ) {
			// Show options for these levels. ?>
			<div id="pmpro_level_options" class="pmpro_checkout">
				<h3><span class="pmpro_checkout-h3-name"><?php esc_attr_e( 'Select a payment plan.', 'paid-memberships-pro' ); ?></span></h3>
				<div class="pmpro_checkout-fields">
					<div class="pmpro_checkout-field pmpro_checkout-field-radio">
						<?php foreach( $group as $level_id ) {
								$level = pmpro_getLevel( $level_id);
								// Apply discount code if available.
							    if ( ! empty( $discount_code ) ) {
								    $code_check = pmpro_checkDiscountCode( $discount_code, $level_id, true );
								    if ( $code_check[0] == false ) {
								       // Discount code doesn't apply to this level.
								    } else {
								        $sqlQuery = "SELECT l.id, cl.*, l.name, l.description, l.allow_signups FROM $wpdb->pmpro_discount_codes_levels cl LEFT JOIN $wpdb->pmpro_membership_levels l ON cl.level_id = l.id LEFT JOIN $wpdb->pmpro_discount_codes dc ON dc.id = cl.code_id WHERE dc.code = '" . $discount_code . "' AND cl.level_id = '" . (int)$level_id . "' LIMIT 1";
								        $level = $wpdb->get_row( $sqlQuery );
								        // If the discount code doesn't adjust the level, let's just get the straight level.
								        if ( empty( $level ) ) {
								            $level = $wpdb->get_row("SELECT * FROM $wpdb->pmpro_membership_levels WHERE id = '" . $level_id . "'");
										}
								        // Filter adjustments to the level.
								        $level->code_id = $discount_code_id;
								        $level = apply_filters( 'pmpro_discount_code_level', $level, $discount_code_id );
								    }
								}
								// Apply filters.
								$level = apply_filters( 'pmpro_checkout_level', $level ); ?>
								<div class="pmpro_checkout-field-radio-item">
									<input type="radio" id="pmpro_level_<?php echo $level_id; ?>" class="pmpro_payment_plan" name="level" value="<?php echo $level_id; ?>" <?php checked( $pmpro_level->id, $level_id );?>>
									<label class="pmpro_label-inline" for="pmpro_level_<?php echo $level_id; ?>" /><?php echo pmpro_getLevelCost( $level, false, true );?></label>
								</div>
								<?php
							}
						?>
					</div> <!-- end pmpro_checkout-field-radio -->
				</div> <!-- end pmpro_checkout-fields -->
			</div> <!-- end pmpro_level_options -->
			<?php
		}
	}
	?>
	<script>
	jQuery(document).ready(function($) {
		$('.pmpro_payment_plan').click(function() {
			label = $("label[for='" + $(this).attr('id') + "']");
			cost = label.html();
			$('#pmpro_level_cost p').html("The price for membership is <strong>"+cost+"</strong>");
		});
	})
	</script>
	<?php
}
add_action('pmpro_checkout_boxes', 'pmpro_level_groups_pmpro_checkout_boxes');