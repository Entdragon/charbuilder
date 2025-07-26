<?php

namespace CharacterGeneratorDev {


/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var array{name: string,slug: string,more: string,pro: array{file: string}} $plugin
 */
$plugin = $tplData['plugin'];

/** @var string */
$installUrl = $tplData['installUrl'];
$moreUrl    = $plugin['more'] . '?' . http_build_query(array(
    'utm_medium' => 'link',
    'utm_source'   => 'duplicatorplugin',
    'utm_campaign' => 'duplicatordashboardwidget'
));

?>
<div class="dup-section-recommended">
    <hr>
    <div class="dup-flex-content" >
        <div>
            <span class="dup-recommended-label">
            </span>
            -
            <span class="action-links">
                    esc_html_e('Learn More', 'duplicator');
                ?></a>
            </span>
        </div>
        <div>
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
    </div>
</div>

} // namespace CharacterGeneratorDev
