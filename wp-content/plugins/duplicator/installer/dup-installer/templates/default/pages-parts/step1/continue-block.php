<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

?>
<div id="next_action" class="bottom-step-action no-display" >           
    <div class="footer-buttons">
        <div class="content-left">
            dupxTplRender('pages-parts/step1/terms-and-conditions');
            PrmMng::getInstance()->getHtmlFormParam(PrmMng::PARAM_ACCEPT_TERM_COND);
            ?>
        </div>
        <div class="content-right" >
            <button 
                id="s1-deploy-btn" 
                type="button" 
                class="default-btn">
                Next <i class="fa fa-caret-right"></i>
            </button>
        </div>
    </div>
</div>
} // namespace CharacterGeneratorDev
