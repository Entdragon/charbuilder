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
/* @var $dbuser string */
/* @var $dbname string */
/* @var $databases string */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
        Be sure the database name already exists. 
        If you want to create a new database choose the action 'Create New Database'.<br>
</p>
    <p>
    </p>


<div class="sub-title">DETAILS</div>
<p>
    This test checks if the database user is allowed to connect or view the database. 
    This test will not be ran if the 'Create New Database' action is selected.
</p>

    <ul class="db-list">
        if (count($databases)) {
            foreach ($databases as $database) {
                ?>
                <li>
                </li>
            }
        } else {
            ?>
            <li>
                <i>No databases are viewable</i>
            </li>
    </ul>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Check the database user privileges.</li>
    <li>
        Check to make sure the 'User' has been added as a valid database user
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
