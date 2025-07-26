<?php

namespace CharacterGeneratorDev {


use Duplicator\Utils\LinkManager;
use Duplicator\Core\Bootstrap;
use Duplicator\Core\Views\TplMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
DUP_Util::hasCapability('manage_options');
global $wpdb;

//COMMON HEADER DISPLAY
$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'detail';
$package_id  = isset($_REQUEST["id"])  ? sanitize_text_field($_REQUEST["id"]) : 0;
$package     = DUP_Package::getByID($package_id);
$err_found   = ($package == null || $package->Status < 100);
?>

<style>
    .narrow-input { width: 80px; }
    .wide-input {width: 400px; }
     table.form-table tr td { padding-top: 25px; }
     div.all-packages {float:right; margin-top: -35px; }
</style>

<div class="wrap">
        duplicator_header(
            sprintf(
                esc_html_x(
                    'Backup Details &raquo; %1$s',
                    '%1$s represents the Backup name',
                    'duplicator'
                ),
                esc_html($package->Name)
            )
        );
        ?>

    <div class="error">
        <p>
            _x(
                'This Backup contains an error. Please review the %1$sBackup log%2$s for details.',
                '%1 and %2 are replaced with <a> and </a> respectively',
                'duplicator'
            ),
            '<a href="' . DUP_Settings::getSsdirUrl() . '/{$package->NameHash}.log" target="_blank">',
            '</a>'
        );
        ?>
            _x(
                'For help visit the %1$sFAQ%2$s and %3$sresources page%4$s.',
                '%1, %3 and %2, %4 are replaced with <a> and </a> respectively',
                'duplicator'
            ),
            '<a href="' . esc_url(LinkManager::getCategoryUrl(LinkManager::TROUBLESHOOTING_CAT, 'failed_package_details_notice', 'FAQ')) . '" target="_blank">',
            '</a>',
            '<a href="' . esc_url(LinkManager::getCategoryUrl(LinkManager::RESOURCES_CAT, 'failed_package_details_notice', 'resources page')) . '" target="_blank">',
            '</a>'
        );
        ?>
        </p>
    </div>

    <h2 class="nav-tab-wrapper">
        </a>
        </a>
    </h2>

    switch ($current_tab) {
        case 'detail':
            include(DUPLICATOR_PLUGIN_PATH . 'views/packages/details/detail.php');
            break;
        case 'transfer':
            Bootstrap::mocksStyles();
            TplMng::getInstance()->render('mocks/transfer/transfer', array(), true);
            break;
    }
    ?>
</div>

} // namespace CharacterGeneratorDev
