<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $userResources array */

?>
<div class="sub-title">STATUS</div>
<p class="green">
    No restrictions on the current DB user's resources were detected.
</p>
<p class="maroon">
    Restrictions on the current DB user's resources were detected.
</p>

<div class="sub-title">DETAILS</div>
<p>
    Some server's put in place restrictions on the resources available to a user, which <i>may</i> cause errors during
    the installation process since a significant number of connections are made and queries are run.
</p>

<div class="sub-title">USER RESOURCES</div>
<ul>
    </li>
</ul>

<div class="sub-title">TROUBLESHOOT</div>
<p>
    We suggest asking your hosting provider to undo the restrictions in case they are present or if you have root access to the mysql server
    you can use the methods described in the <a href="https://dev.mysql.com/doc/refman/5.7/en/user-resources.html" target="_blank">official documentation</a>
    to remove the restrictions.
</p>
} // namespace CharacterGeneratorDev
