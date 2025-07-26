<?php

namespace CustomTablesWP\Inc\Core;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTablesWP as CTWP;
use CustomTablesWP\Inc\Admin as Admin;
use CustomTablesWP\Inc\Frontend as Frontend;

class Init
{
	protected string $plugin_name;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_base_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_basename;

	protected $version;

	/**
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */

	// define the core functionality of the plugin.
	public function __construct()
	{
		$this->plugin_name = CTWP\PLUGIN_NAME;
		$this->version = CTWP\PLUGIN_VERSION;
		$this->plugin_basename = CTWP\PLUGIN_BASENAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Loads the following required dependencies for this plugin.
	 *
	 * - Loader - Orchestrates the hooks of the plugin.
	 * - Internationalization_i18n - Defines internationalization functionality.
	 * - Admin - Defines all hooks for the admin area.
	 * - Frontend - Defines all hooks for the public side of the site.
	 *
	 * @access    private
	 */
	private function load_dependencies()
	{
		$this->loader = new Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Internationalization_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Internationalization_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * Callbacks are documented in inc/admin/class-admin.php
	 *
	 * @access    private
	 */
	private function define_admin_hooks(): void
	{
		$plugin_admin = new Admin\Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		//Add a top-level admin menu for our plugin
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');

		//when a form is submitted to admin-post.php
		$this->loader->add_action('admin_post_nds_form_response', $plugin_admin, 'the_form_response');

		//when a form is submitted to admin-ajax.php
		$this->loader->add_action('wp_ajax_nds_form_response', $plugin_admin, 'the_form_response');
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version()
	{
		return $this->version;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Frontend\Frontend($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}
}
