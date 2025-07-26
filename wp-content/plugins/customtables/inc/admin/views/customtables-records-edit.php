<?php

namespace CharacterGeneratorDev {




if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\Edit;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_record_edit->errors) && is_wp_error($this->admin_record_edit->errors)) {
	foreach ($this->admin_record_edit->errors->get_error_messages() as $error)
		$errors []= $error;
}
$messages = common::getTransientMessages('customtables_success_message');

try {
	$editForm = new Edit($this->admin_record_edit->ct);
	$editForm->layoutContent = $this->admin_record_edit->pageLayout;
	$editForm_render_safe = $editForm->render($this->admin_record_edit->recordRow, $this->admin_record_edit->formLink, 'adminForm', false);
} catch (Exception $e) {
	$errors []= $e->getMessage();
	$editForm_render_safe = '';
}

//include ('customtables-records-edit-help.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

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
        <h1 id="add-new-user">
            if (isset($this->admin_record_edit->ct->Table) and $this->admin_record_edit->ct->Table->tablename !== null) {
                esc_html_e('Custom Tables - Table', 'customtables');
                echo ' "' . esc_html($this->admin_record_edit->ct->Table->tabletitle) . '" - ';
                if ($this->admin_record_edit->listing_id === null)
                    esc_html_e('Add New Record');
                else
                    esc_html_e('Edit Record');
            } else {
                esc_html_e('Custom Tables - Records', 'customtables');
                $errors []= esc_html__('Table not selected or not found.', 'customtables');
            }
            ?>
        </h1>


        <div id="ajax-response"></div>

        if (current_user_can('install_plugins')) {

            if (isset($this->admin_record_edit->ct->Table) and $this->admin_record_edit->ct->Table->tablename !== null):
                ?>

                    if ($this->admin_record_edit->listing_id === null)
                        esc_html_e('Create a brand new record.');
                    else
                        esc_html_e('Edit record.');
                    ?>
                </p>
                <form method="post" name="createrecord" id="createrecord" class="validate" novalidate="novalidate" enctype="multipart/form-data">
                    <input name="action" type="hidden" value="createrecord"/>


                    <div style="display:inline-block;">
                    </div>
                    <div style="display:inline-block;margin-left:20px;">
                        <!-- Cancel Button -->
                        submit_button(esc_html__('Cancel', 'customtables'), 'secondary', 'createrecord-cancel', true,
                            array('id' => 'createrecord-cancel', 'onclick' => 'window.location.href="admin.php?page=customtables-records&table=' . esc_html($this->admin_record_edit->tableId) . '";return false;'));
                        ?></div>
                </form>
    </div>


    <h4>Adding Record Edit Forms</h4>
    <p>You can use these shortcodes to insert forms for adding or editing records:</p>
    <br/>



require_once ABSPATH . 'wp-admin/admin-footer.php';

} // namespace CharacterGeneratorDev
