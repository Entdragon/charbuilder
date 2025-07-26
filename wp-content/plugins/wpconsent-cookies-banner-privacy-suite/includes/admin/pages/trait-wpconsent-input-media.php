<?php

namespace CharacterGeneratorDev {

/**
 * Media input trait.
 *
 * @package WPConsent
 */

/**
 * Trait WPConsent_Input_Media
 */
trait WPConsent_Input_Media {

	/**
	 * File input to choose a file.
	 *
	 * @param string $id ID.
	 * @param string $value Value.
	 * @param string $name Name.
	 * @target string $target CSS selector for element where we should show a preview of the image (aside from in the input area).
	 *
	 * @return string
	 */
	public function media( $id, $value = '', $name = '', $target = '' ) {

		$name    = ! empty( $name ) ? $name : $id;

		ob_start();
		?>
		<div class="wpconsent-media-input">
			<input type="hidden"
			/>
			<div class="wpconsent-image-preview" style="margin-bottom: 10px;">
			</div>
			<div class="wpconsent-buttons">
				</button>
				</button>
			</div>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('.wpconsent-upload-button').click(function(e) {
					e.preventDefault();
					var inputId = $(this).data('input');
					var $previewContainer = $(this).closest('.wpconsent-media-input').find('.wpconsent-image-preview');
					var $clearButton = $(this).closest('.wpconsent-media-input').find('.wpconsent-clear-button');

					var mediaUploader = wp.media({
							button: {
							},
							multiple: false
					});

					mediaUploader.on('select', function() {
						var attachment = mediaUploader.state().get('selection').first().toJSON();
						$('#' + inputId).val(attachment.url);
						$previewContainer.html('<img src="' + attachment.url + '" alt="" style="max-width: 200px; height: auto;">');
						$clearButton.show();
					});

					mediaUploader.open();
				});

				$('.wpconsent-clear-button').click(function(e) {
					e.preventDefault();
					var inputId = $(this).data('input');
					var $previewContainer = $(this).closest('.wpconsent-media-input').find('.wpconsent-image-preview');
					$('#' + inputId).val('');
					$previewContainer.empty();
					$(this).hide();
				});
			});
		</script>

		$html = ob_get_clean();

		return $html;
	}
}

} // namespace CharacterGeneratorDev
