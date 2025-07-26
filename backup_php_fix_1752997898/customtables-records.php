<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_record_list->errors) && is_wp_error($this->admin_record_list->errors)) {
	foreach ($this->admin_record_list->errors->get_error_messages() as $error)
		$errors []= $error;
}
$messages = common::getTransientMessages('customtables_success_message');

$page = common::inputGetCmd('page');

?>
<div class="wrap">

        . esc_html__('&laquo; Tables', 'customtables') . '</a>&nbsp;<br/>';?>

    <h1 class="wp-heading-inline">
        if (!empty($this->admin_record_list->ct->Table)) {
            esc_html_e('Custom Tables - Table', 'customtables');
            echo esc_html(' "' . $this->admin_record_list->ct->Table->tabletitle . '" - ');
            esc_html_e('Records', 'customtables');
        } else {
            esc_html_e('Custom Tables - Records', 'customtables');
            echo esc_html__('Table not selected or not found.', 'customtables');
        }
        ?>
    </h1>

    if (!empty($this->admin_record_list->ct->Table)) {
        $tableId = (int)$this->admin_record_list->tableId;

        $nonce = wp_create_nonce('customtables-records-edit');
        echo '<a href="admin.php?page=customtables-records-edit&table=' . esc_html($tableId) . '&id=0&_wpnonce=' . $nonce . '" class="page-title-action">';
        echo esc_html__('Add New', 'customtables');
        echo '</a>';

        $nonce = wp_create_nonce('customtables-import-records');
        echo '<a href="admin.php?page=customtables-import-records&table=' . esc_html($tableId) . '&_wpnonce=' . $nonce . '" class="page-title-action">';
        echo esc_html__('Import CSV', 'customtables');
        echo '</a>';
    }
    ?>


    <hr class="wp-header-end"/>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-record-list-form" method="post">
                $this->admin_record_list->search_box(esc_html__('Find', 'customtables'), 'nds-record-find');
                $this->admin_record_list->views();
                $this->admin_record_list->display();
                ?>
            </form>
        </div>
    </div>
</div>

<div class="CustomTablesDocumentationTips">
    <h4>Adding Catalog Views and Edit Forms</h4>
    <p>You can use these shortcodes to display table records and add/edit forms:</p>
    <br/>
    <p style="font-weight:bold;">Basic Catalog Views</p>
    <p style="font-weight:bold;">Edit Forms</p>
    <p style="font-weight:bold;">Catalog with Parameters</p>
    <p>Note: The limit parameter controls the number of displayed records. Use limit="0" or omit the parameter to show all records.</p>
</div>



} // namespace CharacterGeneratorDev
