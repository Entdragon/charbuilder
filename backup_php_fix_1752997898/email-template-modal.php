<?php

namespace CharacterGeneratorDev {

/**
 * Email Template modal content.
 *
 * This template is used for rendering the email template modal content
 * and is injected into the DOM via JS. The JS backbone template is used to render loop iterations.
 *
 * @since 1.8.5
 *
 * @var string $pro_badge Pro badge HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="text/html" id="tmpl-wpforms-email-template-modal">
	<div class="wpforms-modal-header">
		<h1>
		</h1>
		<p>
		</p>
	</div>
	<div class="wpforms-email-template-modal-content wpforms-modal-content">
		<div class="wpforms-card-image-group">
			<div class="wpforms-setting-field">
				<# _.each( data.templates, function( template, key ) { #>
						<input type="radio" name="wpforms-email-template-modal-choice" id="wpforms-email-template-modal-choice-{{ data.id }}-{{ key }}" value="{{ key }}"<# if ( key === data.selected ) { #> checked="checked"<# } #> />
						<label for="wpforms-email-template-modal-choice-{{ data.id }}-{{ key }}" class="option-{{ key }}">
							{{ template.name }}
							<# if ( ! data.is_pro && template.is_pro ) { #>
							<# } #>
							<span class="wpforms-card-image-overlay">
								<span class="wpforms-btn-choose wpforms-btn wpforms-btn-md wpforms-btn-orange">
								</span>
								<# if ( template.preview ) { #>
									<a href="{{{ template.preview }}}" target="_blank" class="wpforms-btn-preview wpforms-btn wpforms-btn-md wpforms-btn-light-grey">
									</a>
								<# } #>
							</span>
						</label>
					</div>
				<# } ); #>
			</div>
		</div>
	</div>
</script>

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

} // namespace CharacterGeneratorDev
