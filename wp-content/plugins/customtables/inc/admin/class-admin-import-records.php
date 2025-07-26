<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\ImportCSV;
use CustomTables\ImportTables;
use Exception;
use Throwable;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Admin_Import_Records
{
	public function invalid_nonce_redirect()
	{
		$page = common::inputGetCmd('page');
		wp_die(__('Invalid Nonce', 'customtables'),
			__('Error', 'customtables'),
			array(
				'response' => 403,
				'back_link' => esc_url(add_query_arg(array('page' => wp_unslash($page)), admin_url('users.php'))),
			)
		);
	}

	/**
	 * @throws Exception
	 */
	function handle_import_actions()
	{
		// check for individual row actions
		$the_table_action = esc_html(wp_strip_all_tags($this->current_action()));
		if ($the_table_action == 'import-csv') {

			if (!isset($_REQUEST['_wpnonce']) or !wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'import-table')) {
				$this->invalid_nonce_redirect();
				return;
			}

			$tableId = common::inputGetInt('table', 0);
			if ($tableId == 0) {
				common::enqueueMessage(esc_html__('Table not selected or not found.', 'customtables'));
				return;
			}

			if (isset($_POST['upload_file'])) {
				$uploaded_file = $_FILES['filetosubmit'];

				//To allow txt file
				define('ALLOW_UNFILTERED_UPLOADS', true);

				// Define allowed MIME types
				$allowed_mime_types = array(
					'text/plain' // Allow only .txt files
				);

				// Set up the upload overrides
				$upload_overrides = array(
					'test_form' => false,
					'mimes' => $allowed_mime_types
				);

				// Sanitize the uploaded file using wp_handle_upload()
				$move_file = wp_handle_upload($uploaded_file, $upload_overrides);

				if ($move_file && !isset($move_file['error'])) {
					$importFields = common::inputPostInt('importfields', 0, 'import-table');
					$importLayouts = common::inputPostInt('importlayouts', 0, 'import-table');
					$importMenu = false;//common::inputPostInt('importmenu', 0,'import-table');

					require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR
						. 'helpers' . DIRECTORY_SEPARATOR . 'ImportCSV.php');

					try {
						ImportCSV::importCSVFile($move_file['file'], $tableId);
						common::enqueueMessage( 'The records have been added from the CSV file successfully.', 'notice');

						$url = 'admin.php?page=customtables-records&table=' . $tableId;
						unlink($move_file['file']);
						ob_start(); // Start output buffering
						ob_end_clean(); // Discard the output buffer
						wp_redirect(admin_url($url));
						exit;
					} catch (Throwable $e) {
						unlink($move_file['file']);
						common::enqueueMessage( 'Error processing file: ' . esc_html($e->getMessage()), 'error');
					}

				} else {
					// Store a message for 60 seconds
					if (isset($move_file['error']))
						common::enqueueMessage('Error uploading file: ' . esc_html($move_file['error']));
					else
						common::enqueueMessage( 'Unknown error while uploading file');
				}
			}
		}
	}

	public function current_action(): string
	{
		if (isset($_POST['action']))
			return esc_html(wp_strip_all_tags($_POST['action']));

		return '';
	}
}