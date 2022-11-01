<?php
namespace Legoeso_PDF_Manager\Inc\Admin;
use Legoeso_PDF_Manager\Inc\Libraries;
use Legoeso_PDF_Manager\Inc\Common as Common;
use \ZipArchive;
/**
 * Child Class of WP_List_Table - use for displaying all documents stored within the 
 * document manager table. Extends WordPress WP_List_Table class
 * 
 *
 * @link       http://www.legoeso.com
 * @since      1.0.0
 * 
 * @author     Torvis Wesley
 */
class PDF_Doc_List_Table extends Libraries\WP_List_Table  {

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	protected $plugin_text_domain;

	/**
	 * The text database tablename of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text database tablename of this plugin.
	 */
	protected $plugin_db_tablename;

	/**
	 * Disables the document image preview
	 *
	 * @since    1.0.4
	 * @access   private
	 * @var      boolean    $disable_preview_images   Disables image preview for documents in the list table.
	 */
	protected $disable_preview_images;

    /*
	 * Call the parent constructor to override the defaults $args
	 * 
	 * @param string $plugin_text_domain	Text domain of the plugin.	
	 * 
	 * @since 1.0.0
	 */
	public function __construct( $plugin_text_domain ) {
		
		$this->plugin_text_domain = $plugin_text_domain;
		$this->disable_preview_images = false;
		$this->plugin_db_tablename = 'legoeso_file_storage'; 	// specifiy the database tablename 

		parent::__construct(  array( 
				'plural'	=>	'pdf_docs',		// Plural value used for labels and the objects being listed.
				'singular'	=>	'pdf_doc',		// Singular label for an object being listed, e.g. 'post'.
				'ajax'		=>	true,			// If true, the parent class will call the _js_vars() method in the footer	
				'screen'	=>	'toplevel_page_legoeso-pdf-manager',	// add WP screen id after settings AJAX to true	
			) );
	}	
	
	/**
	 * Prepares the list of items for displaying.
	 * 
	 * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
	 * 
	 * @since   1.0.0
	 */
	public function prepare_items() {
		
		/** 
		 * Check if a search was performed.
		*/
		$pdf_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
	
        /**
		 * Define the column headers. This includes a complete
		 */
        $columns = $this->get_columns();
		$hidden = array('');

		// disables  the image preview
        if ($this->disable_preview_images){
			$hidden = array_merge($hidden, array('pdf_image'));
		}
		
        $sortable = $this->get_sortable_columns();


        $this->_column_headers = array($columns, $hidden, $sortable);
		$this->_column_headers = $this->get_column_info();

		
		/**
		 * Handle and process any table actions
		 */
		$this->handle_table_actions();

		/**
		 * Query the database and fetch the table data
		 */
		$table_data = $this->fetch_table_data($pdf_search_key);	
		
		/**
		 * Filter the data in case of a search.
		 */
		if (!empty( $pdf_search_key) ) {
			$table_data = $this->filter_table_data( $table_data, $pdf_search_key );
		}	
	
		/**
		 * Set number of documents to display per page 
		 */
		$pdfs_per_page = $this->get_items_per_page('upload_per_page', 10);
		
		/**
		 * Get and set the page number
		 */
		$table_page = $this->get_pagenum();	
		
		/**
		 * Provide the ordered data to the List Table. We need to manually slice the data based on the current pagination.
		 * 
		 */
		$this->items = array_slice( $table_data, ( ( $table_page - 1 ) * $pdfs_per_page ), $pdfs_per_page );
		$total_pdfs = count( $table_data );

		/**
		 * Set the pagination arguments
		 */
		$this->set_pagination_args( array (
			'total_items'	=> $total_pdfs,
			'per_page'		=> $pdfs_per_page,
			'total_pages'	=> ceil( $total_pdfs/$pdfs_per_page ),

			// set ordering values if needed (useful for AJAX)
			'orderby'		=> ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby']  : 'date_uploaded',
			'order'			=> ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order']  : 'desc'
		) );
		
		$this->pdf_DebugLog("Function Loaded::", "prepare_items");
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 1.0.0
	 * 
	 * @return array
	 */	
	public function get_columns() {
		
		return array(
			'cb'					=> '<input type="checkbox" />', // to display the checkbox.			 
			'filename'				=>	__( 'PDF Filename', $this->plugin_text_domain ),			
			'text_data'				=> _x( 'Text Preview', 'text_data', $this->plugin_text_domain ),
			'pdf_image'				=> _x( 'PDF Preview', 'pdf_image', $this->plugin_text_domain ),
			'category'				=> _x( 'Category', 'category', $this->plugin_text_domain ),
			'pdf_doc_num'			=>	__( 'PDF ID', $this->plugin_text_domain ),
			'upload_userid'			=>	__( 'Uploaded By', $this->plugin_text_domain ),
			'date_uploaded'			=>	__( 'Date Uploaded', $this->plugin_text_domain ),
		);
	}
	
	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.1.0
	 * 
	 * @return array
	 */
	protected function get_sortable_columns() {
		
		/*
		 * actual sorting still needs to be done by prepare_items.
		 * specify which columns should have the sort icon.
		 * 
		 * key => value
		 * column name_in_list_table => columnname in the db
		 */
		$sortable_columns = array (
				'filename'				=>	array('filename', false),
				'category'				=>	array('category', false),
				'upload_userid'			=>	array('upload_userid', false),	
				'date_uploaded'			=>	array('date_uploaded', false),
			);
		
		return $sortable_columns;
	}	

	/** *************************************************************************
     * REQUIRED! This is where we prepare the data for display. All column methods
     * 
     * @global WPDB $wpdb
     * @uses $this->no_items()
     * @uses $this->fetch_table_data()
     * @uses $this->filter_table_data()
     * @uses $this->column_default()
     * @uses $this->column_cb()
     * @uses $this->column_text_data()
	 * @uses $this->column_pdf_image()
	 * @uses $this->column_filename()
     **************************************************************************/
	/** 
	 * Text displayed when no pdf documents are available
	 * 
	 * @since   1.0.0
	 * 
	 * @return void
	 */
	public function no_items() {
		_e( 'No documents avaliable.', $this->plugin_text_domain );
	}


	/*
	 * Fetch table data from custom table legoeso_file_storage in the WordPress database.
	 * 
	 * @since 1.0.0
	 * 
	 * @return	Array
	 */
	
	public function fetch_table_data($pdf_search_key) {

		global $wpdb;
		
		$wpdb_table = $wpdb->prefix . $this->get_database_tablename();		
		$orderby = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'insert_date';
		$order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'DESC';
		
		// select which columns to exclude in search
		$xluded_columns = array_flip( array('cb', 'insert_date', 'pdf_image', ) );
		// get the current list columns
		$pdm_columns = $this->get_columns();
		// build search parameters for columns to be searched
		$append_query = implode(" LIKE '%{$pdf_search_key}%' OR ", array_flip( array_diff_key($pdm_columns, $xluded_columns) ) );

		if(!empty($pdf_search_key)){
			$pdm_doc_query = "SELECT 
			pdf_doc_num, filename, category, SUBSTRING(text_data,1,100), pdf_image, upload_userid, date_uploaded, ID FROM $wpdb_table 
			WHERE {$append_query} LIKE '%$pdf_search_key%' ORDER BY $orderby $order";
		} else{
			$pdm_doc_query = "SELECT pdf_doc_num, filename, category, SUBSTRING(text_data,1,100), pdf_image, upload_userid, date_uploaded, ID FROM $wpdb_table ORDER BY $orderby $order";
		}

		$this->pdf_DebugLog("Search Query ::", $pdm_doc_query);
		
		// query output_type will be an associative array with ARRAY_A.
		// return result array to prepare_items.
		return $wpdb->get_results( $pdm_doc_query, ARRAY_A  );	
	}

	/*
	 * Fetch table column data from custom table legoeso_file_storage in the WordPress database.
	 * 
	 * @since 1.0.2
	 * 
	 * @return	Array
	 */
	public function get_column_data($_column_id, $_column_name){

		global $wpdb;
		$wpdb_table = $wpdb->prefix . get_database_tablename();		
		$query = "SELECT $_column_name FROM {$wpdb_table} WHERE ID = {$_column_id} ";
		return $wpdb->get_results( $query, ARRAY_A  );
	}

	/*
	 * Filter the table data based on the user search key
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $table_data
	 * @param string $search_key
	 * @returns array
	 */
	public function filter_table_data( $table_data, $search_key ) {
		return array_values( array_filter( $table_data, function( $row ) use( $search_key ) {
			foreach( $row as $row_val ) {
				if( stripos( $row_val, $search_key ) !== false ) {
					return true;
				}				
			}			
		} ) );	
	}

	/**
	 * Renders a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {			
			case 'pdf_doc_num':
			case 'filename':
			case 'date_uploaded':
				return $item[$column_name];
			default:
			  return $item[$column_name];
		}
	}
	
	/**
	 * Get value for checkbox column.
	 *
	 * The special 'cb' column
	 *
	 * @param object $item A row's data
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(		
				'<label class="screen-reader-text" for="pdfdoc_' . esc_attr($item['ID']) . '">' . sprintf( __( 'Select %s' ), esc_attr($item['pdf_doc_num']) ) . '</label>'
				. "<input type='checkbox' name='pdfdocs[]' id='pdfdoc_".esc_attr($item['ID'])."' value='".esc_attr($item['ID'])."' />"					
			);
	}

	/**
	 * Get the value and truncate  the data for text_data column
	 *
	 * @param object $item A row's data
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_text_data( $item ){
		$strTexData = $item['SUBSTRING(text_data,1,100)'];
		if(empty($strTexData)){
			return sprintf( __(' No Text Available ') );
		}
		else {
			return ( $strTexData);
		}
		
	}

	/**
	 * Get blob data from pdf_image column to display pdf preview image  
	 *
	 * @param object $item A row's data
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_pdf_image( $item ){

		if(empty($item['pdf_image'])){
			return sprintf( __(' See Text Preview ') );
		}
		elseif($item['pdf_image'] == 'no_image_data' || $item['pdf_image'] == 'No Image Data') {
			return sprintf(__('::No Image Data::') );
		} 
		else{
			return sprintf("<img height='150px' width='150px' src='data:jpeg;base64,".base64_encode($item['pdf_image']) ."' />");
		}
	}
	
	/*
	 * Adds row action links to the pdf_doc_num column.
	 * 
	 * @param object $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 * 
	 */
	protected function column_filename( $item ) {

		/*
		 * Build pdf_doc_num row actions.
		 *  
		 * load-view-pdf-doc.php?action=view_pdf_doc&file_id=335&_wpnonce=b06ef63ec5
		 */
		
		$admin_page_url =  admin_url( 'admin.php' );
		$load_pdf_page_url =  site_url('index.php');
	
		/**
		 * Row action: View 
		 */
		$query_args_view_pdfdoc = array(
			//'page'		=>	wp_unslash( isset($_REQUEST['page']) ?$_REQUEST['page'] : ''  ),
			'action'	=>	'view_document',
			'pid'	=>	absint( $item['ID']),
			'_wpnonce'	=>	wp_create_nonce( 'view_pdf_file_nonce' ),
		);
		
		$view_pdf_doc_meta_link = esc_url( add_query_arg( $query_args_view_pdfdoc, $load_pdf_page_url ) );
		$actions['view_pdf_doc'] = '<a target="_blank" href="' .$view_pdf_doc_meta_link. '">'. __( 'View', $this->plugin_text_domain ) . '</a>';
		 

		/**
		 * Row action: Email | Not Implemented 
		 */
		$query_args_email_pdfdoc = array(
			'page'		=>	$this->plugin_text_domain,
			'action'	=>	'email_pdf_doc',
			'file_id'	=>	absint( $item['ID']),
			'_wpnonce'	=>	wp_create_nonce( 'email_pdf_file_nonce' ),
		);

		$email_pdf_doc_meta_link = esc_url( add_query_arg( $query_args_email_pdfdoc, $admin_page_url  ) );	
		$actions['email_doc']	= '<a href="javascript:void(0);" name="pdm-email-'.$item['ID'].'-'.$item['pdf_doc_num'].'">' . __( 'Email', $this->plugin_text_domain ) . '</a>';
		
		/**
		 * Row Action: Quick Edit
		 */
		$query_args_quick_edit = array(
			'page'		=>	$this->plugin_text_domain,
			'action'	=>	'quick_edit_pdf_doc',
			'file_id'	=>	absint( $item['ID']),
			'_wpnonce'	=>	wp_create_nonce( 'quick_edit_nonce' ),
		);
		$quick_edit_link = esc_url( add_query_arg( $query_args_quick_edit, $admin_page_url  ) );	
				$actions['quick_edit_doc']	= '<button type="button" class="button-link editinline" aria-label="Quick edit inline" aria-expanded="false" name="pdm-quick_edit-'.$item['ID'].'-'.$item['pdf_doc_num'].'">'. __( 'Quick Edit', $this->plugin_text_domain ) . '</button>';
		
		/**
		 * Row action: Delete 
		 */
		$query_args_delete_pdfdoc = array(
			'page'		=>	$this->plugin_text_domain,
			'action'	=>	'delete_pdf_doc',
			'file_id'	=>	absint( $item['ID']),
			'_wpnonce'	=>	wp_create_nonce( 'delete_pdf_file_nonce' ),
		);
	
		$delete_pdf_doc_meta_link = esc_url( add_query_arg( $query_args_delete_pdfdoc, $admin_page_url  ) );		
		$actions['delete_doc']	= '<a href="javascript:void(0);" class="delete_item" name="pdm-delete-'.$item['ID'].'-'.$item['pdf_doc_num'].'">'. __( 'Delete', $this->plugin_text_domain ) . '</a>';
		

		/**
		 * Default row text
		 */
		$row_value = '<strong>' . $item['filename'] . '</strong>';
		return $row_value . $this->row_actions( $actions );
	}
	
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since    1.0.0
	 * 
	 * @return array
	 */
	public function get_bulk_actions() {

		/*
		 * on hitting apply in bulk actions the url paramas are set as
		 * ?action=bulk-download&paged=1&action2=-1
		 * 
		 * action and action2 are set based on the triggers above or below the table
		 * 		    
		 */
		 $actions = array(
			 'bulk-download' 	=>	'Download PDFs',
			 'bulk-delete'		=>	'Delete PDFs',
			 'bulk-email'		=> 	'Email Documents'
		 );

		 return $actions;
	}

	/**
	 * Returns the database tablename for the plugin
	 *
	 * @since    1.0.2
	 * 
	 * @return string
	 */
	public function get_database_tablename(){
		return $this->plugin_db_tablename;
	}

	/**
	 * Process actions triggered by the user
	 *
	 * @since    1.0.0
	 * 
	 */	
	public function handle_table_actions() {
		
		/*
		 * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
		 * 
		 * action - is set if checkbox from top-most select-all is set, otherwise returns -1
		 * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
		 */
		
		// check for individual row actions
		$the_table_action = $this->current_action();
		$this->pdf_DebugLog("Bulk ACTION Fired::", $the_table_action);
		
		// specify the valid action types
		$valid_actions = ['bulk-download', 'bulk-delete', 'bulk-email'];

		// check for table bulk actions
		if ( isset( $_REQUEST['bulk_action'] ) || isset( $_REQUEST['bulk_action2'] ) ) {
			
			// verify the nonce.
			/*
			 * Note: the nonce field is set by the parent class
			 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			 * 
			 */
			$nonce = sanitize_text_field( $_REQUEST['_ajax_pdm_doc_list_nonce'] );
			if ( ! wp_verify_nonce( $nonce, 'ajax-pdm-doc-list-nonce' ) ) {
				$this->invalid_nonce_redirect();
				return;
			}
			$bulk_action = (isset($_REQUEST['bulk_action']) ? sanitize_text_field($_REQUEST['bulk_action']) : sanitize_text_field($_REQUEST['bulk_action2']));
			
			if(in_array($bulk_action, $valid_actions)) {
				switch($bulk_action) {
					case 'bulk-download':
						//	get and pass the list of pdfs for processing
						$this->pdf_bulk_download((sanitize_text_field($_REQUEST['checkedVals'])));
						$this->graceful_exit();
					break;
					case 'bulk-delete':
						die( wp_json_encode($this->pdf_bulk_delete(sanitize_text_field($_REQUEST['checkedVals'])) ) );
					break;
					case 'bulk-email':
						// not implemented!
					break;
					default:
						return;
					break;
				}
			}
		}
	}

	/**
	 * Bulk process to download PDF documents.
	 *
	 * @since   1.0.1
	 * 
	 * @param array $bulk_pdf_ids
	 */		
	public function pdf_bulk_download( $bulk_pdf_ids ) {
		if(!isset($bulk_pdf_ids) && !is_array($bulk_pdf_ids) && count($bulk_pdf_ids) > 1)
			return; // there's an error'

		$this->get_bulk_pdf_data($bulk_pdf_ids);
	}

	/**
	 * Determines whether the pdf_doc_id has an url associated with it
	 *
	 * @since   1.0.2
	 * 
	 * @param string $pdf_id - pdf doc id
	 * @param object $wpdb - Global WP database object
	 * @return array - list of files that have a URL
	 */	
	private function pdf_has_url($wpdb, $pdf_ids){
		if(!isset($pdf_ids))
			return false;

			$wp_dir_obj = wp_upload_dir();
			$pdf_url_info = array('pdf_file_path'	=> $wp_dir_obj['path'].'/pdm_data');

			$sql_query = "SELECT ID, filename FROM `{$wpdb->prefix}".$this->get_database_tablename()."` WHERE ID IN (".implode(',',$pdf_ids).") AND has_url = 1;" ;
			$_has_url = $wpdb->get_results($sql_query, ARRAY_A);
			$pdf_url_info['files'] = $_has_url;

			// $this->pdf_DebugLog("Query for Delete PDF Documents::", $sql_query);
			// $this->pdf_DebugLog("IDs with a URL: Object::", json_encode($pdf_url_info));
		return ( $pdf_url_info );
	}  

	/**
	 * Bulk process to download PDF documents.
	 *
	 * @since   1.0.1
	 * 
	 * @param array $bulk_pdf_ids
	 */		
	public function pdf_bulk_delete($bulk_pdf_ids){
		print_r($bulk_pdf_ids);exit();
		function delete_pdf_files($files){
			if(!isset($files) && !is_array($files))
				return;
			$deleted = [];	
			$file_dir = $files['pdf_file_path'];
			$_files = $files['files'];
			if(!is_array($_files))
				return;	
			foreach($files['files'] as $file){
				//  delete file form the directory
				$_file = $file_dir.'/'.$file['filename'];
				if(file_exists($_file)){
					unlink($_file);
					$deleted[] = $file['filename'];
				}	
			}
			return($deleted);
		}

		if(!isset($bulk_pdf_ids) && !is_array($bulk_pdf_ids) && count($bulk_pdf_ids) > 1)
			return; // there's an error'
					// set the WordPress database global object
		global $wpdb;
		$wpdb->show_errors();

		// list of pdf files that reside within the file directory
		$_deleted = delete_pdf_files($this->pdf_has_url($wpdb, $bulk_pdf_ids));
	
		$sql_filter = (!empty($bulk_pdf_ids)) ? " WHERE ID IN (".implode(',',$bulk_pdf_ids).")" : '';

		// build an SQL query
		$sql_query = "DELETE FROM `{$wpdb->prefix}".$this->get_database_tablename()."` {$sql_filter};";
		$results = $wpdb->query($sql_query, ARRAY_A);


		$this->pdf_DebugLog("Deleted PDF Documents From File Sytem::", wp_json_encode($_deleted));
		$this->pdf_DebugLog("Query for Delete PDF Documents::", $sql_query);

		if ($results !== false){
			$this->pdf_DebugLog("Bulk Delete: Query Succeeded::", "--");
			//	return a response to the caller
			return 	array(
					'status'	=> 'complete',
					'type'		=>	'bulk_delete',
					'pdf_docs'	=>	$bulk_pdf_ids,
					'total'		=>	count($bulk_pdf_ids),
			);
		} else {
			$this->pdf_DebugLog("Bulk Delete: Query Failed::", "{$wpdb->last_error}");
		}
		
		return array(
			'status'	=> 'complete',
			'type'		=>	'bulk_delete',
		);
	}

	/**
	 * Bulk process to email PDF documents. NOT IMPLEMENTED!
	 *
	 * @since   1.0.2
	 * 
	 * @param array 
	 */		
	public function pdf_bulk_email($bulk_pdf_ids){
		if(!isset($bulk_pdf_ids) && !is_array($bulk_pdf_ids) && count($bulk_pdf_ids) > 1)
			return;
		if ($results !== false){
			$this->pdf_DebugLog("Bulk Email: Query Succeeded::", "Begin Emailing '{$wpdb->num_rows}' PDF Documents ");
			//	return a response to the caller
			return 	array(
					'status'	=> 'complete',
					'type'		=>	'bulk_email',
					'pdf_docs'	=>	$bulk_pdf_ids,
					'total'		=>	count($bulk_pdf_ids),
			);
		} else {
			$this->pdf_DebugLog("Bulk Email: Query Failed::", "{$wpdb->last_error}");
		}
		
		return array(
			'status'	=> 	'complete',
			'type'		=>	'bulk_email',
		);
	}

	/**
	* Stop execution and exit
	*
	* @since    1.0.0
	* 
	* @return void
	*/    
	public function graceful_exit() {
		exit;
	}

	/**
	* Die when the nonce check fails.
	*
	* @since    1.0.0
	* 
	* @return void
	*/    	 
	public function invalid_nonce_redirect() {
		wp_die( __( 'Invalid Nonce', $this->plugin_text_domain ),
				__( 'Error', $this->plugin_text_domain ),
				array( 
						'response' 	=> 403, 
						'back_link' =>  esc_url( add_query_arg( array( 'page' => wp_unslash( $_REQUEST['page'] ) ) , admin_url( 'admin.php' ) ) ),
					)
		);
	}

	/**
	* Get and compile pdf data from database, creates new pdf file
	*
	* @since    1.0.1
	* 
	* @return void
	*/   
	public function get_bulk_pdf_data($pdf_doc_ids){

		function zip_pdf_docs($_this, $zip_filename, $sql_dataset){
			// create new array to collect filenames
			$pdf_docs = []; 		
			// create a new ZipArchive object
			$zip = new ZipArchive();

			// set a better name
			if ($zip->open($zip_filename, ZipArchive::CREATE)!==TRUE) {
				$_this->pdf_DebugLog("*** Bulk download: Error::", "Could not create '{$zip_filename}'" );
				return;
			}

			// loop through sql results
			foreach($sql_dataset as $key => $data){	
				// collect row  data/information
				$sID = $data['ID'];
				$pdf_data = $data['pdf_data'];
				$sfilename = $data['filename'];

				// if the filename has already been added to zip it will be
				// skip not included in zip file.  resolve by checking for file if already exists
				// within the stack append the files' ID
				if(in_array($sfilename, $pdf_docs)){
					$sf = explode('.',$sfilename);
					$sfilename = $sf[0].'_'.$sID.'.'.$sf[1];
				}
				//	add each pdf file to the zip file

				$zip->addFromString($sfilename, $pdf_data);
				//	add filename to stack
				$pdf_docs[] = $sfilename;
			}
			// close zip file
			$zip->close();
			return $pdf_docs;
		}		

		function do_pdf_docs($_this, $sql_results){

			//  get the WordPress upload directory
			$wp_upload_dir = wp_upload_dir();
			$file_dir = $wp_upload_dir['path'];
			
			$zipfile_basedir = "{$file_dir}/pdm_data/pdf_download_".time().".zip";
			$zipfile_url = $wp_upload_dir['url'] . "/pdm_data/pdf_download_".time().".zip";

			// loop through the sql results and add each row of data to 
			// the zip file
			$pdf_docs = zip_pdf_docs($_this, $zipfile_basedir, $sql_results);

			$_this->pdf_DebugLog("*** Bulk download: Complete::", "Path Zip file: {$zipfile_basedir}" );
			$_this->pdf_DebugLog("*** Bulk download: Complete::", "Url Zip file: {$zipfile_url}" );
			
			//	return a response to the caller
			return 	array(
					'status'	=> 'complete',
					'type'		=>	'bulk_download',
					'pdf_docs'	=>	$pdf_docs,
					'total'		=>	count($pdf_docs),
					'zip_url'	=>	$zipfile_url,
			);
		}

		// set the WordPress database global object
		global $wpdb;
		$wpdb->show_errors();
		//  specify the columns to retreive
		$columns = array('ID', 'pdf_data', 'filename');
		$sql_filter = (!empty($pdf_doc_ids)) ? " WHERE ID IN (".implode(',',$pdf_doc_ids).")" : '';

		// build an SQL query
		$sql_query = "SELECT `".implode("`,`", $columns)."` FROM `{$wpdb->prefix}".$this->get_database_tablename()."` {$sql_filter};";
		$results = $wpdb->get_results($sql_query, ARRAY_A);
		
		$this->pdf_DebugLog("Query for Bulk Download::", $sql_query);

		if ($wpdb->num_rows > 0){
			$this->pdf_DebugLog("Bulk download: Query Succeeded::", "Begin Zipping '{$wpdb->num_rows}' PDF Documents ");
			die( wp_json_encode(do_pdf_docs($this, $results)) );
		} else {
			$this->pdf_DebugLog("Bulk download: Query Failed::", "{$wpdb->last_error}");
		}
		
		return false;
	}		
	
	/**
	 * overrides original display() method from WP_List_Table
	 *
	 * @since    1.0.0
	 * 
	 * @return void
	 */  
	public function display(){
		wp_nonce_field( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce' );

		echo '<input id="order" type="hidden" name="order" value="'. esc_attr($this->_pagination_args['order']) . '" />';
		echo '<input id="orderby" type="hidden" name="orderby" value="'. esc_attr($this->_pagination_args['orderby']) . '" />';
		$this->search_box( __( 'Search Docs', $this->plugin_text_domain ), 'pdm-doc-find');

		parent::display();
		//	add the inline quick edit box
		$this->draw_inline_edit_box();

	}

	/**
	 * adds custom inline quick editi box for editing the document filename and category
	 *
	 * @since    1.0.3
	 * 
	 * @return void
	 */  
	public function draw_inline_edit_box(){
		return include plugin_dir_path(__FILE__).'views/partials-inline-edit-box.php';
	}

	/**
	 * override original ajax_response() method from WP_List_Table
	 *
	 * @since    1.0.0
	 * 
	 * @return void
	 */ 
	public function ajax_response(){

		check_ajax_referer( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce');
		// Logging actions taken
		$this->pdf_DebugLog("Method: ajax_response():", "Fired!");
		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP);
	
		$this->prepare_items();

		ob_start();

		if( !empty( $_REQUEST['no_placeholder']) ){
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
			$rows = ob_get_clean();
		}

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();


		$response = array( 'rows' => $rows );
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers'] = $headers;

		if( isset($total_items) )
			$reponse['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n($total_items ) );

		if( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages);

		}

		die( wp_json_encode( $response ) );
	}

}
