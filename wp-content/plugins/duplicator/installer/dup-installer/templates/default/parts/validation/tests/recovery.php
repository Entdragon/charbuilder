<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int */
/* @var $importPage string|false */
/* @var $recoveryPage string|false */
/* @var $recoveryIsOutToDate bool */
/* @var $recoveryPackageLife int */

switch ($testResult) {
    case DUPX_Validation_test_recovery::LV_GOOD:
        ?>
        <b>Recovery URL:</b> <i class="green"> is valid. </i>
        break;
    case DUPX_Validation_test_recovery::LV_SOFT_WARNING:
        ?>
        <p>
            In case of an error and subsequent restore all changes created after the restore point will be lost.
        </p>
        break;
    case DUPX_Validation_test_recovery::LV_HARD_WARNING:
    default:
        ?><b class="maroon">
            <i class="fas fa-exclamation-triangle"></i> The Recovery Point is not set!
        </b> 
        <p>You can continue but in the event you run into an install issue/error you will not be able to restore the current site.   In some cases
            this might be desirable.  For example:
        </p>
        <ul>
            <li>This is a completely blank WordPress site and getting it back is simple.</li>
            <li>Losing access to this site is no big deal and you know how to restore things on your own.</li>
        </ul>
        </i>
        break;
}
?>
<div class="sub-title">DETAILS</div>
<p>
</p>
<p>  
    <b>You can
            go back
        and set a new Recovery Point.</b>
</p>
} // namespace CharacterGeneratorDev
