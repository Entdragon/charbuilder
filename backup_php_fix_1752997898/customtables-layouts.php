<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_layout_list->errors) && is_wp_error($this->admin_layout_list->errors)) {
	foreach ($this->admin_layout_list->errors->get_error_messages() as $error)
		$errors []= $error;
}

$messages = common::getTransientMessages('customtables_success_message');
if (count($this->admin_layout_list->IntegrityChecksResult) > 0)
	$messages = array_merge($messages, $this->admin_layout_list->IntegrityChecksResult);

$page = absint(common::inputGetInt('page', 0));

$allowed_html = array(
	'li' => array()
);

?>
<div class="wrap">
    <a href="admin.php?page=customtables-layouts-edit&layout=0"

    <hr class="wp-header-end">


    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-layout-list-form" method="post">
				$this->admin_layout_list->search_box(esc_html__('Find', 'customtables'), 'nds-layout-find');
				$this->admin_layout_list->views();
				$this->admin_layout_list->display();
				?>
            </form>
        </div>
    </div>
</div>
} // namespace CharacterGeneratorDev
