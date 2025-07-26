<?php

namespace CharacterGeneratorDev {

/**
 * Privacy Policy Guide Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

if ( ! current_user_can( 'manage_privacy_options' ) ) {
	wp_die( __( 'Sorry, you are not allowed to manage privacy options on this site.' ) );
}

if ( ! class_exists( 'WP_Privacy_Policy_Content' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-privacy-policy-content.php';
}

// Used in the HTML title tag.
$title = __( 'Privacy Policy Guide' );

add_filter(
	'admin_body_class',
	static function ( $body_class ) {
		$body_class .= ' privacy-settings ';

		return $body_class;
	}
);

wp_enqueue_script( 'privacy-tools' );

require_once ABSPATH . 'wp-admin/admin-header.php';

?>
<div class="privacy-settings-header">
	<div class="privacy-settings-title-section">
		<h1>
		</h1>
	</div>

			/* translators: Tab heading for Site Health Status page. */
			_ex( 'Settings', 'Privacy Settings' );
			?>
		</a>

			/* translators: Tab heading for Site Health Status page. */
			_ex( 'Policy Guide', 'Privacy Settings' );
			?>
		</a>
	</nav>
</div>

<hr class="wp-header-end">

wp_admin_notice(
	__( 'The Privacy Settings require JavaScript.' ),
	array(
		'type'               => 'error',
		'additional_classes' => array( 'hide-if-js' ),
	)
);
?>

<div class="privacy-settings-body hide-if-no-js">
	<div class="privacy-settings-accordion">
		<h4 class="privacy-settings-accordion-heading">
			<button aria-expanded="false" class="privacy-settings-accordion-trigger" aria-controls="privacy-settings-accordion-block-privacy-policy-guide" type="button">
				<span class="icon"></span>
			</button>
		</h4>
		<div id="privacy-settings-accordion-block-privacy-policy-guide" class="privacy-settings-accordion-panel" hidden="hidden">
			$content = WP_Privacy_Policy_Content::get_default_content( true, false );
			echo $content;
			?>
		</div>
	</div>
	<hr class="hr-separator">
	<div class="privacy-settings-accordion wp-privacy-policy-guide">
	</div>
</div>

require_once ABSPATH . 'wp-admin/admin-footer.php';

} // namespace CharacterGeneratorDev
