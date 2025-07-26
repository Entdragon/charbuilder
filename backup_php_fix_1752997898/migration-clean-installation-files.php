<?php

namespace CharacterGeneratorDev {



defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\MigrationMng;
use Duplicator\Utils\LinkManager;
use Duplicator\Views\AdminNotices;

?>
<div class="dpro-diagnostic-action-installer">
    <p>
    </p>
    $fileRemoved = MigrationMng::cleanMigrationFiles();
    $removeError = false;
    if (count($fileRemoved) === 0) {
        ?>
        <p>
    } else {
        foreach ($fileRemoved as $path => $success) {
            if ($success) {
                ?><div class="success">
            } else {
                ?><div class="failed">
                $removeError = true;
            }
        }
    }
    foreach (MigrationMng::purgeCaches() as $message) {
        ?><div class="success">
        </div>
    }

    if ($removeError) {
        ?>
        <p>
            <span class="link-style" onclick="Duplicator.Tools.deleteInstallerFiles();">
            </span><br>
        </p>
    } else {
        delete_option(AdminNotices::OPTION_KEY_MIGRATION_SUCCESS_NOTICE);
    }
    ?>
    <div style="font-style: italic; max-width:900px; padding:10px 0 25px 0;">
        <p>
            _e(
                ' If the installer files do not successfully get removed with this action, '
                . 'then they WILL need to be removed manually through your hosts control panel '
                . 'or FTP.  Please remove all installer files to avoid any security issues on this site.',
                'duplicator'
            );
            ?><br>
            printf(
                _x(
                    'For more details please visit the FAQ link %1$sWhich files need to be removed after an install?%2$s',
                    '%1$s and %2$s are <a> tags',
                    'duplicator'
                ),
                '<a href="' . esc_url(LinkManager::getDocUrl('which-files-need-to-be-removed-after-an-install', 'migration-notice')) . '" target="_blank">',
                '</a>'
            );
            ?>
        </p>
        <p>
            _e('The Duplicator team has worked many years to make moving a WordPress site a much easier process. ', 'duplicator');
            echo '<br/>';
            printf(
                esc_html_x(
                    'Show your support with a %1$s5 star review%2$s! We would be thrilled if you could!',
                    '%1$s and %2$s are <a> tags',
                    'duplicator'
                ),
                '<a href="' . esc_url(\Duplicator\Core\Notifications\Review::getReviewUrl()) . '" target="_blank">',
                '</a>'
            );
            ?>
        </p>
    </div>
</div>

} // namespace CharacterGeneratorDev
