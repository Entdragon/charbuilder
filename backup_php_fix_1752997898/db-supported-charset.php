<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\InstallerLinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $invalidCharsets string[] */
/* @var $invalidCollations string[] */
/* @var $charsetsList string[] */
/* @var $collationsList string[] */
/* @var $usedCharset string */
/* @var $usedCollate string */
/* @var $errorMessage string */



$statusClass = $testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING ? 'green' : 'red';

$dupDatabase          = basename(DUPX_Package::getSqlFilePath());
$dupDatabaseDupFolder = basename(DUPX_INIT) . '/' . $dupDatabase;
$invalidCheckboxTitle = '';
$subTitle             = '';

?>
<div class="sub-title">STATUS</div>
    switch ($testResult) {
        case DUPX_Validation_abstract_item::LV_FAIL:
            ?>
            It is impossible to verify the list of charsets in the database.
            break;
        case DUPX_Validation_abstract_item::LV_HARD_WARNING:
            if (!empty($invalidCharsets) && !empty($invalidCollations)) {
                $invalidCheckboxTitle = '"Legacy Character set" and "Legacy Collation"';
                $subTitle             = 'character set and collation';
            } elseif (!empty($invalidCharsets)) {
                $invalidCheckboxTitle = '"Legacy Character set"';
                $subTitle             = 'character set';
            } elseif (!empty($invalidCollations)) {
                $invalidCheckboxTitle = '"Legacy Collation"';
                $subTitle             = 'collation';
            }
            ?>
            break;
        default:
            ?>
            Character set and Collate test passed! This database supports the required table character sets and collations.
            break;
    }
    ?>
</p>
    <p>
    </p>

<div class="sub-title">DETAILS</div>
<p>
    This test checks to make sure this database can support the character set and collations found in the 
</p>

<table class="validation-charset-list margin-bottom-1">
    <tbody>
        <tr>
            <td colspan="2" >
                <b>Character set list</b>
            </td>
        </tr>
            <tr>
                <td>
                </td>
                <td>
                </td>
            </tr>
        <tr>
            <td colspan="2" >
                <b>Collations list</b></b>
            </td>
        </tr>
            <tr>
                <td>
                </td>
            </tr>
    <tbody>
</table>
    <p>
        This issue happens when a site is moved from an newer version of MySQL to a older version of MySQL. 
        The recommended fix is to update MySQL on this server to support the character set that is failing below. 
        <b>If this is not an option for your host, then you can continue the installation. Invalid values will be replaced with the default values.</b>
        For more details about this issue and other details regarding this issue see the FAQ link below.
    </p>
<p>
    <i>Default charset and setting in current installation</i><br>
<p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        <i class="far fa-file-code"></i> 
            What is Compatibility mode & 'Unknown Collation' errors?
        </a>
    </li>
    <li>
        In case the default charset/collates are not the desired ones you can <b>change the setting</b> in the <b>advanced installation mode</b>.
    </li>
</ul>


} // namespace CharacterGeneratorDev
