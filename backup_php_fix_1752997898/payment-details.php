<?php

namespace CharacterGeneratorDev {

/**
 * Single Payment page - Payment details template for single and subscription data.
 *
 * @since 1.8.2
 * @since 1.8.6 Added $class variable.
 *
 * @var string $id                  Block id.
 * @var string $class               Extra Class based on type of payment.
 * @var string $title               Block title.
 * @var string $payment_id          Payment id.
 * @var string $gateway_link        Link to gateway payment details.
 * @var string $gateway_text        Gateway link text.
 * @var string $gateway_name        Gateway name.
 * @var string $gateway_action_text Gateway action link text.
 * @var string $gateway_action_link Gateway action link.
 * @var string $gateway_action_slug Gateway action slug.
 * @var int    $payment_id_raw      Payment id raw.
 * @var string $status              Payment or Subscription status.
 * @var string $status_label        Payment or Subscription status label.
 * @var bool   $disabled            Is gateway action disabled.
 * @var array  $stat_cards          Stat cards to display.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>


	<div class="postbox-header">
		<h2 class="hndle">
		</h2>
	</div>

	<div class="inside">
		<ul class="wpforms-payments-details-list">
				<li class="wpforms-payments-details-stat-card">
						<span class="stat-card-value">
						</span>
					</button>
				</li>
		</ul>
	</div>

	<div class="wpforms-payment-actions">
		<div class="status">
		</div>
		<div class="actions">
			</a>
					class="button wpforms-payments-single-action"
					target="_blank"
					rel="noopener noreferrer"
				</a>
		</div>
		<div class="clear"></div>
	</div>
</div>

} // namespace CharacterGeneratorDev
