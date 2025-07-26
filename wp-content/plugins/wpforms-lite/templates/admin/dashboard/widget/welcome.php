<?php

namespace CharacterGeneratorDev {

/**
 * Dashboard widget welcome message block template.
 *
 * @since 1.8.8
 *
 * @var string $welcome_message Welcome message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-dash-widget-block wpforms-dash-widget-welcome-block">
	<span class="wpforms-dash-widget-welcome">
		echo wp_kses(
			$welcome_message,
			[
				'a'      => [
					'href'  => [],
					'class' => [],
				],
				'strong' => [],
			]
		);
		?>
	</span>
		<span class="dashicons dashicons-no-alt"></span>
	</button>
</div>

} // namespace CharacterGeneratorDev
