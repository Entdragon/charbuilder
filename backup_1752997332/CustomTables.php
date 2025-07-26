<?php
/**
Plugin Name: CustomTables
Plugin URI: https://ct4.us
Description: Custom Tables solution for WordPress
Version: 1.6.2
Requires at least: 6.0
Requires PHP: 7.4.0
Author: Ivan Komlev
Author URI: https://ct4.us
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: customtables
Domain Path: /languages
**/

namespace CustomTablesWP;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\ProInputBoxTableJoin;
use CustomTables\Value_file;
use Exception;

/**
 * Define Constants
 */

define(__NAMESPACE__ . '\CTWP', __NAMESPACE__ . '\\');

define(CTWP . 'PLUGIN_NAME', 'customtables');
define(CTWP . 'PLUGIN_VERSION', '1.6.2');
define(CTWP . 'PLUGIN_NAME_DIR', plugin_dir_path(__FILE__));
define(CTWP . 'PLUGIN_NAME_URL', plugin_dir_url(__FILE__));
define(CTWP . 'PLUGIN_BASENAME', plugin_basename(__FILE__));
define(CTWP . 'PLUGIN_TEXT_DOMAIN', 'customtables');

$CUSTOM_TABLES_TEMPLATE = null;

/**
 * Autoload Classes
 */

$path = plugin_dir_path(__FILE__) . 'libraries' . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR;
require_once($path . 'loader.php');

CustomTablesLoader(is_admin(), true, plugin_dir_path(__FILE__));

//require_once(PLUGIN_NAME_DIR . 'inc/libraries/autoloader.php');

// Autoloader setup
require_once plugin_dir_path(__FILE__) . 'inc/libraries/autoloader.php';

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */
register_activation_hook(__FILE__, array(__NAMESPACE__ . '\Inc\Core\Activator', 'activate'));
register_deactivation_hook(__FILE__, array(__NAMESPACE__ . '\Inc\Core\Deactivator', 'deactivate'));

register_activation_hook(__FILE__, array(CTWP . 'Inc\Core\Activator', 'activate'));

/**
 * Plugin CustomTables Container
 *
 * Maintains a single copy of the plugin app object
 *
 * @since    1.0.0
 */

class customtables
{
    static $init;
    public static function init()
    {
        if (null == self::$init) {
            self::$init = new Inc\Core\Init();
            self::$init->run();
        }
        return self::$init;
    }
}

/*
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Also returns copy of the app object so 3rd party developers
 * can interact with the plugin's hooks contained within.
 *
 */

$page = common::inputGetCmd('page', '');
function customtables_init()
{
    if (is_admin()) {

        //Make sure that this is called only when Custom Tables admin section is open
        return customtables::init();
    }
    return null;
}

$min_php = '7.4.0';

// Check the minimum required PHP version and run the plugin.
if (version_compare(PHP_VERSION, $min_php, '>=')) {
    customtables_init();
}

function enqueue_codemirror()
{
    $version = '1.6.2';
    wp_enqueue_style('customtables-js-modal', plugin_dir_url(__FILE__) . 'libraries/customtables/media/css/modal.css', false, $version);
    wp_enqueue_style('customtables-js-layouteditor', plugin_dir_url(__FILE__) . 'libraries/customtables/media/css/layouteditor.css', false, $version);

    wp_enqueue_script('customtables-js-layoutwizard', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/layoutwizard.js', array(), $version, false);
    wp_enqueue_script('customtables-js-layouteditor', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/layouteditor.js', array(), $version, false);
	wp_enqueue_script('customtables-js-edit', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/edit.js', array(), $version, false);

	wp_add_inline_script('customtables-js-edit', 'if (typeof window.CTEditHelper === "undefined") {window.CTEditHelper = new CustomTablesEdit("Joomla",' . (explode('.', CUSTOMTABLES_JOOMLA_VERSION)[0]) . ',null);}');
}

if ($page == 'customtables-layouts-edit')
    add_action('admin_enqueue_scripts', 'CustomTablesWP\enqueue_codemirror');

// Function to generate real content based on block attributes
function customtables_dynamic_block_block_init()
{
	$page = common::inputGetCmd('page', '');

	if ($page == 'customtables-api-tablejoin'){
		$key = common::inputGetCmd('key');
		if ($key != '') {
			$path = CUSTOMTABLES_PRO_PATH . 'inputbox' . DIRECTORY_SEPARATOR;
			if (file_exists($path . 'tablejoin.php')) {
				require_once($path . 'tablejoin.php');
				$ct = new CT([],true);
				ProInputBoxTableJoin::renderTableJoinSelectorJSON($ct, $key);//Inputbox
			}
		}
	}

	register_block_type(
        plugin_dir_path(__FILE__) . 'build',
        array(
            'render_callback' => 'CustomTablesWP\customtables_dynamic_block_render_callback',
        )
    );
}

add_action('init', 'CustomTablesWP\customtables_dynamic_block_block_init');
// In your main CustomTables.php file, add this:
add_action('admin_init', array(CTWP . 'Inc\Core\Activator', 'update'));

/**
 * This function is called when the block is being rendered on the front end of the site
 *
 * @param array $attributes The array of attributes for this block.
 * @param string $content Rendered block output. ie. <InnerBlocks.Content />.
 */

function customtables_dynamic_block_render_callback($attributes, $content, $block_instance)
{
    global $CUSTOM_TABLES_TEMPLATE;
    ob_start();

     // Keeping the markup to be returned in a separate file is sometimes better, especially if there is very complicated markup.
     // All of passed parameters are still accessible in the file.
    require_once plugin_dir_path(__FILE__) . 'build/template.php';
    $preparedAttributes = template::prepareAttributes($attributes);
    $newHash = md5(json_encode($preparedAttributes));
    $newHashFound = false;

    if($CUSTOM_TABLES_TEMPLATE !== null and isset($CUSTOM_TABLES_TEMPLATE->blocks) and is_array($CUSTOM_TABLES_TEMPLATE->blocks)) {
        foreach ($CUSTOM_TABLES_TEMPLATE->blocks as $block) {
            if ($block['hash'] == $newHash) {
                echo $block['html'];
                $newHashFound = true;
            }
        }
    }

    if(!$newHashFound) {
        require_once plugin_dir_path(__FILE__) . 'build/template.php';

        if ($CUSTOM_TABLES_TEMPLATE !== null){
            $temp = $CUSTOM_TABLES_TEMPLATE->enqueueList;
            $CUSTOM_TABLES_TEMPLATE = new template();
            $CUSTOM_TABLES_TEMPLATE->enqueueList = $temp;
        }
        else{
            $CUSTOM_TABLES_TEMPLATE = new template();
        }

		try {
			$result = $CUSTOM_TABLES_TEMPLATE->renderBlock($attributes);
		}catch (Exception $e) {
			$result = '<p style="background-color: #aa0000;color: #f1f1f1;padding: 5px;border-radius: 5px;">'
				. 'CustomTables: '.$e->getMessage().'</p>';
		}

        echo $result;
    }

    return ob_get_clean();
}

if (common::inputGetInt('customtables') == 1) {
    $file = common::inputGetString('file');
    if ($file !== null) {
        //Display file or blob content as PHP output, modifying the http header
        $processor_file = CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'html'
            . DIRECTORY_SEPARATOR . 'value' . DIRECTORY_SEPARATOR . 'file.php';
        require_once($processor_file);

        $fileOutput = new Value_file();
        try {
            $fileOutput->process_file_link($file);
        } catch (Exception $e) {
			echo '<p style="background-color: #aa0000;color: #f1f1f1;padding: 5px;border-radius: 5px;">'
				. 'CustomTables: File content parameters error:' . $e->getMessage().'</p>';
        }

        try {
            $fileOutput->display();
        } catch (Exception $e) {
			echo '<p style="background-color: #aa0000;color: #f1f1f1;padding: 5px;border-radius: 5px;">'
				. 'CustomTables: File content display error:' . $e->getMessage().'</p>';
        }
    }
}

function customtables_enqueue_dynamic_block_assets()
{
    global $CUSTOM_TABLES_TEMPLATE;
    if ($CUSTOM_TABLES_TEMPLATE !== null)
        $CUSTOM_TABLES_TEMPLATE->enqueue_scripts();
}

add_action('wp_enqueue_scripts', 'CustomTablesWP\customtables_enqueue_dynamic_block_assets');

function your_function_to_access_post()
{
    global $post;
    global $CUSTOM_TABLES_TEMPLATE;

    if (has_block('customtables/dynamic-block', $post)) {
        require_once plugin_dir_path(__FILE__) . 'build/template.php';

        if ($CUSTOM_TABLES_TEMPLATE !== null){
            $temp = $CUSTOM_TABLES_TEMPLATE->enqueueList;
            $CUSTOM_TABLES_TEMPLATE = new template();
            $CUSTOM_TABLES_TEMPLATE->enqueueList = $temp;
        }
        else{
            $CUSTOM_TABLES_TEMPLATE = new template();
        }

        $CUSTOM_TABLES_TEMPLATE->load_blocks($post->post_content);
    }
}

add_action('wp', 'CustomTablesWP\your_function_to_access_post');

function customtables__form_generation_function($attributes): string
{
    require_once plugin_dir_path(__FILE__) . 'build/template.php';

    $temp =  new template();

	try {
		$result = $temp->renderBlock($attributes);
	}catch (Exception $e) {
		$result = '<p style="background-color: #aa0000;color: #f1f1f1;padding: 5px;border-radius: 5px;">'
			. 'CustomTables: '.$e->getMessage().'</p>';
	}

    return $result;
}

add_shortcode('customtables', function($form_attributes) {

    // Don't process shortcode in admin area
    if (is_admin()) {
        $shortcode = '[customtables';
        if (is_array($form_attributes)) {
            foreach ($form_attributes as $key => $value) {
                $shortcode .= ' ' . $key . '="' . esc_attr($value) . '"';
            }
        }
        $shortcode .= ']';
        return $shortcode;
    }

    $defaults = array(
        'id' => null,      // optional, int or string
        'view' => null,      // optional, int or string
        'table' => '',      // optional, int or string
        'layout' => null,     // optional, int or string
        'filter' => '',     // optional, string
        'order' => '',      // optional, string
        'limit' => '',      // optional, int
    );

    $attributes = shortcode_atts($defaults, $form_attributes);

    if (empty($attributes['table']) and empty($attributes['layout'])) {
        return 'Error: "table" or "layout" are parameter is required';
    }

    if (isset($attributes['view']) and !empty($attributes['view'])) {
        if ($attributes['view'] != 'edit' and $attributes['view'] != 'details' and $attributes['view'] == 'catalog')
            return 'Error: "view" parameter can be "edititem" or "details" or "catalog")';
    }

    // Sanitize inputs
    $params = array(
        'id' => sanitize_text_field($attributes['id']),
        'view' => sanitize_text_field($attributes['view']),
        'table' => sanitize_text_field($attributes['table']),
        'layout' => sanitize_text_field($attributes['layout']),
        'filter' => sanitize_text_field($attributes['filter']),
        'order' => sanitize_text_field($attributes['order']),
        'limit' => !empty($attributes['limit']) ? absint($attributes['limit']) : null
    );

	try {
		$result = customtables__form_generation_function($params);
	}catch (Exception $e) {
		return '<p style="background-color: #aa0000;color: #f1f1f1;padding: 5px;border-radius: 5px;">'
			. 'CustomTables: '.$e->getMessage().'</p>';
	}

    return $result;
});

