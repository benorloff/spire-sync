<?php

namespace SpireSync\RestApi;

use SpireSync\Admin\Spire_Sync_Encryption;
use SpireSync\Admin\Spire_Sync_Spire_API_Client;

/**
 * Class Spire_Sync_Rest_API
 *
 * Handles the REST API functionality for the Spire Sync plugin.
 */
class Spire_Sync_Rest_API {

	/**
	 * Cached settings loaded from the options table.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Constructor.
	 * Hooks our REST route registration to rest_api_init.
	 */
	public function __construct() {
		// Load settings once on instantiation.
		$this->settings = get_option('spire_sync', []);
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	/**
	 * Register the custom REST API routes.
	 */
	public function register_routes() {
		register_rest_route('spire_sync/v1', '/settings', [
			'methods'             => 'POST',
			'callback'            => [$this, 'save_settings'],
			'permission_callback' => [$this, 'permissions_check'],
		]);
		register_rest_route('spire_sync/v1', '/test-connection', [
			'methods'             => 'GET',
			'callback'            => [$this, 'test_connection'],
			'permission_callback' => [$this, 'permissions_check'],
		]);
		// Add more routes here.
	}

	/**
	 * Permission callback for our REST routes.
	 *
	 * @return bool
	 */
	public function permissions_check() {
		return current_user_can('manage_options');
	}

	/**
	 * Callback function for saving settings.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response
	 */
	public function save_settings(\WP_REST_Request $request) {
		$params = $request->get_json_params();

		// TODO: Encrypt the password before saving it.
		// Use encryption helper.

		$new_settings = [
			'base_url'    => isset($params['base_url']) ? sanitize_text_field($params['base_url']) : '',
			'api_username' => isset($params['api_username']) ? sanitize_text_field($params['api_username']) : '',
			'api_password' => isset($params['api_password']) ? sanitize_text_field($params['api_password']) : '',
		];

		// Encrypt the API password.
		$encryption_key = defined('SPIRE_SYNC_ENCRYPTION_KEY') ? SPIRE_SYNC_ENCRYPTION_KEY : '';
		if (! empty($new_settings['api_password']) && $encryption_key) {
			$new_settings['api_password'] = Spire_Sync_Encryption::encrypt($new_settings['api_password'], $encryption_key);
		}

		update_option('spire_sync', $new_settings);

		// Refresh our local cached settings.
		$this->settings = $new_settings;

		return rest_ensure_response([
			'success' => true,
			'message' => __('Settings saved successfully.', 'spire-sync')
		]);
	}

	/**
	 * Tests the connection to the Spire API using stored credentials.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function test_connection(\WP_REST_Request $request) {
		// Use the cached settings.
		$base_url = isset($this->settings['base_url']) ? esc_url_raw($this->settings['base_url']) : '';
		$api_username = isset($this->settings['api_username']) ? sanitize_text_field($this->settings['api_username']) : '';
		$encrypted_password = isset($this->settings['api_password']) ? $this->settings['api_password'] : '';

		$api_password = '';
		// Decrypt the password.
		if (! empty($encrypted_password)) {
			$encryption_key = defined('SPIRE_SYNC_ENCRYPTION_KEY') ? SPIRE_SYNC_ENCRYPTION_KEY : '';
			$api_password = $encryption_key ? Spire_Sync_Encryption::decrypt($encrypted_password, $encryption_key) : '';
		}

		if (empty($base_url) || empty($api_username) || empty($api_password)) {
			return rest_ensure_response([
				'success' => false,
				'message' => __('Missing required Spire API credentials.', 'spire-sync'),
			]);
		}

		// Instantiate your Spire API client.
		$client = new Spire_Sync_Spire_API_Client();

		// Send a GET request to the Spire API base url to test the connection.
		$response = $client->request('GET', '');

		if (is_wp_error($response)) {
			return rest_ensure_response([
				'success' => false,
				'message' => sprintf(__('Connection test failed: %s', 'spire-sync'), $response->get_error_message()),
			]);
		}

		return rest_ensure_response([
			'success' => true,
			'message' => __('Connection successful.', 'spire-sync'),
		]);
	}
}

// Initialize the REST API functionality.
new Spire_Sync_Rest_API();
