<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

// @var $showInstallerMode bool
// @var $showSwitchView bool

//$showInstallerMode = !isset($showInstallerMode) ? true : $showInstallerMode;
$showInstallerMode = false;
$showSwitchView    = !isset($showSwitchView) ? false : $showSwitchView;
$showInstallerLog  = !isset($showInstallerLog) ? false : $showInstallerLog;
?>
<div id="header-main-wrapper" >
    <div class="hdr-main">
    </div>
    <div class="hdr-secodary">
            <div class="dupx-modes">
            </div>
        }
        if ($showInstallerLog) {
            ?>
            <div class="installer-log" >
            </div>
        }
        if ($showSwitchView) {
            dupxTplRender('pages-parts/step1/actions/switch-template');
        }
        ?>
    </div>
</div>
} // namespace CharacterGeneratorDev
