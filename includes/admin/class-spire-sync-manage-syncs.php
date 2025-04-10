<?php
namespace SpireSync\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
            plugin_dir_url( __FILE__ ) . '../../assets/js/manage-syncs.build.js',
            [ 'jquery' ],
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
        // For now, prepopulate one sync option: Sync Inventory By Brand.
        // Example: Get brands from product_brand taxonomy.
        $brands = get_terms( [
            'taxonomy'   => 'product_brand',
            'hide_empty' => false,
        ]);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Manage Syncs', 'spire-sync' ); ?></h1>
            <p><?php esc_html_e( 'Select a brand and click "Start Sync" to trigger an inventory sync.', 'spire-sync' ); ?></p>
            <div id="sync-option">
                <h2><?php esc_html_e( 'Sync Inventory By Brand', 'spire-sync' ); ?></h2>
                <label for="sync_brand"><?php esc_html_e( 'Select Brand:', 'spire-sync' ); ?></label>
                <select id="sync_brand">
                    <option value=""><?php esc_html_e( '-- Select Brand --', 'spire-sync' ); ?></option>
                    <?php
                    if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) {
                        foreach ( $brands as $brand ) {
                            echo '<option value="' . esc_attr( $brand->name ) . '">' . esc_html( $brand->name ) . '</option>';
                        }
                    }
                    ?>
                </select>
                <button id="start-sync" class="button button-primary"><?php esc_html_e( 'Start Sync', 'spire-sync' ); ?></button>
            </div>
            <div id="sync-progress" style="display:none; margin-top:20px;">
                <div id="sync-status"></div>
                <div id="sync-progress-bar" style="background:#0073aa; height:20px; width:0%;"></div>
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