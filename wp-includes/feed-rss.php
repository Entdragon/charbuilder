<?php

namespace CharacterGeneratorDev {

/**
 * RSS 0.92 Feed Template for displaying RSS 0.92 Posts feed.
 *
 * @package WordPress
 */

header( 'Content-Type: ' . feed_content_type( 'rss' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; ?>
<rss version="0.92">
<channel>
	<docs>http://backend.userland.com/rss092</docs>
	/**
	 * Fires at the end of the RSS Feed Header.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss_head' );
	?>

while ( have_posts() ) :
	the_post();
	?>
	<item>
		/**
		 * Fires at the end of each RSS feed item.
		 *
		 * @since 2.0.0
		 */
		do_action( 'rss_item' );
		?>
	</item>
</channel>
</rss>

} // namespace CharacterGeneratorDev
