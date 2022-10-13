(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 * 
	 * The file is enqueued from inc/admin/class-admin.php.
	 */

	var timer;
	let refreshCount = 1;

	/**
	 * Declare Constants
	 */
	// valid file types
	const allowedTypes = ['application/pdf', 'application/x-zip-compressed'];
	const _debug = true;

	function _show_status_messages(_message_txt, _msg_class = 'warning'){
		if(_debug){
			console.log(_message_txt);
		}
		$('#status_message').removeClass();
		$('#status_message').html(_message_txt).fadeIn();
		$('#status_message').html(_message_txt).fadeOut(10000);
		$('#status_message').addClass('col-auto ' + _msg_class);
	}

	/**
	 * Displays a message to the user.
	 * @param {string} strMsg
	 * @param {boolean} msgType, true if error msg, false otherwise
	 */
	function show_server_response(_message, _message_type) {
		let _rtrn_class = '';

		switch (_message_type) {
			case 'error':
				_rtrn_class = 'text-bg-danger status_messages';
				break;
			case 'warning':
				_rtrn_class = 'text-bg-warning status_messages';
				break;
			case 'success':
				_rtrn_class = 'text-bg-success status_messages';
				break;
			default:
				_rtrn_class = 'text-bg-success status_messages';
				break;
		}

		_show_status_messages(_message, _rtrn_class);
	}

	/**
	 * Add server response to the page
	 * @param {JSON object} _result_data 
	 * @param {string} _text 
	 */
	function show_server_response_long(_result_data, _text = ''){

		// display the server response box div element 
		$("#server-response-box").attr('style', 'display:block');

		// get a reference to both inside elements
 		let _server_response_head = $("#server-response-head");
		// get reference to <pre></pre> element for server messages
		let _server_response_messages = $("#server-response-messages");

		// add response from server to both
		_server_response_head.html(_text);
		_server_response_messages.text(_result_data);	

	}

	/**
	 * Adds event listener to toggle server message display
	 */
	$("#server-response-head").on('click', toggle_server_messages);
	
	/**
	 * Toggles display of messages from server
	 */
	function toggle_server_messages(){
		this.className = (this.className == 'server-response-head') ? 'server-response-head-expand' : 'server-response-head';
		let _server_message = $('#server-response-messages')[0];
		_server_message.className = (_server_message.className == 'server-message') ? 'server-message-expand' : 'server-message';

	}

	function setdebug_log(_text) {
		if (ajax_obj.wp_debug) {
			if (_text)
				console.log(_text);
        }
    }

	function update_progress_bar(percentComplete, _text) {
		let _progress_bar = $('.progress-bar');
		
			_progress_bar.css({ width: percentComplete + '%' });
			_progress_bar.attr('aria-valuenow', percentComplete);
			_progress_bar.html(_text);
		
		if (percentComplete === 100) {
			$('.progress').addClass('hide');
		}
	}
	/**#legoeso-drop-text
	 * Resets the upload form
	 */
	function reset_upload_form(){

		toggle_submit_buttons(false);
		let _upload_form = $('#pdm-upload-form')[0];

		$('.progress').removeClass('hide');
		$('.progress').addClass('display');
		$('#legoeso-drop-text').text('Drag file here to upload');

		if(_upload_form){
			// Reset select category drop down 
			$('#pdf_category')[0].selectedIndex = 0;
			_upload_form.reset();
		}

		// reset progress bar
		update_progress_bar(0,'');
		window.clearInterval(timer);
	}

	function change_drag_area_text(_text = '', t_color = 'none'){
		let drop_area = $("#legoeso-drop-text")
			drop_area.text(_text);

			if(t_color == 'green'){
				drop_area.addClass('drag-drop-text');
			} else {
				drop_area.removeClass('drag-drop-text');
			}
				
	}

	function bytesToSize(bytes) {
		const units = ["byte", "kilobyte", "megabyte", "terabyte", "petabyte"];
		const unit = Math.floor(Math.log(bytes) / Math.log(1024));
		return new Intl.NumberFormat("en", { style: "unit", unit: units[unit] }).format(bytes / 1024 ** unit);
	}

	/**
	 * Used to filter the submitted file list
	 * @param {object} file_list 
	 * @returns {object} returns false on error or an object of valid file types that will be uploaded
	 */	
	function _filter_file_types(file_list){
		
		try {
			if(typeof file_list == 'undefined' || !file_list.toString().split(' ')[1] == 'FileList]' ){
				show_server_response('Invalid files list, unable to filter files', 'error');
				return false;
			}

			const dataTransfer = new DataTransfer();
			let valid_files =  $(file_list).filter(function (idx) {
				return allowedTypes.includes(file_list[idx].type);
			});

			for(const _file of valid_files){
				dataTransfer.items.add(_file);
			}
			return dataTransfer.files;

		} catch (error) {
			console.log(error);
		}
	}
	/**
	 * Enable/disables the select files button
	 * @param {string} _on 
	 */
	function toggle_submit_buttons(_on){
		let _val = (_on) ? true : false;
		$('#pdm-upload-browse-button').prop('disabled', _val);
		$('#pdm-upload-submit-button').prop('disabled', _val);
	}
	/**
	 * validates submitted files are ready to be uploaded
	 * @param {object} _form 
	 * @param {array} _valid_file_list 
	 * @returns 
	 */
	function validate_file_upload(_form, _valid_file_list){
		if(!typeof(_form) == 'object'){
			return;
		}

		const upload_file_max = ajax_obj.php_max_files_upload;	
		const upload_filesize_max = ajax_obj.php_max_upload_size;  
		const upload_post_max_fize = ajax_obj.php_post_max_size;

		let _passed_validation = true;
		let validFiles = _valid_file_list;
		let _error_message = '';

		// calculate the total file size for all files
		let totalFilesize = function (fsize = 0){
			for(const _valid_file of validFiles){
				fsize += _valid_file.size;
			}
			return fsize;
		}

		// if no valid files exists alert the user
		if (validFiles.length < 1) {
			_error_message += ' Invalid file type(s) chosen.';
			_passed_validation = false;
		}

		//	check if the selected files exceed the max upload amount
		if (validFiles.length > upload_file_max) {
			_error_message += 'Maximum number of allowable files that can be uploaded at one time (' + parseInt(upload_file_max) +') has been exceeded., To upload ' + (parseInt(upload_file_max) + 1) + ' or more files use a Zip file.';
			_passed_validation = false;
		}
		// check to see if the user has exceeded the max filesize
		if (totalFilesize() > upload_filesize_max) {
			_error_message = 'The total file size exceeds the maximum allowed.\n' +
				'Files must be less than ' + bytesToSize(upload_filesize_max);
			_passed_validation = false;
        }
		// check to see if the user has exceeded the max filesize
		if (totalFilesize() > upload_post_max_fize) {
			_error_message = 'The total filesize exceeds the maximum that can be posted.\n' +
				'Files must be less than ' + bytesToSize(upload_post_max_fize);
			_passed_validation = false;
        }
		
		return { 'passed_validation':_passed_validation, 'error_message': _error_message} ;
	}

	//	refreshes the progress-bar and keeps open communication with the server
	function refreshProgress() {
		setdebug_log('Called: refreshProgress()');
		//	show the hidden element
		$('.progress').removeClass('hide');

		$.ajax({

			url: ajax_obj.ajax_process_uploads_url +
				'?nonce=' + ajax_obj.pdm_nonce +
				'&pdm_process_text=' +
				ajax_obj.pdm_process_text,

			dataType: 'json',
			success: function (data) {

				// count the number of time the script calls refresh
				console.log(refreshCount++);

				// build progress message 
				let strProgStatus = null;
				strProgStatus = (data.percent) ? '(' + data.percent + '%) ' : '';

				//	update the use as to the progess of the script
				if (refreshCount > 1 && data.status_message == 'Calculating...') {
					strProgStatus += 'Extracting Data from document(s)...';
				} else {

					strProgStatus += data.status_message;
                }
				
				// draw status bar
				change_drag_area_text(strProgStatus);
				update_progress_bar(data.percent, strProgStatus);
				setdebug_log(strProgStatus);
				setdebug_log(data.upload_status);
				
				// if the process is complete stop the checking process.
				if (data.percent == 100 || data.percent == null) {
					console.log('call complete: ' + data.percent);
					// clear refresh interval
					window.clearInterval(timer);
				}
			},
			error: function (e) {
				let errmsg = 'Response: ' + e.status + ' (' + e.statusText + ') ' + e.responseText;

				if (e.status == 200) {
					show_server_response(errmsg, 'success');
				}
				else {
					show_server_response(errmsg, 'error');
				}
			}
		});
	}

	/**
	 * Adds the upload and progress event listener to the XMLHttpRequest
	 * updates the status 
	 * @returns XHR object
	 */
	function callXHR() {
		let xhr = new window.XMLHttpRequest();
		xhr.responseType = 'text';
		xhr.upload.addEventListener("progress", 
			function (evt) {
				let strText = 'Uploading file(s)...';
				let percentComplete = 0;
				
				setdebug_log(strText);
				//setdebug_log(percentComplete);
				
				if (evt.lengthComputable) {
					percentComplete = Math.round( (evt.loaded / evt.total  * 100).toFixed(2) );
					update_progress_bar(percentComplete, percentComplete + '%' + ' ' +strText);
				}
				change_drag_area_text(percentComplete + '%' + strText  );
		}, 
		xhr.upload.addEventListener("load", function(evt){
			console.log('file(s) upload done.');
			// after file(s) uploaded start/set the refresh 
			// timer interval for processing data.
			timer = window.setInterval(refreshProgress, 500);
		}),
		false);

		return xhr;
	}

	/**
	 * uploads form data to ajax handler
	 * @param {object} _formdata 
	 */
	function _upload_file_data(_formdata){
		
		//	disable the submit / upload button
		toggle_submit_buttons(true);

		//	Obtain the form and append WordPress required fields/values
		let formData = ($('#pdm_file').val()) ? new FormData(_formdata) : _formdata;
		
		//	sets callback for the WP hook that will handle/process the upload form
		formData.append('action', 'file_upload_handler');

		//	sets callback for the WP hook that will handle/process the upload form
		formData.append('_pdm_upload_info', ajax_obj.pdm_upload_info);

		//	add the WP nonce to the form object
		formData.append('_ajax_pdm_doc_list_nonce', ajax_obj.pdm_nonce);

		//	add the legoeso_force_image_enabled 
		let force_image = (typeof formData.get === "function") ? formData.get('legoeso_force_image_enabled') : formData['legoeso_force_image_enabled'].value;
		
		formData.append('legoeso_force_image_enabled', force_image);

		$.ajax({
			xhr: callXHR,

			url: ajax_obj.ajax_url,
			type: 'POST',
			contentType: false,
			processData: false,
			cache: false,
			data: formData,
			dataType:'json',
			success: function (data) {	

				let  strmsg = 'Upload Completed';
				let _failed = data.failed;
				let _php_errors = data.php_exceptions;

				if (_failed != 0 || _php_errors.length != 0) { 
					strmsg += ' However, (' + _failed + ') files failed. \n'; 
					show_server_response_long(JSON.stringify(data), strmsg );
				} else {
					if(ajax_obj.wp_debug == 1){
						strmsg = ' <strong>Upload completed. See details below:</strong> \n';
						show_server_response_long(JSON.stringify(data, null, 2), strmsg );
					} 
					else {
						show_server_response('Upload completed!', 'sucess');
					}
				}

				reset_upload_form();
				legoeso_list.display();

			},
			error: function (error) {
				show_server_response_long(JSON.stringify(error, null, 2), 'Process completed with errors.' );
				// reset the form
				reset_upload_form();
				//	update list table
				legoeso_list.display();
			}
		});


	}

	/**
	 * 
	 * Drag n drop handlers
	 */

	// Drag enter 
	$('.pdm-drag-drop-area').on('dragenter', function(e){
		//e.stopPropagation();
		e.preventDefault();
		change_drag_area_text('Drop Here', 'green');
		console.log('dragenter');
	});

	// prevent page from redirecting
	$(".pdm-drag-drop-area").on("dragover", function(e) {
		e.preventDefault();
		//e.stopPropagation();
		change_drag_area_text("Drop Here", "green");
		console.log('dragover');
	});


	// Drag enter 
	$('.pdm-drag-drop-area').on('dragleave', function(e){
		e.stopPropagation();
		e.preventDefault();
		change_drag_area_text('Drag PDF Files Here');
		console.log('dragleave');
	});

	// Drop - Fires after files have been dropped
	$('.pdm-drag-drop-area').on('drop', function(e) {
		e.stopPropagation();
		e.preventDefault();
		// clear the server response box if it is visible
		$("#server-response-box").attr('style', 'display:none');

		change_drag_area_text('Uploading...');

		try{
			// get the current form and create a new from it
			let _pdm_upload_frm = $('#pdm-upload-form')[0];
			// filter file types and update form
			let _valid_file_list = _filter_file_types(e.originalEvent.dataTransfer.files);
			_pdm_upload_frm.pdm_file.files = _valid_file_list;

			// validate the files
			let _data = validate_file_upload(_pdm_upload_frm, _valid_file_list);

			// validate and process upload
			if(_data.passed_validation && _valid_file_list){
				// updload form/data 
				_upload_file_data(_pdm_upload_frm);
			} else {
				show_server_response(_data.error_message + ' - File validation failed.', 'error');
				reset_upload_form();
			}

		} catch( error){
			show_server_response(error, 'error');
		}

	});
	/**
	 * 
	 *  Begin ajax processing when the form is successfully submitted
	 */
	$('#pdm_upload_form').on('submit', (
		function (e) {
			e.preventDefault();
			// clear the server response box if it is visible
			$("#server-response-box").attr('style', 'display:none');
			try {
			
				console.log('ready!');
				// grab the form element
				let _pdm_upload_frm = this;

				//	filter the file list 
				let _valid_file_list = _filter_file_types(this.pdm_file.files);

				// update the file list in the form
				_pdm_upload_frm.pdm_file.files = _valid_file_list;

				let _data = validate_file_upload(_pdm_upload_frm, _valid_file_list);
				//validate and process upload
				if(_data.passed_validation && _valid_file_list){
					// updload form/data 
					_upload_file_data(_pdm_upload_frm);
				} else {
					show_server_response(_data.error_message + ' File validation failed.', 'error');
				}
				
			} catch (error) {
				show_server_response(error + '\nError, unable to upload files or complete process.', 'error');
				reset_upload_form();
			}
        }
	));

	/**
	 * Select Files - Fires after files have been selected
	 */
	$("#pdm-upload-browse-button").on("click", function(){
		$("#pdm_file").click();
		$("#pdm_file").on('change', function(){
		// clear the server response box if it is visible
		$("#server-response-box").attr('style', 'display:none');
		try {
		
			console.log('ready!');
			// grab the form element
			let _pdm_upload_frm = this.closest('form');

			//	filter the file list 
			let _valid_file_list = _filter_file_types(this.files);

			// update the file list in the form
			_pdm_upload_frm.pdm_file.files = _valid_file_list;

			// validate the files
			let _data = validate_file_upload(_pdm_upload_frm, _valid_file_list);
			//validate and process upload
			if(_data.passed_validation && _valid_file_list){
				// updload form/data 
				_upload_file_data(_pdm_upload_frm);
			} else {
				show_server_response(_data.error_message + ' - File validation failed.', 'error');
			}

		} catch (error) {
			show_server_response( error + ' Error, unable to upload files or complete process.', 'error');
			reset_upload_form();
		}
		
			
		});
	});	



})(jQuery);

