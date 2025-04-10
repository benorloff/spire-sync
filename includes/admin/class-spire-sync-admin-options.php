<?php
namespace SpireSync\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Spire_Sync_Admin_Options {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
    }

    /**
     * Registers the top-level Dashboard and Settings submenu pages.
     */
    public function register_admin_menu() {
        // Top-level Dashboard page.
        add_menu_page(
            __( 'Spire Sync Dashboard', 'spire-sync' ),
            __( 'Spire Sync', 'spire-sync' ),
            'manage_options',
            'spire-sync-dashboard',
            [ $this, 'render_dashboard' ],
            'dashicons-update',
            26
        );

        // Settings page.
        add_submenu_page(
            'spire-sync-dashboard',
            __( 'Settings', 'spire-sync' ),
            __( 'Settings', 'spire-sync' ),
            'manage_options',
            'spire-sync-settings',
            [ $this, 'render_settings' ]
        );
    }

    /**
     * Renders the Dashboard page.
     */
    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Spire Sync Dashboard', 'spire-sync' ); ?></h1>
            <p><?php esc_html_e( 'Overview of plugin activity and quick links to other pages.', 'spire-sync' ); ?></p>
            <ul>
                <li><a href="<?php echo admin_url( 'admin.php?page=spire-sync-manage-syncs' ); ?>"><?php esc_html_e( 'Manage Syncs', 'spire-sync' ); ?></a></li>
                <li><a href="<?php echo admin_url( 'admin.php?page=spire-sync-field-mapping' ); ?>"><?php esc_html_e( 'Field Mapping', 'spire-sync' ); ?></a></li>
                <li><a href="<?php echo admin_url( 'admin.php?page=spire-sync-settings' ); ?>"><?php esc_html_e( 'Settings', 'spire-sync' ); ?></a></li>
                <li><a href="<?php echo admin_url( 'admin.php?page=spire-sync-logs' ); ?>"><?php esc_html_e( 'Logs', 'spire-sync' ); ?></a></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Renders the Settings page.
     */
    public function render_settings() {
        // Retrieve current settings.
        $settings = get_option( 'spire_sync_admin_options', [] );
        $api_username = isset( $settings['api_username'] ) ? esc_attr( $settings['api_username'] ) : '';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Spire Sync Settings', 'spire-sync' ); ?></h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'spire_sync_settings_nonce' ); ?>
                <input type="hidden" name="action" value="spire_sync_save_settings">
                <table class="form-table">
                    <tr>
                        <th><label for="api_username"><?php esc_html_e( 'API Username', 'spire-sync' ); ?></label></th>
                        <td><input type="text" name="api_username" id="api_username" value="<?php echo $api_username; ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="api_password"><?php esc_html_e( 'API Password', 'spire-sync' ); ?></label></th>
                        <td><input type="password" name="api_password" id="api_password" value="" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save Settings', 'spire-sync' ) ); ?>
            </form>
        </div>
        <?php
    }
}