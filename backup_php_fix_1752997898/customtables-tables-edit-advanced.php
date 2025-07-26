<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">
	<tr class="form-field form">
		<th scope="row">
			<label for="customphp">
			</label>
		</th>
		<td>
			<input name="customphp" type="text" id="customphp"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<span class="description">
			Custom Tables allows you to execute custom PHP code when users perform specific actions on records (save, refresh, publish, or unpublish). This is accomplished through a custom PHP file with a process() function.
				<br/>
				More <a href="https://ct4.us/docs/custom-php-event-handler-for-custom-tables-in-joomla/" target="_blank">here</a>.
			</span>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customtablename">
			</label>
		</th>
		<td>
			<select name="customtablename" id="customtablename">

					?>
					</option>
			</select>

			<br/>
			<span class="description">
				To connect to third-party tables - any table in the database. Except #__users and Custom Tables
			</span>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customidfield">
			</label>
		</th>
		<td>
			<input name="customidfield" type="text" id="customidfield"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<span class="description">Defaults to id, but this can be customized for tables with different key naming conventions.</span>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customidfieldtype">
			</label>
		</th>
		<td>
			<input name="customidfieldtype" type="text" id="customidfieldtype"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<span class="description">Determines how the primary key field is created in the database.<br>
Defaults to AUTO_INCREMENT for MySQL, but supports any valid SQL type and constraints.</span>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="primarykeypattern">
			</label>
		</th>
		<td>
			<input name="primarykeypattern" type="text" id="primarykeypattern"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<div class="description">
				<p>Define how primary keys are generated for new records.<br/>
					Use AUTO_INCREMENT for automatic numbering, or create custom patterns using Twig syntax.</p>
				<b>Examples:</b>
				<ul>
					<li>AUTO_INCREMENT</li>
					<li>PROJECT-{{ random(1000,9999) }}</li>
					<li>{{ 'prefix-' ~ now|date('Y-m-d H:i:s.u')|md5 }}</li>
					<li>PRJ-{{ (now|date('Y-m-d H:i:s.u')|md5)|slice(0,10) }}-{{ random(1000,9999) }}</li>
				</ul>
			</div>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customfieldprefix">
			</label>
		</th>
		<td>
			<input name="customfieldprefix" type="text" id="customfieldprefix"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<span class="description">
				Specifies the prefix added to all table field names (e.g., 'ct_FieldName').<br/>
				This prefix helps prevent conflicts with MySQL reserved words and ensures database compatibility.<br/>
				Only modify this if you have a specific reason to use a different prefix scheme.<br/>
				Type NO-PREFIX to have field names without a prefix.<br/>
				Changing the prefix doesn't automatically renames fields. You will have to do it manually.
			</span>
		</td>
	</tr>
</table>

} // namespace CharacterGeneratorDev
