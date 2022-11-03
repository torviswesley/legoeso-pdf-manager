<?php

namespace Legoeso_PDF_Manager\Inc\Frontend;

use Legoeso_PDF_Manager as NS;
use Legoeso_PDF_Manager\Inc\Common as Common;

/**
 * The public-facing functionality for Legoeso PDF Manager plugin.
 *
 * @link       https://www.legoeso.com
 * @since      1.0.2
 *
 * @author    Torvis Wesley
 */
class Frontend extends Common\Utility_Functions {

	/**
	 * The name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The name of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
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
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @var      boolean    $_localize_json_file
	 */
	private $_localized_json_file;

	/**
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $pdf_json_file
	 */
	private $pdf_json_file;	

	/**
	 *  used to store datatable object information/data which will be localized for JavaScript access
	 * 
	 * @since    1.0.4
	 * @access   public
	 * @var      array    $datatable_views
	 */
	static $datatable_views = [];	

	/**
	 * Specifies minimum required access capabilities 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      string    $pdm_required_cap
	 */
	private $pdm_required_cap;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since		1.0.0
	 * @param		string $plugin_name       	The name of this plugin.
	 * @param		string $version    			The version of this plugin.
	 * @param		string $plugin_text_domain	The text domain of this plugin
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain) {

		$this->plugin_name = $plugin_name;
		$this->version =  $version;
		$this->plugin_text_domain = $plugin_text_domain;
		$this->pdm_required_cap = 'read';
		// add and load the shortcodes
		$this->load_pdm_short_codes();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
				
		// load stylesheets
		wp_enqueue_style( 'legoeso-frontend-styles', plugin_dir_url( __FILE__ ) . 'css/pdf-doc-manager-frontend.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'legoeso-frontend-styles-datatable', plugin_dir_url( __FILE__ ) .'css/dataTables.jqueryui.min.css');
		//wp_enqueue_style( 'legoeso-frontend-styles-datatables', plugin_dir_url( __FILE__ ) .'css/datatables.min.css');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		//	enque frontend ui scripts
		wp_enqueue_script( 'legoeso-frontend-js-datatables',  plugin_dir_url( __DIR__) . 'frontend/js/datatables.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script( 'legoeso-frontend-js', plugin_dir_url( __DIR__) . 'frontend/js/legoeso-frontend.js', array( 'jquery' ), $this->version, true);
	
		// when everything goes well with the json file, pass it to javascript
		$this->set_localize_json_file();
	}


	/** *******************************************************************
	 * Begin Methods for Shorcodes
	 * @uses pdm_short_code_list_pdf_docs()
	 * @uses pdm_set_display_docs_json_data()
	 * @uses create_pdm_json_file()
	 * @uses load_pdm_short_codes
	 **********************************************************************/
	/**
	 * Callback for shortcode - processes the user specified shortcode and displays a list of documents 
	 * within the calling page
	 * 
	 * @since	1.0.2
	 * @param	string $attributes
	 * @return	string buffering 
	 */
	public function pdm_short_code_list_pdf_docs($attributes, $content, $shortcode_tag)
    {

		// check to see if the user specified a category otherwise use default and a display all categories
		$category = (!empty($attributes) && isset($attributes['category']) ) ? $attributes['category'] : '';
		// obtain datatable view object for storage of displayed view data

		// update number of datatable views generated
		$_view_count = absint(count($this->get_datatable_views())) + 1;
		

		// determines which list view to display to the user.  Default view is partials-pdf-frontend-listview.php
		if($shortcode_tag ==  "legoeso_document_preview")
		{		
			//	generate json data to be displayed in DataTable 
			$this->pdm_set_display_docs_json_data($category, '1000', true);
			// generate datatable object id
			$pdm_datatable_id = "legoeso_datatable_preview_".$_view_count;
			$_view_type = 'preview';
		}
		else {
			//	generate json data to be displayed in DataTable 
			$this->pdm_set_display_docs_json_data($category, '');
			// generate datatable object id
			$pdm_datatable_id = "legoeso_datatable_listview_".$_view_count;
			$_view_type = 'listview';
		}

		// set/ add object data for datatable view generated
		self::$datatable_views[] = array('view_type' => $_view_type, 
			'view' => $_view_count, 'view_doc_url' => home_url('index.php'), 
			'table_id' => $pdm_datatable_id, 'data_filename' => $this->get_json_file());

		ob_start();
			include  plugin_dir_path( __FILE__). 'views/partials-pdf-frontend-listview.php';
		return ob_get_clean();
    }
	/**
	 * Callback for shortcode - processes the user specified shortcode and displays a list of documents 
	 * within the calling page
	 * 
	 * @since	1.0.4
	 * @param	string $attributes
	 * @return	string buffering 
	 */
	public function pdm_short_code_show_document($attributes)
    {
		
		$document_id = $attributes['pdf_id'];
		if(!empty($document_id)){

			// parse document ids submitted by user
			$docs = explode(',', $document_id);
			// trim the user submitted ids
			// removes white space near beginning and end of string
			array_walk($docs,  function(&$ids){
				$ids = trim($ids);
			});
			
			$doc_ids = (count($docs) > 1) ? implode(", ", $docs) : $document_id;

			/**
			 * Let's grab the WordPress database global object we will use it to add the data to the short code in the plugin
			 */
			global $wpdb;
			$wpdb->show_errors;

			//  specify the columns
			$columns = array('ID', 'pdf_doc_num', 'filename', 'category', 'upload_userid', 'date_uploaded');

			// build an SQL query
			$sql_query = "SELECT `".implode("`,`", $columns)."` ";
			$sql_query .= "FROM `{$wpdb->prefix}legoeso_file_storage` WHERE pdf_doc_num IN ({$doc_ids})";

			$document_data = $wpdb->get_results($sql_query, OBJECT);

			ob_start();
			echo '<ul id="" name="" class="pdm-document-view">';
			foreach($document_data as $data){
				$query_args_view_pdfdoc = array(
					'action'	=>	'view_document',
					'pid'	=>	absint( $data->ID),
					'_wpnonce'	=>	wp_create_nonce( 'view_pdf_file_nonce' ),
				);
			
				$view_pdf_doc_meta_link = esc_url( add_query_arg( $query_args_view_pdfdoc, home_url('index.php') ) );

				echo '<li>';
				echo '<a target="_blank" href="' . $view_pdf_doc_meta_link . '">'. __($data->filename , $this->plugin_text_domain ) . '</a> <br>';
				echo '</li>';
			}
			echo '</ul>';
			return ob_get_clean();
		}

    }
	
	/**
	 * Queries the WP database and returns the results
	 * 
	 * @since	1.0.2
	 * @param	$category
	 * @return	boolean
	 */
	public function pdm_set_display_docs_json_data($category, $limit, $get_pdf_image = false){

		/**
		 * Let's grab the WordPress database global object we will use it to add the data to the short code in the plugin
		 */
		global $wpdb;
		$wpdb->show_errors;
		$json_columns = [];
		$results = [];
		//  specify the columns
		$columns = array('ID', 'filename', 'category', 'upload_userid', 'date_uploaded');
		$json_columns = $columns;

		$sql_filter = (!empty($category) || $category != '') ? " WHERE category = '{$category}'" : '';
		$order_by = "ORDER BY date_uploaded DESC";
		$_limit = (! empty($limit) ) ? "LIMIT {$limit} " : "";

		// build an SQL query
		$sql_query = "SELECT ";
		// Base64 encode image data column
		if($get_pdf_image) { $sql_query .= " TO_BASE64(`pdf_image`), "; $json_columns = array_merge(array('pdf_image'), $columns );} 

		$sql_query .= "`".implode("`,`", $columns)."` ";

		// hard coding a limit to prevent Fatal error: Allowed memory size xxx bytes exhausted error
		$sql_query .= "FROM `{$wpdb->prefix}legoeso_file_storage` {$sql_filter}{$order_by} $_limit;";

		$_results = $wpdb->get_results($sql_query, ARRAY_N);
		// add
		$results['data'] = $_results;
		$results['columns'] = $json_columns;

		$num_of_rows = $wpdb->num_rows;
		$json_filename = (empty($category)) ? 'default' : $category;

		$this->pdf_DebugLog("Method: pdm_set_display_docs_json_data(): Json File Query: Rows Found {$num_of_rows}::", wp_json_encode($sql_query));

		if ($num_of_rows > 0){
			return $this->create_pdm_json_file($results, $json_filename, $num_of_rows);
		} 
		else {
			$this->pdf_DebugLog("Error: No Records Found. See also Short Code SQL Query::", $sql_query);
			return $this->create_pdm_json_file($results, $json_filename, $num_of_rows);
		}
	}

	/**
	 * Obtains $wpdb sql resultset, convert to json and writes the file to the directory specified.
	 * 
	 * @since 1.0.2
	 * @param array 	$objDataset
	 * @param string	$json_filename
	 * @return none
	 */
	public function create_pdm_json_file($objDataset, $_json_file, $row_count){

		if (!is_array($objDataset) || empty($_json_file)){
			return false;
		}

		$objDataset = array('data' => $objDataset['data'], 'columns' =>  $objDataset['columns']);
		$json_file = preg_replace('[\x20]','_',  strtolower($_json_file)).'.json';
		
		// build path to the json file that store the data
		
		$json_file_dir = plugin_dir_path(__DIR__).'frontend/data/';
		$json_file_path = $json_file_dir.$json_file;

		if (!is_dir($json_file_dir)) {
			if(mkdir($json_file_dir, 0777)){
				$this->pdf_DebugLog("Dir Created::", $json_file_dir);
			}
			else{
				$this->pdf_DebugLog("Error Creating Dir::", $json_file_dir);
			}
		}
		
		// if file exists delete and create the file
		if (file_exists( $json_file_path )){
			unlink( $json_file_path );
		}

		//  attempt to write the contents of the data to the file location in JSON format
		if( $f_bytes = file_put_contents($json_file_path , wp_json_encode($objDataset) ) ){
			$this->pdf_json_file = $json_file;
			$this->pdf_DebugLog("Json File Size: {$f_bytes}::", $json_file);

		} else {
			$this->pdf_DebugLog("Failed to write Json File Path::", $json_file_path);
		}

		
		//
		// if there was an error encoding json file add message to debug
		if ((json_last_error() !== JSON_ERROR_NONE)){
			$this->pdf_DebugLog("Json last Error::", json_last_error().' Message:'.json_last_error_msg());
		}
		
		
	}
	/**
	 * Register all shortcodes for this plugin.
	 *
	 * @since   1.0.2
	 * @return	none
	 */
	public function load_pdm_short_codes(){

		// add shortcode to display the list of PDF documents
		add_shortcode('legoeso_document_listview', array( $this,'pdm_short_code_list_pdf_docs') );
		// add shortcode to display the list of PDF documents
		add_shortcode('legoeso_document_preview', array( $this,'pdm_short_code_list_pdf_docs') );
		// add shortcode to display single document
		add_shortcode('legoeso_document_item', array( $this,'pdm_short_code_show_document') );
	}

	/**
	 * returns the json_file property.
	 *
	 * @since   1.0.2
	 * @return	string
	 */
	public function get_json_file(){
		return $this->pdf_json_file;
	}

	/**
	 * Returns total count of listview object created by class 
	 * 
	 * @since	1.0.4
	 */
	private function get_datatable_views(){
		return self::$datatable_views;
	}

	/**
	 * passess the json_file name to JavaScript / localizes filename
	 *
	 * @since   1.0.2
	 * @return	none
	 */
	public function set_localize_json_file(){

		// add javascript local variables 
		$pdm_args = array(
			'datatable_views'	=>	$this->get_datatable_views(),
			'json_data_url'		=>	plugin_dir_url( __DIR__) .'frontend/data/',
			'_wpnonce'			=>	wp_create_nonce( 'view_pdf_file_nonce' ),
		);


		//  Add local JavaScript data objects to datatable script
		//	check to see if the property pdf_json_file is empty if so return and do nothing
		if(  $this->_localized_json_file = wp_localize_script('legoeso-frontend-js','legoesodata', $pdm_args ) ){
			$this->pdf_DebugLog("Json File Added to JavaScript 2::", $this->_localized_json_file);
			$this->pdf_DebugLog("Json File Added to JavaScript 3::", $this->get_json_file());
		}

	}


	/**
	 * Retrieves PDF data from database using the requested ID
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  array - 404 redirect on failure
	 */
    function db_get_pdf_data($doc_id)
    {
		if($doc_id){
			global $wpdb;
			$wpdb_table = $wpdb->prefix. 'legoeso_file_storage';
			$charset_collate = $wpdb->get_charset_collate();

			$pdm_doc_query = "SELECT 
							pdf_data, filename, has_url, pdf_url
							FROM $wpdb_table WHERE ID = '$doc_id'";
		
			// query output_type will be an associative array with ARRAY_A.
			$wpdb_results = $wpdb->get_results( $pdm_doc_query, ARRAY_A  );
			
			if(count($wpdb_results) == 1){
				return $wpdb_results; 
			}
		}
		return -1;
    }

	/**
	 * Sets document Content headers to display inline PDF document.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  None - exit on failure
	 */
    function display_pdf_content($pdf_file_id = null){

        if($pdf_file_id){
			// get the data for the document using the ID
			$pdf_results = $this->db_get_pdf_data($pdf_file_id); 

			if(is_array($pdf_results)){
				$pdfContent = $pdf_results[0]['pdf_data'];
				$filename = $pdf_results[0]['filename'];
				$has_url = $pdf_results[0]['has_url'];
				$pdf_url = $pdf_results[0]['pdf_url'];

				if(empty($pdfContent) || $pdfContent == null){
					header( "Location: ".home_url('index.php')."/404?&pid={$pdf_file_id}");
				} 
				else {
					header('Content-type: application/pdf');
					header('Content-Disposition: inline; filename="'.$filename.'"');
					header('Content-Transfer-Encoding: binary');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Accept-Ranges: bytes'); 
					ob_clean();
					flush();
					die($pdfContent); 
				}
			}
			else {
				header( "Location: ".home_url('index.php')."/404?&pid={$pdf_file_id}");
			}

		}
        return;
    }	
	
	/**
	 * Register the filters for the public-facing side of the site.
	 *
	 * @since    1.2.0
	 */
	// TODO: display when users is logged in and if false display message to user or direct to login page.
	public function add_legoeso_viritual_pages(){

		if( isset($_GET['action']) &&  $_GET['action'] == 'view_document') {
			wp_verify_nonce( 'view_pdf_file_nonce', '_wpnonce');
			if(!is_user_logged_in() && !current_user_can($this->pdm_required_cap)){
				header( "Location: ".home_url('index.php')."/404");
				die();
			}		

			$pid = absint($_GET['pid']);

			if(is_numeric($pid)){
				$dt = $this->display_pdf_content($pid);
			}
			else {
				header( "Location: ".home_url('index.php')."/404?&pid={$pid}");
			}
			
			die();
		} 
	} 
}

