<?php

namespace CharacterGeneratorDev {


/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div>
    <b>
    </b>
</div>
<br>
<div>
        _e(
            'Usage tracking for Duplicator helps us better understand our users and their website needs by looking 
            at a range of server and website environments.',
            'duplicator'
        );
        ?>
    <b>
    </b>
</div>
<ul>
    <li>
        _e(
            '<b>PHP Version:</b> so we know which PHP versions we have to test against (no one likes whitescreens or log files full of errors).',
            'duplicator'
        );
        ?>
    </li>
    <li>
        _e(
            '<b>WordPress Version:</b> so we know which WordPress versions to support and test against.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        _e(
            '<b>MySQL Version:</b> so we know which versions of MySQL to support and test against for our custom tables.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        _e(
            '<b>Duplicator Version:</b> so we know which versions of Duplicator are potentially responsible for issues when we get bug reports, 
            allowing us to identify issues and release solutions much faster.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        _e(
            '<b>Plugins and Themes infos:</b> so we can figure out which ones can generate compatibility errors with Duplicator.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        _e(
            '<b>Site info:</b> General information about the site such as database, file size, number of users, and sites in case it is a multisite. 
            This is useful for us to understand the critical issues of Backup creation.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        _e(
            '<b>Backups infos:</b> Information about the Backups created and the type of components included.',
            'duplicator'
        );
        ?>
    </li>
</ul>
} // namespace CharacterGeneratorDev
