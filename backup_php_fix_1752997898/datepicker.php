<?php

namespace CharacterGeneratorDev {

/**
 * Popover (range) datepicker template.
 *
 * @since 1.8.2
 * @since 1.8.8 Added the `$hidden_fields` parameter.
 *
 * @var string $action        The URL that processes the form submission. Ideally points out to the current admin page URL.
 * @var string $id            Identifier to outline the context of where the datepicker will be used. e.g., "entries".
 * @var string $chosen_filter Currently selected filter or date range values. Last "X" days, or i.e. Feb 8, 2023 - Mar 9, 2023.
 * @var array  $choices       A list of date filter options for the datepicker module.
 * @var string $value         Assigned timespan dates.
 * @var array  $hidden_fields An array of hidden fields to be included in the form.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// An array of allowed HTML elements and attributes for the datepicker choices.
$choices_allowed_html = [
	'li'    => [],
	'label' => [],
	'input' => [
		'type'        => [],
		'name'        => [],
		'value'       => [],
		'checked'     => [],
		'aria-hidden' => [],
	],
];

// Hidden fields to be included in the form submission.
// `orderby` and `order` are always included by default.
$default_hidden_fields = [ 'orderby', 'order' ];
$hidden_fields         = array_merge( $default_hidden_fields, $hidden_fields ?? [] );

?>
	// Output hidden fields for the current orderby and order values.

	// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	foreach ( $hidden_fields as $field ) {
		if ( empty( $_REQUEST[ $field ] ) ) {
			continue;
		}

		echo '<input type="hidden" name="' . esc_attr( $field ) . '" value="' . esc_attr( wp_unslash( $_REQUEST[ $field ] ) ) . '" />';
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	?>

	<button id="wpforms-datepicker-popover-button" class="button" role="button" aria-haspopup="true">
	</button>
	<div class="wpforms-datepicker-popover">
		<div class="wpforms-datepicker-popover-content">
			</ul>
			<div class="wpforms-datepicker-calendar">
				<input
					type="text"
					name="date"
					tabindex="-1"
					aria-hidden="true"
				>
			</div>
			<div class="wpforms-datepicker-action">
			</div>
		</div>
	</div>
</form>

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

} // namespace CharacterGeneratorDev
