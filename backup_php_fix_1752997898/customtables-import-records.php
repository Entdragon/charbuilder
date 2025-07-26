<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CTMiscHelper;

$errors = common::getTransientMessages('customtables_error_message');
$messages = common::getTransientMessages('customtables_success_message');

$max_file_size = CTMiscHelper::file_upload_max_size();

?>

<div class="wrap">


    <form method="post" action="" id="esFileUploaderForm_Tables" enctype="multipart/form-data">
        wp_nonce_field('import-table'); // Add a nonce field
        ?>

        <p>
            esc_html_e('Maximum allowed file size', 'customtables');
            echo ': ';
            echo esc_html(CTMiscHelper::formatSizeUnits($max_file_size));
            ?>
        </p>

        <input type="file" name="filetosubmit" accept=".csv"
               onchange="document.getElementById('upload-file').disabled=false"/>
        <input type="submit" id="upload-file" name="upload_file" class="button"
               disabled=""/>
        <input type="hidden" name="action" value="import-csv"/>

</div>

} // namespace CharacterGeneratorDev
