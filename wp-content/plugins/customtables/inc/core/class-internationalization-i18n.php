<?php

namespace CustomTablesWP\Inc\Core;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Internationalization_i18n
{

	/**
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'customtables',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
