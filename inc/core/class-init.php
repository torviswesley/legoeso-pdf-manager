<?php

namespace Legoeso_PDF_Manager\Inc\Core;
use Legoeso_PDF_Manager as NS;
use Legoeso_PDF_Manager\Inc\Admin as Admin;
use Legoeso_PDF_Manager\Inc\Frontend as Frontend;
use Legoeso_PDF_Manager\Inc\Common as Common;

/**
 * The core plugin class.
 * Defines internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @link       https://www.legoeso.com
 * @since      1.0.0
 *
 * @author     Torvis Wesley
 */
class Init extends Common\Utility_Functions {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_base_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_basename;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The text domain of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $plugin_text_domain;

	// define the core functionality of the plugin.
	public function __construct() {

		$this->plugin_name = NS\PLUGIN_NAME;
		$this->version = NS\PLUGIN_VERSION;
		$this->plugin_basename = NS\PLUGIN_BASENAME;
		$this->plugin_text_domain = NS\PLUGIN_TEXT_DOMAIN;
		$this->plugin_dependencies = $this->load_dependencies();
		
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
	private function load_dependencies() {
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
	private function set_locale() {

		$plugin_i18n = new Internationalization_i18n( $this->plugin_text_domain );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * Callbacks are documented in inc/admin/class-admin.php
	 * 
	 * @access    private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin\Admin( 
			$this->get_plugin_name(), 
			$this->get_version(), 
			$this->get_plugin_text_domain(),
		);

		// enque admin styles and scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//Add a top-level admin menu for our plugin
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		/**
		* Implement action hooks for the ajax Callbacks
		*/
		// when pdm_inline_quick_edit form is submitted to admin-ajax.php
		$this->loader->add_action( 'wp_ajax_pdm_inline_quick_edit', $plugin_admin, 'pdm_inline_quick_edit');
		
		// action wp_ajax, call back when PDF files are submitted via form to admin-ajax.php
		//when a form is submitted to admin-post.php
		$this->loader->add_action( 'wp_ajax_file_upload_handler', $plugin_admin, 'ajax_upload_handler');
		
		// Action wp_ajax, for the fetching the PDF doc list table structure for the first time
		$this->loader->add_action( 'wp_ajax__ajax_pdm_display_callback', $plugin_admin, '_ajax_pdm_display_callback');

		// Action wp_ajax, for fetch ajax_response
		$this->loader->add_action( 'wp_ajax__ajax_fetch_pdm_history_callback', $plugin_admin, '_ajax_fetch_pdm_history_callback' );
		
		// Action wp_ajax, for file upload status
		$this->loader->add_action( 'wp_ajax_file_upload_status', $plugin_admin, '_file_upload_status_callback' );

		// Filter for WP Cron scheuled tasks
		$this->loader->add_filter('cron_schedules', $plugin_admin, 'legoeso_add_cron_interval');

		// Action hook to invoke WP scheduled task
		$this->loader->add_action( 'legoeso_cron_hook', $plugin_admin, 'legoeso_cron_cleanup');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_public_hooks() {

		$plugin_public = new Frontend\Frontend( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain());

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'load_legoeso_shortcodes' );
		$this->loader->add_action('wp_ajax_show_data', $plugin_public, 'legoeso_frontend_ajax_handler');
		
		// Add filters
		$this->loader->add_filter( 'init', $plugin_public, 'add_legoeso_viritual_pages' );

		$this->loader->add_filter( 'login_redirect', $plugin_public, 'legoeso_redirect', 10, 3);
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * Retrieve the text domain of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The text domain of the plugin.
	 */
	public function get_plugin_text_domain() {
		return $this->plugin_text_domain;
	}	

}
