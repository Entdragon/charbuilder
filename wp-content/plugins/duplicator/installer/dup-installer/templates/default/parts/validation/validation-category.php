<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $category string */
/* @var $title string */

$vManager = DUPX_Validation_manager::getInstance();
$tests    = $vManager->getTestsCategory($category);
?>
<div class="category-wrapper" >
    <div class="header" >
        <div class="category-title" >
        </div>
    </div>
    <div class="category-content" >
        foreach ($tests as $test) {
            dupxTplRender('parts/validation/validation-test', array('test' => $test));
        }
        ?>
    </div>
</div>
} // namespace CharacterGeneratorDev
