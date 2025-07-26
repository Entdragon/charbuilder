<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/page-header', array(
    'paramView'   => 'exception',
    'bodyId'      => 'page-exception',
    'bodyClasses' => $bodyClasses
));
?>
<div id="content-inner">
    dupxTplRender('pages-parts/head/header-main', array(
        'htmlTitle' => 'Exception error'
    ));
    ?>
    <div id="main-content-wrapper" >
        dupxTplRender('pages-parts/exception/main', array(
            'exception' => $exception
        ));
        ?>
    </div>
</div>

dupxTplRender('pages-parts/page-footer');

} // namespace CharacterGeneratorDev
