<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<form id="s1-input-form" method="post" class="content-form" autocomplete="off" >
    <div class="main-form-content" >
        dupxTplRender('pages-parts/step1/info');
        dupxTplRender('pages-parts/step1/base-setup');
        dupxTplRender('pages-parts/step1/options');
        dupxTplRender('parts/validation/validate-area');
        ?>
    </div>
</form>
} // namespace CharacterGeneratorDev
