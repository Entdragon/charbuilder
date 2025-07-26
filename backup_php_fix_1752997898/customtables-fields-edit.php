<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_field_edit->errors) && is_wp_error($this->admin_field_edit->errors)) {
	foreach ($this->admin_field_edit->errors->get_error_messages() as $error)
		$errors [] = $error;
}

$messages = common::getTransientMessages('customtables_success_message');

require_once ABSPATH . 'wp-admin/admin-header.php';

$allowed_html = array(
	'a' => array(
		'href' => array(),
		'title' => array(),
		'download' => array(),
		'target' => array()
	)
);

foreach ($this->admin_field_edit->allTables as $table) {
	$tableID = $table['id'];

	try {
		$tempCT = new CT([], true);
		$tempCT->getTable($tableID);

		if ($tempCT->Table !== null) {
			$list = [];
			foreach ($tempCT->Table->fields as $field)
				$list[] = [$field['id'], $field['fieldname']];

			echo '<div id="fieldsData' . $tableID . '" style="display:none;">' . common::ctJsonEncode($list) . '</div>
    ';
		}
	} catch (Exception $e) {
		$errors [] = $e->getMessage();
	}
}
?>
	<div class="wrap">
		<h1 id="add-new-user">
			if (isset($this->admin_field_edit->ct->Table) and $this->admin_field_edit->ct->Table->tablename !== null) {
				esc_html_e('Custom Tables - Table', 'customtables');
				echo ' "' . esc_html($this->admin_field_edit->ct->Table->tabletitle) . '" - ';
				if ($this->admin_field_edit->fieldId == 0)
					esc_html_e('Add New Field');
				else
					esc_html_e('Edit Field');
			} else {
				esc_html_e('Custom Tables - Fields', 'customtables');
				$errors [] = 'Table not selected or not found.';
			}
			?>
		</h1>


		<div id="ajax-response"></div>

		if (current_user_can('install_plugins')) {
			?>


			if (isset($this->admin_field_edit->ct->Table) and $this->admin_field_edit->ct->Table->tablename !== null):
				?>

					if ($this->admin_field_edit->fieldId === null)
						esc_html_e('Create a brand new field.');
					else
						esc_html_e('Edit field.');
					?>
				</p>

				<script>
					if ($this->admin_field_edit->ct->Env->advancedTagProcessor)
						echo esc_js('proversion=true;') . PHP_EOL;

					//resulting line example: all_tables=[["29","kot3","kot3"],["30","kot5","kot5"],["31","kot6","kot6"],["25","test1","Test 1"]];
					echo 'all_tables=' . wp_kses_post(wp_json_encode($this->admin_field_edit->allTables)) . ';' . PHP_EOL;
					?>
				</script>

				<form method="post" name="createfield" id="createfield" class="validate" novalidate="novalidate">
					<input name="action" type="hidden" value="createfield"/>
					<input name="table" id="table" type="hidden"

					<table class="form-table" role="presentation">
						<!-- Field Name Field -->
						<tr class="form-field form-required">
							<th scope="row">
								<label for="fieldname">
								</label>
							</th>
							<td>
								<input name="fieldname" type="text" id="fieldname"
									   aria-required="true"
									   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
							</td>
						</tr>

						<!-- Field Title Fields -->
						$moreThanOneLang = false;
						foreach ($this->admin_field_edit->ct->Languages->LanguageList as $lang): ?>
							$id = ($moreThanOneLang ? 'fieldtitle_' . $lang->sef : 'fieldtitle');
							$cssclass = ($moreThanOneLang ? 'form-control valid form-control-success' : 'form-control required valid form-control-success');
							$att = ($moreThanOneLang ? '' : ' required ');
							$vlu = $this->admin_field_edit->fieldRow[$id] ?? null;
							?>

								<th scope="row">
										<br/>
									</label>
								</th>
								<td>
								</td>
							</tr>


						<!-- Field Type Field -->
						<tr class="form-field form-required">
							<th scope="row">
								<label for="type">
								</label>
							</th>
							<td>

								$allowed_html = array(
									'option' => array(
										'value' => array(),
										'selected' => array()
									)
								);

								$selectBoxOptions = [];

								foreach ($this->admin_field_edit->fieldTypes as $type) {
									$selected = $this->admin_field_edit->fieldRow['type'] == $type['name'];
									$selectBoxOptions[] = '<option value="' . $type['name'] . '"' . ($selected ? ' selected="selected"' : '') . '>' . $type['label'] . '</option>';
								}

								$selectBoxOptionsSafe = implode('', $selectBoxOptions);

								echo '<select name="type" id="type" onchange="typeChanged();">' . wp_kses($selectBoxOptionsSafe, $allowed_html) . '</select>';
								?>
							</td>
						</tr>

						<!-- Field Type Params Field -->
						<tr class="form-field form-required">
							<th scope="row">
								<label for="typeparams">
								</label>
							</th>
							<td>
								<div class="typeparams_box" id="typeparams_box"></div>
								<br/>
								<input type="hidden" name="typeparams" id="typeparams" class=""
									   readonly="readonly" maxlength="1024"
							</td>
						</tr>

						<!-- Is Field Required -->
						<tr class="form-field">
							<th scope="row">
								<label for="isrequired">
								</label>
							</th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
									</legend>

									<label class="radio-container">
										<input type="radio"
											   name="isrequired"
											   value="1"
										/>
									</label>

									<br/>

									<label class="radio-container">
										<input type="radio"
											   name="isrequired"
											   value="0"
										/>
									</label>
								</fieldset>
							</td>
						</tr>
					</table>

					<!-- Submit Button -->
					<div style="display:inline-block;">
					</div>

					<div style="display:inline-block;margin-left:20px;">
						<!-- Cancel Button -->
						submit_button(esc_html__('Cancel', 'customtables'), 'secondary', 'createfield-cancel', true,
							array('id' => 'createfield-cancel', 'onclick' => 'window.location.href="admin.php?page=customtables-fields&table=' . esc_html($this->admin_field_edit->tableId) . '";return false;'));
						?></div>

					<script>
						updateTypeParams("type", "typeparams", "typeparams_box");
						//disableProField("jform_defaultvalue");
						//disableProField("jform_valuerule");
						//disableProField("jform_valuerulecaption");
						proversion = true;
					</script>

					<div id="ct_fieldtypeeditor_box"
						echo implode(',', common::folderList(CUSTOMTABLES_IMAGES_PATH)); ?></div>
				</form>

		<p><a href="https://ct4.us/contact-us/"
	</div>
require_once ABSPATH . 'wp-admin/admin-footer.php';

} // namespace CharacterGeneratorDev
