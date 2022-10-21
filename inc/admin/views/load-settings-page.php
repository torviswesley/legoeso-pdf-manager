<div class="accordion" id="accordionEnvVariables">

  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" 
      data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        Operating System: <?php echo esc_html($sys_info['server']);?>
      </button>
    </h2>

    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#PDMAccordion">
      <div class="accordion-body">
        <strong>Current Operating System:</strong>  <?php echo esc_html($sys_info['server']);?><br>
        <strong>Zip:  <?php echo esc_html($sys_info['Zip Version']); ?></strong>
        <strong>Imagick:</strong>  <?php echo esc_html($sys_info['imagick']['imagick module version']);?><br>
        <strong>Imagick compiled Version: </strong> <?php echo esc_html($sys_info['imagick']['Imagick using ImageMagick library version']);?><br>

      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="headingFour">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" 
      aria-controls="collapseFour">
        Short Codes : Using short codes
      </button>
    </h2>

    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#PDMAccordion">
           
    <div class="accordion-body">
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
            <li> Document View - Lists a single document or multiple documents within a page. 
                <ul style="list-style-type:square;">
                   <li> <strong>[legoeso_document_item pdf_id="954647"]</strong> - Single document</li> 
                  <li> <strong>[legoeso_document_item pdf_id="6183770, 221932, 744517, 683331"]</strong> - Multiple documents, notice the document ids are separated by a comma.</li> 
                </ul>
            </li>
            
        </ul>

        </div>

    </div>

    </div>
  </div>


  <div class="accordion-item">
    <h2 class="accordion-header" id="headingFive">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" 
      aria-controls="collapseFive">
        Help : Help with plugin and dependencies
      </button>
    </h2>

    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#PDMAccordion">
           
    <div class="accordion-body">
    <strong>The following must be installed on your server for this plugin to work correctly.</strong>        
      <p> 
      If no text is being extracted from the PDF documents verify all dependencies are installed and the path locations are correct.  
      For PDFs that are not searchable by default PyTessarct will need to be installed and configured on your server, 
      otherwise this plugin will only take a snapshot of the document.
      </p>
      <p>
        
        This plugin uses Simple-DataTables to display the list of PDF documents within WordPress - for more information see 
        <a href="https://github.com/fiduswriter/Simple-DataTables/wiki"> Simple-DataTables</a>
        <br>
        <strong>Your php Server Settings </strong> - <a href="<?php echo plugin_dir_url( __FILE__ );?>partials-view-phpinfo.php" target="_blank">PHP version and settings</a>
        <br>
        <strong>E-mail support request or suggestions to:</strong> <a href="mailto:support@legoeso.com">support@legoeso.com</a>
      </p>   
        </div>
      

    </div>

    </div>
  </div>

</div>