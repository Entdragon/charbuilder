<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/page-header', array(
    'paramView'       => 'secure',
    'bodyId'          => 'page-secure',
    'bodyClasses'     => $bodyClasses,
    'skipTopMessages' => true
));
?>
<div id="content-inner">
    dupxTplRender('pages-parts/head/header-main', array(
        'htmlTitle' => 'Installer Security'
    ));
    ?>
    <div id="main-content-wrapper" >
    </div>
</div>
dupxTplRender('scripts/secure-init');
dupxTplRender('pages-parts/page-footer');

} // namespace CharacterGeneratorDev
