<?php

namespace CharacterGeneratorDev {

/**
 * Form Embed Wizard.
 * Embed popup HTML template.
 *
 * @since 1.6.2
 */

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}
$pages_exists = ! empty( $args['dropdown_pages'] ) ? 1 : 0;
?>

<div id="wpforms-admin-form-embed-wizard-container" class="wpforms-admin-popup-container">
		<div class="wpforms-admin-popup-content">
			<div id="wpforms-admin-form-embed-wizard-content-initial">

			</div>

				<div id="wpforms-admin-form-embed-wizard-content-select-page" style="display: none;">
				</div>
				<div id="wpforms-admin-form-embed-wizard-content-create-page" style="display: none;">
				</div>
				<div id="wpforms-admin-form-embed-wizard-section-btns" class="wpforms-admin-popup-bottom">
				</div>
				<div id="wpforms-admin-form-embed-wizard-section-go" class="wpforms-admin-popup-bottom wpforms-admin-popup-flex" style="display: none;">
				</div>
			<div id="wpforms-admin-form-embed-wizard-section-toggles" class="wpforms-admin-popup-bottom">
				<p class="secondary">
					$allowed_tags = [
						'a' => [
							'href'  => [],
							'class' => [],
						],
					];

					if ( ! empty( $args['user_can_edit_pages'] ) ) {

						printf(
							wp_kses( /* translators: %1$s - video tutorial toggle CSS classes, %2$s - shortcode toggle CSS classes. */
								__( 'You can also <a href="#" class="%1$s">embed your form manually</a> or <a href="#" class="%2$s">use a shortcode</a>', 'wpforms-lite' ),
								$allowed_tags
							),
							'tutorial-toggle wpforms-admin-popup-toggle',
							'shortcode-toggle wpforms-admin-popup-toggle'
						);

					} else {

						printf(
							wp_kses( /* translators: %1$s - video tutorial toggle CSS classes, %2$s - shortcode toggle CSS classes. */
								__( 'You can embed your form using the <a href="#" class="%1$s">WPForms block</a> or <a href="#" class="%2$s">a shortcode</a>.', 'wpforms-lite' ),
								$allowed_tags
							),
							'tutorial-toggle wpforms-admin-popup-toggle',
							'shortcode-toggle wpforms-admin-popup-toggle'
						);

					}
					?>
				</p>
				<iframe style="display: none;" src="about:blank" frameborder="0" id="wpforms-admin-form-embed-wizard-tutorial" allowfullscreen width="450" height="256"></iframe>
				<div id="wpforms-admin-form-embed-wizard-shortcode-wrap" style="display: none;">
					<input type="text" id="wpforms-admin-form-embed-wizard-shortcode" class="wpforms-admin-popup-shortcode" disabled />
						<i class="fa fa-files-o" aria-hidden="true"></i>
					</span>
				</div>
			</div>
			<div id="wpforms-admin-form-embed-wizard-section-goback" class="wpforms-admin-popup-bottom" style="display: none;">
				<p class="secondary">
				</p>
			</div>
		</div>
		<i class="fa fa-times wpforms-admin-popup-close"></i>
	</div>
</div>

} // namespace CharacterGeneratorDev
