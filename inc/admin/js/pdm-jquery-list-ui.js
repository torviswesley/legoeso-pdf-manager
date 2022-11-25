
(function ($) {
	'use strict';

	/**
     * Legoeso PDF Document Manager	
	 * The file is enqueued from inc/admin/class-admin.php.
     * 
	 * Manages Ajax request for displaying custom WP_Admin_Table.
	 *
	 * @namespace legoeso_list
	 *
	 * @since 1.0.2
	 *
	 * @type {Object}
	 *
	 * @property {string} type The type of inline editor.
	 * @property {string} what The prefix before the post ID.
	 *
	 */

	/**
	 * Ajax List Table object loader.  Async load data and redraws the table list
	 */
     var isAjaxing = false;

     var legoeso_list = {
 
         init: function () {
 
             var timer;
             var delay = 500;
 
             /**
              * 
              * Inline Quick Edit fucntions and properties needed for the inline editing pdf documents, derived from, WP wp-admin/js/inline-edit-post.js
              * @since 1.0.3
              * 
              */
             var t = this, qeRow = $('#inline-edit');
 
             t.type = $('table.widefat').hasClass('pdf_docs') ? 'pdf_docs' : 'pdf_doc';
             // Document ID prefix.
             t.what = '#pdf_doc-';
 
             /**
              * Binds the Escape key to revert the changes and close the quick editor.
              *
              * @return {boolean} The result of revert.
              */
             qeRow.on( 'keyup', function(e){
                 // Revert changes if Escape key is pressed.
                 if ( e.which === 27 ) {
                     return legoeso_list.revert();
                 }
             });
 
             /**
              * Reverts changes and close the quick editor if the cancel button is clicked.
              *
              * @return {boolean} The result of revert.
              */
             $( '.cancel', qeRow ).on( 'click', function() {
                 return legoeso_list.revert();
             });
 
             /**
              * Saves changes in the quick editor if the save(named: update) button is clicked.
              *
              * @return {boolean} The result of save.
              */
             $( '.save', qeRow ).on( 'click', function() {
                 return legoeso_list.save(this);
             });
 
             /**
              * If Enter is pressed, and the target is not the cancel button, save the post.
              *
              * @return {boolean} The result of save.
              */
             $('td', qeRow).on( 'keydown', function(e){
                 if ( e.which === 13 && ! $( e.target ).hasClass( 'cancel' ) ) {
                     return legoeso_list.save(this);
                 }
             });
 
             /**
              * Adds onclick events to the apply buttons.
              */
             $('#doaction').on( 'click', function(e){
                 var n;
 
                 t.whichBulkButtonId = $( this ).attr( 'id' );
                 n = t.whichBulkButtonId.substr( 2 );
 
                 if ( 'edit' === $( 'select[name="' + n + '"]' ).val() ) {
                     e.preventDefault();
                     t.setBulk();
                 } else if ( $('form#posts-filter tr.inline-editor').length > 0 ) {
                     t.revert();
                 }
             });
 
             /** marries the top and bottom selectors */
             let topSelector = $('#bulk-action-selector-top');
             let bottomSelector = $('#bulk-action-selector-bottom');
 
             // event handlers for row actions
             function updateTopSelector(){
                 topSelector.val($(this).val());
             }
             function updateBottomSelector(){
                 bottomSelector.val($(this).val());
             }
             
             /**
             * Functions to handle Admin Table row action click actions 
             */
             function handle_clickActions(e) {
                 e.preventDefault();
 
                 let obj_target = (e.target.name).split("-");
                 let action_target = obj_target[1];
                 let bulk_action_target = (topSelector.val() || bottomSelector.val()) ? topSelector.val() : -1;
 
                 let bulk_action_types = ['bulk-download','bulk-delete','bulk-email'];
                 // checks for the bulk action option value
                 let do_bulk = (bulk_action_types.includes(topSelector.val()) || bulk_action_types.includes(bottomSelector.val())) ? true : false;
 
                 if (do_bulk) {
                     let checked = [];
                     //	build an array of the documents that were checked
                     $('input[name="pdfdocs[]"]').each(function (index, obj) {
                         if (obj.checked) {
                             checked.push(obj.value);
                         }
                     });
                     if(checked.length <= 1){
                         alert("You must select 2 or more documents to complete this action!");
                         return;
                     } 
 
                     let data = {
                         bulk_action: topSelector.val(),
                         bulk_action2: bottomSelector.val(),
                         checkedVals: checked
                     };
 
                     switch(bulk_action_target){
                         case "bulk-download":
                             legoeso_list.do_bulk_actions(data);
                         break;
                         case "bulk-delete":
                             let confirmDelete = confirm('Are you sure you would like to delete (' + checked.length + ') items?');
                             if(confirmDelete){
                                 legoeso_list.do_bulk_actions(data);
                             }
                         break;	
                         case "bulk-email":
                             alert("Not Implemented! Email: support@legoeso.com");
                         break;
                         default:
                         break;
                     }
 
                 } else {
                     switch(action_target){
                         case "delete":
                             let checked = [];
                             // get the row id to delete
                             checked.push(obj_target[2]);
                             let data = { 
                             bulk_action : 'bulk-delete',
                             checkedVals : checked };
                             // delete single row 
                             let confirmDelete = confirm('Are you sure you would like to delete the document with ID: (' + obj_target[3] + ')?');
                             if(confirmDelete){
                                 legoeso_list.do_bulk_actions(data);
                             }
                             break;
 
                         case "email":
                            alert("Not Implemented! Email: support@legoeso.com");
                             break;	
 
                         default:
                             break;
                     }
                 }
                 return;
             }
 
             topSelector.on('change', updateBottomSelector);
             bottomSelector.on('change', updateTopSelector);
             
             $('#pdm-list-form').on('submit', handle_clickActions);
             /**
              * Manages sortable columns 
              */
             $('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function (e) {
                 e.preventDefault();
                 var query = this.search.substring(1);

                 var data = {
                     paged: legoeso_list.__query(query, 'paged') || '1',
                     order: legoeso_list.__query(query, 'order') || 'asc',
                     orderby: legoeso_list.__query(query, 'orderby') || 'filename',
                     s: $('#pdm-doc-find-search-input').val(),
                 };
                 legoeso_list.update(data);
             });
 
             // fires when sortable column is clicked
             $('input[name=paged]').on('keyup', function (e) {
 
                 if (13 == e.which)
                     e.preventDefault();
                 let s = $('#pdm-doc-find-search-input').val();
                 var data = {
                     paged: parseInt($('input[name=paged]').val()) || '1',
                     order: $('input[name=order]').val() || 'asc',
                     orderby: $('input[name=orderby]').val() || 'filename',
                     s: $('#pdm-doc-find-search-input').val(),
                 };
 
                    window.clearTimeout(timer);
                    timer = window.setTimeout(function () {
                    legoeso_list.update(data);
                 }, delay);
             });			
 
             $('#pdm-doc-find-search-input').on('keyup', function (e) {
                 e.preventDefault();
                 var data = { s: $('#pdm-doc-find-search-input').val() };
                 
                 window.clearTimeout(timer);
                 timer = window.setTimeout(function () {
                     legoeso_list.update(data);
                 }, delay);
 
             });
 
             // fires when searching for a document
             $('#search-submit').on('click', function (e) {
                 e.preventDefault();
                 var data = { s: $('#pdm-doc-find-search-input').val() };
                 legoeso_list.update(data);
                 
             });
 
             /**
              * Binds click event to the row actions .pdm-delete, pdm-email, pdm-quick_edit.
              */
             $("a[name*='pdm-delete']").each(function(){
                 this.addEventListener("click", handle_clickActions);
             });
             //	Email links
             $("a[name*='pdm-email']").each(function(){
 
                 this.addEventListener("click", handle_clickActions);
             });
 
             /**
              * Binds click event to the .editinline button which opens the quick editor.
              */
             $( '#the-list' ).on( 'click', '.editinline', function() {
                 $( this ).attr( 'aria-expanded', 'true' );
                 legoeso_list.edit( this );
             });
         },
 
         display: function () {
 
             $.ajax({
 
                 url: ajax_obj.ajax_url,
                 dataType: 'json',
                 data: {
                     _ajax_pdm_doc_list_nonce: $('#_ajax_pdm_doc_list_nonce').val(),
                     action: '_ajax_pdm_display_callback'
                 },
                 success: function (response) {
 
                     $("#pdm-doc-list-table").html(response.display);
 
                     $("tbody").on("click", ".toggle-row", function (e) {
                         e.preventDefault();
                         $(this).closest("tr").toggleClass("is-expanded")
                     });
 
                     legoeso_list.init();
                 },
                 error: function (e) {
                     console.log(e.status);
                     console.log(e.responseText);
                 }
             });
 
         },
 
         /** AJAX call to handle bulk actions
         *
         * Send the call and replace table parts with updated version!
         *
         * @param    object    data The data to pass through AJAX
         */
         do_bulk_actions: function (data) {
             if (isAjaxing) return;
             isAjaxing = true;
             
             $.ajax({
                 xhr: function(){
                     var xhr = new window.XMLHttpRequest();
                     var percentComplete = 0;
                     xhr.addEventListener("progress", function (evt) {
                         if (evt.lengthComputable) {
                                percentComplete = Math.round( (evt.loaded / evt.total  * 100).toFixed(2) );

                             console.log('Zipping Files...');	
                         }
                     }, false);
                     return xhr;
                 },
                 type: 'POST',
                 url: ajax_obj.ajax_url,
                 data: $.extend(
                     {
                         _ajax_pdm_doc_list_nonce: $('#_ajax_pdm_doc_list_nonce').val(),
                         action: '_ajax_fetch_pdm_history_callback',
                     },
                     data
                 ),
                 success: function (response) {
                     isAjaxing = false;
                     var response = $.parseJSON(response);
 
                     let bulk_type = (response != null) ? (typeof(response.type) != 'undefine') ? response.type:'' : '';
                     switch(bulk_type){
                         case 'bulk_download':
                             window.open(response.zip_url,'_self');
                             legoeso_list.display();
                         break;
                         case 'bulk_delete':
                             legoeso_list.display();
                         break;
                         default:
                             legoeso_list.display();
                         break;
                     }
                     
 
                 //	console.log(response);
 
                     //	reset the checkboxes
                     $('input[name="pdfdocs[]"]').each(function (index, obj) {
                         if (obj.checked) {
                             obj.checked = false;
                         }
                     });
                     // reset bulk options
                     $('#bulk-action-selector-top').prop('selectedIndex', 0);
                     $('#bulk-action-selector-bottom').prop('selectedIndex', 0);
 
                 },
                 error: function(e){
                    console.log('error:')
                 }
             });
 
         },
         /** AJAX call
          *
          * Send the call and replace table parts with updated version!
          *
          * @param    object    data The data to pass through AJAX
          */
         update: function (data) {
             if (isAjaxing) return;
             isAjaxing = true;
             $.ajax({
                 
                 url: ajax_obj.ajax_url,
                 data: $.extend(
                     {
                         _ajax_pdm_doc_list_nonce: $('#_ajax_pdm_doc_list_nonce').val(),
                         action: '_ajax_fetch_pdm_history_callback',
                     },
                     data
                 ),
                 success: function (response) {
                     isAjaxing = false;
                     var response = $.parseJSON(response);
 
                     if (response.rows.length)
                         $('#the-list').html(response.rows);
                     if (response.column_headers.length)
                         $('.wp-list-table thead tr, .wp-list-table tfoot tr').html(response.column_headers);
                     if (response.pagination.bottom.length)
                         $('.tablenav.top .tablenav-pages').html($(response.pagination.top).html());
                     if (response.pagination.top.length)
                         $('.tablenav.bottom .tablenav-pages').html($(response.pagination.bottom).html());
                     
                     legoeso_list.init();
 
                 }
             });
         },
 
         /**
          * Filter the URL Query to extract variables
          *
          * @see http://css-tricks.com/snippets/javascript/get-url-variables/
          *
          * @param    string    query The URL query part containing the variables
          * @param    string    variable Name of the variable we want to get
          *
          * @return   string|boolean The variable value if available, false else.
          */
         __query: function (query, variable) {
 
             var vars = query.split("&");
             for (var i = 0; i < vars.length; i++) {
                 var pair = vars[i].split("=");
                 if (pair[0] == variable)
                     return pair[1];
             }
             return false;
         },
         
         /**
          * Quick Edit Functions
          * Toggles the quick edit window, hiding it when it's active and showing it when
          * inactive.
          *
          * @since 1.0.3
          *
          * @memberof legoeso_list
          *
          * @param {Object} el Element within a post table row.
          */
         toggle : function(el){
             var t = this;
             $( t.what + t.getId( el ) ).css( 'display' ) === 'none' ? t.revert() : t.edit( el );
         },
         /**
          * Gets the ID for a the pdf document that you want to quick edit from the row in the quick
          * edit table.
          *
          * @since 1.0.3
          *
          * @memberof legoeso_list
          *
          * @param {Object} o DOM row object to get the ID.
          * @return {string} The post ID extracted from the table row in the object.
          */
         getId : function(o) {
             var id = $(o).closest('tr').attr('id'),
                 parts = id.split('-');
             return parts[parts.length - 1];
         },
         /**
          * Gets the ID, filename, category from the selected row in the quick
          * edit table.
          *
          * @since 1.0.3
          *
          * @memberof legoeso_list
          *
          * @param {Object} o DOM row object to get the ID, filename, category.
          * @return {Array} The post ID, filename, category extracted from the table row in the object.
          */
         getRowData : function(o){
                 var rowInfo = $(o).closest('tr').attr('doc_info'),
                 parts = rowInfo.split('|');
             return parts;
         },
         /**
          * Creates a quick edit window for the post that has been clicked.
          *
          * @since 1.0.3
          *
          * @memberof legoeso_list
          *
          * @param {number|Object} id The ID of the clicked pdf document or an element within a documents
          *                           table row.
          * @return {boolean} Always returns false at the end of execution.
          */
         edit : function(id) {
             
             var t = this, fields, editRow, rowData, status, pageOpt, pageLevel, nextPage, pageLoop = true, nextLevel, f, val, pw;
             let qeRowData = t.getRowData(id);
             t.revert();
     
             if ( typeof(id) === 'object' ) {
                 id = t.getId(id);
             }
     
             fields = ['ID', 'edit-document_filename', 'edit-document_category'];
             if ( t.type === 'pages' ) {
                 fields.push('post_parent');
             }
     
             // Add the new edit row with an extra blank row underneath to maintain zebra striping.
             editRow = $('#inline-edit').clone(true);
             $( 'td', editRow ).attr( 'colspan', $( 'th:visible, td:visible', '.widefat:first thead' ).length );
     
             // Remove the ID from the copied row and let the `for` attribute reference the hidden ID.
             $( 'td', editRow ).find('#quick-edit-legend').removeAttr('id');
             $( 'td', editRow ).find('p[id^="quick-edit-"]').removeAttr('id');
     
             $(t.what+id).removeClass('is-expanded').hide().after(editRow).after('<tr class="hidden"></tr>');
     
             for ( f = 0; f < fields.length; f++ ) {
                 val = qeRowData[f]
                 $(':input[name="' + fields[f] + '"]', editRow).val( val );
             }
 
             $(editRow).attr('id', 'edit-'+id).addClass('inline-editor').show();
             $('.ptitle', editRow).trigger( 'focus' );
     
             return false;
         },
 
         /**
              * Saves the changes made in the quick edit window to the post.
              * Ajax saving is only for Quick Edit and not for bulk edit.
              *
              * @since 2.7.0
              *
              * @param {number} id The ID for the post that has been changed.
              * @return {boolean} False, so the form does not submit when pressing
              *                   Enter on a focused field.
              */
         save : function(id) {
             var params, fields, page = $('.post_status_page').val() || '';
             let data = { s: $('#pdm-doc-find-search-input').val(), };
             if ( typeof(id) === 'object' ) {
                 id = this.getId(id);
             }
 
             $( 'table.widefat .spinner' ).addClass( 'is-active' );
             //let _paged = $( '#current-page-selector' ).val();
             params = {
                 action: 'pdm_inline_quick_edit',
                 _ajax_pdm_doc_list_nonce: ajax_obj.pdm_nonce,
                 docid: id,
             };
 
             fields = $('#edit-'+id).find(':input').serialize();
             params = fields + '&' + $.param(params);
             
             // Make Ajax request.
             $.post( ajax_obj.ajax_url, params,
                 function(r) {
                     var $errorNotice = $( '#edit-' + id + ' .inline-edit-save .notice-error' ),
                         $error = $errorNotice.find( '.error' );
                     r = JSON.parse(r); // parse and update var
                     $( 'table.widefat .spinner' ).removeClass( 'is-active' );
 
                     if (r.response) {
                        
                         if ( 1 == r.response ) {
                             legoeso_list.display();
                             legoeso_list.update(data);

                         } else {
                             r = r.replace( /<.[^<>]*?>/g, '' );
                             $errorNotice.removeClass( 'hidden' );
                             $error.html( r );
                             legoeso_list.update(data);
                         }
                     } else {
                         $errorNotice.removeClass( 'hidden' );
                         $error.text( wp.i18n.__( 'Error while saving the changes.' ) );
                     }
                 },
             'html');
             
             legoeso_list.update(data);
             // Prevent submitting the form when pressing Enter on a focused field.
             return false;
         },
 
 
         /**
          * Hides and empties the Quick Edit windows.
          *
          * @since 1.0.3
          *
          * @memberof legoeso_list
          *
          * @return {boolean} Always returns false.
          */
         revert : function(){
             var $tableWideFat = $( '.widefat' ),
                 id = $( '.inline-editor', $tableWideFat ).attr( 'id' );
 
             if ( id ) {
                 $( '.spinner', $tableWideFat ).removeClass( 'is-active' );
 
                 if ( 'bulk-edit' === id ) {
 
                     // Hide the bulk editor.
                     $( '#bulk-edit', $tableWideFat ).removeClass( 'inline-editor' ).hide().siblings( '.hidden' ).remove();
                     $('#bulk-titles').empty();
 
                     // Store the empty bulk editor in a hidden element.
                     $('#inlineedit').append( $('#bulk-edit') );
 
                     // Move focus back to the Bulk Action button that was activated.
                     $( '#' + legoeso_list.whichBulkButtonId ).trigger( 'focus' );
                 } else {
 
                     // Remove both the inline-editor and its hidden tr siblings.
                     $('#'+id).siblings('tr.hidden').addBack().remove();
                     id = id.substr( id.lastIndexOf('-') + 1 );
 
                     // Show the post row and move focus back to the Quick Edit button.
                     $( this.what + id ).show().find( '.editinline' )
                         .attr( 'aria-expanded', 'false' )
                         .trigger( 'focus' );
                 }
             }
 
             return false;
         },
 
 
     }
	/**
	 * check to see if list_args is a valid variable then proceed to check for
	 * the WP_List_Table screen option id
	 */
     if (typeof (list_args) == 'undefined')
        return;

    if (list_args.screen.id != 'toplevel_page_legoeso-pdf-manager')
        return;
    
    legoeso_list.display();
    // create global legoeso_list object 
    window.legoeso_list = legoeso_list;
})(jQuery);