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
		// unschedule the legoeso_cron_hook task upon deactivation
		$legoeso_cron_timestamp = wp_next_scheduled( 'legoeso_cron_hook');
		wp_unschedule_event($legoeso_cron_timestamp, 'legoeso_cron_hook');
	}
	
	
}
