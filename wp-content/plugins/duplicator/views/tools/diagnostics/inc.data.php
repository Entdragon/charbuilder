<?php

namespace CharacterGeneratorDev {


use Duplicator\Utils\Support\SupportToolkit;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

?>

<!-- ==============================
OPTIONS DATA -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fa fa-th-list"></i>
        <div class="dup-box-arrow"></div>
    </div>
        <table class="dup-reset-opts">
            <tr style="vertical-align:text-top">
                <td>
                    <button id="dup-remove-installer-files-btn" type="button" class="button button-small dup-fixed-btn" onclick="Duplicator.Tools.deleteInstallerFiles();">
                    </button>
                </td>
                <td>

                    <div id="dup-tools-delete-moreinfo">
                            esc_html_e("Clicking on the 'Remove Installation Files' button will attempt to remove the installer files used by Duplicator.  These files should not "
                            . "be left on production systems for security reasons. Below are the files that should be removed.", 'duplicator');
                            echo "<br/><br/>";

                            $installer_files = array_keys($installer_files);
                            array_push($installer_files, '[HASH]_archive.zip/daf');
                            echo '<i>' . implode('<br/>', $installer_files) . '</i>';
                            echo "<br/><br/>";
                            ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <button type="button" class="button button-small dup-fixed-btn" onclick="Duplicator.Tools.ConfirmClearBuildCache()">
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <button 
                       type="button"
                       id="dup-download-diagnostic-data-btn"
                       class="button button-small dup-fixed-btn" 
                       >
                    </button>
                </td>
                <td>
                        <i 
                           class="fa fa-question-circle data-size-help" 
                           data-tooltip-title="Diagnostic Data"
                           aria-expanded="false">
                        </i>
                </td>
            </tr>
        </table>
    </div>
</div>
<br/>

<!-- ==========================================
THICK-BOX DIALOGS: -->
    $confirmClearBuildCache             = new DUP_UI_Dialog();
    $confirmClearBuildCache->title      = __('Clear Build Cache?', 'duplicator');
    $confirmClearBuildCache->message    = __('This process will remove all build cache files. Be sure no backups are currently building or else they will be cancelled.', 'duplicator');
    $confirmClearBuildCache->jscallback = 'Duplicator.Tools.ClearBuildCache()';
    $confirmClearBuildCache->initConfirm();
?>

<script>
jQuery(document).ready(function($)
{
    Duplicator.Tools.ConfirmClearBuildCache = function ()
    {
    }

    Duplicator.Tools.ClearBuildCache = function ()
    {
    }

    $('#dup-download-diagnostic-data-btn').click(function () {
    });
});


Duplicator.Tools.deleteInstallerFiles = function()
{
    $url = DUP_CTRL_Tools::getCleanFilesAcrtionUrl();
    echo "window.location = '{$url}';";
    ?>
}
</script>

} // namespace CharacterGeneratorDev
