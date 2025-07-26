<?php

namespace CharacterGeneratorDev {


/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$tooltipTitle   = esc_attr__('Backup creation', 'duplicator');
$tooltipContent = esc_attr__(
    'This will create a new Backup. If a Backup is currently running then this button will be disabled.',
    'duplicator'
);

?>
<div class="dup-section-package-create dup-flex-content">
    <span>
        <span class="dup-last-backup-info">
        </span>
    </span>
    <span
        class="dup-new-package-wrapper"
    >
        <a  
            id="dup-pro-create-new" 
        >
        </a>
    </span>
</div>

} // namespace CharacterGeneratorDev
