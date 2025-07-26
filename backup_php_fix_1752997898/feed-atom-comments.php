<?php

namespace CharacterGeneratorDev {

/**
 * Atom Feed Template for displaying Atom Comments feed.
 *
 * @package WordPress
 */

header( 'Content-Type: ' . feed_content_type( 'atom' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '" ?' . '>';

/** This action is documented in wp-includes/feed-rss2.php */
do_action( 'rss_tag_pre', 'atom-comments' );
?>
<feed
	xmlns="http://www.w3.org/2005/Atom"
	xmlns:thr="http://purl.org/syndication/thread/1.0"
		/** This action is documented in wp-includes/feed-atom.php */
		do_action( 'atom_ns' );

		/**
		 * Fires inside the feed tag in the Atom comment feed.
		 *
		 * @since 2.8.0
		 */
		do_action( 'atom_comments_ns' );
	?>
>
	<title type="text">
	if ( is_singular() ) {
		/* translators: Comments feed title. %s: Post title. */
		printf( ent2ncr( __( 'Comments on %s' ) ), get_the_title_rss() );
	} elseif ( is_search() ) {
		/* translators: Comments feed title. 1: Site title, 2: Search query. */
		printf( ent2ncr( __( 'Comments for %1$s searching on %2$s' ) ), get_bloginfo_rss( 'name' ), get_search_query() );
	} else {
		/* translators: Comments feed title. %s: Site title. */
		printf( ent2ncr( __( 'Comments for %s' ) ), get_wp_title_rss() );
	}
	?>
	</title>


	/**
	 * Fires at the end of the Atom comment feed header.
	 *
	 * @since 2.8.0
	 */
	do_action( 'comments_atom_head' );
?>
while ( have_comments() ) :
	the_comment();
	$comment_post = get_post( $comment->comment_post_ID );
	/**
	 * @global WP_Post $post Global post object.
	 */
	$GLOBALS['post'] = $comment_post;
	?>
	<entry>
		<title>
		if ( ! is_singular() ) {
			$title = get_the_title( $comment_post->ID );
			/** This filter is documented in wp-includes/feed.php */
			$title = apply_filters( 'the_title_rss', $title );
			/* translators: Individual comment title. 1: Post title, 2: Comment author name. */
			printf( ent2ncr( __( 'Comment on %1$s by %2$s' ) ), $title, get_comment_author_rss() );
		} else {
			/* translators: Comment author title. %s: Comment author name. */
			printf( ent2ncr( __( 'By: %s' ) ), get_comment_author_rss() );
		}
		?>
		</title>

		<author>
			if ( get_comment_author_url() ) {
				echo '<uri>' . get_comment_author_url() . '</uri>';
			}
			?>

		</author>



		// Return comment threading information (https://www.ietf.org/rfc/rfc4685.txt).
		if ( '0' === $comment->comment_parent ) : // This comment is top-level.
			?>
		else : // This comment is in reply to another comment.
			$parent_comment = get_comment( $comment->comment_parent );
			/*
			 * The rel attribute below and the id tag above should be GUIDs,
			 * but WP doesn't create them for comments (unlike posts).
			 * Either way, it's more important that they both use the same system.
			 */
			?>
		endif;

		/**
		 * Fires at the end of each Atom comment feed item.
		 *
		 * @since 2.2.0
		 *
		 * @param int $comment_id      ID of the current comment.
		 * @param int $comment_post_id ID of the post the current comment is connected to.
		 */
		do_action( 'comment_atom_entry', $comment->comment_ID, $comment_post->ID );
		?>
	</entry>
endwhile;
?>
</feed>

} // namespace CharacterGeneratorDev
