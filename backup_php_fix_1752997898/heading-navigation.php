<?php

namespace CharacterGeneratorDev {

/**
 * Single Payment page - Heading navigation.
 *
 * @since 1.8.2
 *
 * @var int    $count        Count of all payments.
 * @var int    $prev_count   Count of previous payments.
 * @var string $prev_url     Previous payment URL.
 * @var string $prev_class   Previous payment class.
 * @var string $next_url     Next payment URL.
 * @var string $next_class   Next payment class.
 * @var string $overview_url Payments Overview page URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
	<svg viewBox="0 0 16 14" class="page-title-action-icon">
		<path d="M16 6v2H4l4 4-1 2-7-7 7-7 1 2-4 4h12Z"/>
	</svg>
</a>

<div class="wpforms-admin-single-navigation">
	<div class="wpforms-admin-single-navigation-text">
		printf( /* translators: %1$d - current number of payment, %2$d - total number of payments. */
			esc_html__( 'Payment %1$d of %2$d', 'wpforms-lite' ),
			(int) $prev_count + 1,
			(int) $count
		);
		?>
	</div>
	<div class="wpforms-admin-single-navigation-buttons">
		<a
			id="wpforms-admin-single-navigation-prev-link"
			<span class="dashicons dashicons-arrow-left-alt2"></span>
		</a>
		<span
			class="wpforms-admin-single-navigation-current"
		</span>
		<a
			id="wpforms-admin-single-navigation-next-link"
			<span class="dashicons dashicons-arrow-right-alt2"></span>
		</a>
	</div>
</div>

} // namespace CharacterGeneratorDev
