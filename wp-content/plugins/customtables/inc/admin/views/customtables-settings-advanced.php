<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">
	<tr class="form-field form-required">
		<th scope="row">
			<label for="fieldprefix">
			</label>
		</th>
		<td>
			<input name="fieldprefix" type="text" id="fieldprefix"

				   $vlu = get_option('customtables-fieldprefix');
				   if (empty($vlu))
					   $vlu = 'ct_';

				   echo esc_html($vlu); ?>"
				   aria-required="false"
				   autocapitalize="none" autocomplete="off" maxlength="100"/>
			<br/>
			Specifies the prefix added to all table field names (e.g., 'ct_FieldName'). This prefix helps prevent
			conflicts with MySQL reserved words and ensures database compatibility. Only modify this if you have a
			specific reason to use a different prefix scheme. Type NO-PREFIX to have field names without a prefix.
			Changing the prefix doesn't automatically rename fields. You will have to do it manually.
		</td>
	</tr>
</table>
} // namespace CharacterGeneratorDev
