<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Utils\LinkManager;
use Duplicator\Utils\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="dup-settings-lite-cta">
    <p>
            'Thanks for being a loyal Duplicator Lite user. Upgrade to Duplicator Pro to unlock all the ' .
            'awesome features and experience why Duplicator is consistently rated the best WordPress migration plugin.',
            'duplicator'
        ); ?>
    </p>
    <p>
        printf(
            wp_kses( /* translators: %s - star icons. */
                __(
                    'We know that you will truly love Duplicator. It has over 4000+ five star ratings (%s) and is active on ' .
                    'over 1 million websites.',
                    'duplicator'
                ),
                array(
                    'i' => array(
                        'class'       => array(),
                        'aria-hidden' => array(),
                    ),
                )
            ),
            str_repeat('<i class="fa fa-star" aria-hidden="true"></i>', 5) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
        ?>
    </p>
    <ul class="list">
        foreach (Upsell::getCalloutCTAFeatureList() as $feature) {
            ?>
            <li class="item">
                <span>
                </span>
            </li>
        };
        ?>
    </ul>
    <p>
        </a>
    </p>
    <p>
        printf(
            __(
                '<strong>Bonus:</strong> Duplicator Lite users get <span class="green">%1$d%% off regular price</span>,' .
                'automatically applied at checkout.',
                'duplicator'
            ),
            DUP_Constants::UPSELL_DEFAULT_DISCOUNT
        );
        ?>
    </p>
</div>

} // namespace CharacterGeneratorDev
