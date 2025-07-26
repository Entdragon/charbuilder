<?php

namespace CharacterGeneratorDev {

/**
 * RSS 1 RDF Feed Template for displaying RSS 1 Posts feed.
 *
 * @package WordPress
 */

header( 'Content-Type: ' . feed_content_type( 'rdf' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';

/** This action is documented in wp-includes/feed-rss2.php */
do_action( 'rss_tag_pre', 'rdf' );
?>
<rdf:RDF
	xmlns="http://purl.org/rss/1.0/"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:admin="http://webns.net/mvcb/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	/**
	 * Fires at the end of the feed root to add namespaces.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rdf_ns' );
	?>
>
	<sy:updatePeriod>
		/** This filter is documented in wp-includes/feed-rss2.php */
		echo apply_filters( 'rss_update_period', 'hourly' );
	?>
	</sy:updatePeriod>
	<sy:updateFrequency>
		/** This filter is documented in wp-includes/feed-rss2.php */
		echo apply_filters( 'rss_update_frequency', '1' );
	?>
	</sy:updateFrequency>
	<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
	/**
	 * Fires at the end of the RDF feed header.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rdf_header' );
	?>
	<items>
		<rdf:Seq>
		while ( have_posts() ) :
			the_post();
			?>
		</rdf:Seq>
	</items>
</channel>
rewind_posts();
while ( have_posts() ) :
	the_post();
	?>



	/**
	 * Fires at the end of each RDF feed item.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rdf_item' );
	?>
</item>
</rdf:RDF>

} // namespace CharacterGeneratorDev
