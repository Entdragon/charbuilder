<?php

namespace CharacterGeneratorDev {

/**
 *
 * @package templates/default
 * @var     $wordFencePath
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div class="sub-title">STATUS</div>

<div class="sub-title">DETAILS</div>
<p>
    The Wordfence Web Application Firewall is a PHP based, application level firewall that filters out malicious
    requests to your site. Sometimes Wordfence returns false positives on requests done during the installation process,
    because of which it might fail.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<p>
    during the installation process and reactivate it after the migration is completed. To deactivate the firewall follow these steps:
</p>
<ol>
    <li>Go to WordPress Admin Dashboard ❯ Wordfence ❯ Firewall and click on the "Manage WAF".</li>
    <li>Choose Web Application Firewall Status to the "Disabled" option.</li>
    <li>Click on the "SAVE CHANGES" button and save the changed settings.</li>
    <li>Wait for the changes to take place</li>
</ol>

} // namespace CharacterGeneratorDev
