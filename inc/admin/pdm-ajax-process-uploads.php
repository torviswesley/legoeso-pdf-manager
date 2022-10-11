<?php
/**
 * The progress checker file for the PHP/ Ajax Progress Bar
 *
 * This script is called several times by the Ajax script, retreiving a text file in the background, 
 * returning the status and percent compeleted of the file upload process until the all files
 * are uploaded. The text file is in JSON format which is then read by the calling JavaScript.
 * The process is complete when the status reaches 100.  The file will be
 * deleted when completed. 
 * 
 *
 * @link       http://www.legoeso.com
 * @since      1.0.0
 * 
 * @author     Torvis Wesley
 */
header('Content-Type: application/json');
	$http_referer = explode('/', $_SERVER['HTTP_REFERER']);
	//	Kill script if accessed directly or if not refered by wp-admin
	if( !isset($_SERVER['HTTP_REFERER'])  || !in_array('wp-admin', $http_referer) ){
		die();
	}

	// build the text file used to obtain the processing status
	$pdm_nonce = $_REQUEST['nonce']; //	get the wp_nonce

	//	decode the server path for text file that contains upload status info
	//	and set the filename variable
	$status_filename = base64_decode($_REQUEST['pdm_process_text']);

	 // check and get the contents from the status file
	if( file_exists($status_filename) ) {
		//	get the contents of the file and echo it.
		$text = file_get_contents($status_filename);
		// send the json object back the client-side JavaScript
		echo $text;
		//	convert to JSON to read the status of the process
		$obj = json_decode($text);
		//	if the process is finished, delete the file
		if($obj->percent == 100){
			unlink($status_filename);
		}
	} 
	else {
		// returning empty array
		die( json_encode(array( 
					"percent" 			=> 0, 
					"status_message" 	=> 'Calculating...',
					'upload_status'     => 0,
					'wpdb_errorText'    =>  '',
				) 
			)
		);
		
		
	}

?>