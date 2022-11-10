<?php 
namespace Legoeso_PDF_Manager\Inc\Libraries;

use Legoeso_PDF_Manager as NS;
use Legoeso_PDF_Manager\Inc\Common as Common;
use Timer;
use \Imagick;

/**
 * Adding support for PDF Parser Library see
 * @since       1.1.0
 */
include __DIR__.'/class-parser.php';

/**
 * Class for processing PDF documents. Extends Utility_Functions class 
 * All documents  will be stored within the custom legoeso_file_storage table.
 * 
 *S
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
	 * @var      String    $start_time      The process end start time.
	 */
	private $set_start_time;

	/**
	 * The end time in milliseconds of when file/process ends.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $end_time        The process end time.
	 */
	private $set_end_time;

	/**
	 * The total time in milliseconds of file/process time.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $total_time      The total process time.
	 */
	private $total_time;

    /**
	 * Collects and stores the results of the processed files
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Array    $file_process_results Stores the results for each file 
     *                  processed.
	 */
	private $file_process_results = [];

    /**
	 * stores any errors during upload of the processed files
	 *
	 * @since    1.0.4
	 * @access   private
	 * @var      String    $pdm_upload_errs  stores any errors during upload of the processed files
	 */
	private $pdm_upload_errs;

    /**
	 * Specfifies and stores the upload dirctory of the processed files
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $pdm_upload_dir stores the upload dirctory of the processed files
	 */
	private $pdm_upload_dir;

    /**
	 * Specfifies and stores the upload dirctory of the processed files
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $pdm_upload_dir stores the upload dirctory args of the processed files
	 */
	private $pdm_upload_dir_agrs;

    /**
	 * Enable/disable image extraction of PDF documents - overrides saved settings if enabled text extraction will
	 * be disabled
     * 
	 * @since    1.0.1
	 * @access   private
	 * @var      Boolean    $force_image_extraction - only images will be extracted
	 */
	private $force_image_extraction;

     /**
	 * The directory location to the class libraries for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $pdm_library    The directory location to the class libraries for this plugin.
	 */
    public $pdm_library;

     /**
	 * The directory path location for the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      String    $pdm_plugin_dir    The directory path location for the plugin
	 */
    public $pdm_plugin_dir;

     /**
	 * Property used to store the filename used to save upload status
	 * @since    1.0.1
	 * @access   private
	 * @var      String   $pdm_status_filename   stores the filename used to save upload status
	 */
    private $pdm_status_filename;

     /**
	 * Sets the max_allowed_packet size in bytes, we'll use it as the limit before breaking the file into peices 
	 * @since    1.0.2
	 * @access   private
	 * @var      Integer   $pdm_max_filesize    used to stay below MySQL's allowed memory packet size
	 */
    private $pdm_max_filesize;

     /**
	 * specifies whether the file is a large file.
	 * @since    1.0.2
	 * @access   private
	 * @var      Boolean   $pdm_large_file - specifies wether the file uploaded is large.
	 */
    private $pdm_large_file = true;
    
     /**
	 * specifies valid file types/ 
	 * @since    1.0.2
	 * @access   private
	 * @var      Array   $pdm_valid_file_types - specifies the supported files types. 
	 */
    private $pdm_valid_file_types;

     /**
	 * stores global $_POST data 
	 * @since    1.2.0
	 * @access   private
	 * @var      Array   $_post_data - stores global $_POST data. 
	 */
    private $_post_data;

     /**
	 * stores extraction processing status counter 
	 * @since    1.2.0
	 * @access   private
	 * @var      Integer   $_post_data - stores global $_POST data. 
	 */
    protected $_status_counter = 1;

     /**
	 * flag use to indicate if pdf files should be saved to disk.
     * if false, files will be saved in database
	 * @since    1.2.0
	 * @access   private
	 * @var      Integer   $save_files_to_disk -  indicates if pdf files should be saved to disk. 
	 */
    private $save_files_to_disk = false;

	/**
	 * Specifies the tablename to be used with db queries and operations
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      String    $legoeso_db_tablename
	 */
	private $legoeso_db_tablename;

	/**
	 * Initializes class variables and set its properties.
	 *
	 * @since   1.0.0
     * @return  none
	 */
    public function __construct(){
        global $wpdb;
        $this->pdm_plugin_dir = NS\PLUGIN_NAME_DIR;
        $this->pdm_library = NS\PLUGIN_NAME_DIR.'inc/libraries/';
        $this->pdm_max_filesize = 13010000;
        $this->pdm_valid_file_types = ['application/pdf','application/x-zip-compressed'];

	    // specfiy tablename for database queries
		$this->legoeso_db_tablename = "{$wpdb->prefix}legoeso_file_storage";

        // Get current options set for force_image_extraction
        $this->force_image_extraction = (get_option("legoeso_force_image_enabled") == 'on') ? 1: 0;

        parent::__construct();
    }

    
    /**
	 * Gets the value of $_post_data field and returns sanitized values
	 *
	 * @since 1.2.0
	 * @return Array
	 */
    private function get_post_data(string $what, bool $raw = false) {
        return ($raw) ? $this->_post_data[$what] : $this->sanitize_postdata($this->_post_data[$what]);
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
	 * sets the value of $_post_data field
	 *
	 * @since 1.2.0
	 * @return none
	 */
    private function set_post_data($_post){
        $this->_post_data =  $_post;
    }

    /**
	 * Handles and processes the uploaded file
     * 
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.0.0
	 * @return none
	 */
    public function process_pdf_upload($_files, $_post_data){ 

        //
        /**
        * Step 1: 
        * Check to see if a file was submitted if not return nothing
        */
        // step out if varibale not set
        if (!isset($_files) || !isset($_post_data)){
            return;
        }

        // set the post data field
        $this->set_post_data($_post_data);
       
        //  get the upload directory info
        $set_upload_dir_info = unserialize( stripslashes($this->get_post_data('_pdm_upload_info', true) ) );
        $set_upload_dir_info['wp_upload_dir'] = unserialize(  $set_upload_dir_info['wp_upload_dir']); 
        $set_upload_dir_info['pdm_upload_status_filename'] = base64_decode($set_upload_dir_info['pdm_upload_status_filename']);
        $this->pdm_upload_dir_agrs = $set_upload_dir_info;

        // set the upload directory, including the file use to monitor the status of the process
        $this->pdm_upload_dir =  $this->pdm_upload_dir_agrs['pdm_upload_dir'];
        // get the filename user to save the processing status 
        $this->pdm_status_filename = $this->pdm_upload_dir_agrs['pdm_upload_status_filename'];

        //  override the current option for legoeso_force_image_enabled 
        $this->force_image_extraction = ($this->get_post_data('legoeso_force_image_enabled') == 'on') ? 1: 0;
       
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

        // return a list of filenames with the result objects for files that have failed. 
        function get_failed_files($f_results, $f_files){ 
            $a = array();
            foreach($f_files as $k => $v){
                $a[] = array('filename' => $f_results[$k]['filename'], 'results' => $f_results[$k]);
            }
            return empty($a) ? 0 : $a;
        };

        // do cleanup.  Remove files after files have been added to the database
        $this->clean_dir($this->pdm_upload_dir);

        //  send upload results back to ajax caller
        die( wp_json_encode(
            array(
                'total_files'       => count($file_results),
                'results'           => $file_results,
                '_status'           => 'complete',
                'failed'            => get_failed_files($file_results, $r_failed_files),
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
            $filename      = sanitize_file_name($_files['name'][$i]);
            $_file_type     = sanitize_file_name($_files['type'][$i]);
            $_tmp_filename  = $_files['tmp_name'][$i];
            
            //  save the curent progress for the progress bar. 
            $this->save_progress($i+1, $_num_of_files, $filename, $this->processPDFFile($filename, $_tmp_filename));
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

                 $this->pdf_DebugLog("Method: processZipFile(): **** Prepare to UNZIP FILE Type: $type Name:".$_files['name'][$index]." - ".$index,  $zip_file);
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
        $this->pdf_DebugLog("Method: unzipfiles(): Temp Directory ", $zip_tmp_name);
        $this->pdf_DebugLog("Method: unzipfiles(): Adding Directory ", $folder);
    
        //  create the temp directory
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
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
	 * @param String $filename
     * @param String $tmpFilename
	 * @return array
	 */
    private function processPDFFile($filename, $tmpFilename){
        // verify there is a filename 
        if(isset($filename) && isset($tmpFilename)){
          
            // if the uploaded filesize is double than the filesize limit lets store the file within the 
            // file system instead of the database
            // if(filesize($tmpFilename) >  ($this->pdm_max_filesize * 2)){
                // move the uploaded file for processing and get the directory information
                $file_info_arr =  $this->uploadFileToWp($filename, $tmpFilename, true);
                // set the large file flag to true
            //     $this->pdm_large_file = true;

            // } 
            // else {
            //     // move the uploaded file for processing and get the directory information
            //     $file_info_arr =  $this->uploadFileToWp($filename, $tmpFilename);
            //     // reset the large file flag to the default
            //     $this->pdm_large_file = false;
            // }


            $this->pdf_DebugLog("Uploaded DIR Args: Object::", wp_json_encode($this->pdm_upload_dir_agrs));
            $this->pdf_DebugLog("Uploaded File Information: Object::", wp_json_encode($file_info_arr));
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
    * usd by the JavaScript.  See also Admin::_file_upload_status_callback()
    *
	* @since 1.0.0
	* @param int $idx
    * @param int $total_files
    * @param array $arr_status
	* @return none
    */
    function save_progress($idx, $total_files, $_filename, $arr_status){
        

        if( !is_array($arr_status)){
            return;
        }
        
        // get the remaining time of the process   
        $time_remaining = $this->execTimeRemaining();
        
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

        // populate response array
        $_ajax_response = array(
            'percent'           =>  $percent_complete,
            'total_files'       =>  $total_files,
            'status'            =>  ($percent_complete == 100) ? 'complete' : 'processing',
            'file'              =>  $idx,
            'retries'           =>  $this->_status_counter += 1,
        );

        $ajax_response = array_merge($arr_status, $_ajax_response);
        
       try {
            // create directory to upload status files
            if(! file_exists($this->pdm_upload_dir_agrs['pdm_upload_dir'])) 
            { 
                //$this->pdf_DebugLog("Making file:: $status_filename", $this->pdm_upload_dir_agrs['pdm_upload_dir']);
                mkdir($this->pdm_upload_dir_agrs['pdm_upload_dir'], 0755, true); 
            } 
            file_put_contents($status_filename, wp_json_encode($ajax_response));
       } 
       catch(PDM_Exception_Error $e) {
            throw new Exception("Error updating Ajax Response Object. ".$e->getMessage());
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
	 * @param String $uploadFilename
     * @param String $filename
	 * @return array
	 */
    private function addFileToDatabase($uploadFilename, $filename){
        /**
        * Post the uploaded file to the WordPress database
        */
        // do two things.
        // 1. get the raw contents of the PDF file 
        // 2. Extract text from the pdf file so that it can be
        // parsable and searched.
        $this->pdf_DebugLog("Method: addFileToDatabase():", "Called...");

        global $wpdb;
        $wpdb->show_errors(true);

        //  set the tablename document will be inserted
        $tablename = $this->get_db_tablename();

        //  get the category the document will be associated with
        $pdf_category = ( $this->get_post_data('pdf_category') == -1 ) ? 'General' : $this->get_post_data('pdf_category');

        // initialize the rowID, will be used later to update the table data
        $insert_rowID = null;

        // pdf file path
        $pdf_has_path = -1;
        $pdf_fileversion = -1;
        $pdf_filepath = $this->pdm_upload_dir_agrs['wp_upload_dir']['path'].'/pdm_data/'.$filename;
       
        if(file_exists($pdf_filepath)){
            $pdf_has_path = 1;
            $pdf_fileversion = $this->get_pdfversion($pdf_filepath);
        }

        // create the date object to use in the query
        $theDate = date_create();
        $theDateFormat = date_format($theDate, 'Y-m-d');
        
        // get the current userid
        $logged_in_user = wp_get_current_user();
        $current_user = $logged_in_user->data->user_login;

        // columns we will insert data into database 
        $pdf_query_columns = array(
            'filename'           =>  sanitize_text_field($filename),
            'has_path'           =>  $pdf_has_path,
            'pdf_path'          =>  $pdf_filepath,
            'filetype'          =>  'application/pdf',
            'pdf_version'       =>  $pdf_fileversion,
            'category'          =>  $pdf_category,
            'date_uploaded'      =>  $theDateFormat,
            'upload_userid'      =>  $current_user,
            'pdf_doc_num'        =>  rand(0000001,9999999),
        );

        //  Extract the text from the PDF file and get the document properties
        if($pdf_extract_properties = $this->extractTextFromPDF($uploadFilename)){
            $extracted_textdata     = $pdf_extract_properties['extracted_data'];        // string
            $image_extracted    = $pdf_extract_properties['extracted_image'];       // bool
            $image_paths        = $pdf_extract_properties['image_paths'];  // string
            $pdf_filesize       = $pdf_extract_properties['pdf_filesize'];  // bool

           $pdf_extracted_columns = [            
                'text_data'         =>  $extracted_textdata,
                'pdf_filesize'      =>  $pdf_filesize,
                'has_img'           =>  ($image_extracted) ? 1 : 0,
                'image_url'         =>  $image_paths['img_url'],
                'image_path'        =>  $image_paths['img_path'],
            ];

            $pdf_query_columns = array_merge($pdf_query_columns, $pdf_extracted_columns);
        }

        $this->pdf_DebugLog(" Data:: 1 for Table: {$tablename}", $pdf_query_columns);
        // execute sql query
        $this->pdm_execute_query($wpdb, $tablename,  $pdf_query_columns);

        $insert_rowID = $wpdb->insert_id;

        //  if there was an error during the insert
        if($insert_rowID < 1){
            $uploadStatus = "failed";
            $status_message = "failed to add '{$filename}' to database.";
        }
        else {

            // $this->pdf_DebugLog("SQL Query result::{$filename}", "*** SQL insert sucessful. ***");
            $uploadStatus = "success";
            $status_message  = "{$filename} added successfully.";
        }

        $wpdb_response = array(
            'upload_status'     =>  $uploadStatus,
            'wpdb_errorText'    =>  $wpdb->last_error,
            'status_message'    =>  $status_message,
            'filename'          =>  $filename,
            'pdf_version'       =>  $pdf_fileversion,
            'percent'           =>  100
        );
        
        // collect /store the result of the process here
        $this->file_process_results[] = $wpdb_response;
        $this->pdf_DebugLog(" Data:: 2", $wpdb_response);

        return $wpdb_response;
    }

    /**
     *
	 * @since 1.0.2
	 * 
     * Excecutes appropriate SQL query 
     * @param object    $wpdb
     * @param String    $tablename
     * @param object    $query_data
     * @param int       $rowID
     * @param String    $type
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
     * @param String $input_filename
     * @return array
     */
    public function extractTextFromPDF($input_filename)
    {
        if(file_exists($input_filename)){

            $truncate_file      = false;
            $force_img_only     = $this->force_image_extraction;
            $extracted_image    = false;
            $extractedImagePath = null;
            
            $filesize = filesize($input_filename);
            // sets the maximum up filesize limit, if file size is greater set the $truncate_file
            // flag and we'll break the file into chunks when inserting into the db
            $max_filesize = $this->pdm_max_filesize; 
            //$truncate_file = ($filesize > $max_filesize) ? true : false;
          
            // create new instance of PdfParser
            $pdf_parser = new \Smalot\PdfParser\Parser();

            try {
                // parse the pdf file
                $_pdffile = $pdf_parser->parseFile($input_filename);
                // attempt to extract the text from the file, only extract text from 1st page
                $extractedData = trim( sanitize_text_field($_pdffile->getPages()[0]->getText()) );
          
                // clear pdf_parser reference in attempt to free its resources
                unset($pdf_parser);

                // if no data was extracted from file extract image
                if (strlen($extractedData) < 1){
                    $extractedData = "* NO DATA *";
                }

                $this->pdf_DebugLog("ExtractED Data:: ", $extractedData);

                // try to extract image data from PDF file
                if( $extractedImageInfo =  $this->extract_image_from_pdf($input_filename) ){
                    $extracted_image = true;
                }
                
                return [
                        'extracted_data'        =>  $extractedData,
                        'pdf_filesize'          =>  $filesize,
                        'image_paths'           =>  $extractedImageInfo,
                        'extracted_image'       =>  $extracted_image,
                    ];

            } catch(\Exception | \E_Error $e) { 
                
                // catch if error extracting the text from the pdf file
                // extract an image instead
                if(preg_match('/[Invalid object reference for $obj.]+/', $e->getMessage()) ){

                    if(empty($extractedData)){
                        $this->pdf_DebugLog("Text extraction failed for file '{$input_filename}'::", $e->getMessage());
                    }
                    else{
                        $this->pdf_DebugLog("An error occurred while processing the pdf file::", $e->getMessage());
                    }
                    //throw new Common\PDM_Exception_Error("Text extraction failed for file '{$input_filename}' ".$e->getMessage());
                }
 
            }
                
        }
        return false;
    }

    /**
	 * @since 1.1.0
     *  Converts a PDF file to JPEG Image using Imagick 
     * 
     * @param String $pdf_filename
	 * @return blob of raw image data
	 */
    private function extract_image_from_pdf($pdf_filename){

        if ( file_exists($pdf_filename) ){
            $_ftype = mime_content_type($pdf_filename);
            $wp_upload_dir = $this->pdm_upload_dir_agrs['wp_upload_dir']['path'];
            $legoeso_img_dir = '/pdm_data/images/';

            // local path to image directory
            $legoeso_local_img_dir = $wp_upload_dir.$legoeso_img_dir;
            // image filename
            $legoeso_img_filename = rand(01,999).(time()+strlen($pdf_filename)).'.jpg';
            
            // local image path
            $legoeso_img_path = $wp_upload_dir.$legoeso_img_dir.$legoeso_img_filename;

            // pdf preview image url path
            $_image_url = $this->pdm_upload_dir_agrs['wp_upload_dir']['url'].$legoeso_img_dir.$legoeso_img_filename;
            
            // attempt to extract image only if the file is pdf
            if ($_ftype == 'application/pdf'){                
                $get_image = new \Imagick();
                // extract on the first page
                $get_image->readImage($pdf_filename."[0]");
                $get_image = $get_image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                $get_image->resizeImage( 350, 500, imagick::FILTER_BOX, 0, 0);
                $get_image->setImageFormat( 'jpg' );
                $pdf_image_data = $get_image->getImageBlob();
                $get_image->clear();
               
                // lets check to see if the directory exists if not create it
                if (!file_exists($legoeso_local_img_dir)) {
                    mkdir($legoeso_local_img_dir, 0755, true);
                }

                // lets check to see if the directory was created, if so attempt add
                if(file_exists($legoeso_local_img_dir)){
                    try {
                        if(file_put_contents($legoeso_img_path, $pdf_image_data)){
                            if(file_exists($legoeso_img_path)) {
                                return ['img_url' => $_image_url, 'img_path' => $legoeso_img_path];
                            }
                        }
                    } 
                    catch (Exeception $e){
                        throw new Exception("Error creating image directory {$legoeso_local_img_dir}".$e->getMessage());
                    }
                }
            }
        }
        return false; 
    }

    /**
     *
	 * @since 1.0.2
	 * 
     * splits file into chunks then excecutes appropriate SQL query 
     * @param object $wpdb
     * @param String $tablename
     * @param String $filename
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