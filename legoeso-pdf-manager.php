<?php
/*
Legoeso PDF Manager is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Legoeso PDF Manager is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Legoeso PDF Manager. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
*/
/**
 *
 * @link              	https://www.legoeso.com
 * @since             	1.0.0
 * @package           	Legoeso PDF Manager
 * @author				Torvis Wesley
 * @copyright			2022 Legoeso, LLC
 * @license				GPL-2.0+
 * 
 * @wordpress-plugin
 * Plugin Name:       	Legoeso PDF Manager
 * Plugin URI:        	https://www.legoeso.com/legoeso-pdf-document-manager
 * Description:       	The Legoeso PDF Manager is a simple, lightweight, but powerful PDF document manager. It helps you organize your PDF documents for easy display, and distribution within your WordPress site. It supports multiple file upload or bulk upload of files stored in a zip file.
 * Version:           	1.2.2
 * Requires at least:	5.6
 * Requires PHP:		7.0
 * Author:            	Torvis Wesley
 * Author URI:        	https://www.legoeso.com
 * License:           	GPL-2.0+
 * License URI:       	http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       	Legoeso PDF document manager
 * Domain Path:       	/languages
 */



namespace Legoeso_PDF_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Constants
 */

define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );

define( NS . 'PLUGIN_NAME', 'legoeso-pdf-manager' );

define( NS . 'PLUGIN_VERSION', '1.2.2' );

define( NS . 'PLUGIN_NAME_DIR', plugin_dir_path( __FILE__ ) );

define( NS . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );

define( NS . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( NS . 'PLUGIN_TEXT_DOMAIN', 'legoeso-pdf-manager' );

define( NS . 'JQUERY_UI_WP_PATH', plugin_dir_path( __FILE__ ) );

define( NS . 'JQUERY_UI_WP_URL', plugin_dir_url( __FILE__ ) );


/**
 * Autoload Classes
 */
require_once( PLUGIN_NAME_DIR . 'inc/libraries/autoloader.php' );

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */
register_activation_hook( __FILE__, array( NS . 'Inc\Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */
register_deactivation_hook( __FILE__, array( NS . 'Inc\Core\Deactivator', 'deactivate' ) );

/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin app object
 *
 * @since    1.0.0
 */
class Legoeso_PDF_Manager {

	static $init;
	/**
	 * Loads the plugin
	 *
	 * @access    public
	 */
	public static function init() {

		if ( null == self::$init ) {
			self::$init = new Inc\Core\Init();
			self::$init->run();
		}

		return self::$init;
	}

}

/*
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Also returns copy of the app object so 3rd party developers
 * can interact with the plugin's hooks contained within.
 *
 */
function Legoeso_PDF_Manager_init() {
		return Legoeso_PDF_Manager::init();
}

$min_php = '7.0.0';

// Check the minimum required PHP version and run the plugin.
if ( version_compare( PHP_VERSION, $min_php, '>=' ) ) {
		Legoeso_PDF_Manager_init();
}