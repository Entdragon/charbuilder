<?php

namespace CharacterGeneratorDev {

/**
 * Classic style template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/classic-style.php.
 *
 * @since 1.8.5
 *
 * @var string $email_background_color  Background color for the email.
 * @var string $email_body_color        Background color for the email content body.
 * @var string $email_text_color        Text color for the email content.
 * @var string $email_links_color       Color for links in the email content.
 * @var string $email_typography        Preferred typography font-family for email content.
 * @var string $header_image_max_width  Maximum width for the header image.
 * @var string $header_image_max_height Maximum height for the header image.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPFORMS_PLUGIN_DIR . '/assets/css/emails/classic.min.css';

// Reuse border-color.
$border_color = wpforms_generate_contrasting_color( $email_text_color, 86, 72 );

?>

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

.button-link {
}

.content .field-value {
}

.footer, .footer a {
}

table.wpforms-order-summary-preview {
}

table.wpforms-order-summary-preview td {
}

.header-image {
}
.header-image img {
}

} // namespace CharacterGeneratorDev
