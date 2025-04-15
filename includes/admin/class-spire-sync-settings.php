<?php

namespace SpireSync\Admin;

class Spire_Sync_Settings {

    /**
     * Constructor: Hook in the settings page registration.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        // add_action('init', [ $this, 'spire_sync_settings' ]);
    }

    /**
     * Registers the Settings submenu page.
     */
    public function register_settings_page() {
        add_submenu_page(
            'spire-sync-dashboard',                     // Parent slug; Dashboard.
            __('Spire Sync Settings', 'spire-sync'),   // Page title.
            __('Settings', 'spire-sync'),              // Menu title.
            'manage_options',                            // Capability.
            'spire-sync-settings',                       // Menu slug.
            [$this, 'render_settings_page']                 // Callback.
        );
    }

    public function enqueue_assets($hook) {
        // Only enqueue on the settings page.
        if ('spire-sync_page_spire-sync-settings' !== $hook) {
            return;
        }
        wp_enqueue_script(
            'spire-sync-settings',
            plugin_dir_url(__FILE__) . '../../build/settings.build.js',
            ['wp-element', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch'],
            '1.0.0',
            true
        );

        // wp_localize_script('spire-sync-settings', 'spireSyncSettings', [
        //     'nonce'    => wp_create_nonce('wp_rest'),
        //     'settings' => get_option('spire_sync_admin_options', []),
        // ]);

        wp_enqueue_style(
            'spire-sync-settings-style',
            plugin_dir_url( __FILE__ ) . '../../assets/css/settings.css',
            array(), // Add dependencies if needed.
            '1.0.0'
        );
    }

    /**
     * Renders the Settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <div id="spire-sync-settings-root">
                <!-- React Settings component will mount here -->
            </div>
        </div>
        <?php
    }
}
