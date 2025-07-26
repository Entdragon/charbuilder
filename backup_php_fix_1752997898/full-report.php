<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager   = PrmMng::getInstance();
$nManager        = DUPX_NOTICE_MANAGER::getInstance();
$finalReportData = $paramsManager->getValue(PrmMng::PARAM_FINAL_REPORT_DATA);
?>
<div id="s4-install-report" >    
    <div id="s4-notice-reports" class="report-sections-list">
        $nManager->displayFinalRepostSectionHtml('general', 'General Notices Report');
        $nManager->displayFinalRepostSectionHtml('files', 'Files Notices Report');
        $nManager->displayFinalRepostSectionHtml('database', 'Database Notices Report');
        $nManager->displayFinalRepostSectionHtml('search_replace', 'Search and Replace Notices Report');
        $nManager->displayFinalRepostSectionHtml('plugins', 'Plugins Actions Report');
        ?>
    </div>

    <table class="s4-report-results" >
        <tr>
            <th colspan="4">Database Report</th>
        </tr>
        <tr style="font-weight:bold">
            <td style="width:150px"></td>
            <td>Tables</td>
            <td>Rows</td>
            <td>Cells</td>
        </tr>
        <tr>
            <td>Created</td>
            <td>n/a</td>
        </tr>
        <tr>
            <td>Scanned</td>
        </tr>
        <tr>
            <td>Updated</td>
        </tr>
    </table>
</div>

} // namespace CharacterGeneratorDev
