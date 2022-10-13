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
	protected $_localized_json_file;

	/**
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $pdf_json_file
	 */
	protected $pdf_json_file;	

	/**
	 *  used to store datatable object information/data which will be localized for JavaScript access
	 * 
	 * @since    1.0.4
	 * @access   public
	 * @var      array    $datatable_views
	 */
	static $datatable_views = [];	

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pdf-doc-manager-frontend.css', array(), $this->version, 'all' );
        wp_enqueue_style('pdm-doc-style1-css', plugin_dir_url( __FILE__ ) .'css/pmd_datatables_style_1.css');
		
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pdf-doc-manager-frontend.js', array( 'jquery' ), $this->version, false );

		//	enque the jquery ui scripts
		wp_enqueue_script( 'pdm_simple_datatables', 'https://cdn.jsdelivr.net/npm/simple-datatables@latest', array(), $this->version, true);
		wp_enqueue_script( 'pdm_datatables_jquery', plugin_dir_url( __DIR__) . 'frontend/js/pdm_jquery.datatables.js', array( ), $this->version, true);
		wp_enqueue_script( 'pdm_datatables_script', plugin_dir_url( __DIR__) . 'frontend/js/pdm_datatables.js', array( 'jquery' ), $this->version, true);
	
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
			$pdm_datatable_id = "pdm_datatable_preview_".$_view_count;
			$_view_type = 'preview';
		}
		else {
			//	generate json data to be displayed in DataTable 
			$this->pdm_set_display_docs_json_data($category, '');
			// generate datatable object id
			$pdm_datatable_id = "pdm_datatable_listview_".$_view_count;
			$_view_type = 'listview';
		}

		// set/ add object data for datatable view generated
		self::$datatable_views[] = array('view_type' => $_view_type, 'view' => $_view_count, 'table_id' => $pdm_datatable_id, 'data_filename' => $this->get_json_file());

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
			$hlink_path = $this->plugin_text_domain.'/inc/frontend/views/';
			$load_pdf_page_url =  plugins_url( $hlink_path.'load-view-pdf-doc.php');

			ob_start();
			echo '<ul id="" name="" class="pdm-document-view">';
			foreach($document_data as $data){
				$query_args_view_pdfdoc = array(
					'action'	=>	'view_pdf_doc',
					'file_id'	=>	absint( $data->ID),
					'_wpnonce'	=>	wp_create_nonce( 'view_pdf_file_nonce' ),
				);
			
				$view_pdf_doc_meta_link = esc_url( add_query_arg( $query_args_view_pdfdoc, $load_pdf_page_url ) );

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

		//  specify the columns
		$columns = array('ID', 'filename', 'category', 'upload_userid', 'date_uploaded');
		$sql_filter = (!empty($category) || $category != '') ? " WHERE category = '{$category}'" : '';
		$order_by = "ORDER BY date_uploaded DESC";
		$_limit = (! empty($limit) ) ? "LIMIT {$limit} " : "";

		// build an SQL query
		$sql_query = "SELECT ";
		// Base64 encode image data column
		if($get_pdf_image) { $sql_query .= " TO_BASE64(`pdf_image`), "; } 
		$sql_query .= "`".implode("`,`", $columns)."` ";
		// hard coding a limit to prevent Fatal error: Allowed memory size xxx bytes exhausted error
		$sql_query .= "FROM `{$wpdb->prefix}legoeso_file_storage` {$sql_filter}{$order_by} $_limit;";

		$results = $wpdb->get_results($sql_query, ARRAY_A);
		$num_of_rows = $wpdb->num_rows;
		$json_filename = (empty($category)) ? 'default' : $category;

		$this->pdf_DebugLog("Json File Query: Rows Found {$num_of_rows}::", json_encode($sql_query));

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
	public function create_pdm_json_file($objDataset, $json_filename, $row_count){

		if (!is_array($objDataset) || empty($json_filename)){
			return false;
		}

		$objDataset = array('data' => $objDataset);
		$json_filename = preg_replace('[\x20]','_',  strtolower($json_filename)).'.json';
		
		$this->pdf_json_file = $json_filename;
		$json_file_dir = plugin_dir_path(__DIR__).'frontend/data/';

		// build path to the json file that store the data
		$json_filename = $json_file_dir.$json_filename;
		$this->pdf_DebugLog("Json File Dir::", $json_file_dir);
		$this->pdf_DebugLog("Json File Path::", $json_filename);

		if (!is_dir($json_file_dir)) {
			mkdir($json_file_dir, 0777);
		}
		
		// if file exists delete and create the file
		if (file_exists( $json_filename )){
			unlink( $json_filename );
		}

		//  attempt to write the contents of the data to the file location in JSON format
		file_put_contents($json_filename , json_encode($objDataset));

		//	 when everything goes well with json file, pass it to javascript
		//$this->set_localize_json_file();
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
			'datatable_views'		=>	$this->get_datatable_views(),
			'pdm_json_datafiles_url'		=>	plugin_dir_url( __DIR__) .'frontend/data/',
			'pdm_view_pdf_url'	=>	plugin_dir_url( __DIR__) . 'frontend/views/load-view-pdf-doc.php',
		);
		//  Add local JavaScript data objects to datatable script
		//	check to see if the property pdf_json_file is empty if so return and do nothing
		if( empty( $this->_localized_json_file = wp_localize_script('pdm_datatables_script','pdm_dataobj', $pdm_args ) )){
			$this->pdf_DebugLog("Json File Added to JavaScript::", $this->get_json_file());
		}

	}
}

