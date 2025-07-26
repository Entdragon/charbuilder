<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $pathsList string[] */

$statusClass = ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING ? 'green' : 'red' );
?>
<div class="sub-title">STATUS</div>
        No addon site detected.
        Detected addon sites, see the details section for the list of sites.<br>
            Normal installation generally does not interfere with these sites.
            These sites are not deleted even if you have selected an action that removes the files before extracting them.  
            If there are other folders outside the home path that are necessary for the addon site to work, it will be removed 
            so pay attention in the event there are addon custom installations.
        }
    }
    ?>
</p>

<div class="sub-title">DETAILS</div>
<p>
    An addon site is a WordPress installation in a subfolder of the current home path.
</p>
    <p>
        <i>Addons Site Paths</i>
    </p>
    <ul>
            <li>
            </li>
        }
        ?>
    </ul>
<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        The installer doesn't modify addon sites so their presence doesn't cause problems 
        but if you want to be sure you don't lose data it might be useful to make a backup of the addon site.
    </li>
</ul>

} // namespace CharacterGeneratorDev
