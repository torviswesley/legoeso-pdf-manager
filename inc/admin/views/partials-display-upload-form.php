

<div class="container pt-3 wp-filter">

	<div>
		<h2>Select a single PDF file, a Zip file or multiple files.</h2>

		<div class="container pt-3">
			<form id="pdm_upload_form" method="post" class="row row-cols-lg-auto" enctype="multipart/form-data">
					<?php wp_nonce_field( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce' ); ?>
			
					<div class="col-auto">
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
					<div class="col-auto">
							<input type="file" class="form-control" id="pdm_file" name="pdm_file[]" aria-describedby="pdm_file" aria-label="Upload" multiple/>
					</div>

					<div class="col-auto">
						<button class="btn btn-success btn-sm" type="submit" id="pdm-upload-submit-button">Upload</button>

						<input type="checkbox" class="form-control" 
						name="legoeso_force_image_enabled" id="force_image_enabled" <?php echo esc_attr($force_image_enabled);?> > 
						<strong>Force PDF Preview Only</strong> 
					</div>

			</form>		

            <div id="server-response-box" class="server-response-box">
                <div id=server-response-head class="server-response-head">

                </div> 
				<textarea class="server-message" id="server-response-messages" readonly></textarea>  
            </div>

            <div id="progressbar">
                <div class="progress-label">
                </div>
            </div>
			
			<div class="status_messages" id="status_message"></div>
		</div>

	</div>
	<br>
	<p class="upload-flash-bypass">Use the <a href="<?php echo esc_url( add_query_arg('page', $this->plugin_text_domain, admin_url('admin.php')) ); ?>"> drag-drop uploader</a> instead?</p>
</div>
	
                
            