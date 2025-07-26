<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">
	<!-- Google Map -->
	<tr class="form-field form-required">
		<th scope="row">
			<label for="googlemapapikey">
			</label>
		</th>
		<td>
			<input name="googlemapapikey" type="text" id="googlemapapikey"
				   aria-required="false"
				   autocapitalize="none" autocomplete="off" maxlength="40"/>
		</td>
	</tr>
	<!-- Google Drive -->
	<tr class="form-field form-required">
		<th scope="row">
			<label for="googledriveapikey">
			</label>
		</th>
		<td>
			<input name="googledriveapikey" type="text" id="googledriveapikey"
				   aria-required="false"
				   autocapitalize="none" autocomplete="off" maxlength="40"/>
		</td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row">
			<label for="googledriveclientid">
			</label>
		</th>
		<td>
			<input name="googledriveclientid" type="text" id="googledriveclientid"
				   aria-required="false"
				   autocapitalize="none" autocomplete="off" maxlength="100"/>
		</td>
	</tr>


</table>

} // namespace CharacterGeneratorDev
