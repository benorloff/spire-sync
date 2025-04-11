<?php
/**
 * Plugin Name: Spire Sync
 * Description: Sync WooCommerce data with Spire using React and WordPress components.
 * Version: 1.0.0
 * Author: Ben Orloff
 * Text Domain: spire-sync
 * License: GPLv2 or later
 */

defined( 'ABSPATH' ) || exit;

// Load admin classes.
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-spire-sync-dashboard.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-spire-sync-manage-syncs.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-spire-sync-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-spire-sync-logs.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/rest-api/class-spire-sync-rest-api.php';

use SpireSync\Admin\Spire_Sync_Dashboard;
use SpireSync\Admin\Spire_Sync_Manage_Syncs;
use SpireSync\Admin\Spire_Sync_Settings;
use SpireSync\Admin\Spire_Sync_Logs;
use SpireSync\RestApi\Spire_Sync_Rest_API;

/**
 * Initialize the plugin by instantiating admin classes.
 */
function spire_sync_init() {
    new Spire_Sync_Dashboard();
    new Spire_Sync_Manage_Syncs();
    new Spire_Sync_Settings();
    new Spire_Sync_Logs();
    new Spire_Sync_Rest_API();
}
add_action( 'plugins_loaded', 'spire_sync_init' );

/**
 * Enqueue admin scripts and styles.
 */
function spire_sync_enqueue_global_admin_styles() {
    // This enqueues the built-in wp-components styles on all admin pages.
    wp_enqueue_style( 'wp-components' );
}
add_action( 'admin_enqueue_scripts', 'spire_sync_enqueue_global_admin_styles' );