<?php

namespace CharacterGeneratorDev {

<table class="form-table" role="presentation">

	<tr class="form-field form-required">
		<th scope="row">
			<label for="viewusergroups">
			</label>
		</th>
		<td>
			<br/>
			<span class="description">Select user groups that may view records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>
	*/ ?>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="addusergroups">
			</label>
		</th>
		<td>
			<br/>
			<span class="description">Select user groups that may add new records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="editusergroups">
			</label>
		</th>
		<td>
			<br/>
			<span class="description">Select user groups that may edit existing records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="publishusergroups">
			</label>
		</th>
		<td>
			<br/>
			<span class="description">Select user groups that may publish and unpublish records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="deleteusergroups">
			</label>
		</th>
		<td>
			<br/>
			<span class="description">Select user groups that may delete records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="publishstatus">
			</label>
		</th>
		<td>
				isset($this->admin_layout_edit->params['publishstatus']) ?
					(int)$this->admin_layout_edit->params['publishstatus'] :
					0
			); ?>
			<br/>
			<span class="description">Sets the default publish status for newly created records. If none selected, inherits from parent layout or defaults to Unpublished.</span>
		</td>
	</tr>
</table>
} // namespace CharacterGeneratorDev
