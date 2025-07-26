<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">

    <tr class="form-field form-required">
        <!-- Layout Name Field -->
        <th scope="row">
            <label for="layoutname">
				use CustomTables\Layouts;
				use CustomTables\MySQLWhereClause;

				echo esc_html__('Layout Name', 'customtables'); ?>
            </label>
        </th>
        <td>
            <input name="layoutname" type="text" id="layoutname" style="min-width: 150px;"
                   aria-required="true"
                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
        </td>

        <!-- Layout Type -->
        <th scope="row" style="text-align: right;">
            <label for="layouttype">
            </label>
        </th>
        <th>


			$selectedType = $this->admin_layout_edit->layoutRow['layouttype'] ?? '';
			$Layouts = new Layouts($this->admin_layout_edit->ct);
			$types = $Layouts->layoutTypeTranslation();

			$allowed_html = array(
				'option' => array(
					'value' => array(),
					'selected' => array())
			);

			$options_escaped = '';

			foreach ($types as $key => $type) {
				$options_escaped .= '<option value="' . esc_html($key) . '"';
				if ($key == $selectedType) {
					$options_escaped .= ' selected';
				}
				$options_escaped .= '>' . esc_html($type) . '</option>';
			}
			?>

            <select name="layouttype" id="layouttype">
            </select>

			/*
				* Custom Tables_LAYOUTS_SIMPLE_CATALOG = "Simple Catalog"
				* Custom Tables_LAYOUTS_CATALOG_PAGE = "Catalog Page"
				* Custom Tables_LAYOUTS_CATALOG_ITEM = "Catalog Item"
				* Custom Tables_LAYOUTS_EDIT_FORM = "Edit form"
				* Custom Tables_LAYOUTS_DETAILS = "Details"
				* Custom Tables_LAYOUTS_EMAIL_MESSAGE = "Email Message"
				* Custom Tables_LAYOUTS_XML = "XML File"
				* Custom Tables_LAYOUTS_CSV = "CSV File"
				* Custom Tables_LAYOUTS_JSON = "JSON File"
			 */
			?>
        </th>

        <!-- Table -->
        <th scope="row" style="text-align: right;">
            <label for="layoutname">
            </label>
        </th>
        <td>

			$whereClause = new MySQLWhereClause();
			$whereClause->addCondition('published', 1);

			$allowed_html = array(
				'select' => array(
					'id' => array(),
					'name' => array(),
				    'onchange' => true),
				'option' => array(
					'value' => array(),
					'selected' => array())
			);

			try {
				$list_of_tables_safe = Forms::renderHTMLSelectBoxFromDB('table', $this->admin_layout_edit->layoutRow['tableid'] ?? 0,
                    true, '#__customtables_tables',
					['id', 'tablename'], $whereClause, 'tablename', ['onchange="loadFieldsUpdate();"']);

				echo wp_kses($list_of_tables_safe, $allowed_html);
			} catch (Exception $e) {
				echo 'renderHTMLSelectBoxFromDB: could not load the list';
			}
			?>
        </td>
    </tr>
</table>
<hr/>

} // namespace CharacterGeneratorDev
