<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapString;

$recoveryLink = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
if (SnapString::isHTML($exception->getMessage())) {
    $message = $exception->getMessage();
} else {
    $message = '<b>' . DUPX_U::esc_html($exception->getMessage()) . '</b>';
}
?>
<div id="ajaxerr-data">
    <b style="color:#B80000;">INSTALL ERROR!</b>
    <p>
    </p>
    <hr>
    Trace:
        echo $exception->getTraceAsString();
    ?></pre>
</div>

    <p class="text-center">
            <i class="fas fa-undo-alt"></i> Restore Recovery Point
        </a> 
    </p>

<div style="text-align:center; margin:10px auto 0px auto">
</div>
} // namespace CharacterGeneratorDev
