<?php

namespace CharacterGeneratorDev {

/**
 * Contribute administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

// Used in the HTML title tag.
$title = __( 'Get Involved' );

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
		<div class="column">
		</div>
		<div class="column is-vertically-aligned-center">

			<ul>
			</ul>
		</div>
	</div>

	<div class="about__section has-2-columns is-wider-left">
		<div class="column is-vertically-aligned-center">
			<ul>
			</ul>
		</div>
		<div class="column">
		</div>
	</div>
	<div class="about__section has-2-columns is-wider-right">
		<div class="column">
		</div>
		<div class="column is-vertically-aligned-center">
			<ul>
			</ul>
			<ul>
			</ul>
		</div>
	</div>

	<div class="about__section is-feature has-subtle-background-color">
		<div class="column">
		</div>
	</div>

</div>
require_once ABSPATH . 'wp-admin/admin-footer.php';

} // namespace CharacterGeneratorDev
