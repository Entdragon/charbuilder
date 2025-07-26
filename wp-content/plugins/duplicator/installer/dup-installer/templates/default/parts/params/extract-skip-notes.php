<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/* Variables */
/* @var $currentSkipMode string */
?>
<div 
    ($currentSkipMode == DUP_Extraction::FILTER_NONE ? '' : ' no-display'); ?>"
>
    All Files in the archive are going to be extracted.
</div>
<div 
    ($currentSkipMode == DUP_Extraction::FILTER_SKIP_WP_CORE ? '' : ' no-display'); ?>"
>
    When this option is chosen the wordpress core files, if any, are not modified. They are not deleted and/or extracted.
</div>
<div 
        ($currentSkipMode == DUP_Extraction::FILTER_SKIP_CORE_PLUG_THEMES ? '' : ' no-display'); ?>"
>
    When this option is chosen the wordpress core files, if any, are not modified. They are not deleted and/or extracted. </br>
    Also, if a plugin (theme) exists on BOTH the host and and the archive, the contents of the host plugin (theme) are going to be kept.
</div>
<div 
        ($currentSkipMode == DUP_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES ? '' : ' no-display'); ?>"
>
    When this option is chosen only the "uploads" folder and plugins (themes) that don't exist on the host are going to be extracted.
</div>
} // namespace CharacterGeneratorDev
