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
	 * @var      String    $plugin_name    The name of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;

	/**
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @var      Boolean    $_localize_json_file
	 */
	private $_localized_json_file;

	/**
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @var      String    $pdf_json_file
	 */
	private $pdf_json_file;	

	/**
	 *  used to store datatable object information/data which will be localized for JavaScript access
	 * 
	 * @since    1.0.4
	 * @access   public
	 * @var      Array    $datatable_views
	 */
	static $datatable_views = [];	

	/**
	 * Specifies minimum required access capabilities 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      String    $pdm_required_cap
	 */
	private $pdm_required_cap;

	/**
	 * Specifies the tablename to be used with db queries 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      String    $legoeso_db_tablename
	 */
	private $legoeso_db_tablename;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since		1.0.0
	 * @param		string $plugin_name       	The name of this plugin.
	 * @param		string $version    			The version of this plugin.
	 * @param		string $plugin_text_domain	The text domain of this plugin
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain) {
		global $wpdb;
		$this->plugin_name = $plugin_name;
		$this->version =  $version;
		$this->plugin_text_domain = $plugin_text_domain;
		$this->pdm_required_cap = 'read';		

		// set tablename for database queries
		$this->legoeso_db_tablename = "`{$wpdb->prefix}legoeso_file_storage`";
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
	 * @uses legoeso_shortcode()
	 * @uses get_tableview()
	 * @uses generate_shortcode_ulist()
	 * @uses get_document_data()
	 * @uses load_legoeso_shortcodes()
	 **********************************************************************/
	/**
	 * Callback for shortcode - processes the user specified shortcode and displays a list of documents 
	 * within the calling page
	 * 
	 * @since	1.0.2
	 * @param	string $attributes
	 * @return	string buffering 
	 */
	public function legoeso_shortcode($attributes = [], $content = null, $tag = ''){
		// normalize attribute keys, lowercase
		$attributes = array_change_key_case( (array) $attributes, CASE_LOWER );
		// types = tableview, preview_table, ulistview
		$sc_atts = shortcode_atts(['category' => '', 'type' => 'tableview', 'pdf_id' => null], $attributes, $tag);

		// get the specified category or use default
		$category = sanitize_text_field($sc_atts['category']);
		$type = sanitize_text_field($sc_atts['type']);
		$docids = $sc_atts['pdf_id'];

		// obtain a count of datatable views to be generated
		$_view_count = absint(count($this->get_datatable_views())) + 1;
		
		// determines type of table to display to the user.  Default view is partials-pdf-frontend-listview.php
		if($type ==  "preview_table")
		{		
			//	generate json data to be displayed in DataTable 
			$this->get_document_data($category, '1000', true);

			// generate datatable object id
			$datatable_array = array(
				'view_type' => 'preview', 
				'view' => $_view_count, 
				'view_doc_url' => home_url(), 
				'table_id' => "legoeso_datatable_preview_".$_view_count, 
				'data_filename' => $this->get_json_file());

				ob_start();
				// return the datable to page
				$this->get_tableview($datatable_array, $category);
				return ob_get_clean();
		}
		elseif($type  ==  "tableview") {
			//	generate json data to be displayed in DataTable 
			$this->get_document_data($category, '');
			
			// generate datatable object id
			$datatable_array = array(
				'view_type' => 'listview', 
				'view' => $_view_count, 
				'view_doc_url' => home_url(), 
				'table_id' => "legoeso_datatable_listview_".$_view_count, 
				'data_filename' => $this->get_json_file());
			
			ob_start();
				// collection and return the datatable to hte page
				$this->get_tableview($datatable_array, $category);
			return ob_get_clean();
		} 
		elseif($type == "ulistview"){
			// get the list of documents
			return $this->generate_shortcode_ulist($docids);
		}
		
    }

	/**
	 * gets the HTML table used to build the datatables
	 * @param array 	$_datatable_array
	 * @param string 	$category
	 * @since 1.2.0
	 * @return string
	 */
	public function get_tableview($_datatable_array, $category){

		// set/ add object data for datatable view generated
		self::$datatable_views[] = $_datatable_array;
		$_tableid = $_datatable_array['table_id'];
		
			return include  plugin_dir_path( __FILE__). 'views/partials-pdf-frontend-listview.php';
	}
	/**
	 * gets the plugin database tablename used by WP
	 * @since 1.2.0
	 * @return string
	 */
	private function get_db_tablename(){
		return $this->legoeso_db_tablename;
	}
	
	/**
	 * Generates an unordered list using the document ids passing as a param
	 * 
	 * @param array $doc_ids 
	 * @since 1.2.0
	 * @return string
	 */
	 public function generate_shortcode_ulist($doc_ids){
		if(!empty($doc_ids)){
			// parse document ids submitted by user
			$docs = explode(',', $doc_ids);
			// trim the user submitted ids
			// removes white space near beginning and end of string
			array_walk($docs,  function(&$ids){
				$ids = sanitize_text_field(trim($ids));
			});
			
			$doc_ids = (count($docs) > 1) ? implode(", ", $docs) : $doc_ids;

			//  specify the columns
			$columns = array('ID', 'pdf_doc_num', 'filename', 'category', 'upload_userid', 'date_uploaded');

			// build an SQL query
			$sql_query = "SELECT `".implode("`,`", $columns)."` ";
			$sql_query .= "FROM  ".$this->get_db_tablename()." WHERE pdf_doc_num IN ({$doc_ids})";

			$_results = $this->get_query_results($sql_query, OBJECT);
			$db_results = $_results['db_results'];

			$ulist_html =  wp_kses_post('<ul id="" name="" class="pdm-document-view">');
			foreach($db_results as $data){
				$query_args_view_pdfdoc = array(
					'action'	=>	'view_document',
					'pid'	=>	absint( $data->ID),
					'_wpnonce'	=>	wp_create_nonce( 'view_pdf_file_nonce' ),
				);
			
				$pdf_link = esc_url( add_query_arg( $query_args_view_pdfdoc, home_url($data->filename) ) );
				$ulist_html .= wp_kses_post('<li><a target="_blank" href="' . $pdf_link . '">'. __($data->filename , $this->plugin_text_domain ) . '</a> <br></li>');
			}
			$ulist_html .= wp_kses_post('</ul>') ;
			return $ulist_html;
		}

    }
	
	/**
	 * Queries the WP database, returns results, and generates json file
	 * 
	 * @since	1.0.2
	 * @param	$category
	 * @return	boolean
	 */
	public function get_document_data($category = '', $limit = 2000, $get_pdf_image = false){

		// initialize array values
		$json_columns = [];
		$results = [];

		//  columns to use in query
		$columns = array('ID', 'image_url', 'filename', 'category', 'upload_userid', 'date_uploaded');
		
		// build SQL query
		$order_by = " ORDER BY date_uploaded DESC";
		$_limit = (! empty($limit) ) ? "LIMIT {$limit} " : "";
		$sql_filter = (!empty($category) || $category != '') ? " WHERE category = '{$category}'" : '';

		$sql_query  = "SELECT `".implode("`,`", $columns)."` ";
		$sql_query .= "FROM ".$this->get_db_tablename()." {$sql_filter}{$order_by} $_limit;";

		// get query results from database
		$_results = $this->get_query_results($sql_query);
		
		// add
		$results['data'] = $_results['db_results'];
		$results['columns'] = $columns; // Adds pdf_image column to list of column names for json file

		$num_of_rows = $_results['num_rows'];
		$json_filename = (empty($category)) ? 'default' : $category;

		$this->pdf_DebugLog("Method: pdm_set_display_docs_json_data(): Json File Query: Rows Found {$num_of_rows}::", wp_json_encode($sql_query));

		if (empty($_results['error']) && ($num_of_rows > 0) ){
			return $this->create_pdm_json_file($results, $json_filename, $num_of_rows);
		} 
		else {
			$this->pdf_DebugLog("Error: No Records Found for category: '{$category}'. See also Short Code SQL Query::", $sql_query);
			return $this->create_pdm_json_file($results, $json_filename, $num_of_rows);
		}
	}
	
	// add config setting to all users to choose where they want to store their pdf files
	// on disk or the database
	// ensure that plugin will work when wp-content, wp-upload directories are modified during custom settings
	// 
	private function get_query_results($query, $ResultArrayType = ARRAY_N){
		if(!empty($query)){
			/**
			 * Let's grab the WordPress database global object we will use it to add the data to the short code in the plugin
			 */
			global $wpdb;
			$wpdb->show_errors;
			$_result = $wpdb->get_results($query, $ResultArrayType);
			$wpdb->flush();
			return( ['db_results' => $_result, 'num_rows' => $wpdb->num_rows, 'error' => $wpdb->last_error,] ) ;
		}
	}
	/**
	 * Obtains $wpdb sql resultset, convert to json and writes the file to the directory specified.
	 * 
	 * @since 1.0.2
	 * @param  Array 	$objDataset
	 * @param  String	$json_filename
	 * @param  Integer	$row_count
	 * @return Boolean
	 */
	public function create_pdm_json_file($objDataset, $_json_file, $row_count){

		if (!is_array($objDataset) || empty($_json_file)){
			return false;
		}
		$file_created = false;

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
		
		// if file exists delete and recreate the file
		if (file_exists( $json_file_path )){
			unlink( $json_file_path );
		}

		//  attempt to write the contents of the data to the file location in JSON format
		if( $f_bytes = file_put_contents($json_file_path , json_encode($objDataset) ) ){
			
			$this->pdf_json_file = $json_file;
			$file_created = true;
			$this->pdf_DebugLog("Json File Size: {$f_bytes}::", $json_file);

		} else {
			$this->pdf_DebugLog("Failed to write Json File Path::", $json_file_path);
		}

		//
		// if there was an error encoding json file add message to debug
		if ((json_last_error() !== JSON_ERROR_NONE)){
			$this->pdf_DebugLog("Json last Error::", json_last_error().' Message:'.json_last_error_msg());
		}
		return $file_created;
	}

	/**
	 * Register all shortcodes for this plugin.
	 *
	 * @since   1.0.2
	 * @return	none
	 */
	public function load_legoeso_shortcodes(){
		// add shortcode to display the list of PDF documents
		add_shortcode('legoeso_display_documents', [$this,'legoeso_shortcode'] );

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
			'_wpnonce'			=>	wp_create_nonce( 'legoeso_pdf' ),
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
							pdf_path, filename, has_path 
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
    function display_pdf_document( $pdf_file_id = null ){
		/**
		 * writes out the headers used to display the pdf content
		 * 
		 * @since 1.2.0
		 * @return $df_contents
		 */
		function generate_pdf_document($pdf_contents){
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="index.php"');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Accept-Ranges: bytes'); 
			ob_clean();
			flush();
			return $pdf_contents;
		}
        if($pdf_file_id){
			// get the data for the document using the ID
			$pdf_results = $this->db_get_pdf_data($pdf_file_id); 

			if(is_array($pdf_results)){
				$filename = $pdf_results[0]['filename'];
				$has_path = $pdf_results[0]['has_path'];
				$pdf_path = $pdf_results[0]['pdf_path'];
				$img_url = $pdf_results[0]['image_url'];
				// check the result if it has url data, and if sos does the file exist
				if( ($has_path == 1 && strlen($pdf_path) > 2) && file_exists($pdf_path)){
					// lets get the file and stream the results back to the browser
					if($pdf_contents = file_get_contents($pdf_path)){
						die(generate_pdf_document($pdf_contents));
					}

					// if document not found, nothing to show redirect 
					wp_safe_redirect(home_url("index.php/404"), 302, "WP Legoeso PDF Manager");
					die();
				}
				else{
					// if document not found, nothing to show redirect 
					wp_safe_redirect(home_url("index.php/404"), 302, "WP Legoeso PDF Manager");
					die();
				}
				

			}
			else {
				// if document not found, nothing to show redirect 
				wp_safe_redirect(home_url("index.php/404"), 302, "WP Legoeso PDF Manager");
				die();
			}
		}
        return;
    }	
	
	/**
	 * Register the filters for the public-facing side of the site.
	 *
	 * @since   1.2.0
	 * @return 	none
	 */
	public function add_legoeso_viritual_pages(){
		$is_login = isset($_GET['login']) ? $_GET['login']: '';
		if( isset($_GET['action']) && $_GET['action'] == 'view_document' &&  $is_login != 1) {
			
			wp_verify_nonce( 'legoeso_pdf', '_wpnonce');

			// if user is not logged in redirect to login page
			if(is_user_logged_in()) {	
				$pid = absint($_GET['pid']);
				if(is_numeric($pid)){
					$dt = $this->display_pdf_document($pid);
				}
				else {
					wp_safe_redirect(home_url("index.php/404"), 302, "WP Legoeso PDF Manager");
					die();
				}
			}
			else {
				
				// build redirect query string
				$redirect_to = add_query_arg(array('login' => urlencode('1'), 'redirect_to' => urlencode($_SERVER['HTTP_REFERER']), ), home_url("wp-login.php") );
				wp_safe_redirect( $redirect_to, 302, "WP Legoeso PDF Manager");
				die();
			}
		} 
	} 
	/**
	 * @since 1.2.0	redirects users to previous page after login
	 */
	public function legoeso_redirect($redirect_to, $request, $user){
		return $redirect_to;
	}
}


