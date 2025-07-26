<?php

namespace CharacterGeneratorDev {

/**
 * Overview chart template.
 *
 * @since 1.8.2
 *
 * @var string $id          Identifier to outline the context of where the chart will be used. i.e., "entries".
 * @var array  $notice      Container variable for holding placeholder heading and description text that is displayed when the chart has no stats to show.
 * @var string $total_items Total number of items (entries) as a placeholder text to be shown.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<div class="wpforms-overview-chart">
	<div class="spinner"></div>
	<div class="wpforms-overview-chart-notice wpforms-hide">
		<div class="wpforms-overview-chart-notice-content">
		</div>
	</div>

		<h3 class="wpforms-overview-chart-heading">
			<span class="wpforms-overview-chart-total-items">0</span>
		</h3>

	<div class="wpforms-overview-chart-canvas">
	</div>
</div>

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

} // namespace CharacterGeneratorDev
