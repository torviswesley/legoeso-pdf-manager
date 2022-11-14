=== Plugin Name ===
Contributors: torviswesley
Donate link: https://www.legoeso.com/
Tags: PDF Manager, PDF Documents, PDF Viewer, Organize
Requires at least: 5.6
Tested up to: 6.0
Stable tag: 1.2.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The Legoeso PDF Manager is a simple PDF document manager that allows for easy display, and distribution of PDF documents within your WordPress site. 

== Description ==

The Legoeso PDF Manager is a light weight, simple but powerful PDF document manager. It helps you organize your PDF documents for easy display, and distribution within your WordPress site. It supports multiple file upload or bulk upload of files stored in a zip file.

Features include unlimited file uploads. Upload documents via drag and drop, bulk upload, bulk download, bulk delete, automatically generates snapshot images for document previews, 
and securely view PDF documents within WordPress. In addition, perform keyword search for documents, and if needed you can modify document names and categories. Track uploads by date, upload user, or category.


This plugin uses [DataTables](https://datatables.net/) to display and list of your PDF documents within WordPress. Easily include a list of PDF documents into your posts by using one of the shortcodes. Secure access to your documents, only logged in user are able to view the documents you provide to your visitors.

This plugin has much more to offer and can be used to parse the extracted text to collect and store useful data from the documents you submit. 

== Frequently Asked Questions ==

= Where does Legoeso PDF Manager store its PDF documents? =

= How do I include my PDF documents in my post = 
Simple. Just include the shortcode [legoeso_display_documents] within your page. You can display your PDF documents on any page by using any of the shortcodes listed in the Legoeso Settings admin menu. There are three (3) "types" of views that can be used.

= Is there a file size limit? =

The plugin has no filesize limitation, however, your web hosting provider may limit the maximum upload filesize. In cases of large document, only a preview image will be generated no text will not be extracted from teh document, therefore the document will not be include when performing keyword searches.
 image or take a snapshot of the documentstext will not be extracted fom document that have more than a certain number of pages

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Screenshots are stored in the /assets directory.
2. This is the second screen shot

== Changelog ==

= 1.2.0 =
* A change since the original version. This is the latest version. Fixed security issues. 
Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 31477760 bytes)

Known Error/Issues with imagick - Segmentation fault for pdf input to pngalpha driver - https://bugs.ghostscript.com/show_bug.cgi?id=699815 
PHP Fatal error Uncaught ImagickException: FailedToExecuteCommand `'gs'

https://stackoverflow.com/questions/53560755/ghostscript-9-26-update-breaks-imagick-readimage-for-multipage-pdf
== A brief Markdown Example ==

Markdown is what the parser uses to process much of the readme file.

[markdown syntax]: https://daringfireball.net/projects/markdown/syntax

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Links require brackets and parenthesis:

Here's a link to [WordPress](https://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax]. Link titles are optional, naturally.

Blockquotes are email style:

> Asterisks for *emphasis*. Double it up  for **strong**.

And Backticks for code:

`<?php code(); ?>`


128 Memory Limit: 134217728
before: 12742976
after: 12742976
peak usage: 12744168

134217728
12742976
94409592
94409680

31477760