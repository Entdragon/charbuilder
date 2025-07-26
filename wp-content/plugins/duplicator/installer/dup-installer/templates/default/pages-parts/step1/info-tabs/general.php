<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div id="tabs-1">
    <div class="margin-top-1" >
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_INST_TYPE);
        ?>
    </div>
</div>
} // namespace CharacterGeneratorDev
