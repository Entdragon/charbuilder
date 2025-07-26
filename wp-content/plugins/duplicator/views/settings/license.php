<?php

namespace CharacterGeneratorDev {


use Duplicator\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<hr size="1" />
<table class="form-table licenses-table">
<tr valign="top">
    <td>
            echo sprintf(
                __('%1$sManage Licenses%2$s', 'duplicator'),
                '<a target="_blank" href="' . esc_url(LinkManager::getCampaignUrl('license-tab', 'Manage Licenses')) . '">',
                '</a>'
            );
            ?>
    </td>
</tr>
<tr valign="top">
    <td class="dpro-license-type">
        <div style="padding: 10px">
            <i class="far fa-square"></i> 
            <a target="_blank" 
            >
            </a><br>
        </div>
    </td>
</tr>
<tr valign="top">
    <td>
        <div class="description" style="max-width:700px">
            <p>
                    wp_kses(
                        __('To unlock more features consider <strong><a href="%s" target="_blank" rel="noopener noreferrer">upgrading to PRO</a></strong>.', 'duplicator'),
                        array(
                            'a'      => array(
                                'href'   => array(),
                                'class'  => array(),
                                'target' => array(),
                                'rel'    => array(),
                            ),
                            'strong' => array(),
                        )
                    ),
                    esc_url(LinkManager::getCampaignUrl('license-tab', 'upgrading to PRO'))
                ); ?>
            </p>
            <p class="discount-note">
                printf(
                    __(
                        'As a valued Duplicator Lite user you receive <strong>%1$d%% off</strong>, automatically applied at checkout!',
                        'duplicator'
                    ),
                    DUP_Constants::UPSELL_DEFAULT_DISCOUNT
                );
                ?>
            </p>
            <hr>
            <p>
            <p>
            </p>
        </div>
    </td>
</tr>
</table>

<!-- An absolute position placed invisible form element which is out of browser window -->
<form action="placeholder_will_be_replaced" method="get" id="redirect-to-remote-upgrade-endpoint">
    <input type="hidden" name="oth" id="form-oth" value="">
    <input type="hidden" name="license_key" id="form-key" value="">
    <input type="hidden" name="version" id="form-version" value="">
    <input type="hidden" name="redirect" id="form-redirect" value="">
    <input type="hidden" name="endpoint" id="form-endpoint" value="">
    <input type="hidden" name="siteurl" id="form-siteurl" value="">
    <input type="hidden" name="homeurl" id="form-homeurl" value="">
    <input type="hidden" name="file" id="form-file" value="">
</form>


} // namespace CharacterGeneratorDev
