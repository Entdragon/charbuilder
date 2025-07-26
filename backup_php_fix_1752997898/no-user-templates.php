<?php

namespace CharacterGeneratorDev {

/**
 * User Templates Empty State Template.
 *
 * @since 1.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-user-templates">

	<h2 class="waving-hand-emoji">
	</h2>



	<p class="wpforms-admin-no-forms-footer">
		printf(
			wp_kses( /* translators: %s - URL to the documentation article. */
				__( 'Need some help? Check out our <a href="%s" rel="noopener noreferrer" target="_blank">documentation</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/how-to-create-a-custom-form-template/',
					wpforms_is_admin_page( 'builder' ) ? 'builder-templates' : 'Form Templates Subpage',
					'User Templates Documentation'
				)
			)
		);
		?>
	</p>

</div>

} // namespace CharacterGeneratorDev
