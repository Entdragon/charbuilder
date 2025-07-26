<?php

namespace CharacterGeneratorDev {

/**
 * Trait for the language picker in the WPConsent admin area.
 *
 * @package WPConsent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait WPCOnsent_Language_Picker {

	/**
	 * Get the language picker button.
	 *
	 * @return void
	 */
	public function language_picker_button() {
		?>
		<div class="wpconsent-language-picker-container">
			<button
				type="button"
				id="wpconsent-languages-button"
				class="wpconsent-button-just-icon wpconsent-languages-button wpconsent-languages-button-lite">
			</button>
		</div>
	}
}

} // namespace CharacterGeneratorDev
