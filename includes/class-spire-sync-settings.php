<?php

namespace SpireSync;

/**
 * Class Spire_Sync_Settings
 *
 * Handles the settings page and functionality for the Spire Sync plugin.
 */
class Spire_Sync_Settings {

    /**
     * The page slug for the settings page.
     *
     * @var string
     */
    private $page_slug = 'spire-sync-settings';

    /**
     * Constructor: Hook in the settings page registration.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('spire_sync_settings_updated', [$this, 'handle_settings_update']);
    }

    /**
     * Registers the Settings submenu page.
     */
    public function register_settings_page() {
        add_submenu_page(
            'spire-sync-dashboard',                     // Parent slug; Dashboard.
            __('Spire Sync Settings', 'spire-sync'),   // Page title.
            __('Settings', 'spire-sync'),              // Menu title.
            'manage_options',                          // Capability.
            $this->page_slug,                          // Menu slug.
            [$this, 'render_settings_page']            // Callback.
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting(
            'spire_sync_settings',
            'spire_sync_settings',
            [
                'type'              => 'object',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default'           => $this->get_default_settings(),
            ]
        );
    }

    /**
     * Get default settings.
     *
     * @return array
     */
    private function get_default_settings() {
        return [
            'base_url'     => '',
            'api_username' => '',
            'api_password' => '',
            'company_name' => '',
        ];
    }

    /**
     * Sanitize settings before saving.
     *
     * @param array $settings The settings to sanitize.
     * @return array
     */
    public function sanitize_settings($settings) {
        if (!is_array($settings)) {
            return $this->get_default_settings();
        }

        return [
            'base_url'     => esc_url_raw($settings['base_url'] ?? ''),
            'api_username' => sanitize_text_field($settings['api_username'] ?? ''),
            'api_password' => sanitize_text_field($settings['api_password'] ?? ''),
            'company_name' => sanitize_text_field($settings['company_name'] ?? ''),
        ];
    }

    /**
     * Handle settings update.
     *
     * @param array $settings The updated settings.
     */
    public function handle_settings_update($settings) {
        // Log the settings update
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Spire Sync settings updated: ' . print_r($settings, true));
        }

        // Clear any cached data that might be affected by the settings change
        do_action('spire_sync_clear_cache');
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_assets($hook) {
        if ('spire-sync_page_' . $this->page_slug !== $hook) {
            return;
        }

        // Enqueue WordPress components
        wp_enqueue_style('wp-components');

        // Enqueue our settings script
        wp_enqueue_script(
            'spire-sync-settings',
            plugin_dir_url(__FILE__) . '../build/settings.build.js',
            ['wp-element', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch'],
            filemtime(plugin_dir_path(__FILE__) . '../build/settings.build.js'),
            true
        );

        // Localize script with settings and nonce
        wp_localize_script('spire-sync-settings', 'spireSyncSettings', [
            'nonce'    => wp_create_nonce('wp_rest'),
            'settings' => get_option('spire_sync_settings', []),
            'apiUrl'   => rest_url('spire_sync/v1'),
            'strings'  => [
                'saveSuccess' => __('Settings saved successfully.', 'spire-sync'),
                'saveError'   => __('Failed to save settings.', 'spire-sync'),
                'testSuccess' => __('Connection test successful.', 'spire-sync'),
                'testError'   => __('Connection test failed.', 'spire-sync'),
            ],
        ]);

        // Enqueue our settings styles
        wp_enqueue_style(
            'spire-sync-settings-style',
            plugin_dir_url(__FILE__) . '../assets/css/settings.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . '../assets/css/settings.css')
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
