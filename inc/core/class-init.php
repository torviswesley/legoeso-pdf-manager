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

	/**
	 * Stores dependency check results
	 * @since    1.0.2
	 * @access   protected
	 * @var      object    $plugin_dependencies
	 */
	protected $plugin_dependencies;

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
		return $this->check_dependencies();
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
		// sets the error_reporting to hide notices to aviod AJAX errors. 
		error_reporting( ~E_NOTICE );

		$plugin_admin = new Admin\Admin( 
			$this->get_plugin_name(), 
			$this->get_version(), 
			$this->get_plugin_text_domain(),
			$this->get_plugin_dependencies()
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
	}

    /**
     * Setup and initialize array used for the dependency check
     * @since 1.0.1
     * @return array
     */
    private function set_dependency_arr(){
        // default array
        $installed_pkgs = array(
            array('pytesseract' => null),
            array('pdf2image' => null),
            array('pdfminer.six' => null),
            array('platform' => null),
        );

        return (array(
            'installed_packages'    => $installed_pkgs,
            'phpinfo'               => array(''),
        ));
    }

    /**
    * Checks/returns a list of required dependencies
    * @since 1.0.1
    * @plugin_dir string   directory location of the plugin
    * @return array
    */
    public function check_dependencies(){

        // initialze output buffer variables
        $arrOutput = null;
        $retval = null;

        //  initialize installed lib array
        $installed_libs = $this->set_dependency_arr();

        //  Build and exceute initial dependencies command
        $strCommand = NS\PYTHON_DIR.' '.plugin_dir_path(__DIR__).'py/check_dependencies.py';    
        exec($strCommand, $arrOutput, $retval);

		// build and execute python version check
        $pyv_command = NS\PYTHON_DIR.' --version';
        exec($pyv_command, $py_out, $rval);

        if(!is_array($arrOutput) || empty($arrOutput)){
            $installed_libs['python'] = "Python not detected or installed, or not configured incorrectly. -  code: {$retval}";
            $installed_libs['python_failed'] = true;
        } else {
            // get the put from the executed command  
            $installed_libs = json_decode($arrOutput[0], true);
            $installed_libs['python'] = (!empty($py_out[0]) ? $py_out[0]:'Not Detected');
        }

        //  Add the settings from php enviroment variables
        $phpinfo = $this->phpinfo_array();
        $installed_libs["phpinfo"] = $phpinfo['zip'];
        
        //  add relavant php.ini values
        $installed_libs['server_limits']['max_execution_time'] = $phpinfo['Core']['max_execution_time'];
        $installed_libs['server_limits']['memory_limit'] = $phpinfo['Core']['memory_limit'];
        $installed_libs['server_limits']['memory_limit']['local_bytes'] = $this->php_to_bytes($phpinfo['Core']['memory_limit']['local']);
        $installed_libs['server_limits']['memory_limit']['master_bytes'] = $this->php_to_bytes($phpinfo['Core']['memory_limit']['master']);
        $installed_libs['server_limits']['post_max_size'] = $phpinfo['Core']['post_max_size'];

        // add server information to $installed_dependencies
        $this->installed_dependencies = $installed_libs;

        // Add to debug log    
        $this->pdf_DebugLog("Class: init.php - Medthod:: check_dependencies()", json_encode($installed_libs));
        return($installed_libs);
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
	 * The path to the directory of the plugin, used to specifiy the absolute 
	 * path for included files within WordPress.
	 */
	public function get_plugin_dependencies() {
		return $this->plugin_dependencies;
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
