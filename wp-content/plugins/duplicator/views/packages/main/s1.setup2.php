<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;
use Duplicator\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
    /* -----------------------------
    PACKAGE OPTS*/
    form#dup-form-opts label {line-height:22px}
    form#dup-form-opts input[type=checkbox] {margin-top:3px}
    form#dup-form-opts textarea, input[type="text"] {width:100%}

    textarea#package-notes {height:75px;}
    div.dup-notes-add {float:right; margin:-4px 2px 4px 0;}
    div#dup-notes-area {display:none}
    input#package-name {padding:4px; height: 2em;  font-size: 1.2em;  line-height: 100%; width: 100%;   margin: 0 0 3px;}
    tr.dup-store-path td {padding:14px}
    label.lbl-larger {font-size:1.2em}
    div.tab-hdr-title {font-size: 16px; font-weight: bold; padding: 1px; margin:2px 0 5px 0; border-bottom:1px solid #dcdcde }

    /*EXPANDER TITLE BOXES */
    div.dup-box-title div.dup-title-txt {float:left}
    div.dup-box-title 
    div.dup-title-icons { margin-top:-5px; font-weight:normal; font-size:13px; float:left}
    div.dup-box-title div.dup-title-icons > span {border-left:1px solid silver; padding:2px 14px 5px 14px; user-select:none}
    span#dup-installer-secure-lock, span#dup-installer-secure-unlock  {border:none; padding:0 12px 5px 2px;}
    span#dup-installer-secure-lock {border:none; padding:0 12px 5px 2px;}

    /*TAB-1: ARCHIVE SECTION*/
    form#dup-form-opts div.tabs-panel{max-height:800px; padding:15px 20px 20px 20px; min-height:280px}

    /* TAB-2: DATABASE */
    table#dup-dbtables td {padding:0 20px 5px 10px;}
    label.core-table,
    label.non-core-table {padding:2px 0 2px 0; font-size:14px; display: inline-block}
    label.core-table {color:#9A1E26;font-style:italic;font-weight:bold}
    i.core-table-info {color:#9A1E26;font-style:italic;}
    label.non-core-table {color:#000}
    label.non-core-table:hover, label.core-table:hover {text-decoration:line-through}
    table.dbmysql-compatibility {margin-top:-10px}
    table.dbmysql-compatibility td{padding:0 20px 0 2px}
    div.dup-store-pro {font-size:12px; font-style:italic;}
    div.dup-store-pro img {height:14px; width:14px; vertical-align:text-top}
    div.dup-store-pro a {text-decoration:underline}
    span.dup-pro-text {font-style:italic; font-size:12px; color:#555; font-style:italic }
    div#dup-exportdb-items-checked, div#dup-exportdb-items-off {min-height:275px; display:none}
    div#dup-exportdb-items-checked {padding: 5px; max-width:700px}
    div.dup-tbl-scroll {white-space:nowrap; height:350px; overflow-y: scroll; border:1px solid silver; padding:5px 10px; border-radius: 2px;
     background:#fafafa; margin:3px 0 0 0; width:98%}

    /*INSTALLER SECTION*/
    div.dup-installer-header-1 {font-weight:bold; padding-bottom:2px; width:100%}
    div.dup-install-hdr-2 {font-weight:bold; border-bottom:1px solid #dfdfdf; padding-bottom:2px; width:100%}
    span#dup-installer-secure-lock {color:#A62426; display:none; font-size:14px}
    span#dup-installer-secure-unlock {display:none; font-size:14px}
    span#dup-installer-secure-lock {display:none; font-size:14px}
    label.chk-labels {display:inline-block; margin-top:1px}
    table.dup-install-setup {width:100%; margin-left:2px}
    table.dup-install-setup tr{vertical-align: top}
    div.dup-installer-panel-optional {text-align: center; font-style: italic; font-size: 12px; color:maroon}
    label.secure-pass-lbl {display:inline-block; width:125px}
    div#dup-pass-toggle {position: relative; margin:8px 0 0 0; width:243px}
    input#secure-pass {border-radius:4px 0 0 4px; width:214px; height:30px; min-height: auto; margin:0; padding: 0 4px;}
    button#secure-btn {height:30px; width:30px; position:absolute; top:0px; right:0px; border:1px solid silver; border-radius:0 4px 4px 0; cursor:pointer;}
    div.dup-install-prefill-tab-pnl.tabs-panel {overflow:visible;}
    
    /*TABS*/
    ul.add-menu-item-tabs li, ul.category-tabs li {padding:3px 30px 5px}
    div.dup-install-prefill-tab-pnl {min-height:180px !important; }
</style>
$action_url       = ControllersManager::getMenuLink(ControllersManager::PACKAGES_SUBMENU_SLUG, 'new2', null, ['retry' => $retry_state]);
$action_nonce_url = wp_nonce_url($action_url, 'new2-package');
$storage_position = DUP_Settings::Get('storage_position');
?>
<input type="hidden" id="dup-form-opts-action" name="action" value="">

<div>
    <div class="dup-notes-add">
        <a href="javascript:void(0)" onclick="jQuery('#dup-notes-area').toggle()">
        </a>
    </div>
    <div id="dup-notes-area">
    </div>
</div>
<br/>

<!-- ===================
STORAGE -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fas fa-server fa-fw fa-sm"></i>
        <div class="dup-box-arrow"></div>
    </div>          
        <div style="padding:0 5px 3px 0">
            <span class="dup-guide-txt-color">
            </span>
            <div style="float:right">
            </div>
        </div>

        <table class="widefat package-tbl" style="margin-bottom:15px" >
            <thead>
                <tr>
                    <th style='width:30px'></th>
                </tr>
            </thead>
            <tbody>
                <tr class="dup-store-path">
                    <td>
                        <input type="checkbox" checked="checked" disabled="disabled" style="margin-top:-2px"/>
                    </td>
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
                </tr>
                <tr>
                    <td colspan="4" class="dup-store-promo-area">
                        <div class="dup-store-pro">
                            <span class="dup-pro-text">
                                    __('Back up this site to %1$s, %2$s, %3$s, %4$s, %5$s and other locations with ', 'duplicator'),
                                    '<i class="fab fa-aws  fa-fw"></i>&nbsp;' . 'Amazon',
                                    '<i class="fab fa-dropbox fa-fw"></i>&nbsp;' . 'Dropbox',
                                    '<i class="fab fa-google-drive  fa-fw"></i>&nbsp;' . 'Google Drive',
                                    '<i class="fas fa-cloud  fa-fw"></i>&nbsp;' . 'OneDrive',
                                    '<i class="fas fa-network-wired fa-fw"></i>&nbsp;' . 'FTP/SFTP'
                                );
                        ?>
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
</div><br/>


<!-- ============================
ARCHIVE -->
<div class="dup-box">
    <div class="dup-box-title">
        <div class="dup-title-txt">
        <i class="far fa-file-archive"></i>
                _e('Backup', 'duplicator');
                echo "&nbsp;<sup class='archive-ext'>{$archive_build_mode}</sup>";
            ?> &nbsp; &nbsp;
        </div>
        <div class="dup-title-icons" >
                <i class="far fa-copy fa-fw"></i>
                <sup><i class="fa fa-filter fa-sm"></i></sup>
            </span>
                <i class="fa fa-table fa-fw"></i>
                <sup><i class="fa fa-filter fa-sm"></i></sup>
            </span>
                <sup>&nbsp;</sup>
            </span>
        </div>
        <div class="dup-box-arrow"></div>
    </div>      
        <input type="hidden" name="archive-format" value="ZIP" />

        <!-- NESTED TABS -->
        <div data-dup-tabs='true'>
            <ul>
            </ul>

            <!-- TAB1:PACKAGE -->
                'package' => $Package
            )); ?>

            <!-- TAB2: DATABASE -->
            <div>
                <div class="tab-hdr-title">
                </div>

                <table>
                    <tr>
                        <td>
                            <i class="fas fa-question-circle fa-sm"
                            </i>
                        </td>
                    </tr>
                </table>
                <div id="dup-db-filter-items">
                    <div style="float:right; padding-right:10px;">
                    </div>
                    <div class="dup-tbl-scroll">
                        $tables    = $wpdb->get_results("SHOW FULL TABLES FROM `" . esc_sql(DB_NAME) . "` WHERE Table_Type = 'BASE TABLE' ", ARRAY_N);
                        $num_rows  = count($tables);
                        $next_row  = round($num_rows / 4, 0);
                        $counter   = 0;
                        $tableList = explode(',', $Package->Database->FilterTables);

                        echo '<table id="dup-dbtables"><tr><td valign="top">';
                    foreach ($tables as $table) {
                        if (DUP_Util::isTableExists($table[0])) {
                            if (DUP_Util::isWPCoreTable($table[0])) {
                                $core_css  = 'core-table';
                                $core_note = '*';
                            } else {
                                $core_css  = 'non-core-table';
                                $core_note = '';
                            }

                            if (in_array($table[0], $tableList)) {
                                $checked = 'checked="checked"';
                                $css     = 'text-decoration:line-through';
                            } else {
                                $checked = '';
                                $css     = '';
                            }
                            echo  "<label for='dbtables-{$table[0]}' style='{$css}' class='{$core_css}'>"
                                . "<input class='checkbox dbtable' $checked type='checkbox' name='dbtables[]' id='dbtables-{$table[0]}' value='{$table[0]}' onclick='Duplicator.Pack.ExcludeTable(this)' />"
                                . "&nbsp;{$table[0]}{$core_note}</label><br />";
                            $counter++;
                            if ($next_row <= $counter) {
                                echo '</td><td valign="top">';
                                $counter = 0;
                            }
                        }
                    }
                        echo '</td></tr></table>';
                    ?>
                    </div>  
                </div>

                <div class="dup-tabs-opts-help">
                        _e("Checked tables will be <u>excluded</u> from the database script. ", 'duplicator');
                        _e("Excluding certain tables can cause your site or plugins to not work correctly after install!<br/>", 'duplicator');
                        _e("<i class='core-table-info'> Use caution when excluding tables! It is highly recommended to not exclude WordPress core tables*, unless you know the impact.</i>", 'duplicator');
                    ?>
                </div>
                <br/>

                <div class="tab-hdr-title">
                </div>

                <div class="db-configuration" style="line-height: 30px">


                    <br/>
                    <i class="fas fa-question-circle fa-sm"
                               . ' If the database server version is lower than the version where the Backup was built then these options may help generate a script that is more compliant'
                               . ' with the older database server. It is recommended to try each option separately starting with mysql40.', 'duplicator'); ?>">
                    </i>&nbsp;
                    <small style="font-style:italic">
                    </small>

                            $modes       = explode(',', $Package->Database->Compatible);
                            $is_mysql40  = in_array('mysql40', $modes);
                            $is_no_table = in_array('no_table_options', $modes);
                            $is_no_key   = in_array('no_key_options', $modes);
                            $is_no_field = in_array('no_field_options', $modes);
                        ?>
                        <table class="dbmysql-compatibility">
                            <tr>
                                <td>
                                </td>
                                <td>
                                </td>
                                <td>
                                </td>
                                <td>
                                </td>
                            </tr>
                        </table>
               </div>
            </div>

            <!-- TAB3: SECURITY -->
            <div>
                <p>
                </p>
                   class="dup-btn dup-btn-md dup-btn-green"
                   target="_blank">
                </a>
            </div>
        </div>      
    </div>
</div><br/>

<!-- ============================
INSTALLER -->
<div class="dup-box">
<div class="dup-box-title">
    <div class="dup-title-txt">
    </div>
    <div class="dup-title-icons">
            <i class="fas fa-lock fa-sm"></i>
        </span>
            <i class="fas fa-unlock fa-sm"></i>
        </span>
    </div>
    <div class="dup-box-arrow"></div>
</div>          


    <div class="dup-installer-panel-optional">
        <span class="dup-guide-txt-color">
        </span><br/>
        <i class="fas fa-question-circle fa-sm"
                    . 'optionally enter them here and they will be prefilled at install time.  Otherwise you can just enter them in at install time and ignore all these '
                    . 'options in the Installer section.', 'duplicator'); ?>">
        </i>
    </div>

    <table class="dup-install-setup" style="margin-top: -10px">
        <tr>
        </tr>
        <tr>
            <td>
                <i class="fas fa-question-circle fa-sm"
                <br/><br/>
            </td>
        </tr>
        <tr>
            <td>
                    $dup_install_secure_on   = isset($Package->Installer->OptsSecureOn) ? $Package->Installer->OptsSecureOn : 0;
                    $dup_install_secure_pass = isset($Package->Installer->OptsSecurePass) ? DUP_Util::installerUnscramble($Package->Installer->OptsSecurePass) : '';
                ?>
                <i class="fas fa-question-circle fa-sm"
                               . 'password below must be entered before proceeding with an install.  This password is a general deterrent and should not be substituted for properly '
                               . 'keeping your files secure.  Be sure to remove all installer files when the install process is completed.', 'duplicator'); ?>"></i>

                <div id="dup-pass-toggle">
                </div>
                <br/>
            </td>
        </tr>
    </table>

    <table style="width:100%">
        <tr>
        </tr>
    </table>

    <!-- ===================
    BASIC/CPANEL TABS -->
    <div data-dup-tabs="true">
        <ul>
        </ul>

        <!-- ===================
        TAB1: Basic -->
        <div class="dup-install-prefill-tab-pnl">
            <table class="dup-install-setup">
                <tr>
                </tr>
                <tr>
                    <td>
                        <input 
                            type="text" 
                            name="dbhost"
                            id="dbhost" 
                            maxlength="200" 
                        >
                    </td>
                </tr>
                <tr>
                    <td>
                        <input 
                            type="text" 
                            name="dbport" 
                            id="dbport" 
                            maxlength="200" 
                        >
                    </td>
                </tr>
                <tr>
                    <td>
                        <input 
                            type="text" 
                            name="dbname" 
                            id="dbname" 
                            maxlength="100" 
                        >
                    </td>
                </tr>
                <tr>
                    <td>
                        <input 
                            type="text" 
                            name="dbuser" 
                            id="dbuser" 
                            maxlength="100" 
                        >
                    </td>
                </tr>
                <tr>
                    <td>
                        <input 
                            type="text" 
                            name="dbcharset" 
                            id="dbcharset" 
                            maxlength="100" 
                        >
                    </td>
                </tr>
                <tr>
                    <td>
                        <input 
                            type="text" 
                            name="dbcollation" 
                            id="dbcollation" 
                            maxlength="100" 
                        >
                    </td>
                </tr>
            </table><br />
        </div>
        
        <!-- ===================
        TAB2: cPanel -->
        <div class="dup-install-prefill-tab-pnl">
            <div style="padding:10px 0 0 12px;">
                    </a><br/>
            </div>
        </div>

    </div>


</div>      
</div><br/>


<div class="dup-button-footer">
</div>

</form>

<!-- ==========================================
THICK-BOX DIALOGS: -->
    $confirm1             = new DUP_UI_Dialog();
    $confirm1->title      = __('Reset Backup Settings?', 'duplicator');
    $confirm1->message    = __('This will clear and reset all of the current Backup settings.  Would you like to continue?', 'duplicator');
    $confirm1->jscallback = 'Duplicator.Pack.ResetSettings()';
    $confirm1->initConfirm();

    $default_name1 = DUP_Package::getDefaultName();
    $default_name2 = DUP_Package::getDefaultName(false);
?>
<script>
jQuery(document).ready(function ($) 
{
    var DUP_NAMELAST = $('#package-name').val();

    /* Appends a path to the directory filter */
    Duplicator.Pack.ToggleDBFilters = function () 
    {
        var $filterItems = $('#dup-db-filter-items');
        if ($("#dbfilter-on").is(':checked')) {
            $filterItems.removeAttr('disabled').css({color:'#000'});
            $('#dup-dbtables input').removeAttr('readonly').css({color:'#000'});
            $('#dup-archive-filter-db').show();
            $('div.dup-tbl-scroll label').css({color:'#000'});
        } else {
            $filterItems.attr('disabled', 'disabled').css({color:'#999'});
            $('#dup-dbtables input').attr('readonly', 'readonly').css({color:'#999'});
            $('div.dup-tbl-scroll label').css({color:'#999'});
            $('#dup-archive-filter-db').hide();
        }
    };

    Duplicator.Pack.ConfirmReset = function () 
    {
    }

    Duplicator.Pack.ResetSettings = function () 
    {
        var key = 'duplicator_package_active';
        jQuery('#dup-form-opts-action').val(key);
        jQuery('#dup-form-opts').attr('action', '');
        jQuery('#dup-form-opts').submit();
    }

    Duplicator.Pack.ResetName = function () 
    {
        var current = $('#package-name').val();
        switch (current) {
            case DUP_NAMEDEFAULT1 : $('#package-name').val(DUP_NAMELAST); break;
            case DUP_NAMEDEFAULT2 : $('#package-name').val(DUP_NAMEDEFAULT1); break;
            case DUP_NAMELAST     : $('#package-name').val(DUP_NAMEDEFAULT2); break;
            default:    $('#package-name').val(DUP_NAMELAST);
        }
    }

    Duplicator.Pack.ExcludeTable = function (check) 
    {
        var $cb = $(check);
        if ($cb.is(":checked")) {
            $cb.closest("label").css('textDecoration', 'line-through');
        } else {
            $cb.closest("label").css('textDecoration', 'none');
        }
    }

    Duplicator.Pack.EnableInstallerPassword = function ()
    {
        var $button =  $('#secure-btn');
        if ($('#secure-on').is(':checked')) {
            $('#secure-pass').attr('readonly', false);
            $('#secure-pass').attr('required', 'true').focus();
            $('#dup-installer-secure-lock').show();
            $('#dup-installer-secure-unlock').hide();
            $button.removeAttr('disabled');
        } else {
            $('#secure-pass').removeAttr('required');
            $('#secure-pass').attr('readonly', true);
            $('#dup-installer-secure-lock').hide();
            $('#dup-installer-secure-unlock').show();
            $button.attr('disabled', 'true');
        }
    };

    Duplicator.Pack.ToggleInstallerPassword = function()
    {
        var $input  = $('#secure-pass');
        var $button =  $('#secure-btn');
        if (($input).attr('type') == 'text') {
            $input.attr('type', 'password');
            $button.html('<i class="fas fa-eye fa-sm fa-fw"></i>');
        } else {
            $input.attr('type', 'text');
            $button.html('<i class="fas fa-eye-slash fa-sm fa-fw"></i>');
        }
    }

        $('#dup-pack-archive-panel').show();
        $('#export-onlydb').prop( "checked", true );
    
    //Init:Toggle OptionTabs
    Duplicator.Pack.ToggleDBFilters();
    Duplicator.Pack.EnableInstallerPassword();
    $('input#package-name').focus().select();

});
</script>

} // namespace CharacterGeneratorDev
