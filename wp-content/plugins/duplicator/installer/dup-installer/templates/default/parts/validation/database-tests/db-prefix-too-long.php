<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $errorMessage string */
/* @var $tooLongNewTableNames array */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
        There are no table names whose length exceeds limit of 64 characters after adding prefix.
        Some table names exceed limit of 64 characters after adding prefix.
</p>
    <p>
    </p>


<div class="sub-title">DETAILS</div>
<p>
    This test checks if there are any table names that would be too long after adding prefix to them.
    MySQL accepts length of table names with maximum of 64 characters 
    (see <a href="https://dev.mysql.com/doc/refman/8.0/en/identifier-length.html" target="_blank">length limits</a>).
    With a too long prefix, tables can exceed this limit.    
</p>

    <b>List of database tables that are too long after adding prefix</b><br/>
    <div class="s1-validate-flagged-tbl-list">
        <ul>
        </ul>
    </div>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Choose a shorter prefix in Options ❯ Database Settings ❯ Table Prefix.</li>
</ul>
} // namespace CharacterGeneratorDev
