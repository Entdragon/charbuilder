<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/* Variables */
/* @var $isOk bool */
/* @var $triggers array */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
        ?>
        The source database did not contain any triggers.
    } else {
        ?>
    } ?>
</p>

<div class="sub-title">DETAILS</div>
<p>
    TRIGGERS are not being imported along side the rest of the database, because their presence might cause unintended
    behavior. You can copy the CREATE queries by clicking the button below and manually add triggers via PHPMyAdmin, if necessary.
</p>

<div class="copy-to-clipboard-block">
    if ($isOk) {
        echo 'No triggers found.';
    } else {
        foreach ($triggers as $name => $info) {
            echo $info->create . "\n\n";
        }
    }
    ?>
    </textarea>
</div>

} // namespace CharacterGeneratorDev
