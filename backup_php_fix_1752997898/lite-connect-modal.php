<?php

namespace CharacterGeneratorDev {

/**
 * Admin/Settings/LiteConnect Education modal template for Lite.
 *
 * @since 1.7.4
 */

use WPForms\Integrations\AI\Helpers as AIHelpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<script type="text/html" id="tmpl-wpforms-settings-lite-connect-modal-content">
	<div class="wpforms-settings-lite-connect-modal-content">
		<p>
		</p>
		<div class="wpforms-features">
			<section>
				<aside>
					<p>
					</p>
				</aside>
			</section>
			<section>
				<aside>
					<p>
					</p>
				</aside>
			</section>

				<section>
					<aside>
						<p>
						</p>
					</aside>
				</section>

			<section>
				<aside>
					<p>
					</p>
				</aside>
			</section>
		</div>

		<footer>
			printf(
				wp_kses( /* translators: %s - WPForms Terms of Service link. */
					__( 'By enabling Lite Connect you agree to our <a href="%s" target="_blank" rel="noopener noreferrer">Terms of Service</a> and to share your information with WPForms.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/terms/', 'Lite Connect Modal', 'Terms of Service' ) )
			);
			?>
		</footer>
	</div>
</script>

} // namespace CharacterGeneratorDev
