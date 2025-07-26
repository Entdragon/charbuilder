<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\InstallerLinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $isCpanel bool */
/* @var $dbname string */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
    } else {
        if ($alreadyExists) {
            ?>
            Please continue with the following options:<br/>
            - Choose a different database name or remove this one.<br/>
            - Change the action drop-down to an option like "Connect and Remove All Data".<br/>
        }
    }
    ?>
</p>
    <p>
    </p>

<div class="sub-title">DETAILS</div>
<p>
    The test will attempt to create and drop the database name provided as part of the overall test.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Check the database user privileges:
        <ul class='vids'>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=FfX-B-h3vo0" target="_video">Add database user in phpMyAdmin</a>
            </li>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=peLby12mi0Q" target="_video">Add database user in cPanel older versions</a>
            </li>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=CHwxXGPnw48" target="_video">Add database user in cPanel newer versions</a>
            </li>
        </ul>
    </li>
        <li>Try using the <a href="javascript:void(0)" onclick="DUPX.togglePanels('cpanel')">'cPanel'</a> option.</li>
    <li>
        <a 
            target="_help"
            title="I'm running into issues with the Database what can I do?"
        >
            [Additional FAQ Help]
        </a>
    </li>
</ul>

} // namespace CharacterGeneratorDev
