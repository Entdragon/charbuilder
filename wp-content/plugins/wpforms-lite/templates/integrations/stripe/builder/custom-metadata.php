<?php

namespace CharacterGeneratorDev {

/**
 * Stripe Settings - Custom Metadata table template.
 *
 * @since 1.9.6
 *
 * @var array  $custom_metadata Saved Metadata.
 * @var string $subsection      Current subsection.
 * @var string $slug            Field slug.
 * @var array  $form_data       Form data.
 * @var array  $fields          Allowed fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wpforms-panel-field-stripe-custom-metadata">

	<p>
	</p>

	<table class="wpforms-panel-content-section-stripe-custom-metadata-table">
		<thead>
		<tr>
			<th>
			</th>
			<th>
			</th>
			<th colspan="3">
			</th>
		</tr>
		</thead>
		<tbody>
		foreach ( $custom_metadata as $key => $value ) :
			$is_hidden = ! $key ? 'hidden' : '';
			?>
				<td>
					wpforms_panel_field(
						'select',
						$slug,
						'object_type',
						$form_data,
						'',
						[
							'parent'      => 'payments',
							'subsection'  => $subsection,
							'index'       => $key,
							'placeholder' => esc_html__( '--- Select Object Type ---', 'wpforms-lite' ),
							'options'     => [
								'customer' => esc_html__( 'Customer', 'wpforms-lite' ),
								'payment'  => esc_html__( 'Payment', 'wpforms-lite' ),
							],
							'input_class' => 'wpforms-panel-field-stripe-custom-metadata-object-type',
						]
					);
					?>
				</td>
				<td>
					wpforms_panel_field(
						'text',
						$slug,
						'meta_key',
						$form_data,
						'',
						[
							'parent'      => 'payments',
							'subsection'  => $subsection,
							'index'       => $key,
							'input_class' => 'wpforms-panel-field-stripe-custom-metadata-meta-key',
						]
					);
					?>
				</td>
				<td>
					wpforms_panel_field(
						'select',
						$slug,
						'meta_value',
						$form_data,
						'',
						[
							'parent'      => 'payments',
							'subsection'  => $subsection,
							'index'       => $key,
							'field_map'   => $fields,
							'placeholder' => esc_html__( '--- Select Meta Value ---', 'wpforms-lite' ),
							'input_class' => 'wpforms-panel-field-stripe-custom-metadata-meta-value',
						]
					);
					?>
				</td>
				<td class="add">
						<i class="fa fa-plus-circle"></i>
					</button>
				</td>
				<td class="delete">
						<i class="fa fa-minus-circle"></i>
					</button>
				</td>
			</tr>
		</tbody>
	</table>
</div>

} // namespace CharacterGeneratorDev
