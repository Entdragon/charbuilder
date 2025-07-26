<?php

namespace CharacterGeneratorDev {

/**
 * Atom Feed Template for displaying Atom Posts feed.
 *
 * @package WordPress
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

header( 'Content-Type: ' . feed_content_type( 'atom' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';

/** This action is documented in wp-includes/feed-rss2.php */
do_action( 'rss_tag_pre', 'atom' );
?>
<feed
	xmlns="http://www.w3.org/2005/Atom"
	xmlns:thr="http://purl.org/syndication/thread/1.0"
	/**
	 * Fires at end of the Atom feed root to add namespaces.
	 *
	 * @since 2.0.0
	 */
	do_action( 'atom_ns' );
	?>
>



	/**
	 * Fires just before the first Atom feed entry.
	 *
	 * @since 2.0.0
	 */
	do_action( 'atom_head' );

	while ( have_posts() ) :
		the_post();
		?>
	<entry>
		<author>
			$author_url = get_the_author_meta( 'url' );
			if ( ! empty( $author_url ) ) :
				?>
			endif;

			/**
			 * Fires at the end of each Atom feed author entry.
			 *
			 * @since 3.2.0
			 */
			do_action( 'atom_author' );
			?>
		</author>





		atom_enclosure();

		/**
		 * Fires at the end of each Atom feed item.
		 *
		 * @since 2.0.0
		 */
		do_action( 'atom_entry' );

		if ( get_comments_number() || comments_open() ) :
			?>
	</entry>
</feed>

} // namespace CharacterGeneratorDev
