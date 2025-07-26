<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerLinkManager;
use Duplicator\Libs\Snap\SnapURL;

$paramsManager = PrmMng::getInstance();
$nManager      = DUPX_NOTICE_MANAGER::getInstance();
$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>

<div class="sub-title">
    <b>Review</b>
</div>
<ul class="final-review-actions" >
    <li>
        <!--or re-run the installer and
            go back to step 1
        </span> -->
    </li>
    <li>
        $wpconfigNotice = $nManager->getFinalReporNoticeById('wp-config-changes');
        $htaccessNorice = $nManager->getFinalReporNoticeById('htaccess-changes');
        ?>
    </li>
    <li>
    </li>
</ul>

} // namespace CharacterGeneratorDev
