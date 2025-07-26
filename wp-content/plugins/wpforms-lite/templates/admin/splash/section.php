<?php

namespace CharacterGeneratorDev {

/**
 * What's New modal section.
 *
 * @since 1.8.7
 *
 * @var string $title Section title.
 * @var string $content Section content.
 * @var array $img Section image.
 * @var string $new Is new feature.
 * @var array $buttons Section buttons.
 * @var string $layout Section layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$classes = [
	'wpforms-splash-section',
	'wpforms-splash-section-' . $layout,
];
?>

	<div class="wpforms-splash-section-content">
		if ( ! empty( $new ) ) {
			printf(
				'<span class="wpforms-splash-badge">%s</span>',
				esc_html__( 'New Feature', 'wpforms-lite' )
			);
		}
		?>

			<div class="wpforms-splash-section-buttons">
				foreach ( $buttons as $button_type => $button ) {
					$button_class = $button_type === 'main' ? 'wpforms-btn-orange' : 'wpforms-btn-bordered';

					printf(
						'<a href="%1$s" class="wpforms-btn %3$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
						esc_url( $button['url'] ),
						esc_html( $button['text'] ),
						esc_attr( $button_class )
					);
				}
				?>
			</div>
	</div>

		</div>
</section>

} // namespace CharacterGeneratorDev
