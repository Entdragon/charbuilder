<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
$cpnlCanSel    = true;

$cpnlDisplay  = 'no-display';
$basicDisplay = '';

if ($cpnlCanSel && $paramsManager->getValue(PrmMng::PARAM_DB_VIEW_MODE) === 'cpnl') {
    $cpnlDisplay  = '';
    $basicDisplay = 'no-display';
}
?>
<div id="base-setup-area-header" class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#base-setup-area">
    <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Setup</a>
</div>
<div id="base-setup-area" class="hdr-sub1-area dupx-opts">
    <div id="s1-area-setup-tabs" class="hdr-sub1-area tabs-area dupx-opts" >
        <div class="tabs">
            <ul>
                <li>
                    <a href="#tabs-1-setup">Database</a>
                </li>
                <li>
                    <a href="#tabs-2-setup">Settings</a>
                </li>
            </ul>
            <div id="tabs-1-setup">
                if ($cpnlCanSel) {
                    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_VIEW_MODE);
                }
                ?>
                </div>

                        dupxTplRender('pages-parts/step1/database-tabs/cpanel-panel');
                        ?>
                    </div>

            </div>
            <div id="tabs-2-setup">
            </div>
        </div>
    </div>
</div>
} // namespace CharacterGeneratorDev
