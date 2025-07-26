<?php

namespace CharacterGeneratorDev {


/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var int $lowerCaseTableNames
 * @var array<string[]> $duplicateTableNames
 * @var string[] $reduntantTableNames
 */

?>
<div class="sub-title">STATUS</div>
<p class="red">
    The following tables have the same name but different casing. Underlined tables are going to be excluded from the database extraction.
</p>
<ul>
    <li>
        foreach ($tableNames as $index => $name) {
            if (in_array($name, $reduntantTableNames)) { ?>
            } else {
                echo $name;
            }

            if ($index < (count($tableNames) - 1)) {
                echo ', ';
            }
        }
        ?>
    </li>
</ul>

<div class="sub-title">DETAILS</div>
<p>
    This will cause issues trying to create tables with the same case insensitive table name. To change the filtered tables switch to "Advanced" and 
    mode and choose the tables to extract in Step 2.
</p>

} // namespace CharacterGeneratorDev
