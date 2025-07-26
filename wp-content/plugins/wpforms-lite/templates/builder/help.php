<?php

namespace CharacterGeneratorDev {

/**
 * Form Builder Help Screen template.
 *
 * @since 1.6.3
 *
 * @var array $settings Help Screen settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$times_svg      = '<svg width="1792" height="1792" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1490 1322q0 40-28 68l-136 136q-28 28-68 28t-68-28l-294-294-294 294q-28 28-68 28t-68-28l-136-136q-28-28-28-68t28-68l294-294-294-294q-28-28-28-68t28-68l136-136q28-28 68-28t68 28l294 294 294-294q28-28 68-28t68 28l136 136q28 28 28 68t-28 68l-294 294 294 294q28 28 28 68z"/></svg>';
$url_parameters = add_query_arg(
	[
		'utm_campaign' => wpforms()->is_pro() ? 'plugin' : 'liteplugin',
		'utm_source'   => 'WordPress',
		'utm_medium'   => rawurlencode( 'Builder Help Modal' ),
		'utm_content'  => '',
	],
	''
);

$links_utm_medium = 'Builder Help Modal';

?>
<div id="wpforms-builder-help" style="display: none; opacity: 0;" class="wpforms-admin-page">

	<img id="wpforms-builder-help-logo"
		alt="WPForms Logo">

	</div>

	<div id="wpforms-builder-help-content">

		<div id="wpforms-builder-help-search">
			</div>
		</div>

		<div id="wpforms-builder-help-no-result" style="display: none;">
			<ul class="wpforms-builder-help-docs">
				<li>
				</li>
			</ul>
		</div>
		<div id="wpforms-builder-help-result"></div>
		<div id="wpforms-builder-help-categories"></div>

		<div id="wpforms-builder-help-footer">

			<div class="wpforms-builder-help-footer-block">
				<i class="fa fa-file-text-o"></i>
					class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey"
					rel="noopener noreferrer"
					target="_blank">
				</a>
			</div>

			<div class="wpforms-builder-help-footer-block">
				<i class="fa fa-support"></i>

						class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey"
						rel="noopener noreferrer"
						target="_blank">
					</a>

						class="wpforms-btn wpforms-btn-md wpforms-btn-orange"
						rel="noopener noreferrer"
						target="_blank">
					</a>

			</div>

		</div>
	</div>
</div>

<script type="text/html" id="tmpl-wpforms-builder-help-categories">
	<ul class="wpforms-builder-help-categories-toggle">
		<# _.each( data.categories, function( categoryTitle, categorySlug ) { #>
		<li class="wpforms-builder-help-category">
			<header>
				<i class="fa fa-folder-open-o wpforms-folder"></i>
				<span>{{{ categoryTitle }}}</span>
				<i class="fa fa-angle-right wpforms-arrow"></i>
			</header>
			<ul class="wpforms-builder-help-docs" style="display: none;">
				<# _.each( data.docs[ categorySlug ], function( doc, index ) {
					utmContent = encodeURIComponent( doc.title ); #>
				<li>
				</li>
					<# if ( index === 4 && data.docs[ categorySlug ].length > 4 ) { #>
						<div style="display: none;">
					<# } #>
				<# } ) #>
				<# if ( data.docs[ categorySlug ].length > 4 ) { #>
					</div>
				<# } #>
			</ul>
		</li>
		<# } ) #>
	</ul>
</script>

<script type="text/html" id="tmpl-wpforms-builder-help-categories-error">
	<h4 class="wpforms-builder-help-error">
	</h4>
</script>

<script type="text/html" id="tmpl-wpforms-builder-help-docs">
	<ul class="wpforms-builder-help-docs">
		<# _.each( data.docs, function( doc, index ) {
			utmContent = encodeURIComponent( doc.title ); #>
		<li>
		</li>
		<# } ) #>
	</ul>
</script>

} // namespace CharacterGeneratorDev
