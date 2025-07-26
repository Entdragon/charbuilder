<?php

namespace CharacterGeneratorDev {


use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
$view_state     = DUP_UI_ViewState::getArray();
$ui_css_general = (isset($view_state['dup-package-dtl-general-panel']) && $view_state['dup-package-dtl-general-panel']) ? 'display:block' : 'display:none';
$ui_css_storage = (isset($view_state['dup-package-dtl-storage-panel']) && $view_state['dup-package-dtl-storage-panel']) ? 'display:block' : 'display:none';
$ui_css_archive = (isset($view_state['dup-package-dtl-archive-panel']) && $view_state['dup-package-dtl-archive-panel']) ? 'display:block' : 'display:none';
$ui_css_install = (isset($view_state['dup-package-dtl-install-panel']) && $view_state['dup-package-dtl-install-panel']) ? 'display:block' : 'display:none';

$archiveDownloadInfo       = $package->getPackageFileDownloadInfo(DUP_PackageFileType::Archive);
$logDownloadInfo           = $package->getPackageFileDownloadInfo(DUP_PackageFileType::Log);
$installerDownloadInfo     = $package->getInstallerDownloadInfo();
$archiveDownloadInfoJson   = SnapJson::jsonEncodeEscAttr($archiveDownloadInfo);
$logDownloadInfoJson       = SnapJson::jsonEncodeEscAttr($logDownloadInfo);
$installerDownloadInfoJson = SnapJson::jsonEncodeEscAttr($installerDownloadInfo);
$showLinksDialogJson       = SnapJson::jsonEncodeEscAttr(array(
    "archive" => $archiveDownloadInfo["url"],
    "log"     => $logDownloadInfo["url"],
));

$debug_on                = DUP_Settings::Get('package_debug');
$mysqldump_on            = DUP_Settings::Get('package_mysqldump') && DUP_DB::getMySqlDumpPath();
$mysqlcompat_on          = isset($Package->Database->Compatible) && strlen($Package->Database->Compatible);
$mysqlcompat_on          = ($mysqldump_on && $mysqlcompat_on) ? true : false;
$dbbuild_mode            = $package->Database->info->buildMode;
$archive_build_mode      = ($package->Archive->Format === 'ZIP') ? 'ZipArchive (zip)' : 'DupArchive (daf)';
$dup_install_secure_on   = isset($package->Installer->OptsSecureOn) ? $package->Installer->OptsSecureOn : 0;
$dup_install_secure_pass = isset($package->Installer->OptsSecurePass) ? DUP_Util::installerUnscramble($package->Installer->OptsSecurePass) : '';
$installerNameMode       = DUP_Settings::Get('installer_name_mode');
$storage_position        = DUP_Settings::Get('storage_position');

$currentStoreURLPath = DUP_Settings::getSsdirUrl();
$installerSecureName = $package->getInstDownloadName(true);
$installerDirectLink = "{$currentStoreURLPath}/" . pathinfo($installerSecureName, PATHINFO_FILENAME) . DUP_Installer::INSTALLER_SERVER_EXTENSION;
?>

<style>
    /*COMMON*/
    div.toggle-box {float:right; margin: 5px 5px 5px 0}
    div.dup-box {margin-top: 15px; font-size:14px; clear: both}
    table.dup-dtl-data-tbl {width:100%}
    table.dup-dtl-data-tbl tr {vertical-align: top}
    table.dup-dtl-data-tbl tr:first-child td {margin:0; padding-top:0 !important;}
    table.dup-dtl-data-tbl td {padding:0 5px 0 0; padding-top:10px !important;}
    table.dup-dtl-data-tbl td:first-child {font-weight: bold; width:130px}
    table.dup-sub-list td:first-child {white-space: nowrap; vertical-align: middle; width:100px !important;}
    table.dup-sub-list td {white-space: nowrap; vertical-align:top; padding:2px !important;}
    div.dup-box-panel-hdr {font-size:14px; display:block; border-bottom: 1px solid #efefef; margin:5px 0 5px 0; font-weight: bold; padding: 0 0 5px 0}
    td.sub-notes {font-weight: normal !important; font-style: italic; color:#999; padding-top:10px;}
    div.sub-notes {font-weight: normal !important; font-style: italic; color:#999;}

    /*STORAGE*/
    div.dup-store-pro {font-size:12px; font-style:italic;}
    div.dup-store-pro img {height:14px; width:14px; vertical-align: text-top}
    div.dup-store-pro a {text-decoration: underline}

    /*GENERAL*/
    div#dup-name-info, div#dup-version-info {display: none; line-height:20px; margin:4px 0 0 0}
    table.dup-sub-info td {padding: 1px !important}
    table.dup-sub-info td:first-child {font-weight: bold; width:100px; padding-left:10px}

    div#dup-downloads-area {padding: 5px 0 5px 0; }
    div#dup-downloads-area i.fa-shield-alt {display: block; float:right; margin-top:8px}
    div#dup-downloads-area i.fa-bolt {display: inline-block; border:0 solid red}
    div#dup-downloads-msg {margin-bottom:-5px; font-style: italic}
    div.sub-section {padding:7px 0 0 0}
    textarea.file-info {width:100%; height:100px; font-size:12px }

    /*INSTALLER*/
    div#dup-pass-toggle {position: relative; margin:0; width:273px}
    input#secure-pass {border-radius:4px 0 0 4px; width:250px; height: 23px; margin:0}
    button#secure-btn {height:30px; width:30px; position:absolute; top:0px; right:0px;border:1px solid silver;  border-radius:0 4px 4px 0; cursor:pointer}
    div.dup-install-hdr-2 {font-weight:bold; border-bottom:1px solid #dfdfdf; padding-bottom:2px; width:100%}
</style>


<div class="toggle-box">
</div>

<!-- ===============================
GENERAL -->
<div class="dup-box">
<div class="dup-box-title">
    <div class="dup-box-arrow"></div>
</div>
    <table class='dup-dtl-data-tbl'>
        <tr>
            <td>
                <span class="link-style" onclick="jQuery('#dup-name-info').toggle()">
                </span>
                <div id="dup-name-info">
                    <table class="dup-sub-info">
                        <tr>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                        </tr>                        
                    </table>
                </div>
            </td>
        </tr>
        <tr>
        </tr>
        <tr>
        </tr>
        <tr>
            <td>
                <span class="link-style" onclick="jQuery('#dup-version-info').toggle()">
                </span>
                <div id="dup-version-info">
                    <table class="dup-sub-info">
                        <tr>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                            <td>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
        </tr>
        <tr>
        </tr>
        <tr>
        </tr>
        <tr>
            <td>
                <div id="dup-downloads-area">
                        if ($installerNameMode === DUP_Settings::INSTALLER_NAME_MODE_WITH_HASH) {
                            $installBtnTooltip = __('Download hashed installer ([name]_[hash]_[time]_installer.php)', 'duplicator');
                            $installBtnIcon    = '<i class="fas fa-shield-alt fa-sm fa-fw shield-on"></i>';
                        } else {
                            $installBtnTooltip = __('Download basic installer (installer.php)', 'duplicator');
                            $installBtnIcon    = '<i class="fas fa-shield-alt fa-sm fa-fw shield-off"></i>';
                        }
                        ?>
                        <div class="sub-notes">
                            <i class="fas fa-download fa-fw"></i>
                            <br/><br/>
                        </div>
                        <button class="button"
                        </button>
                        </button>
                        </button>
                        </button>
                </div>
                <table class="dup-sub-list">
                    <tr>
                        <td>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            </a>
                        </td>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                        <td class="sub-notes" colspan="2">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</div>

<!-- ==========================================
DIALOG: QUICK PATH -->
    <p style="color:maroon">
        <i class="fa fa-lock fa-xs"></i>
    </p>

    <div style="padding: 0px 5px 5px 5px;">
        <a href="javascript:void(0)" style="display:inline-block; text-align:right" onclick="Duplicator.Pack.GetLinksText()">
        </a> <br/>
        <textarea id="dup-dlg-quick-path-data" style='border:1px solid silver; border-radius:2px; width:100%; height:175px; font-size:11px'></textarea><br/>
        <i style='font-size:11px'>
                printf(
                    esc_html_x(
                        "A copy of the database.sql and installer.php files can both be found inside of the archive.zip/daf file.  "
                        . "Download and extract the Backup file to get a copy of the installer which will be named 'installer-backup.php'. "
                        . "For details on how to extract a archive.daf file please see: "
                        . '%1$sHow to work with DAF files and the DupArchive extraction tool?%2$s',
                        '%1$s and %2$s are opening and closing <a> tags',
                        'duplicator'
                    ),
                    '<a href="' . esc_url(LinkManager::getDocUrl('how-to-work-with-daf-files-and-the-duparchive-extraction-tool', 'package-deatils')) . '" '
                    . 'target="_blank">',
                    '</a>'
                );
                ?>
        </i>
    </div>
</div>

<!-- ===============================
STORAGE -->
<div class="dup-box">
<div class="dup-box-title">
    <i class="fas fa-hdd fa-sm"></i>
    <div class="dup-box-arrow"></div>
</div>

    <table class="widefat package-tbl" style="margin-bottom:15px" >
        <thead>
            <tr>
            </tr>
        </thead>
        <tbody>
            <tr class="dup-store-path">
                <td>
                    <i>
                        if ($storage_position === DUP_Settings::STORAGE_POSITION_LEGACY) {
                            esc_html_e("(Legacy Path)", 'duplicator');
                        } else {
                            esc_html_e("(Contents Path)", 'duplicator');
                        }
                        ?>
                    </i>
                </td>
                <td>
                    <i class="far fa-hdd fa-fw"></i>
                </td>
                <td>
                        echo DUP_Settings::getSsdirPath();
                        echo '<br/>';
                        echo DUP_Settings:: getSsdirUrl();
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="5" class="dup-store-promo-area">
                    <div class="dup-store-pro">
                        <span class="dup-pro-text">
                                __('Back up this site to %1$s, %2$s, %3$s, %4$s, %5$s and other locations with ', 'duplicator'),
                                '<i class="fab fa-aws  fa-fw"></i>&nbsp;' . 'Amazon',
                                '<i class="fab fa-dropbox fa-fw"></i>&nbsp;' . 'Dropbox',
                                '<i class="fab fa-google-drive  fa-fw"></i>&nbsp;' . 'Google Drive',
                                '<i class="fas fa-cloud  fa-fw"></i>&nbsp;' . 'OneDrive',
                                '<i class="fas fa-network-wired fa-fw"></i>&nbsp;' . 'FTP/SFTP'
                            ); ?>
                            <a 
                                target="_blank"
                                class="link-style">
                            </a>
                            <i class="fas fa-question-circle"
                                        . 'cloud location such as Google Drive, Amazon, Dropbox and many more.', 'duplicator'); ?>">
                             </i>
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    </div>
</div>

<!-- ===============================
ARCHIVE -->
<div class="dup-box">
<div class="dup-box-title">
    <div class="dup-box-arrow"></div>
</div>

    <!-- FILES -->
    <div class="dup-box-panel-hdr">
        <i class="fas fa-folder-open fa-sm"></i>
    </div>
    <table class='dup-dtl-data-tbl'>
        <tr>

        </tr>

            <tr>
            </tr>
            <tr>
                <td>
                    <div class="sub-section">
                            $txt = strlen($package->Archive->FilterDirs)
                                ? str_replace(';', ";\n", $package->Archive->FilterDirs)
                                : esc_html__('- no filters -', 'duplicator');
                        ?>
                    </div>

                    <div class="sub-section">
                        echo isset($package->Archive->FilterExts) && strlen($package->Archive->FilterExts)
                            ? esc_html($package->Archive->FilterExts)
                            : esc_html__('- no filters -', 'duplicator');
                        ?>
                    </div>

                    <div class="sub-section">
                            $txt = strlen($package->Archive->FilterFiles)
                                ? str_replace(';', ";\n", $package->Archive->FilterFiles)
                                : esc_html__('- no filters -', 'duplicator');
                        ?>
                    </div>
                </td>
            </tr>
    </table>
    <br/><br/>

    <!-- DATABASE -->
    <div class="dup-box-panel-hdr">
        <i class="fas fa-database fa-sm"></i>
    </div>
    <table class='dup-dtl-data-tbl'>
        <tr>
        </tr>
        <tr>
        </tr>
        <tr>
            <td>
                    <br/>
                    <small style="font-style:italic; color:maroon">
                    </small>
            </td>
        </tr>
        <tr>
        </tr>
        <tr class="sub-section">
            <td>&nbsp;</td>
            <td>
                    echo isset($package->Database->FilterTables) && strlen($package->Database->FilterTables)
                        ? str_replace(',', "<br>\n", $package->Database->FilterTables)
                        : esc_html__('- no filters -', 'duplicator');
                ?>
            </td>
        </tr>
    </table>
</div>
</div>


<!-- ===============================
INSTALLER -->
<div class="dup-box" style="margin-bottom: 50px">
<div class="dup-box-title">
    <div class="dup-box-arrow"></div>
</div>

    <table class='dup-dtl-data-tbl'>
        <tr>
            <td colspan="2">
            </td>
        </tr>
        <tr>
            <td>
            </td>
            <td>
                if ($dup_install_secure_on) {
                    esc_html_e('Password Protection Enabled', 'duplicator');
                } else {
                    esc_html_e('Password Protection Disabled', 'duplicator');
                }
                ?>
            </td>
        </tr>
            <tr>
                <td></td>
                <td>
                    <div id="dup-pass-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
    </table>
    <br/><br/>

    <table class='dup-dtl-data-tbl'>
        <tr>
            <td colspan="2">
            </td>
        </tr>
        <tr>
        </tr>
        <tr>
        </tr>
        <tr>
        </tr>
    </table>
</div>
</div>

    <div style="margin:0">
    </div>


<script>
jQuery(document).ready(function($)
{

    /*  Shows the 'Download Links' dialog
     *  @param db       The path to the sql file
     *  @param install  The path to the install file
     *  @param pack     The path to the package file */
    Duplicator.Pack.ShowLinksDialog = function(json)
    {
        var url = '#TB_inline?width=650&height=325&inlineId=dup-dlg-quick-path';

            '"%s" + "\n\n%s:\n" + json.archive + "\n\n%s:\n" + json.log + "\n\n%s";',
            '=========== SENSITIVE INFORMATION START ===========',
            esc_html__("BACKUP", 'duplicator'),
            esc_html__("LOG", 'duplicator'),
            '=========== SENSITIVE INFORMATION END ==========='
        );
                    ?>
        $("#dup-dlg-quick-path-data").val(msg);
        return false;
    }

    //LOAD: 'Download Links' Dialog and other misc setup
    Duplicator.Pack.GetLinksText = function() {$('#dup-dlg-quick-path-data').select();};

    Duplicator.Pack.OpenAll = function () {
        Duplicator.UI.IsSaveViewState = false;
        var states = [];
        $("div.dup-box").each(function() {
            var pan = $(this).find('div.dup-box-panel');
            var panel_open = pan.is(':visible');
            if (! panel_open)
                $( this ).find('div.dup-box-title').trigger("click");
            states.push({
                key: pan.attr('id'),
                value: 1
            });
        });
        Duplicator.UI.SaveMulViewStates(states);
        Duplicator.UI.IsSaveViewState = true;
    };

    Duplicator.Pack.CloseAll = function () {
        Duplicator.UI.IsSaveViewState = false;
        var states = [];
        $("div.dup-box").each(function() {
            var pan = $(this).find('div.dup-box-panel');
            var panel_open = pan.is(':visible');
            if (panel_open)
                $( this ).find('div.dup-box-title').trigger("click");
            states.push({
                key: pan.attr('id'),
                value: 0
            });
        });
        Duplicator.UI.SaveMulViewStates(states);
        Duplicator.UI.IsSaveViewState = true;
    };

    Duplicator.Pack.TogglePassword = function()
    {
        var $input  = $('#secure-pass');
        var $button =  $('#secure-btn');
        if (($input).attr('type') == 'text') {
            $input.attr('type', 'password');
            $button.html('<i class="fas fa-eye fa-xs"></i>');
        } else {
            $input.attr('type', 'text');
            $button.html('<i class="fas fa-eye-slash fa-xs"></i>');
        }
    }
});
</script>

} // namespace CharacterGeneratorDev
