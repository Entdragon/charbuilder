<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<div id="tabs-2">
    <table class="s1-archive-local">
        <tr>
            <td colspan="2"><div class="hdr-sub3">Archive File</div></td>
        </tr>
        <tr>
            <td>Created:</td>
        </tr>
        <tr>
            <td>Size:</td>
        </tr>
        <tr>
            <td>Archive:</td>
        </tr>
    </table>


    <table class="s1-archive-local">
        <tr>
            <td colspan="2"><div class="hdr-sub3">Site Details</div></td>
        </tr>
        <tr>
            <td>Site:</td>
        </tr>
        <tr>
            <td>URL:</td>
        </tr>
        <tr>
            <td>Notes:</td>
        </tr>
            <tr>
                <td>Mode:</td>
                <td>Archive only database was enabled during package package creation.</td>
            </tr>
    </table>

</div>
} // namespace CharacterGeneratorDev
