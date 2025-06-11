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
					'required' => false,
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
		register_rest_route('spire_sync/v1', '/encrypt', [
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
		register_rest_route('spire_sync/v1', '/products', [
			'methods' => 'GET',
			'callback' => [$this, 'get_products'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object for the Spire API', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
					'description' => __('Starting index for pagination', 'spire-sync'),
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
					'description' => __('Maximum number of items to return', 'spire-sync'),
				],
				'udf' => [
					'required' => false,
					'type' => 'boolean',
					'default' => true,
					'description' => __('Whether to include user defined fields', 'spire-sync'),
				],
			],
		]);

		// Customers routes
		register_rest_route('spire_sync/v1', '/customers', [
			'methods' => 'GET',
			'callback' => [$this, 'get_customers'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
				],
			],
		]);

		register_rest_route('spire_sync/v1', '/customers/(?P<id>[a-zA-Z0-9-]+)', [
			'methods' => 'GET',
			'callback' => [$this, 'get_customer'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'id' => [
					'required' => true,
					'type' => 'string',
					'description' => __('Customer ID', 'spire-sync'),
				],
			],
		]);

		register_rest_route('spire_sync/v1', '/customers/(?P<id>[a-zA-Z0-9-]+)/contacts', [
			'methods' => 'GET',
			'callback' => [$this, 'get_customer_contacts'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'id' => [
					'required' => true,
					'type' => 'string',
					'description' => __('Customer ID', 'spire-sync'),
				],
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
				],
			],
		]);

		// Inventory routes
		register_rest_route('spire_sync/v1', '/inventory/items', [
			'methods' => 'GET',
			'callback' => [$this, 'get_inventory_items'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
				],
			],
		]);

		register_rest_route('spire_sync/v1', '/inventory/items/(?P<id>[a-zA-Z0-9-]+)', [
			'methods' => 'GET',
			'callback' => [$this, 'get_inventory_item'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'id' => [
					'required' => true,
					'type' => 'string',
					'description' => __('Inventory item ID', 'spire-sync'),
				],
			],
		]);

		// Payment methods route
		register_rest_route('spire_sync/v1', '/payment-methods', [
			'methods' => 'GET',
			'callback' => [$this, 'get_payment_methods'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
				],
			],
		]);

		// Sales orders routes
		register_rest_route('spire_sync/v1', '/sales/orders', [
			'methods' => 'GET',
			'callback' => [$this, 'get_sales_orders'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
				],
			],
		]);

		register_rest_route('spire_sync/v1', '/sales/orders/(?P<id>[a-zA-Z0-9-]+)', [
			'methods' => 'GET',
			'callback' => [$this, 'get_sales_order'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'id' => [
					'required' => true,
					'type' => 'string',
					'description' => __('Sales order ID', 'spire-sync'),
				],
			],
		]);

		register_rest_route('spire_sync/v1', '/sales/orders', [
			'methods' => 'POST',
			'callback' => [$this, 'create_sales_order'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'order_data' => [
					'required' => true,
					'type' => 'object',
					'description' => __('Sales order data', 'spire-sync'),
				],
			],
		]);

		register_rest_route('spire_sync/v1', '/sales/orders/(?P<id>[a-zA-Z0-9-]+)', [
			'methods' => 'PUT',
			'callback' => [$this, 'update_sales_order'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'id' => [
					'required' => true,
					'type' => 'string',
					'description' => __('Sales order ID', 'spire-sync'),
				],
				'order_data' => [
					'required' => true,
					'type' => 'object',
					'description' => __('Updated sales order data', 'spire-sync'),
				],
			],
		]);

		// Shipping methods route
		register_rest_route('spire_sync/v1', '/shipping-methods', [
			'methods' => 'GET',
			'callback' => [$this, 'get_shipping_methods'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
				],
			],
		]);

		// Territories routes
		register_rest_route('spire_sync/v1', '/territories', [
			'methods' => 'GET',
			'callback' => [$this, 'get_territories'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'filter' => [
					'required' => false,
					'type' => 'string',
					'description' => __('JSON encoded filter object', 'spire-sync'),
				],
				'start' => [
					'required' => false,
					'type' => 'integer',
					'default' => 0,
				],
				'limit' => [
					'required' => false,
					'type' => 'integer',
					'default' => 100,
				],
			],
		]);

		register_rest_route('spire_sync/v1', '/territories/(?P<id>[a-zA-Z0-9-]+)', [
			'methods' => 'GET',
			'callback' => [$this, 'get_territory'],
			'permission_callback' => [$this, 'permissions_check'],
			'args' => [
				'id' => [
					'required' => true,
					'type' => 'string',
					'description' => __('Territory ID', 'spire-sync'),
				],
			],
		]);

		// Dynamic field value endpoints
		register_rest_route('spire_sync/v1', '/inventory/warehouses', [
			'methods' => 'GET',
			'callback' => [$this, 'get_warehouses'],
			'permission_callback' => [$this, 'permissions_check'],
		]);
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
		$base_url     = $request->get_param('base_url') ? esc_url_raw($request->get_param('base_url')) : '';
		$api_username = $request->get_param('api_username') ? sanitize_text_field($request->get_param('api_username')) : '';
		$api_password = $request->get_param('api_password') ? sanitize_text_field($request->get_param('api_password')) : '';

		if (empty($base_url) || empty($api_username) || empty($api_password)) {
			return rest_ensure_response([
				'success' => false,
				'message' => __('Missing required Spire API credentials.', 'spire-sync'),
			]);
		}

		$client = new Spire_Sync_Spire_API_Client([
			'base_url'  => $base_url,
			'username'  => $api_username,
			'password'  => $api_password,
		]);

		$response = $client->request('GET', '');

		if (is_wp_error($response)) {
			return rest_ensure_response([
				'success' => false,
				'error' => $response->get_error_message()
			]);
		}

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
				return $response;
			}

			if (!$response['success']) {
				return new \WP_Error(
					'companies_fetch_failed',
					(string) $response['error'],
					['status' => 500]
				);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'companies_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting products.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_products(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			
			// Prepare query parameters
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
				'udf' => $request->get_param('udf'),
			];

			// Add filter if provided
			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_products($query_params);

			if (is_wp_error($response)) {
				return $response;
			}

			if (!$response['success']) {
				return new \WP_Error(
					'products_fetch_failed',
					(string) $response['error'],
					['status' => 500]
				);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'products_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting customers.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_customers(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
			];

			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_customers($query_params);

			if (!$response['success']) {
				return new \WP_Error(
					'customers_fetch_failed',
					(string) $response['error'],
					['status' => 500]
				);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'customers_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting a specific customer.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_customer(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$customer_id = $request->get_param('id');
			
			$response = $client->get_customer($customer_id);

			if (!$response['success']) {
				return new \WP_Error(
					'customer_fetch_failed',
					(string) $response['error'],
					['status' => 500]
				);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'customer_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting customer contacts.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_customer_contacts(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$customer_id = $request->get_param('id');
			
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
			];

			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_customer_contacts($customer_id, $query_params);

			if (!$response['success']) {
				return new \WP_Error(
					'contacts_fetch_failed',
					(string) $response['error'],
					['status' => 500]
				);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'contacts_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting inventory items.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_inventory_items(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
			];

			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_inventory_items($query_params);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch inventory items', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'inventory_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting a specific inventory item.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_inventory_item(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$item_id = $request->get_param('id');
			
			$response = $client->get_inventory_item($item_id);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch inventory item', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'inventory_item_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting payment methods.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_payment_methods(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
			];

			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_payment_methods($query_params);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch payment methods', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'payment_methods_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting sales orders.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_sales_orders(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
			];

			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_sales_orders($query_params);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch sales orders', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'sales_orders_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting a specific sales order.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_sales_order(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$order_id = $request->get_param('id');
			
			$response = $client->get_sales_order($order_id);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch sales order', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'sales_order_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for creating a sales order.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_sales_order(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$order_data = $request->get_param('order_data');
			
			$response = $client->create_sales_order($order_data);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to create sales order', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'sales_order_create_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for updating a sales order.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_sales_order(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$order_id = $request->get_param('id');
			$order_data = $request->get_param('order_data');
			
			$response = $client->update_sales_order($order_id, $order_data);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to update sales order', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'sales_order_update_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting shipping methods.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_shipping_methods(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
			];

			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_shipping_methods($query_params);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch shipping methods', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'shipping_methods_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting territories.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_territories(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			
			$query_params = [
				'start' => $request->get_param('start'),
				'limit' => $request->get_param('limit'),
			];

			$filter = $request->get_param('filter');
			if (!empty($filter)) {
				$decoded_filter = json_decode($filter, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$query_params['filter'] = $filter;
				} else {
					return new \WP_Error(
						'invalid_filter',
						__('Invalid filter JSON format', 'spire-sync'),
						['status' => 400]
					);
				}
			}

			$response = $client->get_territories($query_params);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch territories', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'territories_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Callback function for getting a specific territory.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_territory(\WP_REST_Request $request) {
		try {
			$client = new Spire_Sync_Spire_API_Client();
			$territory_id = $request->get_param('id');
			
			$response = $client->get_territory($territory_id);

			if (is_wp_error($response)) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response->get_error_message()
				]);
			}

			if (!$response['success']) {
				return rest_ensure_response([
					'success' => false,
					'error' => $response['error'] ?? __('Failed to fetch territory', 'spire-sync')
				]);
			}

			return rest_ensure_response([
				'success' => true,
				'data' => $response['data']
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'territory_fetch_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Get warehouses from Spire API.
	 *
	 * @return WP_REST_Response
	 */
	public function get_warehouses() {
		$client = new Spire_Sync_Spire_API_Client();
		$response = $client->get_warehouses();

		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('Spire REST API - Warehouses Response: ' . print_r($response, true));
		}

		if (is_wp_error($response)) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('Spire REST API - Warehouses Error: ' . $response->get_error_message());
			}
			return new \WP_REST_Response([
				'success' => false,
				'error' => $response->get_error_message()
			]);
		}

		if (!$response['success']) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('Spire REST API - Warehouses Error: ' . ($response['error'] ?? 'Unknown error'));
			}
			return new \WP_REST_Response([
				'success' => false,
				'error' => $response['error'] ?? __('Failed to fetch warehouses', 'spire-sync')
			]);
		}

		// Extract warehouse codes and descriptions
		$warehouses = array_map(function($warehouse) {
			return [
				'code' => $warehouse['code'] ?? '',
				'description' => $warehouse['description'] ?? ''
			];
		}, $response['data']);

		// Filter out empty values and sort by code
		$warehouses = array_filter($warehouses, function($warehouse) {
			return !empty($warehouse['code']);
		});
		usort($warehouses, function($a, $b) {
			return strcmp($a['code'], $b['code']);
		});

		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('Spire REST API - Processed Warehouses: ' . print_r($warehouses, true));
		}

		return new \WP_REST_Response([
			'success' => true,
			'data' => array_values($warehouses)
		]);
	}
}
