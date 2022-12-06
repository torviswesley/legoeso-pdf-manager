<div class="container pt-4"><p class="h2"> <strong> Legoeso PDF Manager | Enviroment | Help</strong></p> 
	
	<?php 
			wp_nonce_field( 'pdm-doc-settings-nonce', '_pdm_doc_settings_nonce' ); 
			$sys_info = $this->check_dependencies();
	?>
	<div class="accordion" id="accordion">
		<!-- Item #1 -->
		<h3><strong>Operating System:</strong> <?php echo esc_html($sys_info['server']);?></h3>

		<div class="accordion-item accordion-body">
			<strong>Current Operating System:</strong>  <?php echo esc_html($sys_info['server']);?><br>
			<strong>PHP Version:  </strong><?php echo esc_html($sys_info['php_version']); ?><br>
			<strong>Zip:  </strong><?php echo esc_html($sys_info['zip_info']['Zip version']); ?><br>
			<strong>Imagick:</strong>  <?php echo esc_html($sys_info['imagick']['imagick module version']);?><br>
			<strong>Imagick compiled Version: </strong> <?php echo esc_html($sys_info['imagick']['Imagick using ImageMagick library version']);?>
		</div>

		<!-- Item #2 -->				
		<h3> <strong>Short Codes:</strong>  How to use shortcodes to display documents within your pages.</h3>

		<div class="accordion-item accordion-body">
			<strong>Legoeso PDF Shortcodes</strong><br><br>
			Displaying documents to your viewers is simple. You can display your PDF documents on any page by using any 
					of the shortcodes listed below.  There are three (3) "types" of views that can be used.
					<ul  style="list-style-type:square;">
						<li> <strong> tableview</strong> - This is the default type and generates an easy to read table using the columns: Filename, Category, Upload Userid, and Date Uploaded. </li>
						<li> <strong> document_preview</strong> - This type also generates an easy to read table but includes a preview image of the document. 
							(Please note, when displaying a large number of rows while using this view type there maybe a slight delay loading the table. 
							If this is an issue, you can limit the number of rows returned by specifying a category)</li>
						<li> <strong> listview </strong>- This view generates an ordered or unordered list of the documents by specifying the "pdf_id" for each document you would like to list.</li>
					</ul>
			<strong> <span style="color:red;"> Example usage of each available [shortcode] can be seen below.</span></strong>
			<div class="mb-3">
				<ul style="list-style-type:round;">
					<li> <u> Tableview [tableview]</u>
						<ul style="list-style-type:square;">
							<li><strong>[legoeso_display_documents]</strong> - This is the default shortcode and will generate a table with all documents that were uploaded.
							</li> 
							<li><strong>[legoeso_display_documents category="Saved Documents"]</strong> -  This shortcode will generate a table with all documents assigned to the category "Saved Documents".</li> 
						</ul>
					</li>
					<li> <u>Preview_table [document_preview]</u>
						<ul style="list-style-type:square;">
							<li> <strong>[legoeso_display_documents type="document_preview"]</strong> - This shortcode will generate a table with all documents but includes an image preview of the document if one is available.</li> 
							<li> <strong>[legoeso_display_documents type="document_preview" category="Saved Documents"]</strong> - This shortcode will generate a table with all documents assigned to the "Saved Documents" category and will included an image preview of the document if one is available.</li> 
						</ul>
					</li>
					<li> <u>Listview [listview]</u> - This shortcode accepts a comma delimited list of document ID's.  Document IDs can be obtained within the Legoeso PDF's admin menu. Locate the "PDF ID" column to locate the documnets id you wish to display.  
						<ul style="list-style-type:square;">
							<li> <strong>[legoeso_display_documents type="listview" pdf_id="954647"]</strong> - This shortcode will list a single document as an unordered list within your page.</li> 
							<li> <strong>[legoeso_display_documents type="listview" pdf_id="6183770, 221932, 744517, 683331"]</strong> - This shortcode will list multiple documents as an "<strong> <u> unordered</u></strong>" list within your page. (Notice the pfd_ids are separated by commas.)</li>
							<li> <strong>[legoeso_display_documents type="listview" pdf_id="183770, 744517, 683331" ordered]</strong> - This shortcode will list multiple documents as an "<strong><u>ordered</u></strong>" list within your page. (Notice the pdf_ids are separated by commas.)</li>
						</ul>
					</li>
				</ul>
				
			</div>

			<div class="mb-3">
			<strong>Adding categories</strong><br>
			Legoeso PDF Manager uses WordPress's categories. To add a new category click the link label "Categories" with the Legoeso PDF's admin menu.
			</div>

		</div>
		<!-- Item #3 -->
				<!-- Item #1 -->
				<h3><strong>Help: Troubling Shooting : Dealing with errors.</strong></h3>

				<div class="accordion-item accordion-body">
					<strong>Troubling with common errors:</strong>  <br>
					<ul style="list-style: square;">
						<li> The Legoeso PDF Manager uses <u> smalot/PHP PDF Parser</u> and is not suitable for parsing very large files 
							See: <a href="https://github.com/smalot/pdfparser/issues/104" target="_blank">smalot/pdfparser </a> and may generate out of memory allocation
							 errors, or HTTP: 500 Internal Server Errors when attempting to parse some files.
						</li>
						<li>If one of the errors above occurs, try checking the option "Force PDF Preview Only" when uploading files. No text parsing will be attempted, 
							 and an image preview will be extracted instead. </li>
						<li>
							Encrypted PDF's files are not supported.	
						</li>
					</ul>
				</div>

	</div>

	<div>
		<p>
			This plugin uses:<a href="https://github.com/smalot/pdfparser" target="_blank"> PDF Parser</a> and <a href="https://datatables.net/" target="_blank"> DataTables</a>.

			<br>
			<strong>Your PHP Server Settings </strong> - <a href="<?php echo esc_attr(plugin_dir_url( __FILE__ )); ?>partials-view-phpinfo.php" target="_blank">PHP version and settings</a>
			<br>
			<strong>E-mail support requests, suggestions, or customizations to:</strong> <a href="mailto:support@legoeso.com">support@legoeso.com</a>
		</p>   
	</div>

</div>

