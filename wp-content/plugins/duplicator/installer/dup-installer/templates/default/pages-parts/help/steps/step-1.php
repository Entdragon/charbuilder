<?php

namespace CharacterGeneratorDev {

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<!-- ============================================
STEP 1
============================================== -->
$sectionId   = 'section-step-1';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>
    <h2 class="header expand-header">
        Step <span class="step">1</span>: Deployment
    </h2>
    <div class="content" >
        <div id="dup-help-scanner" class="help-page">
            dupxTplRender('pages-parts/help/steps/step1-parts/basic-step1-setup');
            dupxTplRender('pages-parts/help/steps/step1-parts/advanced-step1-options');
            dupxTplRender('pages-parts/help/steps/step1-parts/validation-step1');
            ?>
        </div>
    </div>
</section>
} // namespace CharacterGeneratorDev
