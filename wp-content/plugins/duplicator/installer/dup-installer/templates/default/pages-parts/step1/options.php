<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="options-area-header" class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#step1-options-wrapper">
    <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Options</a>
</div>
<div id="step1-options-wrapper" class="hdr-sub1-area tabs-area no-display">
    <div class="tabs">
        <ul>
            <li><a href="#tabs-advanced">Advanced</a></li>
            <li><a href="#tabs-database">Database</a></li>
            <li>
                <a href="#tabs-other">
                    URLs & Paths
                </a> 
            </li>
        </ul>
        <div id="tabs-advanced" class="dupx-opts" >
        </div>
        <div id="tabs-database" class="dupx-opts" >
        </div>
        <div id="tabs-other" class="dupx-opts" >
        </div>
    </div>
</div>
} // namespace CharacterGeneratorDev
