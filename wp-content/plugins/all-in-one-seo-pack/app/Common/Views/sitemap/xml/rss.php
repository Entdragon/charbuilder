<?php

namespace CharacterGeneratorDev {

/**
 * XML template for the RSS Sitemap.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	// Yandex doesn't support some tags so we need to check the user agent.
	if ( ! aioseo()->helpers->isYandexUserAgent() ) {
		?>

		?><docs>https://validator.w3.org/feed/docs/rss2.html</docs>

foreach ( $entries as $entry ) {
		if ( empty( $entry['guid'] ) ) {
			continue;
			}?>
		<item>
			if ( ! empty( $entry['title'] ) ) {
				?>

			}
			if ( ! empty( $entry['pubDate'] ) ) {
				?>

			}
			?>

		</item>
	</channel>
</rss>

} // namespace CharacterGeneratorDev
