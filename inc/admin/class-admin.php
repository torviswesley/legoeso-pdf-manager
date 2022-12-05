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
	 * Specifies minimum required access capabilities 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      string    $pdm_required_cap
	 */
	private $pdm_required_cap;

	/**
	 * Check file status call count
	 * @since	1.2.2
	 * @access	private	
	 * @var		String	$pdm_call_count
	 */
	private static $pdm_call_count = 0;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name	The name of this plugin.
	 * @param    string $version	The version of this plugin.
	 * @param	 string $plugin_text_domain	The text domain of this plugin
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;
		$this->pdm_required_cap = 'upload_files'; // specify minimum required capability
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
		// build path to current upload directory
		$pdm_upload_dir = $wp_upload_dir['path']."/legoeso_pdm_data/".time();
		$pdm_upload_status_file = $pdm_upload_dir."/".rand(01,9999999).".txt";

		$upload_dir_ars  = array(
			'wp_upload_dir'					=>	serialize($wp_upload_dir),
			'pdm_upload_dir'				=>	$pdm_upload_dir,
			'pdm_upload_status_filename'	=>	base64_encode($pdm_upload_status_file),
		);
		// return sanitized json object
		return ($upload_dir_ars);
	}
	/**
	 * WP Register the stylesheets for the admin area.
	 *
	 * @since   1.0.0
	 * @return	none
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pdf-doc-manager-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-ui-theme', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(),'', 'all' );
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
		$pdm_progress_text_path = $this->pdm_upload_dir_args;

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
			'pdm_upload_info'			=>	serialize($this->pdm_upload_dir_args),
			'wp_debug'					=>	WP_DEBUG, 

			//	action used to check the status the uploaded pdfs
			'ajax_process_uploads'	=>	'file_upload_status',

			// maximum number of files that can be uploaded via the form post
			'php_max_files_upload'		=>	$php_max_files_upload,

			// maximum total file size that can be uploaded via the form post
			'php_max_upload_size'		=>	$php_max_upload_size,

			// maximum number of bytes that can be uploaded via the form post.
			'php_post_max_size'			=>	$php_max_post_size,


		);

		//	enqueue the ajax handler for the file uploading process
		wp_enqueue_script( 'legoeso_ajax_ui', plugin_dir_url( __FILE__ ) . 'js/pdm-jquery-ui-ajax-footer.js', array( 'jquery' ), $this->version, true );

		//	enqueue the ajax handler for the file uploading process
		wp_enqueue_script( 'legoeso_ajax_list_ui', plugin_dir_url( __FILE__ ) . 'js/pdm-jquery-list-ui.js', array( 'jquery' ), $this->version, true );
		
		// enqueue function to handle jquery accordion ui
		wp_enqueue_script( 'legoeso_ajax_accordion_ui', plugin_dir_url( __FILE__ ) . 'js/pdm-jquery-ui-accordion-footer.js', array( 'jquery-ui-accordion'), $this->version, true );
		
		// enqueue function to handle jquery accordion ui
		wp_enqueue_script('jquery-ui-accordion');

		// enqueue jquery-ui-progressbar
		wp_enqueue_script('jquery-ui-progressbar');

		// send/add local values to JavaScript to communicate with JavaScript handler
		wp_localize_script( 'legoeso_ajax_ui', 'ajax_obj', $params );
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
							$this->pdm_required_cap,								// capability
							$this->plugin_name,									// menu_slug,
				array( $this, 'load_pdf_doc_list_table' ),	
							'dashicons-database-import',						// icon url
							6,													// $position - the position in the menu order this item should appear	
		);

		//	add submenu
		add_submenu_page($this->plugin_name,
				__( 'Legoeso PDF Settings', $this->plugin_text_domain ),	// page title
				__( 'Legoeso Settings', $this->plugin_text_domain ),		// menu title
				$this->pdm_required_cap,										// capability
				$this->plugin_name.'_settings',								// menu slug
				array($this, 'legoeso_show_settings'),						// cal back
		);
		// only include if user has manage_categories
		//	add submenu 
		if(current_user_can('manage_categories')){
			add_submenu_page($this->plugin_name,
					__( 'Categories', $this->plugin_text_domain ),		// page title
					__( 'Categories', $this->plugin_text_domain ),		// menu title
					$this->pdm_required_cap,								// capability
					'edit-tags.php?taxonomy=category',					// menu slug
																		// cal back
			);
		}
		/*
		 * The $page_hook_suffix can be combined with the load-($page_hook) action hook
		 * https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page) 
		 * 
		 * The callback below will be called when the respective page is loaded
		 * 		 
		 */	
		add_action( 'load-'.$page_hook, [$this, 'load_pdf_doc_list_table_screen_options'] );

		$this->pdf_DebugLog("Class: admin.php - Method: add_plugin_admin_menu()::", "Loaded...");

		// Schedule the WP Cron task
		if(! wp_next_scheduled('legoeso_cron_hook') ){
			wp_schedule_event( time(), 'weekly', 'legoeso_cron_hook');
		}

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
		$this->pdf_DebugLog("Loading::","Loaded screen options and instantiate PDF List Table");

	}

	/**
	 * Callback - Loads and displays the initial PDF Doc List Table
	 * 
	 * @since 1.0.1
	 * @return none
	 */
	public function load_pdf_doc_list_table(){
		if(is_user_logged_in() && current_user_can($this->pdm_required_cap)){
			// render and display initial List Table
			include_once( 'views/partials-pdm-display.php' );
			$this->pdf_DebugLog("Load List Table", wp_json_encode($this->pdf_list_table));
		}
	}

	/**
	 * Ajax Callback - Renders PDF Doc List Table
	 * 
	 * @since 1.0.1
	 * @return none
	 */
	public function _ajax_pdm_display_callback(){
		check_ajax_referer( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce');
		if(is_user_logged_in() && current_user_can($this->pdm_required_cap)){
			$this->pdf_list_table = new PDF_Doc_List_Table( $this->plugin_text_domain );

			$this->pdf_list_table->prepare_items();
			
			ob_start();
			$this->pdf_list_table->display();
			$display  = ob_get_clean();

			die(
				wp_json_encode(array( "display" => $display))
			);
		}
	}

	/**
	 * The progress checker file for the PHP/ Ajax Progress Bar
	 *
	 * This callback is called several times by the Ajax script, retreiving a text file in the background, 
	 * returning the status / percent compeleted of the file extraction process until all files
	 * have been processed. The text file is in JSON format which is then read by the calling JavaScript.
	 * The process is complete when the status reaches 100.  The file will be
	 * deleted when completed.  See also PDF_Doc_Core::save_progress()
	 * @since  1.2.0
	 * @return object json
	 */
	public function _file_upload_status_callback(){
		// Verify the Ajax request
		check_ajax_referer( 'ajax-pdm-doc-list-nonce', 'nonce');
		if(is_user_logged_in() && current_user_can($this->pdm_required_cap)){
			//	decode the server path for text file that contains upload status info
			//	and set the filename variable
			$status_filename = base64_decode($_REQUEST['pdm_process_text']);

			// check and get the contents from the status file
			if( file_exists($status_filename) ) {
				//	get the contents of the file.
				$status_text = file_get_contents($status_filename);
				// send the json object back the client-side JavaScript
				
				$this->pdf_DebugLog(" Refresh Called:: Time: ".time(), "");
				//	convert to JSON to read the status of the process
				$obj = json_decode($status_text);
				if($status_text){
					// finished
					$this->pdf_DebugLog(" Sent Reponse:: Time: ".time(), "");
					die($status_text);
				} else {
					// send Ajax response JSON encoded
					die(json_encode(array('status' => 'file not found.')));
				}

			}
		}
	}

	/**
	 * Callback for the ajax wp_ajax__ajax_pdm_history_callback for checking ajax response.
	 *
	 * this function is for checking ajax response.
	 * @since   1.0.0
	 * @return	none
	 */
	public function _ajax_fetch_pdm_history_callback(){
		if(is_user_logged_in() && current_user_can($this->pdm_required_cap)){
			// instantiate a new instance of the PDF_Doc_List_Table class 
			$this->pdf_list_table = new PDF_Doc_List_Table( $this->plugin_text_domain);

			// Send and display the response back to the browser
			$this->pdf_list_table->ajax_response();
		}

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
		//	verify a valid nonce
		check_ajax_referer( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce');

		if(is_user_logged_in() && current_user_can($this->pdm_required_cap)){
		
			$this->pdf_DebugLog("Beginning Upload Process ::", "ajax_upload_handler");
			
			/**
			* PDF_Doc_Core handles the upload request.
			*
			* create a new instance of the PDF document handler
			*/
			$uploadHandler = new Libraries\PDF_Doc_Core();
			// Get uploaded file(s) array from HTTP $_FILES super global variable  
			// and begin the uploading process
			$uploadHandler->process_pdf_upload($_FILES['pdm_file'], $_POST);
			die();
		}
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
	public function legoeso_show_settings(){
		if(is_user_logged_in() && current_user_can($this->pdm_required_cap)){
			// render and displays the Seettngs tabs
			include( 'views/partials-pdm-settings.php' );
		}
	}

	/**
	 * Callback hook for WP Cron scheduling, this hook will run every seven days to cleanup up the
	 * legoeso_pdm_data directory: removes expired zip files, and any unmapped files
	 * @since 1.2.2
	 */
	public function legoeso_cron_cleanup(){
		// execute clean up function
		$deleted_files = $this->legoeso_cleanup();
		//  setup the upload directory to store pdf files
		$wp_upload_dir = wp_upload_dir();

		// build path to current upload directory
		$pdm_upload_dir = $wp_upload_dir['path']."/legoeso_pdm_data/";
		if(is_dir($pdm_upload_dir)){
			$str_text = 'Legoeso PDF Manager last scheduled cleanup was ran @ '.time().', clean up successful! ';
			file_put_contents($pdm_upload_dir.'legoeso-last-scheduled-cleanup-run-'.time().'.txt', $str_text.'The following files were deleted:');
		}
	}

	/**
	 * Callback function for WP Cron scheduling interval filter that will run task 
	 * rename to legoeso_scheduled clean up
	 * @since 1.2.2
	 * 
	 */
	public function legoeso_add_cron_interval($schedules){
		// Testing Schedule

		// $schedules['five_seconds'] = array(
		// 	'interval'	=> 5,
		// 	'display'	=> esc_html__('Every Five Seconds'), );

		$schedules['weekly'] = array(
			'interval'	=> 		604800,
			'display'	=> esc_html__('Once... Weekly'), );

		return $schedules;
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
		if(is_user_logged_in() && current_user_can($this->pdm_required_cap)){
			die( $this->save_changes_pdf_quick_edit($_REQUEST) ) ;
		}
	}
}