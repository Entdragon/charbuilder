<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\InstallerLinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $memoryLimit string */
/* @var $minMemoryLimit string */
/* @var $isOk bool */
?>
<p>
<div class="sub-title">STATUS</div>
<p>
        <i class='green'>
        </i>
        <i class='red'>
        </i>
</p>

<div class="sub-title">DETAILS</div>
<p>

</p>
The 'memory_limit' configuration in php.ini sets how much memory a script can use during its runtime. 
When this value is lower than the suggested minimum of

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Try Increasing the memory_limit.&nbsp;
        <a 
                'how-to-manage-server-resources-cpu-memory-disk',
                'install',
                'validation memory limit'
            ); ?>" 
            target="_blank"
        >
            [Additional FAQ Help]
        </a>
    </li>
</ul>

} // namespace CharacterGeneratorDev
