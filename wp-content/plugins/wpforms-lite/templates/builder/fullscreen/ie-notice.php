<?php

namespace CharacterGeneratorDev {

/**
 * Form Builder IE / unsupported browser notice template.
 *
 * @since 1.7.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id='wpforms-builder-ie-notice' class='wpforms-fullscreen-notice'>


	<p>
		printf(
			wp_kses( /* translators: %1$s - link to the update Internet Explorer page, %2$s - link to the browse happy page. */
				__( 'The Internet Explorer browser no more supported.<br>Our form builder is optimized for modern browsers.<br>Please <a href="%1$s" target="_blank" rel="nofollow noopener">install Microsoft Edge</a> or learn<br>how to <a href="%2$s" target="_blank" rel="nofollow noopener">browse happy</a>.', 'wpforms-lite' ),
				[
					'a'  => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
					'br' => [],
				]
			),
			'https://www.microsoft.com/en-us/edge',
			'https://browsehappy.com/'
		);
		?>
	</p>

	<div class="wpforms-fullscreen-notice-buttons">
		</a>
	</div>

</div>

} // namespace CharacterGeneratorDev
