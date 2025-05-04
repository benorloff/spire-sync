<?php

/**
 * Plugin Name: Spire Sync
 * Description: Sync WooCommerce data with Spire using React and WordPress components.
 * Version: 1.0.0
 * Author: Ben Orloff
 * Text Domain: spire-sync
 * License: GPLv2 or later
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

// Load admin classes.
require_once plugin_dir_path(__FILE__) . 'includes/class-spire-sync-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-spire-sync-manage-syncs.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-spire-sync-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-spire-sync-logs.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-spire-sync-spire-api-client.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-spire-sync-rest-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-spire-sync-encryption.php';

use SpireSync\Spire_Sync_Dashboard;
use SpireSync\Spire_Sync_Manage_Syncs;
use SpireSync\Spire_Sync_Settings;
use SpireSync\Spire_Sync_Logs;
use SpireSync\Spire_Sync_Spire_API_Client;
use SpireSync\Spire_Sync_Rest_API;
use SpireSync\Spire_Sync_Encryption;

/**
 * Initialize the plugin by instantiating admin classes.
 */
function spire_sync_init() {
    new Spire_Sync_Dashboard();
    new Spire_Sync_Manage_Syncs();
    new Spire_Sync_Settings();
    new Spire_Sync_Logs();
    new Spire_Sync_Spire_API_Client();
    new Spire_Sync_Rest_API();
    new Spire_Sync_Encryption();
}
add_action('plugins_loaded', 'spire_sync_init');

/**
 * Register plugin settings.
 */
function spire_sync_register_settings() {
    $default_settings = [
        'base_url'     => '',
        'api_username' => '',
        'api_password' => '',
        'company_name' => '',
    ];

    $schema = [
        'type'       => 'object',
        'properties' => [
            'base_url' => [
                'type'        => 'string',
                'format'      => 'uri',
                'description' => __('Base URL for Spire API', 'spire-sync'),
            ],
            'api_username' => [
                'type'        => 'string',
                'description' => __('API Username', 'spire-sync'),
            ],
            'api_password' => [
                'type'        => 'string',
                'description' => __('API Password', 'spire-sync'),
            ],
            'company_name' => [
                'type'        => 'string',
                'description' => __('Spire Company Name', 'spire-sync'),
            ],
        ],
    ];

    register_setting(
        'spire_sync_settings',
        'spire_sync_settings',
        [
            'type'         => 'object',
            'default'      => $default_settings,
            'show_in_rest' => [
                'schema' => $schema,
            ],
        ]
    );
}
add_action('init', 'spire_sync_register_settings');

/**
 * Enqueue global admin styles.
 */
function spire_sync_enqueue_global_admin_styles() {
    wp_enqueue_style('wp-components');
}
add_action('admin_enqueue_scripts', 'spire_sync_enqueue_global_admin_styles');

/**
 * Activation hook.
 */
function spire_sync_activate() {
    // Add any activation tasks here
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'spire_sync_activate');

/**
 * Deactivation hook.
 */
function spire_sync_deactivate() {
    // Add any deactivation tasks here
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'spire_sync_deactivate');

/**
 * Uninstall hook.
 */
function spire_sync_uninstall() {
    // Add any uninstall tasks here
    delete_option('spire_sync_settings');
}
register_uninstall_hook(__FILE__, 'spire_sync_uninstall');
