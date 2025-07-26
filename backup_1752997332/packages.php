<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Utils\LinkManager;
use Duplicator\Core\Notifications\Notifications;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/* @var $Package DUP_Package */

// Never display incomplete packages and purge those that are no longer active
DUP_Package::purge_incomplete_package();

$totalElements          = DUP_Package::count_by_status();
$completeCount          = DUP_Package::count_by_status(array(array('op' => '>=', 'status' => DUP_PackageStatus::COMPLETE))); // total packages completed
$active_package_present = DUP_Package::isPackageRunning();
$is_mu                  = is_multisite();

$package_running = false;
global $packageTablerowCount;
$packageTablerowCount = 0;

if (DUP_Settings::Get('installer_name_mode') == DUP_Settings::INSTALLER_NAME_MODE_SIMPLE) {
    $packageExeNameModeMsg = __("When clicking the Installer download button, the 'Save as' dialog is currently defaulting the name to 'installer.php'. "
        . "To improve the security and get more information, go to: Settings > Backups Tab > Installer > Name option or click on the gear icon at the top of this page.", 'duplicator');
} else {
    $packageExeNameModeMsg = __("When clicking the Installer download button, the 'Save as' dialog is defaulting the name to '[name]_[hash]_[time]_installer.php'. "
        . "This is the secure and recommended option.  For more information, go to: Settings > Backups Tab > Installer > Name or click on the gear icon at the top of this page.<br/><br/>"
        . "To quickly copy the hashed installer name, to your clipboard use the copy icon link or click the installer name and manually copy the selected text.", 'duplicator');
}
?>

<style>
    div#dup-list-alert-nodata {padding:70px 20px;text-align:center; font-size:20px; line-height:26px}
    div.dup-notice-msg {border:1px solid silver; padding: 10px; border-radius:3px; width: 550px;
                        margin:40px auto 0px auto; font-size:12px; text-align: left; word-break:normal;
                        background: #fefcea; 
                        background: -moz-linear-gradient(top,  #fefcea 0%, #efe5a2 100%);
                        background: -ms-linear-gradient(top,  #fefcea 0%,#efe5a2 100%);
                        background: linear-gradient(to bottom,  #fefcea 0%,#efe5a2 100%);
    }
    input#dup-bulk-action-all {margin:0 2px 0 0;padding:0 2px 0 0 }
    button.dup-button-selected {border:1px solid #000 !important; background-color:#dfdfdf !important;}
    div.dup-quick-start {font-style:italic; font-size: 13px; line-height: 18px; margin-top: 15px}
    div.dup-no-mu {font-size:13px; margin-top:25px; color:maroon; line-height:18px}
    a.dup-btn-disabled {color:#999 !important; border: 1px solid #999 !important}

    /* Table package details */
    table.dup-pack-table {word-break:break-all;}
    table.dup-pack-table th {white-space:nowrap !important;}
    table.dup-pack-table td.pack-name {text-overflow:ellipsis; white-space:nowrap}
    table.dup-pack-table td.pack-size {min-width: 65px; }

    table.dup-pack-table input[name="delete_confirm"] {margin-left:15px}
    table.dup-pack-table td.fail {border-left: 4px solid maroon;}
    table.dup-pack-table td.pass {border-left: 4px solid #2ea2cc;}

    .dup-pack-info {height:50px;}
    .dup-pack-info td {vertical-align: middle; }
    tr.dup-pack-info td {white-space:nowrap; padding:2px 30px 2px 7px;}
    tr.dup-pack-info td.get-btns {text-align:right; padding:3px 5px 6px 0px !important;}
    tr.dup-pack-info td.get-btns button {box-shadow:none}
    textarea.dup-pack-debug {width:98%; height:300px; font-size:11px; display:none}
    td.error-msg a {color:maroon; text-decoration: underline}
    td.error-msg a:hover {color:maroon; text-decoration:none}
    td.error-msg {padding:7px 18px 0px 0px; color:maroon; text-align: center !important;}
    div#dup-help-dlg i {display: inline-block; width: 15px; padding:2px;line-height:28px; font-size:14px;}
    tr.dup-pack-info sup  {font-style:italic;font-size:10px; cursor: pointer; vertical-align: baseline; position: relative; top: -0.8em;}
    tr#pack-processing {display: none}

    th.inst-name {width: 1000px; padding: 2px 7px; }
    .inst-name  input {width: 175px; margin: 0 5px; border:1px solid #CCD0D4; cursor:pointer;}
    .inst-name  input:focus { width: 600px;}

    /* Building package */
    .dup-pack-info .building-info {display: none; color: #2C8021; font-style: italic}
    .dup-pack-info .building-info .perc {font-weight: bold}
    .dup-pack-info.is-running .building-info {display: inline;}
    .dup-pack-info.is-running .get-btns button {display: none;}
    div.sc-footer-left {color:maroon; font-size:11px; font-style: italic; float:left}
    div.sc-footer-right {font-style: italic; float:right; font-size:12px}
</style>
<form id="form-duplicator" method="post">

    <!-- ====================
    TOOL-BAR -->
    <table id="dup-toolbar">
        <tr valign="top">
            <td style="white-space: nowrap">
                <select id="dup-pack-bulk-actions">
                    <option value="-1" selected="selected">
                    </option>
                    </option>
                </select>
                <input 
                    type="button" id="dup-pack-bulk-apply" 
                    onclick="Duplicator.Pack.ConfirmDelete()"
                >
                <span class="btn-separator"></span>
                    <i class="fa fa-question-circle"></i>
                </a>
                <a 
                    class="button" 
                >
                    <i class="fas fa-sliders-h"></i>
                </a>
                <a 
                    class="button dup-btn-disabled" 
                >
                    <i class="far fa-clone"></i>
                </a>
                <span class="btn-separator"></span>
                <a 
                    class="button dup-btn-disabled" 
                >
                    <i class="fas fa-arrow-alt-circle-down"></i>
                </a>
                    <i class="fas fa-undo-alt"></i>
                </a>
            </td>
            <td>                        
                $package_url = ControllersManager::getPackageBuildUrl();
                ?>
                <a id="dup-create-new" 
                   onclick="return Duplicator.Pack.CreateNew(this);"
                </a>
            </td>
        </tr>
    </table>

        <!-- ====================
        NO-DATA MESSAGES-->
        <table class="widefat dup-pack-table">
            <thead><tr><th>&nbsp;</th></tr></thead>
            <tbody>
                <tr>
                    <td>
                        <div id='dup-list-alert-nodata'>
                            <i class="fa fa-archive fa-sm"></i> 
                                </a>
                            </div>
                                    echo '<div class="dup-no-mu">';
                                    echo '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;';
                                    esc_html_e('Duplicator Lite does not officially support WordPress multisite.', 'duplicator');
                                    echo "<br/>";
                                    esc_html_e('We strongly recommend upgrading to ', 'duplicator');
                                    echo "&nbsp;<i><a href='" . esc_url(LinkManager::getCampaignUrl('packages-list', "Mutlisite no packages")) . "' target='_blank'>[" . esc_html__('Duplicator Pro', 'duplicator') . "]</a></i>.";
                                    echo '</div>';
                            }
                            ?>
                            <div style="height:75px">&nbsp;</div>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot><tr><th>&nbsp;</th></tr></tfoot>
        </table>
        <!-- ====================
        LIST ALL PACKAGES -->
        <table class="widefat dup-pack-table">
            <thead>
                <tr>
                    <th style="width: 30px;">
                    </th>
                    <th class="inst-name">
                        <i class="fas fa-question-circle fa-sm" 
                        </i>
                    </th>
                    <th style="text-align:center; width: 200px;">
                    </th>
                </tr>
            </thead>
            <tr id="pack-processing">
                <td colspan="6">
                    <div id='dup-list-alert-nodata'>
                        <i class="fa fa-archive fa-sm"></i>
                        <div class="dup-quick-start">
                            </a>
                        </div>
                        <div style="height:75px">&nbsp;</div>
                    </div>
                </td>
            </tr>

            function tablePackageRow(DUP_Package $Package)
            {
                global $packageTablerowCount;

                $is_running_package = $Package->isRunning();
                $pack_name          = $Package->Name;
                $pack_archive_size  = $Package->getArchiveSize();
                $pack_perc          = $Package->Status;
                $pack_dbonly        = $Package->Archive->ExportOnlyDB;
                $pack_build_mode    = ($Package->Archive->Format === 'ZIP') ? true : false;

                //Links
                $uniqueid    = $Package->NameHash;
                $packagepath = DUP_Settings::getSsdirUrl() . '/' . $Package->Archive->File;

                $css_alt = ($packageTablerowCount % 2 != 0) ? '' : 'alternate';

                if ($Package->Status >= 100 || $is_running_package) :
                    ?>
                        <td>
                            echo DUP_Package::getCreatedDateFormat($Package->Created, DUP_Settings::get_create_date_format());
                            echo ' ' . ($pack_build_mode ?
                                "<sup title='" . __('Backup created as zip file', 'duplicator') . "'>zip</sup>" :
                                "<sup title='" . __('Backup created as daf file', 'duplicator') . "'>daf</sup>");
                            ?>
                        </td>
                        <td class='pack-name'>
                            <span class="building-info" >
                                &nbsp; <i class="fas fa-question-circle fa-sm" style="color:#2C8021"
                            </span>
                        </td>
                        <td class="inst-name">
                            switch (DUP_Settings::Get('installer_name_mode')) {
                                case DUP_Settings::INSTALLER_NAME_MODE_SIMPLE:
                                    $lockIcon = 'fa-shield-alt fa-fw shield-off';
                                    break;
                                case DUP_Settings::INSTALLER_NAME_MODE_WITH_HASH:
                                default:
                                    $lockIcon = 'fa-shield-alt fa-fw shield-on';
                                    break;
                            }
                            $installerName = $Package->getInstDownloadName();
                            ?>
                            <a href="admin.php?page=duplicator-settings&tab=packageadmin.php?page=duplicator-settings&tab=package#duplicator-installer-settings"
                            </a>
                        </td>
                        <td class="get-btns">
                            <button 
                                class="button no-select" 
                            </button>
                            <button 
                                class="button no-select"
                            </button>
                                <i class="fa fa-archive fa-sm" ></i>
                            </button>
                        </td>
                    </tr>
                else :
                    $error_url = "?page=duplicator&action=detail&tab=detail&id={$Package->ID}";
                    ?>
                        <td>&nbsp;</td>
                        <td class="get-btns error-msg" colspan="3">
                            <i class="fa fa-exclamation-triangle fa-sm"></i>
                        </td>
                    </tr>
                //$totalSize = $totalSize + $pack_archive_size;
                $packageTablerowCount++;
            }
            DUP_Package::by_status_callback('tablePackageRow', array(), false, 0, '`id` DESC');
            ?>
            <tfoot>
                <tr>
                    <th colspan="11">
                        <div class="sc-footer-left">
                                esc_html_e("Trace Logging Enabled.  Please disable when trace capture is complete.", 'duplicator');
                                echo '<br/>';
                            } ?>
                        </div>
                        <div class="sc-footer-right">
                                $dup_serv_time = date_i18n('H:i');
                                esc_html_e("Time", 'duplicator');
                                echo ": {$dup_serv_time}";
                            ?>
                        </span>
                        </div>

                    </th>
                </tr>
            </tfoot>
        </table>

        <div style="float:right; padding:10px 5px">
            echo $totalElements;
            echo '&nbsp;';
            esc_html_e("Items", 'duplicator');
            ?>
        </div>

</form>

<!-- ==========================================
THICK-BOX DIALOGS: -->
$alert1           = new DUP_UI_Dialog();
$alert1->title    = __('Bulk Action Required', 'duplicator');
$alert1->message  = '<i class="fa fa-exclamation-triangle fa-sm"></i>&nbsp;';
$alert1->message .= __('No selections made! Please select an action from the "Bulk Actions" drop down menu.', 'duplicator');
$alert1->initAlert();

$alert2           = new DUP_UI_Dialog();
$alert2->title    = __('Selection Required', 'duplicator', 'duplicator');
$alert2->message  = '<i class="fa fa-exclamation-triangle fa-sm"></i>&nbsp;';
$alert2->message .= __('No selections made! Please select at least one Backup to delete.', 'duplicator');
$alert2->initAlert();

$confirm1               = new DUP_UI_Dialog();
$confirm1->title        = __('Delete Backups?', 'duplicator');
$confirm1->message      = __('Are you sure you want to delete the selected Backup(s)?', 'duplicator');
$confirm1->progressText = __('Removing Backups, Please Wait...', 'duplicator');
$confirm1->jscallback   = 'Duplicator.Pack.Delete()';
$confirm1->initConfirm();

$alert3          = new DUP_UI_Dialog();
$alert3->height  = 400;
$alert3->width   = 450;
$alert3->title   = __('Duplicator Help', 'duplicator');
$alert3->message = "<div id='dup-help-dlg'></div>";
$alert3->initAlert();

$alertPackRunning          = new DUP_UI_Dialog();
$alertPackRunning->title   = __('Alert!', 'duplicator');
$alertPackRunning->message = __('A Backup is being processed. Retry later.', 'duplicator');
$alertPackRunning->initAlert();
?>

<!-- =======================
DIALOG: HELP DIALOG -->
<div id="dup-help-dlg-info" style="display:none">
    <i class="far fa-file-alt fa-sm"></i>
    </a> <br/>
    <i class="far fa-file-alt fa-sm"></i> 
    </a>  <br/>
    <i class="far fa-file-code"></i>
    </a>
    <br/><br/>

    <i class="fas fa-question-circle fa-sm fa-fw"></i>

        <i class="fa fa-star fa-fw"></i>
</div>

<script>
    jQuery(document).ready(function ($)
    {
        /** Create new package check */
        Duplicator.Pack.CreateNew = function (e) {
            var cButton = $(e);
            if (cButton.hasClass('disabled')) {
            } else {
                Duplicator.Pack.GetActivePackageInfo(function (info) {
                    if (info.present) {
                        cButton.addClass('disabled');
                        // reloag current page to update packages list
                        location.reload(true);
                    } else {
                        // no active package. Load step1 page.
                        window.location = cButton.attr('href');
                    }
                });
            }
            return false;
        };

        /*  Creats a comma seperate list of all selected package ids  */
        Duplicator.Pack.GetDeleteList = function ()
        {
            var arr = new Array;
            var count = 0;
            $("input[name=delete_confirm]").each(function () {
                if (this.checked) {
                    arr[count++] = this.id;
                }
            });
            return arr;
        }

        /*  Provides the correct confirmation items when deleting packages */
        Duplicator.Pack.ConfirmDelete = function ()
        {
            if ($("#dup-pack-bulk-actions").val() != "delete") {
                return;
            }

            var list = Duplicator.Pack.GetDeleteList();
            if (list.length == 0) {
                return;
            }
        }


        /*  Removes all selected package sets 
         *  @param event    To prevent bubbling */
        Duplicator.Pack.Delete = function (event)
        {
            var list = Duplicator.Pack.GetDeleteList();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'duplicator_package_delete',
                    package_ids: list,
                },
                complete: function (data) {
                    console.log(data);
                    Duplicator.ReloadWindow(data);
                }
            });

        };

        Duplicator.Pack.ActivePackageInfo = function (info) {
            $('.dup-pack-info.is-running .pack-size').text(info.size_format);

            if (info.present) {
                $('.dup-pack-info.is-running .building-info .perc').text(info.status);

                setTimeout(function () {
                    Duplicator.Pack.GetActivePackageInfo(Duplicator.Pack.ActivePackageInfo);
                }, 1000);

            } else {
                $('.dup-pack-info.is-running').removeClass('is-running');
                $('#dup-create-new.disabled').removeClass('disabled');
            }
        }

        /*  Get active package info
         *
         *    */
        Duplicator.Pack.GetActivePackageInfo = function (callbackOnSuccess)
        {
            $.ajax({
                type: "POST",
                cache: false,
                url: ajaxurl,
                dataType: "json",
                timeout: 10000000,
                data: {
                    action: 'duplicator_active_package_info',
                },
                complete: function () {},
                success: function (result) {
                    console.log(result);
                    if (result.success) {
                        if ($.isFunction(callbackOnSuccess)) {
                            callbackOnSuccess(result.data.active_package);
                        }
                    } else {
                        // @todo manage error
                    }
                },
                error: function (result) {
                    var result = result || new Object();
                    // @todo manage error
                }
            });
        };

        /* Toogles the Bulk Action Check boxes */
        Duplicator.Pack.SetDeleteAll = function ()
        {
            var state = $('input#dup-bulk-action-all').is(':checked') ? 1 : 0;
            $("input[name=delete_confirm]").each(function () {
                this.checked = (state) ? true : false;
            });
        }

        /*  Opens detail screen */
        Duplicator.Pack.OpenPackageDetails = function (package_id)
        {
            window.location.href = '?page=duplicator&action=detail&tab=detail&id=' + package_id;
        }

        /*  Toggles the feedback form */
        Duplicator.Pack.showHelp = function ()
        {
            $('#dup-help-dlg').html($('#dup-help-dlg-info').html());
        }

            $('#pack-processing').show();
endif;

if ($active_package_present) :
    ?>
            Duplicator.Pack.GetActivePackageInfo(Duplicator.Pack.ActivePackageInfo);

    });
</script>

} // namespace CharacterGeneratorDev
