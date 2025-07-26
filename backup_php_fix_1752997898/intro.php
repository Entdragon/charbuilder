<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") || exit;

/**
* Variables
*
* @var \Duplicator\Core\Views\TplMng  $tplMng
* @var array<string, mixed> $tplData
*/
?>
<div class="intro">
    <div class="sullie">
    </div>
    <div class="block">
    </div>
    <div class="block">
        <h6>
                'Opt in to get email notifications for security & feature updates, educational content, ' .
                'and occasional offers, and to share some basic WordPress environment info. This will ' .
                'help us make the plugin more compatible with your site and better at doing what you need it to.',
                'duplicator'
            ); ?>
        </h6>
        <div class="button-wrap">
            <div>
                <button id="enable-usage-stats-btn" class="dup-btn dup-btn-lg dup-btn-orange dup-btn-block">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <div>
                   class="dup-btn dup-btn-lg dup-btn-grey dup-btn-block"
                   rel="noopener noreferrer">
                </a>
            </div>
        </div>
    </div>
    <div class="block terms-container">
        <div class="terms-list-toggle">
            <i class="fas fa-chevron-right fa-sm"></i>
        </div>
        <ul class="terms-list" style="display: none;">
            <li>
                <i class="fas fa-user"></i>
                <div>
                    <b>
                        <i 
                            class="fas fa-question-circle"
                                esc_attr_e(
                                    'Never miss important updates, get security warnings before they ' .
                                    'become public knowledge, and receive notifications about special offers and awesome new features.',
                                    'duplicator'
                                ); ?>"
                            aria-expanded="false"
                        ></i>
                    </b>
                    <p>
                            'Your WordPress user\'s: first & last name, and email address',
                            'duplicator'
                        ); ?>
                    </p>
                </div>
            </li>
            <li>
                <i class="fas fa-globe"></i>
                <div>
                    <b>
                        <i 
                            class="fas fa-question-circle"
                                esc_attr_e(
                                    'To provide additional functionality that\'s relevant to your website, avoid WordPress ' .
                                    'or PHP version incompatibilities that can break your website, and recognize which ' .
                                    'languages & regions the plugin should be translated and tailored to.',
                                    'duplicator'
                                ); ?>"
                            aria-expanded="false"
                        ></i>
                    </b>
                    <p>
                            'Homepage URL & title, WP & PHP versions, and site language',
                            'duplicator'
                        ); ?>
                    </p>
                </div>
            </li>
            <li>
                <i class="fas fa-plug"></i>
                <div>
                    <p>
                            'Current plugin & SDK versions, and if active or uninstalled',
                            'duplicator'
                        ); ?>
                    </p>
                </div>
            </li>
            <li>
                <i class="fas fa-palette"></i>
                <div>
                    <b>
                        <i 
                            class="fas fa-question-circle"
                                esc_attr_e(
                                    'To ensure compatibility and avoid conflicts with your installed plugins and themes.',
                                    'duplicator'
                                ); ?>"
                            aria-expanded="false"
                        ></i>
                    </b>
                    <p>
                            'Names, slugs, versions, and if active or not',
                            'duplicator'
                        ); ?>
                    </p>
                </div>
            </li>
        </ul>
    </div>
</div>

} // namespace CharacterGeneratorDev
