<?php

namespace CharacterGeneratorDev {


/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Utils\Help\Article;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

/** @var Article[] $articles p*/
$articles  = $tplData['articles'];
$listClass = isset($tplData['list_class']) ? 'class="' . $tplData['list_class'] . '"' : '';
?>
                <i aria-hidden="true" class="fa fa-file-alt"></i>
            </li>
    </ul>

} // namespace CharacterGeneratorDev
