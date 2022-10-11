
<div class="accordion" id="accordionEnvVariables">

  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" 
      data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        Operating System: <?php echo $sys_platform;?>
      </button>
    </h2>

    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#PDMAccordion">
      <div class="accordion-body">
       <strong>Current Operating System:  <?php echo $sys_platform; ?></strong><br>
             <strong>Zip:  <?php echo "{$zip_enabled} Version: {$zip_version}"; ?></strong>
      </div>
    </div>
  </div>


  <div class="accordion-item">
    <h2 class="accordion-header" id="headingTwo">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
        Plugin Dependencies Variables
      </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#PDMAccordion">

      <div class="accordion-body">
        <strong><?php echo "Python: {$python_version}"; ?></strong>
        
        <div class="mb-3">
          <label for="python_executable" class="form-label">Path to Python Executable (i.e python.exe) </label>
            <input type="text" class="form-control" id="python_executable" 
          placeholder="e.g. /usr/bin/python3" value="<?php echo get_option("legoeso_python_dir");?>" name="legoeso_python_dir">
        </div>
         <strong>PDFMiner.six Version : <?php echo $pdfMinder_version; ?></strong>
        <div class="mb-3">
          <label for="pdfminer_path" class="form-label"> Path to PdfMinder Script : (i.e. /home/bin/pdf2txt.py) {<?php echo $pdf2txt_detected; ?>}</label>
          <input type="text" class="form-control" name="legoeso_pdfminer_dir" id="pdfminer_path" 
          placeholder="e.g. /usr/bin/pdfminer/pdf2txt.py" value="<?php echo get_option("legoeso_pdfminer_dir");?>">
        </div>
                
        <strong><?php echo "PDF2Image Version: {$pdfimage_version}"; ?></strong>
          
        <div class="mb-3">
          <label for="force_image_enabled" class="form-label">   
          <strong>Extract Image Only</strong> </label>
            <input type="checkbox" class="form-control" name="legoeso_force_image_enabled" id="force_image_enabled" 
           <?php echo $force_image_enabled;?>> Check if you prefer to only extract the image preview of the document.  If checked, text extraction will be disabled.
        </div>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="headingThree">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" 
      aria-controls="collapseThree">
        PyTesseract Text Extraction Library
      </button>
    </h2>

    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#PDMAccordion">
           
    <div class="accordion-body">
    <strong><?php echo "Tesseract Version: {$tesseract}"; ?></strong>
        <div class="mb-3">
          <label for="pytesseract_enabled" class="form-label">Enable PyTesseract </label>
            <input type="checkbox" class="form-control" name="legoeso_pytesseract_enabled" id="pytesseract_enabled" 
           value="<?php echo $enable_PyTesseract_value;?>" <?php echo $enable_PyTesseract;?>>
        </div>
        <div class="mb-3">
          <label for="pytesseract_path" class="form-label">Path to PyTesseract </label>
            <input type="text" class="form-control" name="legoeso_pytesseract_path" id="pytesseract_path" 
          placeholder="e.g. /usr/bin/pytesseract.exe" value="<?php echo get_option("legoeso_pytesseract_path"); ?>">
        </div>
      <strong>Tesseract Installation Instructions:</strong><br>
        <p><strong>Note:</strong> The following informatation is intended for experienced webmasters and developers. There maybe additional 
          dependencies required in order to run Tesseract depending on your operating system.
          For installation instructions or more information on pyTesseract see the links below.
          <br>
         
          <ul style="list-style-type:square;">

                                <li><strong>Introduction </strong> - <a href="https://github.com/tesseract-ocr/tessdoc/blob/main/Installation.md" target="_blank"> 
                                https://github.com/tesseract-ocr/tessdoc/blob/main/Installation.md</a> </li> 
            </li>
                      <li><strong>Tesseract at UB Mannheim</strong> - <a href="https://github.com/UB-Mannheim/tesseract/wiki" target="_blank"> 
                      https://github.com/UB-Mannheim/tesseract/wiki</a> </li> 
            </li>
            <li><strong>Installing Tesseract for OCR</strong> - <a href="https://pyimagesearch.com/2017/07/03/installing-tesseract-for-ocr/" target="_blank"> 
                    https://pyimagesearch.com/2017/07/03/installing-tesseract-for-ocr/</a> </li> 
            </li>
            <li><strong>PIP Installing Tesseract</strong> - <a href="https://pypi.org/project/pytesseract/" target="_blank"> 
              https://pypi.org/project/pytesseract/</a> </li> 
            </li>
        </ul>
      </p>
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
       For help installing and locating the server path/location of your Python execution directory or PDFMiner script, contact your webhosting provider.
         
        </p>    
        <div class="mb-3">
        <p>
         <strong> Dependencies</strong>
        <ol>
            <li> PDFMiner Visit: 
                <ul style="list-style-type:square;">
                   <li> <a href="https://pypi.org/project/pdfminer.six" target="_blank">https://pypi.org/project/pdfminer.six</a> </li> 
                   <li><a href="https://pdfminersix.readthedocs.io/en/latest/tutorial/install.html" target="_blank">
                     https://pdfminersix.readthedocs.io/en/latest/tutorial/install.html </a>
                   </li>
                </ul>
            </li>
            <li> Zip Archive: See server/host administrator for installation instructions.</li>
            <li> Pdf2Image Visit:
                            <ul style="list-style-type:square;">
                   <li> <a href="https://pypi.org/project/pdf2image" target="_blank">https://pypi.org/project/pdf2image</a> </li> 
                </ul>
            </li>
        </ol>
        </p>
     
        
        <p >
        
        This plugin uses Simple-DataTables to display the list of PDF documents within WordPress - for more information see 
        <a href="https://github.com/fiduswriter/Simple-DataTables/wiki"> Simple-DataTables</a>
        <br>
        <strong>Your php Server Settings </strong> - <a href="<?php echo plugin_dir_url( __FILE__ );?>partials-view-phpinfo.php" target="_blank">PHP version and settings</a>
        <br>
        <strong>E-mail support inquiries to:</strong> <a href="mailto:support@legoeso.com">support@legoeso.com</a>
      </p>   
        </div>
      

    </div>

    </div>
  </div>

</div>