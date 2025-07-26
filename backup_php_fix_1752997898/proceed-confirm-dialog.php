<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

/**
 * Variables
 *
 * @var int $tableCount
 */
$paramsManager = PrmMng::getInstance();
$recoveryLink  = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
$txtTable      = $tableCount . ' table' . ($tableCount == 1 ? '' : 's');
?>
<div id="db-install-dialog-confirm" title="Install Confirmation" style="display:none">
    <p>
        <i>Run installer with these settings?</i>
    </p>

    <div class="hdr-sub3">
        Site Settings
    </div>
   
    <table class="margin-bottom-1 margin-left-1  dup-s1-confirm-dlg">
        <tr>
            <td>Install Type: &nbsp; </td>
        </tr>
        <tr>
            <td>New URL:</td>
        </tr>
        <tr>
            <td>New Path:</td>
        </tr>
    </table> 

    <div class="hdr-sub3">
       Database Settings
    </div>
    <table class="margin-left-1 margin-bottom-1 dup-s1-confirm-dlg">
        <tr>
            <td>Server:</td>
        </tr>
        <tr>
            <td>Name:</td>
        </tr>
        <tr>
            <td>User:</td>
        </tr>
        <tr>
            <td>Data:</td>
                <td class="maroon">
                </td>
                <td>
                    No existing tables will be overwritten in the database
                </td>
        </tr>
    </table>

        <div class="margn-bottom-1" >
            <small class="maroon">
                <i class="fas fa-exclamation-circle"></i>
                and/or removed! Only proceed if the data is no longer needed. Entering the wrong information WILL overwrite an existing database.
                Make sure to have backups of all your data before proceeding.
            </small>
        </div>
</div>
} // namespace CharacterGeneratorDev
