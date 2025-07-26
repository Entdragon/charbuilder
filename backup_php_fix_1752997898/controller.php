<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Views\TplMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/ui/class.ui.dialog.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php');

global $wpdb;
global $wp_version;

DUP_Handler::init_error_handler();
DUP_Util::hasCapability('manage_options');
$current_tab = isset($_REQUEST['tab']) ? esc_html($_REQUEST['tab']) : 'diagnostics';
if ('d' == $current_tab) {
    $current_tab = 'diagnostics';
}
?>

<div class="wrap">  

    <h2 class="nav-tab-wrapper">  
    </h2>

    switch ($current_tab) {
        case 'diagnostics':
            include(DUPLICATOR_PLUGIN_PATH . 'views/tools/diagnostics/main.php');
            break;
        case 'templates':
            TplMng::getInstance()->render('mocks/templates/templates', array(), true);
            break;
        case 'recovery':
            TplMng::getInstance()->render('mocks/recovery/recovery', array(), true);
            break;
    }
    ?>
</div>

} // namespace CharacterGeneratorDev
