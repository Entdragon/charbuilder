<?php

namespace CustomTablesWP\Inc\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

class Admin_Settings
{
    function handle_settings_actions(): void
    {
        $action = common::inputPostCmd('action','','settings');

        if('save-settings'=== $action) {

            //Google Maps
            $GoogleMapAPIKey = common::inputPostString('googlemapapikey','','settings');
            if(get_option('customtables-googlemapapikey')===false)
                add_option('customtables-googlemapapikey', sanitize_text_field($GoogleMapAPIKey));
            else
                update_option('customtables-googlemapapikey', sanitize_text_field($GoogleMapAPIKey));

            //Google Drive
            $GoogleDriveAPIKey = common::inputPostString('googledriveapikey','','settings');
            if(get_option('customtables-googledriveapikey')===false)
                add_option('customtables-googledriveapikey', sanitize_text_field($GoogleDriveAPIKey));
            else
                update_option('customtables-googledriveapikey', sanitize_text_field($GoogleDriveAPIKey));

            $GoogleDriveClientId = common::inputPostString('googledriveclientid','','settings');
            if(get_option('customtables-googledriveclientid')===false)
                add_option('customtables-googledriveclientid', sanitize_text_field($GoogleDriveClientId));
            else
                update_option('customtables-googledriveclientid', sanitize_text_field($GoogleDriveClientId));

            $fieldPrefix = common::inputPostString('fieldprefix','','settings');
            $fieldPrefix = trim(preg_replace("/[^a-zA-Z_\d]/", "_", $fieldPrefix));
            if (empty($fieldPrefix))
                $fieldPrefix = 'ct_';

            if(get_option('customtables-fieldprefix')===false)
                add_option('customtables-fieldprefix', sanitize_text_field($fieldPrefix));
            else
                update_option('customtables-fieldprefix', sanitize_text_field($fieldPrefix));

			//Toolbar Icons
			$toolbarIcons = common::inputPostString('toolbaricons','','settings');
			if(get_option('customtables-toolbaricons')===false)
				add_option('customtables-toolbaricons', sanitize_text_field($toolbarIcons));
			else
				update_option('customtables-toolbaricons', sanitize_text_field($toolbarIcons));


            $url = 'admin.php?page=customtables-settings';

            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}