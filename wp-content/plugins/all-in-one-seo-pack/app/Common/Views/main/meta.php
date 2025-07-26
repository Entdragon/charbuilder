<?php

namespace CharacterGeneratorDev {

/**
 * This is the output for meta on the page.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
$description = aioseo()->helpers->encodeOutputHtml( aioseo()->meta->description->getDescription() );
$robots      = aioseo()->meta->robots->meta();
$keywords    = $this->keywords->getKeywords();
$canonical   = aioseo()->helpers->canonicalUrl();
$links       = $this->links->getLinks();
$postType    = get_post_type();
$post        = aioseo()->helpers->getPost();
?>
endif;
if (
	apply_filters( 'aioseo_author_meta', true ) &&
	! is_page() &&
	post_type_supports( $postType, 'author' ) &&
	! empty( $post->post_author ) &&
	! empty( get_the_author_meta( 'display_name', $post->post_author ) )
) :
	?>
endif;
?>

// This adds the miscellaneous verification to the head tag inside our comments.
// @TODO: [V4+] Maybe move this out of meta? Better idea would be to have a global wp_head where meta gets
// attached as well as other things like this:
$miscellaneous = aioseo()->helpers->decodeHtmlEntities( aioseo()->options->webmasterTools->miscellaneousVerification );
$miscellaneous = trim( $miscellaneous );
if ( ! empty( $miscellaneous ) ) {
	echo "\n\t\t$miscellaneous\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
} // namespace CharacterGeneratorDev
