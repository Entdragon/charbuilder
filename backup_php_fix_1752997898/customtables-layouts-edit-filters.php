<?php

namespace CharacterGeneratorDev {

<table class="form-table" role="presentation">
	<tr class="form-field form-required">
		<!-- Layout Name Field -->
		<th scope="row">
			<label for="filter">
			</label>
		</th>
		<td>
			<input name="filter" type="text" id="filter" style="min-width: 150px;"
				   aria-required="false"
				   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="1024"/>
			<br/>

			<span class="description">
  <strong>Basic Format:</strong><br>
  Field=value<br><br>

  <strong>Comparison Operators:</strong><br>
  =, &#60;, &#60;=, &#62;, &#62;=, !=, == (exact match)<br><br>

  <strong>Dynamic Values:</strong><br>
  • User data: {{ user.id }}<br>
  • URL parameters: {{ url.getint('param_name') }}<br>
  • Current date: {{ 'now'|date('m/d/Y') }}<br><br>

  <strong>Multiple Conditions:</strong><br>
  Combine with 'and' or 'or'<br><br>

  <strong>Examples:</strong><br>
  • URL parameter: color={{ url.getstring('string') }}<br>
  • Date formats:<br>
    - Full date: {{ 'now'|date('m/d/Y') }}<br>
    - Year only: {{ 'now'|date('Y') }}<br>
    - Month only: {{ 'now'|date('m') }}
</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<!-- Layout Name Field -->
		<th scope="row">
			<label for="showpublished">
			</label>
		</th>
		<td>

			$allowed_html = array(
				'option' => array(
					'value' => array(),
					'selected' => array())
			);

			$showPublished = $this->admin_layout_edit->params['showpublished'] ?? '';

			$options_escaped = '';

			$options = ['0' => 'Show Published Only', '1' => 'Show Unpublished Only', '2' => 'Show Any'];

			foreach ($options as $key => $option) {
				$options_escaped .= '<option value="' . esc_html($key) . '"';
				if ($key == $showPublished) {
					$options_escaped .= ' selected';
				}
				$options_escaped .= '>' . esc_html($option) . '</option>';
			}
			?>

			<select name="showpublished" id="showpublished">
			</select>
		</td>
	</tr>


</table>
} // namespace CharacterGeneratorDev
