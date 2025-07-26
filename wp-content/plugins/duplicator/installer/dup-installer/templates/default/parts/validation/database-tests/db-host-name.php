<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $host string */
/* @var $fixedHost string */
?>
<p>
    <b>Database host:</b> 
    if ($isOk) {
        ?><i class='green'>
    } else {
        ?><i class='red'>
        </i>
    }
    ?>
</p>
} // namespace CharacterGeneratorDev
