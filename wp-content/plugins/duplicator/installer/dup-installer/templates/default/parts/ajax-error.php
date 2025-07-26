<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerLinkManager;

$recoveryLink = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
?>
<div id="ajaxerr-area" class="no-display">
    <p>
        <b>ERROR:</b> <div class="message"></div>
    <i>Please try again an issue has occurred.</i>
</p>
<div id="ajaxerr-data">
    <div class="html-content" ></div>
    <pre class="pre-content"></pre>
</div>
<p>
    <b>Additional Resources:</b><br/>
    &raquo; 
        Help Resources
    </a><br/>
    &raquo; 
        Technical FAQ
    </a>
</p>
<p class="text-center">
    <input id="ajax-error-try-again" type="button" class="default-btn" value="&laquo; Try Again" />
            <i class="fas fa-undo-alt"></i> Restore Recovery Point
        </a> 
</p>
<p class="text-center">
</p>
</div>

} // namespace CharacterGeneratorDev
