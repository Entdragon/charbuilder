<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div class="help-target">
</div>
<div  class="dupx-opts">
    if (DUPX_InstallerState::isRestoreBackup()) {
        dupxTplRender('parts/restore-backup-mode-notice');
    } else {
        ?>
    <div class="hdr-sub3">
        Import and Update
    </div>
    <small>
        To exclude a table from install click the "Import" checkbox.  To prevent a table from being updated with the new site data click the "Update" checkbox.
    </small><br/>
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_TABLES);
    }?>
</div>
} // namespace CharacterGeneratorDev
