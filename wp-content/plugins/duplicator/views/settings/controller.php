<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Bootstrap;
use Duplicator\Core\Views\TplMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

DUP_Handler::init_error_handler();
DUP_Util::hasCapability('export');

global $wpdb;

//COMMON HEADER DISPLAY
require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/ui/class.ui.dialog.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/ui/class.ui.messages.php');

$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'general';
?>

<div class="wrap dup-settings-pages">

    <h2 class="nav-tab-wrapper">
        <a 
        >
        </a>
        <a 
        >
        </a>
        <a 
        >
        </a>
        <a 
        >
        </a>
        <a 
        >
        </a>
    </h2>

    switch ($current_tab) {
        case 'general':
            TplMng::getInstance()->render("admin_pages/settings/general/general");
            break;
        case 'package':
            include(DUPLICATOR_PLUGIN_PATH . "views/settings/packages.php");
            break;
        case 'storage':
            include(DUPLICATOR_PLUGIN_PATH . "views/settings/storage.php");
            break;
        case 'access':
            Bootstrap::mocksStyles();
            TplMng::getInstance()->render("mocks/settings/access/capabilities");
            break;
        case 'license':
            include(DUPLICATOR_PLUGIN_PATH . "views/settings/license.php");
            break;
    }
    do_action('duplicator_settings_page_footer');
    ?>
</div>

} // namespace CharacterGeneratorDev
