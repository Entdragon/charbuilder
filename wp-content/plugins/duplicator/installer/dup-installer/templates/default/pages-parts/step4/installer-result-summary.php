<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$nManager     = DUPX_NOTICE_MANAGER::getInstance();
$noticesCount = DUPX_Ctrl_S4::getNoticesCount();
?>

<div class="sub-title">
    <b>Install Result</b>
</div>
<table id="report-summary" class="s4-report-results margin-bottom-2">
    <tbody>
        <tr>
            <td class="desc" >General Notices</td>
        </tr>
        <tr>
            <td class="desc" >Files Status</td>
        </tr>
        <tr>
            <td class="desc" >Database Status</td>
        </tr>
        <tr>
            <td class="desc" >Search and Replace Status</td>
        </tr>
        <tr>
            <td class="desc" >Plugins Status</td>
        </tr>
    </tbody>
</table>
} // namespace CharacterGeneratorDev
