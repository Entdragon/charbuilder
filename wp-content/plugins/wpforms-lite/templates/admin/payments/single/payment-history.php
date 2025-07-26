<?php

namespace CharacterGeneratorDev {

/**
 * Single Payment page - Display a table outlining the subscription payment history.
 *
 * @since 1.8.4
 *
 * @var string $title               Table heading.
 * @var array  $renewals            Renewal payments data.
 * @var array  $types               Payment types.
 * @var array  $statuses            Payment statuses.
 * @var string $placeholder_na_text Placeholder text. Display "N\A" if empty.
 * @var string $single_url          Single payment URL. Note that payment ID will be appended to this URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="postbox">
	<div class="postbox-header">
		<h2 class="hndle">
		</h2>
	</div>
		<thead>
			<tr>
		</thead>
		<tbody>
			foreach ( $renewals as $renewal ) :
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$is_current = isset( $_GET['payment_id'] ) && $renewal->id === wp_unslash( $_GET['payment_id'] );
			?>
						</a>
					</td>
					</td>
					</td>
					</td>
					</td>
				</tr>
		</tbody>
	</table>
</div>

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

} // namespace CharacterGeneratorDev
