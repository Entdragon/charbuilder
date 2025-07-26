<?php

namespace CharacterGeneratorDev {


/**
 * @package Duplicator
 */

use Duplicator\Utils\LinkManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */


$upgradeUrl = LinkManager::getCampaignUrl('lite-multisite-notice', 'Upgrade now!')
?>
<span class='dashicons dashicons-warning'></span>
<div class="dup-sub-content">
    <h3 class="margin-bottom-0 margin-top-0">
    </h3>
    <p>
        sprintf(
            _x(
                'By upgrading to the Elite or Pro plans you will unlock the ability to create backups and do advanced migrations on multi-site installations!',
                '1: name of pro plan, 2: name of elite plan',
                'duplicator-pro'
            )
        )
    ); ?>
    </p>
</div>

} // namespace CharacterGeneratorDev
