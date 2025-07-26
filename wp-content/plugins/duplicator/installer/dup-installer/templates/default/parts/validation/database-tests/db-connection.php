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
/* @var $dbhost string */
/* @var $dbpass string */
/* @var $mysqlConnErr string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
            Please contact your hosting provider or server administrator.
</p>

<div class="sub-title">DETAILS</div>
<p>
    This test checks that the database user is allowed to connect to the database server.  
    It validates on the user name, password and host values.
    The check does not take into account the database name or the user permissions. A database user must first exist and have access to the host
    database server before any additional checks can be made.
</p>

<table>
    <tr>
        <td>Host:</td>
    </tr>
    <tr>
        <td>User:</td>
    </tr>
    <tr>
        <td>Password:</td>
    </tr>
</table><br/>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Check that the 'Host' name settings are correct via your hosts documentation.</li>
    <li>On some servers, the default name 'localhost' will not work. Be sure to contact your hosting provider.</li>
    <li>Triple check the 'User' and 'Password' values are correct.</li>
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
        If using the 'Basic' option then try using the <a href="javascript:void(0)" onclick="DUPX.togglePanels('cpanel')">'cPanel'</a> option.
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
