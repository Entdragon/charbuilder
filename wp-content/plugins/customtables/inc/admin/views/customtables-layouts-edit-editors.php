<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<h2 class="nav-tab-wrapper wp-clearfix">
	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(0,'layoutcode');return false;" data-toggle="tab"
			data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutcode-tab" class="nav-tab nav-tab-active">HTML (Desktop)
	</button>

	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(1,'layoutmobile');return false;"
			data-toggle="tab" data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutmobile-tab" class="nav-tab">HTML
		(Mobile)
	</button>

	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(2,'layoutcss');return false;"
			data-toggle="tab" data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutcss-tab" class="nav-tab">CSS
	</button>

	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(3,'layoutjs');return false;" data-toggle="tab"
			data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutjs-tab" class="nav-tab">JavaScript
	</button>

	<button type="button" data-toggle="tab"
			data-tabs=".gtabs.layouteditorTabs" data-tab=".filters-tab" class="nav-tab">Filters
	</button>

	<button type="button" data-toggle="tab"
			data-tabs=".gtabs.layouteditorTabs" data-tab=".params-tab" class="nav-tab">Parameters
	</button>
</h2>

<div class="gtabs layouteditorTabs">

	<div class="gtab active layoutcode-tab" style="margin-left:-20px;">
		<textarea id="layoutcode"
	</div>

	<div class="gtab layoutmobile-tab" style="margin-left:-20px;">
			<textarea id="layoutmobile" name="layoutmobile">
			</textarea>
			<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
			</a>
	</div>

	<div class="gtab layoutcss-tab" style="margin-left:-20px;">
			<textarea id="layoutcss" name="layoutcss">
			</textarea>
			<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
			</a>
	</div>

	<div class="gtab layoutjs-tab" style="margin-left:-20px;">
			<textarea id="layoutjs" name="layoutjs">
			</textarea>
			<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
			</a>
	</div>

	<div class="gtab filters-tab" style="margin-left:-20px;">
	</div>

	<div class="gtab params-tab" style="margin-left:-20px;">
	</div>
</div>

} // namespace CharacterGeneratorDev
