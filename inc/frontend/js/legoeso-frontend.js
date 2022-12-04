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
	


	function drawMetaTb(d){
		// create a custom table to include the metadata from the documents
		if(typeof d == 'string'){
			let tableHTML;
			let data = parseMetaData(d);
			tableHTML = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
				// generate column headers
				tableHTML += '<tr>';
					for( const rdata in data) {
						tableHTML += '<td><strong>' + rdata + '</strong></td>';
					}
				tableHTML += '</tr>';
				// gernerate row data
				tableHTML += '<tr>';
				for( const rdata in data) {
					tableHTML += '<td>' + data[rdata]  + '</td>';
				}
				tableHTML += '</tr>';
			tableHTML += '</table>';
			return tableHTML;
		}
	}

	/**
	 * returns document metadata
	 * 
	 * @param {String} metadata 
	 * @returns {Object} returns object containing documents metadata or false
	 */
	function parseMetaData(metadata){
		if(typeof(metadata) == "string"){
			try{
				return JSON.parse(metadata);
			} catch (e){
				return {'Title':'Unknown','CreationDate':'Unknown','Pages':'Unknown'};
			}
		}
	}

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
	function formatColumnNames(objHeadings) {
		if (!objHeadings.length)
			return;
		let oHeadings = [];

		objHeadings.forEach(element => {
			let str;

			if(element.toLowerCase() == 'id'){
				str = {
					orderable:false,
					data:null,
					defaultContent: '',
					className: 'dt-control',
					}
			} else {
				str = {
					title:titleCase((element.toLocaleUpperCase()).replaceAll('_', ' ')),
					//className: 'dt-control',
				}
			}

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
		let ajax_url = legoesodata.ajax_url +'?action='+ legoesodata.action +
			'&category='+ encodeURIComponent(dt_viewdata.category) + 
			'&nonce=' + get_wpnonce();

		// create new instance of XMLHttpRequest to fetch data
		// let xhr = new XMLHttpRequest();

		// xhr.open('GET', ajax_url);
		// xhr.send();
		// xhr.addEventListener("load", function (e) {
		// 	if (xhr.readyState === 4 && xhr.status === 200) {

		// 		//  get and parse the JSON string
		// 		let json_resp =  xhr.responseText;
				
		// 		// if(!isJsonString(json_resp))
		// 		// 	return;

		// 		let oData = JSON.parse(json_resp);
		// 		// extract table headers from first row of data
		// 		let getColumnNames 	= formatColumnNames(oData.columns);
		// 		// get info to the current used to create DataTable
		// 		let view_data 	= getDTViewData();
		// 		let viewType = view_data.view_type;
		// 		// get document url
				let _doc_url 	= dt_viewdata.view_doc_url;
				let viewType = dt_viewdata.view_type;
					// set table id 
					let tableid = "#"+dt_viewdata.table_id;
					//  add the new data to the table
					var legoeso_dtable = $(tableid).DataTable({
						className: 'ui-toolbar ui-widger-header ui-helper-clearfix ui-corner-tl ui-corner-tr',
						ajax: ajax_url,
						//data:  oData.data,
						autowidth: true,

						columns: [
							{title:'ID'},
							{title:'Image'},
							{title:'Filename'},
							{title:'Category'},
							{title:'Upload User'},
							{title:'Upload Date'},
							{title:'text_data'},
							{title:'metadata'},
						],
						columnDefs:[

							{
								targets: 0,
								visible: true,
								className: 'dt-control',
								defaultContent:'',
								orderable: false,
								data:null,
							},
							{
								targets: 1,
								render: function(data, type, row, meta){
									if(viewType == "document_preview"){
										if(data){
											return 	"<img height='150px' width='150px' src='"+ row[1] +"' />";
										} else { return "* NO IMAGE *";	}
									}
									else { return data;	}
								},
								visible : (viewType == 'document_preview') ? true : false ,			
							},
							{
								targets: 2,
								render: function(data, type, row, meta){
									let o = Object.fromEntries(Object.entries(row).filter(e => e[0] != '6'));
									let pid = btoa( JSON.stringify(o) );
									//let pid = btoa(JSON.stringify(row.slice(0, row.length-2)));
									return '<a target="_blank" href="' + _doc_url + '/' + row[2] + '?action=view_document&pid=' + pid +'&nonce='+ get_wpnonce() +'"> ' + row[2] +'</a> ';
								}
							},
							{
								targets: 6,
								visible: false,

							},
							{
								targets: 7,
								visible: false,

							},
						],
					});	

					// add event listeners for opening and closing details
					$(tableid + ' tbody').on('click', 'td.dt-control', function(){
						let tr = $(this).closest('tr');
						let row = legoeso_dtable.row(tr);

						if(row.child.isShown()){
							// this row is already open - close it
							row.child.hide();
							tr.removeClass('shown');
						}
						else {
							// open this row
							row.child(drawMetaTb( (row.data()[7]) )).show();
							tr.addClass('shown');
						}
					});
				return;
			// }
		// });
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
		let views = legoesodata.datatable_views;
		let _json_data_url = legoesodata.json_data_url;
	
		for(const table_view of views){
			//show_viewType(viewData, _json_data_url);
			loadTableData(table_view, _json_data_url, legoesodata.nonce);
		}
	}

})(jQuery );
