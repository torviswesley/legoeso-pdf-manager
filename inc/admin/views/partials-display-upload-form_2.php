    <br><br>
    <form enctype="multipart/form-data" method="post" id="pdm_upload_form">

    
        <?php wp_nonce_field( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce' ); ?>

        <div id="pdm-pload-upload-ui" class="hide-if-no-js drag-drop">
            <div class="pdm-drag-drop-area" id="pdm-drag-drop-area" style="position: relative;">
                <div class="drag-drop-category"><strong>Generate PDF Preview</strong>
                    <input type="checkbox" name="legoeso_force_image_enabled" id="force_image_enabled" <?php echo esc_attr($force_image_enabled);?>/> 
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
                <textarea class="server-message" id="server-response-messages" readonly></textarea>  
            </div>
		
            <div id="progressbar">
                <div class="progress-label">
                </div>
            </div>

            <div class="status_messages" id="status_message"></div>
            <p class="upload-flash-bypass">
            <?php 
                $up_link = add_query_arg( array('page' => $this->plugin_text_domain, 'pdm_upload_view' => '1', ), admin_url('admin.php') );
            ?>

                You are using the multi-file drag-drop uploader. Problems? Try the <a href="<?php  echo esc_url( $up_link ); ?>">browser uploader</a> instead.	
            </p>
        </div>
    </form>