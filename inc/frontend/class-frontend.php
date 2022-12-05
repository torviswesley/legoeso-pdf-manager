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
	private $_localized_json_file = false;

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
	public $datatable_views = [];

	/**
	 * Specifies minimum required access capabilities 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      String    $legoeso_min_capability
	 */
	private $legoeso_min_capability;

	/**
	 * Specifies the tablename to be used with db queries 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      String    $legoeso_db_tablename
	 */
	private $legoeso_db_tablename;

	/**
	 * holds maximum records to return from query 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      String    $max_records_limit
	 */
	private $max_records_limit;

	/**
	 * holds the seed used to generate the nonce 
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      String    $wpnonce_seed
	 */
	private $wpnonce_seed;


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
		$this->legoeso_min_capability = 'read'; // minimum default WP user capability
		$this->max_records_limit = 2000;
		$this->wpnonce_seed = 'legoeso_pdf';

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
		wp_enqueue_style('dashicons');
		wp_enqueue_style( 'legoeso-frontend-styles-jquery-ui', plugin_dir_url( __FILE__ ) .'css/jquery-ui.min.css');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		//	enque frontend ui scripts
		wp_enqueue_script( 'legoeso-frontend-js-datatables',  plugin_dir_url( __DIR__) . 'frontend/js/datatables.js', array('jquery'), '', true);
		wp_enqueue_script( 'legoeso-frontend', plugin_dir_url( __DIR__) . 'frontend/js/legoeso-frontend.js', array( 'jquery' ), $this->version, true);
	
		// when everything goes well with the json file, pass it to javascript
		$this->set_localize_json_file();
	}

	/**
	 * Returns the current seed specified for the WP nonce
	 * @since 1.2.2
	 */
	public function get_nonce_seed(){
		return $this->wpnonce_seed;
	}
	/**
	 * 
	 * returns the minmum capability set
	 * @since 1.2.2
	 * 
	 */
	private function get_min_capability(){
		return $this->legoeso_min_capability;
	}
	/** *******************************************************************
	 * Begin Methods for Shorcodes
	 * @uses legoeso_shortcode()
	 * @uses get_tableview()
	 * @uses generate_shortcode_listview()
	 * @uses generate_datatable_json_data()
	 * @uses load_legoeso_shortcodes()
	 **********************************************************************/

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

		// types = tableview, document_preview, listview
		$sc_atts = shortcode_atts(['category' => '', 'type' => 'tableview', 'pdf_id' => null], $attributes, $tag);

		// get the specified category or use default
		$category = sanitize_text_field($sc_atts['category']);
		//
		$type = sanitize_text_field($sc_atts['type']);

		$docids = $sc_atts['pdf_id']; // document ids are sanitized later on

		// obtain a count of datatable views to be generated
		$_view_count = absint(count($this->get_datatable_views())) + 1;

		$datatable_id = "legoeso_{$type}_".$_view_count;

		// generate datatable object information
		$datatable_array = [
			'view_type' 	=> 	$type, 
			'view' 			=> 	$_view_count,
			'category'		=>	$category,
			'view_doc_url' 	=> 	home_url(), 
			'table_id' 		=> 	$datatable_id, 
		];
 
		if($type == "listview"){
			// get the list of documents
			return $this->generate_shortcode_listview( $docids, "legoeso_listview_".$_view_count, $this->get_listview_type($attributes));
		}

		// set/add object data to be used by DataTables to generate view
		$this->add_datatable_view($datatable_array);

		// localize javascript dataTable object
		$this->set_localize_json_file();

		// collect and return the datatable to the page
		return $this->get_tableview($datatable_id, $category);
    }

	/**
	 * builds the HTML table template used to generate the datatables
	 * @param array 	$_datatable_array
	 * @param string 	$category
	 * @since 1.2.0
	 * @return string
	 */
	public function get_tableview($_tableid, $category){
		
		// update custom class to fix table formating
		$datatable_template = 	'<div class="legoeso-dt-container ">';
		$datatable_template .=	'<div class="legoeso-table-header"><i class="dashicons dashicons-editor-table"></i>Category: ';
		$datatable_template .=	(!empty($category)) ? esc_html(strtoupper($category)) : 'All Documents';
		$datatable_template .=	'</div><table id="'.esc_attr($_tableid).'" class="stripe hover" style="width:100%"></table></div>';
		return $datatable_template;
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
	 public function generate_shortcode_listview($doc_ids, $listview_id, $type = 'unordered'){
		if(!empty($doc_ids)){
			// parse document ids submitted by user
			$docs = explode(',', $doc_ids);
			// trim the user submitted ids
			// removes white space near beginning and end of string
			array_walk($docs,  function(&$ids){
				$ids = sanitize_text_field(trim($ids));
			});
			
			// specify valid list types.
			$valid_types = ['unordered' => 'u', 'ordered' => 'o'];

			$list_type = (array_key_exists($type, $valid_types)) ? $valid_types[$type] : $valid_types['unordered'];

			// get a list of document ids
			$doc_ids = (count($docs) > 1) ? implode(", ", $docs) : $doc_ids;

			//  specify the columns
			$columns = array('ID', 'pdf_doc_num', 'filename', 'category', 'upload_userid', 'date_uploaded',);

			// build an SQL query
			$sql_query = "SELECT `".implode("`,`", $columns)."` ";
			$sql_query .= "FROM  ".$this->get_db_tablename()." WHERE pdf_doc_num IN ({$doc_ids})";

			$_results = $this->get_query_results($sql_query, ARRAY_A);
			$db_results = $_results['db_results'];

			$icon_class = '';
			$list_class = '';
			if($list_type == 'u'){
				$list_class = 'legoeso_ulist';
				$icon_class = '<i class="dashicons dashicons-pdf"></i>';
			}

			$html_list =  wp_kses_post('<'.$list_type.'l id="'.$listview_id.'" name="'.$listview_id.'" class="'.$list_class.'">');
			foreach($db_results as $data){
				$query_args_view_pdfdoc = array(
					'action'	=>	'view_document',
					'pid'		=>	base64_encode(serialize($data)),
					'nonce'	=>	wp_create_nonce( $this->get_nonce_seed() ),
				);

				$pdf_link = esc_url( add_query_arg( $query_args_view_pdfdoc, home_url($data['filename']) ) );
				$html_list .= wp_kses_post('<li>'.$icon_class.'<a target="_blank" href="' . $pdf_link . '">'. __($data['filename'] , $this->plugin_text_domain ) . '</a> <br></li>');
			}
			$html_list .= wp_kses_post('</'.$list_type.'l>') ;
			return $html_list;
		}
    }
	
	/**
	 * Queries the WP database, returns results, and generates json file
	 * 
	 * @since	1.0.2
	 * @param	$category
	 * @return	boolean
	 */
	public function generate_datatable_json_data($category = '', $limit = ''){

		// initialize array values
		$json_columns = [];
		$results = [];

		//  columns to include in query
		$columns = array('ID', 'image_url', 'filename', 'category', 'upload_userid', 
		'date_uploaded', 'text_data', 'metadata');
		
		// build SQL query
		$order_by = " ORDER BY date_uploaded DESC";
		$_limit = (! empty($limit) ) ? "LIMIT {$limit} " : "";
		$sql_filter = (!empty($category) || $category != '') ? " WHERE category = '{$category}'" : '';

		$sql_query  = "SELECT `".implode("`,`", $columns)."` ";
		$sql_query .= "FROM ".$this->get_db_tablename()." {$sql_filter}{$order_by} $_limit;";

		// get query results from database
		$_results = $this->get_query_results($sql_query, ARRAY_N );
		
		// add
		$results['data'] = $_results['db_results'];
		//$results['columns'] = $columns; // Adds pdf_image column to list of column names for json file

		$num_of_rows = $_results['num_rows'];
		//$json_filename = (empty($category)) ? 'default' : $category;

		// $this->pdf_DebugLog("Method: pdm_set_display_docs_json_data(): Json File Query: Rows Found {$num_of_rows}::", wp_json_encode($sql_query));

		if (empty($_results['error']) && ($num_of_rows > 0) ){
			
			return $results;
			//return $this->create_pdm_json_file($results, $json_filename, $num_of_rows);

		} 
		else {
			$this->pdf_DebugLog("Error: {$num_of_rows} Records Found for category: '{$category}'. See SQL Query below::", $sql_query);
			//return $this->create_pdm_json_file($results, $json_filename, $num_of_rows);
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
			$_result = $wpdb->get_results($query, $ResultArrayType);
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
	// public function create_pdm_json_file($objDataset, $_json_file, $row_count){

	// 	if (!is_array($objDataset) || empty($_json_file)){
	// 		return false;
	// 	}
	// 	$file_created = false;

	// 	$objDataset = array('data' => $objDataset['data'], 'columns' =>  $objDataset['columns']);
	// 	$json_file = preg_replace('[\x20]','_',  strtolower($_json_file)).'.json';
		
	// 	// build path to the json file that store the data
	// 	$json_file_dir = plugin_dir_path(__DIR__).'frontend/data/';
	// 	$json_file_path = $json_file_dir.$json_file;

	// 	if (!is_dir($json_file_dir)) {
	// 		if(mkdir($json_file_dir, 0777)){
	// 			$this->pdf_DebugLog("Dir Created::", $json_file_dir);
	// 		}
	// 		else{ $this->pdf_DebugLog("Error Creating Dir::", $json_file_dir); }
	// 	}
		
	// 	// if file exists delete and recreate the file
	// 	if (file_exists( $json_file_path )){
	// 		unlink( $json_file_path );
	// 	}

	// 	//  attempt to write the contents of the data to the file location in JSON format
	// 	if( $f_bytes = file_put_contents($json_file_path , json_encode($objDataset) ) ){
			
	// 		$this->pdf_json_file = $json_file;
	// 		$file_created = true;
	// 		$this->pdf_DebugLog("Json File Size: {$f_bytes}::", $json_file);

	// 	} else {
	// 		$this->pdf_DebugLog("Failed to write Json File Path::", $json_file_path);
	// 	}

	// 	// if there was an error encoding json file add message to debug
	// 	if ((json_last_error() !== JSON_ERROR_NONE)){
	// 		$this->pdf_DebugLog("Json last Error::", json_last_error().' Message:'.json_last_error_msg());
	// 	}
	// 	return $file_created;
	// }

	/**
	 * Returns the type to use with listview shortcode
	 *
	 * @since   1.2.1
	 * @return	String
	 */
	private function get_listview_type($atts){
		$valid_types = ['ordered', 'unordered'];
		$re = array_intersect($atts, $valid_types);
		// return first match
		if(isset($re[0])){ return $re[0];}
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
	 * Returns number of listview objects generated from shortcodes by the class 
	 * 
	 * @since	1.0.4
	 */
	private function get_datatable_views(){
		return $this->datatable_views;
	}
	
	/**
	 * Sets/ add listview objects generated from shortcodes by the class 
	 * 
	 * @since	1.2.2
	 */
	private function add_datatable_view($tableview){
		if(is_array($tableview)){
			array_push($this->datatable_views, $tableview);
			return true;
		}
	}
	/**
	 * returns the current mysql query result limit for shortcode queries
	 * 
	 * @since	1.2.1
	 * @return Int
	 */

	private function get_record_limit(){
		return $this->max_records_limit;
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
			'ajax_url'			=>	admin_url( 'admin-ajax.php'),
			'action'			=>	'show_data',
			'datatable_views'	=>	$this->get_datatable_views(),
			'nonce'				=>	wp_create_nonce( $this->get_nonce_seed() ),
		);
	
		//  Add local JavaScript data objects to datatable script
		//	check to see if the property pdf_json_file is empty if so return and do nothing
		if(  $this->_localized_json_file = wp_localize_script('legoeso-frontend','legoesodata', $pdm_args ) ){
		}

	}

	/**
	 * Setup ajax handler retreives json data to be used with shortcode
	 * 
	 * @since 1.2.2
	 * 
	 */
	public function legoeso_frontend_ajax_handler(){
		//check_ajax_referer($this->get_nonce_seed(),'nonce', true );

		$view_category = sanitize_text_field(urldecode($_GET['category']));
		
		//	generate json data to be displayed in DataTable 
		$json_data = $this->generate_datatable_json_data($view_category, $this->get_record_limit());
		die (wp_json_encode($json_data));

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
			$wpdb_table = $this->get_db_tablename();
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
		 * generates and returns document headers to display the pdf content
		 * 
		 * @since 1.2.0
		 * @return $pdf_contents
		 */
		function generate_pdf_document($pdf_contents){
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="index.php"');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Accept-Ranges: bytes'); 
			if(ob_get_contents()){
				ob_clean();
				flush();
			} 
			return $pdf_contents;
		}

        if($pdf_file_id){
			// get the data for the document using the ID
			$pdf_results = $this->db_get_pdf_data($pdf_file_id); 

			if(is_array($pdf_results)){
				$filename = $pdf_results[0]['filename'];
				$has_path = $pdf_results[0]['has_path'];
				$pdf_path = $pdf_results[0]['pdf_path'];
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
		$is_login = isset($_GET['login']) ? sanitize_text_field($_GET['login']): '';
		if( isset($_GET['action']) && $_GET['action'] == 'view_document' &&  $is_login != 1) {
			
			// if user is not logged in redirect to login page
			if(is_user_logged_in()) {	
				
				$pid = (isset($_GET['pid']) && strlen($_GET['pid']) > 10) ? $this->get_doc_id($_GET['pid']) : null;
				
				if( !empty($pid) && is_numeric($pid) && wp_verify_nonce($_GET['nonce'], 'legoeso_pdf')){
					$dt = $this->display_pdf_document($pid);
				}
				else {
					wp_safe_redirect(home_url("index.php/404"), 302, "WP Legoeso PDF Manager");
					die();
				}
			}
			else {
				$_redirect_referer = (isset($_SERVER['HTTP_REFERER'])) ? urlencode($_SERVER['HTTP_REFERER']): home_url("index.php");
				// build redirect query string
				$redirect_to = add_query_arg(array('login' => urlencode('1'), 'redirect_to' => $_redirect_referer, ), home_url("wp-login.php") );
				wp_safe_redirect( $redirect_to, 302, "WP Legoeso PDF Manager");
				die();
			}
		} 
	}

	/**
	 * returns the document id for the givem query string
	 * 
	 * @since 1.2.1
	 * @param String query_uri query string containing document information 
	 */
	private function get_doc_id($query_uri){
		if(isset($query_uri)){
			try{
				// decodes json web token
				$base =  base64_decode($query_uri) ;
				// regex pattern to search for admin object
				$admin_re = '/^[\w]:\d:{[\w\D]+}$/';
				// regex pattern to search for frontend object
				$front_re = '/^\[{1}[\w\D]+\]{1}$/';
				// regex pattern to search for frontend object
				$front_re2 = '/^{[\d\W\w]*}$/';

				if( preg_match($admin_re, $base) == 1){
					$doc_obj = unserialize($base);
					$doc_key = sanitize_key(abs($doc_obj['ID']) );
				} elseif(preg_match($front_re, $base) == 1 ){
					$doc_data = json_decode($base);
					if(json_last_error() == JSON_ERROR_NONE){
						$doc_key = isset($doc_data[0]) ? sanitize_key($doc_data[0]) : '';
					}
					else {
						// log the error
						$this->pdf_DebugLog("Failed getting document id: [1]", json_last_error());
						return false;
					}
				} elseif ( preg_match($front_re2, $base) == 1) {
					$doc_data = get_object_vars( json_decode($base) );
					if(json_last_error() == JSON_ERROR_NONE){
						$doc_key = isset($doc_data) ? sanitize_key($doc_data[0]) : '';
					}
					else {
						// log the error
						$this->pdf_DebugLog("Failed getting document id: [2]", json_last_error());
						return false;
					}
				}
				return $doc_key;
			}
			catch(\E_NOTICE | \ERROR $e){
				$this->pdf_DebugLog("Failed getting document id: PHP Error [3]", $e);

				return false;
			}
		}
		return false;
	} 

	/**
	 * @since 1.2.0	redirects users to previous page after login
	 * @todo resolve blank rediret when no reffer is present
	 */
	public function legoeso_redirect($redirect_to, $request, $user){
		return $redirect_to;
	}
}


