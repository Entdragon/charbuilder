<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerLinkManager;

/**
 * Variables
 *
 * @var int      $testResult    DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...]
 * @var string[] $faildDirPerms
 * @var array    $phpPerms
 */
?>
<div class="sub-title">STATUS</div>
    <p class="red">
        Some folders do not have write permission, see details for more information.
    </p>
    <p class="green">
        Write permissions granted for WordPress core directories.
    </p>   

<div class="sub-title">DETAILS</div>
<table>
    <tr>
        <td>
            Deployment Path:
        </td>
        <td>
        </td>
    </tr>
    <tr>
        <td>
            Check folders permission:
        </td>
        <td>
            if (count($faildDirPerms) == 0) {
            } else {
            }
            ?>
        </td>
    </tr>
        <tr>
            <td>
            </td>
            <td>
                <span class="green">Pass</span>
        </tr>       
    <tr>
        <td>
            Suhosin Extension:
        </td>
        <td>    
            if (!extension_loaded('suhosin')) {
            } else {
            }
            ?>
        </td>
    </tr>
    <tr>
        <td>
            PHP Safe Mode:
        </td>
        <td>
            if (!DUPX_Server::phpSafeModeOn()) {
            } else {
            }
            ?>
        </td>
    </tr>
</table>

<p>
    <b>Overwrite fails for these folders (change permissions or remove then restart):</b>
</p>
<div class="validation-iswritable-failes-objects">
    foreach ($faildDirPerms as $failedPath) {
        echo '- ' . DUPX_U::esc_html($failedPath) . "\n";
    }
    ?></pre>
</div>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        If there are problems with permissions (not writable files or folders) try to change the permissions to 755 for folders or 644 for files.
        <a href="https://en.wikipedia.org/wiki/File-system_permissions" target="_blank">Here you can find general information about File-system permissions.</a>
    </li>
    <li>
        Generally if the folders have write permissions but it is not possible to extract the PHP files, 
        the cause could be an external security service like "Imunify 360".
        If this is the case 
        <a 
                'how-to-fix-installer-archive-extraction-issues',
                'install',
                'validation writable deactivate checks'
            ); ?>" 
            target="_blank"
        >
            deactivate the checks
        </a> 
        temporarily, and run the installation again.
    </li>
    <li>
    </li>
</ul>

} // namespace CharacterGeneratorDev
