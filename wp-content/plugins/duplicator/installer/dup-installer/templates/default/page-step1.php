<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/page-header', array(
    'paramView'   => 'step1',
    'bodyId'      => 'page-step1',
    'bodyClasses' => $bodyClasses
));
?>
<div id="content-inner">
    </div>
    dupxTplRender('parts/ajax-error');
    dupxTplRender('parts/progress-bar', array(
        'display' => DUPX_Validation_manager::validateOnLoad()
    ));
    ?>
</div>
dupxTplRender('scripts/step1-init');
dupxTplRender('pages-parts/page-footer');

} // namespace CharacterGeneratorDev
