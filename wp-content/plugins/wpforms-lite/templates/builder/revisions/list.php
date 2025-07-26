<?php

namespace CharacterGeneratorDev {

/**
 * A list of form revisions in the Form Builder Revisions panel.
 *
 * @since 1.7.3
 *
 * @var string $active_class        Active item class.
 * @var string $current_version_url The URL to load the current form version.
 * @var string $author_id           Current form author ID.
 * @var array  $revisions           A list of all form revisions.
 * @var string $show_avatars        Whether the site settings for showing avatars is enabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class='wpforms-revisions-content'>
				<div class="wpforms-revision-gravatar">
				</div>

			<div class='wpforms-revision-details'>
				<p class='wpforms-revision-created'>
				</p>

				<p class='wpforms-revision-author'>
					$display_name = get_the_author_meta( 'display_name', $author_id );

					printf( /* translators: %s - form revision author name. */
						esc_html__( 'by %s', 'wpforms-lite' ),
						! empty( $display_name ) ? esc_html( $display_name ) : esc_html__( 'Unknown user', 'wpforms-lite' )
					);
					?>
				</p>
			</div>
		</a>
	</div>

	<ul class="wpforms-revisions-list">

						<div class="wpforms-revision-gravatar">
						</div>

					<div class='wpforms-revision-details'>
						<p class='wpforms-revision-created'>
						</p>

						<p class='wpforms-revision-author'>
							$display_name = get_the_author_meta( 'display_name', $revision['author_id'] );

							printf( /* translators: %s - form revision author name. */
								esc_html__( 'by %s', 'wpforms-lite' ),
								! empty( $display_name ) ? esc_html( $display_name ) : esc_html__( 'Unknown user', 'wpforms-lite' )
							);
							?>
						</p>
					</div>
				</a>
			</li>

	</ul>
</div>

} // namespace CharacterGeneratorDev
