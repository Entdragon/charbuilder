<?php

namespace CharacterGeneratorDev {


/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Controllers\PackagesPageController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var DUP_Package[] $packages
 */
$packages = $tplData['packages'];

?>
<div class="dup-section-last-packages">
    <p>
    </p>
    <ul>
            $createdTime  = strtotime($package->Created);
            $createdDate  = date_i18n(get_option('date_format'), $createdTime);
            $createdHours = date_i18n(get_option('time_format'), $createdTime);

            ?>
            <li>
            </li>
    </ul>
    <p class="dup-packages-counts">
    </p>
</div>

} // namespace CharacterGeneratorDev
