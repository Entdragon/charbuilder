<?php

namespace CustomTablesWP\Inc\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTables\ListOfFields;
use CustomTables\MySQLWhereClause;
use Exception;
use WP_List_Table;

class Admin_Field_List extends WP_List_Table
{
	/**
	 * @since    1.0.0
	 * @access   private
	 */
	public CT $ct;
	public ListOfFields $helperListOfFields;
	public ?int $tableId;
	public array $fieldTypes;
	protected int $count_all;
	protected int $count_trashed;
	protected int $count_published;
	protected int $count_unpublished;
	protected ?string $current_status;

	/**
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function __construct()
	{
		require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoffields.php');
		$this->ct = new CT([], true);
		$this->helperListOfFields = new ListOfFields($this->ct);

		$this->tableId = common::inputGetInt('table');

		if ($this->tableId) {
			$this->ct->getTable($this->tableId, null, true);

			$whereClause = new MySQLWhereClause();
			$whereClause->addCondition('tableid', $this->tableId);
			$whereClause->addCondition('published', -2, '!=');
			$this->count_all = database::loadColumn('#__customtables_fields', ['COUNT_ROWS'], $whereClause)[0] ?? 0;

			$whereClause = new MySQLWhereClause();
			$whereClause->addCondition('tableid', $this->tableId);
			$whereClause->addCondition('published', -2);
			$this->count_trashed = database::loadColumn('#__customtables_fields', ['COUNT_ROWS'], $whereClause)[0] ?? 0;

			$whereClause = new MySQLWhereClause();
			$whereClause->addCondition('tableid', $this->tableId);
			$whereClause->addCondition('published', 1);
			$this->count_published = database::loadColumn('#__customtables_fields', ['COUNT_ROWS'], $whereClause)[0] ?? 0;

		} else {
			$this->count_all = 0;
			$this->count_trashed = 0;
			$this->count_published = 0;
		}

		$this->count_unpublished = $this->count_all - $this->count_published;
		$this->current_status = common::inputGetCmd('status');

		if ($this->current_status !== null and $this->current_status !== 'all') {
			if ($this->current_status == 'trash' and $this->count_trashed == 0)
				$this->current_status = null;
			if ($this->current_status == 'unpublished' and $this->count_unpublished == 0)
				$this->current_status = null;
			if ($this->current_status == 'published' and $this->count_published == 0)
				$this->current_status = null;
		}
		parent::__construct(array(
			'plural' => 'users',    // Plural value used for labels and the objects being listed.
			'singular' => 'user',        // Singular label for an object being listed, e.g. 'post'.
			'ajax' => false,        // If true, the parent class will call the _js_vars() method in the footer
		));

		$this->fieldTypes = $this->helperListOfFields->getFieldTypesFromXML();
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	function prepare_items()
	{
		$data = $this->get_data(); // Fetch your data here

		$columns = $this->get_columns();
		$hidden = array(); // Columns to hide (optional)
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		// Paginate the data
		$per_page = 10; // Number of items per page
		$current_page = $this->get_pagenum(); // Get the current page
		$total_items = count($data); // Total number of items

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));

		// Slice the data to display the correct items for the current page
		$this->items = array_slice($data, (($current_page - 1) * $per_page), $per_page);

		// Set up actions
		//$this->process_bulk_action();
	}

	/**
	 * @throws Exception
	 */
	function get_data(): array
	{
		// Fetch and return your data here
		if ($this->tableId === null or $this->ct->Table == null or $this->ct->Table->tablename === null)
			return [];

		$search = common::inputPostString('s', null, 'bulk-' . $this->_args['plural']);
		$orderby = common::inputGetCmd('orderby');
		$order = common::inputGetCmd('order');

		switch ($this->current_status) {
			case 'published':
				$published = 1;
				break;
			case 'unpublished':
				$published = 0;
				break;
			case 'trash':
				$published = -2;
				break;
			default:
				$published = null;
				break;
		}

		try {
			$data = $this->helperListOfFields->getListQuery($this->tableId, $published, $search, null, $orderby, $order);
		} catch (Exception $exception) {
			$data = [];
		}

		$newData = [];
		foreach ($data as $item) {
			if ($item['published'] == -2) {
				$label = '<span>' . $item['fieldname'] . '</span>';
			}
			else {

				$link = '?page=customtables-fields-edit&action=edit&table=' . $this->tableId . '&field=' . $item['id'];

				$paged = common::inputGetInt('paged');
				if ($paged !== null) {
					$link .= '&paged=' . $paged;
				}

				$label = '<a class="row-title" href="' . $link . '">' . $item['fieldname'] . '</a>'
					. (($this->current_status != 'unpublished' and $item['published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');
			}

			$item['fieldname'] = '<strong>' . $label . '</strong>';

			$result = '<ul style="list-style: none !important;margin-left:0;padding-left:0;">';
			$moreThanOneLang = false;
			foreach ($this->ct->Languages->LanguageList as $lang) {
				$fieldTitle = 'fieldtitle';
				$fieldDescription = 'description';

				if ($moreThanOneLang) {
					$fieldTitle .= '_' . $lang->sef;
					$fieldDescription .= '_' . $lang->sef;
				}

				if (!array_key_exists($fieldTitle, $item))
					Fields::addLanguageField('#__customtables_fields', 'fieldtitle', $fieldTitle);

				if (!array_key_exists($fieldTitle, $item))
					Fields::addLanguageField('#__customtables_fields', 'description', $fieldDescription);

				$result .= '<li>' . (count($this->ct->Languages->LanguageList) > 1 ? $lang->title . ': ' : '') . '<b>' . ($item[$fieldTitle] ?? '') . '</b></li>';
				$moreThanOneLang = true; //More than one language installed
			}

			$result .= '</ul>';
			$item['fieldtitle'] = $result;
			$item['typeparams'] = str_replace('****apos****', "'", str_replace('****quote****', '"', common::escape($item['typeparams'])));
			$item['table'] = str_replace('****apos****', "'", str_replace('****quote****', '"', common::escape($item['tabletitle'])));
			$item['type'] = $this->getFieldTypeLabel($item['type']);
			$item['isrequired'] = (int)$item['isrequired'] ? esc_html__('Yes', 'customtables') : esc_html__('No', 'customtables');
			$newData[] = $item;
		}
		return $newData;
	}

	protected function getFieldTypeLabel($typeName): string
	{
		foreach ($this->fieldTypes as $type)
			if ($type['name'] == $typeName)
				return $type['label'];

		return '<span style="color:red;">Unknown Type</span>';
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	function get_columns(): array
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'fieldname' => esc_html__('Field Name', 'customtables'),
			'fieldtitle' => esc_html__('Field Title', 'customtables'),
			'type' => esc_html__('Field Type', 'customtables'),
			'typeparams' => esc_html__('Type Parameters', 'customtables'),
			'isrequired' => esc_html__('Required', 'customtables'),
			'table' => esc_html__('Table', 'customtables'),
			'id' => esc_html__('Id', 'customtables')
		);
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @return array
	 * @since 1.1.0
	 *
	 */
	protected function get_sortable_columns(): array
	{
		return array(
			'fieldname' => array('fieldname', false),
			'type' => array('type', false),
			'id' => array('id', true)
		);
	}

	/**
	 * Generates clickable tools in the first column "fieldname" like delete, trash, edit.
	 *
	 * @param array $item The table row item.
	 *
	 * @return string The table row with the clickable tools added.
	 */
	function column_fieldname(array $item): string
	{
		$url = 'admin.php?page=customtables-fields';
		if ($this->current_status !== null) {
			$url .= '&status=' . $this->current_status;
		}

		$actions = [];
		if ($this->current_status === 'trash') {
			$actions['restore'] = sprintf('<a href="' . $url . '&action=restore&table=%s&field=%s&_wpnonce=%s">' . esc_html__('Restore') . '</a>',
				$this->tableId,
				$item['id'],
				urlencode(wp_create_nonce('restore_nonce'))
			);

			$actions['delete'] = sprintf('<a href="' . $url . '&action=delete&table=%s&field=%s&_wpnonce=%s">' . esc_html__('Delete Permanently') . '</a>',
				$this->tableId,
				$item['id'],
				urlencode(wp_create_nonce('delete_nonce'))
			);
		} else {
			$actions['edit'] = sprintf('<a href="?page=customtables-fields-edit&action=edit&table=%s&field=%s">' . esc_html__('Edit') . '</a>',
				$this->tableId,
				$item['id']
			);

			$actions['trash'] = sprintf('<a href="' . $url . '&action=trash&table=%s&field=%s&_wpnonce=%s">' . esc_html__('Trash') . '</a>',
				$this->tableId,
				$item['id'],
				urlencode(wp_create_nonce('trash_nonce'))
			);
		}

		return sprintf('%1$s %2$s', $item['fieldname'], $this->row_actions($actions));
	}

	/**
	 * Text displayed when no fields found
	 *
	 * @return void
	 * @since   1.0.0
	 *
	 */
	public function no_items(): void
	{
		esc_html_e('No fields found.', 'customtables');
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	function column_default($item, $column_name)
	{
		switch ($column_name) {
			case 'fieldtitle':
			case 'type':
			case 'typeparams':
			case 'isrequired':
			case 'table':
			case 'id':
			case 'fieldname':
				return $item[$column_name];
			default:
				return print_r($item, true);
		}
	}

	/**
	 * Get value for checkbox column.
	 *
	 * The special 'cb' column
	 *
	 * @param object $item A row's data
	 * @return string Text to be placed inside the column <td>.
	 */
	function column_cb($item): string
	{
		return sprintf(
			'<input type="checkbox" name="field[]" value="%s" />',
			$item['id']
		);
	}

	public function views()
	{
		$views = $this->get_views();

		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'class' => array()
			)
		);

		if (!empty($views)) {
			echo '<ul class="subsubsub">';
			foreach ($views as $view) {
				echo '<li>' . wp_kses($view, $allowed_html) . '</li>';
			}
			echo '</ul>';
		}
	}

	public function get_views(): array
	{
		$link = 'admin.php?page=customtables-fields&table=' . $this->tableId;

		$views = [];

		$views['all'] = '<a href="' . admin_url($link) . '" class="' . (($this->current_status === 'all' or $this->current_status === null) ? 'current' : '') . '">'
			. esc_html__('All') . ' <span class="count">(' . $this->count_all . ')</span></a>';

		if ($this->count_published > 0)
			$views['published'] = '<a href="' . admin_url($link . '&status=published') . '" class="' . ($this->current_status === 'published' ? 'current' : '') . '">'
				. esc_html__('Published') . ' <span class="count">(' . $this->count_published . ')</span></a>';

		if ($this->count_unpublished > 0)
			$views['unpublished'] = '<a href="' . admin_url($link . '&status=unpublished') . '" class="' . ($this->current_status === 'unpublished' ? 'current' : '') . '">'
				. esc_html__('Draft') . ' <span class="count">(' . $this->count_unpublished . ')</span></a>';

		if ($this->count_trashed > 0)
			$views['trash'] = '<a href="' . admin_url($link . '&status=trash') . '" class="' . ($this->current_status === 'trash' ? 'current' : '') . '">' . esc_html__('Trash')
				. ' <span class="count">(' . $this->count_trashed . ')</span></a>';

		return $views;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 * @since    1.0.0
	 *
	 */
	public function get_bulk_actions(): array
	{
		/*
		 * on hitting apply in bulk actions the url params are set as
		 * ?action=action&field=1
		 *
		 * action and action2 are set based on the triggers above or below the table
		 *
		 */

		$actions = [];

		if ($this->current_status != 'trash')
			$actions['customtables-fields-edit'] = __('Edit');

		if ($this->current_status == '' or $this->current_status == 'all') {
			$actions['customtables-fields-publish'] = __('Publish', 'customtables');
			$actions['customtables-fields-unpublish'] = __('Draft', 'customtables');
		} elseif ($this->current_status == 'unpublished')
			$actions['customtables-fields-publish'] = __('Publish', 'customtables');
		elseif ($this->current_status == 'published')
			$actions['customtables-fields-unpublish'] = __('Draft', 'customtables');

		if ($this->current_status != 'trash')
			$actions['customtables-fields-trash'] = __('Move to Trash');

		if ($this->current_status == 'trash') {
			$actions['customtables-fields-restore'] = __('Restore');
			$actions['customtables-fields-delete'] = __('Delete Permanently');
		}
		return $actions;
	}

	/**
	 * Process actions triggered by the user
	 *
	 * @throws Exception
	 * @since    1.0.0
	 *
	 */
	function handle_field_actions()
	{
		// check for individual row actions
		$the_table_action = esc_html(wp_strip_all_tags($this->current_action()));

		if ('restore' === $the_table_action) {

			// verify the nonce.
			if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'restore_nonce')) {
				$this->invalid_nonce_redirect();
			} else {
				$fieldId = common::inputGetInt('field');

				$whereClauseUpdate = new MySQLWhereClause();
				$whereClauseUpdate->addCondition('id', $fieldId);

				try {
					database::update('#__customtables_fields', ['published' => 0], $whereClauseUpdate);
				} catch (Exception $e) {
					common::enqueueMessage($e->getMessage());
				}

				common::enqueueMessage('Field restored from the Trash.','notice');
				$this->graceful_redirect();
			}
		}

		if ('trash' === $the_table_action) {

			// verify the nonce.
			if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'trash_nonce')) {
				$this->invalid_nonce_redirect();
			} else {
				$fieldId = common::inputGetInt('field');

				$whereClauseUpdate = new MySQLWhereClause();
				$whereClauseUpdate->addCondition('id', $fieldId);

				try {
					database::update('#__customtables_fields', ['published' => -2], $whereClauseUpdate);
				} catch (Exception $e) {
					common::enqueueMessage($e->getMessage());
				}

				common::enqueueMessage('Field moved to the Trash.','notice');
				$this->graceful_redirect();
			}
		}

		if ('delete' === $the_table_action) {
			// verify the nonce.
			if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'delete_nonce')) {
				$this->invalid_nonce_redirect();
			} else {

				$fieldId = common::inputGetInt('field');

				if ($fieldId !== null) {

					try {
						Fields::deleteField_byID($this->ct, $fieldId);
					} catch (Exception $e) {
						common::enqueueMessage($e->getMessage());
					}

					common::enqueueMessage('Field permanently deleted.','notice');
					$this->graceful_redirect();
				} else {
					common::enqueueMessage(__("Field not selected.", 'customtables'));
				}
			}
		}

		// check for field bulk actions
		try {
			if ($this->is_table_action('customtables-fields-edit'))
				$this->handle_field_actions_edit();

			if ($this->is_table_action('customtables-fields-publish'))
				$this->handle_field_actions_publish(1);

			if ($this->is_table_action('customtables-fields-unpublish') or $this->is_table_action('customtables-fields-restore'))
				$this->handle_field_actions_publish(0);

			if ($this->is_table_action('customtables-fields-trash'))
				$this->handle_field_actions_publish(-2);

			if ($this->is_table_action('customtables-fields-delete'))
				$this->handle_field_actions_delete();
		} catch (Exception $e) {
			common::enqueueMessage($e->getMessage());
		}
	}

	/**
	 * Die when the nonce check fails.
	 *
	 * @return void
	 * @since    1.0.0
	 *
	 */
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
	 * Stop execution, redirect and exit
	 *
	 * @param ?string $url
	 *
	 * @return void
	 * @since    1.0.0
	 *
	 */
	public function graceful_redirect(?string $url = null)
	{
		if ($url === null)
			$url = 'admin.php?page=customtables-fields&table=' . $this->tableId;

		$paged = common::inputGetInt('paged');
		if ($paged !== null) {
			$url .= '&paged=' . $paged;
		}

		if ($this->current_status != null)
			$url .= '&status=' . $this->current_status;

		ob_start(); // Start output buffering
		ob_end_clean(); // Discard the output buffer
		wp_redirect(admin_url($url));
		exit;
	}

	function is_table_action($action): bool
	{
		$action1 = common::inputPostCmd('action', '', 'bulk-' . $this->_args['plural']);
		$action2 = common::inputPostCmd('action2', '', 'bulk-' . $this->_args['plural']);

		if ($action1 === $action || $action2 === $action)
			return true;

		return false;
	}

	function handle_field_actions_edit()
	{
		if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural'])) {
			$this->invalid_nonce_redirect();
		} else {
			$field_id = isset($_POST['field']) ? intval($_POST['field'][0]) : '';
			// Redirect to the edit page with the appropriate parameters
			$this->graceful_redirect('admin.php?page=customtables-fields-edit&action=edit&table=' . $this->tableId . '&field=' . $field_id);
		}
	}

	/**
	 * @throws Exception
	 */
	function handle_field_actions_publish(int $state): void
	{
		// verify the nonce.
		if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural'])) {
			$this->invalid_nonce_redirect();
		} else {

			$fields = (isset($_POST['field']) && is_array($_POST['field'])) ? common::sanitize_post_field_array($_POST['field']) : [];

			foreach ($fields as $field) {

				$whereClauseUpdate = new MySQLWhereClause();
				$whereClauseUpdate->addCondition('id', (int)$field);

				database::update('#__customtables_fields', ['published' => $state], $whereClauseUpdate);
			}

			if (count($fields) > 0)
				$this->graceful_redirect();

			common::enqueueMessage(__("Field not selected.", 'customtables'));
		}
	}

	/**
	 * @throws Exception
	 */
	function handle_field_actions_delete()
	{
		if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural'])) {
			$this->invalid_nonce_redirect();
		} else {

			$fields = (isset($_POST['field']) && is_array($_POST['field'])) ? common::sanitize_post_field_array($_POST['field']) : [];

			if (count($fields) > 0) {
				foreach ($fields as $fieldId)
					Fields::deleteField_byID($this->ct, $fieldId);

				$this->graceful_redirect();
			}
			common::enqueueMessage(__("Field not selected.", 'customtables'));
		}
	}

	/**
	 * Process tasks triggered by the user
	 *
	 * @since    1.0.0
	 *
	 */
	function handle_field_tasks()
	{

	}
}
