<?php

namespace CharacterGeneratorDev {

/**
 * No forms HTML template.
 *
 * @since 1.6.2.3
 */

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-forms">




	<br>


	<p class="wpforms-admin-no-forms-footer">
		printf(
			wp_kses( /* translators: %s - URL to the documentation article. */
				__( 'Need some help? Check out our <a href="%s" target="_blank" rel="noopener noreferrer">comprehensive guide</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/creating-first-form/', 'forms-overview', 'Create Your First Form Documentation' ) )
		);
		?>
	</p>

</div>

} // namespace CharacterGeneratorDev
