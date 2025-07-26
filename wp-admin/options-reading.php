<?php

namespace CharacterGeneratorDev {

/**
 * Reading settings administration panel.
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
$title       = __( 'Reading Settings' );
$parent_file = 'options-general.php';

add_action( 'admin_head', 'options_reading_add_js' );

get_current_screen()->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' => '<p>' . __( 'This screen contains the settings that affect the display of your content.' ) . '</p>' .
			'<p>' . sprintf(
				/* translators: %s: URL to create a new page. */
				__( 'You can choose what&#8217;s displayed on the homepage of your site. It can be posts in reverse chronological order (classic blog), or a fixed/static page. To set a static homepage, you first need to create two <a href="%s">Pages</a>. One will become the homepage, and the other will be where your posts are displayed.' ),
				'post-new.php?post_type=page'
			) . '</p>' .
			'<p>' . sprintf(
				/* translators: %s: Documentation URL. */
				__( 'You can also control the display of your content in RSS feeds, including the maximum number of posts to display and whether to show full text or an excerpt. <a href="%s">Learn more about feeds</a>.' ),
				__( 'https://developer.wordpress.org/advanced-administration/wordpress/feeds/' )
			) . '</p>' .
			'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ) . '</p>',
	)
);

get_current_screen()->add_help_tab(
	array(
		'id'      => 'site-visibility',
		'title'   => has_action( 'blog_privacy_selector' ) ? __( 'Site visibility' ) : __( 'Search engine visibility' ),
		'content' => '<p>' . __( 'You can choose whether or not your site will be crawled by robots, ping services, and spiders. If you want those services to ignore your site, click the checkbox next to &#8220;Discourage search engines from indexing this site&#8221; and click the Save Changes button at the bottom of the screen.' ) . '</p>' .
			'<p>' . __( 'Note that even when set to discourage search engines, your site is still visible on the web and not all search engines adhere to this directive.' ) . '</p>' .
			'<p>' . __( 'When this setting is in effect, a reminder is shown in the At a Glance box of the Dashboard that says, &#8220;Search engines discouraged&#8221;, to remind you that you have directed search engines to not crawl your site.' ) . '</p>',
	)
);

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://wordpress.org/documentation/article/settings-reading-screen/">Documentation on Reading Settings</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/forums/">Support forums</a>' ) . '</p>'
);

require_once ABSPATH . 'wp-admin/admin-header.php';
?>

<div class="wrap">

<form method="post" action="options.php">
settings_fields( 'reading' );

if ( ! is_utf8_charset() ) {
	add_settings_field( 'blog_charset', __( 'Encoding for pages and feeds' ), 'options_reading_blog_charset', 'reading', 'default', array( 'label_for' => 'blog_charset' ) );
}
?>

<input name="show_on_front" type="hidden" value="posts" />
<table class="form-table" role="presentation">
	if ( 'posts' !== get_option( 'show_on_front' ) ) :
		update_option( 'show_on_front', 'posts' );
	endif;

else :
	if ( 'page' === get_option( 'show_on_front' ) && ! get_option( 'page_on_front' ) && ! get_option( 'page_for_posts' ) ) {
		update_option( 'show_on_front', 'posts' );
	}
	?>
<table class="form-table" role="presentation">
<tr>
<td id="front-static-pages"><fieldset>
	<legend class="screen-reader-text"><span>
		/* translators: Hidden accessibility text. */
		_e( 'Your homepage displays' );
		?>
	</span></legend>
	<p><label>
	</label>
	</p>
	<p><label>
		printf(
			/* translators: %s: URL to Pages screen. */
			__( 'A <a href="%s">static page</a> (select below)' ),
			'edit.php?post_type=page'
		);
		?>
	</label>
	</p>
<ul>
	<li><label for="page_on_front">
	printf(
		/* translators: %s: Select field to choose the front page. */
		__( 'Homepage: %s' ),
		wp_dropdown_pages(
			array(
				'name'              => 'page_on_front',
				'echo'              => 0,
				'show_option_none'  => __( '&mdash; Select &mdash;' ),
				'option_none_value' => '0',
				'selected'          => get_option( 'page_on_front' ),
			)
		)
	);
	?>
</label></li>
	<li><label for="page_for_posts">
	printf(
		/* translators: %s: Select field to choose the page for posts. */
		__( 'Posts page: %s' ),
		wp_dropdown_pages(
			array(
				'name'              => 'page_for_posts',
				'echo'              => 0,
				'show_option_none'  => __( '&mdash; Select &mdash;' ),
				'option_none_value' => '0',
				'selected'          => get_option( 'page_for_posts' ),
			)
		)
	);
	?>
</label></li>
</ul>
	if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) === get_option( 'page_on_front' ) ) :
		wp_admin_notice(
			__( '<strong>Warning:</strong> these pages should not be the same!' ),
			array(
				'type'               => 'warning',
				'id'                 => 'front-page-warning',
				'additional_classes' => array( 'inline' ),
			)
		);
	endif;
	if ( get_option( 'wp_page_for_privacy_policy' ) === get_option( 'page_for_posts' ) || get_option( 'wp_page_for_privacy_policy' ) === get_option( 'page_on_front' ) ) :
		wp_admin_notice(
			__( '<strong>Warning:</strong> these pages should not be the same as your Privacy Policy page!' ),
			array(
				'type'               => 'warning',
				'id'                 => 'privacy-policy-page-warning',
				'additional_classes' => array( 'inline' ),
			)
		);
	endif;
	?>
</fieldset></td>
</tr>
<tr>
<td>
</td>
</tr>
<tr>
</tr>
<tr>
<td><fieldset>
	<legend class="screen-reader-text"><span>
		/* translators: Hidden accessibility text. */
		_e( 'For each post in a feed, include' );
		?>
	</span></legend>
	<p>
	</p>
	<p class="description">
		printf(
			/* translators: %s: Documentation URL. */
			__( 'Your theme determines how content is displayed in browsers. <a href="%s">Learn more about feeds</a>.' ),
			__( 'https://developer.wordpress.org/advanced-administration/wordpress/feeds/' )
		);
		?>
	</p>
</fieldset></td>
</tr>

<tr class="option-site-visibility">
<td><fieldset>
	<legend class="screen-reader-text"><span>
		has_action( 'blog_privacy_selector' )
			/* translators: Hidden accessibility text. */
			? _e( 'Site visibility' )
			/* translators: Hidden accessibility text. */
			: _e( 'Search engine visibility' );
		?>
	</span></legend>
	/**
	 * Enables the legacy 'Site visibility' privacy options.
	 *
	 * By default the privacy options form displays a single checkbox to 'discourage' search
	 * engines from indexing the site. Hooking to this action serves a dual purpose:
	 *
	 * 1. Disable the single checkbox in favor of a multiple-choice list of radio buttons.
	 * 2. Open the door to adding additional radio button choices to the list.
	 *
	 * Hooking to this action also converts the 'Search engine visibility' heading to the more
	 * open-ended 'Site visibility' heading.
	 *
	 * @since 2.1.0
	 */
	do_action( 'blog_privacy_selector' );
	?>
</fieldset></td>
</tr>

</table>


</form>
</div>

} // namespace CharacterGeneratorDev
