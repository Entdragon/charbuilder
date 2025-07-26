<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\MigrationMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$safeMsg = MigrationMng::getSaveModeWarning();
$nonce   = wp_create_nonce('duplicator_cleanup_page');
$url     = DUP_CTRL_Tools::getDiagnosticURL();
?>
<div class="dup-notice-success notice notice-success duplicator-pro-admin-notice dup-migration-pass-wrapper" >
    <p>
        if (MigrationMng::getMigrationData('restoreBackupMode')) {
            _e('Restore Backup Almost Complete!', 'duplicator');
        } else {
            _e('Migration Almost Complete!', 'duplicator');
        }
        ?></b>
    </p>
    <p>
        esc_html_e(
            'Reserved Duplicator installation files have been detected in the root directory.  '
            . 'Please delete these installation files to avoid security issues.',
            'duplicator'
        );
        ?>
        <br/>
        esc_html_e('Go to: Duplicator > Tools > General > Information > Utils and click the "Remove Installation Files" button', 'duplicator'); ?><br>
        </a>
    </p>
        <div class="notice-safemode">
        </div>
    <p class="sub-note">
            _e(
                'If an archive.zip/daf file was intentially added to the root '
                . 'directory to perform an overwrite install of this site then you can ignore this message.',
                'duplicator'
            );
            ?>
        </i>
    </p>

</div>
} // namespace CharacterGeneratorDev
