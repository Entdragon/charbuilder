<?php

namespace CharacterGeneratorDev {


/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Utils\Help\Category;
use Duplicator\Utils\Help\Help;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

/** @var Category[] $categories */
$categories = $tplData['categories'];
if (empty($categories)) : ?>
    <p>
            printf(
                esc_html_x(
                    'Could not fetch help information. Please try again later or visit the %1$sonline docs%2$s.',
                    '%1$s and %2$s are the opening and closing tags for the link to the online docs.',
                    'duplicator-pro'
                ),
                '<a href="' . esc_url(DUPLICATOR_BLOG_URL . 'knowledge-base') . '" target="_blank">',
                '</a>'
            );
        ?>
    </p>
    <ul class="duplicator-help-category-list">
        <li class="duplicator-help-category">
            <header>
                <i class="fa fa-folder-open"></i>
                <i class="fa fa-angle-right"></i>
            </header>
            } ?>
                    'parts/help/article-list',
                    [
                        'articles'   => Help::getInstance()->getArticlesByCategory($category->getId()),
                        'list_class' => 'duplicator-help-article-list',
                    ]
                ); ?>
        </li>
</ul>

} // namespace CharacterGeneratorDev
