<?php

namespace CharacterGeneratorDev {


/**
 * Template for lite vs pro page
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || exit;

use Duplicator\Controllers\AboutUsController;
use Duplicator\Utils\LinkManager;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="wrap" id="dup-admin-about">
    <div class="dup-admin-about-section dup-admin-about-section-squashed">
        <h1 class="centered">
            echo wp_kses(
                __(
                    '<strong>Lite</strong> vs <strong>Pro</strong>',
                    'duplicator'
                ),
                [
                    'strong' => [],
                ]
            );
            ?>
        </h1>

        <p class="centered">
        </p>
    </div>

    <div class="dup-admin-about-section dup-admin-about-section-squashed dup-admin-about-section-hero dup-admin-about-section-table">

        <div class="dup-admin-about-section-hero-main dup-admin-columns">
            <div class="dup-admin-column-33">
                <h3 class="no-margin">
                </h3>
            </div>
            <div class="dup-admin-column-33">
                <h3 class="no-margin">
                </h3>
            </div>
            <div class="dup-admin-column-33">
                <h3 class="no-margin">
                </h3>
            </div>
        </div>
        <div class="dup-admin-about-section-hero-extra no-padding dup-admin-columns">

            <table>
                    <tr class="dup-admin-columns">
                        <td class="dup-admin-column-33">
                        </td>
                        <td class="dup-admin-column-33">
                                <strong>
                                if (isset($feature['lite_text'])) {
                                    echo $feature['lite_text'];
                                } else {
                                    $feature['lite_enabled'] === AboutUsController::LITE_ENABLED_FULL ? _e('Included', 'duplicator')
                                        : _e('Not Available', 'duplicator');
                                }
                                ?>
                                </strong>
                            </p>
                        </td>
                        <td class="dup-admin-column-33">
                            <p class="features-full">
                                <strong>
                                </strong>
                            </p>
                        </td>
                    </tr>
            </table>
        </div>
    </div>
    <div class="dup-admin-about-section dup-admin-about-section-hero">
        <div class="dup-admin-about-section-hero-main no-border">
            <h3 class="call-to-action centered">
                printf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">',
                    esc_url(LinkManager::getCampaignUrl('about_duplicator_lite_vs_pro', 'Get Duplicator Pro Today'))
                );
                _e('Get Duplicator Pro Today and Unlock all the Powerful Features', 'duplicator')
                ?>
                </a>
            </h3>

            <p class="centered">
                printf(
                    __(
                        'Bonus: Duplicator Lite users get <span class="price-20-off">%1$d%% off regular price</span>, ' .
                        'automatically applied at checkout.',
                        'duplicator'
                    ),
                    DUP_Constants::UPSELL_DEFAULT_DISCOUNT
                );
                ?>
            </p>
        </div>
    </div>
</div>

} // namespace CharacterGeneratorDev
