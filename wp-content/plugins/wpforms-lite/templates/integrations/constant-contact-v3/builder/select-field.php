<?php

namespace CharacterGeneratorDev {


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection-block">
	<h4>{{ data.field.label }}<# if ( data.field.required ) { #><span class="required">*</span><# } #></h4>
	<select
		class="wpforms-builder-constant-contact-v3-provider-connection-{{data.name}} <# if ( data.field.map ) { #> wpforms-field-map-select<# } #><# if ( data.field.required ) { #> wpforms-required<# } #>"
		name="providers[{{ data.provider.slug }}][{{ data.connection.id }}][{{ data.name }}]"
		<# if ( data.field.map ) { #>
			data-field-map-allowed="{{ data.field.map }}"
		<# } #>
	>
		<# fieldValue = data.connection[data.name] ?? ''; #>
		<option value="">{{ data.field.placeholder }}</option>
		<# _.each( data.options, function( option, key ) {
			selected = fieldValue.toString() === option.id.toString(); #>
			<option value="{{ option.id }}" <# if ( selected ) { #> selected<# } #> >
				{{ option.label }}
			</option>
		<# } ) #>
	</select>
</div>

} // namespace CharacterGeneratorDev
