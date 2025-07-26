<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
    /*IDE Helper*/
    /* @var $Package DUP_Package */
function _duplicatorGetRootPath()
{
    $txt   = __('Root Path', 'duplicator');
    $root  = duplicator_get_abs_path();
    $sroot = strlen($root) > 50 ? substr($root, 0, 50) . '...' : $root;
    echo "<div title=" . str_replace('\\/', '/', json_encode($root)) . " class='divider'><i class='fa fa-folder-open'></i> {$sroot}</div>";
}

$archive_type_label     =  DUP_Settings::Get('archive_build_mode') == DUP_Archive_Build_Mode::ZipArchive ? "ZipArchive" : "DupArchive";
$archive_type_extension =  DUP_Settings::Get('archive_build_mode') == DUP_Archive_Build_Mode::ZipArchive ? "zip" : "daf";
$duparchive_max_limit   = DUP_Util::readableByteSize(DUPLICATOR_MAX_DUPARCHIVE_SIZE);
$skip_archive_scan      = DUP_Settings::Get('skip_archive_scan');
$dbbuild_mode           = DUP_DB::getBuildMode();

global $wpdb;
?>

<!-- ================================================================
ARCHIVE -->
<div class="details-title">
</div>

<div class="scan-header scan-item-first">
    <i class="fas fa-folder-open"></i>
    
    <div class="scan-header-details">
        <div class="dup-scan-filter-status">
            if ($Package->Archive->ExportOnlyDB) {
                echo '<i class="fa fa-filter fa-sm"></i> ';
                esc_html_e('Database Only', 'duplicator');
            } elseif ($Package->Archive->FilterOn) {
                echo '<i class="fa fa-filter fa-sm"></i> ';
                esc_html_e('Enabled', 'duplicator');
            }
            ?>
        </div>
        <div id="data-arc-size1"></div>
        <i class="fa fa-question-circle data-size-help"
                        . 'database script or any applied filters.  Once complete the Backup size will be smaller than this number.', 'duplicator'); ?>"></i>

    </div>
</div>

if ($Package->Archive->ExportOnlyDB) { ?>
<div class="scan-item ">
    <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
    </div>
    <div class="info">
            . "will not be capable of restoring a full WordPress site, but only the database.  If this is the desired intention then this notice can be ignored.", 'duplicator'); ?>
    </div>
</div>
} elseif ($skip_archive_scan) { ?>
<div class="scan-item ">
    <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
    </div>
    <div class="info">
        <br><br>
    </div>
</div>
} else {
    ?>

<!-- ============
TOTAL SIZE -->
<div class="scan-item">
    <div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
        <div id="data-arc-status-size"></div>
    </div>
    <div class="info" id="scan-itme-file-size">
            _e('Compressing larger sites on <i>some budget hosts</i> may cause timeouts.  ', 'duplicator');
            echo "<i>&nbsp; <a href='javascipt:void(0)' onclick='jQuery(\"#size-more-details\").toggle(100);return false;'>[" . esc_html__('more details...', 'duplicator') . "]</a></i>";
        ?>
        <div id="size-more-details">
            printf(
                _x(
                    'This notice is triggered at [%s] and can be ignored on most hosts.  If during the build process you see a "Host Build Interrupt"'
                    . ' message then this host has strict processing limits.  Below are some options you can take to overcome constraints '
                    . 'set up on this host.',
                    '%s size in bytes',
                    'duplicator'
                ),
                '<b>' . DUP_Util::byteSize(DUPLICATOR_SCAN_SIZE_DEFAULT) . '</b>'
            );
            ?>
            <br/><br/>
            <br/>
            <ul>
                <li>
                    printf(
                        _x(
                            'See the FAQ link to adjust this hosts timeout limits: %1$sWhat can I try for Timeout Issues?%2$s',
                            '%1$s and %2$s are <a> tags',
                            'duplicator'
                        ),
                        '<a href="' . esc_url(LinkManager::getDocUrl('how-to-handle-server-timeout-issues', 'scan3', 'timeout issues')) . '" target="_blank">',
                        '</a>'
                    );
                    ?>
                </li>
                <li>
                    printf(
                        _x(
                            'Consider trying multi-threaded support in %1$sDuplicator Pro%2$s.',
                            '%1$s and %2$s are <a> tags',
                            'duplicator'
                        ),
                        '<a href="' . esc_url(LinkManager::getCampaignUrl(array('utm_medium' => 'package-build-scan', 'utm_content' => 'Multi Threaded Get Pro')))  . '" target="_blank">',
                        '</a>'
                    );
                    ?>
                </li>
            </ul>
        </div>
            __('Files over %1$s are listed below. Larger files such as movies or zipped content can cause timeout issues on some budget hosts.  If you are having '
            . 'issues creating a Backup try excluding the directory paths below or go back to Step 1 and add them.', 'duplicator'),
            DUP_Util::byteSize(DUPLICATOR_SCAN_WARNFILESIZE)
        ); ?>
        <script id="hb-files-large" type="text/x-handlebars-template">
            <div class="container">
                <div class="hdrs">
                    <span style="font-weight:bold">
                    </span>
                    <div class='hdrs-up-down'>
                    </div>
                </div>
                <div class="data">
                    {{#if ARC.FilterInfo.Files.Size}}
                        {{#each ARC.FilterInfo.TreeSize as |directory|}}
                            <div class="directory">
                                <i class="fa fa-caret-right fa-lg dup-nav" onclick="Duplicator.Pack.toggleDirPath(this)"></i> &nbsp;
                                {{#if directory.iscore}}
                                {{else}}
                                    <input type="checkbox" name="dir_paths[]" value="{{directory.dir}}" id="lf_dir_{{@index}}" onclick="Duplicator.Pack.filesOff(this)" />
                                {{/if}}
                                <label for="lf_dir_{{@index}}" title="{{directory.dir}}">
                                    <i class="size">[{{directory.size}}]</i> {{directory.sdir}}/
                                </label> <br/>
                                <div class="files">
                                    {{#each directory.files as |file|}} 
                                        <input type="checkbox" name="file_paths[]" value="{{file.path}}" id="lf_file_{{directory.dir}}-{{@index}}" />
                                        <label for="lf_file_{{directory.dir}}-{{@index}}" title="{{file.path}}">
                                            <i class="size">[{{file.bytes}}]</i>    {{file.name}}
                                        </label> <br/>
                                    {{/each}}
                                </div>
                            </div>
                        {{/each}}
                    {{else}}
                            if (! isset($_GET['retry'])) {
                                _e('No large files found during this scan.', 'duplicator');
                            } else {
                                echo "<div style='color:maroon'>";
                                    _e('No large files found during this scan.  If you\'re having issues building a Backup click the back button and try '
                                    . 'adding a file filter to non-essential files paths like wp-content/uploads.   These excluded files can then '
                                    . 'be manually moved to the new location after you have ran the migration installer.', 'duplicator');
                                echo "</div>";
                            }
                            ?>
                    {{/if}}
                </div>
            </div>


            <div class="apply-btn" style="margin-bottom:5px;float:right">
                <div class="apply-warn">
                </div>
                <button type="button" class="button-small duplicator-quick-filter-btn" disabled="disabled" onclick="Duplicator.Pack.applyFilters(this, 'large')">
                </button>
                    <i class="fa far fa-clipboard" aria-hidden="true"></i>
                </button>
            </div>
            <div style="clear:both"></div>


        </script>
        <div id="hb-files-large-result" class="hb-files-style"></div>
    </div>
</div>

<!-- ======================
ADDON SITES -->
<div id="addonsites-block"  class="scan-item">
    <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
        <div id="data-arc-status-addonsites"></div>
    </div>
    <div class="info">
        <div style="margin-bottom:10px;">
                printf(
                    __(
                        'An "Addon Site" is a separate WordPress site(s) residing in subdirectories within this site. If you confirm these to be separate sites, 
                        then it is recommended that you exclude them by checking the corresponding boxes below and clicking the \'Add Filters & Rescan\' button.  To backup the other sites 
                        install the plugin on the sites needing to be backed-up.',
                        'duplicator'
                    )
                );
            ?>
        </div>
        <script id="hb-addon-sites" type="text/x-handlebars-template">
            <div class="container">
                <div class="hdrs">
                    <span style="font-weight:bold">
                    </span>
                </div>
                <div class="data">
                    {{#if ARC.FilterInfo.Dirs.AddonSites.length}}
                        {{#each ARC.FilterInfo.Dirs.AddonSites as |path|}}
                        <div class="directory">
                            <input type="checkbox" name="dir_paths[]" value="{{path}}" id="as_dir_{{@index}}"/>
                            <label for="as_dir_{{@index}}" title="{{path}}">
                                {{path}}
                            </label>
                        </div>
                        {{/each}}
                    {{else}}
                    {{/if}}
                </div>
            </div>
            <div class="apply-btn">
                <div class="apply-warn">
                </div>
                <button type="button" class="button-small duplicator-quick-filter-btn" disabled="disabled" onclick="Duplicator.Pack.applyFilters(this, 'addon')">
                </button>
            </div>
        </script>
        <div id="hb-addon-sites-result" class="hb-files-style"></div>
    </div>
</div>


<!-- ============
FILE NAME CHECKS -->
<div class="scan-item">
    <div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
        <div id="data-arc-status-names"></div>
    </div>
    <div class="info">
            _e('Unicode and special characters such as "*?><:/\|", can be problematic on some hosts.', 'duplicator');
            esc_html_e('  Only consider using this filter if the Backup build is failing. Select files that are not important to your site or you can migrate manually.', 'duplicator');
            $txt = __('If this environment/system and the system where it will be installed are set up to support Unicode and long paths then these filters can be ignored.  '
                . 'If you run into issues with creating or installing a Backup, then is recommended to filter these paths.', 'duplicator');
        ?>
        <script id="hb-files-utf8" type="text/x-handlebars-template">
            <div class="container">
                <div class="hdrs">
                    <div class='hdrs-up-down'>
                    </div>
                </div>
                <div class="data">
                    {{#if  ARC.FilterInfo.TreeWarning}}
                        {{#each ARC.FilterInfo.TreeWarning as |directory|}}
                            <div class="directory">
                                {{#if directory.count}}
                                    <i class="fa fa-caret-right fa-lg dup-nav" onclick="Duplicator.Pack.toggleDirPath(this)"></i> &nbsp;
                                {{else}}
                                    <i class="empty"></i>
                                {{/if}}
                                        
                                {{#if directory.iscore}}
                                {{else}}        
                                    <input type="checkbox" name="dir_paths[]" value="{{directory.dir}}" id="nc1_dir_{{@index}}" onclick="Duplicator.Pack.filesOff(this)" />
                                {{/if}}
                                
                                <label for="nc1_dir_{{@index}}" title="{{directory.dir}}">
                                    <i class="count">({{directory.count}})</i>
                                    {{directory.sdir}}/
                                </label> <br/>
                                <div class="files">
                                    {{#each directory.files}}
                                        <input type="checkbox" name="file_paths[]" value="{{path}}" id="warn_file_{{directory.dir}}-{{@index}}" />
                                        <label for="warn_file_{{directory.dir}}-{{@index}}" title="{{path}}">
                                            {{name}}
                                        </label> <br/>
                                    {{/each}}
                                </div>
                            </div>
                        {{/each}}
                    {{else}}
                    {{/if}}
                </div>
            </div>
            <div class="apply-btn">
                <div class="apply-warn">
                </div>
                <button type="button" class="button-small duplicator-quick-filter-btn"  disabled="disabled" onclick="Duplicator.Pack.applyFilters(this, 'utf8')">
                </button>
                    <i class="fa far fa-clipboard" aria-hidden="true"></i>
                </button>
            </div>
        </script>
        <div id="hb-files-utf8-result" class="hb-files-style"></div>
    </div>
</div>
<!-- ======================
UNREADABLE FILES -->
<div id="scan-unreadable-items" class="scan-item">
    <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
        <div id="data-arc-status-unreadablefiles"></div>
    </div>
    <div class="info">
        esc_html_e('PHP is unable to read the following items and they will NOT be included in the Backup.  Please work with your host to adjust the permissions or resolve the '
            . 'symbolic-link(s) shown in the lists below.  If these items are not needed then this notice can be ignored.', 'duplicator');
        ?>
        <script id="unreadable-files" type="text/x-handlebars-template">
            <div class="container">
                <div class="data">
                    <div class="directory">
                        {{#if ARC.UnreadableItems}}
                            {{#each ARC.UnreadableItems as |uitem|}}
                                <i class="fa fa-lock fa-xs"></i> {{uitem}} <br/>
                            {{/each}}
                        {{else}}
                        {{/if}}
                    </div>

                    <div class="directory">
                        {{#if  ARC.RecursiveLinks}}
                            {{#each ARC.RecursiveLinks as |link|}}
                                <i class="fa fa-lock fa-xs"></i> {{link}} <br/>
                            {{/each}}
                        {{else}}
                        {{/if}}
                    </div>
                </div>
            </div>
        </script>
        <div id="unreadable-files-result" class="hb-files-style"></div>
    </div>
</div>




<!-- ============
DATABASE -->
<div id="dup-scan-db">
    <div class="scan-header">
        <i class="fas fa-database fa-sm"></i>
        <div class="scan-header-details">
            <div class="dup-scan-filter-status">
                if ($Package->Database->FilterOn) {
                    echo '<i class="fa fa-filter fa-sm"></i> ';
                    esc_html_e('Enabled', 'duplicator');
                }
                ?>
            </div>
            <div id="data-db-size1"></div>
            <i class="fa fa-question-circle data-size-help"
                    . 'The overall size of the database file can impact the final size of the Backup.', 'duplicator'); ?>"></i>


        </div>
    </div>

    <div class="scan-item">
        <div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
            <div id="data-db-status-size"></div>
        </div>
        <div class="info">
                $dup_scan_tbl_total_trigger_size = DUP_Util::byteSize(DUPLICATOR_SCAN_DB_ALL_SIZE) . ' OR ' . number_format(DUPLICATOR_SCAN_DB_ALL_ROWS);
                printf(__('Total size and row counts are approximate values.  The thresholds that trigger notices are %1$s records total for the entire database.  Larger databases '
                    . 'take more time to process.  On some budget hosts that have cpu/memory/timeout limits this may cause issues.', 'duplicator'), $dup_scan_tbl_total_trigger_size);
                echo '<br/><hr size="1" />';

                //TABLE DETAILS
                echo '<b>' . __('TABLE DETAILS:', 'duplicator') . '</b><br/>';
                $dup_scan_tbl_trigger_size = DUP_Util::byteSize(DUPLICATOR_SCAN_DB_TBL_SIZE) . ', ' . number_format(DUPLICATOR_SCAN_DB_TBL_ROWS);
                printf(esc_html__('The notices for tables are %1$s records or names with upper-case characters.  Individual tables will not trigger '
                    . 'a notice message, but can help narrow down issues if they occur later on.', 'duplicator'), $dup_scan_tbl_trigger_size);

                echo '<div id="dup-scan-db-info"><div id="data-db-tablelist"></div></div>';

                //RECOMMENDATIONS
                echo '<br/><hr size="1" />';
                echo '<b>' . esc_html__('RECOMMENDATIONS:', 'duplicator') . '</b><br/>';

                echo '<div style="padding:5px">';
                $lnk = '<a href="maint/repair.php" target="_blank">' . esc_html__('repair and optimization', 'duplicator') . '</a>';
                printf(__('1. Run a %1$s on the table to improve the overall size and performance.', 'duplicator'), $lnk);
                echo '<br/><br/>';
                _e('2. Remove post revisions and stale data from tables.  Tables such as logs, statistical or other non-critical data should be cleared.', 'duplicator');
                echo '<br/><br/>';
                $lnk = '<a href="?page=duplicator-settings&tab=package" target="_blank">' . esc_html__('Enable mysqldump', 'duplicator') . '</a>';
                printf(__('3. %1$s if this host supports the option.', 'duplicator'), $lnk);
                echo '<br/><br/>';
                $lnk = '<a href="http://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_lower_case_table_names" target="_blank">' . esc_html__('lower_case_table_names', 'duplicator') . '</a>';
                printf(__('4. For table name case sensitivity issues either rename the table with lower case characters or be prepared to work with the %1$s system variable setting.', 'duplicator'), $lnk);
                echo '</div>';

                ?>
        </div>
    </div>
    $triggers = $wpdb->get_col("SHOW TRIGGERS", 1);
    if (count($triggers)) { ?>
        <div class="scan-item">
            <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
                <div id="data-arc-status-triggers"></div>
            </div>
            <div class="info">
                <script id="hb-triggers-result" type="text/x-handlebars-template">
                    <div class="container">
                        <div class="data">
                            <span class="color:maroon">
                                   $lnk = '<a href="https://dev.mysql.com/doc/refman/8.0/en/triggers.html" target="_blank">' . esc_html__('triggers', 'duplicator') . '</a>';
                                   printf(__('This database makes use of %1$s which can manually be imported at install time.  Instructions and SQL statement queries will be '
                                       . 'provided at install time for users to execute. No actions need to be performed at this time, this message is simply a notice.', 'duplicator'), $lnk);
                                ?>
                            </span>
                        </div>
                    </div>
                </script>
                <div id="triggers-result"></div>
            </div>
        </div>
    $procQuery  = $wpdb->prepare("SHOW PROCEDURE STATUS WHERE `Db` = %s", $wpdb->dbname);
    $procedures = $wpdb->get_col($procQuery, 1);
    $funcQuery  = $wpdb->prepare("SHOW FUNCTION STATUS WHERE `Db`  = %s", $wpdb->dbname);
    $functions  = $wpdb->get_col($funcQuery, 1);
    if (count($procedures) || count($functions)) { ?>
    <div id="showcreateprocfunc-block"  class="scan-item">
        <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
            <div id="data-arc-status-showcreateprocfunc"></div>
        </div>
        <div class="info">
            <script id="hb-showcreateprocfunc-result" type="text/x-handlebars-template">
                <div class="container">
                    <div class="data">
                        {{#if ARC.Status.showCreateProcFunc}}
                        {{else}}
                        <span style="color: red;">
                            esc_html_e(
                                "The database user for this WordPress site does NOT sufficient permissions to write stored procedures or" .
                                " functions to the sql file of the archive.  Stored procedures will not be added to the sql file.",
                                'duplicator'
                            );
                            ?>
                        </span>
                        {{/if}}
                    </div>
                </div>
            </script>
            <div id="showcreateprocfunc-package-result"></div>
        </div>
    </div>
    
    <!-- ============
    TOTAL SIZE -->
    <div class="data-ll-section scan-header" style="display:none">
        <i class="far fa-file-archive"></i>
        <div class="scan-header-details">

            <div id="data-ll-totalsize"></div>
            <i class="fa fa-question-circle data-size-help"


        </div>
    </div>

    <div class="data-ll-section scan-item" style="display: none">
        <div style="padding: 7px; background-color:#F3B2B7; font-weight: bold ">
            printf(__('The build can\'t continue because the total size of files and the database exceeds the %s limit that can be processed when creating a DupArchive Backup. ', 'duplicator'), $duparchive_max_limit);
        ?>
        </div>
        <div class="info" id="data-ll-status-recommendations">
            echo '<b>';
            $lnk = '<a href="admin.php?page=duplicator-settings&tab=package" target="_blank">' . esc_html__('Backup Engine', 'duplicator') . '</a>';
            printf(esc_html__("The %s is set to create Backups in the 'DupArchive' format.  This custom format is used to overcome budget host constraints."
                    . " With DupArchive, Duplicator is restricted to processing sites up to %s.  To process larger sites, consider these recommendations. ", 'duplicator'), $lnk, $duparchive_max_limit, $duparchive_max_limit);
            echo '</b>';
            echo '<br/><hr size="1" />';

            echo '<b>' . esc_html__('RECOMMENDATIONS:', 'duplicator') . '</b><br/>';
            echo '<div style="padding:5px">';

            $new1_package_url       = ControllersManager::getMenuLink(
                ControllersManager::PACKAGES_SUBMENU_SLUG,
                'new1'
            );
            $new1_package_nonce_url = wp_nonce_url($new1_package_url, 'new1-package');
            $lnk                    = '<a href="' . $new1_package_nonce_url . '">' . esc_html__('Step 1', 'duplicator') . '</a>';
            printf(__('- Add data filters to get the Backup size under %s: ', 'duplicator'), $duparchive_max_limit);
            echo '<div style="padding:0 0 0 20px">';
                _e("- In the 'Size Checks' section above consider adding filters (if notice is shown).", 'duplicator');
                echo '<br/>';
                printf(__("- In %s consider adding file/directory or database table filters.", 'duplicator'), $lnk);
            echo '</div>';
            echo '<br/>';

            printf(
                _x(
                    '- Perform a two part install as %1$sdescribed in the documentation%2$s.',
                    '%1$s and %2$s represent opening and closing anchor tags',
                    'duplicator'
                ),
                '<a href="' . esc_url(LinkManager::getDocUrl('two-part-install', 'scan_step')) . '">',
                '</a>'
            );
            echo '<br/><br/>';

            $lnk = '<a href="admin.php?page=duplicator-settings&tab=package" target="_blank">' . esc_html__('ZipArchive Engine', 'duplicator') . '</a>';
            printf(__("- Switch to the %s which requires a capable hosting provider (VPS recommended).", 'duplicator'), $lnk);
            echo '<br/><br/>';

            $lnk = '<a href="' . esc_url(LinkManager::getCampaignUrl('package-build-scan', 'Backup to big Get Pro')) . '" target="_blank">' . esc_html__('Duplicator Pro', 'duplicator') . '</a>';
            printf(__("- Consider upgrading to %s for unlimited large site support.", 'duplicator'), $lnk);

            echo '</div>';

            ?>
        </div>
    </div>
        <div class="scan-item" id="mysqldump-limit-result"></div>
        <script id="hb-mysqldump-limit-result" type="text/x-handlebars-template">
            <div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
                <div class="text">
                </div>
                <div id="data-db-status-mysqldump-limit">
                    {{#if DB.Status.mysqlDumpMemoryCheck}}
                    {{else}}
                    {{/if}}
                </div>
            </div>
            {{#if DB.Status.mysqlDumpMemoryCheck}}
                <div class="info">
                    <p class="green">
                    </p>
                    printf(
                        _x(
                            'If you encounter any issues with mysqldump please change the setting SQL Mode to PHP Code.'
                            . ' You can do that by opening %1$sDuplicator Pro > Settings > Backups.%2$s',
                            '1$s and 2$s represent opening and closing anchor tags',
                            'duplicator'
                        ),
                        '<a href="?page=duplicator-settings&tab=package" target="_blank">',
                        '</a>'
                    );
                    ?>
                </div>
            {{else}}
                <div class="info" style="display:block;">
                    <p class="red">
                    </p>
                    esc_html_e(
                        'The database size is larger than the PHP memory_limit value.'
                        . ' This can lead into issues when building a Backup, during which the system can run out of memory.'
                        . ' To fix this issue please consider doing one of the below mentioned recommendations.',
                        'duplicator'
                    );
                    ?>
                    <hr size="1" />
                    <p>
                    </p>
                    <ul class="dup-pro-simple-style-disc" >
                        <li>
                                printf(
                                    _x(
                                        'Please change the setting SQL Mode to PHP Code.'
                                        . ' You can do that by opening %1$sDuplicator Pro > Settings > Backups.%2$s',
                                        '%1$s and %2$s represent opening and closing anchor tags',
                                        'duplicator'
                                    ),
                                    '<a href="?page=duplicator-settings&tab=package" target="_blank">',
                                    '</a>'
                                );
                            ?>
                        </li>
                        <li>
                                printf(
                                    _x(
                                        'If you want to build the Backup with mysqldump, increase the PHP <b>memory_limit</b> ' .
                                        'value in your php.ini file to at least %1$s.',
                                        '%1$s represents the memory limit value (e.g. 256MB)',
                                        'duplicator'
                                    ),
                                    '<b><span id="data-db-size3">{{DB.Status.requiredMysqlDumpLimit}}</span></b>'
                                );
                            ?>
                        </li>
                    </ul>
                </div>
            {{/if}}
        </script>

        echo '<div class="dup-pro-support">&nbsp;';
        esc_html_e('Migrate large, multi-gig sites with', 'duplicator');
        echo '&nbsp;<i><a href="' .  esc_url(LinkManager::getCampaignUrl('package-build-scan', 'Multi Gig Backup Get Pro')) . '" target="_blank">' . esc_html__('Duplicator Pro', 'duplicator') . '!</a></i>';
        echo '</div>';
    ?>
</div>
<br/><br/>


<!-- ==========================================
DIALOGS:
========================================== -->
    $alert1          = new DUP_UI_Dialog();
    $alert1->height  = 600;
    $alert1->width   = 600;
    $alert1->title   = __('Scan Details', 'duplicator');
    $alert1->message = "<div id='arc-details-dlg'></div>";
    $alert1->initAlert();

    $alert2          = new DUP_UI_Dialog();
    $alert2->height  = 450;
    $alert2->width   = 650;
    $alert2->title   = __('Copy Quick Filter Paths', 'duplicator');
    $alert2->message = "<div id='arc-paths-dlg'></div>";
    $alert2->initAlert();
?>

<!-- =======================
DIALOG: Scan Results -->
<div id="dup-archive-details" style="display:none">
    
    <!-- PACKAGE -->
    <br/><br/>

    <!-- DATABASE -->
    <table id="db-area">
        <tr>
            <td style="line-height:18px">
                    <br/>
                    <small style="font-style:italic; color:maroon">
                    </small>
            </td>
        </tr>
    </table><br/>

    <!-- FILE FILTERS -->
    <h2 style="border: none">
    </h2>
    <div class="filter-area">

        <script id="hb-filter-file-list" type="text/x-handlebars-template">
            <div class="file-info">
                <div class="file-info">
                    {{#if ARC.FilterInfo.Dirs.Instance}}
                        {{#each ARC.FilterInfo.Dirs.Instance as |dir|}}
                            {{stripWPRoot dir}}/<br/>
                        {{/each}}
                    {{else}}
                    {{/if}}
                </div>

                <div class="file-info">
                    if (strlen($Package->Archive->FilterExts)) {
                        echo esc_html($Package->Archive->FilterExts);
                    } else {
                        _e('No file extension filters have been set.', 'duplicator');
                    }
                    ?>
                </div>

                <div class="file-info">
                    {{#if ARC.FilterInfo.Files.Instance}}
                        {{#each ARC.FilterInfo.Files.Instance as |file|}}
                            {{stripWPRoot file}}<br/>
                        {{/each}}
                    {{else}}
                    {{/if}}
                </div>

                <div class="file-info">
                    {{#each ARC.FilterInfo.Dirs.Core as |dir|}}
                        {{stripWPRoot dir}}/<br/>
                    {{/each}}
                    <br/>
                    {{#each ARC.FilterInfo.Files.Global as |file|}}
                        {{stripWPRoot file}}<br/>
                    {{/each}}
                </div>

            </div>
        </script>
        <div class="hb-filter-file-list-result"></div>

    </div>

    <small>
        </a>
        <br/>
    </small><br/>
</div>

<!-- =======================
DIALOG: PATHS COPY & PASTE -->
<div id="dup-archive-paths" style="display:none">
    
    <div class="copy-button">
        <button type="button" class="button-small" onclick="Duplicator.Pack.copyText(this, '#arc-paths-dlg textarea.path-dirs')">
        </button>
    </div>
    <textarea class="path-dirs"></textarea>
    <br/><br/>

    <div class="copy-button">
        <button type="button" class="button-small" onclick="Duplicator.Pack.copyText(this, '#arc-paths-dlg textarea.path-files')">
        </button>
    </div>
    <textarea class="path-files"></textarea>
    <br/>
</div>


<script>
jQuery(document).ready(function($)
{

    Handlebars.registerHelper('stripWPRoot', function(path) {
    });

    //Uncheck file names if directory is checked
    Duplicator.Pack.filesOff = function (dir)
    {
        var $checks = $(dir).parent('div.directory').find('div.files input[type="checkbox"]');
        $(dir).is(':checked')
            : $.each($checks, function() {$(this).removeAttr('disabled checked title');});
        $('div.apply-warn').show(300);
    }

    //Opens a dialog to show scan details
    Duplicator.Pack.showDetailsDlg = function ()
    {
        $('#arc-details-dlg').html($('#dup-archive-details').html());
        Duplicator.UI.loadQtip();
        return;
    }
    
    //Opens a dialog to show scan details
    Duplicator.Pack.showPathsDlg = function (type)
    {
        var id = (type == 'large') ? '#hb-files-large-result' : '#hb-files-utf8-result'
        var dirFilters  = [];
        var fileFilters = [];
        $(id + " input[name='dir_paths[]']:checked").each(function()  {dirFilters.push($(this).val());});
        $(id + " input[name='file_paths[]']:checked").each(function() {fileFilters.push($(this).val());});

        var $dirs  = $('#dup-archive-paths textarea.path-dirs');
        var $files = $('#dup-archive-paths textarea.path-files');
        (dirFilters.length > 0)
           ? $dirs.text(dirFilters.join(";\n"))

        (fileFilters.length > 0)
           ? $files.text(fileFilters.join(";\n"))

        $('#arc-paths-dlg').html($('#dup-archive-paths').html());
        
        return;
    }

    //Toggles a directory path to show files
    Duplicator.Pack.toggleDirPath = function(item)
    {
        var $dir   = $(item).parents('div.directory');
        var $files = $dir.find('div.files');
        var $arrow = $dir.find('i.dup-nav');
        if ($files.is(":hidden")) {
            $arrow.addClass('fa-caret-down').removeClass('fa-caret-right');
            $files.show();
        } else {
            $arrow.addClass('fa-caret-right').removeClass('fa-caret-down');
            $files.hide(250);
        }
    }

    //Toggles a directory path to show files
    Duplicator.Pack.toggleAllDirPath = function(item, toggle)
    {
        var $dirs  = $(item).parents('div.container').find('div.data div.directory');
         (toggle == 'hide')
            ? $.each($dirs, function() {$(this).find('div.files').show(); $(this).find('i.dup-nav').trigger('click');})
            : $.each($dirs, function() {$(this).find('div.files').hide(); $(this).find('i.dup-nav').trigger('click');});
    }

    Duplicator.Pack.copyText = function(btn, query)
    {
        $(query).select();
         try {
           document.execCommand('copy');
           $(btn).css({color: '#fff', backgroundColor: 'green'});
         } catch(err) {
         }
    }

    Duplicator.Pack.applyFilters = function(btn, type)
    {
        var $btn = $(btn);
        $btn.attr('disabled', 'true');

        //var id = (type == 'large') ? '#hb-files-large-result' : '#hb-files-utf8-result'
        var id = '';
        switch(type){
            case 'large':
                id = '#hb-files-large-result';
                break;
            case 'utf8':
                id = '#hb-files-utf8-result';
                break;
            case 'addon':
                id = '#hb-addon-sites-result';
                break;
        }
        var dirFilters  = [];
        var fileFilters = [];
        $(id + " input[name='dir_paths[]']:checked").each(function()  {dirFilters.push($(this).val());});
        $(id + " input[name='file_paths[]']:checked").each(function() {fileFilters.push($(this).val());});

        var data = {
            action: 'DUP_CTRL_Package_addQuickFilters',
            dir_paths : dirFilters.join(";"),
            file_paths : fileFilters.join(";"),
        };

        $.ajax({
            type: "POST",
            cache: false,
            dataType: "text",
            url: ajaxurl,
            timeout: 100000,
            data: data,
            complete: function() { },
            success:  function(respData) {
                try {
                    var data = Duplicator.parseJSON(respData);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    console.log(data);
                    return false;
                }
                Duplicator.Pack.rescan();
            },
            error: function(data) {
                console.log(data);
            }
        });
    }

    Duplicator.Pack.initArchiveFilesData = function(data)
    {
        //TOTAL SIZE
        //var sizeChecks = data.ARC.Status.Size == 'Warn' || data.ARC.Status.Big == 'Warn' ? 'Warn' : 'Good';
        $('#data-arc-status-size').html(Duplicator.Pack.setScanStatus(data.ARC.Status.Size));
        $('#data-arc-status-names').html(Duplicator.Pack.setScanStatus(data.ARC.Status.Names));
        $('#data-arc-status-unreadablefiles').html(Duplicator.Pack.setScanStatus(data.ARC.Status.UnreadableItems));
        $('#data-arc-status-triggers').html(Duplicator.Pack.setScanStatus(data.DB.Status.Triggers));
        
        $('#data-arc-status-migratepackage').html(Duplicator.Pack.setScanStatus(data.ARC.Status.MigratePackage));
+        $('#data-arc-status-showcreateprocfunc').html(Duplicator.Pack.setScanStatus(data.ARC.Status.showCreateProcFuncStatus));
        $('#data-arc-size1').text(data.ARC.Size || errMsg);
        $('#data-arc-size2').text(data.ARC.Size || errMsg);
        $('#data-arc-files').text(data.ARC.FileCount || errMsg);
        $('#data-arc-dirs').text(data.ARC.DirCount || errMsg);

        //LARGE FILES
        if ($('#hb-files-large').length > 0) {
            var template = $('#hb-files-large').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#hb-files-large-result').html(html);
        }
        //ADDON SITES
        if ($('#hb-addon-sites').length > 0) {
            var template = $('#hb-addon-sites').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#hb-addon-sites-result').html(html);
        }
        //NAME CHECKS
        if ($('#hb-files-utf8').length > 0) {
            var template = $('#hb-files-utf8').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#hb-files-utf8-result').html(html);
        }

        //NAME CHECKS
        if ($('#unreadable-files').length > 0) {
            var template = $('#unreadable-files').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#unreadable-files-result').html(html);
        }

        //SCANNER DETAILS: Dirs
        if ($('#hb-filter-file-list').length > 0) {
            var template = $('#hb-filter-file-list').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('div.hb-filter-file-list-result').html(html);
        }

        //MIGRATE PACKAGE
        if ($("#hb-migrate-package-result").length) {
            var template = $('#hb-migrate-package-result').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#migrate-package-result').html(html);
        }

        //SHOW CREATE
        if ($("#hb-showcreateprocfunc-result").length) {
            var template = $('#hb-showcreateprocfunc-result').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#showcreateprocfunc-package-result').html(html);
        }

        //TRIGGERS
        if ($("#hb-triggers-result").length) {
            var template = $('#hb-triggers-result').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#triggers-result').html(html);
        }

        //MYSQLDUMP LIMIT
        if ($("#hb-mysqldump-limit-result").length) {
            var template = $('#hb-mysqldump-limit-result').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#mysqldump-limit-result').html(html);
        }

        Duplicator.UI.loadQtip();
    }

    Duplicator.Pack.initArchiveDBData = function(data)
    {
        var errMsg = "unable to read";
        var color;
        var html = "";
        var DB_TotalSize = 'Good';
        if (data.DB.Status.Success)
        {
            DB_TotalSize = data.DB.Status.DB_Rows == 'Warn' || data.DB.Status.DB_Size == 'Warn' ? 'Warn' : 'Good';
            $('#data-db-status-size').html(Duplicator.Pack.setScanStatus(DB_TotalSize));
            $('#data-db-size1').text(data.DB.EasySize || errMsg);
            $('#data-db-size2').text(data.DB.EasySize || errMsg);
            $('#data-db-rows').text(data.DB.Rows || errMsg);
            $('#data-db-tablecount').text(data.DB.TableCount || errMsg);
            //Table Details
            if (data.DB.TableList == undefined || data.DB.TableList.length == 0) {
            } else {
                $.each(data.DB.TableList, function(i) {
                    html += '<b>' + i  + '</b><br/>';
                    html += '<table><tr>';
                    $.each(data.DB.TableList[i], function(key,val) {
                        switch(key) {
                            case 'Case':
                                color = (val == 1) ? 'red' : 'black';
                                break;
                            case 'Rows':
                                color = (val > DB_TableRowMax) ? 'red' : 'black';
                                break;
                            case 'USize':
                                color = (parseInt(val) > DB_TableSizeMax) ? 'red' : 'black';
                                break;
                        }   
                    });
                    html += '</tr></table>';
                });
            }
            $('#data-db-tablelist').html(html);
        } else {
            $('#dup-scan-db').html(html);
        }
    }

    Duplicator.Pack.initLiteLimitData = function(data)
    {       
        if(data.LL.Status.TotalSize == 'Fail') {
            $('.data-ll-section').show();
            $('#dup-build-button').hide();
            $('#dup-scan-warning-continue').hide();
            //$('#data-ll-status-totalsize').html(Duplicator.Pack.setScanStatus(data.LL.Status.TotalSize));
            $('#data-ll-totalsize').text(data.LL.TotalSize || errMsg);
            $('.dup-pro-support').hide();
        } else {
           // $('#dup-scan-warning-continue').show();
            $('#dup-build-button').show();
           // $('#dup-build-button').prop("disabled",true);
            $('.data-ll-section').hide();
        }
    }

    if (isset($_GET['retry']) && $_GET['retry'] == '1') {
        echo "$('#scan-itme-file-size').show(300)";
    }
    ?>

    // alert('before binding ' + $("#form-duplicator").length);
    $("#form-duplicator").on('change', "#hb-files-large-result input[type='checkbox'], #hb-files-utf8-result input[type='checkbox'], #hb-addon-sites-result input[type='checkbox']", function() {
        if ($("#hb-files-large-result input[type='checkbox']:checked").length) {
            var large_disabled_prop = false;
        } else {
            var large_disabled_prop = true;
        }
        $("#hb-files-large-result .duplicator-quick-filter-btn").prop("disabled", large_disabled_prop);
        
        if ($("#hb-files-utf8-result input[type='checkbox']:checked").length) {
            var utf8_disabled_prop = false;
        } else {
            var utf8_disabled_prop = true;
        }
        $("#hb-files-utf8-result .duplicator-quick-filter-btn").prop("disabled", utf8_disabled_prop);
        
        if ($("#hb-addon-sites-result input[type='checkbox']:checked").length) {
            var addon_disabled_prop = false;
        } else {
            var addon_disabled_prop = true;
        }
        $("#hb-addon-sites-result .duplicator-quick-filter-btn").prop("disabled", addon_disabled_prop);         
    });
});
</script>

} // namespace CharacterGeneratorDev
