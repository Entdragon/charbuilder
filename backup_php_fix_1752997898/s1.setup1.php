<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Controllers\ControllersManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
global $wpdb;

//POST BACK: Rest Button
if (isset($_POST['action'])) {
    $action        = sanitize_text_field($_POST['action']);
    $action_result = DUP_Settings::DeleteWPOption($action);
    switch ($action) {
        case 'duplicator_package_active':
            $action_result   = DUP_Settings::DeleteWPOption($action);
            $action_response = __('Backup settings have been reset.', 'duplicator');
            break;
    }
}

DUP_Util::initSnapshotDirectory();

$Package   = DUP_Package::getActive();
$dup_tests = array();
$dup_tests = DUP_Server::getRequirements();

//View State
$ctrl_ui = new DUP_CTRL_UI();
$ctrl_ui->setResponseType('PHP');
$data = $ctrl_ui->GetViewStateList();

$ui_css_storage     = (isset($data->payload['dup-pack-storage-panel']) && !$data->payload['dup-pack-storage-panel']) ? 'display:none' : 'display:block';
$ui_css_archive     = (isset($data->payload['dup-pack-archive-panel']) && $data->payload['dup-pack-archive-panel']) ? 'display:block' : 'display:none';
$ui_css_installer   = (isset($data->payload['dup-pack-installer-panel']) && $data->payload['dup-pack-installer-panel']) ? 'display:block' : 'display:none';
$dup_intaller_files = implode(", ", array_keys(DUP_Server::getInstallerFiles()));
$dbbuild_mode       = (DUP_Settings::Get('package_mysqldump') && DUP_DB::getMySqlDumpPath()) ? 'mysqldump' : 'PHP';
$archive_build_mode = DUP_Settings::Get('archive_build_mode') == DUP_Archive_Build_Mode::ZipArchive ? 'zip' : 'daf';

//="No Selection", 1="Try Again", 2="Two-Part Install"
$retry_state = isset($_GET['retry']) ? $_GET['retry'] : 0;
?>

<style>
    /* REQUIREMENTS*/
    div.dup-sys-section {margin:1px 0px 5px 0px}
    div.dup-sys-title {display:inline-block; width:250px; padding:1px; }
    div.dup-sys-title div {display:inline-block; }
    div.dup-sys-info {display:none; max-width: 98%; margin:4px 4px 12px 4px}    
    div.dup-sys-pass {display:inline-block; color:green;font-weight:bold}
    div.dup-sys-fail {display:inline-block; color:#AF0000;font-weight:bold}
    div.dup-sys-contact {padding:5px 0px 0px 10px; font-size:11px; font-style:italic}
    span.dup-toggle {float:left; margin:0 2px 2px 0; }
    table.dup-sys-info-results td:first-child {width:200px}
    table.dup-sys-info-results td:nth-child(2) {width:100px; font-weight:bold}
    table.dup-sys-info-results td:nth-child(3) {font-style:italic}
</style>


<!-- ============================
TOOL BAR: STEPS -->
<table id="dup-toolbar">
    <tr valign="top">
        <td style="white-space: nowrap">
            <div id="dup-wiz">
                <div id="dup-wiz-steps">
                </div>
            </div>
            <div id="dup-wiz-title" class="dup-guide-txt-color">
                <i class="fab fa-wordpress"></i>
            </div>
        </td>
        <td>&nbsp;</td>
    </tr>
</table>    
<hr class="dup-toolbar-line">



<!-- ============================
SYSTEM REQUIREMENTS -->
    <div class="dup-box">
        <div class="dup-box-title">
            esc_html_e("Requirements:", 'duplicator');
            echo ($dup_tests['Success']) ? ' <div class="dup-sys-pass">Pass</div>' : ' <div class="dup-sys-fail">Fail</div>';
            ?>
            <div class="dup-box-arrow"></div>
        </div>

        <div class="dup-box-panel">

            <div class="dup-sys-section">
            </div>

            <!-- PHP SUPPORT -->
            <div class='dup-sys-req'>
                <div class='dup-sys-title'>
                </div>
                <div class="dup-sys-info dup-info-box">
                    <table class="dup-sys-info-results">
                        <tr>
                        </tr>
                            <tr>
                                <td>
                                        esc_html_x(
                                            'ZipArchive extension is required or %1$sSwitch to DupArchive%2$s to by-pass this requirement.',
                                            '1 and 2 are <a> tags',
                                            'duplicator'
                                        ),
                                        '<a href="admin.php?page=duplicator-settings&tab=package">',
                                        '</a>'
                                    );
                                    ?>
                                </td>
                            </tr>
                        <tr>
                        </tr>                   
                        <tr>
                        </tr>                   
                        <tr>
                        </tr>
                        <tr>
                        </tr>                   
                    </table>
                    <small>
                    </small>
                </div>
            </div>      

            <!-- PERMISSIONS -->
            <div class='dup-sys-req'>
                <div class='dup-sys-title'>
                       <div>
                        if (!in_array('Fail', $dup_tests['IO'])) {
                            echo in_array('Warn', $dup_tests['IO']) ? 'Warn' : 'Pass';
                        } else {
                            echo 'Fail';
                        }
                        ?>
                    </div>
                </div>
                <div class="dup-sys-info dup-info-box">
                    $abs_path = duplicator_get_abs_path();

                    printf("<b>%s</b> &nbsp; [%s] <br/>", $dup_tests['IO']['SSDIR'], DUP_Settings::getSsdirPath());
                    printf("<b>%s</b> &nbsp; [%s] <br/>", $dup_tests['IO']['SSTMP'], DUP_Settings::getSsdirTmpPath());
                    printf("<b>%s</b> &nbsp; [%s] <br/>", $dup_tests['IO']['WPROOT'], $abs_path);
                    ?>
                    <div style="font-size:11px; padding-top: 3px">
                        if ($dup_tests['IO']['WPROOT'] == 'Warn') {
                            echo sprintf(__('If the root WordPress path is not writable by PHP on some systems this can cause issues.', 'duplicator'), $abs_path);
                            echo '<br/>';
                        }
                        esc_html_e("If Duplicator does not have enough permissions then you will need to manually create the paths above. &nbsp; ", 'duplicator');
                        ?>
                    </div>
                </div>
            </div>

            <!-- SERVER SUPPORT -->
            <div class='dup-sys-req'>
                <div class='dup-sys-title'>
                </div>
                <div class="dup-sys-info dup-info-box">
                    <table class="dup-sys-info-results">
                        <tr>
                        </tr>
                        <tr>
                        </tr>
                    </table>
                    <small>
                        esc_html_e(
                            "MySQL version 5.0+ or better is required and the PHP MySQLi extension (note the trailing 'i') is also required.  " .
                            "Contact your server administrator and request that mysqli extension and MySQL Server 5.0+ be installed.",
                            'duplicator'
                        );
                        echo "&nbsp;<i><a href='http://php.net/manual/en/mysqli.installation.php' target='_blank'>[" . esc_html__('more info', 'duplicator') . "]</a></i>";
                        ?>                                      
                    </small>
                    <hr>
                    <table class="dup-sys-info-results">
                        <tr>
                            <td><a href="https://www.php.net/manual/en/mysqli.real-escape-string.php" target="_blank">mysqli_real_escape_string</a></td>
                        </tr>
                    </table>
                    <small>
                            "support and ask them to switch to a different PHP version or configuration.", "duplicator"); ?>
                    </small>
                </div>
            </div>

            <!-- RESERVED FILES -->
            <div class='dup-sys-req'>
                <div class='dup-sys-title'>
                </div>
                <div class="dup-sys-info dup-info-box">
                        esc_html_e("None of the reserved files where found from a previous install.  This means you are clear to create a new Backup.", 'duplicator');
                        echo "  [" . esc_html($dup_intaller_files) . "]";
                        ?>
                    else :
                        $duplicator_nonce = wp_create_nonce('duplicator_cleanup_page');
                        ?> 
                                " To archive your data correctly please remove any of these files from your WordPress root directory. " .
                                " Then try creating your Backup again.", 'duplicator'); ?>
                        </form>
                </div>
            </div>

        </div>
    </div><br/>


<!-- ============================
FORM PACKAGE OPTIONS -->
<div style="padding:5px 5px 2px 5px">
</div>

<!-- CACHE PROTECTION: If the back-button is used from the scanner page then we need to
refresh page in-case any filters where set while on the scanner page -->
<form id="cache_detection">
    <input type="hidden" id="cache_state" name="cache_state" value="" />
</form>

<script>
    jQuery(document).ready(function ($)
    {
        Duplicator.Pack.checkPageCache = function ()
        {
            var $state = $('#cache_state');
            if ($state.val() == "") {
                $state.val("fresh-load");
            } else {
                $state.val("cached");
                    $redirect           = ControllersManager::getMenuLink(
                        ControllersManager::PACKAGES_SUBMENU_SLUG,
                        'new1'
                    );
                    $redirect_nonce_url = wp_nonce_url($redirect, 'new1-package');
                    echo "window.location.href = '{$redirect_nonce_url}'";
                    ?>
            }
        }

        //INIT
        Duplicator.Pack.checkPageCache();

        //Toggle for system requirement detail links
        $('.dup-sys-title a').each(function () {
            $(this).attr('href', 'javascript:void(0)');
            $(this).click({selector: '.dup-sys-info'}, Duplicator.Pack.ToggleSystemDetails);
            $(this).prepend("<span class='ui-icon ui-icon-triangle-1-e dup-toggle' />");
        });

        //Color code Pass/Fail/Warn items
        $('.dup-sys-title div').each(function () {
            console.log($(this).text());
            var state = $(this).text().trim();
            $(this).removeClass();
            $(this).addClass((state == 'Pass') ? 'dup-sys-pass' : 'dup-sys-fail');
        });
    });
</script>

} // namespace CharacterGeneratorDev
