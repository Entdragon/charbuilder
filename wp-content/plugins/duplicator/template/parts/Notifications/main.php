<?php

namespace CharacterGeneratorDev {

/**
 * Admin Notifications template.
 *
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

defined('ABSPATH') || exit;

?>
<div id="dup-notifications">
    <div class="dup-notifications-header">
        <div class="dup-notifications-bell">
            <span class="wp-ui-notification dup-notifications-circle"></span>
        </div>
    </div>

    <div class="dup-notifications-body">

            <div class="navigation">
                <a class="prev">
                    <span aria-hidden="true">&lsaquo;</span>
                </a>
                <a class="next">
                    <span aria-hidden="true">&rsaquo;</span>
                </a>
            </div>

        <div class="dup-notifications-messages">
                $tplMng->render('parts/Notifications/single-message', $notification);
            } ?>
        </div>
    </div>
</div>

} // namespace CharacterGeneratorDev
