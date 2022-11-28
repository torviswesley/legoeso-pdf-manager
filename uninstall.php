<?php
namespace Legoeso_PDF_Manager\Inc\Admin;

use Legoeso_PDF_Manager as NS;
use Legoeso_PDF_Manager\Inc\Common as Common;
use Legoeso_PDF_Manager\Inc\Libraries as Libraries;
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

if(is_user_logged_in() && current_user_can($this->pdm_required_cap))
{

// Drop the custom table if plugin is uninstalled.
// global $wpdb;
// $tablename = $wpdb->prefix.'legoeso_file_storage';
// $wpdb->query( "DROP TABLE IF EXISTS `{$tablename}`;" );
	exit;
}