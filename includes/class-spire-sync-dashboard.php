<?php
namespace SpireSync;

class Spire_Sync_Dashboard {

	/**
	 * Constructor: Hook in the admin menu registration.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_dashboard_page' ] );
	}

	/**
	 * Registers the top-level Dashboard page.
	 */
	public function register_dashboard_page() {
		add_menu_page(
			__( 'Spire Sync Dashboard', 'spire-sync' ), // Page title.
			__( 'Spire Sync', 'spire-sync' ),            // Menu title.
			'manage_options',                             // Capability.
			'spire-sync-dashboard',                       // Menu slug.
			[ $this, 'render_dashboard' ],                // Callback.
			'dashicons-update',                           // Icon.
			26                                            // Position.
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
}