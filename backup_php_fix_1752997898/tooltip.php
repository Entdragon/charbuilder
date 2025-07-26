<?php

namespace CharacterGeneratorDev {

/**
 * Form Embed Wizard.
 * Embed page tooltip HTML template.
 *
 * @since 1.6.2
 */

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-admin-form-embed-wizard-tooltip">
	<div id="wpforms-admin-form-embed-wizard-tooltip-content">
			<p>
				printf(
					wp_kses( /* translators: %s - link to the WPForms documentation page. */
						__( 'Click the plus button, search for WPForms, click the block to<br>embed it. <a href="%s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'wpforms-lite' ),
						[
							'a'  => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
							'br' => [],
						]
					),
					'https://wpforms.com/docs/creating-first-form/#display-form'
				);
				?>
			</p>
			<i class="wpforms-admin-form-embed-wizard-tooltips-red-arrow"></i>
	</div>
</div>

} // namespace CharacterGeneratorDev
