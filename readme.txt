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

The Legoeso PDF Manager is a simple PDF document manager that allows for easy display, and distribution of PDF's within your WordPress site. 

== Description ==

The Legoeso PDF Manager is a light weight, simple but powerful PDF document manager. It helps you to organize your PDF documents for easy display, and distribution within your WordPress site. It supports multiple file upload or bulk upload of files stored in a zip file.

Features include unlimited file uploads. upload documents via drag and drop, bulk upload, bulk download, bulk delete, automatically generates documents previews for list display, and securely view PDF documents within WordPress. In addition, modify document names and categories, and perform keyword document searches. 



This plugin uses [DataTables](https://datatables.net/) to display and list of your PDF documents within WordPress. Easily include a list of PDF documents into your posts by using one of the shortcodes. Users of your website will be able to securely view the documents you provide to your visitors.

This plugin has much more to offer and can be used as an OCR text extraction tool for parsing the extracted text to collect and store useful data. 

== Frequently Asked Questions ==

= Where does Legoeso PDF Manager store its PDF documents? =

The Legoeso PDF manager stores PDF document information within a separate table within your WordPress database. It also stores the files within the WordPress upload directory. 
This allows for easy backup of your files, as well as integration or migration for use on or within a different platform. 

Text will not be extracted from files larger than a certain size, we will attempt to extract an
 image or take a snapshot of the documentstext will not be extracted fom document that have more than a certain number of pages

= Is there a file size limit? =

The plugin has no filesize limitation, however, your web hosting provider may limit the maximum upload filesize. In cases of very large PDF files, they are not stored within the database, but rather within WP upload directory.

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