<?php

namespace CharacterGeneratorDev {

/**
 * Media settings administration panel.
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
$title       = __( 'Media Settings' );
$parent_file = 'options-general.php';

$media_options_help = '<p>' . __( 'You can set maximum sizes for images inserted into your written content; you can also insert an image as Full Size.' ) . '</p>';

if ( ! is_multisite()
	&& ( get_option( 'upload_url_path' )
		|| get_option( 'upload_path' ) && 'wp-content/uploads' !== get_option( 'upload_path' ) )
) {
	$media_options_help .= '<p>' . __( 'Uploading Files allows you to choose the folder and path for storing your uploaded files.' ) . '</p>';
}

$media_options_help .= '<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ) . '</p>';

get_current_screen()->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' => $media_options_help,
	)
);

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://wordpress.org/documentation/article/settings-media-screen/">Documentation on Media Settings</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/forums/">Support forums</a>' ) . '</p>'
);

require_once ABSPATH . 'wp-admin/admin-header.php';

?>

<div class="wrap">

<form action="options.php" method="post">


<table class="form-table" role="presentation">
<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Thumbnail size' );
	?>
</span></legend>
<br />
</fieldset>
</td>
</tr>

<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Medium size' );
	?>
</span></legend>
<br />
</fieldset></td>
</tr>

<tr>
<td><fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Large size' );
	?>
</span></legend>
<br />
</fieldset></td>
</tr>

</table>

/**
 * @global array $wp_settings
 */
if ( isset( $GLOBALS['wp_settings']['media']['embeds'] ) ) :
	?>
<table class="form-table" role="presentation">
</table>

<table class="form-table" role="presentation">
	/*
	 * If upload_url_path is not the default (empty),
	 * or upload_path is not the default ('wp-content/uploads' or empty),
	 * they can be edited, otherwise they're locked.
	 */
	if ( get_option( 'upload_url_path' )
		|| get_option( 'upload_path' ) && 'wp-content/uploads' !== get_option( 'upload_path' ) ) :
		?>
<tr>
<p class="description">
		/* translators: %s: wp-content/uploads */
		printf( __( 'Default is %s' ), '<code>wp-content/uploads</code>' );
		?>
</p>
</td>
</tr>

<tr>
</td>
</tr>
<tr>
<td colspan="2" class="td-full">
<tr>
<td class="td-full">
<label for="uploads_use_yearmonth_folders">
</label>
</td>
</tr>

</table>



</form>

</div>


} // namespace CharacterGeneratorDev
