<?php

namespace CharacterGeneratorDev {

/**
 * Your Rights administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

// This file was used to also display the Privacy tab on the About screen from 4.9.6 until 5.3.0.
if ( isset( $_GET['privacy-notice'] ) ) {
	wp_redirect( admin_url( 'privacy.php' ), 301 );
	exit;
}

// Used in the HTML title tag.
$title = __( 'Freedoms' );

list( $display_version ) = explode( '-', get_bloginfo( 'version' ) );

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
<div class="wrap about__container">

	<div class="about__header">
		<div class="about__header-title">
			<h1>
			</h1>
		</div>

		<div class="about__header-text">
		</div>
	</div>

	</nav>

	<div class="about__section is-feature">
		<p class="about-description">
		printf(
			/* translators: %s: https://wordpress.org/about/license/ */
			__( 'WordPress comes with some awesome, worldview-changing rights courtesy of its <a href="%s">license</a>, the GPL.' ),
			__( 'https://wordpress.org/about/license/' )
		);
		?>
		</p>
	</div>

	<div class="about__section has-2-columns">
		<div class="column aligncenter">
		</div>
		<div class="column aligncenter">
		</div>
		<div class="column aligncenter">
		</div>
		<div class="column aligncenter">
		</div>
	</div>

	<div class="about__section has-1-column">
		<div class="column">
			<p>
			printf(
				/* translators: %s: https://wordpressfoundation.org/trademark-policy/ */
				__( 'WordPress grows when people like you tell their friends about it, and the thousands of businesses and services that are built on and around WordPress share that fact with their users. The WordPress community is flattered every time someone spreads the good word, just make sure to <a href="%s">check out the WordPress Foundation trademark guidelines</a> first.' ),
				'https://wordpressfoundation.org/trademark-policy/'
			);
			?>
			</p>

			<p>
			$plugins_url = current_user_can( 'activate_plugins' ) ? admin_url( 'plugins.php' ) : __( 'https://wordpress.org/plugins/' );
			$themes_url  = current_user_can( 'switch_themes' ) ? admin_url( 'themes.php' ) : __( 'https://wordpress.org/themes/' );
			printf(
				/* translators: 1: URL to Plugins screen, 2: URL to Themes screen, 3: https://wordpress.org/about/license/ */
				__( 'Every plugin and theme in WordPress.org&#8217;s directory is 100%% GPL or a similarly free and compatible license, so you can feel safe finding <a href="%1$s">plugins</a> and <a href="%2$s">themes</a> there. If you get a plugin or theme from another source, make sure to <a href="%3$s">ask them if it&#8217;s GPL</a> first. If they do not respect the WordPress license, it is not recommended to use them.' ),
				$plugins_url,
				$themes_url,
				__( 'https://wordpress.org/about/license/' )
			);
			?>
			</p>
		</div>
	</div>

</div>

} // namespace CharacterGeneratorDev
