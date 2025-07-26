<?php

namespace CharacterGeneratorDev {

/**
 * Challenge main modal window template.
 *
 * @since 1.6.2
 *
 * @var string  $state
 * @var integer $step
 * @var integer $minutes
 */

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

?>

	<div class="wpforms-challenge-list-block">
		<p>
			echo wp_kses(
				sprintf(
					/* translators: %1$d - number of minutes, %2$s - singular or plural form of 'minute'. */
					__( 'Complete the <b>WPForms Challenge</b> and get up and running within %1$d&nbsp;%2$s.', 'wpforms-lite' ),
					absint( $minutes ),
					_n( 'minute', 'minutes', absint( $minutes ), 'wpforms-lite' )
				),
				[ 'b' => [] ]
			);
			?>
		</p>
		<ul class="wpforms-challenge-list">
		</ul>
	</div>

	<div class="wpforms-challenge-bar" style="display:none">
		<div></div>
	</div>

	<div class="wpforms-challenge-block-timer">
		<div>
			<p>
				printf(
					/* translators: %s - minutes in 2:00 format. */
					esc_html__( '%s remaining', 'wpforms-lite' ),
					'<span id="wpforms-challenge-timer">' . absint( $minutes ) . ':00</span>'
				);
				?>
			</p>
		</div>
	</div>

	<div class="wpforms-challenge-block-under-timer">
			</a>
	</div>
</div>

} // namespace CharacterGeneratorDev
