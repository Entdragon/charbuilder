<?php

namespace CharacterGeneratorDev {

/**
 * General education template.
 *
 * @since 1.8.6
 *
 * @var string $action               Is plugin installed?
 * @var string $path                 Plugin file.
 * @var string $url                  URL download plugin download.
 * @var bool   $plugin_allow         Allow using plugin.
 * @var string $heading_title        Heading title.
 * @var string $badge                Badge.
 * @var string $heading_description  Heading description.
 * @var string $features_description Features description.
 * @var array  $features             List of features.
 * @var array  $images               List of images.
 * @var string $license_level        License level.
 * @var string $utm_medium           UTM medium.
 * @var string $utm_content          UTM content.
 * @var string $upgrade_link         Upgrade link.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpforms-education-page">
	<div class="wpforms-education-page-heading">
			<h4>
			</h4>
			if ( isset( $heading_description ) ) {
				echo wp_kses_post( $heading_description );
			}
		?>
	</div>

	<div class="wpforms-education-page-media">
		<div class="wpforms-education-page-images">
			if ( isset( $images ) ) :
				foreach ( $images as $image ) :
					?>
				<figure>
					<div class="wpforms-education-page-images-image">
					</div>
				</figure>
				endforeach;
			endif;
			?>
		</div>
	</div>

	<div class="wpforms-education-page-caps">
		<ul>
			if ( isset( $features ) ) :
				foreach ( $features as $feature ) :
					?>
					<li>
						<i class="fa fa-solid fa-check"></i>
					</li>
				endforeach;
			endif;
			?>
		</ul>
	</div>

	<div class="wpforms-education-page-button">
		if ( isset( $action ) ) {
			wpforms_edu_get_button(
				$action,
				$plugin_allow,
				$path,
				$url,
				[
					'medium'  => $utm_medium,
					'content' => $utm_content,
				],
				$license_level
			);
		} else {
			printf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-upgrade-modal wpforms-btn wpforms-btn-lg wpforms-btn-orange">%s</a>',
				esc_url( wpforms_admin_upgrade_link( $utm_medium, $utm_content ) ),
				esc_html__( 'Upgrade to WPForms Pro', 'wpforms-lite' )
			);
		}
		?>
	</div>
</div>

} // namespace CharacterGeneratorDev
