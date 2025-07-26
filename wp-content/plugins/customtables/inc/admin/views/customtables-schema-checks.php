<?php

namespace CharacterGeneratorDev {

/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\CT;
use CustomTables\IntegrityChecks;

try {
	$ct = new CT([],true);
	$result = IntegrityChecks::check($ct);
}catch (Exception $e) {
	$result []= $e->getMessage();
}

$allowed_html = array(
	'li' => array()
);


if (count($result) > 0)
	echo '<ol><li>' . wp_kses(implode('</li><li>', $result), $allowed_html) . '</li></ol>';
else
	echo '<p>Database table structure is up-to-date.</p>';

} // namespace CharacterGeneratorDev
