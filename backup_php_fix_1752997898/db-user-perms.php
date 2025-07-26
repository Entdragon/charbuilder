<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\InstallerLinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int */
/* @var $dbuser string */
/* @var $dbname string */
/* @var $perms array */
/* @var $errorMessages string[] */

$statusClass = $testResult == DUPX_Validation_test_db_user_perms::LV_PASS ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
    switch ($testResult) {
        case DUPX_Validation_test_db_user_perms::LV_PASS:
            ?>
            break;
        case DUPX_Validation_test_db_user_perms::LV_FAIL:
            ?>        
            break;
        case DUPX_Validation_test_db_user_perms::LV_HARD_WARNING:
            ?>        
            You can continue with the installation but some features may not be restored correctly.
            break;
    }
    ?>
</p>
    <p>
        Error detail: <br>
    </p>

<div class="sub-title">DETAILS</div>
<p>
    This test checks the privileges of the current database user.  In order to successfully use Duplicator all of the privileges should pass.
    In the event the checks below   fail, contact your hosting provider to make sure the database user has the correct permissions listed below.
    <br/><br/>

    <i>
        Note:  In some cases "Create Views, Procedures, Functions and Triggers" will not pass, but continuing with the install will still work.
        It is however recommended that a green pass on all permissions is set when possible.  Please work with your hosting provider to get all
        values to pass.
    </i>
</p><br/>


<table class="s1-validate-sub-status">
    <tr>
        <td>Create</td>
    </tr>
    <tr>
        <td>Select</td>
    </tr>
    <tr>
        <td>Insert</td>
    </tr>
    <tr>
        <td>Update</td>
    </tr>
    <tr>
        <td>Delete</td>
    </tr>
    <tr>
        <td>Drop</td>
    </tr>
        <tr>
            <td>Create Views</td>
        </tr>

    <tr>
        <td>Procedures <small>(Create &amp; Alter)</small> </td>
    </tr>

    <tr>
        <td>Functions <small>(Create &amp; Alter)</small>  </td>
    </tr>

    <tr>
        <td>Trigger</td>
    </tr>
</table><br/>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Validate that the database user is correct per your hosts documentation</li>
    <li>
        Check to make sure the 'User' has been granted the correct privileges
        <ul class='vids'>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href='https://www.youtube.com/watch?v=UU9WCC_-8aI' target='_video'>How to grant user privileges in cPanel</a>
            </li>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=FfX-B-h3vo0" target="_video">How to grant user privileges in phpMyAdmin</a>
            </li>
        </ul>
    </li>
    <li>
        <a
            target="_help"
            title="I'm running into issues with the Database what can I do?"
        >
            [Additional FAQ Help]
        </a>
    </li>
</ul>

} // namespace CharacterGeneratorDev
