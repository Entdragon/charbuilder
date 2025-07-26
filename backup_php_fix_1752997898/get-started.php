<?php

namespace CharacterGeneratorDev {

/**
 * Get Started HTML template.
 *
 * @since 1.8.2
 *
 * @var string $message An abort message to display.
 * @var string $version Determine whether is pro or lite version.
 * @var string $cta_url URL for the "Get Started" CTA button.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_html = [
	'a'      => [
		'href'   => [],
		'rel'    => [],
		'target' => [],
	],
	'strong' => [],
];

?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-payments">

	</a>

	<p class="wpforms-admin-no-forms-footer">
		printf(
			wp_kses( /* translators: %s - URL to the comprehensive guide. */
				__( 'Need some help? Check out our <a href="%s" rel="noopener noreferrer" target="_blank">comprehensive guide.</a>', 'wpforms-lite' ),
				$allowed_html
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/using-stripe-with-wpforms-lite/',
					'Payments Dashboard',
					'Splash - Manage Payments Documentation'
				)
			)
		);
	?>
	</p>
</div>

} // namespace CharacterGeneratorDev
