<?php
/**
 * Plugin Name: Backofenrezepte Experience Database
 * Plugin URI:  https://backofenrezepte.example
 * Description: Collects, moderates, and manages structured user experiences for baking recipes. A standalone experience database, not a generic contact form.
 * Version:     1.0.0
 * Requires PHP: 7.4
 * Author:      Backofenrezepte
 * Text Domain: backofenrezepte-experiences
 * Domain Path: /languages
 *
 * @package Backofenrezepte_Experiences
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

// ---------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------
define( 'BR_EXP_VERSION', '1.0.0' );
define( 'BR_EXP_DB_SCHEMA_VERSION', '1.0.0' );
define( 'BR_EXP_PLUGIN_FILE', __FILE__ );
define( 'BR_EXP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BR_EXP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BR_EXP_TABLE_EXPERIENCES', 'br_experiences' );

// ---------------------------------------------------------------------
// Simple PSR-0-ish autoloader (PHP 7.4 friendly, no Composer requirement)
// ---------------------------------------------------------------------
spl_autoload_register(
	function ( $class ) {
		if ( strpos( $class, 'BR_' ) !== 0 ) {
			return;
		}

		$map = array(
			'BR_Plugin'                    => 'includes/class-br-plugin.php',
			'BR_Activator'                 => 'includes/class-br-activator.php',
			'BR_Deactivator'               => 'includes/class-br-deactivator.php',
			'BR_Migrations'                => 'includes/class-br-migrations.php',
			'BR_REST_Controller'           => 'includes/rest/class-br-rest-controller.php',
			'BR_REST_Public_Controller'    => 'includes/rest/class-br-rest-public-controller.php',
			'BR_REST_Admin_Controller'     => 'includes/rest/class-br-rest-admin-controller.php',
			'BR_Validator'                 => 'includes/core/class-br-validator.php',
			'BR_Sanitizer'                 => 'includes/core/class-br-sanitizer.php',
			'BR_Security_Guard'            => 'includes/core/class-br-security-guard.php',
			'BR_Experience_Service'        => 'includes/core/class-br-experience-service.php',
			'BR_Experience_Repository'     => 'includes/core/class-br-experience-repository.php',
			'BR_Id_Generator'              => 'includes/core/class-br-id-generator.php',
			'BR_Vocabulary'                => 'includes/core/class-br-vocabulary.php',
			'BR_Admin_Menu'                => 'includes/admin/class-br-admin-menu.php',
			'BR_List_Table'                => 'includes/admin/class-br-list-table.php',
			'BR_Admin_Detail'              => 'includes/admin/class-br-admin-detail.php',
			'BR_Admin_Settings'            => 'includes/admin/class-br-admin-settings.php',
			'BR_Admin_Analytics'           => 'includes/admin/class-br-admin-analytics.php',
			'BR_Csv_Export'                => 'includes/admin/class-br-csv-export.php',
			'BR_Frontend_Loader'           => 'includes/frontend/class-br-frontend-loader.php',
			'BR_Logger'                    => 'includes/utils/class-br-logger.php',
			'BR_Helpers'                   => 'includes/utils/class-br-helpers.php',
		);

		if ( isset( $map[ $class ] ) ) {
			require_once BR_EXP_PLUGIN_DIR . $map[ $class ];
		}
	}
);

// ---------------------------------------------------------------------
// Activation / Deactivation
// ---------------------------------------------------------------------
register_activation_hook( __FILE__, array( 'BR_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'BR_Deactivator', 'deactivate' ) );

// ---------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------
add_action( 'plugins_loaded', array( 'BR_Plugin', 'init' ) );
