<?php

namespace CharacterGeneratorDev {

/**
 * Email Body Template
 *
 * Uses modern HTML/CSS while maintaining email client compatibility.
 * CSS classes are prefixed with 'mset-' (MonsterInsights Summary Email Template)
 * to avoid conflicts with email client styles.
 *
 * @since 8.19.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if ( isset( $update_available ) && $update_available ) : ?>
	<div class="mset-update-notice">
			<span class="mset-icon-long-arrow-right mset-icon"></span>
		</a>
	</div>

<div class="mset-section mset-analytics-report">
		<div class="mset-section-header">
		</div>
	<div class="mset-section-content">
				class="mset-report-image">

		if ( ! empty( $report_description ) ) : ?>
			<div class="mset-report-description">
			</div>

		if ( ! empty( $report_features ) ) : ?>
			<div class="mset-report-features">
					<div class="mset-feature-item">
						<span class="mset-feature-item-icon"></span>
					</div>
			</div>

		if ( ! empty( $report_button_text ) && ! empty( $report_link ) ) : ?>
			<div class="mset-report-center-button">
				</a>
			</div>
			<div class="mset-report-center-button">
				</a>
			</div>
	</div>
</div>

<div class="mset-section mset-analytics-stats">
	<div class="mset-section-header">
	</div>

	<div class="mset-section-content">
			<div class="mset-stats-grid">
					<div class="mset-stat-item">
					<div class="mset-stat-value">
						echo esc_html($stat['value']);
						if (isset($stat['difference'])) : ?>
							</span>
					</div>
				</div>
			</div>

			<div class="mset-report-center-button">
			</a>
		</div>
	</div>
</div>

<div class="mset-section mset-top-pages">
	<div class="mset-section-header">
	</div>

	<div class="mset-section-content">
		<div class="mset-pages-table">
			<div class="mset-table-header">
			</div>
				<div class="mset-table-row">
					<div class="mset-table-cell">
						</a>
					</div>
					<div class="mset-table-cell">
					</div>
				</div>
		</div>

			<div class="mset-report-center-button">
				</a>
			</div>
	</div>
</div>

<div class="mset-section">
	<div class="mset-section-header">
	</div>
	<div class="mset-section-content">
		<ul class="mset-blog-posts">
				<li class="mset-blog-post">
						<div class="mset-blog-post-image">
						</div>
					<div class="mset-blog-post-content">
						</a>
					</div>
				</li>
		</ul>
			<div class="mset-report-center-button">
				</a>
			</div>
	</div>
</div>

} // namespace CharacterGeneratorDev
