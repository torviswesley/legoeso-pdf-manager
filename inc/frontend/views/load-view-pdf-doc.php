<?php

	/**
	 * Retrieves and loads the PDF document from raw data.
	 *
	 * - setup_ExternalPage - Setup the WordPress global enviroment variables.
	 * - db_get_pdf_data - Retrieves PDF data from database using the requested ID.
	 * - dispalyPDFContent - Defines all hooks for the admin area.
	 *
	 * @access    private - login required
	 */

	/**
	 * Loads WordPress enviroment variables
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  None - exit on failure
	 */
    function setup_ExternalPage(){
        function find_word_pos($string, $word) {
            //case in-sensitive
            $string = strtolower($string); //make the string lowercase
            $word = strtolower($word);//make the search string lowercase
            $exp = explode(DIRECTORY_SEPARATOR, $string);
            if (in_array($word, $exp)) { 
                return array_search($word, $exp) + 1;
            }
            return -1; //return -1 if not found
        }

        $wp_str_pos = find_word_pos(__FILE__, "wp-content");
        $wp_dir_array = explode(DIRECTORY_SEPARATOR, __FILE__);
        $new_wp_dir_array = array_slice($wp_dir_array, 0, $wp_str_pos-1);
        $strPath = join(DIRECTORY_SEPARATOR, $new_wp_dir_array);
        $str_file_to_include = $strPath.'/wp-load.php';

        if(is_dir($strPath)){
            if(file_exists($str_file_to_include)) {
                require_once( $str_file_to_include );
                return;
            }
            die("Failed to load required resources!");
        }
    
        // If this file is called directly, abort.
        if ( ! defined( 'WPINC' ) ) {
	        die;
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
        if(!isset($doc_id))
            return;

        global $wpdb;
        $wpdb_table = $wpdb->prefix. 'legoeso_file_storage';
        $charset_collate = $wpdb->get_charset_collate();

   	    $pdm_doc_query = "SELECT 
					    pdf_data, filename, has_url, pdf_url
					    FROM $wpdb_table WHERE ID = '$doc_id'";
        
        // query output_type will be an associative array with ARRAY_A.
        $wpdb_results = $wpdb->get_results( $pdm_doc_query, ARRAY_A  );

        if(count($wpdb_results) < 1){
            header( "Location: {$_SERVER["REQUEST_SCHEME"]}://{$_SERVER["HTTP_HOST"]}/404?error=1&docid={$doc_id}");
            exit();
        }
        return $wpdb_results;  
    }

    /**
	 * Sets document Content headers to display inline PDF document.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  None - exit on failure
	 */
    function dispalyPDFContent(){
        $doc_id = $_REQUEST['file_id'];
        // if( !is_user_logged_in() ){
        //    $_error = "You must be logged in to view documents!";

        //     header( "Location: {$_SERVER['HTTP_REFERER']}?{$_SERVER['QUERY_STRING']}&error={$_error}" );

        //     die("You must be logged in to use this feature!");
        // }	
        if(!isset($doc_id)){
            die("Error: Document not found! - 1");
        }
            
        $pdf_data = db_get_pdf_data($doc_id); 
        $pdfContent = $pdf_data[0]['pdf_data'];
        $filename = $pdf_data[0]['filename'];
        $has_url = $pdf_data[0]['has_url'];
        $pdf_url = $pdf_data[0]['pdf_url'];

        switch($has_url){
            case 1:
                header("Location: {$pdf_url}?docid={$doc_id}");
            break;
            case 0:
                if(empty($pdfContent) || $pdfContent == null){
                   header( "Location: {$_SERVER["REQUEST_SCHEME"]}://{$_SERVER["HTTP_HOST"]}/404?error=2&docid={$doc_id}");
                }
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="'.$filename.'"');
                header('Content-Transfer-Encoding: binary');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Accept-Ranges: bytes'); 
                ob_clean();
                flush();
                echo  $pdfContent; 
            break;
        }

        return;
    }

    //   Setup WordPress Enviroment Variables
    setup_ExternalPage();
    //  Dispaly PDF content
    dispalyPDFContent();
?>