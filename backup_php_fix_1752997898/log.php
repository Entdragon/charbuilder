<?php

namespace CharacterGeneratorDev {

/**
 * Single Payment page - Log metabox.
 *
 * @since 1.8.2
 *
 * @var array $logs Logs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wpforms-payment-logs" class="postbox">

	<div class="postbox-header">
		<h2 class="hndle">
		</h2>
	</div>

	<div class="inside">


		foreach ( $logs as $log ) :

			$item      = json_decode( $log['value'], false );
			$date_time = sprintf( /* translators: %1$s - date, %2$s - time when item was created, e.g. "Oct 22, 2022 at 11:11 am". */
				__( '%1$s at %2$s', 'wpforms-lite' ),
				wpforms_date_format( $item->date, 'M j, Y', true ),
				wpforms_time_format( $item->date, '', true )
			);

			if ( empty( $item->value ) ) {
				continue;
			}
			?>

			<div class="wpforms-payment-log-item" >

				<span class="wpforms-payment-log-item-value">
				</span>

				<span class="wpforms-payment-log-item-date">
				</span>
			</div>
	</div>
</div>

} // namespace CharacterGeneratorDev
