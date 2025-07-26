<?php
/**
 * All the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @param array $attributes The array of attributes for this block.
 * @param string $content Rendered block output. ie. <InnerBlocks.Content />.
 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered.
 */

namespace CustomTablesWP;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\CTMiscHelper;
use CustomTables\Layouts;
use Exception;
use Throwable;

class template
{
	var array $enqueueList;
	var array $blocks;

	function __construct()
	{
		$this->enqueueList = [];
	}

	static public function prepareAttributes(array $a): array
	{
		$filter = $a['filter'] ?? null;
		$orderby = $a['orderby'] ?? null;
		$order = $a['order'] ?? null;
		$limit = $a['limit'] ?? null;

		return ['type' => $a['type'] ?? 0,
			'table' => $a['table'] ?? 0,
			'cataloglayout' => $a['cataloglayout'] ?? 0,
			'editlayout' => $a['editlayout'] ?? 0,
			'detailslayout' => $a['detailslayout'] ?? 0,
			'filter' => $filter == '' ? null : $filter,
			'orderby' => $orderby == '' ? null : $orderby,
			'order' => $order == '' ? null : $order,
			'limit' => $limit == '' ? null : $limit,
			'loading' => $a['loading'] ?? 0,];
	}

	function load_blocks($post_content)
	{
		$blocks = parse_blocks($post_content);
		$this->blocks = $this->get_block_attributes('customtables/dynamic-block', $blocks);
	}

	function get_block_attributes($block_name, $blocks): array
	{

		$attributes = [];

		foreach ($blocks as $block) {

			if ($block['blockName'] === $block_name) {

				try {
					$html = $this->renderBlock($block['attrs']);
				}catch (Exception $e) {
					$html = '<p style="background-color: #aa0000;color: #f1f1f1;padding: 5px;border-radius: 5px;">'
						. $e->getMessage().'</p>';
				}

				$preparedAttributes = self::prepareAttributes($block['attrs']);
				$attributes[] = ['hash' => md5(json_encode($preparedAttributes)), 'attributes' => $preparedAttributes, 'html' => $html];
			}

			if (!empty($block['innerBlocks'])) {
				$inner_attrs = $this->get_block_attributes($block_name, $block['innerBlocks']);

				if ($inner_attrs)
					$attributes = array_merge($attributes, $inner_attrs);
			}
		}
		return $attributes;
	}

	/**
	 * @throws Exception
	 */
	function renderBlock($attributes): string
	{
		$result = '';

		$ct = new CT([], true);

		if (!empty($attributes['table'])) {
			$ct->getTable($attributes['table']);
			if ($ct->Table->tablename === null) {
				throw new Exception('Table "' . $attributes['table'] . '" not found.');
			}
		} elseif (!empty($attributes['layout'])) {
			$layoutId = $attributes['layout'];
		} else {
			throw new Exception('Table or Layout are required.');
		}


		//try {

		if (!isset($attributes['limit']) or $attributes['limit'] == "")
			$attributes['limit'] = 20;

		if (!empty($attributes['id']))
			$menu_params['listingid'] = $attributes['listingid'];
		//$ct->Params->listing_id = $attributes['id'];

		//$ct->Params->limit = $attributes['limit'];
		//$ct->Params->sortBy = $attributes['orderby'] ?? null;
		//$ct->Params->filter = $attributes['filter'] ?? null;

		$menu_params = [];
		$menu_params['listingid'] = $attributes['listingid'] ?? null;
		$menu_params['filter'] = $attributes['filter'] ?? null;
		$menu_params['sortby'] = $attributes['orderby'] ?? null;
		$menu_params['limit'] = $attributes['limit'];

		$ct->Params->setParams($menu_params);
		$ct->Params->blockExternalVars = false;
		$layouts = new Layouts($ct);

		if (!empty($attributes['view'])) {
			if ($attributes['view'] == 'edit')
				$layoutType = CUSTOMTABLES_LAYOUT_TYPE_EDIT_FORM;
			elseif ($attributes['view'] == 'details')
				$layoutType = CUSTOMTABLES_LAYOUT_TYPE_DETAILS;
			elseif ($attributes['view'] == 'catalog')
				$layoutType = CUSTOMTABLES_LAYOUT_TYPE_SIMPLE_CATALOG;
			else
				$layoutType = CUSTOMTABLES_LAYOUT_TYPE_SIMPLE_CATALOG;

			try {
				$mixedLayout_array = $layouts->renderMixedLayout(0, $layoutType);
			} catch (Throwable $e) {
				throw new Exception($e->getMessage());
			}

		} else {
			if ($ct->Table !== null) {
				$view = common::inputGetCmd('view' . $ct->Table->tableid);
				if ($view == 'edit' or $view == 'edititem') {
					$layoutType = CUSTOMTABLES_LAYOUT_TYPE_EDIT_FORM;
					$layoutId = (int)($attributes['editlayout'] ?? 0);
				} elseif ($view == 'details') {
					$layoutType = CUSTOMTABLES_LAYOUT_TYPE_DETAILS;
					$layoutId = (int)($attributes['detailslayout'] ?? 0);
				} else {
					$layoutType = $attributes['type'] ?? CUSTOMTABLES_LAYOUT_TYPE_SIMPLE_CATALOG;

					if ((int)$layoutType == CUSTOMTABLES_LAYOUT_TYPE_SIMPLE_CATALOG)
						$layoutId = (int)($attributes['cataloglayout'] ?? 0);
					elseif ((int)$layoutType == CUSTOMTABLES_LAYOUT_TYPE_EDIT_FORM)
						$layoutId = (int)($attributes['editlayout'] ?? 0);
					elseif ((int)$layoutType == CUSTOMTABLES_LAYOUT_TYPE_DETAILS)
						$layoutId = (int)($attributes['detailslayout'] ?? 0);
					else
						$layoutId = 0;
				}

				try {
					$mixedLayout_array = $layouts->renderMixedLayout($layoutId, $layoutType);
				} catch (Throwable $e) {
					throw new Exception($e->getMessage());
				}

			} else {

				try {
					$mixedLayout_array = $layouts->renderMixedLayout($layoutId);
				} catch (Throwable $e) {
					throw new Exception($e->getMessage());
				}
			}
		}

		if (!empty($mixedLayout_array['redirect']))
			common::redirect($mixedLayout_array['redirect'], $mixedLayout_array['message'], $mixedLayout_array['success']);

		if ($mixedLayout_array ['success']) {
			if ($ct->Env->clean) {
				if ($ct->Env->frmt == 'json')
					CTMiscHelper::fireSuccess($mixedLayout_array ['id'] ?? null, $mixedLayout_array ['data'] ?? null, $ct->Params->msgItemIsSaved);
				else
					die($mixedLayout_array ['short'] ?? 'done');
			}
		} else {
			if ($ct->Env->clean) {
				if ($ct->Env->frmt == 'json')
					CTMiscHelper::fireError(500, $mixedLayout_array['message'] ?? 'Error');
				else {
					echo 'message: ' . $mixedLayout_array['message'] . '<br/>';
					die($mixedLayout_array['short'] ?? 'error');
				}

			}else{
				throw new Exception($mixedLayout_array ['message'] ?? 'Error');
			}
		}

		$mixedLayout_safe = $mixedLayout_array['html'] ?? null;
		$this->enqueueList['FieldInputPrefix'] = $ct->Table->fieldInputPrefix;

		$message = get_transient('customtables_error_message');
		if ($message) {
			$result .= '<blockquote style="background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 10px;"><p>' . esc_html($message) . '</p></blockquote>';
			// Once displayed, clear the transient
			delete_transient('customtables_error_message');
		}

		$success_message = get_transient('customtables_success_message');
		if (!empty($success_message)) {
			$result .= '<blockquote style="background-color: #d4edda;border-left: 5px solid #28a745;padding: 10px;"><p>' . esc_html($success_message) . '</p></blockquote>';
			// Optionally, you can delete the transient after displaying it
			delete_transient('customtables_success_message');
		}

		if (!is_admin()) {
			if (isset($mixedLayout_array['style'])) {

				if (!isset($this->enqueueList['style']) or $this->enqueueList['style'] === null)
					$this->enqueueList['style'] = [];

				if (!in_array($mixedLayout_array['style'], $this->enqueueList['style']))
					$this->enqueueList['style'][] = $mixedLayout_array['style'];
			}

			if (isset($mixedLayout_array['script']))
				$this->enqueueList['script'] = $mixedLayout_array['script'];

			if (isset($mixedLayout_array['captcha']))
				$this->enqueueList['recaptcha'] = $mixedLayout_array['captcha'];

			if (isset($mixedLayout_array['scripts']))
				$this->enqueueList['scripts'] = $mixedLayout_array['scripts'];

			if (isset($mixedLayout_array['styles']))
				$this->enqueueList['styles'] = $mixedLayout_array['styles'];

			if (isset($mixedLayout_array['jslibrary']))
				$this->enqueueList['jslibrary'] = $mixedLayout_array['jslibrary'];

			//Fields
			if (isset($mixedLayout_array['fieldtypes'])) {

				if (in_array('date', $mixedLayout_array['fieldtypes']))
					$this->enqueueList['fieldtype:date'] = true;

				if (in_array('datetime', $mixedLayout_array['fieldtypes']))
					$this->enqueueList['fieldtype:datetime'] = true;

				if (in_array('color', $mixedLayout_array['fieldtypes']))
					$this->enqueueList['fieldtype:color'] = true;

				if (in_array('googlemapcoordinates', $mixedLayout_array['fieldtypes']))
					$this->enqueueList['fieldtype:googlemapcoordinates'] = true;

				if (in_array('file', $mixedLayout_array['fieldtypes']))
					$this->enqueueList['fieldtype:file'] = true;
			}

			//add_filter( 'wp_title', 'customtables_wp_title', 10, 2 );
			//add_action('wp_enqueue_scripts', array($this, 'ct_enqueue_frontend_scripts'), 10);
			//add_action('wp_enqueue_scripts', 'ct_enqueue_frontend_scripts', 10);
			//add_action('init', 'ct_enqueue_frontend_scripts', 10);
			//wp_add_inline_script('ct-sadckmt', 'alert(1)');
			//wp_enqueue_script('jquery-ui-tabs');
			//add_action('wp_footer', 'ct_enqueue_frontend_scripts', 100);
		}

		$result .= '<div>' . $mixedLayout_safe . '</div>';
		return $result;
	}

	function enqueue_scripts()
	{
		wp_enqueue_script('ct-catalog-ajax', CUSTOMTABLES_MEDIA_WEBPATH . 'js/ajax.js', array(), PLUGIN_VERSION, true);
		wp_enqueue_script('ct-catalog-base64', CUSTOMTABLES_MEDIA_WEBPATH . 'js/base64.js', array(), PLUGIN_VERSION, true);
		wp_enqueue_script('ct-catalog-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/catalog.js', array(), PLUGIN_VERSION, true);
		wp_enqueue_script('ct-edit-form-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/edit.js', array(), PLUGIN_VERSION, true);

		wp_enqueue_script('ct-uploader-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/uploader.js', array(), PLUGIN_VERSION, true);

		wp_enqueue_style('ct-catalog-style', CUSTOMTABLES_MEDIA_WEBPATH . 'css/style.css', array(), PLUGIN_VERSION, false);


		//wp_enqueue_style('font-awesome-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2');
		//wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
		//wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css');

		// Add inline script after enqueuing the main script
		$ctWebsiteRoot = home_url();
		if ($ctWebsiteRoot !== '' and $ctWebsiteRoot[strlen($ctWebsiteRoot) - 1] !== '/')
			$ctWebsiteRoot .= '/';

		wp_add_inline_script('ct-edit-form-script', 'let ctWebsiteRoot = "' . esc_url($ctWebsiteRoot) . '";');
		wp_add_inline_script('ct-edit-form-script', 'let ctFieldInputPrefix = "' . $this->enqueueList['FieldInputPrefix'] . '";');
		wp_add_inline_script('ct-edit-form-script', 'let gmapdata = [];');
		wp_add_inline_script('ct-edit-form-script', 'let gmapmarker = [];');
		$wp_version_decimal = floatval(substr(get_bloginfo('version'), 0, 3));
		wp_add_inline_script('ct-edit-form-script', 'const CTEditHelper = new CustomTablesEdit("WordPress",' . $wp_version_decimal . ');');

		// Add inline script after enqueuing the main script
		if (isset($this->enqueueList['style']))
			wp_add_inline_style('ct-catalog-style', implode('', $this->enqueueList['style']));

		wp_add_inline_style('ct-catalog-style', '

	:root {--ctToolBarIconSize: 16px;--ctToolBarIconFontSize: 16px;}
	
	.toolbarIcons{
		text-decoration: none;
	}
	
	.toolbarIcons a{
		text-decoration: none;
	}
	
	.ctToolBarIcon{
		width: var(--ctToolBarIconSize);
		height: var(--ctToolBarIconSize);
	}
	
	.ctToolBarIcon + span {
		margin-left:10px;
	}
	
	.ctToolBarIcon2x{
		width: calc(var(--ctToolBarIconSize) * 2);
		height: calc(var(--ctToolBarIconSize) * 2);
		font-size: 1.5em;
	}
	
	.ctToolBarIcon2x + span {
		margin-left:15px;
	}

	.nav-links a, .nav-links span {
		margin: 0 10px 0 10px;
	}
		');
		// Add inline script after enqueuing the main script
		if (isset($this->enqueueList['script']))
			wp_add_inline_script('ct-edit-form-script', $this->enqueueList['script']);

		if (isset($this->enqueueList['recaptcha']))
			wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');

		// Add JS script files
		if (isset($this->enqueueList['scripts']) and is_array($this->enqueueList['scripts'])) {
			foreach ($this->enqueueList['scripts'] as $script) {
				$parts = explode('/', $script);
				$handle = str_replace('.js', '-js', end($parts));
				wp_enqueue_script($handle, $script);
			}
		}

		// Add CSS style files
		if (isset($this->enqueueList['styles']) and is_array($this->enqueueList['styles'])) {
			foreach ($this->enqueueList['styles'] as $style) {
				$parts = explode('/', $style);
				$handle = str_replace('.css', '-css', end($parts));
				wp_enqueue_style($handle, $style);
			}
		}

		// Add common JS libraries
		if (isset($this->enqueueList['jslibrary']) and is_array($this->enqueueList['jslibrary'])) {
			foreach ($this->enqueueList['jslibrary'] as $jslibrary) {
				switch ($jslibrary) {
					case 'jquery';
						wp_enqueue_script('jquery');
						break;
					case 'jquery-ui-core';
						wp_enqueue_script('jquery-ui-core');
						break;
					case 'jquery-ui-tabs';
						wp_enqueue_script('jquery-ui-tabs');
						break;
				}
			}
		}

		//Add scripts needed for field types:

		//Date
		if (isset($this->enqueueList['fieldtype:date']) and $this->enqueueList['fieldtype:date']) {

			// Enqueue jQuery UI
			wp_enqueue_script('jquery-ui-core');

			wp_enqueue_script('ct-edit-form-script-jquery-ui-min', PLUGIN_NAME_URL . 'assets/jquery-ui.min.js');
			wp_enqueue_style('ct-edit-form-style-jquery-timepicker', PLUGIN_NAME_URL . 'assets/jquery.datetimepicker.min.css', array(), PLUGIN_VERSION);

			//Include jQuery UI Timepicker addon from CDN
			wp_enqueue_script('ct-edit-form-script-jquery-timepicker', PLUGIN_NAME_URL . 'assets/jquery.datetimepicker.full.min.js');
		}

		//Color
		if (isset($this->enqueueList['fieldtype:color']) and $this->enqueueList['fieldtype:color']) {
			wp_enqueue_script('ct-spectrum-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/spectrum.js', array(), PLUGIN_VERSION, true);
			wp_enqueue_style('ct-spectrum-style', CUSTOMTABLES_MEDIA_WEBPATH . 'css/spectrum.css', array(), PLUGIN_VERSION, false);
		}

		//Google Map Coordinates
		$googleMapAPIKey = get_option('customtables-googlemapapikey') ?? '';
		if ($googleMapAPIKey != '')
			wp_enqueue_script('ct-google-map-script', 'https://maps.google.com/maps/api/js?key=' . $googleMapAPIKey . '&sensor=false', array(), PLUGIN_VERSION, true);

		//Google Drive
		if (isset($this->enqueueList['fieldtype:file']) and $this->enqueueList['fieldtype:file']) {

			$GoogleDriveAPIKey = get_option('customtables-googledriveapikey') ?? '';
			$GoogleDriveClientId = get_option('customtables-googledriveclientid') ?? '';

			if ($GoogleDriveAPIKey != '' and $GoogleDriveClientId != '') {
				wp_enqueue_script('ct-google-api', 'https://apis.google.com/js/api.js', array(), PLUGIN_VERSION, true);
				wp_enqueue_script('ct-google-gsi-client', 'https://accounts.google.com/gsi/client', array(), PLUGIN_VERSION, true);
			}
		}

		wp_localize_script(
			'ct-edit-form-script',
			'ctTranslationScriptObject',
			common::getLocalizeScriptArray()
		);
	}
}
