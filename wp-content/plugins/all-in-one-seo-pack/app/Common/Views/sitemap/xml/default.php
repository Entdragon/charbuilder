<?php

namespace CharacterGeneratorDev {

/**
 * XML template for our sitemap index pages.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>
<urlset
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
>
	if ( empty( $entry['loc'] ) ) {
		continue;
	}
	?>
	<url>
	if ( ! empty( $entry['lastmod'] ) ) {
			?>

	}
	if ( ! empty( $entry['changefreq'] ) ) {
			?>

	}
	if ( isset( $entry['priority'] ) ) {
			?>

	}
	if ( ! empty( $entry['languages'] ) ) {
		foreach ( $entry['languages'] as $subentry ) {
			if ( empty( $subentry['language'] ) || empty( $subentry['location'] ) ) {
				continue;
			}
		?>

		}
	}
	if ( ! aioseo()->sitemap->helpers->excludeImages() && ! empty( $entry['images'] ) ) {
			foreach ( $entry['images'] as $image ) {
				$image = (array) $image;
			?>

		<image:image>
			<image:loc>
				if ( aioseo()->helpers->isRelativeUrl( $image['image:loc'] ) ) {
					$image['image:loc'] = aioseo()->helpers->makeUrlAbsolute( $image['image:loc'] );
				}

				aioseo()->sitemap->output->escapeAndEcho( $image['image:loc'] );
				?>
			</image:loc>
		}
	}
	?>

	</url>
</urlset>

} // namespace CharacterGeneratorDev
