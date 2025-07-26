<?php

namespace CharacterGeneratorDev {

/**
 * Single Payment page - Advanced details template.
 *
 * @since 1.8.2
 *
 * @var array $details_list Details list.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wpforms-payment-advanced-info" class="postbox">

	<div class="postbox-header">
		<h2 class="hndle">
		</h2>
	</div>

	<div class="inside">

		foreach ( $details_list as $item ) :
			?>

			<div class="wpforms-payment-advanced-item" >

				<p class="wpforms-payment-advanced-item-label">
				</p>

				<div class="wpforms-payment-advanced-item-value">
						</a>
				</div>
			</div>
	</div>
</div>

} // namespace CharacterGeneratorDev
