<?php

namespace CharacterGeneratorDev {

/**
 * Email Footer Template
 *
 * Uses modern HTML/CSS while maintaining email client compatibility.
 * CSS classes are prefixed with 'mset-' (MonsterInsights Summary Email Template)
 * to avoid conflicts with email client styles.
 *
 * @since 8.19.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
			</div><!-- .mset-content -->
			<div class="mset-footer">
				<div class="mset-footer-content">
						</a>

					if ( isset( $settings_tab_url ) && $settings_tab_url ) :
						$footer = sprintf(
							/* translators: Placeholders adds wrapping span tags and links to settings page. */
							esc_html__('%1$sThis email was auto-genetrated and sent from MonsterInsights.%2$s Learn how to %3$s disable it%4$s.', 'google-analytics-for-wordpress' ),
						'<span>',
						'</span><span>',
						'<a href="' . $settings_tab_url . '" target="_blank" class="mset-footer-link">',
						'</a></span>'
						);

						echo apply_filters( 'mi_email_summaries_footer_text', $footer ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the sprintf.
					endif; ?>
				</div>
				<div class="mset-footer-bar">
						</a>

					if ( isset( $facebook_url ) && $facebook_url ) : ?>
							<span class="mset-icon mset-icon-facebook"></span>
						</a>

					if ( isset( $linkedin_url ) && $linkedin_url ) : ?>
							<span class="mset-icon mset-icon-linkedin"></span>
						</a>
				</div>
			</div>
		</div><!-- .mset-container -->
	</div><!-- .mset-wrapper -->
</body>
</html>
} // namespace CharacterGeneratorDev
