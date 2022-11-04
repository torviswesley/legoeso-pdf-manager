<div class="container pt-4"><p class="h1"> Legoeso PDF | Enviroment Variables</p> 
	
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
		<h3>Short Codes : Using Short Codes</h3>

		<div class="accordion-item accordion-body">
			<strong>PDF Document ListView Shortcodes</strong> - You can display your PDF documents on any page by using any 
					of the two type of shortcodes.  Listview or Listview Preview <br>See examples below.
			<div class="mb-3">
				<ul style="list-style-type:round;">
					<li> Listview - Lists  the documents in an easy to read list. 
						<ul style="list-style-type:square;">
							<li><strong>[legoeso_document_listview]</strong>  - Lists all available documents. </li> 
							<li><strong>[legoeso_document_listview category="Saved Documents"]</strong> - Lists all documents using the specified category.</li> 
						</ul>
					</li>
					<li> Listview Preview - Lists all documents but includes an image preview of the document if one is available.
						<ul style="list-style-type:square;">
							<li> <strong>[legoeso_document_preview category="Saved Documents"]</strong> </li> 
						</ul>
					</li>
					<li> Document Listview - Lists a single document or multiple documents as an unordered list within a page within a page.
						<ul style="list-style-type:square;">
							<li> <strong>[legoeso_document_item pdf_id="954647"]</strong> - Single document</li> 
							<li> <strong>[legoeso_document_item pdf_id="6183770, 221932, 744517, 683331"]</strong> - Multiple documents, notice the document ids are separated by a comma.</li> 
						</ul>
					</li>
				</ul>
			</div>
				
		</div>
	</div>

	<div>
		<p>
			This plugin uses Simple-DataTables to display the list of PDF documents within WordPress - for more information see 
			<a href="https://github.com/fiduswriter/Simple-DataTables/wiki"> Simple-DataTables</a>
			<br>
			<strong>Your php Server Settings </strong> - <a href="<?php echo esc_attr(plugin_dir_url( __FILE__ )); ?>partials-view-phpinfo.php" target="_blank">PHP version and settings</a>
			<br>
			<strong>E-mail support request or suggestions to:</strong> <a href="mailto:support@legoeso.com">support@legoeso.com</a>
		</p>   
	</div>

</div>

