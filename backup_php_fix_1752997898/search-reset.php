<?php

namespace CharacterGeneratorDev {

/**
 * Search reset block on forms overview page.
 *
 * @since 1.7.2
 *
 * @var string $message Message to display inside the Search reset block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="wpforms-reset-filter">
	echo wp_kses(
		$message,
		[
			'strong' => [],
			'em'     => [],
		]
	);
	?>
</div>

} // namespace CharacterGeneratorDev
