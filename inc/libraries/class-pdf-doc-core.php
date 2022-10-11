<?php 
namespace Legoeso_PDF_Manager\Inc\Libraries;

use Legoeso_PDF_Manager as NS;
use Legoeso_PDF_Manager\Inc\Common as Common;
use Timer;

/**
 * Class for processing PDF documents. Extends Utility_Functions class 
 * All documents  will be stored within the custom legoeso_file_storage table.
 * 
 *
 * @link       http://www.legoeso.com
 * @since      1.0.0
 * 
 * @author     Torvis Wesley
 */
class PDF_Doc_Core extends Common\Utility_Functions {

	/**
	 * The start time in milliseconds of when file upload/process starts.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $start_time      The process end start time.
	 */
	private $set_start_time;

	/**
	 * The end time in milliseconds of when file/process ends.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $end_time        The process end time.
	 */
	private $set_end_time;

	/**
	 * The total time in milliseconds of file/process time.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $total_time      The total process time.
	 */
	private $total_time;

    /**
	 * Collects and stores the results of the processed files
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $file_process_results Stores the results for each file 
     *                  processed.
	 */
	private $file_process_results = [];

    /**
	 * stores any errors during upload of the processed files
	 *
	 * @since    1.0.4
	 * @access   private
	 * @var      string    $pdm_upload_errs  stores any errors during upload of the processed files
	 */
	private $pdm_upload_errs;

    /**
	 * Specfifies and stores the upload dirctory of the processed files
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_upload_dir stores the upload dirctory of the processed files
	 */
	private $pdm_upload_dir;

    /**
	 * Specfifies and stores the upload dirctory of the processed files
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_upload_dir stores the upload dirctory args of the processed files
	 */
	private $pdm_upload_dir_agrs;

    /**
	 * Indicates use of the tesserct library to extract data from PDF's
	 * when PDFminer fails
     * 
	 * @since    1.0.1
	 * @access   private
	 * @var      boolean    $use_Tesseract set to true to use the tesserct library on second extraction attempt
	 */
	private $use_Tesseract;

    /**
	 * Enable/disable image extraction of PDF documents - overrides saved settings if enabled text extraction will
	 * be disabled
     * 
	 * @since    1.0.1
	 * @access   private
	 * @var      boolean    $force_image_extraction - only images will be extracted
	 */
	private $force_image_extraction;

     /**
	 * The directory location to the class libraries for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_library    The directroy location to the class libraries for this plugin.
	 */
    public $pdm_library;

     /**
	 * The directory path location for the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_plugin_dir    The directory path location for the plugin
	 */
    public $pdm_plugin_dir;

     /**
	 * The directory path location to pyhton.exe on the server.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_Python    The directory path location to pyhton.exe on the server.
	 */
    public $pdm_Python;

     /**
	 * Directory to the location of the Python script for PDFMiner
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $pdm_PdfMiner    The directory location to PDF Miner for this plugin.
	 */
    public $pdm_PdfMiner;

     /**
	 * Path to poppler-utils executables, used to extract images from PDF files
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pdm_PdfMiner    The directory location to PDF Miner for this plugin.
	 */
    private $pdm_PdfImages;

     /**
	 * Property used to store the filename used to save upload status
	 * @since    1.0.1
	 * @access   private
	 * @var      string   $pdm_status_filename   stores the filename used to save upload status
	 */
    private $pdm_status_filename;

     /**
	 * Sets the max_allowed_packet size in bytes, we'll use it as the limit before breaking the file into peices 
	 * @since    1.0.2
	 * @access   private
	 * @var      integer   $pdm_max_filesize    used to stay below MySQL's allowed memory packet size
	 */
    private $pdm_max_filesize;

     /**
	 * specifies whether the file is a large file.
	 * @since    1.0.2
	 * @access   private
	 * @var      boolean   $pdm_large_file - specifies wether the file uploaded is large.
	 */
    private $pdm_large_file = false;
     /**
	 * specifies valid file types/ 
	 * @since    1.0.2
	 * @access   private
	 * @var      array   $pdm_valid_file_types - specifies the supported files types. 
	 */
    private $pdm_valid_file_types;

	/**
	 * Initializes class variables and set its properties.
	 *
	 * @since   1.0.0
     * @return  none
	 */
    public function __construct(){

        $this->pdm_plugin_dir = NS\PLUGIN_NAME_DIR;
        $this->pdm_library = NS\PLUGIN_NAME_DIR.'inc/libraries/';
        $this->pdm_PdfImages = NS\PDFIMAGES_DIR;
        $this->pdm_PdfMiner = NS\PDFMINER_DIR;
        $this->pdm_Python = NS\PYTHON_DIR;
        $this->pdm_max_filesize = 13010000;
        $this->pdm_valid_file_types = array('application/pdf','application/x-zip-compressed');
        
        parent::__construct();
        
        // Gets the current option for the tesserct library setting, enable/disable'
        $this->use_Tesseract = (get_option("legoeso_pytesseract_enabled") == 'on') ? 1: 0;
        // Get/set the current options to force_image_extraction '
        $this->force_image_extraction = (get_option("legoeso_force_image_enabled") == 'on') ? 1: 0;
    }

    /**
	 * Handles and processes the uploaded file
     * 
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.0.0
	 * @return none
	 */
    public function process_pdf_upload($_files){ 

        /**
        * Step 1: 
        * Check to see if a file was submitted if not return nothing
        */
        // step out if varibale not set
        if (!isset($_files)){
            return;
        }
            
        //  get the upload directory info
        $set_upload_dir_info = json_decode(stripslashes($_POST['_pdm_upload_info']));
        $this->pdm_upload_dir_agrs = $set_upload_dir_info;

        // set the upload directory, including the file use to monitor the status of the process
        $this->pdm_upload_dir = $this->pdm_upload_dir_agrs->pdm_upload_dir;
        $this->pdm_status_filename = base64_decode($this->pdm_upload_dir_agrs->pdm_upload_status_filename);

        // write to the debug log
        $this->pdf_DebugLog('Method: process_pdf_upload(): *** STEP 1: BEGIN UPLOAD PROCESS *** ', "STARTED");

        // write to the debug log
        $this->pdf_DebugLog('Method: process_pdf_upload(): Files Submitted [Object]::', ($_files));

        //  override the current option for legoeso_force_image_enabled 
        if(array_key_exists('legoeso_force_image_enabled', $_POST)){
                $this->force_image_extraction = ($_POST['legoeso_force_image_enabled'] == 'on') ? 1: 0;
        }

        /**
        * Step 2:
        * Validate, filter and process the uploaded file(s)
        */
        $this->validateFileUpload($_files);
 
    }

	/**
	 *  Validates the uploaded file(s) type
	 *
	 * @since 1.0.0
	 * @param array $_POST
	 * @return none
	 */
    public function validateFileUpload($_submitted_files){

        //  include the timer class
        include $this->pdm_plugin_dir.'inc/libraries/class-timer.php';

        //  Translates the file uplaod error number to readable text
        function displayFileError($errNumber){
            //  Define upload errors
            $FileUploadErrors = array(
                0   =>  'There is no error, the file uploaded with success',
                1   =>  'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                2   =>  'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                3   =>  'The uploaded file was only partially uploaded',
                4   =>  'No file was uploaded',
                6   =>  'Missing a temporary folder',
                7   =>  'Failed to write file to disk.',
                8   =>  'A PHP extension stopped the file upload.',
                9   =>  'No file detected or no file(s) submitted',
            );
            if(!array_key_exists($errNumber, $FileUploadErrors)){
                return ($FileUploadErrors['9']);
            } else {
                return($FileUploadErrors[$errNumber]);
            }   
        }
        
        function check_files_for_errors($_files){

            if(!is_array($_files)){
                return("Expected an array object, none was found.");
            }

            $_file_errs = $_files['error'];
            $err_count = array_sum($_file_errs);
            $_errors = [];

            if($err_count > 0){
                foreach($_file_errs as $id => $err){
                    if($err == 0){
                        continue;
                    }
                    $_errors[] = $_files['name'][$id] ." - ". displayFileError($err);
                }
                die(implode(" ", $_errors));
            }
        }
        /**
        * Start Timer
        */
        $sTimer = new Timer();
        $this->pdf_DebugLog("Method: validateFileUpload(): File Process Start Time:", $sTimer->startTimer());
        
        //  Count the number of files submitted
        $file_count = count($_submitted_files['name']);

        // check files for errors
        check_files_for_errors($_submitted_files);

        # Add to debug/log 
        $this->pdf_DebugLog("Method: validateFileUpload(): File Upload Status:", "Success");
        $this->pdf_DebugLog("Method: validateFileUpload(): Number of File(s) Submitted:", $file_count);

        // filter and process the uploaded files
        $this->processFileUploads($this->filter_file_types($_submitted_files));

        // finalize the process
        $this->completeProcess();

        // log timer
        $this->pdf_DebugLog("Method: validateFileUpload(): File Process End Time:",  $sTimer->stopTimer());
    }
    /**
     * Finalizes the upload process 
     * 
     * @since 1.0.2
     * @return object json
     */
    private function completeProcess(){
        //  gets the private field that stores the file process results
        $file_results = $this->file_process_results;

        //  isolate the upload_status column from the $this->file_process_results array
        //  and return only the files that failed
        $r_failed_files = array_filter( array_column($file_results, 'upload_status'), function($val) { 
            return $val == 'failed';
        });

        // return a list of filenames that have failed. no other information will be provided
        // at this time other the filename.
        function get_failed_files($f_results, $f_files){ 
            $a = array();
            foreach($f_files as $k => $v){
                $a[] = $f_results[$k]['filename'];
            }
            return empty($a) ? 0 : $a;
        };

        // do cleanup.  Remove files after files have been added to the database
        $this->clean_dir($this->pdm_upload_dir);

        //  send results back to ajax caller
        die( json_encode(
            array(
                'total_files'   => count($file_results),
                'results'       => $file_results,
                '_status'       => 'complete',
                'failed'        => get_failed_files($file_results, $r_failed_files),
                'php_exceptions'    => $this->Exception_Error,
                )
            ) 
        );
    }

    /**
	 * Loops through and filters the files uploaded/submitted and returns an array with the supported file types.
	 * types for processing
     *
	 * @since 1.0.2
	 * @param array $_files
	 * @return array valid file types
	 */
    private function filter_file_types($_files){
        if(!isset($_files) && !is_array($_files))
            return null;
              
        $valid_files = [];
        $invalid_files = [];

        // get the file types
        $_file_types = $_files['type'];
        
        // set/hold index counter
        $i = 0;  
        foreach($_file_types as $key => $_type){
            if(!in_array($_type, $this->pdm_valid_file_types)){
                $invalid_files['name'][$key] = $_files['name'][$key];
                continue;      
            }
                
            $valid_files['name'][$key] = $_files['name'][$key];
            $valid_files['type'][$key] = $_files['type'][$key];
            $valid_files['tmp_name'][$key] = $_files['tmp_name'][$key];
            $valid_files['error'][$key] = $_files['error'][$key];
            $valid_files['size'][$key] = $_files['size'][$key];
            $i++;
        }

        return $valid_files;
    }

    /**
     *  Processess the uploaded files individually
     *
     * @since 1.0.0
     * @param array @_files
     * @return object json
     */
    private function processFileUploads($_files){

        if(!is_array($_files) && !array_key_exists('tmp_name', $_files)){
            return;
        }

        // filters the submitted zip files and adds them to $_files array
        $_files = $this->processZipFile($_files);

        // count and add the file count element to the $_files array
        $_files['file_count'] = (isset($_files['name'])) ? count($_files['name']):0;

        // get the total number of files
        $_num_of_files = $_files['file_count'];
        $this->pdf_DebugLog("Method: processFileUploads(): ({$_num_of_files }) File(s) to be processed: [Object]::", $_files);

        // loop over the row of  files
        for($i=0; $i < $_num_of_files;$i++){
            // get the file information for each file
            $_filename      = sanitize_file_name($_files['name'][$i]);
            $_file_type     = $_files['type'][$i];
            $_tmp_filename  = $_files['tmp_name'][$i];
            
            //  save the curent progress for the progress bar. 
            $this->save_progress($i+1, $_num_of_files, $this->processPDFFile($_filename, $_tmp_filename));
        }
        
    }

    /**
	 *  Extract the contents of a zip file and pass the file to proccessPDFFile
     *  for futher processing.
	 *
	 * @since 1.0.2
	 * @param object $_files
	 * @return array 
	 */
    private function processZipFile($_files){
        if(!is_array($_files) && !isset($_files['type']))
            return;
        
        /**
         * removes the unzipped file from the list of files to be processed
         * @since   1.0.2
         * @param array     $_file array object 
         * @param array     $_removed
         * 
         * @return array
         */
        function remove_item($file, $_removed){
            if(!is_array($file) && !empty($_removed)){
                return;
            }
                

            foreach($_removed as $index){
                foreach($file as $key => $items){
                    unset($items[$index]);
                    $file[$key] = $items;
                }          
            }
            // reset the indices
            foreach($file as $key => $items){
                $file[$key] = array_values($items);
            } 

            return $file;
        }

        $zip_file = [];
        $unzipped = [];
        $removed = [];

        // specify compressed file types for addtional processing, for now ignoring 
        // 'application/zip' types
        $_compressed_file_types = array("application/x-zip-compressed");

        // get the collection of types
        $_file_types = $_files['type']; // fix, if value is not there stop script do add to database

        // $this->pdf_DebugLog("Method: processZipFile(): **** Types:: ",   $_file_types);
        // filter file types to only include zip files
        foreach($_file_types as $index => $type){
            
            // if the file type is not a zip file, skip it
            if(in_array($type, $_compressed_file_types)) {
                // collect indices of zip files for later removal 
                $removed[] = $index;

                $zip_file['name']     =  $_files['name'][$index];
                $zip_file['type']    =  $_files['type'][$index];
                $zip_file['tmp_name'] =  $_files['tmp_name'][$index];
                $zip_file['error']   =  $_files['error'][$index];
                $zip_file['size']    =  $_files['size'][$index];

                // $this->pdf_DebugLog("Method: processZipFile(): **** Prepare to UNZIP FILE Type: $type Name:".$_files['name'][$index]." - ".$index,  $zip_file);
                // pass the zip file to unzip and collect the extracted files in an array
                $this->unzipfiles( $zip_file, $unzipped );
                 
            }    
        }

        // merge all unzipped files into the original $_files array     
        if(!empty($unzipped) && is_array($unzipped)){
            $_files = array_merge_recursive( $_files, $unzipped);;
        } 
        // remove the zip files from $_files array
        $_files = remove_item($_files, $removed);   

        $this->pdf_DebugLog("Method: processZipFile(): **** Extracted Files: 'unzipped' ::",  $unzipped);
        $this->pdf_DebugLog("Method: processZipFile(): **** Updated: '_files' Array ::",  $_files);

        return ($_files);
    }

    /**
	 *  Unzip, filter and return passed in zip file for futher processing.
	 *
	 * @since 1.0.2
	 * @param array     $_zip_file
     * @param array     $_unzipped_files
	 * @return none 
	 */
    private function unzipfiles($_zip_file, &$_unzipped_files){
        //  Get the filename of the uploaded zip file
        $zip_filename = $_zip_file['name'];
        $zip_tmp_name = $_zip_file['tmp_name'];

        // set the upload directory to store the supported unzipped files
        $writeToPath = trailingslashit($this->pdm_upload_dir);

        // Initializes and connects the WordPress Filesystem Abstraction classes.
        WP_Filesystem();

        // Build folder path to temp directory         
        $folder = $writeToPath . "unzipped_" . basename($zip_tmp_name);

        //  create the temp directory
        if (!is_dir($folder) && file_exists($folder)) {
            mkdir($folder);
        }

        // unzip and filter the files
        if (unzip_file($zip_tmp_name, $folder) === True) {
            $filepaths = $this->dir_tree($folder);
            foreach ($filepaths as $k => $filepath) {
                // Get the file type
                $_file_type =  mime_content_type($filepath);
                
                // if the file type is unsupported skip it
                if(!in_array($_file_type, $this->pdm_valid_file_types)){
                    continue;      
                }

                // collect/add extracted files to the collection
                $_unzipped_files['name'][] = basename($filepath);
                $_unzipped_files['type'][] = $_file_type;
                $_unzipped_files['tmp_name'][] = $filepath;
                $_unzipped_files['error'][] = 0;
                $_unzipped_files['size'][] = isset($filepath) ? filesize($filepath):0;
            }
        }
        $this->pdf_DebugLog("Method: unzipfiles(): **** FILES EXTRACTED ***:: ", $_unzipped_files);
    
    }
    /**
	 *  Processess a single PDFFile for futher processing.
	 *
	 * @since 1.0.0
	 * @param string $filename
     * @param string $tmpFilename
	 * @return array
	 */
    private function processPDFFile($filename, $tmpFilename){
        // verify there is a filename 
        if(isset($filename) && isset($tmpFilename)){
            // if the uploaded filesize is double than the filesize limit lets store the file within the 
            // file system instead of the database
            if(filesize($tmpFilename) >  ($this->pdm_max_filesize * 2)){
                // move the uploaded file for processing and get the directory information
                $file_info_arr =  $this->uploadFileToWp($filename, $tmpFilename, true);
                // set the large file flag to true
                $this->pdm_large_file = true;

            } else {
                // move the uploaded file for processing and get the directory information
                $file_info_arr =  $this->uploadFileToWp($filename, $tmpFilename);
                // reset the large file flag to the default
                $this->pdm_large_file = false;
            }
            $this->pdf_DebugLog("Uploaded DIR Args: Object::", json_encode($this->pdm_upload_dir_agrs));
            $this->pdf_DebugLog("Uploaded File Information: Object::", json_encode($file_info_arr));
            // Get the file path that was uploaded
            $fileUploaded = $file_info_arr['file'];
            
            if (is_wp_error($file_info_arr)) {

                $uploadErrorMsg = $file_info_arr->get_error_message();
                $errorMsg = "Error uploading file: " . $uploadErrorMsg;
                # Add to debug/log 
                $this->pdf_DebugLog("Upload Status:: ", $errorMsg);
            } 
            else {
                # Add to debug/log 
                $this->pdf_DebugLog("Upload Status::", "File successfully uploaded!");
            }

            //  add file to database / returns an array
            //  return result to the caller
            return $this->addFileToDatabase($fileUploaded, $filename);
        }
    }
    
    /** 
    * Saves the status of the file upload process. this function is 
    * used in conjuction with JavaScript and ajax.  It converts the status 
    * to a json object and writes the current status to a file, which is then
    * usd by the JavaScript 
    *
	* @since 1.0.0
	* @param int $idx
    * @param int $total_files
    * @param array $status_arr
	* @return none
    */
    function save_progress($idx, $total_files, $status_arr){

        if( !is_array($status_arr)){
            return;
        }
        
        // get the remaining time of the process   
        $time_remaining = $this->execTimeRemaining();

        $status_message = "Processing File: ".($idx)." of {$total_files} ";
        
        // calculate the percentage
        $percent_complete = intval($idx / $total_files * 100);

        // if the percentage complete is less than 100 add 10 seconds until
        // the process is complete
        if($percent_complete < 100 && $time_remaining < 10){
            set_time_limit(60);
        }

        // get the WP upload directory
        // use the nonce to create the text file
        $status_filename = $this->pdm_status_filename;

        // populate the status array
        $status_arr['percent'] = $percent_complete;
        $status_arr['status_message'] = $status_message;

        # Add to debug/log 
       try {
            file_put_contents($status_filename, json_encode($status_arr));
       } 
       catch(PDM_Exception_Error $e) {
            $this->Exception_Error[] = $e->getErrorObject($e);
            $status_arr['status_message'] = 'Error updating status. Unable to write file.';
       }
        
        // Sleep one second so we can see the delay
        sleep(1);
    }

     /**
     *
	 * @since 1.0.0
	 * 
	 * @return none
	 */
    // Use the wordpress function to upload
    private function uploadFileToWp($Filename, $tmp_Filename, $large_file=false){
        if(isset($Filename) && isset($tmp_Filename)){
            // if the file is too large to go into the database just upload it to the WP upload directory
            if($large_file){
                return $this->pdm_upload($Filename, file_get_contents($tmp_Filename), '');
            } else {
                return $this->pdm_upload($Filename, file_get_contents($tmp_Filename), $this->pdm_upload_dir);
            }
        }
    }

     /**
	 * Inserts file into database 
     *
	 * @since 1.0.0
	 * @param string $uploadFilename
     * @param string $filename
	 * @return array
	 */
    private function addFileToDatabase($uploadFilename, $filename){
        /**
        * Post the uploaded file to the WordPress database
        */
        // do two things.
        // 1. get the raw contents of the PDF file 
        // 2. Extract the text from the pdf file so that it can be
        // parsable and searched.

        global $wpdb;
        $wpdb->show_errors(true);

        //  set/get the tablename document will be inserted
        $tablename = $wpdb->prefix. 'legoeso_file_storage';

        //  get the category the document will be associated with
        $pdf_category = ( $_REQUEST['pdf_category'] == -1 ) ? 'General' : $_REQUEST['pdf_category'];

        // initialize the rowID, will be used later to update the table data
        $insert_rowID = null;

        //  Extract the text from the PDF file and get the document properties
        $objDocumentProperties = $this->extractTextFromPDF($uploadFilename);
        $extracted_data = $objDocumentProperties['extracted_data'];
        $extraction_outputType = $objDocumentProperties['output_type'];
        
        // create the date object to use in the query
        $theDate = date_create();
        $theDateFormat = date_format($theDate, 'Y-m-d');
        
        // get the current userid
        $logged_in_user = wp_get_current_user();
        $current_user = $logged_in_user->data->user_login;

        $this->pdf_DebugLog("Extraction Output Object::", json_encode($objDocumentProperties));
        $this->pdf_DebugLog("Extraction Output Type::", $extraction_outputType);
        $this->pdf_DebugLog("Insert Date::", $theDateFormat);

        // Add additional field data to the database
        switch ($extraction_outputType) 
        {
            case 'image_data':
                //  Get pdf image/data contents - Reads entire file into a string

                if(empty($extracted_data)){
                    $this->pdf_DebugLog(":: Missing Image Data ::", "Image File/Path Name Missing!");
                    $extracted_data = "no_image_data";
                }
                else{
                    if(file_exists($extracted_data))
                        $extracted_data = file_get_contents($extracted_data);
                }

                $added_columns = array( 
                    'pdf_image'     =>  $extracted_data, 
                    );
            break;
	
            default:
                $added_columns = array( 
                    'text_data'     =>  $extracted_data, 
                    );
            break;
        }           
     
        // insert data into database 
        $columnData = array(
            'filename'                  =>      $filename,
            'filetype'                  =>      'application/pdf',
            'category'                  =>      $pdf_category,
            'date_uploaded'             =>      $theDateFormat,
            'upload_userid'             =>      $current_user,
            'pdf_doc_num'               =>      rand(0000001,9999999)
        );

        // if the filesize is less than or equal the max truncate file before insert, 
        // otherwise the file is too big save file to file system

        // store the size of the uoloaded file
        $upload_filesize = filesize($uploadFilename);

        if($this->pdm_large_file){
            $columnData['has_url'] = 1;
            $columnData['pdf_url'] = $this->pdm_upload_dir_agrs->wp_upload_dir->url.'/pdm_data/'.$filename;
            
            $query_data = array_merge($columnData, $added_columns);
            $this->pdm_execute_query($wpdb, $tablename,  $query_data);

        } else {
            // merege all the added columns into one array
            $query_data = array_merge($columnData, $added_columns);
            if($upload_filesize > $this->pdm_max_filesize){
                $this->pdm_split_execute_query($wpdb, $tablename, $uploadFilename, $query_data); 
             } else {
                $wpdb->show_errors();
                //  Get pdf file/data contents -  Reads entire file into a string
                // add the file contents to the column data array
                $query_data['pdf_data'] = (file_exists($uploadFilename)) ? file_get_contents($uploadFilename) : '';
                $this->pdm_execute_query($wpdb, $tablename, $query_data);
            }
        }
        
        $insert_rowID = $wpdb->insert_id;
        $this->pdf_DebugLog("Insert data into database for file::", $filename.':: insert id '.$insert_rowID);
        $this->pdf_DebugLog("Uploaded FILE SIZE::", $upload_filesize);
        //  if there was an error during the insert
        if($insert_rowID < 1){
            $this->pdf_DebugLog("SQL Query result::{$filename}", $wpdb->last_error);

            $uploadStatus = "failed";
            $status_message = "failed to add '{$filename}' to database.";
            
        }
        else {

            $this->pdf_DebugLog("SQL Query result::{$filename}", "*** SQL insert sucessful. ***");
            $uploadStatus = "success";
            $status_message  = "{$filename} added successfully.";
        }

        $wpdb_response = array(
            'upload_status'     =>  $uploadStatus,
            'wpdb_errorText'    =>  $wpdb->last_error,
            'status_message'    =>  $status_message,
            'filename'          =>  $filename,
            'percent'           =>  100
        );
        
        // collect /store the result of the process here
        $this->file_process_results[] = $wpdb_response;
 
        return $wpdb_response;
    }

    /**
     *
	 * @since 1.0.2
	 * 
     * Excecutes appropriate SQL query 
     * @param object    $wpdb
     * @param string    $tablename
     * @param object    $query_data
     * @param int       $rowID
     * @param string    $type
	 * @return integer
	 */
    private function pdm_execute_query($wpdb, $tablename, $query_data, $rowID = null, $type = 'insert')
    {
        switch($type){

            case 'update_concat':  
                $_query = $wpdb->prepare(
                        "UPDATE {$tablename} SET `pdf_data` = CONCAT(pdf_data, '%s') WHERE `{$tablename}`.`ID` = %d ",
                        $query_data, $rowID
                    );
                $wpdb->query($_query);    
            break;
            case 'update':  
           
                $_query = $wpdb->prepare(
                        "UPDATE {$tablename} SET `pdf_data` = '%s' WHERE `{$tablename}`.`ID` = %d ",
                        $query_data, $rowID
                );
               $wpdb->query($_query); 
            break;
            case 'insert':
            default:
                $wpdb->insert( $tablename, $query_data);
                $rowID = $wpdb->insert_id;
            break;
        }
        
        return($rowID);
    }
    
    /**
     *
     * @since 1.0.1
     * 
     * Extracts text from PDF document
     * @param string $input_filename
     * @return array
     */
    public function extractTextFromPDF($input_filename)
    {
        
        if(is_file($input_filename)){
 

        $this->pdf_DebugLog("Text Extraction: File Validation Check:: "," Passed!");

            // initialze output buffer and return value variables
            $arrOutput = null;
            $retval = null;
            $truncate_file = false;
            $force_img_only = $this->force_image_extraction;
            $outputType = 'text_data';
            $extraction = true;
        
            $file_size = filesize($input_filename);
            // sets the maximum up filesize limit, if file size is greater set the $truncate_file
            // flag and we'll break the file into chunks when inserting into the db
            $max_filesize = $this->pdm_max_filesize; 
            $truncate_file = ($file_size > $max_filesize) ? true : false;
            $tries = ($force_img_only == 1 || $this->pdm_large_file == true) ? 3 : 1;

            $this->pdf_DebugLog("Use pyTesseract On?:: ", $this->use_Tesseract);
            $this->pdf_DebugLog("Force Images On?:: ",$force_img_only);
            $this->pdf_DebugLog("Text Extraction: File Size:: ", $file_size);

            // Try all thress extraction methods until text or image has been
            while($extraction){

                switch($tries){
                    case 1:
                        // Build initial text extaction path for command to execute
                        // 1st attempt:
                        $pathToScript = $this->pdm_Python.' '.$this->pdm_PdfMiner;
                        $strCommand = $pathToScript." -t text ".$input_filename;
                    break;
                    case 2:
                        // Build secondary text extaction path for command to execute
                        // 2nd attempt:
                        if(!$this->use_Tesseract){
                            break;
                        }
                            
                        $pathToScript = $this->pdm_Python.' '.$this->pdm_plugin_dir.'inc/py/pyConvertPDF.py';
                        $strCommand = $pathToScript.' '.$input_filename.' "'. get_option("legoeso_pytesseract_path") .'" 6';
                        // adding a few more seconds to extend execution time of second command
                        set_time_limit(120);
                    break;
                    default:
                        // Build Image Extraction Commmand
                        // 3rd Attempt
                        $pathToScript = $this->pdm_Python.' '.$this->pdm_plugin_dir.'inc/py/getImages.py';
                        $strCommand = $pathToScript." ".$input_filename." {$this->pdm_upload_dir}";
                        # set the output type
                        $outputType = 'image_data';
                        $extraction = false;
                    break;
                }

                // Execute initial command
                exec($strCommand, $arrOutput, $retval);
                // remove all special characters and return the output
                $extractedData = ($outputType == 'image_data' ) ? implode("",$arrOutput) : $this->clean(implode("\t", $arrOutput));

                // count the length of the strings
                $strLen = strlen($extractedData);
                $extraction = ($strLen < 10 && $outputType == "text_data") ? true : false;
                // Add to debug log
                $this->pdf_DebugLog("Text Extraction Command Attempt {$tries}:: ",$strCommand);

                $tries++;
            }

            return array(
                'extracted_data'    => $extractedData,
                'output_type'       => $outputType,
            );
        }
    }
    /**
     *
	 * @since 1.0.2
	 * 
     * splits file into chunks then excecutes appropriate SQL query 
     * @param object $wpdb
     * @param string $tablename
     * @param string $filename
     * @param array  $columnData
	 * @return None
	 */
    private function pdm_split_execute_query($wpdb, $tablename, $filename, $columnData){
        
        if(!file_exists($filename)){
            return;
        }
        $buffer = $this->pdm_max_filesize;
        // get file size
        $file_size = filesize($filename);
        // number of parts to split
        $parts = $file_size / $buffer;

        // open file to read
        $file_handle = fopen($filename, 'r');

        // name of input file
        //$file_name = basename($filename);
        // initialize rowID
        $rowID = null;

        for($i=0; $i<$parts; $i++)
        {
            // read buffer sized amount from file
            $file_data = fread($file_handle, $buffer);
            if(isset($rowID)){
                // update row with new data from file
                // add_pdf_data($file_name, $file_part, $rowID, 'update_concat');
                $this->pdm_execute_query($wpdb, $tablename, $file_data, $rowID, 'update_concat');
            } else {
                // add the data and set the rowID on the first/initial loop, otherwise 
                // otherwise assume row will be updated
                $columnData['pdf_data'] = $file_data;
                $this->pdm_execute_query($wpdb, $tablename, $columnData);
                $rowID = $wpdb->insert_id;
            }
        }
        // close the main file handle
        fclose($file_handle);
    }

}
?>