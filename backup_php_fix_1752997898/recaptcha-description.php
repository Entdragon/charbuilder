<?php

namespace CharacterGeneratorDev {

/**
 * Description template for reCAPTCHA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<ul style="list-style: disc;margin-left: 20px;">
	<li>
		echo wp_kses(
			__( '<strong>v2 Checkbox reCAPTCHA</strong>: Prompts users to check a box to prove they\'re human.', 'wpforms-lite' ),
			[ 'strong' => [] ]
		);
	?>
	</li>
	<li>
		echo wp_kses(
			__( '<strong>v2 Invisible reCAPTCHA</strong>: Uses advanced technology to detect real users without requiring any input.', 'wpforms-lite' ),
			[ 'strong' => [] ]
		);
	?>
	</li>
	<li>
		echo wp_kses(
			__( '<strong>v3 reCAPTCHA</strong>: Uses a behind-the-scenes scoring system to detect abusive traffic, and lets you decide the minimum passing score. Recommended for advanced use only (or if using Google AMP).', 'wpforms-lite' ),
			[ 'strong' => [] ]
		);
	?>
	</li>
</ul>
<p>
	printf(
		wp_kses( /* translators: %s - WPForms.com Setup reCAPTCHA URL. */
			__( '<a href="%s" target="_blank" rel="noopener noreferrer">Read our walk through</a> to learn more and for step-by-step directions.', 'wpforms-lite' ),
			[
				'a' => [
					'href'   => [],
					'target' => [],
					'rel'    => [],
				],
			]
		),
		esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-set-up-and-use-recaptcha-in-wpforms/', 'Settings - Captcha', 'reCAPTCHA Documentation' ) )
	);
	?>
</p>

} // namespace CharacterGeneratorDev
