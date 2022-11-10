(function($) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
         * 
         * The file is enqueued from inc/frontend/class-frontend.php.
	 */

	/**
	 * Converts string to Title Case
	 * @param str text to be change to Tile Case    
	 * @returns string text as Tile Case
	 */
	function titleCase(str) {
		// check for the column that contains the image data to adjust the column name
		//str = (str == 'TO BASE64(`PDF IMAGE`)') ? 'PREVIEW' : str;
		str = (str == 'PDF IMAGE') ? 'PDF PREVIEW' : str;
		// Step 1. Lowercase the string
		str = str.toLowerCase();
		// Step 2. Split the string into an array of strings
		str = str.split(' ');
		// Step 3. Create the FOR loop
		for (var i = 0; i < str.length; i++) {
			str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1);
		}
		// Step 4. Return the output
		return str.join(' ');
	}


	/**
	 *  Formats the string headings 
	 * @param { Array} objHeadings 
	 * @returns { Object} oHeadings
	 */
	function formatHeadings(objHeadings) {
		if (!objHeadings.length)
			return;
		let oHeadings = [];

		objHeadings.forEach(element => {
			let str = {'title':titleCase((element.toLocaleUpperCase()).replaceAll('_', ' '))}
			oHeadings.push(str);
		});
		return oHeadings;
	}
	/**
	 * loads the json data file and passes it to the datatable object
	 * @param {*} objDataTable HTML Table Object
	 * @param {*} strJsonFilename  JSON File
	 */
	function loadTableData(dt_viewdata, json_data_url, _wpnonce) {

		function isJsonString(str){
			try{
				JSON.parse(str);
			} catch(e) {
				return false;
			}
			return true;
		}
		// returns a local copy of the data for DataTable
		function getDTViewData(){
			return dt_viewdata;
		}
		// returns a local copy of the _wpnonce
		function get_wpnonce(){
			return _wpnonce;
		}
		let data_url = json_data_url + dt_viewdata.data_filename;
		// create new instance of XMLHttpRequest to fetch data
		let xhr = new XMLHttpRequest();

		xhr.open('GET', data_url);
		xhr.send();
		xhr.addEventListener("load", function (e) {
			if (xhr.readyState === 4 && xhr.status === 200) {

				//  get and parse the JSON string
				let json_resp =  xhr.responseText;
				
				if(!isJsonString(json_resp))
					return;

				let oData = JSON.parse(json_resp);
				// extract table headers from first row of data
				let doHeadings 	= formatHeadings(oData.columns);
				// get info to the current used to create DataTable
				let view_data 	= getDTViewData();
				// get document url
				let _doc_url 	= function () { return getDTViewData().view_doc_url; } 

				if(view_data.view_type == "preview"){

					//  add the new data to the table
					$("#"+view_data.table_id).DataTable({
						ajax: data_url,
						//data:  oData.data,
						autowidth: true,
						columnDefs:[

							{
								targets: 0,
								visible: false,

							},
							{
								targets: 1,
								render: function(data, type, row, meta){

									if(data){
										return 	"<img height='150px' width='150px' src='"+row[1] +"' />";
									} else {
										return "* NO IMAGE *";
									}

								}			
							},
							{
								targets: 2,
								render: function(data, type, row, meta){
									
									return '<a target="_blank" href="' + _doc_url() + '/' + row[2] + '?action=view_document&pid=' + row[0] +'&_wpnonce='+ get_wpnonce() +'"> ' + data +'</a> ';
								}
							},
						],
						columns: doHeadings,
					});				
				} else {
					//  add the new data to the table
					$("#"+view_data.table_id).DataTable({
						data:  oData.data,
						autowidth: true,
						columnDefs:[
							{
								targets: 0,
								visible: false,

							},
							{
								targets: 1,
								visible: false,

							},
							{
								targets: 2,
								render: function(data, type, row, meta){
									return '<a target="_blank" href="' + _doc_url() + '/' + row[2] + '?action=view_document&pid=' + row[0] +'&_wpnonce='+ get_wpnonce() +'"> ' + data +'</a> ';
								}
							},
						],
						columns: doHeadings,
					});
				}
				return;
			}
		});
	}
	/**
	 * TODO: fix view types, display of tables and add render option to display images. 
	 * add link to download individual pdf files
	 */
	/**
	 * sets up and displays all datatables created
	 * @param {string} view_type type of datatable to render
	 * @param {object} objViewData  object containing datatable data
	 */

	// duck out if the legoesodata onject is not found
	if(typeof(legoesodata) != "object")
		return;

	// load each of the datatable views
	if(legoesodata.datatable_views.length > 0){
		// collection of views to display i.e Datatables
		let _views = legoesodata.datatable_views;
		let _json_data_url = legoesodata.json_data_url;
	
		for(const _dt_viewdata of _views){
			//show_viewType(viewData, _json_data_url);
			loadTableData(_dt_viewdata, _json_data_url, legoesodata._wpnonce);
		}
	}
	window.addEventListener('resize', function(){
		console.log('resized!');
	})

})(jQuery );
