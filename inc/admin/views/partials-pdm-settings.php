<div class="container pt-4"><p class="h2"> Legoeso PDF Manager | Enviroment | Help</p> 
	
	<?php 
			wp_nonce_field( 'pdm-doc-settings-nonce', '_pdm_doc_settings_nonce' ); 
			$sys_info = $this->check_dependencies();
	?>
	<div class="accordion" id="accordion">
		<!-- Item #1 -->
		<h3>Operating System: <?php echo esc_html($sys_info['server']);?></h3>

		<div class="accordion-item accordion-body">
			<strong>Current Operating System:</strong>  <?php echo esc_html($sys_info['server']);?><br>
			<strong>Zip:  </strong><?php echo esc_html($sys_info['phpinfo']['Zip version']); ?>
			<strong>Imagick:</strong>  <?php echo esc_html($sys_info['imagick']['imagick module version']);?><br>
			<strong>Imagick compiled Version: </strong> <?php echo esc_html($sys_info['imagick']['Imagick using ImageMagick library version']);?>
		</div>

		<!-- Item #2 -->				
		<h3>Short Codes: How to use shortcodes and display your documents within your pages.</h3>

		<div class="accordion-item accordion-body">
			<strong>Legoeso PDF Shortcodes</strong><br>
			Displaying your documents to your viewers is simple. You can display your PDF documents on any page by using any 
					of the shortcodes listed below.  There are three (3) "types" of views that can be used.
					<ul  style="list-style-type:square;">
						<li>tableview - This the default type and generates an easy to read table using the columns: Filename, Category, Upload Userid, and Date Uploaded. </li>
						<li>preview_table - This type also generates an easy to read table but includes a preview image of the document. 
							(Please note, when displaying large number of rows while using this view type there maybe a slight delay loading the table. 
							If this is an issue, you can limit the number of rows returned by specifying a category)</li>
						<li>ulistview - This view generates an unordered list of the documents by specifying the "pdf_id" for each document you would like to list.</li>
					</ul>
			Example usage of each available shortcode can be seen below.
			<div class="mb-3">
				<ul style="list-style-type:round;">
					<li> Tableview [tableview]
						<ul style="list-style-type:square;">
							<li><strong>[legoeso_display_documents]</strong> - This is the default shortcode and will generate a table with all documents that were uploaded.
							</li> 
							<li><strong>[legoeso_display_documents category="Saved Documents"]</strong> -  This shortcode will generate a table with all documents assigned to the category "Saved Documents".</li> 
						</ul>
					</li>
					<li> Preview_table [preview_table]
						<ul style="list-style-type:square;">
							<li> <strong>[legoeso_display_documents type="preview_table"]</strong> - This shortcode will generate a table with all documents but includes an image preview of the document if one is available.</li> 
							<li> <strong>[legoeso_display_documents type="preview_table" category="Saved Documents"]</strong> - This shortcode will generate a table with all documents assigned to the "Saved Documents" category and will included an image preview of the document if one is available.</li> 
						</ul>
					</li>
					<li> Ulistview [ulistview] - Document ID's can be obtained within the Legoeso PDF's admin menu. Locate the "PDF ID" column to locate the documnet id you wish to use.  
						<ul style="list-style-type:square;">
							<li> <strong>[legoeso_display_documents type="ulistview" pdf_id="954647"]</strong> - This shortcode will list a single document as an unordered list within your page.</li> 
							<li> <strong>[legoeso_display_documents type="ulistview" pdf_id="6183770, 221932, 744517, 683331"]</strong> - This shortcode will list multiple documents as an unordered list within your page. (Notice the document ids are separated by commas.)</li> 
						</ul>
					</li>
				</ul>
				
			</div>

			<div class="mb-3">
			<strong>Adding categories</strong><br>
			Legoeso PDF Manager uses WordPress's categories. To add a new category click the link label "Categories" with the Legoeso PDF's admin menu.
			</div>

		</div>
	</div>

	<div>
		<p>
			This plugin uses DataTables to display a list of PDF documents on your Wordpress pages - for more information see 
			<a href="https://datatables.net/"> DataTables</a>
			<br>
			<strong>Your php Server Settings </strong> - <a href="<?php echo esc_attr(plugin_dir_url( __FILE__ )); ?>partials-view-phpinfo.php" target="_blank">PHP version and settings</a>
			<br>
			<strong>E-mail support request or suggestions to:</strong> <a href="mailto:support@legoeso.com">support@legoeso.com</a>
		</p>   
	</div>

</div>

