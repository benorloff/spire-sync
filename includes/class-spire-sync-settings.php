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
            'inventory_sync' => [
                'conditions' => [
                    [
                        'key' => 'upload',
                        'value' => 'true',
                        'operator' => 'equals'
                    ],
                    [
                        'key' => 'status',
                        'value' => '0',
                        'operator' => 'equals'
                    ]
                ],
                'match_type' => 'all', // 'all' or 'any'
                'warehouse_filter' => '', // Optional warehouse filter
                'category_filter' => '', // Optional category filter
                'sync_interval' => 'hourly', // How often to sync
                'last_sync' => '', // Timestamp of last sync
                'sync_status' => 'idle', // idle, running, error
                'error_message' => '' // Last error message if any
            ]
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

        $sanitized = [
            'base_url'     => esc_url_raw($settings['base_url'] ?? ''),
            'api_username' => sanitize_text_field($settings['api_username'] ?? ''),
            'api_password' => sanitize_text_field($settings['api_password'] ?? ''),
            'company_name' => sanitize_text_field($settings['company_name'] ?? ''),
        ];

        // Sanitize inventory sync settings
        if (isset($settings['inventory_sync'])) {
            $sanitized['inventory_sync'] = [
                'conditions' => [],
                'match_type' => in_array($settings['inventory_sync']['match_type'] ?? '', ['all', 'any']) 
                    ? $settings['inventory_sync']['match_type'] 
                    : 'all',
                'warehouse_filter' => sanitize_text_field($settings['inventory_sync']['warehouse_filter'] ?? ''),
                'category_filter' => sanitize_text_field($settings['inventory_sync']['category_filter'] ?? ''),
                'sync_interval' => in_array($settings['inventory_sync']['sync_interval'] ?? '', ['hourly', 'daily', 'weekly']) 
                    ? $settings['inventory_sync']['sync_interval'] 
                    : 'hourly',
                'last_sync' => sanitize_text_field($settings['inventory_sync']['last_sync'] ?? ''),
                'sync_status' => in_array($settings['inventory_sync']['sync_status'] ?? '', ['idle', 'running', 'error']) 
                    ? $settings['inventory_sync']['sync_status'] 
                    : 'idle',
                'error_message' => sanitize_text_field($settings['inventory_sync']['error_message'] ?? '')
            ];

            // Sanitize conditions
            if (isset($settings['inventory_sync']['conditions']) && is_array($settings['inventory_sync']['conditions'])) {
                foreach ($settings['inventory_sync']['conditions'] as $condition) {
                    if (isset($condition['key']) && isset($condition['value'])) {
                        $sanitized['inventory_sync']['conditions'][] = [
                            'key' => sanitize_text_field($condition['key']),
                            'value' => sanitize_text_field($condition['value']),
                            'operator' => in_array($condition['operator'] ?? '', ['equals', 'not_equals', 'contains', 'greater_than', 'less_than']) 
                                ? $condition['operator'] 
                                : 'equals'
                        ];
                    }
                }
            }
        }

        return $sanitized;
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
     * Get available inventory filter fields.
     *
     * @return array
     */
    public function get_inventory_filter_fields() {
        return [
            'upload' => __('Upload to WooCommerce', 'spire-sync'),
            'status' => __('Status', 'spire-sync'),
            'whse' => __('Warehouse', 'spire-sync'),
            'category' => __('Category', 'spire-sync'),
            'type' => __('Item Type', 'spire-sync'),
            'active' => __('Active Status', 'spire-sync'),
            'price' => __('Price', 'spire-sync'),
            'stock' => __('Stock Level', 'spire-sync'),
            'min_stock' => __('Minimum Stock', 'spire-sync'),
            'max_stock' => __('Maximum Stock', 'spire-sync'),
            'taxable' => __('Taxable', 'spire-sync'),
            'weight' => __('Weight', 'spire-sync'),
            'length' => __('Length', 'spire-sync'),
            'width' => __('Width', 'spire-sync'),
            'height' => __('Height', 'spire-sync')
        ];
    }

    /**
     * Get available operators for conditions.
     *
     * @return array
     */
    public function get_condition_operators() {
        return [
            'equals' => __('Equals', 'spire-sync'),
            'not_equals' => __('Not Equals', 'spire-sync'),
            'contains' => __('Contains', 'spire-sync'),
            'greater_than' => __('Greater Than', 'spire-sync'),
            'less_than' => __('Less Than', 'spire-sync')
        ];
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
            'inventoryFilterFields' => $this->get_inventory_filter_fields(),
            'conditionOperators' => $this->get_condition_operators(),
            'strings'  => [
                'saveSuccess' => __('Settings saved successfully.', 'spire-sync'),
                'saveError'   => __('Failed to save settings.', 'spire-sync'),
                'testSuccess' => __('Connection test successful.', 'spire-sync'),
                'testError'   => __('Connection test failed.', 'spire-sync'),
                'addCondition' => __('Add Condition', 'spire-sync'),
                'removeCondition' => __('Remove Condition', 'spire-sync'),
                'matchAll' => __('All conditions must be met', 'spire-sync'),
                'matchAny' => __('Any condition must be met', 'spire-sync'),
                'syncNow' => __('Sync Now', 'spire-sync'),
                'lastSync' => __('Last Sync', 'spire-sync'),
                'syncStatus' => __('Sync Status', 'spire-sync'),
                'syncInterval' => __('Sync Interval', 'spire-sync'),
                'hourly' => __('Hourly', 'spire-sync'),
                'daily' => __('Daily', 'spire-sync'),
                'weekly' => __('Weekly', 'spire-sync'),
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
