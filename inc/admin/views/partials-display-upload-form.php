

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
						name="legoeso_force_image_enabled" id="force_image_enabled" <?php echo $force_image_enabled;?> > 
						<strong>Force PDF Preview Only</strong> 
					</div>

			</form>		

            <div id="server-response-box" class="server-response-box">
                <div id=server-response-head class="server-response-head">

                </div> 
                <pre class="server-message" id="server-response-messages">  
                </pre>
            </div>

			<div class="progress display">
				<div id="progress_bar" 
					class="progress-bar bg-success" 
					role="progressbar" aria-valuenow="0" aria-valuemin="0" 
					aria-valuemax="100" style="width: 0%">
				</div>
			</div>
			<div class="status_messages" id="status_message"></div>
		</div>

	</div>
	<br>
	<p class="upload-flash-bypass">Use the <a href="<?php echo $_SERVER['PHP_SELF']."?page={$this->plugin_text_domain}"; ?>"> drag-drop uploader</a> instead?</p>
</div>
	
                
            