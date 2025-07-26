<?php

namespace CharacterGeneratorDev {

/**
 * Compact media queries style template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/compact-queries.php.
 *
 * Note: To override the existing styles of the template in this file, ensure that all
 * overriding styles are declared as !important to take precedence over the default styles.
 *
 * @since 1.8.5
 * @since 1.8.6 Added dark mode variables.
 *
 * @var string $email_background_color_dark  Background color for the email.
 * @var string $email_body_color_dark        Background color for the email content body.
 * @var string $email_text_color_dark        Text color for the email content.
 * @var string $email_links_color_dark       Color for links in the email content.
 * @var string $email_typography_dark        Preferred typography font-family for email content.
 * @var string $header_image_max_width_dark  Maximum width for the header image.
 * @var string $header_image_max_height_dark Maximum height for the header image.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPFORMS_PLUGIN_DIR . 'assets/css/emails/partials/compact_media_queries.css';

// Reuse border-color.
$border_color_dark = wpforms_generate_contrasting_color( $email_text_color_dark, 86, 72 );

?>

@media (prefers-color-scheme: dark) {
	body, .body {
	}

	.wrapper-inner {
	}

	body, table.body, h1, h2, h3, h4, h5, h6, p, td, th, a {
	}

	a, a:visited,
	a:hover, a:active,
	h1 a, h1 a:visited,
	h2 a, h2 a:visited,
	h3 a, h3 a:visited,
	h4 a, h4 a:visited,
	h5 a, h5 a:visited,
	h6 a, h6 a:visited {
	}

	a.button-link {
	}

	.content td {
	}

	.footer, .footer a {
	}

	table.wpforms-order-summary-preview {
	}

	table.wpforms-order-summary-preview td {
	}

	.dark-mode .header-image {
	}
	.dark-mode .header-image img {
	}
}

} // namespace CharacterGeneratorDev
