=== Legoeso PDF Manager ===
Contributors: torviswesley
Donate link: https://www.legoeso.com/
Tags: PDF, PDFs, PDF Manager, PDF Documents, PDF Viewer, Organize, File Manager, OCR, PDF to Text
Requires at least: 5.6
Tested up to: 6.1
Stable tag: 1.2.1
Requires PHP: 7.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The Legoeso PDF Manager is a simple PDF document manager that allows for easy display, and distribution of PDF documents within your WordPress site. 

== Description ==

The Legoeso PDF Manager is a lightweight, simple but powerful PDF document manager. It helps you organize your PDF documents for easy display, and distribution within your WordPress site. It supports multiple file upload or bulk upload of files stored in a zip file.

Easily include all or a list of PDF documents into your posts by using one of the shortcodes. Secure access to your documents, only logged in users are able to view the documents you provide to your visitors.

== Installation Manually ==
1. Download the latest Legoeso PDF Manager archive from WordPress and extract to a folder or alternatively search the WordPress plugin repository for Legoeso PDF Manager
2. Upload the plugin to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Plugin Features  ==
* Bulk import of PDFs documents - easily drag and drop to upload a single document, a multiple documents or a zip file containing a multiple documents.  
* Single or bulk delete of PDFs.
* Single or bulk download of pdf documents.
* Sort by category, document name, upload by, or date uploaded.
* Perform key word search on PDF documents.
* Easily list or display some or all documents by document id or category within a WordPress page.
* Quickly rename a document or its category.
* Secure access to all PDF documents, users must be logged in to access your documents.
* This plugin has much more to offer and can be used to parse the extracted text to harvest and store useful data from the documents you submit. 

== Usage ==

== To add new PDF Documents, select Legoeso PDFs ==
1. Locate Legoeso PDFs within the WordPress menu. 
2. Select a category, Drag and Drop your PDF documents to the upload area. You can choose to a single .pdf document, multiple documents or a zip file containing multiple PDF documents. 

== Add Categories. Documents can be grouped by categories. ==
1. To add categories locate 'Post' within the WordPress Menu, select categories.  
2. Add a new categories then return to Legoeso PDFs, the category will now be listed within the drop-down menu. 

== Displaying PDF Documents ==
1. To list you document within your WordPress site. Create or edit a page, insert one the shortcodes listed on the Legoeso setting page
2. Save your page. A list of documents or link shall appear on the page.

== Limitations ==
1. The PHP PDF Parser is not suitable for parsing very large files (See: https://github.com/smalot/pdfparser/issues/104 ) and may generate a memory allocation errors when attempting to parse some file. In cases of large files, no text parsing will be attempted, only an image preview will be extracted. 
2. Encrypted PDF's files are not supported 

== Other Usages: ==
1. E-mail PDF - @ request
2. Restrict documents by user groups - @ request
3. Extract and collect data from pdf documents that can be stored - @ request

== Frequently Asked Questions ==

= How do I get started? =
* Upload and activate the Legoeso PDF Manager to your WordPress site.
* Locate Legoeso PDF’s within the admin menu.
* select or drag your documents to the drop area.  

= How do I include my PDF documents in my post = 
* Simple. Just include the shortcode [legoeso_display_documents] within your page. You can display your PDF documents on any page by using any of the shortcodes listed in the Legoeso Settings admin menu. There are three (3) "types" of views that can be used.

= Is there a file size limit? =
The plugin has no file size limitation, however, your web hosting provider may have limitations, such as the maximum number of files that can be uploaded at one time, the maximum file upload size and the maximum memory that can be allocated to a pHp script. In cases of large documents, only a preview image will be generated, no text will not be extracted from the document, therefore the document will not be included when performing keyword searches. 
In addition a document's text will not be extracted from a document that has too many pages.

= Why wasn't there a text preview after I uploaded my document?
If no text could be detected from a document only a preview image will be shown.  This is true also for large documents with many pages, only a snap shot of the document will be generated.

= Where does Legoeso PDF Manager store its PDF documents? =
The Legoeso PDF Manager stores your PDF documents within the WordPress upload directory

== Screenshots ==

1. Example of table list view of PDF documents with image preview of document.(png|jpg|jpeg|gif). Screenshots are stored in the /assets directory.
2. Another example of table list view of PDF documents with image preview of document.
3. Example of table list view of PDF documents.
4. Preview of admin area.

== Changelog ==

= 1.2.2 =
Implemented new DataTable widget child rows to show document metadata
Removed support Bootstrap v5.2.0-beta1 
Add support for jQuery Ui widgets
Implemented jQuery Ui progressbar
Improved performance working with large files
Added WP Cron scheduled clean-up to remove unmapped and unused files weekly, zip files created by the plugin will expire and deleted after 7 days.

== Upgrade Notice ==

= 1.2.1 =
Initial release no upgrades at this time.  