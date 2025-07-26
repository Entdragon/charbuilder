<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Hooks\HooksMng;

$archiveConfig = DUPX_ArchiveConfig::getInstance();

/* Variables */
/* @var $paramView string */
?>
<table cellspacing="0" class="header-wizard">
    <tr>
        <td style="width:100%;">
            <div class="dupx-branding-header">
                <i class="fa fa-bolt fa-sm"></i>
            </div>
        </td>
        <td class="wiz-dupx-version">
            <div style="padding: 6px 0">
                        DUPX_View_Funcs::installerLogLink();
                        echo '<span>&nbsp;|&nbsp;</span>';
                        DUPX_View_Funcs::helpLink($paramView, 'Help<i class="fas fa-question-circle main-help-icon fa-sm"></i>');
                    ?>
                    &nbsp;
            </div>
        </td>
    </tr>
</table>
dupxTplRender('pages-parts/head/server-details');

} // namespace CharacterGeneratorDev
