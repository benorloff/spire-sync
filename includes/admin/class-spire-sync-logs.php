<?php
namespace SpireSync\Admin;

use \Automattic\WooCommerce\Utilities\LoggingUtil;

class Spire_Sync_Logs {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_submenu' ] );
    }

    /**
     * Registers the Logs submenu page.
     */
    public function register_submenu() {
        add_submenu_page(
            'spire-sync-dashboard',
            __( 'Sync Logs', 'spire-sync' ),
            __( 'Logs', 'spire-sync' ),
            'manage_options',
            'spire-sync-logs',
            [ $this, 'render_logs_page' ]
        );
    }

    /**
     * Renders the Logs page.
     *
     * This example simply scans WooCommerce log files for a given source and displays the latest one.
     */
    public function render_logs_page() {
        $source = 'spire-sync'; // Make sure you log messages using this source.
        $log_dir = $this->get_log_directory();
        $files = glob( trailingslashit( $log_dir ) . $source . '-*.log' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Sync Logs', 'spire-sync' ); ?></h1>
            <form method="get">
                <input type="hidden" name="page" value="spire-sync-logs" />
                <label for="log_file"><?php esc_html_e( 'Select Log File:', 'spire-sync' ); ?></label>
                <select name="log_file" id="log_file">
                    <?php
                    if ( $files && is_array( $files ) ) {
                        foreach ( $files as $file ) {
                            $basename = basename( $file );
                            echo '<option value="' . esc_attr( $basename ) . '">' . esc_html( $basename ) . '</option>';
                        }
                    } else {
                        echo '<option value="">' . esc_html__( 'No log files found', 'spire-sync' ) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit" class="button"><?php esc_html_e( 'View Log', 'spire-sync' ); ?></button>
            </form>
            <?php
            if ( isset( $_GET['log_file'] ) && ! empty( $_GET['log_file'] ) ) {
                $selected_file = sanitize_text_field( $_GET['log_file'] );
                $file_path = trailingslashit( $log_dir ) . $selected_file;
                if ( file_exists( $file_path ) ) {
                    echo '<h2>' . esc_html__( 'Log File: ', 'spire-sync' ) . esc_html( $selected_file ) . '</h2>';
                    echo '<pre style="background:#f7f7f7;padding:10px;border:1px solid #ccc;">' . esc_html( file_get_contents( $file_path ) ) . '</pre>';
                } else {
                    echo '<p>' . esc_html__( 'Selected log file does not exist.', 'spire-sync' ) . '</p>';
                }
            }
            ?>
        </div>
        <?php
    }

    /**
     * Retrieve the WooCommerce log directory.
     *
     * Attempts to use LoggingUtil from WooCommerce; if not, falls back to wp-content/uploads/wc-logs.
     *
     * @return string Log directory path.
     */
    protected function get_log_directory() {
        if ( class_exists( 'LoggingUtil' ) && method_exists( 'LoggingUtil', 'get_log_directory' ) ) {
            if ( class_exists( 'LoggingUtil' ) && method_exists( 'LoggingUtil', 'get_log_directory' ) ) {
                return LoggingUtil::get_log_directory();
            }
            return trailingslashit( WP_CONTENT_DIR ) . 'uploads/wc-logs';
        }
        if ( class_exists( '\Automattic\WooCommerce\Utilities\LoggingUtil' ) &&
             method_exists( '\Automattic\WooCommerce\Utilities\LoggingUtil', 'get_log_directory' ) ) {
            return \Automattic\WooCommerce\Utilities\LoggingUtil::get_log_directory();
        }
        return trailingslashit( WP_CONTENT_DIR ) . 'uploads/wc-logs';
    }
}