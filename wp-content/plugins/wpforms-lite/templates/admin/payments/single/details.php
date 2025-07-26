<?php

namespace CharacterGeneratorDev {

/**
 * Single Payment page - Details metabox.
 *
 * @since 1.8.2
 *
 * @var object $payment        Payment object.
 * @var string $submitted      Submitted date.
 * @var string $gateway_name   Gateway name.
 * @var string $gateway_link   Link to gateway payment details.
 * @var string $form_edit_link Link to the builder.
 * @var string $delete_link    Link for a payment delete action.
 * @var bool   $test_mode      Is payment in test mode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="wpforms-payment-details" class="postbox">

	<div class="postbox-header">
		<h2 class="hndle">
		</h2>
	</div>

	<div class="inside">

		<div class="wpforms-payment-details-meta">

			<p class="wpforms-payment-date">
				<span class="dashicons dashicons-calendar"></span>
				<strong class="date-time">
				</strong>
			</p>

			<p class="wpforms-payment-gateway">
				<span class="dashicons dashicons-store"></span>
			</p>

			<p class="wpforms-payment-form">
				<span class="dashicons dashicons-wpforms"></span>
				esc_html_e( 'Form:', 'wpforms-lite' );
				// Output the form edit link, if available.
				// The output could be a link or a span, depending on the availability of the form.
				echo wp_kses(
					$form_edit_link,
					[
						'a'    => [
							'href'  => [],
							'class' => [],
						],
						'span' => [ 'class' => [] ],
					]
				);
				?>
			</p>

            <p class="wpforms-payment-test-mode">
                <span class="dashicons dashicons-marker"></span>
            </p>
		</div>

		<div class="wpforms-payment-actions">
			<div class="status"></div>
			<div class="actions">
				</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>

} // namespace CharacterGeneratorDev
