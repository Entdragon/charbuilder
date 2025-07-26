<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/page-header', array(
    'paramView'   => 'step3',
    'bodyId'      => 'page-step3',
    'bodyClasses' => $bodyClasses
));
?>
<div id="content-inner">
    <div id="main-content-wrapper" >
    </div>
    dupxTplRender('parts/ajax-error');
    dupxTplRender('parts/progress-bar');
    ?>
</div>
dupxTplRender('scripts/step3-init');
dupxTplRender('pages-parts/page-footer');

} // namespace CharacterGeneratorDev
