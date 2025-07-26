<?php

namespace CharacterGeneratorDev {

/**
 * Modal for the Entries List page.
 *
 * @since 1.8.9
 *
 * @var bool $is_lite_connect_enabled Whether Lite Connect is enabled.
 * @var bool $is_lite_connect_allowed Whether Lite Connect is allowed.
 * @var int $entries_count Entries count.
 * @var string $enabled_since Enabled since.
 * @var bool $is_enabled Whether sample entries are enabled.
 */
?>
		<div class="entries-modal-content-top-notice">
		</div>
	<div class="entries-modal-content">
		<h2>
		</h2>
		<p>
		</p>
		<div class="wpforms-clear">
			<ul class="left">
			</ul>
			<ul class="right">
			</ul>
		</div>
	</div>
	<div class="entries-modal-button">

			<p class="entries-modal-button-before">

				printf(
					'<strong>' . esc_html( /* translators: %d - backed up entries count. */
						_n(
							'%d entry has been backed up',
							'%d entries have been backed up',
							$entries_count,
							'wpforms-lite'
						)
					) . '</strong>',
					absint( $entries_count )
				);

				if ( ! empty( $enabled_since ) ) {
					echo ' ';
					printf(
						/* translators: %s - time when Lite Connect was enabled. */
						esc_html__( 'since you enabled Lite Connect on %s', 'wpforms-lite' ),
						esc_html( wpforms_date_format( $enabled_since, '', true ) )
					);
				}
				// phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd
				?>.</p>
			</a>


			</a>


		<p class="wpforms-entries-sample">
			<a id="wpforms-entries-explore" href="#">
			</a>
		</p>
	</div>
</div>

} // namespace CharacterGeneratorDev
