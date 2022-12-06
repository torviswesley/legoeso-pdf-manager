## Legoeso PDF Document Manager
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description
The Legoeso PDF Manager is a lightweight, simple but powerful PDF document manager. It helps you organize your PDF documents for easy display, and distribution within your WordPress site. It supports multiple file upload or bulk upload of files stored in a zip file.

Features include unlimited file uploads. Upload documents via drag and drop, bulk upload, bulk download, bulk delete, automatically generates document previews for list display, and visitors can securely view PDF documents within WordPress. In addition, you can modify document names and categories on the fly, and perform keyword document searches.
		  

## Installation Manually
1. Download the latest Legoeso PDF Manager archive from WordPress and extract to a folder or alternatively search the WordPress plugin repository for Legoeso PDF Manager
2. Upload the plugin to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress


## This plugin requires the following dependencies.
1. Imagick
2. ZipArchive

## Features include:
1. Bulk import of PDFs documents - easily drag and drop to upload a single document, a multiple documents or a zip file containing a multiple documents.  
2. Single or bulk delete of PDFs.
3. Single or bulk download of pdf documents.
4. Sort by category, document name, upload by, or date uploaded.
5. Perform key word search on PDF documents.
6. Easily list or display some or all documents by document id or category within a WordPress page.
7. Quickly rename the document or its category.
8. Secure access to all PDF documents, users must be logged in to access your documents.

## Usage
## To add new PDF Documents, select Legoeso PDFs 
1. Locate Legoeso PDFs within the WordPress menu. 
2. Select a category, Drag and Drop your PDF documents to the upload area. You can choose to a single .pdf document, multiple documents or a zip file containing multiple PDF documents. 

## Add Categories. Documents can be grouped by categories. 
1. To add categories locate 'Post' within the WordPress Menu, select categories.  
2. Add a new categories then return to Legoeso PDFs, the category will now be listed within the drop-down menu. 

## Displaying PDF Documents
1. To list you document within your WordPress site. Create or edit a page, insert one the shortcodes listed on the Legoeso setting page
2. Save your page. A list of documents or link shall appear on the page.

## Limitations
1. The PHP PDF Parser is not suitable for parsing very large files (See: https://github.com/smalot/pdfparser/issues/104 ) and may generate 'Fatal errors:  Allowed memory allocation error' when attempting to parse some files. In cases of large files, no text parsing will be attempted, only an image preview will be extracted. 
2. Encrypted PDF's files are not supported. 

## Other Usages:
1. E-mail PDF - @ request
2. Restrict documents by user groups - @ request
3. Extract and collect data from pdf documents that can be stored - @ request

## Screenshots
![Tableview with preview image](https://legoeso.com/wp-content/uploads/2022/12/Screenshot-2022-12-06-061842.png?raw=true)
![Tableview with metadata](https://legoeso.com/wp-content/uploads/2022/12/Screenshot-2022-12-06-062058.png?raw=true)
![Admin | drag drop uploader](https://legoeso.com/wp-content/uploads/2022/12/Screenshot-2022-12-06-063800.png?raw=true)
![Admin | alternate uploader](https://legoeso.com/wp-content/uploads/2022/12/Screenshot-2022-12-06-061842.png?raw=true)
![Admin | filter list](https://legoeso.com/wp-content/uploads/2022/12/Screenshot-2022-12-06-063945.png?raw=true)
![Admin | setting view](https://legoeso.com/wp-content/uploads/2022/12/Screenshot-2022-12-06-064014.png?raw=true)
![Unorered list view](https://legoeso.com/wp-content/uploads/2022/12/Screenshot-2022-12-06-064051.png?raw=true)

## Changes v1.2.2 
1. Implemented new DataTable widget child rows to allow access to document metadata.
2. Removed Bootstrap v5.2.0-beta1 support
3. Added support for jQuery Ui widgets
4. Implemented jQuery Ui progressbar
5. Improved performance working with large files.
6. Added WP Cron scheduled clean-up to remove unmapped and unused files weekly, and zip files created by the plugin will expire and be deleted after 7 days.

## Changes v1.2.1 
1. Resolved WordPress security vulnerabilities
2. Refactored PDF extraction code for better performance

## Changes v1.1.0 
1. Added support for standalone PDF Parser see: https://github.com/smalot/pdfparser
2. Add Imagick support to convert PDF to image
3. Removed all python scripts
4. Removed and/or Updated required dependencies

## For Help, comments or request additional features.
Email support@legoeso.com