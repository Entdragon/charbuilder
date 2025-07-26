<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $isNewSubSite bool */
/* @var $message string */
/* @var $affectedTableCount array */
/* @var $affectedTables array */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>

        <p class="green">
            Adding a new subsite into WordPress does not require removing or renaming any tables.
        </p>
        <p class="green">
            The chosen Database Action does not affect any tables in the selected database.
        </p>
    <p class="red">
        table(s).
    </p>

    <div class="sub-title">DETAILS</div>

    <div class="s1-validate-flagged-tbl-list">
        <ul>
        </ul>
    </div>

} // namespace CharacterGeneratorDev
