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

Features include unlimited file uploads. Upload documents via drag and drop, bulk upload, bulk download, bulk delete, automatically generates snapshot images for document previews, 
and securely view PDF documents within WordPress. In addition, perform keyword search for documents, and if needed you can modify document names and categories. Track uploads by date, upload user, or category.


This plugin uses [DataTables](https://datatables.net/) to display and list your PDF documents within WordPress. Easily include a list of PDF documents into your posts by using one of the shortcodes. Secure access to your documents, only logged in users are able to view the documents you provide to your visitors.

This plugin has much more to offer and can be used to parse the extracted text to collect and store useful data from the documents you submit. 

== Frequently Asked Questions ==

= How do I get started? =
Upload and activate the Legoeso PDF Manager to your WordPress site.
Locate Legoeso PDF’s within the admin menu.
select or drag your documents to the drop area.  

= How do I include my PDF documents in my post = 
Simple. Just include the shortcode [legoeso_display_documents] within your page. You can display your PDF documents on any page by using any of the shortcodes listed in the Legoeso Settings admin menu. There are three (3) "types" of views that can be used.

= Is there a file size limit? =
The plugin has no file size limitation, however, your web hosting provider may have limitations, such as the maximum number of files that can be uploaded at one time, the maximum file upload size and the maximum memory that can be allocated to a pHp script. In cases of large documents, only a preview image will be generated, no text will not be extracted from the document, therefore the document will not be included when performing keyword searches. image or take a snapshot of the document's text will not be extracted from document that have more than a certain number of pages
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
Implemented new DataTable widgit child rows to show document metadata
Removed support Bootstrap v5.2.0-beta1 
Add support for jQuery Ui widgets
Implemented jQuery Ui progressbar

== Upgrade Notice ==

= 1.2.1 =
Initial release no upgrades at this time.  

== Plugin Features  ==

Features:

* Upload unlimited PDF files. 
* Group documents by category
* Easily display your documents by category
* Keyword Search 
* OCR text recognition
