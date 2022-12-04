<?php 
namespace Legoeso_PDF_Manager\Inc\Libraries;

use Legoeso_PDF_Manager as NS;
use Legoeso_PDF_Manager\Inc\Common as Common;
use Timer;
use \Imagick;

/**
 * Add support for Smlot PDF Parser Library see
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2021-02-09
 *
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 * 
 * @since       1.1.0 - Legoeso PDF Manager
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
	 * Property used to store the filename used to save upload status
	 * @since    1.0.1
	 * @access   private
	 * @var      String   $pdm_status_filename   stores the filename used to save upload status
	 */
    private $pdm_status_filename;

     /**
	 * Stores the current scripts memory info, used to help process the current script 
	 * @since    1.0.2
	 * @access   private
	 * @var      Array   $pdm_mem_info    used to stay below PhP's allowed memory limit
	 */
    private $pdm_mem_info = [];

     /**
	 * Stores the current filesize for the file being processed 
	 * @since    1.0.2
	 * @access   private
	 * @var      Int   $pdm_filesize  store filesize in bytes
	 */
    private $pdm_filesize = 0;

    /**
	 * Sets the filesize the script considers a large file
	 * @since    1.0.2
	 * @access   private
	 * @var      Int   $pdm_max_filesize    used to stay below PhP's allowed memory limit
	 */
    private $pdm_max_filesize;

    /**
	 * Sets maximum pages document can have for text extraction, documents with pages > 
     * than max pages will skip text extraction
     * 
	 * @since    1.0.2
	 * @access   private
	 * @var      Int   $max_pages_to_extract    don't extract documents with pages > than max
	 */
    private $max_pages_to_extract;

     /**
	 * specifies whether the file is a large file.
	 * @since    1.0.2
	 * @access   private
	 * @var      Boolean   $pdm_large_file - specifies wether the file uploaded is large.
	 */
    private $pdm_large_file = false;
    
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
	 * Store the info for the current file that is being processed
	 *
	 * @since    1.2.2
	 * @access   private
	 * @var      Array    $legoeso_db_tablename
	 */
    private $process_file_info = [];

	/**
	 * Initializes class variables and set its properties.
	 *
	 * @since   1.0.0
     * @return  none
	 */
    public function __construct(){
        global $wpdb;
        
        // set the max filesize i.e 25MB
        $this->pdm_max_filesize = 26214400;
        
        // set the max number of pages to extract text from
        $this->max_pages_to_extract = 25;

        // log memory limit for debugging and file processing
        $mem_limit = $this->return_bytes( ini_get('memory_limit') );

        // set server mem info
        $this->pdm_mem_info = ['mem_limit' => $mem_limit, 'max_filesize'  => abs($mem_limit / 2), ];
        
        // sets the valid mime type to process   
        $this->pdm_valid_file_types = ['application/pdf','application/x-zip-compressed'];

	    // specfiy tablename for database queries
		$this->legoeso_db_tablename = "{$wpdb->prefix}legoeso_file_storage";

        // Get current options set for force_image_extraction
        $this->force_image_extraction = 0;

        parent::__construct();
    }

    /**
     * sets the filesize for the file being processed
     * @since 1.2.1
     * 
     */
    private function set_working_filesize(int $filesize ){
        $this->pdm_filesize = abs($filesize);
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
	 * Returns the value of $_post_data field and returns sanitized values
	 *
	 * @since 1.2.0
	 * @return Array
	 */
    private function get_post_data(string $what, bool $raw = false) {
        return ($raw) ? $this->_post_data[$what] : $this->sanitize_postdata($this->_post_data[$what]);
    }

    /**
     * returns true if large file is being processed
     * 
     * @since 1.2.1
     * @return Boolean
     */
    private function is_large_file(): bool {
        return $this->pdm_large_file;
    }

    /**
     * return the filesize for the current filesize
     * @since 1.2.1
     * @return Int
     */
    public function get_working_filesize(){
        return $this->pdm_filesize;
    }

	/**
	 * returns plugin database tablename used by WP
	 * @since 1.2.0
	 * @return String
	 */
	private function get_db_tablename(){
		return $this->legoeso_db_tablename;
	}

    /**
     * returns max filesize to parse/extract text
     * @since 1.2.1
     * @return Int
     */
    private function get_max_filesize(){
        return $this->pdm_max_filesize;
    }

    /**
     * returns min memory limit to use before raising memory limit
     * 
     * @since 1.2.1
     */
    private function get_min_peak_limit(){
        $mem = ini_get('memory_limit');
        $mem = $this->return_bytes($mem);
        return ( ceil( $mem / 2) );
    }

     /* returns current value option 'force_image_extraction' aka generate pdf preview 
     * 
     * @since 1.2.1
     */
    private function get_force_image_extraction(){
        return $this->force_image_extraction;
    }

    /* sets the current value option for 'force_image_extraction' aka generate pdf preview 
     * 
     * @since 1.2.1
     */
    private function set_force_image_extraction($option){
        $this->force_image_extraction = $option;
    }

    /**
     * temporary sets the memory limit for the script
     * 
     * @since 1.2.1
     */
    private function raise_memory_limit(){
        ini_set('memory_limit', '768M');
    }

    /**
     * restores the original memory limit
     * 
     * @since 1.2.1
     */
    private function reset_memory_limit(){
        gc_collect_cycles();
        ini_restore('memory_limit');
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
        $this->set_force_image_extraction( ($this->get_post_data('legoeso_force_image_enabled') == 'on') ? 1: 0) ;
       
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
        include wp_normalize_path(plugin_dir_path (__DIR__)).'libraries/class-timer.php';

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
        $this->pdf_DebugLog("File Process Start Time:", $sTimer->startTimer());
        
        //  Count the number of files submitted
        $file_count = count($_submitted_files['name']);

        // check files for errors
        check_files_for_errors($_submitted_files);

        // filter and process the uploaded files
        $this->processFileUploads($this->filter_file_types($_submitted_files));

        // finalize the process
        $this->completeProcess();

        // log timer
        $this->pdf_DebugLog("File Process End Time:",  $sTimer->stopTimer());
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

        $this->pdf_DebugLog("({$_num_of_files }) File(s) to be processed: [Object]::", $_files);
        $this->pdf_DebugLog("Upload Directory Info: [Object]::", wp_json_encode($this->pdm_upload_dir_agrs));

        // loop over the row of  files
        for($i=0; $i < $_num_of_files;$i++){
            // get the file information for each file
            $filename       = sanitize_file_name($_files['name'][$i]);
            $_file_type     = sanitize_file_name($_files['type'][$i]);
            $_tmp_filename  = $_files['tmp_name'][$i];

            // saves/stores info about the current file process
            $this->process_file_info = ['filename' => $filename, 'id' => ($i+1), 'total_files' => $_num_of_files];

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

        // $this->pdf_DebugLog("** Types:: ",   $_file_types);
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

                 $this->pdf_DebugLog("Unzipping file Type: $type Name:".$_files['name'][$index]." - ".$index,  $zip_file);
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

        $this->pdf_DebugLog("* Extracted Files: 'unzipped' ::",  $unzipped);
        $this->pdf_DebugLog("* Updated: '_files' Array ::",  $_files);

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
        // $this->pdf_DebugLog("Temp Directory ", $zip_tmp_name);
        // $this->pdf_DebugLog("Adding Directory ", $folder);
    
        //  create the temp directory
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        // unzip and filter the files
        if (unzip_file($zip_tmp_name, $folder) === True) {
            $filepaths = $this->dir_tree($folder);
            $_file_type = '';
            foreach ($filepaths as $k => $filepath) {
                
                if(file_exists($filepath)){
                    // Get the file type
                    $_file_type =  mime_content_type($filepath);
                }
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
        $this->pdf_DebugLog("* FILES EXTRACTED *:: ", $_unzipped_files);
    
    }
    
    /**
	 *  Processess a single PDFFile for futher processing.
	 *
	 * @since 1.0.0
	 * @param String $filename
     * @param String $tmpFilename
	 * @return Array
	 */
    private function processPDFFile($filename, $tmpFilename){

        // verify if we have a valid file i.e. if file can be located 
        if( (isset($filename) && isset($tmpFilename)) && file_exists($tmpFilename)){

            // set current working filesize for later use
            $this->set_working_filesize( filesize($tmpFilename) );

            // used to obsure the original filename for added security
            $secure_filename = rand(2000001,9999999).'.pdf';

            // move the uploaded file for processing, specify new filename and get the directory information
            $file_info_arr =  $this->uploadFileToWp($secure_filename, $tmpFilename, true);

            // if the filesize is larger than the limit/max filesize, flag as large file
            if($this->get_working_filesize() >  $this->get_max_filesize()){
                // set the large file flag to true
                $this->pdm_large_file = true;
            }

            $this->pdf_DebugLog("Uploaded File Information: Object::", wp_json_encode($file_info_arr));
            
            // Get the path to the file that was uploaded
            $fileUploaded = $file_info_arr['file'];
            
            if(!is_wp_error($file_info_arr)){
                $this->pdf_DebugLog("Upload Status: File successfully uploaded!::", "Adding file to database");
                // add file to database / return an array results
                return $this->addFileToDatabase($fileUploaded, $filename);
            }
        }

        $this->pdf_DebugLog("pload Status: File NOT uploaded!::", "Failed Upload.");
        return ['upload_status' => 'failed', 'status_message' => 'File NOT uploaded!', 'filename'  =>  $filename, 'percent' => 100];
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
    private function save_progress($idx, $total_files, $_filename, $arr_status){
        
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
            'filename'          =>  esc_html($_filename),
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
     * sends Ajax respone/message of current file being procesed
     * 
     * @since 1.2.2 
     * @param int $idx
     * @param int $total_files
     * @param array $arr_status
	 * @return none
     */
    private function send_progress($message = 'Processing '){
        $fileinfo = $this->process_file_info;
        $idx = $fileinfo['id'];
        $total_files = $fileinfo['total_files'];
        $filename = $fileinfo['filename'];

        // message to send back as response
        $status_response = array(
            'message'    =>  esc_html($message),
        );
        // sends info back to sercer
        $this->save_progress($idx, $total_files, $filename, $status_response);
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
    private function addFileToDatabase($pdf_filepath, $filename){
        /**
        * Initialize wp database & variables
        */
        global $wpdb;
        $wpdb->show_errors(true);
        
        //  set the tablename document will be inserted
        $tablename = $this->get_db_tablename();
        
        //  get the category the document will be associated with
        $pdf_category = ( $this->get_post_data('pdf_category') == -1 ) ? 'General' : $this->get_post_data('pdf_category');

        // initialize default row values, will be used later to update the table data
        $insert_rowID = null;
        $pdf_has_path = -1; // pdf file path
        $pdf_fileversion = -1;

        // create the date object to use in the query
        $theDate = date_create();
        $theDateFormat = date_format($theDate, 'Y-m-d');

        // get the current userid
        $logged_in_user = wp_get_current_user();
        $current_user = $logged_in_user->data->user_login;

        // initialize image properties
        $force_img_only     = $this->get_force_image_extraction();

        $extracted_image    = false;
        $uploadStatus       = 'failed';
        $status_message     = 'incomplete';
        $pdf_fileversion    = '0';

        if(file_exists($pdf_filepath)){
       
            // get the pdf version
            $pdf_has_path = 1;
            $pdf_fileversion = $this->get_pdfversion($pdf_filepath);

            // default columns used to insert data into database 
            $pdf_query_columns = array(
                'filename'           =>  sanitize_title_with_dashes($filename),
                'has_path'           =>  $pdf_has_path,
                'pdf_path'           =>  realpath($pdf_filepath),
                'filetype'           =>  'application/pdf',
                'pdf_version'        =>  $pdf_fileversion,
                'category'           =>  $pdf_category,
                'date_uploaded'      =>  $theDateFormat,
                'upload_userid'      =>  $current_user,
                'pdf_doc_num'        =>  rand(0000001,9999999),
            );

            /**
             * Begin processing file
            */
            // attempt text extraction only, no preview image will be added to database for document
            $this->pdf_DebugLog("Force Image Extraction?", $force_img_only);

            if($force_img_only == 0){

                //  Extract the text from the PDF file and get the document properties
                if($pdf_extract_properties = $this->extractTextFromPDF($pdf_filepath)){

                    $extracted_textdata     = $pdf_extract_properties['extracted_data'];  // string

                    // add text extraction data to columns array
                    $pdf_extracted_columns = [            
                        'text_data'         =>  $extracted_textdata,
                        'pdf_filesize'      =>  $this->get_working_filesize(),
                    ]; 

                    $metadata = $pdf_extract_properties['pdf_metadata'];

                    // add metadata to columns
                    if(is_array($metadata)){
                        $pdf_query_columns = array_merge($pdf_query_columns, $metadata);
                    }

                    $pdf_query_columns = array_merge($pdf_query_columns, $pdf_extracted_columns);
                    $pdf_extract_properties = null;
                }
                else {
                    // send status back to the Ajax caller
                    $this->send_progress("Text extraction failed.");
                    // append filesize/version to metadata
                    $metadata = ['metadata' => wp_json_encode(['FileSize' => $this->make_file_size_readable($this->get_working_filesize()), 'PdfVersion' => $pdf_fileversion])  ];
                
                    // merge metadata to columns array
                    if(is_array($metadata)){
                        $pdf_query_columns = array_merge($pdf_query_columns, $metadata);
                    }
                }

                // Attempt to extract image data from PDF file
                if($extractedImageInfo =  $this->extract_image_from_pdf($pdf_filepath)){
                    $extracted_image = true;
                }

                // if image extraction successful, get path info to image
                // and merge with standard columns
                if($extracted_image){
                    $image_paths = $extractedImageInfo;   // array
                    $image_paths['has_img'] = 1;
                    $pdf_query_columns = array_merge($pdf_query_columns, $image_paths);
                }
                else {
                    // send status back to the Ajax caller
                    $this->send_progress("Image extraction failed.");
                }



            } 
            else {

                // Attempt to extract image data from PDF file
                if($extractedImageInfo =  $this->extract_image_from_pdf($pdf_filepath)){
                    $extracted_image = true;
                }

                // if image extraction successful, get path info to image
                // and merge with standard columns
                if($extracted_image){
                    $image_paths = $extractedImageInfo;   // array
                    $image_paths['has_img'] = 1;
                    $pdf_query_columns = array_merge($pdf_query_columns, $image_paths);
                }
                else {
                    // send status back to the Ajax caller
                    $this->send_progress("Image extraction failed.");
                }

                // append filesize/version to metadata
                $metadata = ['metadata' => wp_json_encode(['FileSize' => $this->make_file_size_readable($this->get_working_filesize()), 'PdfVersion' => $pdf_fileversion])  ];
                
                // merge metadata to columns array
                if(is_array($metadata)){
                    $pdf_query_columns = array_merge($pdf_query_columns, $metadata);
                }

            }
        
            // execute sql query to insert file data into the database
            $this->pdm_execute_query($wpdb, $tablename,  $pdf_query_columns);

            // if successful obtain row id
            $insert_rowID = $wpdb->insert_id;

            //  if there was an error during the insert
            if($insert_rowID < 1){
                $uploadStatus = "failed";
                $status_message = "failed to add '{$filename}' to database.";
                // send status back to the Ajax caller
                $this->send_progress($status_message);
            }
            else {
                $uploadStatus = "success";
                $status_message  = "{$filename} - added successfully.";
                // send status back to the Ajax caller
                $this->send_progress($status_message);
            }
        }

        // package results
        $wpdb_response = array(
            'upload_status'     =>  $uploadStatus,
            'wpdb_errorText'    =>  $wpdb->last_error,
            'status_message'    =>  $status_message,
            'filename'          =>  $filename,
            'pdf_version'       =>  $pdf_fileversion,
            'percent'           =>  100
        );
        
        // collect/store the result of the process here
        $this->file_process_results[] = $wpdb_response;
        //$this->pdf_DebugLog("Database results: Data:: 2", $wpdb_response);
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
        // log the following stats for debugging
        $mem_usage = memory_get_usage(true);
        $filesize = $this->get_working_filesize();

        // if the size of the file is less than the max filesize
        if( ( file_exists($input_filename)) && ($filesize < $this->get_max_filesize()) ){
            // initialize variables
            $extractedImagePath = null;
            $extractedImageInfo = null;
            $extractedData = '';
            $pdf_metadata = [];
            $_mem_info = $this->pdm_mem_info;
            $_mem_peak_usage = memory_get_peak_usage(true);

            try {

                // if the current peak memory usage is greater than min peak, temporarily raise limit
                if($_mem_peak_usage >= $this->get_min_peak_limit() ){
                    // temporarily increases memory_limit
                    $this->raise_memory_limit();
                    $this->pdf_DebugLog("Method: extractTextFromPDF(): ", "Raising memory limit.");
                }

                    // create new instance of PdfParser config
                    $_config = new \Smalot\PdfParser\Config();
                    $_config->setFontSpaceLimit(-60);
                    $_config->setDecodeMemoryLimit(102400000);
                    $_config->setRetainImageContent(false);

                    // create new pdfParser object
                    $pdf_parser = new \Smalot\PdfParser\Parser([], $_config);

                    // parse the pdf file and obtain data
                    $_pdffile = $pdf_parser->parseFile($input_filename);

                    // get the number pages document contains
                    $number_pages = count($_pdffile->getPages());

                    // only attempt to extract text if the file contains < the max pages allowed
                    if($number_pages <= $this->max_pages_to_extract){
                        $extractedData = sanitize_text_field($_pdffile->getText());
                    } 

                    // if no data was extracted from file extract image
                    if ( strlen($extractedData) < 1 ){
                        $extractedData = "* NO DATA *";
                    }

                    // get pdf metadata
                    $pdf_metadata = $_pdffile->getDetails();

                    // append filesize to metadata
                    $pdf_metadata['FileSize'] = $this->make_file_size_readable($this->get_working_filesize());
                    $pdf_metadata['PdfVersion'] = $this->get_pdfversion($input_filename);
                    //$this->pdf_DebugLog("Meta Data:",  json_encode($pdf_metadata));
                  
                    // clear pdf_parser reference in attempt to free its resources
                    $pdf_parser = null;

                    return [
                            'extracted_data'        =>  $extractedData,
                            'pdf_metadata'          =>  ['metadata' => wp_json_encode($pdf_metadata),],
                            'pdf_filesize'          =>  $filesize,
                            'status'                =>  'complete',
                            ];

            } 
            catch(\Exception | \E_Error $e) { 
                
                // catch if error extracting the text from the pdf file
                // extract an image instead
                if(preg_match('/[Invalid object reference for $obj.]+/', $e->getMessage()) ){

                    if(empty($extractedData)){
                        $this->pdf_DebugLog("Text extraction failed for file '{$input_filename}'::", $e->getMessage().", Line:".$e->getLine());
                    }

                    return [
                        'extracted_data'        =>  "* NO DATA *",
                        'pdf_filesize'          =>  $filesize,
                        'pdf_metadata'          =>  '',
                        'status'                =>  'failed',
                        'error_message'         =>  $e->getMessage(),
                        ];
                }
            }  
        }
        else {
            // $_config = new \Smalot\PdfParser\Config();
            // $_config->setFontSpaceLimit(-60);
            // reducing deocode memory size for large files
            // 
            // fix processing to say this must be a large file please wait
            // $_config->setDecodeMemoryLimit(43361254);
            // $_config->setRetainImageContent(false);
            // $this->raise_memory_limit();

            // $this->pdf_DebugLog("Method: extractTextFromPDF(): Large File:", memory_get_usage(true));
            // $this->pdf_DebugLog("Method: extractTextFromPDF():2 Large File:", memory_get_peak_usage(true));
            try{
                // function get_pdf_prop($pdfdata)
                // {

                //     if(!$pdfdata)
                //         return false;
                //     //Extract cross-reference table and trailer
                //     if(!preg_match("/xref[\r\n]+(.*)trailer(.*)startxref/s", $pdfdata, $a))
                //         return false;
                //     $xref = $a[1];
                //     $trailer = $a[2];
                    
                //     print($s);exit();  
                    
                    
                //     //Extract Info object number
                //     if(!preg_match('/Info ([0-9]+) /', $trailer, $a))
                //         return false;
                //     $object_no = $a[1];
                
                //     //Extract Info object offset
                //     $lines = preg_split("/[\r\n]+/", $xref);
                //     $line = $lines[1 + $object_no];
                //     $offset = (int)$line;
                //     if($offset == 0)
                //         return false;
                
                //     //Read Info object
                //     fseek($f, $offset, SEEK_SET);
                //     $s = fread($f, 1024);
                //     fclose($f);
                
                //     //Extract properties
                //     if(!preg_match('/<<(.*)>>/Us', $s, $a))
                //         return false;
                //     $n = preg_match_all('|/([a-z]+) ?\((.*)\)|Ui', $a[1], $a);
                //     $prop = array();
                //     for($i=0; $i<$n; $i++){
                //         $prop[$a[1][$i]] = $a[2][$i];
                //     }
                        
                    
                //     return $prop;
                // }
        
            

                // create new pdfParser object
                // $pdf_parser = new \Smalot\PdfParser\Parser([], $_config);

                // $this->reset_memory_limit();

                // // parse the pdf file and obtain data
                // $_pdffile = $pdf_parser->parseFile($input_filename);

                // // get pdf metadata
                // $pdf_metadata = $_pdffile->getDetails();
            

                // $this->reset_memory_limit();

                // $pdf_data = file_get_contents($input_filename, false, null, -16384);
                // $meta = preg_match_all('/\/[^\(]*\(([^\/\)]*)/', $pdf_data, $matches);
                // $matches = get_pdf_prop($pdf_data);
                // $this->pdf_DebugLog("Method: extractTextFromPDF():Meta Large File:", $matches);

                // $pdf_data = null;
                // $matches = null;
                // return [
                //     'extracted_data'        =>  '*NO DATA*',
                //     'pdf_metadata'          =>  ['metadata' => wp_json_encode($meta),],
                //     'pdf_filesize'          =>  $filesize,
                //     'status'                =>  'complete',
                $this->pdf_DebugLog("File too Large:", $input_filename);

                    return false;
            }
            catch(\Exception $e) {

                return false;

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
            $legoeso_img_dir = '/legoeso_pdm_data/images/';

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
                try{
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
                        if(file_put_contents($legoeso_img_path, $pdf_image_data)){
                            if(file_exists($legoeso_img_path)) {
                                $pdf_image_data = null;
                                return ['image_url' => $_image_url, 'image_path' => realpath($legoeso_img_path)];
                            }
                        }
                        else {
                            throw new Common\PDM_Exception_Error("Error image {$legoeso_local_img_dir}: - ".$e->getMessage());
                        }
                    }
                }
                catch(\Exception | \E_Error $e) {
                    //plugin_basename()
                    return ['image_url' => plugin_dir_url(__DIR__).'/assets/'.'no_image.png', 'image_path' => plugin_dir_path( __DIR__ ).'assets/no_image.png'];
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

        $buffer = $this->get_working_filesize();

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