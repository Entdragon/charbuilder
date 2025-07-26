<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_table_list->errors) && is_wp_error($this->admin_table_list->errors)) {
	foreach ($this->admin_table_list->errors->get_error_messages() as $error)
		$errors []= $error;
}

$messages = common::getTransientMessages('customtables_success_message');

$page = common::inputGetCmd('page');

$allowed_html = array(
	'a' => array(
		'href' => array(),
		'title' => array(),
		'download' => array(),
		'target' => array()
	)
);

?>
<div class="wrap">

    <a href="admin.php?page=customtables-tables-edit&table=0"


    <hr class="wp-header-end">

        <ol>
        </ol>
        <hr/>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-table-list-form" method="post">
                $this->admin_table_list->search_box(esc_html__('Find', 'customtables'), 'nds-table-find');
                $this->admin_table_list->views();
                $this->admin_table_list->display();
                ?>
            </form>
        </div>
    </div>
</div>

<p><a href="https://ct4.us/contact-us/"


} // namespace CharacterGeneratorDev
