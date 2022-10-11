<?php

namespace Legoeso_PDF_Manager\Inc\Core;

/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://www.legoeso.com
 * @since      1.0.0
 *
 * @author     Torvis Wesley
 */

class Deactivator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
	global $wpdb;
	/**
	* Table information
	*/
	$tablename = $wpdb->prefix.'legoeso_file_storage';
	$wpdb->query( "DELETE FROM `{$tablename}` WHERE `option_name` LIKE '%Legoeso_%" );
	}

}
