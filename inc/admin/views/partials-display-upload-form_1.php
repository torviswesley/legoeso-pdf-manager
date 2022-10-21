    <br><br>
    <form enctype="multipart/form-data" method="post" id="pdm_upload_form">

    
        <?php wp_nonce_field( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce' ); ?>

        <div id="pdm-pload-upload-ui" class="hide-if-no-js drag-drop">
            <div class="pdm-drag-drop-area" id="pdm-drag-drop-area" style="position: relative;">
                <div class="drag-drop-category">
                    <label for="attachment-filter" class="screen-reader-text">Select Category</label>
                    <?php
                        $args = array(
                            'hide_empty'		=>	0,
                            'show_option_none'	=>	'Select Category',
                            'orderby'			=>	'name',
                            'order'				=>	'ASC',		
                            'value_field'		=>	'name',
                            'name'				=>	'pdf_category',
                            'id'				=>	'pdf_category'
                        );
                        //  Using WordPress function to create dropdown menu of categories
                        wp_dropdown_categories($args);
                    ?>
                    <input type="checkbox" name="legoeso_force_image_enabled" id="force_image_enabled" <?php echo $force_image_enabled;?>/> 
                    <strong>Force PDF Preview Only</strong>  
                </div>
                <div class="drag-drop-inside"  >
                    <p class="drag-drop-info" id="legoeso-drop-text">Drop files to upload</p>
                    <p>or</p>
                    <p class="drag-drop-buttons">
                        <input style="display:none;" type="file" id="pdm_file" name="pdm_file[]" multiple/>
                        <input id="pdm-upload-browse-button" type="button" value="Select Files" class="button" style="position: relative; z-index: 1;"/>
                    </p>
                </div>
            </div>

            <div id="server-response-box" class="server-response-box">
                <div id=server-response-head class="server-response-head"></div>
                <textarea class="server-message" id="server-response-messages"></textarea>  
            </div>
		
            <div class="progress display">
                <div id="progress_bar" 
                    class="progress-bar bg-success" 
                    role="progressbar" aria-valuenow="0" aria-valuemin="0" 
                    aria-valuemax="100" style="width: 0%">
                </div>
            </div>
            
            <div class="status_messages" id="status_message"></div>
            <p class="upload-flash-bypass">
                You are using the multi-file drag-drop uploader. Problems? Try the <a href="<?php echo $_SERVER['PHP_SELF']."?page={$this->plugin_text_domain}&pdm_upload_view=1"; ?>">browser uploader</a> instead.	
            </p>
        </div>
    </form>