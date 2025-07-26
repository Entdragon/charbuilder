<?php

namespace CharacterGeneratorDev {



if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_settings->errors) && is_wp_error($this->admin_settings->errors)) {
	foreach ($this->admin_settings->errors->get_error_messages() as $error)
		$errors []= $error;
}
$messages = common::getTransientMessages('customtables_success_message');

$allowed_html = array(
	'a' => array(
		'href' => array(),
		'title' => array(),
		'download' => array(),
		'target' => array()
	)
);

?>

<div class="wrap ct_doc">


	<div id="ajax-response"></div>



		<form method="post" name="settings" id="settings" class="validate" novalidate="novalidate">
			<input name="action" type="hidden" value="save-settings"/>



			<h2 class="nav-tab-wrapper wp-clearfix">
				<button type="button" data-toggle="tab" data-tabs=".gtabs.settings" data-tab=".tab-ui"
						class="nav-tab nav-tab-active">
					User Interface
				</button>
				<button type="button" data-toggle="tab" data-tabs=".gtabs.settings" data-tab=".tab-api" class="nav-tab">
					API
				</button>
				<button type="button" data-toggle="tab" data-tabs=".gtabs.settings" data-tab=".tab-advanced"
						class="nav-tab">
					Advanced
				</button>
			</h2>

			<div class="gtabs settings">
				<div class="gtab active tab-ui">
				</div>

				<div class="gtab tab-api">
				</div>

				<div class="gtab tab-advanced">
				</div>
			</div>

			<!-- Submit Button -->
			$buttonText = esc_html__('Save Settings', 'customtables');
			submit_button($buttonText, 'primary', 'savesettings');
			?>

			<p><a href="https://ct4.us/contact-us/"
			</p>
		</form>


</div>
} // namespace CharacterGeneratorDev
