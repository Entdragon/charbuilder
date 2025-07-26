<?php

namespace CharacterGeneratorDev {

/**
 * Edit comment form for inclusion in another file.
 *
 * @package WordPress
 * @subpackage Administration
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @global WP_Comment $comment Global comment object.
 */
global $comment;
?>
<form name="post" action="comment.php" method="post" id="post">
<div class="wrap">

<div id="poststuff">
<input type="hidden" name="action" value="editedcomment" />

<div id="post-body" class="metabox-holder columns-2">
<div id="post-body-content" class="edit-form-section edit-comment-section">
if ( 'approved' === wp_get_comment_status( $comment ) && $comment->comment_post_ID > 0 ) :
	$comment_link = get_comment_link( $comment );
	?>
<div class="inside">
	<div id="comment-link-box">
		<span id="sample-permalink">
			</a>
		</span>
	</div>
</div>
<div id="namediv" class="stuffbox">
<div class="inside">
<fieldset>
<legend class="screen-reader-text">
	/* translators: Hidden accessibility text. */
	_e( 'Comment Author' );
	?>
</legend>
<table class="form-table editcomment" role="presentation">
<tbody>
<tr>
</tr>
<tr>
	<td>
	</td>
</tr>
<tr>
	<td>
	</td>
</tr>
</tbody>
</table>
</fieldset>
</div>
</div>

<div id="postdiv" class="postarea">
<label for="content" class="screen-reader-text">
	/* translators: Hidden accessibility text. */
	_e( 'Comment' );
	?>
</label>
	$quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' );
	wp_editor(
		$comment->comment_content,
		'content',
		array(
			'media_buttons' => false,
			'tinymce'       => false,
			'quicktags'     => $quicktags_settings,
		)
	);
	wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
	?>
</div>
</div><!-- /post-body-content -->

<div id="postbox-container-1" class="postbox-container">
<div id="submitdiv" class="stuffbox" >
<div class="inside">
<div class="submitbox" id="submitcomment">
<div id="minor-publishing">

<div id="misc-publishing-actions">

<div class="misc-pub-section misc-pub-comment-status" id="comment-status">
switch ( $comment->comment_approved ) {
	case '1':
		_e( 'Approved' );
		break;
	case '0':
		_e( 'Pending' );
		break;
	case 'spam':
		_e( 'Spam' );
		break;
}
?>
</span>

<fieldset id="comment-status-radio">
<legend class="screen-reader-text">
	/* translators: Hidden accessibility text. */
	_e( 'Comment status' );
	?>
</legend>
</fieldset>
</div><!-- .misc-pub-section -->

<div class="misc-pub-section curtime misc-pub-curtime">
$submitted = sprintf(
	/* translators: 1: Comment date, 2: Comment time. */
	__( '%1$s at %2$s' ),
	/* translators: Publish box date format, see https://www.php.net/manual/datetime.format.php */
	date_i18n( _x( 'M j, Y', 'publish box date format' ), strtotime( $comment->comment_date ) ),
	/* translators: Publish box time format, see https://www.php.net/manual/datetime.format.php */
	date_i18n( _x( 'H:i', 'publish box time format' ), strtotime( $comment->comment_date ) )
);
?>
<span id="timestamp">
/* translators: %s: Comment date. */
printf( __( 'Submitted on: %s' ), '<b>' . $submitted . '</b>' );
?>
</span>
	/* translators: Hidden accessibility text. */
	_e( 'Edit date and time' );
	?>
</span></a>
<fieldset id='timestampdiv' class='hide-if-js'>
<legend class="screen-reader-text">
	/* translators: Hidden accessibility text. */
	_e( 'Date and time' );
	?>
</legend>
/**
 * @global string $action
 */
global $action;

touch_time( ( 'editcomment' === $action ), 0 );
?>
</fieldset>
</div>

$post_id = $comment->comment_post_ID;
if ( current_user_can( 'edit_post', $post_id ) ) {
	$post_link  = "<a href='" . esc_url( get_edit_post_link( $post_id ) ) . "'>";
	$post_link .= esc_html( get_the_title( $post_id ) ) . '</a>';
} else {
	$post_link = esc_html( get_the_title( $post_id ) );
}
?>

<div class="misc-pub-section misc-pub-response-to">
	printf(
		/* translators: %s: Post link. */
		__( 'In response to: %s' ),
		'<b>' . $post_link . '</b>'
	);
	?>
</div>

if ( $comment->comment_parent ) :
	$parent = get_comment( $comment->comment_parent );
	if ( $parent ) :
		$parent_link = esc_url( get_comment_link( $parent ) );
		$name        = get_comment_author( $parent );
		?>
	<div class="misc-pub-section misc-pub-reply-to">
		printf(
			/* translators: %s: Comment link. */
			__( 'In reply to: %s' ),
			'<b><a href="' . $parent_link . '">' . $name . '</a></b>'
		);
		?>
	</div>
endif;
endif;
?>

	/**
	 * Filters miscellaneous actions for the edit comment form sidebar.
	 *
	 * @since 4.3.0
	 *
	 * @param string     $html    Output HTML to display miscellaneous action.
	 * @param WP_Comment $comment Current comment object.
	 */
	echo apply_filters( 'edit_comment_misc_actions', '', $comment );
?>

</div> <!-- misc actions -->
<div class="clear"></div>
</div>

<div id="major-publishing-actions">
<div id="delete-action">
</div>
<div id="publishing-action">
</div>
<div class="clear"></div>
</div>
</div>
</div>
</div><!-- /submitdiv -->
</div>

<div id="postbox-container-2" class="postbox-container">
/** This action is documented in wp-admin/includes/meta-boxes.php */
do_action( 'add_meta_boxes', 'comment', $comment );

/**
 * Fires when comment-specific meta boxes are added.
 *
 * @since 3.0.0
 *
 * @param WP_Comment $comment Comment object.
 */
do_action( 'add_meta_boxes_comment', $comment );

do_meta_boxes( null, 'normal', $comment );

$referer = wp_get_referer();
?>
</div>

<input type="hidden" name="noredir" value="1" />

</div><!-- /post-body -->
</div>
</div>
</form>

<script type="text/javascript">
try{document.post.name.focus();}catch(e){}
</script>
endif;

} // namespace CharacterGeneratorDev
