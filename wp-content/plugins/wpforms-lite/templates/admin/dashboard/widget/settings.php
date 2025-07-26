<?php

namespace CharacterGeneratorDev {

/**
 * Dashboard widget settings gear icon template.
 *
 * @since 1.7.4
 *
 * @var int  $graph_style  Graph style, value 1 for Bar style, 2 for Line style.
 * @var int  $color_scheme Color scheme, value 1 for WPForms color scheme, 2 for for WordPress color scheme.
 * @var bool $enabled      If form fields should be enabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$disabled = ! $enabled;

?>
<div class="wpforms-dash-widget-settings-container">
	<button id="wpforms-dash-widget-settings-button" class="wpforms-dash-widget-settings-button button" type="button">
		<span class="dashicons dashicons-admin-generic"></span>
	</button>

			<div class="wpforms-dash-widget-settings-menu-wrap">
				<div>
					<div class="wpforms-dash-widget-settings-menu-item">
					</div>
					<div class="wpforms-dash-widget-settings-menu-item">
					</div>
				</div>
			</div>

			<div class="wpforms-dash-widget-settings-menu-wrap color-scheme">
				<div>
					<div class="wpforms-dash-widget-settings-menu-item">
					</div>
					<div class="wpforms-dash-widget-settings-menu-item">
					</div>
				</div>
			</div>

	</div>
</div>

} // namespace CharacterGeneratorDev
