<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapOS;

$paramsManager = PrmMng::getInstance();
$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<div class="help-target">
</div>

<div class="hdr-sub3">Processing</div>
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ARCHIVE_ENGINE);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ZIP_THROTTLING);
?>

<div class="hdr-sub3 margin-top-2">Extraction Flow</div>
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ARCHIVE_ACTION);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_FILE_TIME);
if (!SnapOS::isWindows()) {
    ?>
    <div class="param-wrapper" >
        &nbsp;
    </div>
    <div class="param-wrapper" >
        &nbsp;
    </div>
}
?>

<div class="hdr-sub3 margin-top-2">Configuration Files</div>
$paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONFIG);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_HTACCESS_CONFIG);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_OTHER_CONFIG);
?>

<div class="hdr-sub3 margin-top-2">General</div>
$paramsManager->getHtmlFormParam(PrmMng::PARAM_USERS_MODE);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_LOGGING);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_REMOVE_RENDUNDANT);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_REMOVE_USERS_WITHOUT_PERMISSIONS);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_SAFE_MODE);




} // namespace CharacterGeneratorDev
