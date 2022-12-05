<?php
namespace Legoeso_PDF_Manager\Inc\Common;

use Legoeso_PDF_Manager as NS;
use \ErrorException;
/**
 * Utility Class - defines helper utility functions.
 *
 * Defines helper functions used by the plugin 
 *
 * @link       https://www.legoeso.com
 * @since      1.0.0
 *
 * @author    Torvis Wesley
 */ 
class Utility_Functions {

    
    /**
     * @param object collects execption objects 
     */
    public $Exception_Error = [];

    /**
     * Sets the max number of day before file is removed from the server
     * @since   1.2.2
     * @access  private
     * @var     integer
     * 
     */
    private $max_file_age = 8; // zipi files created by this plugin will be deleted after 7 days

    public function __construct() {

        //   set the exeception handler
        set_error_handler(function ($severity, $message, $file, $line){
            if( 0 === error_reporting()){
                // error was suppressed with @-operator
                return false;
            }
            throw ( 
                (new PDM_Exception_Error($message, 0, $severity, $file, $line))
                ->setErrorFile($file)
                ->setErrorLine($line) 
            );
        });
            
    }
       
    /**
     * Helper Function: writes error/log messages to the webserver's log file
     * 
     * @since 1.0.0
     * @param string $message
     * @return string
     */
    public function pdm_docs_log($message)
    {
        if (WP_DEBUG === true) {
        
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
        return $message;
    }

    /**
     * Extends the pdm_docs_log() function 
     * 
     * @since 1.0.1
     * @param string $headerMsg
     * @param string $resultMsg
     * @return none
     */
    public function pdf_DebugLog($headerMsg, $resultMsg=''){
        if(WP_DEBUG === true){
            $headerMsg = (empty($headerMsg)) ? "Default Header:" : $headerMsg;
            $this->pdm_docs_log(debug_backtrace()[1]['function'].'() '.$headerMsg);

            if(!is_array($resultMsg)){
                $this->pdm_docs_log("\t".$resultMsg);

            } 
            elseif(is_array($resultMsg)){
                $this->pdm_docs_log("\t".wp_json_encode($resultMsg));
            }    
        }
    }

    /**
     * Uploads the specified file to the WordPress upload directory
     * 
     * @since 1.0.1
     * @param string    $filename
     * @param object    $file_contents
     * @param string    $pdm_upload_directory
     * @param boolean   $return_dirinfo_only
     * @return boolean
    */
    public function pdm_upload($filename, $file_contents, 
        $pdm_upload_directory = '', $return_dirinfo_only = false){
        //  check if a string was passed
        if(!empty($filename)){
        
            $file_info = [];

            //  get the WordPress upload directory
            $wp_upload_dir = wp_upload_dir();
            $pdm_upload_dir = (!empty($pdm_upload_directory)) ? $pdm_upload_directory : $wp_upload_dir['path'].'/legoeso_pdm_data';

            $str_filepath = $pdm_upload_dir.'/'.$filename;

            $file_info['wp_info'] = $wp_upload_dir;
            $file_info['file'] = $str_filepath;
            $file_info['path'] = $pdm_upload_dir;
        
            // $this->pdf_DebugLog("Current Upload Directory:: ", $pdm_upload_dir);
            // $this->pdf_DebugLog("Full file path to upload:: ", $str_filepath);

            if(!file_exists($pdm_upload_dir)){
                mkdir( $pdm_upload_dir, 0755, true);
            }
            // returns only the directory info
            if($return_dirinfo_only){
                return $file_info;
            } else {
                // move file to specified directory
                if(file_put_contents($str_filepath, $file_contents)){      
                    $file_info['size'] = filesize($str_filepath);
                    return $file_info;
                }
            }
        }
        return false;
    }

    /**
     * Removes special characters from string
     * @since 1.1.0
     * @param string $string
     * @return string
     */
    public function get_pdfversion($pdf_file_path){
        if(file_exists($pdf_file_path)){
            return sanitize_text_field(substr(file_get_contents($pdf_file_path), 1, 9));
        }
    }
        
    /**
     * callback function used by WP Cron scheduled task 
     * 
     * @since 1.2.2
     * @return 
     */
    public function legoeso_cleanup($clean_all = false){
        // get WP upload directory information
        $wp_upload_dir = wp_upload_dir();
        $files_to_delete = [];

        if($clean_all){
            // clean up / remove all plugin files and directories
            return $this->clean_dir($wp_upload_dir['basedir'], true);
        }
        // get list of files that can be removed/deleted
        $files_to_delete = array_diff($this->legoeso_dir_tree($wp_upload_dir['basedir'],''), $this->get_valid_filepaths());

        // delete the files and return list to caller
        return ( $this->delete_files($files_to_delete) );
    }

    /**
     *  Recursively removes all legoeso_pdm_data files/directories and its subdirectories
     *  
     * @since 1.0.1
     * @param string    $directory
     * @param boolean   $uninstall - set to true delete all files/folders
     * @return boolean 
     */
    public function clean_dir($directory, $uninstall = false){
        if(is_dir($directory) ){   
            $directory = wp_normalize_path($directory);   
            try 
            {
                $files = array_diff(scandir($directory), array('.', '..'));
                
                if($uninstall){
                    foreach($files as $file){
                        if(is_dir("$directory/$file")){
                            $this->clean_dir("$directory/$file", $uninstall); 
                        } 
                        else { 
                            if (preg_match('/[\/]legoeso_pdm_data/m', $directory)) {
                                $this->pdf_DebugLog("Deleting File:", "[$directory/$file]");
                                unlink("$directory/$file");
                            }
                        }
                    }
                    if (preg_match('/[\/]legoeso_pdm_data/m', $directory)) {
                        $this->pdf_DebugLog("Removing DIR:", "[$directory]");
                        return rmdir($directory);
                    }
                }
                else{
                    // removes all files and directories up to the specified dir
                    foreach($files as $file){
                        (is_dir("$directory/$file")) ? $this->clean_dir("$directory/$file") : unlink("$directory/$file");
                    }
                    return rmdir($directory);
                } 

            } catch(PDM_Exception_Error $e) {
                $this->Exception_Error[] = $e->getErrorObject($e);
                return false;
            }
            return true;
        }
    }

    /**
     * Validates if zip file has expired, returns true if so
     * 
     * @since 1.2.2
     * @param string $file
     * @return bool true if files is expired
     */
    private function is_expired($file){
        if( file_exists($file) && ($this->get_file_age($file) >= $this->max_file_age) ){
            return true;
        }
        return false;
    }

    /**
     * removes unmapped files within the legoeso pdm_data directory 
     * 
     * @since 1.2.2
     * @param array - list of filename/paths to remove
     * @return bool - returns true if  passed objectis an array
     */
    function delete_files($unmapped_files){
        if(is_array($unmapped_files)){
            $deleted_files = [];
            foreach($unmapped_files as $file){
                if(file_exists($file)){
                    if(mime_content_type($file) == 'application/x-zip-compressed'){
                        if($this->is_expired($file)){
                            unlink($file);
                            $deleted_files[] = $file;
                        }
                    }
                    else {
                        unlink($file);
                        $deleted_files[] = $file;
                    }
                }
            }
            return $deleted_files;
        }
    }

    /**
     * Recursively reads the given directory structure and returns list of files in array
     * 
     * @since 1.0.1
     * @param string $dir
     * @return array 
     */
    public function dir_tree($dir)
    {
        // http://www.php.net/manual/de/function.scandir.php#102505
        $paths = [];
        $stack[] = $dir;
        while ($stack) {
            $thisdir = array_pop($stack);
            if ($dircont = scandir($thisdir)) {
                $i = 0;
                while (isset($dircont[$i])) 
                {
                    if ($dircont[$i] !== '.' && $dircont[$i] !== '..') 
                    {
                        $current_file = "{$thisdir}/{$dircont[$i]}";

                        if (is_file($current_file)) {
                            $paths[] = "{$thisdir}/{$dircont[$i]}";
                        } elseif (is_dir($current_file)) {
                            $paths[] = "{$thisdir}/{$dircont[$i]}";
                            $stack[] = $current_file;
                        }
                    }
                    $i ++;
                }
            }
        }
        return $paths;
    }

    /**
     * Recursively reads the given directory structure, 
     * only returns .pdf or .jpg files located in WP_upload/legoeso_pdm_data directory
     * 
     * @since 1.2.2
     * 
     * @param string $dir - directory to search
     * @param string $type - one of three types pfd, jpg, zip
     * @return array
     */
    function legoeso_dir_tree($dir, $type = 'pdf'){
        $paths = [];
        $stack[] = $dir;

        while($stack){
            $thisdir = array_pop($stack);
            if($dircont = scandir($thisdir)){
                $i=0;
                while(isset($dircont[$i])){
                    if($dircont[$i] !== '.' && $dircont[$i] !== '..'){
                        $current_file = "{$thisdir}/{$dircont[$i]}";
                        if(is_file($current_file)) {
                            if(preg_match('/legoeso_pdm_data/',$current_file)){
                                $paths[] = realpath("{$thisdir}/{$dircont[$i]}");
                            }
                        } elseif (is_dir($current_file) ){
                            $stack[] = realpath($current_file);
                        }
                    }
                    $i++;
                }
            }
        }

        // choose which files to filter and return
        if($type == 'jpg'){
            return array_filter( $paths, function($filename, $ext = '/[.]jpg/'){
                return preg_match($ext, $filename);
            });
        }
        elseif($type == 'pdf'){
            return array_filter( $paths, function($filename, $ext = '/[.]pdf/'){
                return preg_match($ext, $filename);
            });
        }
        elseif($type == 'zip') {
            return array_filter( $paths, function($filename, $ext = '/[.]zip/'){
                return preg_match($ext, $filename);
            });
        }
        else {
            return $paths;
        }
    }

    /**
     * Returns an array of file paths from the database
     * 
     * @since 1.2.2
     * 
     * @param bool 
     * @return array
     */
    private function get_valid_filepaths(){
        // setup wpdb
        global $wpdb;
        if($wpdb){

            $sql_query = "SELECT id, pdf_path, image_path from {$wpdb->prefix}legoeso_file_storage";
            $result = $wpdb->get_results($sql_query, ARRAY_A );

            $paths = [];
            foreach($result as $row){
                $pdf_file = $row['pdf_path'];
                $image_file = $row['image_path'];

                ( !empty($pdf_file)) ? $paths[] = $pdf_file: '';
                ( !empty($image_file)) ?  $paths[] = $image_file: '';
            }
            asort($paths);
            return $paths;
        }
    }
    
    /**
     * returns the number of days since the file was created/last modified
     * 
     * @since 1.2.2
     * 
     * @param string $file - file path
     * @return int timestamp
     */
    public function get_file_age($file){
        if(isset($file) && file_exists($file)){
            // time of last modification (Unix timestamp)
            return abs( round( (time()-(filemtime($file))) / 86400) );
        }
    }
    
    /**
    * Checks the remaining execution time left for the current running process
    * @since 1.0.0
    * 
    * @return none
    */
    public function execTimeRemaining(){
       return  ini_get('max_execution_time') === "0" ? null :
            ((int)ini_get('max_execution_time')) - (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
    }

    /**
    * convert MB to bytes, preforms simple conversion
    *
    * @since 1.2.1
    *
    * @num int expects number represented as i.e. 256M (megabytes)
    *
    * @return int 
    */
    function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = substr($val, 0, strlen($val)-1);
        switch($last) {
            // The 'G' modifier is available
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
    
        return $val;
    }

    /**
     * Converts bytes to human readable form
     * 
     * @since 1.2.2
     * @param int in bytes
     * @return string files in human readable form
     */

    public function make_file_size_readable($bytes) {
        if(!empty($bytes) && $bytes > 0){
            $i = floor(log($bytes, 1024));
            return round($bytes / pow(1024, $i), [0,0,2,2,3][$i]).['B','kB','MB','GB','TB'][$i];
        }
    }
    /**
     * Parses the filesize remove unit and non numeric characters
     * 
     * @since 1.0.1
     * @param string $size
     * @return int 
     */
    public function parse_size($size) {
        // Remove the non-unit characters from the size.
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); 

        // Remove the non-numeric characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); 
        if ($unit) {
            // Find the position of the unit in the ordered string which is the 
            // power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }

    /**
    * convert MB to bytes, preforms simple conversion
    *
    * @since 1.0.2
    *
    * @num int expects number represented as i.e. 256M (megabytes)
    *
    * @return int 
    */
    public function php_to_bytes($num){
        $const_b = 1048576; // 1MB = 1048576 bytes
        $n = explode('M', $num); 
        // expects number represented as i.e. 256M (megabytes)
        $num_conv = ($n[0] * $const_b);
        return ($num_conv);
    }

    /**
     * Intercepts the output to the phpinfo() call and returns its values in an associative array
     * curtouse
     * 
     * @since 1.0.1
     * @return array
     */
    public function phpinfo_array()
    {
        ob_start();
        phpinfo();
        $info_arr = array();
        $info_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
        $cat = "";
        foreach($info_lines as $line)
        {
            // new cat?
            preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat = $title[1] : null;
            if(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val))
            {
                $info_arr[$cat][trim($val[1])] =  trim($val[2]);
            }
            elseif(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val))
            {
                $info_arr[$cat][trim($val[1])] = array("local" => trim($val[2]), "master" => trim($val[3]));
            }
        }
        return $info_arr;
    }

    /**
     * Saves the changes made with the settings page
     * 
     * @since 1.0.1
     * 
     * @param array $_POST
     * @return none
     */
	// public function updateSettings($POST){
	// 	if(!wp_verify_nonce( $POST['_pdm_doc_settings_nonce'], 'pdm-doc-settings-nonce' ) && !is_array($POST)){
	// 		$this->pdf_DebugLog("Update Settings Security Check::", "failed!");
	// 		return;
	// 	}
  
	// 	global $wpdb;
	// 	$wpdb->show_errors(true);
	// 	// table to update
	// 	$tablename = $wpdb->prefix.'options';

	// 	// columns to process
	// 	$do = array('legoeso_force_image_enabled');

	// 	foreach($POST as  $key => $val){
			
	// 		if(in_array($key, $do)){

	// 			$columns_update = array(
	// 				'option_name'		=>	$key,
	// 				'option_value'		=>	sanitize_option($val),
	// 				'autoload'			=>	'yes',
	// 			);

	// 			// what to update
	// 			$_where = array('option_name' => $key);

	// 			//	update values of the WP_Options TABLE
	// 			$update_result = $wpdb->update($tablename, $columns_update, $_where);
	// 			$this->pdf_DebugLog("Updated {$key}: Result", $update_result);
	// 		}
	// 	}
	// }

    /**
     * Saves the changes made to the pdf document using Quick Edit
     * 
     * @since 1.0.1
     * 
     * @param array $_POST
     * @return none
     */
	public function save_changes_pdf_quick_edit($doc_data){
        //  sanitize post data 
        $doc_data = $this->sanitize_postdata_strong($doc_data);

         try{
            global $wpdb;
            $wpdb->show_errors(true);
            
            // table to update
            $tablename = $wpdb->prefix.'legoeso_file_storage';
            // columns to update
            $columns_update = array(
                'filename'		=>	sanitize_title_with_dashes($doc_data['edit-document_filename']),
                'category'		=>	(isset($doc_data['edit-document_category']) && $doc_data['edit-document_category'] != '-1') ? $doc_data['edit-document_category'] : 'General',
            );

            // what to update
            $_where = array('ID' => absint($doc_data['docid']));

            //	update values of the WP_Options TABLE
            $rs = $wpdb->update($tablename, $columns_update, $_where);            
           
            if($rs == 1){
                return wp_json_encode( array('response' => $rs ) );
                
            } else {
                $this->pdf_DebugLog("Error Updating: Result", $wpdb->last_error);
                return wp_json_encode( array('response' =>  $wpdb->last_error) );
            }

           
        }catch(PDM_Exception_Error $e) {
            $this->Exception_Error[] = $e->getErrorObject($e);
        }
        
	}

    /**
    * toggle checkbox values
    *
    * @since 1.0.1
    *
    * @value string  current value of checkox
    *
    * @return array
    */
    public function toggle_checkbox($value){
        if(empty($value)){
            return array('off','');
        }
        return ($value == 'on') ? array('off','checked') : array('on',''); 
    }

    /**
     * Sanitize Input $_POST data array, aggressive approach
     * 
     * @since 1.1.0
     * @return Array
     */
    public function sanitize_postdata_strong($HTTP_RAW_POST_DATA) {
        if(is_array($HTTP_RAW_POST_DATA)){
            $v = [];
            foreach($HTTP_RAW_POST_DATA as $key => $val){
                if(!empty($val)){
                    $v[$key] = $this->sanitize_postdata($val);
                }
            }
            return $v;
        } 
        else {
            return strip_tags(
                stripslashes(
                    sanitize_text_field(
                        filter_input(INPUT_POST, $HTTP_RAW_POST_DATA)
                    )
                )
            );
        }
    }

    /**
     * Sanitize Input $_POST data single item
     * 
     * @since 1.1.0
     * @return Array
     */
    public function sanitize_postdata(string $HTTP_RAW_POST_DATA): string {
        return sanitize_text_field( $HTTP_RAW_POST_DATA );
    }

    /**
    * Checks/returns a list of required dependencies
    * @since 1.0.1
    * @plugin_dir string   directory location of the plugin
    * @return array
    */
    public function check_dependencies(){

        // initialze output buffer variables
        $arrOutput = null;
        $retval = null;

        //  initialize installed lib array
        $installed_libs = [];

        //  Add the settings from php enviroment variables
        $phpinfo = $this->phpinfo_array();

		$installed_libs['server'] = $phpinfo['']['System'];
		$installed_libs['imagick'] =$phpinfo['imagick'];
        $installed_libs['zip_info'] = $phpinfo['zip'];
        $installed_libs['php_version'] = $phpinfo['Core']['PHP Version'];

        //  add relavant php.ini values
        $installed_libs['server_limits']['max_execution_time'] = $phpinfo['Core']['max_execution_time'];
        $installed_libs['server_limits']['memory_limit'] = $phpinfo['Core']['memory_limit'];
        $installed_libs['server_limits']['memory_limit']['local_bytes'] = $this->php_to_bytes($phpinfo['Core']['memory_limit']['local']);
        $installed_libs['server_limits']['memory_limit']['master_bytes'] = $this->php_to_bytes($phpinfo['Core']['memory_limit']['master']);
        $installed_libs['server_limits']['post_max_size'] = $phpinfo['Core']['post_max_size'];

          // Add to debug log    
        $this->pdf_DebugLog("Dependency Check", wp_json_encode($installed_libs));
        return($installed_libs);
    } 
}