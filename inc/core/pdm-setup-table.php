<?php 
/**
* Creates a new table to used by the plugin within the WordPress
* database
 *
 * @link       https://www.legoeso.com
 * @since      1.0.0
 *
 * @author     Torvis Wesley
 */
function setupPDF_DocTables()
{
	global $wpdb;
	/**
	* Table information
	*/
	$tablename = $wpdb->prefix.'legoeso_file_storage';
	$charset_collate = $wpdb->get_charset_collate();
 	
	// $sql_query = "DROP TABLE IF EXISTS `{$tablename}`;";
	// $wpdb->query( $sql_query );

	$sql_query = "CREATE TABLE `{$tablename}` (
		`ID` int(11) NOT NULL,
		`pdf_doc_num` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`pdf_filesize` int(25) NOT NULL DEFAULT 0,
		`has_path` tinyint(1) NOT NULL DEFAULT 0,
		`has_img` tinyint(1) NOT NULL DEFAULT 0,
		`pdf_path` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
		`image_path` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
		`image_url` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
		`metadata` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
		`filetype` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`pdf_version` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`pdf_image` blob DEFAULT NULL,
		`text_data` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`pdf_data` longblob DEFAULT NULL,
		`category` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`upload_userid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`date_uploaded` date DEFAULT NULL,
		`insert_date` datetime NOT NULL DEFAULT current_timestamp()
	) ENGINE=InnoDB {$charset_collate};";
	$wpdb->query( $sql_query );

	$sql_query = "ALTER TABLE `{$tablename}`
	ADD PRIMARY KEY (`ID`);";
	$wpdb->query( $sql_query );

	$sql_query =   "ALTER TABLE `{$tablename}`
	MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;";

	$wpdb->query( $sql_query );
	$wpdb->show_errors();

	//	Initial values/ defaults for 

	// $data_force_image_enabled = array(
	// 	'option_name'		=>	'legoeso_force_image_enabled',
	// 	'option_value'		=>	'on',
	// 	'autoload'			=>	'yes',
	// );

	// //	Insert values into the WP_Options TABLE
	// $wpdb->insert($wpdb->prefix.'options', $data_force_image_enabled);

}

setupPDF_DocTables();

