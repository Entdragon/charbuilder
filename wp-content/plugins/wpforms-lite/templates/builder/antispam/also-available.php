<?php

namespace CharacterGeneratorDev {

/**
 * Also Available block.
 *
 * @since 1.7.8
 *
 * @var array $blocks All educational content blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-panel-content-also-available">
	foreach ( $blocks as $key => $block ) :

		if ( empty( $block['show'] ) ) {
			continue;
		}

		$slug  = strtolower( $key );
		$class = ! empty( $block['class'] ) ? $block['class'] : '';
		?>

			<div class='wpforms-panel-content-also-available-item-logo'>
			</div>

			<div class='wpforms-panel-content-also-available-item-info'>
				   target="_blank"
				   rel="noopener noreferrer">
				</a>
			</div>
		</div>

</div>

} // namespace CharacterGeneratorDev
