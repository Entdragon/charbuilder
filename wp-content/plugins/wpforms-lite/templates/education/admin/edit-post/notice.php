<?php

namespace CharacterGeneratorDev {

/**
 * Gutenberg Editor notice for Edit Post Education template.
 *
 * @since 1.8.1
 *
 * @var string $message Notice message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpforms-edit-post-education-notice-body">
	<p>
	</p>
	<p>
		echo wp_kses(
			$message,
			[
				'a' => [
					'href'   => [],
					'target' => [],
					'rel'    => [],
				],
			]
		);
		?>
	</p>
</div>

} // namespace CharacterGeneratorDev
