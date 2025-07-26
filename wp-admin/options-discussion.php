<?php

namespace CharacterGeneratorDev {

/**
 * Discussion settings administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */
/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Sorry, you are not allowed to manage options for this site.' ) );
}

// Used in the HTML title tag.
$title       = __( 'Discussion Settings' );
$parent_file = 'options-general.php';

add_action( 'admin_print_footer_scripts', 'options_discussion_add_js' );

get_current_screen()->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' => '<p>' . __( 'This screen provides many options for controlling the management and display of comments and links to your posts/pages. So many, in fact, they will not all fit here! :) Use the documentation links to get information on what each discussion setting does.' ) . '</p>' .
			'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ) . '</p>',
	)
);

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://wordpress.org/documentation/article/settings-discussion-screen/">Documentation on Discussion Settings</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/forums/">Support forums</a>' ) . '</p>'
);

require_once ABSPATH . 'wp-admin/admin-header.php';
?>

<div class="wrap">

<form method="post" action="options.php">

<table class="form-table indent-children" role="presentation">
<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Default post settings' );
	?>
</span></legend>
<label for="default_pingback_flag">
<br />
<label for="default_ping_status">
<br />
<label for="default_comment_status">
<br />
</fieldset></td>
</tr>
<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Other comment settings' );
	?>
</span></legend>
<br />
<label for="comment_registration">
if ( ! get_option( 'users_can_register' ) && is_multisite() ) {
	echo ' ' . __( '(Signup has been disabled. Only members of this site can comment.)' );
}
?>
</label>
<br />
<ul>
	<li>
	</li>
</ul>

<br />

/**
 * Filters the maximum depth of threaded/nested comments.
 *
 * @since 2.7.0
 *
 * @param int $max_depth The maximum depth of threaded comments. Default 10.
 */
$maxdeep = (int) apply_filters( 'thread_comments_depth_max', 10 );

$thread_comments_depth = '<select name="thread_comments_depth" id="thread_comments_depth">';
for ( $i = 2; $i <= $maxdeep; $i++ ) {
	$thread_comments_depth .= "<option value='" . esc_attr( $i ) . "'";
	if ( (int) get_option( 'thread_comments_depth' ) === $i ) {
		$thread_comments_depth .= " selected='selected'";
	}
	$thread_comments_depth .= ">$i</option>";
}
$thread_comments_depth .= '</select>';
?>
<ul>
	<li>
	</li>
</ul>
</fieldset></td>
</tr>

<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Comment Pagination' );
	?>
</span></legend>
<ul>
	<li>
	</li>
	<li>
		<select name="default_comments_page" id="default_comments_page">
		</select>
	</li>
	<li>
		<select name="comment_order" id="comment_order">
		</select>
	</li>
</ul>
</fieldset></td>
</tr>
<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Email me whenever' );
	?>
</span></legend>
<label for="comments_notify">
<br />
<label for="moderation_notify">
</fieldset></td>
</tr>
<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Before a comment appears' );
	?>
</span></legend>
<label for="comment_moderation">
<br />
</fieldset></td>
</tr>
<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Comment Moderation' );
	?>
</span></legend>
<p><label for="comment_max_links">
printf(
	/* translators: %s: Number of links. */
	__( 'Hold a comment in the queue if it contains %s or more links. (A common characteristic of comment spam is a large number of hyperlinks.)' ),
	'<input name="comment_max_links" type="number" step="1" min="0" id="comment_max_links" value="' . esc_attr( get_option( 'comment_max_links' ) ) . '" class="small-text" />'
);
?>
</label></p>

<p>
</p>
</fieldset></td>
</tr>
<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Disallowed Comment Keys' );
	?>
</span></legend>
<p>
</p>
</fieldset></td>
</tr>
</table>



// The above would be a good place to link to the documentation on the Gravatar functions, for putting it in themes. Anything like that?

$show_avatars       = get_option( 'show_avatars' );
$show_avatars_class = '';
if ( ! $show_avatars ) {
	$show_avatars_class = ' hide-if-js';
}
?>

<table class="form-table" role="presentation">
<tr>
<td>
	<label for="show_avatars">
	</label>
</td>
</tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Maximum Rating' );
	?>
</span></legend>

$ratings = array(
	/* translators: Content suitability rating: https://en.wikipedia.org/wiki/Motion_Picture_Association_of_America_film_rating_system */
	'G'  => __( 'G &#8212; Suitable for all audiences' ),
	/* translators: Content suitability rating: https://en.wikipedia.org/wiki/Motion_Picture_Association_of_America_film_rating_system */
	'PG' => __( 'PG &#8212; Possibly offensive, usually for audiences 13 and above' ),
	/* translators: Content suitability rating: https://en.wikipedia.org/wiki/Motion_Picture_Association_of_America_film_rating_system */
	'R'  => __( 'R &#8212; Intended for adult audiences above 17' ),
	/* translators: Content suitability rating: https://en.wikipedia.org/wiki/Motion_Picture_Association_of_America_film_rating_system */
	'X'  => __( 'X &#8212; Even more mature than above' ),
);
foreach ( $ratings as $key => $rating ) :
	$selected = ( get_option( 'avatar_rating' ) === $key ) ? 'checked="checked"' : '';
	echo "\n\t<label><input type='radio' name='avatar_rating' value='" . esc_attr( $key ) . "' $selected/> $rating</label><br />";
endforeach;
?>

</fieldset></td>
</tr>
<td class="defaultavatarpicker"><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Default Avatar' );
	?>
</span></legend>

<p>
</p>

$avatar_defaults = array(
	'mystery'          => __( 'Mystery Person' ),
	'blank'            => __( 'Blank' ),
	'gravatar_default' => __( 'Gravatar Logo' ),
	'identicon'        => __( 'Identicon (Generated)' ),
	'wavatar'          => __( 'Wavatar (Generated)' ),
	'monsterid'        => __( 'MonsterID (Generated)' ),
	'retro'            => __( 'Retro (Generated)' ),
	'robohash'         => __( 'RoboHash (Generated)' ),
);
/**
 * Filters the default avatars.
 *
 * Avatars are stored in key/value pairs, where the key is option value,
 * and the name is the displayed avatar name.
 *
 * @since 2.6.0
 *
 * @param string[] $avatar_defaults Associative array of default avatars.
 */
$avatar_defaults = apply_filters( 'avatar_defaults', $avatar_defaults );
$default         = get_option( 'avatar_default', 'mystery' );
$avatar_list     = '';

// Force avatars on to display these choices.
add_filter( 'pre_option_show_avatars', '__return_true', 100 );

foreach ( $avatar_defaults as $default_key => $default_name ) {
	$selected     = ( $default === $default_key ) ? 'checked="checked" ' : '';
	$avatar_list .= "\n\t<label><input type='radio' name='avatar_default' id='avatar_{$default_key}' value='" . esc_attr( $default_key ) . "' {$selected}/> ";
	$avatar_list .= get_avatar( $user_email, 32, $default_key, '', array( 'force_default' => true ) );
	$avatar_list .= ' ' . $default_name . '</label>';
	$avatar_list .= '<br />';
}

remove_filter( 'pre_option_show_avatars', '__return_true', 100 );

/**
 * Filters the HTML output of the default avatar list.
 *
 * @since 2.6.0
 *
 * @param string $avatar_list HTML markup of the avatar list.
 */
echo apply_filters( 'default_avatar_select', $avatar_list );
?>

</fieldset></td>
</tr>
</table>


</form>
</div>


} // namespace CharacterGeneratorDev
