<?php

namespace CustomTablesWP\Inc\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Layouts;
use CustomTables\ListOfLayouts;
use CustomTables\TableHelper;
use Exception;

class Admin_Layout_Edit
{
	/**
	 * @since    1.0.0
	 * @access   private
	 */
	public CT $ct;
	public ListOfLayouts $helperListOfLayouts;
	public ?int $layoutId;
	public ?array $layoutRow;
	public ?array $params;
	public array $allTables;
	public array $roles;

	/**
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function __construct()
	{
		require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoflayouts.php');
		$this->ct = new CT([], true);
		$this->helperListOfLayouts = new ListOfLayouts($this->ct);
		$this->layoutId = common::inputGetInt('layout');
		$this->params = [];

		if ($this->layoutId === 0)
			$this->layoutId = null;

		if ($this->layoutId !== null) {
			$layout = new Layouts($this->ct);
			$this->layoutRow = $layout->getLayoutRowById($this->layoutId);

			if (!empty($this->layoutRow['params']))
				$this->params = json_decode($this->layoutRow['params'], true);
		} else {
			$this->layoutRow = null;
		}
		$this->allTables = TableHelper::getAllTables();
		add_action('admin_enqueue_scripts', array($this, 'codemirror_enqueue_scripts'));

		$this->roles = wp_roles()->roles;
	}

	function codemirror_enqueue_scripts($hook): void
	{
		$cm_settings1['codeEditor_layoutcode'] = wp_enqueue_code_editor(array('mode' => 'text/html'));
		wp_localize_script('jquery', 'cm_settings_layoutcode', $cm_settings1);
		if ($this->ct->Env->advancedTagProcessor) {
			$cm_settings2['codeEditor_layoutmobile'] = wp_enqueue_code_editor(array('mode' => 'text/html'));
			wp_localize_script('jquery', 'cm_settings_layoutmobile', $cm_settings2);
			$cm_settings3['codeEditor_layoutcss'] = wp_enqueue_code_editor(array('mode' => 'css'));
			wp_localize_script('jquery', 'cm_settings_layoutcss', $cm_settings3);
			$cm_settings4['codeEditor_layoutjs'] = wp_enqueue_code_editor(array('mode' => 'javascript'));
			wp_localize_script('jquery', 'cm_settings_layoutjs', $cm_settings4);
		}

		wp_enqueue_script('wp-theme-plugin-editor');
		wp_enqueue_style('wp-codemirror');
	}

	function handle_layout_actions(): void
	{
		$action = common::inputPostCmd('action', '', 'create-edit-layout');

		if ('createlayout' === $action || 'savelayout' === $action) {
			$this->helperListOfLayouts->save($this->layoutId);
			$url = 'admin.php?page=customtables-layouts';

			$paged = common::inputGetInt('paged');
			if($paged !== null)
				$url .= '&paged=' . $paged;

			ob_start(); // Start output buffering
			ob_end_clean(); // Discard the output buffer
			wp_redirect(admin_url($url));
			exit;
		}
	}

	/**
	 * Generates an HTML select element for WordPress role selection.
	 *
	 * Creates a multiple-select dropdown of WordPress roles with optional guest user selection.
	 * The selected values are taken from the params array using the provided parameter name.
	 * Selected roles are expected to be stored as a comma-separated string.
	 *
	 * @param string $paramName The name of the parameter in the params array to get selected roles from
	 * @param bool $allowGuests Optional. Whether to include a "Guest" option for non-logged-in users. Default false
	 *
	 * @return string HTML markup for the select element with role options
	 *
	 * @since 1.4.2
	 *
	 * @example
	 * // Generate a role selector with guest option
	 * $html = $this->get_role_selector('user_roles', true);
	 *
	 * // Generate a role selector without guest option
	 * $html = $this->get_role_selector('admin_roles');
	 *
	 * @uses $this->params  Array of parameters containing selected roles
	 * @uses $this->roles   Array of WordPress roles
	 */
	function get_role_selector(string $paramName, bool $allowGuests = false): string
	{
		$selected = $this->params[$paramName] ?? []; //$selected_roles

		// Start building the select element
		$output = '<select id="' . $paramName . '" name="' . $paramName . '[]" multiple class="regular-text" style="min-height: 130px;">';

		if ($allowGuests) {
			$is_selected = in_array('guest', $selected) ? 'selected' : '';
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				'guest',
				$is_selected,
				esc_html__("Guest", "customtables")
			);
		}

		// Add each role as an option
		foreach ($this->roles as $role_id => $role) {
			// Check if this role should be selected
			$is_selected = in_array($role_id, $selected) ? 'selected' : '';

			// Add the option element
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr($role_id),
				$is_selected,
				esc_html($role['name'])
			);
		}

		// Close the select element
		$output .= '</select>';

		return $output;
	}

	/**
	 * Generates an HTML select element for publish status selection.
	 *
	 * Creates a dropdown with Published/Unpublished options and an optional default "Select" option.
	 * The status parameter determines which option is pre-selected.
	 *
	 * @since 1.4.2
	 *
	 * @param string $elementId  The ID and name to be used for the select element
	 * @param ?int   $status    The currently selected status. Null for no selection, 1 for Published, 0 for Unpublished
	 *
	 * @return string HTML markup for the select element with status options
	 *
	 * @example
	 * // Generate a status selector with no pre-selected value
	 * $html = $this->get_publish_status_selector('publish_status', null);
	 *
	 * // Generate a status selector with 'Published' selected
	 * $html = $this->get_publish_status_selector('publish_status', 1);
	 *
	 * // Generate a status selector with 'Unpublished' selected
	 * $html = $this->get_publish_status_selector('publish_status', 0);
	 */
	function get_publish_status_selector(string $elementId, int $status = 0): string
	{
		$statuses = [1 => 'Published', 0 => 'Unpublished'];

		// Start building the select element
		$output = '<select id="' . $elementId . '" name="' . $elementId . '">';

		// Add each role as an option
		foreach ($statuses as $status_id => $status_label) {
			// Check if this role should be selected
			$is_selected = $status_id === $status ? ' selected' : '';

			// Add the option element
			$output .= sprintf(
				'<option value="%d" %s>%s</option>',
				esc_attr($status_id),
				$is_selected,
				esc_html($status_label)
			);
		}

		// Close the select element
		$output .= '</select>';

		return $output;
	}
}