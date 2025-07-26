<?php

namespace CharacterGeneratorDev {

/**
 * Credits administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/includes/credits.php';

// Used in the HTML title tag.
$title = __( 'Credits' );

list( $display_version ) = explode( '-', get_bloginfo( 'version' ) );

require_once ABSPATH . 'wp-admin/admin-header.php';

$credits = wp_credits();
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

	<div class="about__section has-1-column has-gutters">
		<div class="column aligncenter">

			<p>
				printf(
					/* translators: 1: https://wordpress.org/about/ */
					__( 'WordPress is created by a <a href="%1$s">worldwide team</a> of passionate individuals.' ),
					__( 'https://wordpress.org/about/' )
				);
				?>
				<br />
			</p>


			<p>
				<br />
			</p>

		</div>
	</div>

if ( ! $credits ) {
	echo '</div>';
	require_once ABSPATH . 'wp-admin/admin-footer.php';
	exit;
}
?>

	<hr class="is-large" />

	<div class="about__section">
		<div class="column is-edge-to-edge">
		</div>
	</div>

	<hr />

	<div class="about__section">
		<div class="column">
		</div>
	</div>

	<hr />

	<div class="about__section">
		<div class="column">
		</div>
	</div>

	<hr />

	<div class="about__section">
		<div class="column">
		</div>
	</div>
</div>

require_once ABSPATH . 'wp-admin/admin-footer.php';

return;

// These are strings returned by the API that we want to be translatable.
__( 'Project Leaders' );
/* translators: %s: The current WordPress version number. */
__( 'Core Contributors to WordPress %s' );
__( 'Noteworthy Contributors' );
__( 'Cofounder, Project Lead' );
__( 'Lead Developer' );
__( 'Release Lead' );
__( 'Release Design Lead' );
__( 'Release Deputy' );
__( 'Release Coordination' );
__( 'Minor Release Lead' );
__( 'Core Developer' );
__( 'Core Tech Lead' );
__( 'Core Triage Lead' );
__( 'Editor Tech Lead' );
__( 'Editor Triage Lead' );
__( 'Documentation Lead' );
__( 'Test Lead' );
__( 'Design Lead' );
__( 'Performance Lead' );
__( 'Default Theme Design Lead' );
__( 'Default Theme Development Lead' );
__( 'Tech Lead' );
__( 'Triage Lead' );
__( 'External Libraries' );

} // namespace CharacterGeneratorDev
