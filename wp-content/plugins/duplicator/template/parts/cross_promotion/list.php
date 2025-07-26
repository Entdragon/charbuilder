<?php

namespace CharacterGeneratorDev {


use Duplicator\Utils\ExtraPlugins\ExtraItem;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

defined('ABSPATH') || die();

if (!current_user_can('install_plugins')) {
    return;
}

/** @var ExtraItem[] $plugins */
$plugins = $tplData['plugins'];
/** @var int $limit */
$limit = $tplData['limit'];
?>
<div id="dup-cross-promotion">
    <p>
    </p>
    <div class="list">
        foreach ($plugins as $i => $plugin) {
            if ($i > $limit - 1) {
                break;
            }

            $tplMng->render(
                'parts/cross_promotion/item',
                array('plugin' => $plugin)
            );
        }
        ?>
    </div>
</div>

} // namespace CharacterGeneratorDev
