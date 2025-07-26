<?php

namespace CharacterGeneratorDev {

/**
 * Builder/DidYouKnow Education template for Lite.
 *
 * @since 1.6.6
 *
 * @var string $desc    Message body.
 * @var string $more    Learn More button URL.
 * @var string $link    Upgrade to Pro page URL.
 * @var string $section The slug of the dismissible section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<section class="wpforms-dyk wpforms-dismiss-container">
	<div class="wpforms-dyk-fbox wpforms-dismiss-out">
		<div class="wpforms-dyk-message">
		</div>
		<div class="wpforms-dyk-buttons">
			if ( ! empty( $more ) ) {
				echo '<a href="' . esc_url( $more ) . '" class="learn-more">' . esc_html__( 'Learn More', 'wpforms-lite' ) . '</a>';
			}
			?>
		</div>
	</div>
</section>

} // namespace CharacterGeneratorDev
