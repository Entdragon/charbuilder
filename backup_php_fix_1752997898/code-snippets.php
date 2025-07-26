<?php

namespace CharacterGeneratorDev {

/**
 * WPCode integration code snippets page.
 *
 * @since 1.8.5
 *
 * @var array  $snippets        WPCode snippets list.
 * @var bool   $action_required Indicate that user should install or activate WPCode.
 * @var string $action          Popup button action.
 * @var string $plugin          WPCode Lite download URL | WPCode Lite plugin slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

$container_class   = $action_required ? 'wpforms-wpcode-blur' : '';
$popup_title       = esc_html__( 'Please Install WPCode to Use the WPForms Snippet Library', 'wpforms-lite' );
$popup_button_text = esc_html__( 'Install + Activate WPCode', 'wpforms-lite' );

if ( $action === 'update' ) {
	$popup_title       = esc_html__( 'Please Update WPCode to Use the WPForms Snippet Library', 'wpforms-lite' );
	$popup_button_text = esc_html__( 'Update + Activate WPCode', 'wpforms-lite' );
}

if ( $action === 'activate' ) {
	$popup_title       = esc_html__( 'Please Activate WPCode to Use the WPForms Snippet Library', 'wpforms-lite' );
	$popup_button_text = esc_html__( 'Activate WPCode', 'wpforms-lite' );
}
?>

<div class="wpforms-wpcode">
		<div class="wpforms-wpcode-popup">
			<div class="wpforms-wpcode-popup-description">
			</div>
			<a
					href="https://wordpress.org/plugins/insert-headers-and-footers/?utm_source=wpformsplugin&utm_medium=WPCode+WordPress+Repo&utm_campaign=plugin&utm_content=WPCode"
					class="wpforms-wpcode-popup-link">
			</a>
		</div>

		<div class="wpforms-setting-row tools wpforms-wpcode-header">
			<div class="wpforms-wpcode-header-meta">
				<p>
					printf(
						wp_kses( /* translators: %s - WPCode library website URL. */
							__( 'Using WPCode, you can install WPForms code snippets with 1 click directly from this page or the <a href="%s" target="_blank" rel="noopener noreferrer">WPCode library</a>.', 'wpforms-lite' ),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						esc_url( admin_url( 'admin.php?page=wpcode-library' ) )
					);
					?>
				</p>
			</div>
			<div class="wpforms-wpcode-header-search">
				<label for="wpforms-wpcode-snippet-search"></label>
				<input
				        id="wpforms-wpcode-snippet-search">
			</div>
		</div>

		<div id="wpforms-wpcode-snippets-list">
			<div class="list">
				foreach ( $snippets as $snippet ) :
					$button_text       = $snippet['installed'] ? __( 'Edit Snippet', 'wpforms-lite' ) : __( 'Install Snippet', 'wpforms-lite' );
					$button_type_class = $snippet['installed'] ? 'button-primary' : 'button-secondary';
					$button_action     = $snippet['installed'] ? 'edit' : 'install';
					$badge_text        = $snippet['installed'] ? __( 'Installed', 'wpforms-lite' ) : '';
					?>
					<div class="wpforms-wpcode-snippet">
						<div class="wpforms-wpcode-snippet-header">
						</div>
						<div class="wpforms-wpcode-snippet-footer">
							<a
						</div>
					</div>
			</div>
		</div>
	</div>
</div>

} // namespace CharacterGeneratorDev
