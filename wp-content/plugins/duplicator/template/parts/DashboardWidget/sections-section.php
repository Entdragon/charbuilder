<?php

namespace CharacterGeneratorDev {


/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$templatesURL = ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    'templates'
);
$recoveryURl  = ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    'recovery'
);

?>
<div class="dup-section-sections">
    <ul>
        <li class="dup-flex-content">
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-update gary"></span>
                    echo esc_html(sprintf(
                        _n(
                            '%s Schedule',
                            '%s Schedules',
                            $tplData['numSchedules'],
                            'duplicator'
                        ),
                        $tplData['numSchedules']
                    ));
                    ?></a>
            </span>
            <span>
                </b>
            </span>
        </li>
        <li>
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-database gary"></span>
                    echo esc_html(sprintf(
                        _n(
                            '%s Storage',
                            '%s Storages',
                            $tplData['numStorages'],
                            'duplicator'
                        ),
                        $tplData['numStorages']
                    ));
                    ?>
                </a>
            </span>
        </li>
        <li>
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-admin-settings gary"></span>
                    echo esc_html(sprintf(
                        _n(
                            '%s Template',
                            '%s Templates',
                            $tplData['numTemplates'],
                            'duplicator'
                        ),
                        $tplData['numTemplates']
                    ));
                    ?>
                </a>
            </span>
        </li>
        <li  class="dup-flex-content">
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-image-rotate gary"></span>
                    esc_html_e('Recovery Point', 'duplicator');
                ?> 
                </a>
            </span>
            <span>
            </span>
        </li>
    </ul>
</div>

} // namespace CharacterGeneratorDev
