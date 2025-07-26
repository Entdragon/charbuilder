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
/* @var $hostDBVersion string */
?>
<div class="sub-title">STATUS</div>
<p>
        <i class='green'> 
        </i>
        <i class='red'>
            Please work with your server admin or hosting provider to update the database server.
        </i>
</p>

<div class="sub-title">DETAILS</div>
<p>
    The minimum supported database server is MySQL Server 5.0 or the 
    <a href="https://mariadb.com/kb/en/mariadb/mariadb-vs-mysql-compatibility/" target="_blank">MariaDB equivalent</a>.
    Versions prior to MySQL 5.0 are over 10 years old and will not be compatible with Duplicator.  
    If your host is using a legacy version, please ask them
    to upgrade the MySQL database engine to a more recent version.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Contact your host and have them upgrade your MySQL server.</li>
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
