<?php
namespace SpireSync\RestApi;

class Spire_Sync_Rest_API {

	/**
	 * Constructor.
	 * Hooks our REST route registration to rest_api_init.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register the custom REST API routes.
	 */
	public function register_routes() {
		register_rest_route( 'spire_sync/v1', '/settings', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'save_settings' ],
			'permission_callback' => [ $this, 'permissions_check' ],
		] );
		// You can add more routes here.
	}

	/**
	 * Permission callback for our REST routes.
	 *
	 * @return bool
	 */
	public function permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Callback function for saving settings.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response
	 */
	public function save_settings( \WP_REST_Request $request ) {
		$params = $request->get_json_params();

		$settings = [
			'base_url'    => isset( $params['base_url'] ) ? sanitize_text_field( $params['base_url'] ) : '',
			'api_username'=> isset( $params['api_username'] ) ? sanitize_text_field( $params['api_username'] ) : '',
			'api_password'=> isset( $params['api_password'] ) ? sanitize_text_field( $params['api_password'] ) : '',
		];

		// Optionally, use your encryption helper to encrypt the password.
		// e.g., $settings['api_password'] = \SpireSync\Admin\Spire_Sync_Encryption::encrypt( $settings['api_password'], $encryption_key );

		update_option( 'spire_sync_admin_options', $settings );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Settings saved successfully.', 'spire-sync' )
		] );
	}
}

// Initialize the REST API functionality.
new Spire_Sync_Rest_API();