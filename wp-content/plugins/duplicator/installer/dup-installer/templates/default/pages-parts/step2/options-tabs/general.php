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
        Charset & Collation
    </div>
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_CHARSET);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_COLLATE);
    }
    ?>
</div>

} // namespace CharacterGeneratorDev
