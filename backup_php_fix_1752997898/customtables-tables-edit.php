<?php

namespace CharacterGeneratorDev {



if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_table_edit->errors) && is_wp_error($this->admin_table_edit->errors)) {
	foreach ($this->admin_table_edit->errors->get_error_messages() as $error)
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

if ($this->admin_table_edit->tableId === null)
	$new_tablename = '';
else
	$new_tablename = $this->admin_table_edit->ct->Table->tablename;

if ($this->admin_table_edit->ct->Env->advancedTagProcessor) {
	if ($this->admin_table_edit->tableId === null)
		$customphp = '';
	else
		$customphp = $this->admin_table_edit->ct->Table->tablerow['customphp'];

	$customTableName = $this->admin_table_edit->ct->Table->tablerow['customtablename'] ?? '';

	$customIdField = $this->admin_table_edit->ct->Table->tablerow['customidfield'] ?? '';

	if (empty($customIdField))
		$customIdField = 'id';

	$customIdFieldType = $this->admin_table_edit->ct->Table->tablerow['customidfieldtype'] ?? '';

	if (empty($customIdFieldType))
		$customIdFieldType = 'int UNSIGNED NOT NULL AUTO_INCREMENT';

	$primaryKeyPattern = $this->admin_table_edit->ct->Table->tablerow['primarykeypattern'] ?? '';

	if (empty($primaryKeyPattern))
		$primaryKeyPattern = 'AUTO_INCREMENT';

	$customFieldPrefix = $this->admin_table_edit->ct->Table->tablerow['customfieldprefix'] ?? '';
}

?>
	<div class="wrap">
		<h1>
			if ($this->admin_table_edit->tableId == 0)
				esc_html_e('Add New Custom Table');
			else
				esc_html_e('Edit Custom Table');
			?>
		</h1>


		<div id="ajax-response"></div>


			<form method="post" name="createtable" id="createtable" class="validate" novalidate="novalidate">
				<input name="action" type="hidden" value="createtable"/>

				<h2 class="nav-tab-wrapper wp-clearfix">
					<button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs" data-tab=".tableName-tab"
							class="nav-tab nav-tab-active">Table Name
					</button>
					<button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs"
							data-tab=".advanced-tab"
							class="nav-tab">Advanced
					</button>
					<button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs"
							data-tab=".schema-tab"
							class="nav-tab">Schema
					</button>
				</h2>

				<div class="gtabs tableEditTabs">
					<div class="gtab active tableName-tab" style="margin-left:-20px;">

						<table class="form-table" role="presentation">
							<!-- Table Name Field -->
							<tr class="form-field form-required">
								<th scope="row">
									<label for="tablename">
									</label>
								</th>
								<td>
									<input name="tablename" type="text" id="tablename"
										   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
								</td>
							</tr>

							<!-- Table Title Fields -->
							$moreThanOneLang = false;
							foreach ($this->admin_table_edit->ct->Languages->LanguageList as $lang): ?>
								$id = ($moreThanOneLang ? 'tabletitle_' . $lang->sef : 'tabletitle');
								$cssclass = ($moreThanOneLang ? 'form-control valid form-control-success' : 'form-control required valid form-control-success');
								$att = ($moreThanOneLang ? '' : ' required ');

								$vlu = $item_array[$id] ?? ($this->admin_table_edit->ct->Table !== null ? $this->admin_table_edit->ct->Table->tablerow[$id] : '');
								?>

									<th scope="row">
											<br/>
										</label>
									</th>
									<td>
									</td>
								</tr>
						</table>
					</div>

					<div class="gtab advanced-tab" style="margin-left:-20px;">
							<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
							</a>
					</div>

					<div class="gtab schema-tab" style="margin-left:-20px;">
						<div class="CustomTablesDocumentationTips">
						</div>
					</div>

				</div>

				<div style="display:inline-block;">
				</div>

				<div style="display:inline-block;margin-left:20px;">
					<!-- Cancel Button -->
					submit_button(esc_html__('Cancel', 'customtables'), 'secondary', 'createtable-cancel', true,
						array('id' => 'createtable-cancel', 'onclick' => 'window.location.href="admin.php?page=customtables-tables";return false;'));
					?></div>

			</form>
	</div>


	<div class="CustomTablesDocumentationTips">
		<h4>Adding Catalog Views and Edit Forms</h4>
		<p>You can use these shortcodes to display table records and add/edit forms:</p>
		<br/>
		<p style="font-weight:bold;">Basic Catalog Views</p>
		<div>
			- Displays catalog view using table name
		</div>
		<div>
			- Displays catalog view using table ID
		</div>
		<div>
			- Explicit catalog view
		</div>
		<p style="font-weight:bold;">Edit Forms</p>
		<div>
			- Adds a form to create a new record
		</div>
		<p style="font-weight:bold;">Catalog with Parameters</p>
		<div>
			- Shows only 5 records
		</div>
		<p>Note: The limit parameter controls the number of displayed records. Use limit="0" or omit the parameter to
			show
			all records.</p>
	</div>

	<p><a href="https://ct4.us/contact-us/"

require_once ABSPATH . 'wp-admin/admin-footer.php';

} // namespace CharacterGeneratorDev
