<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerLinkManager;

$paramsManager = PrmMng::getInstance();

$wpHostUrl    = InstallerLinkManager::getCampaignUrl('installer', "For cPanel Get Pro");
$freeStep2Url = InstallerLinkManager::getCampaignUrl('installer', "With Pro use cPanel without leaving");
$freeNoteUrl  = InstallerLinkManager::getCampaignUrl('installer', "With Pro improve cPanel workflow");

$pageURL        = DUPX_HTTP::get_page_url(false);
$hostScheme     = parse_url($pageURL, PHP_URL_SCHEME);
$hostName       = parse_url($pageURL, PHP_URL_HOST);
$currentHostUrl = "{$hostScheme}://{$hostName}";
$cpanelURL      = DUPX_U::esc_url('https://' . $_SERVER['SERVER_NAME'] . ':2083');

?>
<div class="hdr-sub3 database-setup-title">cPanel Connection</div>

<div class="s2-gopro">
    <h2>
        cPanel Connectivity
    </h2>
    <div class="s1-cpanel-whatis">
        <a href="https://cpanel.net" target="cpanel">What is cPanel?</a>
    </div>
    
        <div class='s1-cpanel-login'>
            Click the link below to login to this server's cPanel<br/>
            <small><i>cPanel support is available only in Duplicator Pro with participating hosts.</i></small>
        </div>
        <div class='s1-cpanel-off'>
            This server does not appear to support cPanel!<br/>
             <small><i>cPanel support is available only in Duplicator Pro with participating hosts.</i></small>
        </div>

    <i>without leaving this installer:</i>
    
    <ul>
        <li>Directly login to cPanel</li>
        <li>Instantly create new databases &amp; users</li>
        <li>Preview and select existing databases  &amp; users</li>
    </ul>
    
    <small>
        Note: Hosts that support cPanel provide remote access to server resources, allowing operations such as direct database and user creation.
        supports cPanel API access, which can help improve and speed up your workflow.
    </small>
</div>

} // namespace CharacterGeneratorDev
