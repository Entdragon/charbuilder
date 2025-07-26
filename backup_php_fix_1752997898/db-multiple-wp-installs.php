<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $uniquePrefixes array */

?>
<div class="sub-title">STATUS</div>

<p class="green">
    The selected database action does not affect other WordPress installations.
</p>
<p class="red">
</p>

<div class="sub-title">DETAILS</div>
<p>
    This test makes sure that the selected database action affects at most one WordPress installation. Please make sure that the
    chosen database action will not cause unwanted consequences for tables of other sites residing on the same database. In case
    you want to avoid removing the tables of the second WordPress installation we recommend switching the Database action to
    "Overwrite Existing Tables".
</p>
<p>WordPress tables with the following table prefixes will be affected by the chosen database action:</p>
<ul>
</ul>





} // namespace CharacterGeneratorDev
