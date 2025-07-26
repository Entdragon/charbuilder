<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */

namespace CustomTablesWP\Inc\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTables\Layouts;
use CustomTables\MySQLWhereClause;
use Exception;
use Throwable;

class Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private string $version;

	/**
	 * WP_List_Table object
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      admin_table_list $admin_list_table
	 */
	private admin_table_list $admin_table_list;
	private $admin_table_edit;
	private $admin_field_list;
	private $admin_field_edit;
	private object $admin_record_list;
	private $admin_record_edit;
	private $admin_layout_list;
	private $admin_layout_edit;
	private $admin_import_tables;

	private $admin_import_records;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 * @since    1.0.0
	 */
	public function __construct(string $plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('init', array($this, 'my_load_plugin_textdomain'));
	}

	function my_load_plugin_textdomain(): void
	{
		$domain = 'customtables';
		$mo_file = ABSPATH . 'wp-content/plugins/customtables/Languages/' . $domain . '-' . get_locale() . '.mo';

		load_textdomain($domain, $mo_file);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles(): void
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/customtables-admin.css', array(), $this->version);
		wp_enqueue_style('fieldtypes', plugin_dir_url(__FILE__) . '../../libraries/customtables/media/css/fieldtypes.css', false, $this->version);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void
	{
		wp_enqueue_script('nds_ajax_handle', plugin_dir_url(__FILE__) . 'js/customtables-admin.js', array(), $this->version, false);

		$page = common::inputGetCmd('page');

		if ($page == 'customtables-schema') {
			wp_enqueue_script('customtables-js-raphael', home_url() . '/wp-content/plugins/customtables/assets/raphael.min.js', array('jquery'), $this->version, false);
			wp_enqueue_script('customtables-js-diagram', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/diagram.js', array('jquery'), $this->version, false);
		}

		if ($page == 'customtables-fields-edit') {
			wp_enqueue_script('customtables-js-ajax', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/ajax.js', array(), $this->version, false);
			wp_enqueue_script('customtables-js-typeparams_common', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/typeparams_common.js', array(), $this->version, false);
			wp_enqueue_script('customtables-js-typeparams_j4', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/typeparams_j4.js', array(), $this->version, false);
		}

		if ($page == 'customtables-layouts-edit') {
			wp_enqueue_script('customtables-js-ajax', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/ajax.js', array(), $this->version, false);
			wp_enqueue_script('customtables-js-typeparams_common', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/typeparams_common.js', array(), $this->version, false);
			wp_enqueue_script('customtables-js-typeparams_j4', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/typeparams_j4.js', array(), $this->version, false);
		}

		//if ($page == 'customtables-records' or $page == 'customtables-records-edit') {
		//wp_add_inline_script('ct-edit-form-script', 'let ctWebsiteRoot = "' . esc_url(home_url()) . '";');
		//}

		$ctWebsiteRoot = home_url();
		if ($ctWebsiteRoot !== '' and $ctWebsiteRoot[strlen($ctWebsiteRoot) - 1] !== '/')
			$ctWebsiteRoot .= '/';

		if ($page == 'customtables-records-edit') {

			// Add inline script after enqueuing the main script
			wp_enqueue_script('ct-catalog-ajax', CUSTOMTABLES_MEDIA_WEBPATH . 'js/ajax.js', array(), \CustomTablesWP\PLUGIN_VERSION);
			wp_enqueue_script('ct-catalog-base64', CUSTOMTABLES_MEDIA_WEBPATH . 'js/base64.js', array(), \CustomTablesWP\PLUGIN_VERSION);
			wp_enqueue_script('ct-edit-form-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/edit.js', array(), \CustomTablesWP\PLUGIN_VERSION);


			wp_add_inline_script('ct-edit-form-script', 'let ctWebsiteRoot = "' . esc_url($ctWebsiteRoot) . '";');
			wp_add_inline_script('ct-edit-form-script', 'let gmapdata = [];');
			wp_add_inline_script('ct-edit-form-script', 'let gmapmarker = [];');

			$wp_version_decimal = floatval(substr(get_bloginfo('version'), 0, 3));
			wp_add_inline_script('ct-edit-form-script', 'const CTEditHelper = new CustomTablesEdit("WordPress",' . $wp_version_decimal . ');');

			wp_enqueue_script('jquery');

			// Enqueue jQuery UI
			wp_enqueue_script('jquery-ui-core');

			$filePath = CUSTOMTABLES_PRO_PATH . 'js' . DIRECTORY_SEPARATOR . 'jquery.uploadfile.js';

			if (file_exists($filePath))
				wp_enqueue_script('ct-uploadfile-script', home_url() . '/wp-content/plugins/customtablespro/js/jquery.uploadfile.js', array(), \CustomTablesWP\PLUGIN_VERSION);

			wp_enqueue_script('ct-uploader-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/uploader.js', array(), \CustomTablesWP\PLUGIN_VERSION);


			wp_enqueue_script('ct-edit-form-script-jquery-ui-min', \CustomTablesWP\PLUGIN_NAME_URL . 'assets/jquery-ui.min.js', array(), $this->version);
			wp_enqueue_style('ct-edit-form-style-jquery-timepicker', \CustomTablesWP\PLUGIN_NAME_URL . 'assets/jquery.datetimepicker.min.css', array(), $this->version);

			//Include jQuery UI Timepicker addon from CDN
			wp_enqueue_script('ct-edit-form-script-jquery-timepicker', \CustomTablesWP\PLUGIN_NAME_URL . 'assets/jquery.datetimepicker.full.min.js', array(), $this->version);

			//Color Field Type
			wp_enqueue_script('ct-spectrum-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/spectrum.js', array(), \CustomTablesWP\PLUGIN_VERSION, true);
			wp_enqueue_style('ct-spectrum-style', CUSTOMTABLES_MEDIA_WEBPATH . 'css/spectrum.css', array(), \CustomTablesWP\PLUGIN_VERSION, false);

			wp_enqueue_style('ct-edit-form-style', CUSTOMTABLES_MEDIA_WEBPATH . 'css/style.css', array(), \CustomTablesWP\PLUGIN_VERSION, false);
		}

		if ($page == 'customtables-records') {

			wp_enqueue_script('ct-edit-form-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/edit.js', array(), \CustomTablesWP\PLUGIN_VERSION);
			wp_add_inline_script('ct-edit-form-script', 'let ctWebsiteRoot = "' . esc_url($ctWebsiteRoot) . '";');
			wp_add_inline_script('ct-edit-form-script', 'let gmapdata = [];');
			wp_add_inline_script('ct-edit-form-script', 'let gmapmarker = [];');

			$wp_version_decimal = floatval(substr(get_bloginfo('version'), 0, 3));
			wp_add_inline_script('ct-edit-form-script', 'const CTEditHelper = new CustomTablesEdit("WordPress",' . $wp_version_decimal . ');');

			wp_enqueue_script('ct-catalog-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/catalog.js', array(), \CustomTablesWP\PLUGIN_VERSION, false);
		}

		//Google Map Coordinates
		//if (isset($this->enqueueList['fieldtype:googlemapcoordinates']) and $this->enqueueList['fieldtype:googlemapcoordinates']) {

		$googleMapAPIKey = get_option('customtables-googlemapapikey') ?? '';
		if ($googleMapAPIKey != '')
			wp_enqueue_script('ct-google-map-script', 'https://maps.google.com/maps/api/js?key=' . $googleMapAPIKey . '&sensor=false', array(), \CustomTablesWP\PLUGIN_VERSION, true);
		//}

		//Google Drive
		//if (isset($this->enqueueList['fieldtype:file']) and $this->enqueueList['fieldtype:file']) {

		$GoogleDriveAPIKey = get_option('customtables-googledriveapikey') ?? '';
		$GoogleDriveClientId = get_option('customtables-googledriveclientid') ?? '';

		if ($GoogleDriveAPIKey != '' and $GoogleDriveClientId != '') {
			wp_enqueue_script('ct-google-api', 'https://apis.google.com/js/api.js', array(), \CustomTablesWP\PLUGIN_VERSION, true);
			wp_enqueue_script('ct-google-gsi-client', 'https://accounts.google.com/gsi/client', array(), \CustomTablesWP\PLUGIN_VERSION, true);
		}
		//}

		wp_localize_script(
			'ct-edit-form-script',
			'ctTranslationScriptObject',
			common::getLocalizeScriptArray()
		);
	}

	/**
	 * Callback for the user sub-menu in define_admin_hooks() for class Init.
	 *
	 * @throws Exception
	 * @since    1.1.0
	 */
	public function add_plugin_admin_menu(): void
	{
		// Get the custom tables icon
		$icon = $this->getCustomTablesIcon();

		// Dashboard
		add_menu_page(
			'Dashboard - CustomTables ', // Page Title
			'Custom Tables',             // Menu Title
			'manage_options',            // Capability
			'customtables',               // Menu Slug
			array($this, 'load_customtablesAdminDashboard'), // Callback Function
			$icon                         // Icon URL
		);

		// Dashboard Submenu
		add_submenu_page(
			'customtables',          // Parent Menu Slug
			'Dashboard - CustomTables', // Page Title
			'Dashboard',              // Menu Title
			'manage_options',         // Capability
			'customtables',           // Menu Slug
			array($this, 'load_customtablesAdminDashboard'), // Callback Function
			1                          // Position
		);

		// Tables
		$page_hook = add_submenu_page(
			'customtables',                    // Parent Menu Slug
			esc_html__('Tables - CustomTables', 'customtables'), // Page Title
			esc_html__('Tables', 'customtables'),                // Menu Title
			'manage_options',                                      // Capability
			'customtables-tables',                                 // Menu Slug
			array($this, 'load_admin_table_list'),                  // Callback Function
			2                                                      // Position
		);
		add_action('load-' . $page_hook, array($this, 'preload_admin_table_list'));

		// Layouts
		$page_hook = add_submenu_page(
			'customtables',                     // Parent Menu Slug
			'Custom Tables - Layouts',          // Page Title
			'Layouts',                          // Menu Title
			'manage_options',                   // Capability
			'customtables-layouts',             // Menu Slug
			array($this, 'load_admin_layout_list'), // Callback Function
			3                                   // Position
		);
		add_action('load-' . $page_hook, array($this, 'preload_admin_layout_list'));

		// Import Tables
		$page_hook = add_submenu_page(
			'customtables',                       // Parent Menu Slug
			'Custom Tables - Import Tables',       // Page Title
			'Import Tables',                       // Menu Title
			'manage_options',                      // Capability
			'customtables-import-tables',          // Menu Slug
			array($this, 'load_customtablesAdminImportTables'), // Callback Function
			4                                       // Position
		);
		add_action('load-' . $page_hook, array($this, 'preload_admin_import_tables'));

		// Database Schema
		add_submenu_page(
			'customtables',                      // Parent Menu Slug
			'Custom Tables - Database Schema',   // Page Title
			'Database Schema',                   // Menu Title
			'manage_options',                    // Capability
			'customtables-schema',        // Menu Slug
			array($this, 'load_customtablesAdminSchema'), // Callback Function
			5                                    // Position
		);

		// Documentation
		add_submenu_page(
			'customtables',                       // Parent Menu Slug
			'Custom Tables - Documentation',       // Page Title
			'Documentation',                       // Menu Title
			'manage_options',                      // Capability
			'customtables-documentation',          // Menu Slug
			array($this, 'load_customtablesAdminDocumentation'), // Callback Function
			6                                       // Position
		);

		// Documentation
		$page_hook = add_submenu_page(
			'customtables',                       // Parent Menu Slug
			'Custom Tables - Settings',       // Page Title
			'Settings',                       // Menu Title
			'manage_options',                      // Capability
			'customtables-settings',          // Menu Slug
			array($this, 'load_customtablesAdminSettings'), // Callback Function
			6                                       // Position
		);
		add_action('load-' . $page_hook, array($this, 'preload_admin_settings'));

		// Edit Table Sub Sub Menu
		$page = common::inputGetCmd('page');
		$tableId = common::inputGetInt('table');

		switch ($page) {
			case 'customtables-api-xml':
				$file = common::inputGetCmd('xmlfile');

				$xml = 'unknown file';
				if ($file == 'tags')
					$xml = common::getStringFromFile(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR
						. 'media' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . $file . '.xml');

				elseif ($file == 'fieldtypes')
					$xml = common::getStringFromFile(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR
						. 'media' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . $file . '.xml');

				die($xml);

			case 'customtables-api-fields':

				if ($tableId == 0) {
					$result = array('error' => 'tableid not set');
				} else {

					$tempCT = new CT([], true);
					$tempCT->getTable($tableId, null, true);
					if ($tempCT->Table === null) {
						$result = array('error' => 'table id "' . $tableId . '" not found');
					} else {
						$result = $tempCT->Table->fields;
					}
				}
				header('Content-Type: application/json');
				die(wp_json_encode($result));

			case 'customtables-api-tables':
				$whereClause = new MySQLWhereClause();
				$whereClause->addCondition('published', 1);
				$tablesRows = database::loadAssocList('#__customtables_tables', ['id', 'tablename'], $whereClause, 'tablename');
				$tables = [];
				$tables[] = ['label' => '- Select Table', 'value' => null];
				foreach ($tablesRows as $tablesRow) {
					$tables[] = ['label' => $tablesRow['tablename'], 'value' => $tablesRow['id']];
				}
				header('Content-Type: application/json');
				die(wp_json_encode($tables));

			case 'customtables-api-layouts':
				$whereClause = new MySQLWhereClause();
				$whereClause->addCondition('published', 1);
				$layoutsRows = database::loadAssocList('#__customtables_layouts', ['id', 'layoutname', 'layouttype'], $whereClause, 'layoutname');
				$layouts = [];
				$layouts[] = ['label' => '- Select Layout', 'value' => 0, 'type' => 0];
				foreach ($layoutsRows as $layoutsRow) {
					$layouts[] = ['label' => $layoutsRow['layoutname'], 'value' => $layoutsRow['id'], 'type' => (int)$layoutsRow['layouttype']];
				}
				header('Content-Type: application/json');
				die(wp_json_encode($layouts));

			case 'customtables-api-preview':
				$attributesString = common::inputGetString('attributes');
				$attributesDecoded = stripslashes(urldecode($attributesString));
				$attributes = json_decode($attributesDecoded);
				if ($attributes === null)
					die('Table not found, probably deleted.');

				$ct = new CT([], true);
				$ct->getTable($attributes->table);

				if ($attributes->limit === null or $attributes->limit == "")
					$attributes->limit = 20;

				$ct->Params->limit = $attributes->limit;
				$ct->Params->filter = $attributes->filter;
				$ct->Params->blockExternalVars = true;
				$layouts = new Layouts($ct);

				if ((int)$attributes->type == CUSTOMTABLES_LAYOUT_TYPE_SIMPLE_CATALOG)
					$layoutId = (int)$attributes->cataloglayout;
				elseif ((int)$attributes->type == CUSTOMTABLES_LAYOUT_TYPE_EDIT_FORM)
					$layoutId = (int)$attributes->editlayout;
				elseif ((int)$attributes->type == CUSTOMTABLES_LAYOUT_TYPE_DETAILS)
					$layoutId = (int)$attributes->detailslayout;
				else
					$layoutId = 0;

				try {
					$output = $layouts->renderMixedLayout($layoutId, (int)$attributes->type);
				} catch (Throwable $e) {
					common::enqueueMessage($e->getMessage());
				}

				$image_url = plugins_url('assets/block-glass.png', __FILE__);

				$background = "background-image: url(\'' . $image_url . '\')";
				$preview_html = '<div style="position: relative;">' . $output['html']
					. '<div style="position:absolute;top:0;left:0;width:100%;height:100%;' . $background . ';background-repeat: repeat;"></div>'
					. '</div>';

				echo $preview_html;
				exit;

			case 'customtables-tables-edit':
				$tableId = common::inputGetInt('table');
				$page_hook = add_submenu_page(
					'customtables',                     // Parent Menu Slug
					($tableId === 0 ? esc_html__('Add Table', 'customtables') : esc_html__('Edit Table', 'customtables')) . ' - CustomTables', // Page Title
					' -- ' . ($tableId === 0 ? esc_html__('Add', 'customtables') : esc_html__('Edit')),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-tables-edit',                               // Menu Slug
					array($this, 'load_customtablesAdminTablesEdit'),        // Callback Function
					2                                                        // Position
				);
				add_action('load-' . $page_hook, array($this, 'load_customtablesAdminTablesEdit'));
				break;

			case 'customtables-fields':
				$page_hook = add_submenu_page(
					'customtables',                     // Parent Menu Slug
					esc_html__('Fields - CustomTables', 'customtables'), // Page Title
					' - ' . esc_html__('Fields', 'customtables'),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-fields',                               // Menu Slug 'customtables-fields'
					array($this, 'load_admin_field_list'),        // Callback Function
					2                                                        // Position
				);
				add_action('load-' . $page_hook, array($this, 'preload_admin_field_list'));
				break;

			case 'customtables-fields-edit':
				$tableId = common::inputGetInt('table');
				add_submenu_page(
					'customtables',                     // Parent Menu Slug
					esc_html__('Fields - CustomTables', 'customtables'), // Page Title
					' - ' . esc_html__('Fields', 'customtables'),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-fields&table=' . $tableId,                               // Menu Slug
					array($this, 'load_admin_field_list'),        // Callback Function
					2                                                        // Position
				);

				$fieldId = common::inputGetInt('field');
				$page_hook = add_submenu_page(
					'customtables',                     // Parent Menu Slug
					($fieldId == 0 ? esc_html__('Add Field', 'customtables') : esc_html__('Edit Field', 'customtables')) . ' - CustomTables', // Page Title
					' -- ' . ($fieldId == 0 ? esc_html__('Add', 'customtables') : esc_html__('Edit')),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-fields-edit',                               // Menu Slug
					array($this, 'load_customtablesAdminFieldsEdit'),        // Callback Function
					3                                                        // Position
				);
				add_action('load-' . $page_hook, array($this, 'load_customtablesAdminFieldsEdit'));
				break;

			case 'customtables-records':
				$page_hook = add_submenu_page(
					'customtables',                     // Parent Menu Slug
					esc_html__('Records - CustomTables', 'customtables'), // Page Title
					' - ' . esc_html__('Records', 'customtables'),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-records',                        // Menu Slug 'customtables-fields'
					array($this, 'load_admin_record_list'),        // Callback Function
					2                                                        // Position
				);
				add_action('load-' . $page_hook, array($this, 'preload_admin_record_list'));
				break;

			case 'customtables-records-edit':

				add_submenu_page(
					'customtables',                     // Parent Menu Slug
					esc_html__('Records - CustomTables', 'customtables'), // Page Title
					' - ' . esc_html__('Records', 'customtables'),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-records&table=' . $tableId,                               // Menu Slug
					array($this, 'load_admin_record_list'),        // Callback Function
					2                                                        // Position
				);

				$id = common::inputGetInt('id');
				$page_hook = add_submenu_page(
					'customtables',                     // Parent Menu Slug
					($id == 0 ? esc_html__('Add Record', 'customtables') : esc_html__('Edit Record', 'customtables')) . ' - CustomTables', // Page Title
					' -- ' . ($id == 0 ? esc_html__('Add', 'customtables') : esc_html__('Edit')),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-records-edit',                               // Menu Slug
					array($this, 'load_customtablesAdminRecordsEdit'),        // Callback Function
					3                                                        // Position
				);
				add_action('load-' . $page_hook, array($this, 'load_customtablesAdminRecordsEdit'));
				break;

			case 'customtables-import-records':

				$tableId = common::inputGetInt('table');
				add_submenu_page(
					'customtables',                     // Parent Menu Slug
					esc_html__('Records - CustomTables', 'customtables'), // Page Title
					' - ' . esc_html__('Records', 'customtables'),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-records&table=' . $tableId,                               // Menu Slug
					array($this, 'load_admin_record_list'),        // Callback Function
					2                                                        // Position
				);

				$page_hook = add_submenu_page(
					'customtables',                     // Parent Menu Slug
					esc_html__('Import CSV - CustomTables'), // Page Title
					' -- ' . __('Import CSV'),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-import-records',                               // Menu Slug
					array($this, 'load_customtablesAdminImportRecords'),        // Callback Function
					3                                                      // Position
				);
				//add_action('load-' . $page_hook, array($this, 'load_customtablesAdminImportRecords2'));
				add_action('load-' . $page_hook, array($this, 'preload_admin_import_records'));
				break;

			case 'customtables-layouts-edit':

				$layoutId = common::inputGetInt('layout');
				$page_hook = add_submenu_page(
					'customtables',                     // Parent Menu Slug
					($layoutId == 0 ? __('Add Layout', 'customtables') : __('Edit Layout', 'customtables')) . ' - CustomTables', // Page Title
					' -- ' . ($layoutId == 0 ? __('Add', 'customtables') : __('Edit')),                     // Menu Title
					'manage_options',                                         // Capability
					'customtables-layouts-edit',                               // Menu Slug
					array($this, 'load_customtablesAdminLayoutsEdit'),        // Callback Function
					3                                                        // Position
				);
				add_action('load-' . $page_hook, array($this, 'load_customtablesAdminLayoutsEdit'));
				break;
		}
	}

	protected function getCustomTablesIcon(): string
	{
		$svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><svg xmlns:sodipodi="https://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"   xmlns="http://www.w3.org/2000/svg"
   xmlns:inkscape="https://www.inkscape.org/namespaces/inkscape"
   
   inkscape:version="1.0 (4035a4fb49, 2020-05-01)"
   sodipodi:docname="ct-gray.svg"
   viewBox="0 0 114 115"
   height="115"
   width="114"
   id="svg858">
  <defs
     id="defs862" />

  <g
     id="g866"
     inkscape:label="Image"
     inkscape:groupmode="layer">
    <path
       id="path870"
       d="M 35.103826,96.56662 C 23.278826,89.9971 13.093146,83.832692 12.468982,82.867939 10.795824,80.281786 9.8404542,33.632036 11.417889,31.544115 13.001683,29.447778 53.915377,5.6138435 55.930197,5.6138435 c 2.43754,0 44.123893,24.0385655 45.211953,26.0716115 1.14004,2.130191 1.28774,48.867091 0.16003,50.641674 -1.19538,1.88108 -41.302403,26.293271 -43.102383,26.235391 -0.87778,-0.0282 -11.27097,-5.42638 -23.095971,-11.9959 z M 51.406787,75.963401 c 3.54226,-0.585352 3.78219,-0.8413 3.5,-3.733727 -0.30086,-3.083731 -0.3435,-3.105334 -6.13946,-3.110564 -5.15179,-0.0046 -6.119637,-0.334426 -8.249999,-2.811031 -1.921122,-2.233358 -2.413502,-3.932138 -2.413502,-8.326923 0,-4.806975 0.389869,-5.911028 3.01397,-8.535129 2.704151,-2.704154 3.544471,-2.978928 8.174891,-2.673077 4.92357,0.325215 5.19554,0.212012 5.91354,-2.46142 0.64567,-2.404117 0.39843,-2.936974 -1.73999,-3.75 -6.40245,-2.434204 -16.319434,0.374476 -20.107948,5.694953 -5.6066,7.873744 -4.870656,19.869788 1.576962,25.704801 2.097016,1.897774 8.514176,4.539463 11.168576,4.597661 0.825,0.01809 3.21133,-0.249907 5.30296,-0.595544 z m 22.76236,-14.599558 -0.16146,-13.75 h 4.54807 c 4.531126,0 4.548066,-0.01304 4.548066,-3.5 v -3.5 h -12.499996 -12.5 v 3.5 c 0,3.481481 0.0238,3.5 4.5,3.5 h 4.5 v 14.060365 14.060366 l 3.61339,-0.310366 3.61339,-0.310365 z"
       style="fill-opacity:1;" />
  </g>
</svg>';

		// WPCS: The use of base64_encode() is required for Menu Item in SVG format.
		return 'data:image/svg+xml;base64,' . base64_encode($svg);// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	public function preload_admin_table_list(): void
	{
		/*
		$arguments	=	array(
			'label'		=>	__( 'Users Per Page', 'customtables' ),
			'default'	=>	5,
			'option'	=>	'users_per_page'
		);

		add_screen_option( 'per_page', $arguments );
		*/

		// instantiate the Admin Table List

		$this->admin_table_list = new Admin_Table_List();
		$this->admin_table_list->handle_table_actions();
	}

	public function preload_admin_field_list(): void
	{

		/*
		$arguments	=	array(
			'label'		=>	__( 'Users Per Page', 'customtables' ),
			'default'	=>	5,
			'option'	=>	'users_per_page'
		);

		add_screen_option( 'per_page', $arguments );
		*/

		// instantiate the Admin Field List
		$this->admin_field_list = new Admin_Field_List();
		$this->admin_field_list->handle_field_actions();
		$this->admin_field_list->handle_field_tasks();

		$page = common::inputGetCmd('page');

		if ($page == 'customtables-fields') {
			$tableId = common::inputGetInt('table');
			if ($tableId === null) {
				// Redirect the user to the external URL
				$url = 'admin.php?page=customtables-tables';
				wp_redirect($url);
				exit();
			}
		}
	}

	public function preload_admin_record_list(): void
	{

		/*
		$arguments	=	array(
			'label'		=>	__( 'Users Per Page', 'customtables' ),
			'default'	=>	5,
			'option'	=>	'users_per_page'
		);

		add_screen_option( 'per_page', $arguments );
		*/

		$page = common::inputGetCmd('page');
		if ($page == 'customtables-records') {
			$tableId = common::inputGetInt('table');
			if ($tableId === null) {
				// Redirect the user to the external URL
				$url = 'admin.php?page=customtables-tables';
				wp_redirect($url);
				exit();
			}
		}

		// instantiate the Admin Record List
		$this->admin_record_list = new Admin_Record_List();
		$this->admin_record_list->handle_record_actions();
	}

	public function preload_admin_layout_list(): void
	{
		/*
		$arguments	=	array(
			'label'		=>	__( 'Users Per Page', 'customtables' ),
			'default'	=>	5,
			'option'	=>	'users_per_page'
		);

		add_screen_option( 'per_page', $arguments );
		*/

		// instantiate the Admin Layout List
		$this->admin_layout_list = new Admin_Layout_List();
		$this->admin_layout_list->handle_layout_actions();
	}

	public function preload_admin_import_tables(): void
	{
		// instantiate the Admin Layout List
		$this->admin_import_tables = new Admin_Import_Tables();
		$this->admin_import_tables->handle_import_actions();
	}

	public function preload_admin_import_records(): void
	{

		$tableId = common::inputGetInt('table');

		// Redirect the user to the external URL
		if ($tableId === null) {
			$url = 'admin.php?page=customtables-tables';
			wp_redirect($url);
			exit();
		}


		$this->admin_import_records = new Admin_Import_Records();
		$this->admin_import_records->handle_import_actions();
	}

	public function preload_admin_settings(): void
	{
		// instantiate the Admin Settings
		$admin_settings = new Admin_Settings();
		$admin_settings->handle_settings_actions();
	}

	/**
	 * Display the Table List
	 *
	 * Callback for
	 *
	 * @since    1.0.0
	 */
	public function load_admin_table_list(): void
	{
		{
			// instantiate the Admin Table List
			$this->admin_table_list = new Admin_Table_List();

			// query, filter, and sort the data
			$this->admin_table_list->prepare_items();

			// render the List of Tables
			include_once('views/customtables-tables.php');
		}
	}

	public function load_admin_field_list(): void
	{
		{
			// instantiate the Admin Field List
			$this->admin_field_list = new Admin_Field_List();

			// query, filter, and sort the data
			$this->admin_field_list->prepare_items();

			// render the List of Fields
			include_once('views/customtables-fields.php');
		}
	}

	public function load_admin_record_list(): void
	{
		{
			// instantiate the Admin Record List
			$this->admin_record_list = new Admin_Record_List();

			// query, filter, and sort the data
			$this->admin_record_list->prepare_items();

			// render the List of Records
			include_once('views/customtables-records.php');
		}
	}

	public function load_admin_layout_list(): void
	{
		// instantiate the Admin Layout List
		$this->admin_layout_list = new Admin_Layout_List();

		// query, filter, and sort the data
		$this->admin_layout_list->prepare_items();

		// render the List of Layout
		include_once('views/customtables-layouts.php');

	}

	public function load_customtablesAdminDashboard(): void
	{
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-dashboard.php');
	}

	/**
	 * @throws Exception
	 * @since 1.1.4
	 */
	public function load_customtablesAdminTablesEdit(): void
	{
		$this->admin_table_edit = new Admin_Table_Edit();
		$this->admin_table_edit->handle_table_actions();

		$page = common::inputGetCmd('page');
		if ($page == 'customtables-tables-edit') {
			$tableId = common::inputGetInt('table');

			if ($tableId === null) {
				$url = 'admin.php?page=customtables-tables';
				wp_redirect($url);
				exit();
			}
		}
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-tables-edit.php');
	}

	public function load_customtablesAdminFieldsEdit(): void
	{
		$this->admin_field_edit = new Admin_Field_Edit();
		$this->admin_field_edit->handle_field_actions();

		$page = common::inputGetCmd('page');
		if ($page == 'customtables-fields-edit') {
			$tableId = common::inputGetInt('table');
			$fieldId = common::inputGetInt('field');
			if ($fieldId === null) {
				// Redirect the user to the external URL
				if ($tableId === null)
					$url = 'admin.php?page=customtables-tables';
				else
					$url = 'admin.php?page=customtables-fields&table=' . $tableId;

				wp_redirect($url);
				exit();
			}
		}
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-fields-edit.php');
	}

	public function load_customtablesAdminRecordsEdit(): void
	{
		$tableId = common::inputGetInt('table');

		// Redirect the user to the external URL
		if ($tableId === null) {
			$url = 'admin.php?page=customtables-tables';
			wp_redirect($url);
			exit();
		}

		$this->admin_record_edit = new Admin_Record_Edit();
		$this->admin_record_edit->handle_record_actions();
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-records-edit.php');
	}

	public function load_customtablesAdminLayoutsEdit(): void
	{
		$this->admin_layout_edit = new Admin_Layout_Edit();
		$this->admin_layout_edit->handle_layout_actions();
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-layouts-edit.php');
	}

	public function load_customtablesAdminSchema(): void
	{
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-schema.php');
	}

	public function load_customtablesAdminDocumentation(): void
	{
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-documentation.php');
	}

	public function load_customtablesAdminSettings(): void
	{
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-settings.php');
	}

	public function load_customtablesAdminImportTables(): void
	{
		// instantiate the Admin Import Tables page
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-import-tables.php');
	}

	public function load_customtablesAdminImportRecords(): void
	{
		$tableId = common::inputGetInt('table');

		// Redirect the user to the external URL
		if ($tableId === null) {
			$url = 'admin.php?page=customtables-tables';
			wp_redirect($url);
			exit();
		}

		// instantiate the Admin Import CSV file page
		include_once('views' . DIRECTORY_SEPARATOR . 'customtables-import-records.php');
	}

	/*
	public function load_customtablesAdminImportRecords2(): void
	{
		$tableId = common::inputGetInt('table');

		// Redirect the user to the external URL
		if ($tableId === null) {
			$url = 'admin.php?page=customtables-tables';
			wp_redirect($url);
			exit();
		}
	}
	*/
}
