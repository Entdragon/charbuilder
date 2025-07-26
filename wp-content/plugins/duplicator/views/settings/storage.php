<?php

namespace CharacterGeneratorDev {


use Duplicator\Controllers\StorageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapUtil;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$action_updated  = false;
$action_response = esc_html__("Storage Settings Saved", 'duplicator');

// Save results
if (SnapUtil::sanitizeTextInput(INPUT_POST, 'action') === 'save') {
    // Nonce check
    $nonce = SnapUtil::sanitizeTextInput(INPUT_POST, 'dup_storage_settings_save_nonce_field');
    if (!wp_verify_nonce($nonce, 'dup_settings_save')) {
        wp_die(esc_html__('Invalid token permissions to perform this request.', 'duplicator'));
    }

    DUP_Settings::Set('storage_htaccess_off', SnapUtil::sanitizeBoolInput(INPUT_POST, 'storage_htaccess_off'));
    DUP_Settings::Save();
    $action_updated = true;
}

$storageHtaccessCheck = DUP_Settings::Get('storage_htaccess_off');
$actionUrl            = ControllersManager::getMenuLink(ControllersManager::SETTINGS_SUBMENU_SLUG, 'storage');
$storagePath          = DUP_Settings::Get('storage_position');
// Keep the storage path in sync with the storage position to identify the correct storage path after migration
$storagePath = $storagePath === DUP_Settings::STORAGE_POSITION_WP_CONTENT ? DUP_Settings::getSsdirPathWpCont() : DUP_Settings::getSsdirPathLegacy();
?>
<style>
    div.panel {padding: 20px 5px 10px 10px;}
    div.area {font-size:16px; text-align: center; line-height: 30px; width:500px; margin:auto}
    ul.li {padding:2px}
</style>

<div class="panel">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="page"   value="duplicator-settings">


        <table class="form-table">
            <tr valign="top">
                <td>
                    <p>
                    </p>
                    <p class="description">
                        <br/>
                        <i class="fas fa-server fa-sm"></i>&nbsp;
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <td>
                    <p class="description">
                            esc_html_e("When checked this setting will prevent Duplicator from laying down an .htaccess file in the storage location above.", 'duplicator');
                            esc_html_e("Only disable this option if issues occur when downloading either the installer/archive files.", 'duplicator');
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <p class="submit" style="margin: 20px 0px 0xp 5px;">
            <br/>
        </p>
    </form>
    <br/>
</div>
<!-- ==========================================
THICK-BOX DIALOGS: -->
$storageAlert = StorageController::getDialogBox('settings-storage-tab');
?>
<script>
    jQuery(document).ready(function ($) {
        $("#duplicator_advanced_storage_text").click(function () {
        });
    });
</script>

} // namespace CharacterGeneratorDev
