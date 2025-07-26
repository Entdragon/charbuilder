<?php

namespace CharacterGeneratorDev {

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function monsterinsights_tools_url_builder() {
	ob_start(); ?>
	<div class="monsterinsights-upsell-under-box">
		</p>
	</div>
	// Sanitization applied above
	echo ob_get_clean(); // phpcs:ignore
}

add_action( 'monsterinsights_tools_url_builder_tab', 'monsterinsights_tools_url_builder' );

} // namespace CharacterGeneratorDev
