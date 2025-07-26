<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerLinkManager;

$paramsManager = PrmMng::getInstance();
?><p>
</p>
The installer has detected that the archive file has been extracted to the deployment path above.

<p>
    The installer has detected that the archive file has been extracted to the deployment path above. The installer is going
    to skip the extraction process by default. If you want to re-extract the archive file, switch to "Advanced" mode, and
    under "Options" > "Extraction Mode" choose the preferred extraction mode.
</p>

<small>
    If the file exists then this notice is shown.
    The <i>dup-manual-extract__[HASH]</i> file is created with every archive and removed once the install is complete.  For more details on this process see the
    <a 
        target="_blank"
    >
        manual extraction FAQ
    </a>.
</small>

} // namespace CharacterGeneratorDev
