<?php

namespace CharacterGeneratorDev {

/**
 * Admin/Integrations item Education template for Lite.
 *
 * @since 1.6.6
 *
 * @var string $clear_slug    Clear slug (without `wpforms-` prefix).
 * @var string $modal_name    Name of the addon used in modal window.
 * @var string $license_level License level.
 * @var string $name          Name of the addon.
 * @var string $icon          Addon icon.
 * @var string $video         Video URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
	class="wpforms-settings-provider wpforms-clear focus-out education-modal"
	data-action="upgrade"
	<div class="wpforms-settings-provider-header wpforms-clear">
		<div class="wpforms-settings-provider-logo ">
			<i class="fa fa-chevron-right"></i>
		</div>
		<div class="wpforms-settings-provider-info">
			<p>
			printf( /* translators: %s - provider name. */
				esc_html__( 'Integrate %s with WPForms', 'wpforms-lite' ),
				esc_html( $name )
			);
			?>
			</p>
		</div>
	</div>
</div>

} // namespace CharacterGeneratorDev
