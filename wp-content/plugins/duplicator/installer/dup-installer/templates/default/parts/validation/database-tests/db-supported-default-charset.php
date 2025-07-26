<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $charsetOk bool */
/* @var $collateOk bool */
/* @var $sourceCharset string */
/* @var $sourceCollate string */
/* @var $usedCharset string */
/* @var $usedCollate string */
/* @var $errorMessage string */

$statusClass = ($testResult === DUPX_Validation_abstract_item::LV_FAIL || !$charsetOk || !$collateOk) ? 'red' : 'green';
?>
<div class="sub-title">STATUS</div>
        It is not possible to read the list of available charsets in the database.<br>
        (set in the wp-config file).<br>
</p>

<div class="sub-title">DETAILS</div>
<p>
    <i>Settings used in the current installation</i><br>
<p>
<p>
    DB_CHARSET and DB_COLLATE are set in wp-config.php 
    (see: <a href="https://wordpress.org/support/article/editing-wp-config-php/#database-character-set" target="_blank">Editing wp-config.php</a> ).<br>
    When the charset or collate of the source site is not supported in the database of the target site, the default is automatically set.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>In case the default charset/collates are not the desired ones you can <b>change the setting</b> in the <b>advanced installation mode</b>.</li>
</ul>

} // namespace CharacterGeneratorDev
