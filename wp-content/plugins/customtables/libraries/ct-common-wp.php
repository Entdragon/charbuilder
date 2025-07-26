<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2024. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

use DateTime;
use DateTimeZone;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class common
{
	const CSS_Classes = [
		'btn' => 'wp-block-button__link wp-element-button', // Default button
		'btn-primary' => 'button-primary', // Primary button
		'btn-secondary' => 'button-secondary', // Secondary button
		'btn-success' => 'button-primary', // Success button (mapped to primary)
		'btn-danger' => 'button-link', // Danger button (alternative mapping)
		'btn-warning' => null, // Warning button (no direct equivalent)
		'btn-info' => null, // Info button (no direct equivalent)
		'btn-light' => 'button-link', // Light button (alternative mapping)
		'btn-dark' => null, // Dark button (no direct equivalent)
		'btn-link' => 'button-link', // Link button
		'btn-lg' => 'is-large', // Large button (block editor size)
		'btn-sm' => 'is-small', // Small button (block editor size)
		'btn-block' => 'is-fullwidth', // Block-level button (full width)
		'icon-delete' => 'dashicons dashicons-dismiss', // Delete icon
		'form-select' => 'wp-block-select', // Form select (custom mapping)
		'form-control' => 'wp-block-input', // Form control (custom mapping)
		'form-group' => 'wp-block-group', // Form group (custom mapping)
		'form-check' => 'wp-block-checkbox', // Form check (custom mapping)
		'form-check-input' => 'wp-block-checkbox__input', // Form check input (custom mapping)
		'form-check-label' => 'wp-block-checkbox__label', // Form check label (custom mapping)
		'form-inline' => 'wp-block-inline', // Form inline (custom mapping)
	];

	public static function convertClassString(string $class_string): string
	{
		$classes = explode(' ', $class_string);
		$newClasses = [];

		foreach ($classes as $class)
			$newClasses [] = self::CSS_Classes[$class] ?? $class;//if(isset(self::CSS_Classes[$class]))

		return implode(' ', $newClasses);
	}

	static function enqueueMessage(string $text, string $type = 'error'): void
	{
		if ($type == 'notice')
			set_transient('customtables_success_message', $text, 60);
		elseif ($type == 'error')
			set_transient('customtables_error_message', $text, 60);
	}

	public static function inputPostString($parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		$source = wp_strip_all_tags($_POST[$parameter]);
		return sanitize_text_field($source);
	}

	public static function inputPostFloat($parameter, ?float $default, string $action): ?float
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		// Only use the first floating point value
		return (float)$_POST[$parameter];
	}

	public static function inputGetFloat($parameter, ?float $default = null): ?float
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Only use the first floating point value
		return (float)$_GET[$parameter];
	}

	public static function inputPostInt($parameter, ?int $default, string $action): ?int
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		return (int)$_POST[$parameter];
	}

	public static function inputPostUInt($parameter, ?int $default, string $action): ?int
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		return (int)$_POST[$parameter];
	}

	public static function inputGetUInt($parameter, ?int $default = null): ?int
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		return (int)$_GET[$parameter];
	}

	public static function inputPostCmd(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce')) {
				if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
					return null;
			}
		}

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		if (!isset($_POST[$parameter]))
			return $default;

		$result = (string)preg_replace('/[^A-Z\d_.-]/i', '', sanitize_key($_POST[$parameter]));
		return ltrim($result, '.');
	}

	public static function inputGetCmd(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') && !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter])) {
			return $default;
		}

		// Then apply our custom regex that allows both upper and lowercase letters
		$result = (string)preg_replace('/[^A-Za-z\d_.-]/i', '', $_GET[$parameter]);

		return ltrim($result, '.');
	}

	public static function inputPostRaw(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		return stripslashes($_POST[$parameter]);
	}

	public static function inputGetRow(string $parameter, ?string $default = null)
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		return stripslashes($_GET[$parameter]);
	}

	public static function inputPostBase64(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		if (!isset($_POST[$parameter]))
			return $default;

		// Allow a-z, 0-9, slash, plus, equals.
		return (string)preg_replace('/[^A-Z\d\/+=]/i', '', sanitize_text_field($_POST[$parameter]));
	}

	public static function inputGetBase64(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Allow a-z, 0-9, slash, plus, equals.
		return (string)preg_replace('/[^A-Z\d\/+=]/i', '', sanitize_text_field($_GET[$parameter]));
	}

	public static function inputGetWord(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Only allow characters a-z, and underscores
		return (string)preg_replace('/[^A-Z_]/i', '', sanitize_text_field($_GET[$parameter]));
	}

	public static function inputPostAlnum(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		if (!isset($_POST[$parameter]))
			return $default;

		// Allow a-z and 0-9 only
		return (string)preg_replace('/[^A-Z\d]/i', '', sanitize_text_field($_POST[$parameter]));
	}

	public static function inputGetAlnum(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Allow a-z and 0-9 only
		return (string)preg_replace('/[^A-Z\d]/i', '', sanitize_text_field($_GET[$parameter]));
	}

	public static function inputPostArray($parameter, $default = null)
	{
		if (isset($_POST[$parameter])) {

			if (is_array($_POST[$parameter])) {
				$values = [];
				foreach ($_POST[$parameter] as $value)
					$values[] = sanitize_text_field(wp_strip_all_tags($value));

				return $values;
			} else {
				return [wp_strip_all_tags($_POST[$parameter])];
			}
		} else
			return $default;
	}

	public static function inputSet(string $parameter, string $value): void
	{
		echo 'common::inputSet not supported in WordPress';
	}

	public static function inputFiles(string $fileId, string $action)
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return null;
		}

		if (!isset($_FILES[$fileId])) {
			return null;
		}

		$files = [];
		$fileData = $_FILES[$fileId];

		for ($i = 0; $i < count($fileData['name']); $i++) {
			$files[] = [
				'name' => $fileData['name'][$i],
				'full_path' => $fileData['full_path'][$i] ?? $fileData['name'][$i],  // fallback for older PHP versions
				'type' => $fileData['type'][$i],
				'tmp_name' => $fileData['tmp_name'][$i],
				'error' => $fileData['error'][$i],
				'size' => $fileData['size'][$i]
			];
		}

		return $files;
	}

	public static function inputCookieSet(string $parameter, $value, $time, $path, $domain): void
	{
		die('common::inputCookieSet not supported in WordPress');
	}

	public static function inputCookieGet($parameter)
	{
		die('common::inputCookieGet not supported in WordPress');
	}

	/**
	 * @throws Exception
	 */
	public static function inputServer($parameter, $default = null, $filter = null)
	{
		if (!class_exists('CustomTables\ctProHelpers'))
			return 'Please install Custom Tables Pro.';

		if (!method_exists('CustomTables\ctProHelpers', 'inputServer')) {
			throw new Exception(__('Please install Custom Tables Pro.', 'customtables'));
		}

		return ctProHelpers::inputServer($parameter, $default, $filter);
	}

	public static function folderList(string $directory): ?array
	{
		$folders = [];
		$directoryLength = strlen($directory);

		if ($directory > 0 and $directory[$directoryLength - 1] !== DIRECTORY_SEPARATOR)
			$directoryLength += 1;

		if (is_dir($directory)) {
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

			foreach ($iterator as $item) {
				if ($item->isDir())
					$folders[] = substr($item->getPathname(), $directoryLength);
			}
		} else {
			// Handle the case when $directory is not a valid directory
			// You can throw an exception, return an error message, etc.
			return null;
		}
		return $folders;
	}

	public static function escape($var)
	{
		if ($var === null)
			$var = '';

		if (strlen($var) > 50) {
			// use the helper htmlEscape method instead and shorten the string
			return self::htmlEscape($var, 'UTF-8', true);
		}
		// use the helper htmlEscape method instead.
		return self::htmlEscape($var);
	}

	public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		if (self::checkString($var)) {
			// Encode special characters to HTML entities
			$encoded = htmlentities($var, ENT_COMPAT, $charset);

			// Decode HTML entities to their corresponding characters
			$decoded = html_entity_decode($encoded, ENT_COMPAT, $charset);

			// Remove any potential scripting or dangerous content
			$string = wp_strip_all_tags($decoded);

			if ($shorten) {
				return self::shorten($string, $length);
			}
			return $string;
		} else {
			return '';
		}
	}

	public static function checkString($string): bool
	{
		if (isset($string) && is_string($string) && strlen($string) > 0) {
			return true;
		}
		return false;
	}

	public static function shorten($string, $length = 40, $addTip = true)
	{
		if (self::checkString($string)) {
			$initial = strlen($string);
			$words = preg_split('/([\s\n\r]+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			$words_count = count((array)$words);

			$word_length = 0;
			$last_word = 0;
			for (; $last_word < $words_count; ++$last_word) {
				$word_length += strlen($words[$last_word]);
				if ($word_length > $length) {
					break;
				}
			}

			$newString = implode(array_slice($words, 0, $last_word));
			$final = strlen($newString);
			if ($initial != $final && $addTip) {
				$title = self::shorten($string, 400, false);
				return '<span class="hasTip" title="' . $title . '" style="cursor:help;">' . trim($newString) . '...</span>';
			} elseif ($initial != $final && !$addTip) {
				return trim($newString) . '...';
			}
		}
		return $string;
	}

	public static function ctJsonEncode($argument): string
	{
		return wp_json_encode($argument);
	}

	public static function ctStripTags($argument): string
	{
		return wp_strip_all_tags($argument);
	}

	public static function getReturnToURL(bool $decode, $default, string $action): ?string
	{
		$returnto = self::inputGetBase64('returnto');

		if ($returnto === null) {
			$returnto = self::inputPostBase64('returnto', $default, $action);
			if ($returnto === null)
				return null;
		}

		if ($decode) {
			return base64_decode($returnto);

			/* TODO: future optional method
			// Construct the session variable key from the received returnto ID
			$returnto_key = 'returnto_' . $returnto_id;

			// Retrieve the value associated with the returnto key from the session
			$session = JFactory::getSession();
			return $session->get($returnto_key, '');
			*/
		} else
			return $returnto;
	}

	public static function inputGetInt(string $parameter, ?int $default = null): ?int
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !\wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		$value = sanitize_key($_GET[$parameter] ?? null);

		if ($value === null)
			return $default;

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		preg_match('/-?\d+/', (string)$value, $matches);
		return @ (int)$matches[0];
	}

	public static function makeReturnToURL(string $currentURL = null): ?string
	{
		if ($currentURL === null)
			$currentURL = self::curPageURL();

		return base64_encode($currentURL);
	}

	/*
	public static function makeReturnToURL(string $currentURL = null): ?string
	{
		if ($currentURL === null) {
			// Get the current URL
			$currentURL = self::curPageURL();
		}

		// Generate a unique identifier for the session variable
		$returnto_id = uniqid();

		$returnto_key = 'returnto_' . $returnto_id;

		// Start the session (if not started already)
		if (!headers_sent() and !session_id()) {
			session_start();
		}

		// Set the session variable using the generated ID as the key
		$_SESSION[$returnto_key] = $currentURL;

		return $returnto_id;
	}
	*/

	public static function curPageURL(): string
	{
		$WebsiteRoot = str_replace(site_url(), '', home_url());
		$RequestURL = $_SERVER["REQUEST_URI"];//important not to escape the URL characters

		if ($WebsiteRoot !== '' && str_ends_with($WebsiteRoot, '/')) {
			if ($RequestURL !== '' && $RequestURL[0] === '/') {
				$WebsiteRoot = rtrim($WebsiteRoot, '/');
			}
		}

		return $WebsiteRoot . $RequestURL;
	}

	public static function UriRoot(bool $pathOnly = false, bool $addTrailingSlash = false): string
	{
		if ($pathOnly)
			$url = site_url();
		else
			$url = home_url();

		if (strlen($url) > 0 and $url[strlen($url) - 1] == '/')
			$url = substr($url, 0, strlen($url) - 1);

		if ($addTrailingSlash and ($url == "" or $url[strlen($url) - 1] != '/'))
			$url .= '/';

		return $url;
	}

	public static function getServerParam(string $param): string
	{
		return sanitize_text_field($_SERVER[$param]);
	}

	public static function inputGet(string $parameter, $default, string $filter)
	{
		echo 'common::inputGet not supported in WordPress';
		return null;
	}

	public static function ctParseUrl($argument)
	{
		return wp_parse_url($argument);
	}

	public static function generateRandomString(int $length = 32): string
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++)
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		//$randomString .= $characters[\wp_rand(0, $charactersLength - 1)];

		return $randomString;
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function saveString2File(string $filePath, string $content)
	{
		global $wp_filesystem;

		if (!function_exists('WP_Filesystem')) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		WP_Filesystem();

		if (!$wp_filesystem)
			throw new Exception('Unable to initialize WP_Filesystem.');

		try {
			$result = $wp_filesystem->put_contents($filePath, $content);

			if (!$result) {
				throw new Exception('Failed to write content to file.');
			}

		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public static function getStringFromFile(string $filePath): ?string
	{
		global $wp_filesystem;

		if (!function_exists('WP_Filesystem')) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		WP_Filesystem();

		if (!$wp_filesystem) {
			return 'Unable to initialize WP_Filesystem.';
		}

		try {
			$content = $wp_filesystem->get_contents($filePath);

			if ($content === false) {
				throw new Exception('Failed to read content from file.');
			}

		} catch (Exception $e) {
			return $e->getMessage();
		}

		return $content;
	}

	/**
	 * @throws Exception
	 */
	public static function default_timezone_set(): void
	{
		$timezone_string = get_option('timezone_string');

		if ($timezone_string) {
			$timezone_object = new DateTimeZone($timezone_string);
			$current_time = new DateTime(null, $timezone_object);
			$offset = $timezone_object->getOffset($current_time);
		}
	}

	public static function getWhereParameter($field): string
	{
		$list = self::getWhereParameters();

		if ($list === null)
			return '';

		foreach ($list as $l) {
			$p = explode('=', $l);
			$fld_name = str_replace('_t_', '', $p[0]);
			$fld_name = str_replace('_r_', '', $fld_name); //range

			if ($fld_name == $field and isset($p[1]))
				return $p[1];
		}
		return '';
	}

	protected static function getWhereParameters(): ?array
	{
		$value = self::inputGetString('where');
		if ($value !== null) {
			$b = urldecode($value);
			$b = str_replace(' or ', ' and ', $b);
			$b = str_replace(' OR ', ' and ', $b);
			$b = str_replace(' AND ', ' and ', $b);
			return explode(' and ', $b);
		}
		return null;
	}

	public static function inputGetString($parameter, $default = null)
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		return sanitize_text_field($_GET[$parameter]);
	}

	public static function sanitize_post_field_array($input, string $type = 'int'): array
	{
		$sanitized_array = [];
		foreach ($input as $item) {
			// Ensure the item is an integer and meets any other criteria you have
			if ($type == 'int')
				$sanitized_item = intval($item);
			elseif ($type == 'string')
				$sanitized_item = sanitize_text_field($item);
			else
				$sanitized_item = null;

			$sanitized_array[] = $sanitized_item;
		}
		return $sanitized_array;
	}

	public static function filterText(?string $text): string
	{
		if ($text === null)
			return '';

		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'b' => array(),
			'strong' => array(),
			'em' => array(),
			'i' => array(),
			'u' => array(),
			'br' => array(),
			'p' => array(),
			'ul' => array(),
			'ol' => array(),
			'li' => array(),
			'blockquote' => array(),
			'pre' => array(),
			'code' => array(),
			'div' => array(
				'style' => array()
			),
			'hr' => array(
				'style' => array()
			),
		);

		return wp_kses($text, $allowed_html);
	}

	/**
	 * Redirect user to a specified URL with optional notification message
	 *
	 * @param string $link The URL to redirect to
	 * @param string|null $message Optional message to display after redirect
	 * @param bool $success Whether the message is a success (true) or error (false) notification
	 * @return void
	 */
	public static function redirect(string $link, ?string $message = null, bool $success = true): void
	{
		if ($message !== null)
			common::enqueueMessage($message, $success ? 'notice' : 'error');

		wp_safe_redirect(esc_url_raw($link));
		exit;
	}

	public static function loadJSAndCSS(Params $params, Environment $env): void
	{
	}

	public static function formatDate(?string $date = null, ?string $format = 'Y-m-d H:i:s', ?string $emptyValue = 'Never'): ?string
	{
		if ($format === null)
			$format = 'Y-m-d H:i:s';

		if ($date === null or $date == '0000-00-00 00:00:00')
			return $emptyValue;

		// Check if the date is a Unix timestamp
		if (ctype_digit($date) && strlen($date) === 10) {
			// Treat it as a Unix timestamp
			$timestamp = (int)$date;
		} else {
			// Convert date string to Unix timestamp
			$timestamp = strtotime($date);
		}

		// Return timestamp as string if requested
		if ($format === 'timestamp')
			return (string)$timestamp;

		// Format date using date_i18n for localization (WordPress function)
		return date_i18n($format, $timestamp);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function currentDate(string $format = 'Y-m-d H:i:s'): string
	{
		$timezone_string = get_option('timezone_string');

		if (!empty($timezone_string)) {
			$timezone = new DateTimeZone($timezone_string);
			$timezone_name = $timezone->getName();
		} else {
			$timezone_offset = get_option('gmt_offset');
			$hours = intval($timezone_offset); // Extracting hours
			$minutes = abs(($timezone_offset - $hours) * 60); // Extracting minutes

			// Format the offset
			$timezone_name = sprintf("%+03d:%02d", $hours, $minutes);
		}

		$date = new DateTime(); // Get current date and time
		$timezone = new DateTimeZone($timezone_name); // Get WordPress site timezone
		$date->setTimezone($timezone); // Set timezone

		// Format the date and time as a string in the desired format
		return $date->format($format);
	}

	public static function clientAdministrator(): bool
	{
		//returns true when called from the back-end / administrator
		return is_admin();
	}

	public static function setUserState(string $key, $value)
	{
		if (!session_id()) {
			session_start();
		}
		$_SESSION[$key] = $value;
	}

	public static function getUserState($key, $default = null)
	{
		if (!session_id()) {
			session_start();
		}
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	public static function getLocalizeScriptArray()
	{
		return [
			'COM_CUSTOMTABLES_JS_SELECT_RECORDS' => __(esc_html__("Please select records first", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_SELECT_DO_U_WANT_TO_DELETE1' => __(esc_html__("Do you want to delete selected record?", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_SELECT_DO_U_WANT_TO_DELETE' => __(esc_html__("Do you want to delete %s records?", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_NOTHING_TO_SAVE' => __(esc_html__("Nothing to save. Check Edit From layout.", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_SESSION_EXPIRED' => __(esc_html__("Session expired. Please login again.", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_SELECT' => __(esc_html__("Select", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_SELECT_NOTHING' => __(esc_html__("No items to select", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_ADD' => __(esc_html__("Add New", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_REQUIRED' => __(esc_html__("%s required.", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_NOT_SELECTED' => __(esc_html__("%s not selected.", "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_EMAIL_INVALID' => __(esc_html__('The %s "%s" is not a valid Email.', "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_URL_INVALID' => __(esc_html__('The %s "%s" is not a valid URL.', "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_SECURE_URL_INVALID' => __(esc_html__('The %s "%s" must be secure - must start with "https://".', "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_SIGNATURE_REQUIRED' => __(esc_html__('Please provide a signature first.', "customtables"), 'customtables'),
			'COM_CUSTOMTABLES_JS_HOSTNAME_INVALID' => __(esc_html__('The value "%s" in the "%s" field must match "%s".', "customtables"), 'customtables')
		];
	}

	public static function getSiteName()
	{
		return get_bloginfo('name');
	}

	public static function getEmailFromName()
	{
		return get_option('blogname');
	}

	public static function getMailFrom()
	{
		return get_option('admin_email');
	}

	static public function sendEmail($email, $emailSubject, $emailBody, $isHTML = true, $attachments = array()): bool
	{
		$headers = array();

		// Set HTML email if requested
		if ($isHTML) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}

		// Set from name and email
		$headers[] = 'From: ' . self::getEmailFromName() . ' <' . self::getMailFrom() . '>';

		try {
			$send = wp_mail(
				$email,
				$emailSubject,
				$emailBody,
				$headers,
				$attachments
			);
		} catch (Exception $e) {
			$msg = $e->getMessage();
			self::enqueueMessage($msg);
			return false;
		}

		if ($send !== true) {
			return false;
		}

		return true;
	}

	public static function getTransientMessages(string $transient): array
	{
		$messages = [];
		$messages_transient = get_transient($transient);
		delete_transient($transient);

		if (is_array($messages_transient))
			$messages = $messages_transient;
		elseif (!empty($messages_transient))
			$messages [] = $messages_transient;

		return $messages;
	}

	public static function showTransient(array $errors, array $messages): void
	{
		if (count($errors) > 0) {
			echo '<div class="notice notice-error is-dismissible"><ul>';
			foreach ($errors as $error)
				echo '<li>' . esc_html($error) . '</li>';

			echo '</ul></div>';
		}

		if ($messages > 0) {
			$allowed_html = array(
				'a' => array(
					'href' => array(),
					'title' => array(),
					'download' => array(),
					'target' => array()
				)
			);

			foreach ($messages as $msg)
				echo '<div id="message" class="updated notice is-dismissible"><p>' . wp_kses($msg, $allowed_html) . '</p></div>';
		}
	}
}
