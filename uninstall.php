<?php
namespace Legoeso_PDF_Manager;

use Legoeso_PDF_Manager\Inc\Common as Common;

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://www.legoeso.com
 * @since      1.0.0
 *
 * @package    Legoeso_PDF_Manager
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
/**
 * Autoload Classes
 */
require_once( __DIR__ . '/inc/libraries/autoloader.php' );

if (is_user_logged_in() && current_user_can('upload_files')){

	// Drop the custom legoeso_file_storage table when plugin is uninstalled.
	global $wpdb;
	$tablename = $wpdb->prefix.'legoeso_file_storage';
	$wpdb->query("DROP TABLE IF EXISTS `{$tablename}`;");
	// clean up any remaining files/documents left behind
	$utils = new Common\Utility_Functions();
	$utils->legoeso_cleanup(true);
}