<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $freeSpace int */
/* @var $requiredSpace int */
?>
<div class="sub-title">STATUS</div>
<p>
        <span class="green">You have sufficient disk space on your machine to extract the archive.</span>
        <span class="maroon">You don’t have sufficient disk space on your machine to extract the archive.</span>
</p>

<div class="sub-title">DETAILS</div>
<p>
    Duplicator needs at least enough disk space to be able to host the package file and the extracted files.<br>
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Ask your host to increase your disk space.</li>
    <li>Back-up and remove all unnecessary files you have in the install directory to free up space.</li>
</ul>
} // namespace CharacterGeneratorDev
