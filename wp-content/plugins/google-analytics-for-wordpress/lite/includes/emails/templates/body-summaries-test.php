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

// Initialize variables with fake data for testing
$update_available = true;
$report_title = __('Your Monthly Website Analytics Summary', 'google-analytics-for-wordpress');
$report_image_src = 'https://placehold.co/600x400'; // Placeholder image URL
$report_description = __('Here\'s a quick overview of your website\'s performance over the last month. Check out your key stats and top pages below.', 'google-analytics-for-wordpress');
$report_features = array(
	__('Track key metrics', 'google-analytics-for-wordpress'),
	__('Identify top content', 'google-analytics-for-wordpress'),
	__('Improve user engagement', 'google-analytics-for-wordpress'),
);
$report_button_text = __('View Full Report', 'google-analytics-for-wordpress');
$report_link = admin_url('admin.php?page=monsterinsights_reports');
$report_stats = array(
	array('icon' => '📊', 'label' => __('Sessions', 'google-analytics-for-wordpress'), 'value' => '1.5K', 'difference' => 15, 'trend_icon' => '↑', 'trend_class' => 'mset-text-increase'),
	array('icon' => 'Users', 'label' => __('Users', 'google-analytics-for-wordpress'), 'value' => '1.2K', 'difference' => -5, 'trend_icon' => '↓', 'trend_class' => 'mset-text-decrease'),
	array('icon' => 'Pageviews', 'label' => __('Page Views', 'google-analytics-for-wordpress'), 'value' => '2.8K', 'difference' => 10, 'trend_icon' => '↑', 'trend_class' => 'mset-text-increase'),
	array('icon' => 'Avg. Duration', 'label' => __('Avg. Session Duration', 'google-analytics-for-wordpress'), 'value' => '00:02:30', 'difference' => 2, 'trend_icon' => '↑', 'trend_class' => 'mset-text-increase'),
	array('icon' => 'Bounce Rate', 'label' => __('Bounce Rate', 'google-analytics-for-wordpress'), 'value' => '45%', 'difference' => -3, 'trend_icon' => '↓', 'trend_class' => 'mset-text-decrease'),
);
$top_pages = array(
	array('hostname' => 'example.com', 'url' => '/page-1', 'title' => 'Example Page 1', 'sessions' => 500),
	array('hostname' => 'example.com', 'url' => '/page-2', 'title' => 'Example Page 2', 'sessions' => 450),
	array('hostname' => 'example.com', 'url' => '/page-3', 'title' => 'Example Page 3', 'sessions' => 400),
	array('hostname' => 'example.com', 'url' => '/page-4', 'title' => 'Example Page 4', 'sessions' => 350),
	array('hostname' => 'example.com', 'url' => '/page-5', 'title' => 'Example Page 5', 'sessions' => 300),
);
$more_pages_url = admin_url('admin.php?page=monsterinsights_reports#/overview/toppages-report/');
$blog_posts = array(
	array('featured_image' => 'https://placehold.co/100x100', 'title' => 'Blog Post Title 1', 'excerpt' => 'Blog post excerpt 1...', 'link' => '#'),
	array('featured_image' => 'https://placehold.co/100x100', 'title' => 'Blog Post Title 2', 'excerpt' => 'Blog post excerpt 2...', 'link' => '#'),
	array('featured_image' => 'https://placehold.co/100x100', 'title' => 'Blog Post Title 3', 'excerpt' => 'Blog post excerpt 3...', 'link' => '#'),
);
$blog_posts_url = 'https://monsterinsights.com/blog/';

if ( $update_available ) : ?>
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
