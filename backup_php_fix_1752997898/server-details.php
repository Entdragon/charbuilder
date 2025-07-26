<?php

namespace CharacterGeneratorDev {


$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<div id="dialog-server-details" title="Setup Information" style="display:none">
    <!-- DETAILS -->
    <div class="dlg-serv-info">
        $ini_path       = php_ini_loaded_file();
        $ini_max_time   = ini_get('max_execution_time');
        $ini_memory     = ini_get('memory_limit');
        $ini_error_path = ini_get('error_log');
        ?>
        <div class="hdr">SERVER DETAILS</div>
        <label>Try CDN Request:</label>

        <br/>
        <div class="hdr">PACKAGE BUILD DETAILS</div>

    </div>
</div>

<script>
    DUPX.openServerDetails = function ()
    {
        $("#dialog-server-details").dialog({
            resizable: false,
            height: "auto",
            width: 700,
            modal: true,
            position: {my: 'top', at: 'top+150'},
            buttons: {"OK": function () {
                    $(this).dialog("close");
                }}
        });
    }
</script>
} // namespace CharacterGeneratorDev
