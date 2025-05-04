<?php

namespace SpireSync;

use SpireSync\Spire_Sync_Encryption;
use SpireSync\Spire_Sync_Spire_API_Client;

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
		$raw_settings = get_option('spire_sync_settings', []);
		$this->settings = [
			'base_url' => $raw_settings['base_url'] ?? '',
			'company_name' => $raw_settings['company_name'] ?? '',
			'api_username' => $raw_settings['api_username'] ?? '',
			'api_password' => $raw_settings['api_password'] ?? ''
		];
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	/**
	 * Register the custom REST API routes.
	 */
	public function register_routes() {
		register_rest_route('spire_sync/v1', '/settings', [
			'methods'             => 'GET',
			'callback'            => [$this, 'get_settings'],
			'permission_callback' => [$this, 'permissions_check'],
			'args'               => [
				'context' => [
					'default' => 'view',
					'enum'    => ['view', 'edit'],
				],
			],
		]);
		register_rest_route('spire_sync/v1', '/settings', [
			'methods'             => 'POST',
			'callback'            => [$this, 'save_settings'],
			'permission_callback' => [$this, 'permissions_check'],
			'args'               => [
				'base_url' => [
					'required'    => true,
					'type'        => 'string',
					'format'      => 'uri',
					'description' => __('The base URL for the Spire API', 'spire-sync'),
				],
				'company_name' => [
					'required' => true,
					'type' => 'string',
					'description' => __('The Spire company name', 'spire-sync'),
				],
				'api_username' => [
					'required'    => true,
					'type'        => 'string',
					'description' => __('The API username', 'spire-sync'),
				],
				'api_password' => [
					'required'    => true,
					'type'        => 'string',
					'description' => __('The API password', 'spire-sync'),
				],
			],
		]);
		register_rest_route('spire_sync/v1', '/test-connection', [
			'methods'             => 'POST',
			'callback'            => [$this, 'test_connection'],
			'permission_callback' => [$this, 'permissions_check'],
			'args'               => [
				'base_url' => [
					'required'    => true,
					'type'        => 'string',
					'format'      => 'uri',
				],
				'company_name' => [
					'required' => true,
					'type' => 'string',
				],
				'api_username' => [
					'required'    => true,
					'type'        => 'string',
				],
				'api_password' => [
					'required'    => true,
					'type'        => 'string',
				],
			],
		]);
		register_rest_route('spire-sync/v1', '/encrypt', [
			'methods' => 'POST',
			'callback' => [$this, 'encrypt_data'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'data' => [
					'required' => true,
					'type' => 'string',
				],
			],
		]);
		register_rest_route('spire_sync/v1', '/companies', [
			'methods' => 'GET',
			'callback' => [$this, 'get_companies'],
			'permission_callback' => [$this, 'permissions_check'],
		]);
		// Add more routes here.
	}

	/**
	 * Permission callback for our REST routes.
	 *
	 * @return bool|\WP_Error
	 */
	public function permissions_check() {
		if (!current_user_can('manage_options')) {
			return new \WP_Error(
				'rest_forbidden',
				__('Sorry, you are not allowed to manage these settings.', 'spire-sync'),
				['status' => rest_authorization_required_code()]
			);
		}
		return true;
	}

	/**
	 * Callback function for saving settings.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function save_settings(\WP_REST_Request $request) {
		try {
			$params = $request->get_json_params();

			// Validate required fields
			if (empty($params['base_url']) || empty($params['company_name']) || empty($params['api_username']) || empty($params['api_password'])) {
				return new \WP_Error(
					'missing_required_fields',
					__('Missing required fields: base_url, company_name, api_username, or api_password', 'spire-sync'),
					['status' => 400]
				);
			}

			// Sanitize inputs
			$new_settings = [
				'base_url'    => esc_url_raw($params['base_url']),
				'company_name' => sanitize_text_field($params['company_name']),
				'api_username' => sanitize_text_field($params['api_username']),
				'api_password' => sanitize_text_field($params['api_password']),
			];

			// Encrypt the API password
			// $encryption_key = defined('SPIRE_SYNC_ENCRYPTION_KEY') ? SPIRE_SYNC_ENCRYPTION_KEY : '';
			// if (!empty($new_settings['api_password']) && $encryption_key) {
			// 	$new_settings['api_password'] = Spire_Sync_Encryption::encrypt($new_settings['api_password'], $encryption_key);
			// }

			// Save settings
			$result = update_option('spire_sync_settings', $new_settings);
			if (!$result) {
				return new \WP_Error(
					'settings_save_failed',
					__('Failed to save settings', 'spire-sync'),
					['status' => 500]
				);
			}

			// Refresh our local cached settings
			$this->settings = $new_settings;

			// Log the settings update
			do_action('spire_sync_settings_updated', $new_settings);

			return rest_ensure_response([
				'success' => true,
				'message' => __('Settings saved successfully.', 'spire-sync')
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'settings_save_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Tests the connection to the Spire API.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function test_connection(\WP_REST_Request $request) {
		// 1. Retrieve credentials from the request.
		$base_url     = $request->get_param('base_url') ? esc_url_raw($request->get_param('base_url')) : '';
		$api_username = $request->get_param('api_username') ? sanitize_text_field($request->get_param('api_username')) : '';
		$api_password = $request->get_param('api_password') ? sanitize_text_field($request->get_param('api_password')) : '';

		// 2. Validate inputs.
		if (empty($base_url) || empty($api_username) || empty($api_password)) {
			return rest_ensure_response([
				'success' => false,
				'message' => __('Missing required Spire API credentials.', 'spire-sync'),
			]);
		}

		// 3. Instantiate your Spire API client using the provided credentials.
		//    Here we assume your client class can accept URL, username, and password,
		//    either via constructor or setter methods.
		$client = new Spire_Sync_Spire_API_Client([
			'base_url'  => $base_url,
			'username'  => $api_username,
			'password'  => $api_password,
		]);

		// 4. Perform a simple GET request to verify connectivity.
		$response = $client->request('GET', '');

		// 5. Check for errors.
		if (is_wp_error($response)) {
			return rest_ensure_response([
				'success' => false,
				'message' => sprintf(
					__('Connection test failed: %s', 'spire-sync'),
					$response->get_error_message()
				),
			]);
		}

		// 6. Success!
		return rest_ensure_response([
			'success' => true,
			'message' => __('Connection successful.', 'spire-sync'),
		]);
	}

	/**
	 * Callback function for getting settings.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings() {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('Spire Sync REST API - Raw settings from DB: ' . print_r(get_option('spire_sync_settings', []), true));
			error_log('Spire Sync REST API - Processed settings: ' . print_r($this->settings, true));
		}
		$settings = get_option('spire_sync_settings', []);

		if (empty($settings)) {
			return rest_ensure_response([
				'success' => false,
				'message' => __('No settings found.', 'spire-sync'),
			]);
		}

		return rest_ensure_response([
			'success' => true,
			'data' => $settings
		]);
	}

	/**
	 * Callback function for encrypting data.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function encrypt_data(\WP_REST_Request $request) {
		$data = $request->get_param('data');
		if (empty($data)) {
			return new \WP_Error('missing_data', 'Data is required', ['status' => 400]);
		}

		// Get encryption key from wp-config or use a fallback
		$key = defined('SPIRE_SYNC_ENCRYPTION_KEY') ? SPIRE_SYNC_ENCRYPTION_KEY : wp_salt('auth');
		
		$encrypted = Spire_Sync_Encryption::encrypt($data, $key);
		if (false === $encrypted) {
			return new \WP_Error('encryption_failed', 'Failed to encrypt data', ['status' => 500]);
		}

		return [
			'success' => true,
			'data' => $encrypted
		];
	}

	/**
	 * Callback function for getting companies.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_companies() {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$response = $client->get_companies();

			if (is_wp_error($response)) {
				return new \WP_Error(
					'companies_fetch_failed',
					$response->get_error_message(),
					['status' => 500]
				);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'companies_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}
}
