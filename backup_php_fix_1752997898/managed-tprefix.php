<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
?>
<p>
    if ($isOk) {
        ?><span class="green">
            The prefix of the existing WordPress configuration table is equal of the prefix of the table of the source site where the package was created.
    } else {
        ?><span class="maroon">
            The prefix of the existing WordPress configuration table does not match the prefix of the table of the source site where the package was created, 
            so the prefix will be changed to the managed hosting prefix.
    }
    ?>
</p>
} // namespace CharacterGeneratorDev
