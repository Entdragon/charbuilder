<?php

namespace CharacterGeneratorDev {

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\MigrationMng;
use Duplicator\Libs\Snap\SnapUtil;

$safeMsg         = MigrationMng::getSaveModeWarning();
$cleanupReport   = MigrationMng::getCleanupReport();
$cleanFileAction = (SnapUtil::filterInputRequest('action', FILTER_DEFAULT) === 'installer');
?>
<div class="dup-notice-success notice notice-success duplicator-pro-admin-notice dup-migration-pass-wrapper">
    <div class="dup-migration-pass-title">
        if (MigrationMng::getMigrationData('restoreBackupMode')) {
            _e('This site has been successfully restored!', 'duplicator');
        } else {
            _e('This site has been successfully migrated!', 'duplicator');
        }
        ?>
    </div>
    <p>
    </p>
    <ul class="dup-stored-minstallation-files">
            <li>
            </li>
    </ul>

    if ($cleanFileAction) {
        require DUPLICATOR_LITE_PATH . '/views/parts/migration-clean-installation-files.php';
    } else {
        if (count($cleanupReport['instFile']) > 0) { ?>
            <p>
            </p>
            <ul class="dup-stored-minstallation-files">
                foreach ($cleanupReport['instFile'] as $html) { ?>
                    <li>
                    </li>
            </ul>
        <p>
            <span id="dpro-notice-action-remove-installer-files" class="link-style" onclick="Duplicator.Tools.deleteInstallerFiles();" >
            </span>
        </p>
            <div class="notice-safemode">
            </div>

        <p class="sub-note">
                _e(
                    'Note: This message will be removed after all installer files are removed.'
                    . ' Installer files must be removed to maintain a secure site.'
                    . ' Click the link above to remove all installer files and complete the migration.',
                    'duplicator'
                );
                ?><br>
                <i class="fas fa-info-circle"></i>
                _e(
                    'If an archive.zip/daf file was intentially added to the root directory to '
                    . 'perform an overwrite install of this site then you can ignore this message.',
                    'duplicator'
                )
                ?>
            </i>
        </p>
    }

    echo apply_filters(MigrationMng::HOOK_BOTTOM_MIGRATION_MESSAGE, '');
    ?>
</div>

} // namespace CharacterGeneratorDev
