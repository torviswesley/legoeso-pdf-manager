/**
 * Converts string to Title Case
 * @param str text to be change to Tile Case    
 * @returns string text as Tile Case
 */
function titleCase(str) {
    // check for the column that contains the image data to adjust the column name
    str = (str == 'TO BASE64(`PDF IMAGE`)') ? 'PREVIEW' : str;
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
        str = titleCase((element.toLocaleUpperCase()).replaceAll('_', ' '));
        oHeadings.push(str);
    });
    return oHeadings;
}
/**
 * loads the json data file and passes it to the datatable object
 * @param {*} objDataTable HTML Table Object
 * @param {*} strJsonFilename  JSON File
 */
function loadTableData(objDataTable, str_json_data) {
    if (!str_json_data)
        return;
    function isJsonString(str){
        try{
            JSON.parse(str);
        } catch(e) {
            return false;
        }
        return true;
    }
    let xhr = new XMLHttpRequest();
    xhr.addEventListener("load", function (e) {
        if (xhr.readyState === 4 && xhr.status === 200) {

            //  get and parse the JSON string
            let json_resp =  xhr.responseText;
            
            if(!isJsonString(json_resp))
                return;

            let oData = JSON.parse(json_resp);
            let doHeadings = formatHeadings(Object.keys(oData.data[0]));
            let obj = {
                //   Quickly get the headings
                headings: doHeadings,
                // Data array
                data: []
            };

            // grab the JSON data obj that was parsed
            let objData = oData.data;
            // Loop over the objects to get the values
            for (let i = 0; i < objData.length; i++) {
                obj.data[i] = [];
                for (let p in objData[i]) {
                    if (objData[i].hasOwnProperty(p)) {
                        obj.data[i].push(objData[i][p]);
                    }
                }
            }
            //  add the new data to the table
            objDataTable.insert(obj);
            return;
        }
    });

    xhr.open('GET', str_json_data);
    xhr.send();
}

/**
 * sets up and displays all datatables created
 * @param {string} view_type type of datatable to render
 * @param {object} objViewData  object containing datatable data
 */
function show_viewType(view_type, objViewData){
    let objDataTable_View = document.getElementById(objViewData.table_id);
    let json_data_url = pdm_dataobj.pdm_json_datafiles_url;
    // get the url to the view pdf file url
    let view_pdf_url = pdm_dataobj.pdm_view_pdf_url;

    // build and display datatables
    if(objDataTable_View){
        if(view_type == 'preview'){
            pdm_dataTable = new simpleDatatables.DataTable(objDataTable_View, {
                searchable: true,
                fixedHeight: true,
                columns: [
                    {
                        select: 0,
                        sortable: false,
                        render: function (data, cell, row){

                            let image_data = data;
                            if(image_data){
                                return 	"<img height='150px' width='150px' src='data:jpeg;base64," + btoa(atob(image_data)) + "' />";
                            }
                            else {
                                return " :: NO IMAGE DATA ::";
                            }
                        }
                    },
                    {
                        select:1, hidden: true
                    },
                    {
                        select: 2,
                        render: function (data, cell, row) {
                            return "<a href='" + view_pdf_url + "?file_id=" + this.data[row.dataIndex].cells[1].data + "' target='_blank' >" + data + "</a>";
                        }
                    }
                ]
            });
            //   load the data into the datatable
            loadTableData(pdm_dataTable, json_data_url + objViewData.data_filename);
        }
        else {
            pdm_dataTable = new simpleDatatables.DataTable(objDataTable_View, {
                searchable: true,
                fixedHeight: true,
                columns: [
                    {
                        select:0, hidden: true
                    },
                    {
                        select: 1,
                        render: function (data, cell, row) {
                            return "<a href='" + view_pdf_url + "?file_id=" + this.data[row.dataIndex].cells[0].data + "' target='_blank' >" + data + "</a>";
                        }
                    }
                ]
            });
            //   load the data into the datatable
            loadTableData(pdm_dataTable, json_data_url + objViewData.data_filename);
        }
    }
} 


// adds event listener that 
window.addEventListener('DOMContentLoaded', event => {

    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

    // load each of the datatable views
    if(pdm_dataobj && pdm_dataobj.datatable_views.length > 0){
        let _views = pdm_dataobj.datatable_views;
        for(const viewData of _views){
            show_viewType(viewData.view_type, viewData);
        }
    }
    
});
