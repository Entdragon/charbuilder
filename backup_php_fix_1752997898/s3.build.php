<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Utils\LinkManager;
use Duplicator\Views\EducationElements;
use Duplicator\Views\AdminNotices;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
//Nonce Check
if (!isset($_POST['dup_form_opts_nonce_field']) || !wp_verify_nonce(sanitize_text_field($_POST['dup_form_opts_nonce_field']), 'dup_form_opts')) {
    AdminNotices::redirect('admin.php?page=duplicator&tab=new1&_wpnonce=' . wp_create_nonce('new1-package'));
}
require_once(DUPLICATOR_PLUGIN_PATH . 'classes/package/duparchive/class.pack.archive.duparchive.php');

$retry_nonuce           = wp_create_nonce('new1-package');
$zip_build_nonce        = wp_create_nonce('duplicator_package_build');
$duparchive_build_nonce = wp_create_nonce('duplicator_duparchive_package_build');
$active_package_present = true;

if (DUP_Settings::Get('installer_name_mode') == DUP_Settings::INSTALLER_NAME_MODE_SIMPLE) {
    $txtInstallHelpMsg = __("When clicking the Installer download button, the 'Save as' dialog will default the name to 'installer.php'. "
        . "To improve the security and get more information, goto: Settings ❯ Backups Tab ❯ Installer Name option.", 'duplicator');
} else {
    $txtInstallHelpMsg = __("When clicking the Installer download button, the 'Save as' dialog will save the name as '[name]_[hash]_[time]_installer.php'. "
        . "This is the secure and recommended option.  For more information goto: Settings ❯ Backups Tab ❯ Installer Name Option.  To quickly copy the hashed "
        . "installer name, to your clipboard use the copy icon link.", 'duplicator');
}
?>

<style>
    a#dup-create-new {margin-left:-5px}
    div#dup-progress-area {text-align:center; max-width:800px; min-height:200px;  border:1px solid silver; border-radius:3px; margin:25px auto 50px auto;
                           padding:0px; box-shadow:0 8px 6px -6px #999;}
    div.dup-progress-title {font-size:22px;padding:5px 0 20px 0; font-weight:bold}
    div#dup-progress-area div.inner {padding:10px; line-height:22px}
    div#dup-progress-area h2.title {background-color:#efefef; margin:0px}
    div#dup-progress-area span.label {font-weight:bold}
    div#dup-msg-success {color:#18592A; padding:5px;}
    div.dup-no-mu {font-size:13px; margin-top:15px; color:maroon; line-height:18px}
    sup.dup-new {font-weight:normal; color:#b10202; font-size:12px}

    div.dup-msg-success-stats{color:#999;margin:5px 0; font-size:11px; line-height:13px}
    div.dup-msg-success-links {margin:20px 5px 5px 5px; font-size:13px;}
    div#dup-progress-area div.done-title {font-size:18px; font-weight:bold; margin:0px 0px 10px 0px}
    div#dup-progress-area div.dup-panel-title {background-color:#dfdfdf;}
    div.hdr-pack-complete {font-size:14px; color:green; font-weight:bold}

    div#dup-create-area-nolink, div#dup-create-area-link {float:right; font-weight:bold; margin:0; padding:0}
    div#dup-create-area-link {display:none; margin-left:-5px}
    div#dup-progress-area div.dup-panel-panel { border-top:1px solid silver}
    fieldset.download-area {border:2px dashed #b5b5b5; padding:20px 20px 20px 20px; border-radius:4px; margin:auto; width:500px }
    fieldset.download-area legend {font-weight:bold; font-size:18px; margin:auto; color:#000}
    button#dup-btn-installer, button#dup-btn-archive { line-height:28px; min-width:175px; height:38px !important; padding-top:3px !important; }
    a#dup-link-download-both {min-width:200px; padding:3px;}
    div.one-click-download {margin:20px 0 10px 0; font-size:16px; font-weight:bold}
    div.one-click-download i.fa-bolt{padding-right:5px}
    div.one-click-download i.fa-file-archive-o{padding-right:5px}

    div.dup-button-footer {text-align:right; margin:20px 10px 0px 0px}
    button.button {font-size:16px !important; height:30px !important; font-weight:bold; padding:0px 10px 5px 10px !important; min-width:150px }
    span.dup-btn-size {font-size:11px;font-weight:normal}
    p.get-pro {font-size:13px; color:#222; border-bottom:1px solid #eeeeee; padding-bottom: 10px; margin-bottom: 25px; font-style:italic}
    p.get-pro.subscribed {border-top: 1px solid #eeeeee; border-bottom: 0; padding:5px 0 0 0; margin:0;}
    div.dup-howto-exe {font-size:14px; font-weight:bold; margin:25px 0 40px 0;line-height:20px; color:#000; padding-top:10px;}
    div.dup-howto-exe-title {font-size:18px; margin:0 0 8px 0; color:#000}
    div.dup-howto-exe-title a {text-decoration:none; outline:none; box-shadow:none}
    div.dup-howto-exe small {font-weight:normal; display:block; margin-top:-2px; font-style:italic; font-size:12px; color:#444 }
    div.dup-howto-exe a {margin-top:8px; display:inline-block}
    div.dup-howto-exe-info {display:block; border:1px dotted #b5b5b5; padding:20px; margin:auto; width:500px; background-color:#F0F0F1; border-radius:4px;}
    div.dup-howto-exe-info a i {display:inline-block; margin:0 2px 0 2px}
    div.dup-howto-exe-area {display: flex; justify-content: center;}
    div.dup-howto-exe-txt {text-align: left; font-size:16px}
    div.dup-howto-exe-txt sup.modes {font-weight: normal; color:#999; font-style: italic;}
    div.dup-howto-exe-txt small {padding:4px 0 4px 0}
    span#dup-installer-name {display:inline-block; color:gray; font-style: italic;}
    span#dup-installer-name a {text-decoration: none}
    span#dup-installer-name-help-icon {display:none}

    /*HOST TIMEOUT */
    div#dup-msg-error {color:maroon; padding:5px;}
    div.dup-box-title {text-align:left; background-color:#F6F6F6}
    div.dup-box-title:hover { background-color:#efefef}
    div.dup-box-panel {text-align:left}
    div.no-top {border-top:none}
    div.dup-box-panel b.opt-title {font-size:18px}
    div.dup-msg-error-area {
        overflow-y:scroll; padding:5px 15px 15px 15px; 
        height:100px; 
        width:95%; 
        border:1px solid #EEEEEE;
        border-radius:2px; 
        line-height:22px; 
        text-align: left; 
        background-color: #FFFFF3;
    }
    .dup-msg-error-area .data {
        white-space: pre;
    }
    div#dup-logs {text-align:center; margin:auto; padding:5px; width:350px;}
    div#dup-logs a {display:inline-block;}
    span.sub-data {display:inline-block; padding-left:20px}
</style>

<!-- =========================================
TOOL BAR:STEPS -->
<table id="dup-toolbar">
    <tr valign="top">
        <td style="white-space:nowrap">
            <div id="dup-wiz">
                <div id="dup-wiz-steps">
                </div>
                <div id="dup-wiz-title" class="dup-guide-txt-color">
                    <i class="fab fa-wordpress"></i>
                </div>
            </div>
        </td>
        <td style="padding-bottom:4px">
            <span>
                </a>
            </span>
            $package_url       = ControllersManager::getMenuLink(
                ControllersManager::PACKAGES_SUBMENU_SLUG,
                'new1'
            );
            $package_nonce_url = wp_nonce_url($package_url, 'new1-package');
            ?>
            <a id="dup-create-new"
               onclick="return !jQuery(this).hasClass('disabled');"
            </a>
        </td>
    </tr>
</table>
<hr class="dup-toolbar-line">


<form id="form-duplicator" method="post" action="?page=duplicator">

<!--  PROGRESS BAR -->
<div id="dup-build-progress-bar-wrapper">
    <div id="dup-progress-bar-area">
        <div id="dup-progress-bar"></div>
    </div>
</div>

<div id="dup-progress-area" class="dup-panel" style="display:none">
    <div class="dup-panel-panel">

        <!--  =========================
        SUCCESS MESSAGE -->
        <div id="dup-msg-success" style="display:none">
            <div class="hdr-pack-complete">
            </div>

            <div class="dup-msg-success-stats">
            </div><br/>

            <!-- DOWNLOAD FILES -->
            <fieldset class="download-area">
                <legend>
                </legend>
                </button> &nbsp;
                    <span id="dup-btn-archive-size" class="dup-btn-size"></span> &nbsp;
                </button>
                <div class="one-click-download">
                        <i class="fa fa-bolt fa-sm"></i><i class="far fa-file-archive"></i>
                    </a>
                    <sup>
                        <i class="fas fa-question-circle fa-sm" style='font-size:11px'
                                . 'downloading each file separately with two clicks.  On some browsers you may have to disable pop-up warnings on this domain for this to '
                                . 'work correctly.', 'duplicator'); ?>">
                        </i>
                    </sup>
                </div>
                <div style="margin-top:20px; font-size:11px">
                    <span id="dup-click-to-copy-installer-name"
                        class="link-style no-decoration"
                            <i class="far fa-copy"></i>
                    </span><br/>
                    <span id="dup-installer-name" data-installer-name="">
                        <span class="link-style" onclick="Duplicator.Pack.ShowInstallerName()">
                        </span>
                    </span>
                    <span id="dup-installer-name-help-icon">
                        <i class="fas fa-question-circle fa-sm"
                        </i>
                    </span>
                </div>
            </fieldset>

            if (is_multisite()) {
                echo '<div class="dup-no-mu">';
                echo '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;';
                esc_html_e('Notice:Duplicator Lite does not officially support WordPress multisite.', 'duplicator');
                echo "<br/>";
                esc_html_e('We strongly recommend upgrading to ', 'duplicator');
                echo "&nbsp;<i><a href='" . esc_url(LinkManager::getCampaignUrl('package-build-complete', 'Multisite Get Pro')) . "' target='_blank'>[" . esc_html__('Duplicator Pro', 'duplicator') . "]</a></i>.";
                echo '</div>';
            }
            ?>

            <div class="dup-howto-exe">
                <div class="dup-howto-exe-title">
                </div>
                <div class="dup-howto-exe-info">
                    <div class="dup-howto-exe-area">
                        <div class="dup-howto-exe-txt">

                            <!-- CLASSIC -->
                            <i class="far fa-save fa-sm fa-fw"></i>
                            </a>
                            <sup class="modes">
                                <i class="fas fa-external-link-alt fa-xs"></i>
                            </sup>
                            <br/>

                            <small>
                                    _e('Install to an empty directory like a new WordPress install does.', 'duplicator');
                                ?>
                            </small><br/>

                            <!-- OVERWRITE -->
                            <i class="far fa-window-close fa-sm fa-fw"></i>
                            </a>
                            <sup class="modes">
                                <i class="fas fa-external-link-alt fa-xs"></i>
                            </sup>
                            <br/>

                            <br/>


                            <!-- IMPORT -->
                            <i class="fas fa-arrow-alt-circle-down fa-sm fa-fw"></i>
                            </a>
                            <sup class="modes">
                                <i class="fas fa-external-link-alt fa-xs"></i>
                            </sup>
                            <br/>

                        </div>
                    </div>
                </div>
            </div>
                </a>
            </p>
        </div>

        <!--  =========================
        ERROR MESSAGE -->
        <div id="dup-msg-error" style="display:none; color:#000">
            <br/><br/>

            <!-- OPTION 1:Try DupArchive Engine -->
            <div class="dup-box">
                <div class="dup-box-title">
                    <i class="far fa-check-circle fa-sm fa-fw"></i>
                    <div class="dup-box-arrow"><i class="fa fa-caret-down"></i></div>
                </div>
                <div class="dup-box-panel" id="dup-pack-build-try1" style="display:none">

                    <br/><br/>

                    <div style="font-style:italic">
                            printf(
                                esc_html_x(
                                    'Note: DupArchive on Duplicator only supports sites up to 500MB.  If your site is over 500MB then use a file filter on 
                                    step 1 to get the size below 500MB or try the other options mentioned below.  Alternatively, you may want to consider 
                                    %1$sDuplicator Pro%2$s, which is capable of migrating sites much larger than 500MB.',
                                    '1: opening link tag, 2: closing link tag (<a></a>)',
                                    'duplicator'
                                ),
                                '<a href="' . esc_url(LinkManager::getCampaignUrl('package-build-complete', 'Build Failed Get Pro')) . '" target="_blank">',
                                '</a>'
                            );
                            ?>
                    </div><br/>

                    <ol>
                        <li>
                        </li>
                    </ol>

                    <small style="font-style:italic">
                            printf(
                                esc_html_x(
                                    'Note: The DupArchive engine will generate an archive.daf file. This file is very similar to a .zip except that it can 
                                    only be extracted by the installer.php file or the %1$scommandline extraction tool%2$s.',
                                    '1: opening link tag, 2: closing link tag (<a></a>)',
                                    'duplicator'
                                ),
                                '<a href="'
                                . esc_url(LinkManager::getDocUrl('how-to-work-with-daf-files-and-the-duparchive-extraction-tool', 'backup_step_3_fail', 'DupArchive Extraction Tool'))
                                . '" target="_blank">',
                                '</a>'
                            );
                            ?>
                    </small>
                </div>
            </div>

            <!-- OPTION 2:TRY AGAIN -->
            <div class="dup-box  no-top">
                <div class="dup-box-title">
                    <i class="fas fa-filter fa-sm fa-fw"></i>
                    <div class="dup-box-arrow"><i class="fa fa-caret-down"></i></div>
                </div>
                <div class="dup-box-panel" style="display:none">
                        esc_html_e('The first pass for reading files on some budget hosts maybe slow and have conflicts with strict timeout settings setup by the hosting provider.  '
                        . 'In these cases, it is recommended to retry the build by adding file filters to larger files/directories.', 'duplicator');

                        echo '	<br/><br/>';

                        esc_html_e('For example, you could  filter out the  "/wp-content/uploads/" folder to create the Backup then move the files from that directory over manually.  '
                            . 'If this work-flow is not desired or does not work please check-out the other options below.', 'duplicator');
                        ?>
                    <br/><br/>
                    <div style="text-align:center; margin:10px 0 2px 0">
                    </div>

                    <div style="color:#777; padding:15px 5px 5px 5px">
                        printf(
                            '<b><i class="fa fa-folder-o"></i> %s %s</b> <br/> %s',
                            esc_html__('Build Folder:', 'duplicator'),
                            DUP_Settings::getSsdirTmpPath(),
                            __("On some servers the build will continue to run in the background. To validate if a build is still running; open the 'tmp' folder above and see "
                                . "if the Backup file is growing in size or check the main Backups screen to see if the Backup completed. If it is not then your server "
                                . "has strict timeout constraints.", 'duplicator')
                        );
                        ?>
                    </div>
                </div>
            </div>

            <!-- OPTION 3:Two-Part Install -->
            <div class="dup-box no-top">
                <div class="dup-box-title">
                    <i class="fas fa-random fa-sm fa-fw"></i>
                    <div class="dup-box-arrow"><i class="fa fa-caret-down"></i></div>
                </div>
                <div class="dup-box-panel" style="display:none">

                        . '\'database-only\' Backup, manually move the website files, and then run the installer to complete the process.', 'duplicator');
?><br/><br/>

                    <ol>
                        <li>
                                printf(
                                    esc_html_x(
                                        'Complete the Backup build and follow the %1$sQuick Start Two-Part Install Instructions%2$s',
                                        '1: opening link, 2: closing link',
                                        'duplicator'
                                    ),
                                    '<a href="' . esc_url(LinkManager::getDocUrl('two-part-install', 'backup_step_3_fail', 'Two-Part Install')) . '" target="_blank">',
                                    '</a>'
                                );
                                ?>
                        </li>
                    </ol>

                    <div style="text-align:center; margin:10px">
                        <input type="checkbox" id="dup-two-part-check" onclick="Duplicator.Pack.ToggleTwoPart()">
                        </button>
                    </div><br/>
                </div>
            </div>

            <!-- OPTION 4:DIAGNOSE SERVER -->
            <div class="dup-box no-top">
                <div class="dup-box-title">
                    <i class="fas fa-cog fa-sm fa-fw"></i>
                    <div class="dup-box-arrow"><i class="fa fa-caret-down"></i></div>
                </div>
                <div class="dup-box-panel" id="dup-pack-build-try3" style="display:none">
                        . 'FAQ page that will show various recommendations you can take to improve/unlock constraints set up on this server.', 'duplicator');
                                                ?><br/><br/>

                    <div style="text-align:center; margin:10px; font-size:16px; font-weight:bold">
                        <a 
                            target="_blank"
                        >
                        </a>
                    </div>

                    <div class="dup-msg-error-area">
                        <div id="dup-msg-error-response-time">
                            <span class="data"></span>
                        </div>
                        <div id="dup-msg-error-response-php">
                            <span class="data sub-data">
                                $try_value  = @ini_get('max_execution_time');
                                $try_update = set_time_limit(0);
                                echo "$try_value <a href='http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time' target='_blank'> (default)</a>";
                                ?>
                                <i class="fa fa-question-circle data-size-help"
                            </span><br/>

                            <span class="data sub-data">
                                    $try_update = $try_update ? __('is dynamic') : __('value is fixed');
                                    echo "{$try_update}";
                                    ?>
                                <i class="fa fa-question-circle data-size-help"
                                    esc_html_e('If the value is [dynamic] then its possible for PHP to run longer than the default.  '
                                       . 'If the value is [fixed] then PHP will not be allowed to run longer than the default. <br/><br/> If this value is larger than the [Allowed Runtime] above then '
                                       . 'the web server has been enabled with a timeout cap and is overriding the PHP max time setting.', 'duplicator');
                                    ?>"></i>
                            </span>
                        </div>
                        <div id="dup-msg-error-response-status">
                        </div>
                    </div>
                </div>
            </div>
            <br/><br/>


            <!-- ERROR DETAILS-->
            <div class="dup-box no-top">
                <div class="dup-box-title" id="dup-pack-build-err-info" >
                    <i class="fas fa-file-contract fa-fw fa-sm"></i>
                    <div class="dup-box-arrow"><i class="fa fa-caret-down"></i></div>
                </div>
                <div class="dup-box-panel" style="display:none">
                    <div class="dup-msg-error-area">
                        <div id="dup-msg-error-response-text">
                        </div>
                    </div>

                    <div id="dup-logs" style="color:maroon; font-size:16px">
                        <br/>
                        <i class="fas fa-file-contract fa-fw "></i>
                        <a href='javascript:void(0)' style="color:maroon" onclick='Duplicator.OpenLogWindow(true)'>
                        </a>
                    </div>
                </div>
            </div>
            <br/><br/>

        </div>
    </div>
</div>
</form>
<div id="build-success-footer-cta" style="display: none;">
</div>
<script>
jQuery(document).ready(function ($) {

    Duplicator.Pack.DupArchiveFailureCount = 0;
    Duplicator.Pack.DupArchiveMaxRetries = 10;
    Duplicator.Pack.DupArchiveRetryDelayInMs = 8000;
    Duplicator.Pack.DupArchiveStartTime = new Date().getTime();
    Duplicator.Pack.StatusFrequency = 8000;

    /*  ----------------------------------------
     *  METHOD:Performs Ajax post to create a new package
     *  Timeout (10000000 = 166 minutes)  */
    Duplicator.Pack.CreateZip = function () {
        var startTime;
        var statusInterval = setInterval(Duplicator.Pack.GetActivePackageStatus, Duplicator.Pack.StatusFrequency);

        $.ajax({
            type:"POST",
            cache:false,
            dataType:"text",
            url:ajaxurl,
            timeout:0, // no timeout
            data:data,
            beforeSend:function () {
                startTime = new Date().getTime();
            },
            complete:function () {
                Duplicator.Pack.PostTransferCleanup(statusInterval, startTime);
            },
            success:function (respData, textStatus, xHr) {
                try {
                    var data = Duplicator.parseJSON(respData);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data:' + respData);
                    $('#dup-build-progress-bar-wrapper').hide();
                    $('#dup-progress-area, #dup-msg-error').show(200);
                    var status = xHr.status + ' -' + data.statusText;
                    var response = (xHr.responseText != undefined && xHr.responseText.trim().length > 1)
                        ? xHr.responseText.trim()
                        : 'No client side error - see Backup log file';
                    $('#dup-msg-error-response-status span.data').html(status)
                    $('#dup-msg-error-response-text span.data').html(response);
                    console.log(xHr);
                    return false;
                }

                if ((data != null) && (typeof (data) != 'undefined') && data.status == 1) {
                    Duplicator.Pack.WireDownloadLinks(data);
                } else {
                    var message = (typeof (data.error) != 'undefined' && data.error.length) ? data.error :'Error processing Backup';
                    Duplicator.Pack.DupArchiveProcessingFailed(message);
                }

            },
            error:function (xHr) {
                $('#dup-build-progress-bar-wrapper').hide();
                $('#dup-progress-area, #dup-msg-error').show(200);
                var status = xHr.status + ' -' + data.statusText;
                var response = (xHr.responseText != undefined && xHr.responseText.trim().length > 1)
                    ? xHr.responseText.trim()
                    : 'No client side error - see Backup log file';
                $('#dup-msg-error-response-status span.data').html(status)
                $('#dup-msg-error-response-text span.data').html(response);
                console.log(xHr);
            }
        });
        return false;
    }

    /*  ----------------------------------------
     *  METHOD:Performs Ajax post to create a new DupArchive-based package */
    Duplicator.Pack.CreateDupArchive = function () {
        console.log('Duplicator.Pack.CreateDupArchive');
        var statusInterval = setInterval(Duplicator.Pack.GetActivePackageStatus, Duplicator.Pack.StatusFrequency);

        $.ajax({
            type:"POST",
            timeout:0, // no timeout
            dataType:"text",
            url:ajaxurl,
            data:data,
            complete:function () {
                Duplicator.Pack.PostTransferCleanup(statusInterval, Duplicator.Pack.DupArchiveStartTime);
            },
            success:function (respData, textStatus, xHr) {
                try {
                    var data = Duplicator.parseJSON(respData);
                } catch(err) {
                    console.log(err);
                    console.log('JSON parse failed for response data:' + respData);
                    console.log('DupArchive AJAX error!');
                    console.log("jqHr:");
                    console.log(xHr);
                    console.log("textStatus:");
                    console.log(textStatus);
                    Duplicator.Pack.HandleDupArchiveInterruption(xHr.responseText);
                    return false;
                }

                console.log("CreateDupArchive:AJAX success. Data equals:");
                console.log(data);
                // DATA FIELDS
                // archive_offset, archive_size, failures, file_index, is_done, timestamp

                if ((data != null) && (typeof (data) != 'undefined') && ((data.status == 1) || (data.status == 3) || (data.status == 4))) {

                    Duplicator.Pack.DupArchiveFailureCount = 0;

                    // Status = 1 means complete, 4 means more to process
                    console.log("CreateDupArchive:Passed");
                    var criticalFailureText = Duplicator.Pack.GetFailureText(data.failures, true);

                    if (data.failures.length > 0) {
                        console.log("CreateDupArchive:There are failures present. (" + data.failures.length) + ")";
                    }

                    if ((criticalFailureText === '') && (data.status != 3)) {
                        console.log("CreateDupArchive:No critical failures");
                        if (data.status == 1) {

                            // Don't stop for non-critical failures - just display those at the end TODO:put these in the log not popup
                            console.log("CreateDupArchive:archive has completed");
                            if (data.failures.length > 0) {
                                console.log(data.failures);
                                var errorMessage = "CreateDupArchive:Problems during Backup creation. These may be non-critical so continue with install.\n------\n";
                                var len = data.failures.length;

                                for (var j = 0; j < len; j++) {
                                    failure = data.failures[j];
                                    errorMessage += failure + "\n";
                                }
                                alert(errorMessage);
                            }

                            Duplicator.Pack.WireDownloadLinks(data);

                        } else {
                            // data.Status == 4
                            console.log('CreateDupArchive:Archive not completed so continue ping DAWS in 500');
                            setTimeout(Duplicator.Pack.CreateDupArchive, 500);
                        }
                    } else {
                        console.log("CreateDupArchive:critical failures present");
                        // If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
                        var errorString = 'Error Processing Step 1<br/>';
                        errorString += criticalFailureText;
                        Duplicator.Pack.DupArchiveProcessingFailed(errorString);
                    }
                } else {
                    // data is null or Status is warn or fail
                    var errorString = '';
                    if(data == null) {
                        errorString = "Data returned from web service is null.";
                    }
                    else {
                        var errorString = '';
                        if(data.failures.length > 0) {
                            errorString += Duplicator.Pack.GetFailureText(data.failures, false);
                        }
                    }
                    Duplicator.Pack.HandleDupArchiveInterruption(errorString);
                }
            },
            error:function (xHr, textStatus) {
                console.log('DupArchive AJAX error!');
                console.log("jqHr:");
                console.log(xHr);
                console.log("textStatus:");
                console.log(textStatus);
                Duplicator.Pack.HandleDupArchiveInterruption(xHr.responseText);
            }
        });
    };

    /*  ----------------------------------------
     *  METHOD:Retrieves package status and updates UI with build percentage */
    Duplicator.Pack.GetActivePackageStatus = function () {
        console.log('####Duplicator.Pack.GetActivePackageStatus');

        $.ajax({
            type:"POST",
            url:ajaxurl,
            dataType:"text",
            timeout:0, // no timeout
            data:data,
            success:function (respData, textStatus, xHr) {
                try {
                    var data = Duplicator.parseJSON(respData);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data:' + respData);
                    console.log('Error retrieving build status');
                    console.log(xHr);
                    return false;
                }
                if(data.report.status == 1) {
                    $('#dup-progress-percent').html(data.payload.status + "%");
                } else {
                    console.log('Error retrieving build status');
                    console.log(data);
                }
            },
            error:function (xHr) {
                console.log('Error retrieving build status');
                console.log(xHr);
            }
        });
        return false;
    }

    Duplicator.Pack.PostTransferCleanup = function(statusInterval, startTime) {
        clearInterval(statusInterval);
        endTime = new Date().getTime();
        var millis = (endTime - startTime);
        var minutes = Math.floor(millis / 60000);
        var seconds = ((millis % 60000) / 1000).toFixed(0);
        var status = minutes + ":" + (seconds < 10 ? '0' :'') + seconds;
        $('#dup-msg-error-response-time span.data').html(status);
    };

    Duplicator.Pack.WireDownloadLinks = function(data) {
        var pack = data.package;
        var archive_json = {
            filename:pack.Archive.File,
        };
        var installer_json = {
            id:pack.ID,
            hash:pack.Hash
        };

        $('#dup-build-progress-bar-wrapper').hide();
        $('#dup-progress-area, #dup-msg-success').show(300);
        $('#build-success-footer-cta').show();

        $('#dup-btn-archive-size').append('&nbsp; (' + data.archiveSize + ')')
        $('#data-name-hash').text(pack.NameHash || 'error read');
        $('#data-time').text(data.runtime || 'unable to read time');
        $('#dup-create-new').removeClass('no-display');
        $('#dup-packages-btn').removeClass('no-display');

        //Wire Up Downloads
        $('#dup-btn-installer').click(function() {
            Duplicator.Pack.DownloadInstaller(installer_json);
            return false;
        });

        $('#dup-btn-archive').click(function() {
            Duplicator.Pack.DownloadFile(archive_json);
            return false;
        });

        $('#dup-link-download-both').on("click", function () {
            $('#dup-btn-installer').trigger('click');
            setTimeout(function(){
                $('#dup-btn-archive').trigger('click');
            }, 700);
            return false;
        });

        $('#dup-click-to-copy-installer-name').data('dup-copy-text', data.instDownloadName);
        $('#dup-installer-name').data('data-installer-name', data.instDownloadName);
    };

    Duplicator.Pack.HandleDupArchiveInterruption = function (errorText) {
        Duplicator.Pack.DupArchiveFailureCount++;

        if (Duplicator.Pack.DupArchiveFailureCount <= Duplicator.Pack.DupArchiveMaxRetries) {
            console.log("Failure count:" + Duplicator.Pack.DupArchiveFailureCount);
            // / rsr todo don’t worry about this right now Duplicator.Pack.DupArchiveThrottleDelay = 9; // Equivalent of 'low' server throttling (ms)
            console.log('Relaunching in ' + Duplicator.Pack.DupArchiveRetryDelayInMs);
            setTimeout(Duplicator.Pack.CreateDupArchive, Duplicator.Pack.DupArchiveRetryDelayInMs);
        } else {
            console.log('Too many failures.' + errorText);
            // Processing problem
            Duplicator.Pack.DupArchiveProcessingFailed("Too many retries when building DupArchive Backup. " + errorText);
        }
    };

    Duplicator.Pack.DupArchiveProcessingFailed = function (errorText) {
        $('#dup-build-progress-bar-wrapper').hide();
        $('#dup-progress-area, #dup-msg-error').show(200);
        $('#dup-msg-error-response-text span.data').html(errorText);
        $('#dup-pack-build-err-info').trigger('click');
    };

    Duplicator.Pack.GetFailureText = function (failures, onlyCritical)
    {
        var retVal = '';
        if ((failures !== null) && (typeof failures !== 'undefined')) {
            var len = failures.length;

            for (var j = 0; j < len; j++) {
                failure = failures[j];
                if (!onlyCritical || failure.isCritical) {
                    retVal += failure.description;
                    retVal += "<br/>";
                }
            }
        }
        return retVal;
    };

    Duplicator.Pack.ToggleTwoPart = function () {
        var $btn = $('#dup-two-part-btn');
        if ($('#dup-two-part-check').is(':checked')) {
            $btn.removeAttr("disabled");
        } else {
            $btn.attr("disabled", true);
        }
    };

    Duplicator.Pack.ShowInstallerName = function () {
        var txt = $('#dup-installer-name').data('data-installer-name');
        $('#dup-installer-name').html(txt);
        $('#dup-installer-name-help-icon').show();
    };

    //Page Init:
    Duplicator.UI.AnimateProgressBar('dup-progress-bar');

        Duplicator.Pack.CreateZip();
        Duplicator.Pack.CreateDupArchive();
});
</script>

} // namespace CharacterGeneratorDev
