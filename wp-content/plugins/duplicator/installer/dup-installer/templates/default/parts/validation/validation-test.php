<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Core\Params\PrmMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $test DUPX_Validation_abstract_item */

if (!$test->display()) {
    return;
}

$validationLevel = PrmMng::getInstance()->getValue(PrmMng::PARAM_VALIDATION_LEVEL);
$open            = ($test->test() <= DUPX_Validation_abstract_item::LV_HARD_WARNING && $test->test() <= $validationLevel);
$icon            = $open ? 'fa-caret-down' : 'fa-caret-right';
?>
    <div class="test-title" >
    </div>
    </div>
</div>
} // namespace CharacterGeneratorDev
