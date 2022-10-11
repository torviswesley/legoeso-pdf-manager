<?php
namespace Legoeso_PDF_Manager\Inc\Admin;

use Legoeso_PDF_Manager as NS;
use Legoeso_PDF_Manager\Inc\Common as Common;
use Legoeso_PDF_Manager\Inc\Libraries as Libraries;


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, plugin text domain, and variable to hold 
 * list table object.
 *
 * @link       https://www.legoeso.com
 * @since      1.0.0
 *
 * @author    Torvis Wesley
 */
class Admin extends Common\Utility_Functions{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;
	
	/**
	 * Stores the pdf lisit table object
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $pdf_list_table
	 */
	private $pdf_list_table;	

	/**
	 * Stores the plugin dependencies
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $plugin_dependencies
	 */
	public $plugin_dependencies;	

    /**
	 * Specfifies and stores the upload dirctory of the processed files
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_upload_dir stores the upload dirctory of the processed files
	 */
	private $pdm_upload_dir;

    /**
	 * Store WP upload directory info
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $wp_upload_dir_args stores WP upload dirctory info
	 */
	private $wp_upload_dir_args;

    /**
	 * Specifies and stores the upload dirctory arguments for processing files
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $pdm_upload_dir_args stores the upload dirctory of the processed files
	 */
	private $pdm_upload_dir_args = null;

    /**
	 * Specifies nonce
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_nonce
	 */
	private $pdm_nonce;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name	The name of this plugin.
	 * @param    string $version	The version of this plugin.
	 * @param	 string $plugin_text_domain	The text domain of this plugin
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain, $plugin_dependencies) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;
		$this->plugin_dependencies = $plugin_dependencies;

		//set the upload directory
		$this->pdm_upload_dir_args = $this->set_upload_directory();

	}

	/**
	 * Initializes and sets the upload directory that will be uses to process all documents 
	 * and data
	 * 
	 * @since	1.0.1
	 * @return	object json with directory info
	 */
	private function set_upload_directory(){

		//  setup the upload directory to store pdf files
		$wp_upload_dir = wp_upload_dir();

		$pdm_upload_dir = $wp_upload_dir['path']."/pdm_data/".$_SERVER['REQUEST_TIME_FLOAT'];
		$pdm_upload_status_file = $pdm_upload_dir."/".rand(01,9999999).".txt";

		$upload_dir_ars  = array(
			'wp_upload_dir'					=>	$wp_upload_dir,
			'pdm_upload_dir'				=>	$pdm_upload_dir,
			'pdm_upload_status_filename'	=>	base64_encode($pdm_upload_status_file),
		);

		return wp_json_encode($upload_dir_ars);
	}
	/**
	 * WP Register the stylesheets for the admin area.
	 *
	 * @since   1.0.0
	 * @return	none
	 */
	public function enqueue_styles() {
		wp_enqueue_style('bootstrap_css', plugin_dir_url( __FILE__ ) .  'css/bootstrap.min.css' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pdf-doc-manager-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style('dashicons');
	}

	/**
	 * WP Register the JavaScript for the admin area.
	 *
	 * @since   1.0.0
	 * @return	none
	 */
	public function enqueue_scripts() {
		//	create a WordPress nonce
		$this->pdm_nonce = wp_create_nonce('ajax-pdm-doc-list-nonce');

		//	set progress/status text path to be passed to JavaScript
		$pdm_progress_text_path = json_decode($this->pdm_upload_dir_args, true);

		// get the maximum post size and file uploads from the php ini file
		// and pass it to JavaScript
		$php_max_files_upload = $this->parse_size(ini_get('max_file_uploads'));
		// max post file size
		$php_max_post_size = $this->parse_size(ini_get('post_max_size'));
		// max file upload size
		$php_max_upload_size = $this->parse_size(ini_get('upload_max_filesize'));
		// specify/set the parameters for the WP localize_script function
		// which will be passed to the JavaScript files
		$params = array ( 
			'ajax_url'					=>	admin_url( 'admin-ajax.php'), 
			'pdm_nonce'					=>  $this->pdm_nonce,
			'pdm_process_text'			=>	$pdm_progress_text_path['pdm_upload_status_filename'],
			'pdm_upload_info'			=>	$this->pdm_upload_dir_args,
			'wp_debug'					=>	WP_DEBUG, 

			// maximum number of files that can be uploaded via the form post
			'php_max_files_upload'		=>	$php_max_files_upload,

			// maximum total file size that can be uploaded via the form post
			'php_max_upload_size'		=>	$php_max_upload_size,

			// maximum number of bytes that can be uploaded via the form post.
			'php_post_max_size'			=>	$php_max_post_size,

			//	script that processes the uploaded pdfs
			'ajax_process_uploads_url'	=> plugin_dir_url( __FILE__ ) . 'pdm-ajax-process-uploads.php'
		);

		//	enque the ajax handler for the file uploading process
		wp_enqueue_script( 'pdm_ajax_script', plugin_dir_url( __FILE__ ) . 'js/pdm-jquery-ui-ajax.js', array( 'jquery' ), $this->version, true );

		//	enque the ajax handler for the file uploading process
		wp_enqueue_script( 'pdm_ajax_script_2', plugin_dir_url( __FILE__ ) . 'js/pdm-jquery-list-ui.js', array( 'jquery' ), $this->version, true );

		wp_enqueue_script( 'pdm_bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true);	
		
		// send/add local values to JavaScript to communicate with JavaScript handler
		wp_localize_script( 'pdm_ajax_script', 'ajax_obj', $params );
	}

	/**
	 * Callback for the top-level admin menu in define_admin_hooks() for class Init.
	 * 
	 * @since   1.0.0
	 * @return	none
	 */
	public function add_plugin_admin_menu() {
			
		$page_hook = add_menu_page( 
						__( 'Legoeso PDF Manager', $this->plugin_text_domain ), //page title
						__( 'Legoeso PDFs', $this->plugin_text_domain ), 		//menu title
							'manage_categories',								// capability
							$this->plugin_name,									// menu_slug,
				array( $this, 'load_pdf_doc_list_table' ),	
							'dashicons-database-import',						// icon url
							6,													// $position - the position in the menu order this item should appear	
		);

		//	add submenu
		add_submenu_page($this->plugin_name,
				__( 'Legoeso PDF Settings', $this->plugin_text_domain ),	// page title
				__( 'Legoeso Settings', $this->plugin_text_domain ),		// menu title
				'manage_categories',										// capability
				$this->plugin_name.'_settings',								// menu slug
				array($this, 'load_pdf_doc_settings'),						// cal back
		);
		
		//	add submenu 
		add_submenu_page($this->plugin_name,
				__( 'Categories', $this->plugin_text_domain ),		// page title
				__( 'Categories', $this->plugin_text_domain ),		// menu title
				'manage_categories',								// capability
				'edit-tags.php?taxonomy=category',					// menu slug
																	// cal back
		);
		
		/*
		 * The $page_hook_suffix can be combined with the load-($page_hook) action hook
		 * https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page) 
		 * 
		 * The callback below will be called when the respective page is loaded
		 * 		 
		 */	
		add_action( 'load-'.$page_hook, array( $this, 'load_pdf_doc_list_table_screen_options' ) );

		$this->pdf_DebugLog("Class: admin.php - Method: add_plugin_admin_menu()::", "Loaded...");

	}
	
	/**
	* Screen options for the List Table
	*
	* Callback for the load-($page_hook_suffix)
	* Called when the plugin page is loaded
	* 
	* @since  1.0.0
	* @return none
	*/
	public function load_pdf_doc_list_table_screen_options() {
				
		$arguments	=	array(
						'label'		=>	__( 'Documents Per Page', $this->plugin_text_domain ),
						'default'	=>	10,
						'option'	=>	'upload_per_page',
		);

		add_screen_option( 'per_page', $arguments );

		// instantiate the PDF List Table
		$this->pdf_list_table = new PDF_Doc_List_Table( $this->plugin_text_domain );
		$this->pdf_DebugLog("Method: load_pdf_doc_list_table_screen_options()::","Loaded screen options and instantiate PDF List Table");

	}

	/**
	 * Callback - Loads and displays the initial PDF Doc List Table
	 * 
	 * @since 1.0.1
	 * @return none
	 */
	public function load_pdf_doc_list_table(){

		// render and display initial List Table
		include_once( 'views/partials-pdm-display.php' );
		$this->pdf_DebugLog("Method: load_pdf_doc_list_table()::", json_encode($this->pdf_list_table));
	}

	/**
	 * Callback for the ajax wp_ajax__ajax_pdm_display_callback in define_admin_hooks() for class Init.
	 *
	 * this function is called on inital display of Wp_List_Table of PDF documents.
	 * @since  1.0.0
	 * @return object json
	 */
	public function _ajax_pdm_display_callback(){
		check_ajax_referer( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce');

		$this->pdf_list_table = new PDF_Doc_List_Table( $this->plugin_text_domain );

		$this->pdf_list_table->prepare_items();
		
		ob_start();
		$this->pdf_list_table->display();
		$display  = ob_get_clean();

		die(
			json_encode(array( "display" => $display))
		);
	}

	/**
	 * Callback for the ajax wp_ajax__ajax_pdm_history_callback for checking ajax response.
	 *
	 * this function is for checking ajax response.
	 * @since   1.0.0
	 * @return	none
	 */
	public function _ajax_fetch_pdm_history_callback(){
		// add hook_suffix back to global variable scope, bug after setting AJAX to true
		//$GLOBALS['hook_suffix'] = 'toplevel_page_pdf-doc-manager';
		
		// instantiate a new instance of the PDF_Doc_List_Table class 
		$this->pdf_list_table = new PDF_Doc_List_Table( $this->plugin_text_domain);

		// Send and display the response back to the browser
		$this->pdf_list_table->ajax_response();

	}

	/** *******************************************************************
	 * Begin Methods for PDM Document Uploads 
	 * @uses ajax_upload_handler()
	 **********************************************************************/
	/**
	 * Callback for the ajax wp_ajax_file_upload_handler in define_admin_hooks() for class Init.
	 *
	 * this function is called when the user submits a PDF file for processing.
	 * @since   1.0.0
	 * @return	none
	 */
	public function ajax_upload_handler(){
		$this->pdf_DebugLog("Medthod: ajax_upload_handler(): Fired Callback Function ::", "ajax_upload_handler");
		if( !is_user_logged_in() ){
			die("You must be logged in to use this feature!") ;	
		}		
		// Logging actions taken
		$this->pdf_DebugLog("Current user login?:", "Yes");

		//	verify a valid nonce
		check_ajax_referer( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce');

		// Logging actions taken
		$this->pdf_DebugLog("nonce:", "Valid");
		
		/**
		* PDF_Doc_Core handles the upload request.
		*
		* create a new instance of the PDF document handler
		*/
		$uploadHandler = new Libraries\PDF_Doc_Core();
		 // Get uploaded file(s) array from HTTP $_FILES super global variable  
		 // and begin the uploading process
		$uploadHandler->process_pdf_upload($_FILES['pdm_file']);
		die();
	}
		
	/** *******************************************************************
	 * Begin Method for PDM Settings
	 * includes dependency variable collection 
	 * 
	 * @uses load_pdf_doc_settings()
	 **********************************************************************/
	/**
	 * Callback - Displays the Setting for the plugin, sets and updates the dependency variables
	 * 
	 * @since	1.0.1
	 * @return	none
	 */
	public function load_pdf_doc_settings(){
		if($_POST){	
			//	manaully add checkbox element/value if not present
			if(!array_key_exists('legoeso_pytesseract_enabled', $_POST)){
				$_POST['legoeso_pytesseract_enabled'] = 'off';
			}
			if(!array_key_exists('legoeso_force_image_enabled', $_POST)){
				$_POST['legoeso_force_image_enabled'] = 'off';
			}
			
			//	update setting upon saving changes.
			$this->updateSettings($_POST);
		}
		
		/** *******************************************************************
		 * Begin collection of dependency variables
		 **********************************************************************/
		$installed_libs = $this->plugin_dependencies;
		// Logging actions taken

		$python_error = (isset($installed_libs['python_failed'])) ? $installed_libs['python_failed'] : false;
		
		// get the list of installed libraries
		$obj_libraries = $installed_libs['installed_packages'];

		// get the Python version
		$python_version = $installed_libs['python'];

		// get the Zip version
		$zip_enabled = $installed_libs['phpinfo']['Zip'];
		$zip_version = $installed_libs['phpinfo']['Zip version'];
		
		// check tesseract version
		$tesseract = $obj_libraries[0]['pytesseract'];

		// get the platform running on the server
		$sys_platform = $obj_libraries[3]['platform'];
		// get the PDF2Image version
		$pdfimage_version = $obj_libraries[1]['pdf2image'];
		// get PDFMiner version
		$pdfMinder_version = $obj_libraries[2]['pdfminer.six'];
		
		// toggle enable Pytesseract
		$cb_pyTess = $this->toggle_checkbox(get_option("legoeso_pytesseract_enabled"));
		$enable_PyTesseract_value = $cb_pyTess[0];
		$enable_PyTesseract = $cb_pyTess[1];
		
		// toggle force image only
		$cb_force_img = $this->toggle_checkbox(get_option("legoeso_force_image_enabled"));
		$force_image_enabled_value = $cb_force_img[0];
		$force_image_enabled = $cb_force_img[1];

		// verify the file path specified for he PDFMiner script exists on the server
		if(file_exists(get_option("legoeso_pdfminer_dir"))){
			$pdf2txt_detected = "OK - PDFMiner script found.";
		} else {
			$pdf2txt_detected = "File not found on server.";
		}

		// render and displays the Seettngs tabs
		include( 'views/partials-pdm-settings.php' );
	}

	/** *******************************************************************
	 * Begin Method for Updating documents using Inline Quick Edit 
	 * 
	 * 
	 * @uses pdm_inline_quick_edit()
	 **********************************************************************/
	/**
	 * Callback - Save quick edit changes
	 * 
	 * @since	1.0.3
	 * @return	none
	 */
	public function pdm_inline_quick_edit(){		
		//	verify a valid nonce
		check_ajax_referer( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce');
		die($this->updatePdfDocument($_REQUEST)) ;
	}
}