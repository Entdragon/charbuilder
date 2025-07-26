<?php

namespace CharacterGeneratorDev {

/**
 * Privacy administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

// Used in the HTML title tag.
$title = __( 'Privacy' );

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

	<div class="about__section has-2-columns is-wider-right">
		<div class="column about__image">
		</div>
		<div class="column is-vertically-aligned-center">

			<p>
				printf(
					/* translators: %s: https://wordpress.org/about/stats/ */
					__( 'This data is used to provide general enhancements to WordPress, which includes helping to protect your site by finding and automatically installing new updates. It is also used to calculate statistics, such as those shown on the <a href="%s">WordPress.org stats page</a>.' ),
					__( 'https://wordpress.org/about/stats/' )
				);
				?>
			</p>

			<p>
				printf(
					/* translators: %s: https://wordpress.org/about/privacy/ */
					__( 'WordPress.org takes privacy and transparency very seriously. To learn more about what data is collected, and how it is used, please visit <a href="%s">the WordPress.org Privacy Policy</a>.' ),
					__( 'https://wordpress.org/about/privacy/' )
				);
				?>
			</p>
		</div>
	</div>

</div>

} // namespace CharacterGeneratorDev
