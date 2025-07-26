<?php

namespace CharacterGeneratorDev {

/**
 * This is the output for social meta on the page.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable Generic.WhiteSpace.ScopeIndent

// Set context for meta class to social meta.
$facebookMeta = aioseo()->social->output->getFacebookMeta();
foreach ( $facebookMeta as $key => $meta ) :
	// Each article tag needs to be output in a separate meta tag so we cast and loop over each key.
	if ( ! is_array( $meta ) ) {
		$meta = [ $meta ];
	}
	foreach ( $meta as $m ) :
	?>
	endforeach;
endforeach;

$twitterMeta = aioseo()->social->output->getTwitterMeta();
foreach ( $twitterMeta as $key => $meta ) :
?>
endforeach;
} // namespace CharacterGeneratorDev
