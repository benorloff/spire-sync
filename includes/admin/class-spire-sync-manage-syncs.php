<?php
namespace SpireSync\Admin;

class Spire_Sync_Manage_Syncs {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_submenu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Register AJAX handlers for syncing (if using them; these could also be WP-Cron callbacks).
        add_action( 'wp_ajax_spire_sync_start_inventory_sync', [ $this, 'ajax_start_inventory_sync' ] );
        add_action( 'wp_ajax_spire_sync_get_progress', [ $this, 'ajax_get_progress' ] );
    }

    public function register_submenu() {
        add_submenu_page(
            'spire-sync-dashboard',
            __( 'Manage Syncs', 'spire-sync' ),
            __( 'Manage Syncs', 'spire-sync' ),
            'manage_options',
            'spire-sync-manage-syncs',
            [ $this, 'render_manage_syncs_page' ]
        );
    }

    public function enqueue_assets() {
        wp_enqueue_script(
            'spire-sync-manage-syncs',
            plugin_dir_url( __FILE__ ) . '../../build/manage-syncs.build.js',
            [ 'wp-element', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch', 'jquery' ],
            '1.0.0',
            true
        );
        wp_enqueue_style(
            'spire-sync-manage-syncs',
            plugin_dir_url( __FILE__ ) . '../../assets/css/admin.css'
        );
        wp_localize_script( 'spire-sync-manage-syncs', 'spireSyncSettings', [
            'nonce' => wp_create_nonce( 'spire_sync_sync_nonce' ),
        ]);
    }

    /**
     * Renders the Manage Syncs page.
     */
    public function render_manage_syncs_page() {
        ?>
        <div class="wrap">
            <div id="spire-sync-manage-syncs-root">
                <!-- React ManageSyncs component will mount here -->
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler to start the sync.
     */
    public function ajax_start_inventory_sync() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }
        check_ajax_referer( 'spire_sync_sync_nonce', 'nonce' );
        $brand = isset( $_POST['brand'] ) ? sanitize_text_field( $_POST['brand'] ) : '';
        if ( empty( $brand ) ) {
            wp_send_json_error( 'No brand selected', 400 );
        }

        // Schedule a WP-Cron event to process the sync.
        wp_schedule_single_event( time(), 'spire_sync_cron_inventory_sync', [ $brand ] );

        // Set initial progress.
        $progress = [
            'status'    => "Sync scheduled for brand {$brand}.",
            'processed' => 0,
            'total'     => 0,
        ];
        set_transient( "spire_sync_progress_{$brand}", $progress, 5 * MINUTE_IN_SECONDS );
        wp_send_json_success( $progress );
    }

    /**
     * AJAX handler to get progress.
     */
    public function ajax_get_progress() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }
        $brand = isset( $_GET['brand'] ) ? sanitize_text_field( $_GET['brand'] ) : '';
        if ( empty( $brand ) ) {
            wp_send_json_error( 'No brand specified', 400 );
        }
        $progress = get_transient( "spire_sync_progress_{$brand}" );
        if ( false === $progress ) {
            wp_send_json_error( 'No progress found', 404 );
        }
        wp_send_json_success( $progress );
    }

    /**
     * WP-Cron callback to process the inventory sync.
     * This method should call your WooCommerce sync function.
     *
     * @param string $brand Brand to sync.
     */
    public static function process_inventory_sync( $brand ) {
        // Here, you would call your API client to retrieve data,
        // then pass the response to your WooCommerce syncing function.
        // For example:
        $client = new Spire_Sync_Spire_API_Client();
        $query_params = [
            'limit'  => 100,
            'udf'    => 1,
            'filter' => json_encode( [
                'whse'     => '01',
                'status'   => 0,
                'upload'   => 'TRUE',
                'userDef1' => $brand,
            ]),
            'fields' => 'whse,partNo,userDef1,description,alternatePartNo,manufactureCountry,pricing.sellPrice,uom.weight'
        ];
        $endpoint = 'inventory/items?' . http_build_query( $query_params );
        $response = $client->request( 'GET', $endpoint );
        if ( is_wp_error( $response ) ) {
            $progress = [
                'status'    => "Error: " . $response->get_error_message(),
                'processed' => 0,
                'total'     => 0,
            ];
            set_transient( "spire_sync_progress_{$brand}", $progress, 5 * MINUTE_IN_SECONDS );
            return;
        }
        // Now delegate to the WooCommerce sync function.
        $woocommerce_sync = new Spire_Sync_WooCommerce();
        $woocommerce_sync->insert_or_update_products( $response, $brand );
    }
}
 
// Register WP-Cron hook.
add_action( 'spire_sync_cron_inventory_sync', [ 'SpireSync\Admin\Spire_Sync_Manage_Syncs', 'process_inventory_sync' ] );