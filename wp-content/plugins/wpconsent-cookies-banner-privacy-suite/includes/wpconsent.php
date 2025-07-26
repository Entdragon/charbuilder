<?php

namespace CharacterGeneratorDev {

/**
 * WPConsent Lite.
 *
 * @package WPConsent
 */

/**
 * Get the main instance of WPConsent.
 *
 * @return WPConsent
 */
function wpconsent() {// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WPConsent::instance();
}
} // namespace CharacterGeneratorDev
