<?php

namespace CustomTablesWP\Inc\Core;

use Exception;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Activator {

	/**
	 * @since    1.0.0
	 */
	public static function activate() {

		$min_php = '7.4.0';

		// Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
		if ( version_compare( PHP_VERSION, $min_php, '<' ) ) {
					deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'This plugin requires a minimum PHP Version of ' . $min_php );
		}

        // Set initial version if not already set
        update_option('customtables_version', \CustomTablesWP\PLUGIN_VERSION);
	}

    public static function update() {
        $installed_version = get_option('customtables_version') ?? '1.0.0'; // Get stored version
        $current_version = \CustomTablesWP\PLUGIN_VERSION;  // Get current version from your constant

        if($installed_version != $current_version) {
            update_option('customtables_version', \CustomTablesWP\PLUGIN_VERSION);
            self::setFieldPrefix();
        }
    }

    private static function setFieldPrefix(): bool {
        global $wpdb;

        try {
            // Get list of CustomTables tables
            $tables = $wpdb->get_col($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $wpdb->prefix . "customtables_table_%"
            ));

            foreach ($tables as $table) {
                // Get all columns for the current table
                $columns = array();
                $col_results = $wpdb->get_results("SHOW COLUMNS FROM {$table}");

                foreach ($col_results as $col) {
                    if ($col->Field !== 'id' && $col->Field !== 'published') {
                        $columns[$col->Field] = $col->Type;
                    }
                }

                // If there are any custom fields
                if (!empty($columns)) {
                    $firstField = array_key_first($columns);

                    // Extract prefix (everything before the first underscore)
                    if (strpos($firstField, '_') !== false) {
                        $prefix = substr($firstField, 0, strpos($firstField, '_') + 1);

                        // Verify all other custom fields use the same prefix
                        $prefixValid = true;
                        foreach ($columns as $fieldName => $fieldType) {
                            if (!str_starts_with($fieldName, $prefix)) {
                                $prefixValid = false;
                                break;
                            }
                        }

                        if ($prefixValid) {
                            // Store table name and its prefix
                            $tableName = str_replace($wpdb->prefix . 'customtables_table_', '', $table);

                            $wpdb->update(
                                $wpdb->prefix . 'customtables_tables',
                                array('customfieldprefix' => $prefix),
                                array(
                                    'tablename' => $tableName,
                                    'customfieldprefix' => null
                                ),
                                array('%s'),
                                array('%s', null)
                            );
                        }
                    }
                }
            }
            return true;

        } catch (Exception $e) {
            echo 'CustomTables setFieldPrefix error: ' . $e->getMessage().'<br/>';

            error_log('CustomTables setFieldPrefix error: ' . $e->getMessage());
            return false;
        }
    }
}

// PHP 8 polyfill for str_starts_with if needed
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle): bool
	{
        return strpos($haystack, $needle) === 0;
    }
}