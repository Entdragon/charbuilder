<?php

namespace CharacterGeneratorDev {

/**
 * Payments overview reports (summary metrics).
 * i.e. Total Payments, Total Sales, etc.
 *
 * @since 1.8.2
 *
 * @var string $current   The active stat card upon page load.
 * @var array  $statcards Payments report stat cards (clickable list-items).
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Bail early, if stat cards are empty or not assigned.
if ( empty( $statcards ) ) {
	return;
}

?>
	<p id="wpforms-payments-overview-reports-helptext" class="screen-reader-text">
	</p>
	<ul class="wpforms-payments-overview-reports-legend">

		foreach ( $statcards as $chart => $attributes ) :

			// Skip stat card, if it's not supposed to be displayed.
			if ( isset( $attributes['condition'] ) && ! $attributes['condition'] ) {
				continue;
			}

			$button_classes = ! empty( $attributes['button_classes'] ) ? (array) $attributes['button_classes'] : [];

			// To highlight the stats being displayed in the chart at the moment, identify the selected stat card.
			if ( $chart === $current ) {
				$button_classes[] = 'is-selected';
			}
		?>
			<li class="wpforms-payments-overview-reports-statcard">
				</button>
			</li>

	</ul>
</div>

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

} // namespace CharacterGeneratorDev
