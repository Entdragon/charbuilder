<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2025. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

if (!defined('ABSPATH')) exit;

class InputBox_fileLink extends BaseInputBox
{
	function __construct(CT &$ct, Field $field, ?array $row, array $option_list = [], array $attributes = [])
	{
		parent::__construct($ct, $field, $row, $option_list, $attributes);
		self::selectBoxAddCSSClass($this->attributes);
	}

	function render(?string $value, ?string $defaultValue): string
	{
		if ($value === null) {
			$value = common::inputGetString($this->ct->Table->fieldPrefix . $this->field->fieldname, '');
			if ($value == '')
				$value = $defaultValue;
		}

		if ($this->field->params === null or count($this->field->params) == 0)
			$path = CUSTOMTABLES_IMAGES_PATH . DIRECTORY_SEPARATOR;
		else {
			$path = CUSTOMTABLES_IMAGES_PATH . DIRECTORY_SEPARATOR . $this->field->params[0] ?? '';
		}

		//Check if the path does not start from the root directory
		if (!empty($path)) {
			if ($path[0] !== '/' && (strlen($path) >= 2 && $path[1] !== ':')) {
				$path = '/images/' . $path;
			}
		}

		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		$real_path = $path;//un-relative path
		$options = [];

		if (file_exists($real_path)) {

			$options [] = '<option value="">' . esc_html__("Select file", "customtables") . '</option>'; // Optional default option

			$files = scandir($real_path);
			foreach ($files as $file) {
				if ($file !== '.' && $file !== '..' && !is_dir($real_path . '/' . $file)) {
					$fileValue = htmlspecialchars($file);
					$selected = ($fileValue === $value) ? ' selected' : '';
					$options [] = '<option value="' . $file . '"' . $selected . '>' . $file . '</option>';
				}
			}
		} else
			$options [] = '<option value="">' . esc_html__("Path", "customtables") . ' (' . $path . ') ' . esc_html__("not found.", "customtables") . '</option>';

		return '<select ' . self::attributes2String($this->attributes) . '>' . implode('', $options) . '</select>';
	}
}