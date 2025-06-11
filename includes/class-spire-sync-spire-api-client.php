<?php

namespace SpireSync;

class Spire_Sync_Spire_API_Client {

    protected $base_url;
    protected $company_name;
    protected $username;
    protected $password;

    public function __construct() {
        $options = get_option('spire_sync_settings', []);
        $this->base_url = isset($options['base_url']) ? rtrim($options['base_url'], '/') : '';
        $this->company_name = isset($options['company_name']) ? $options['company_name'] : '';
        $this->username = isset($options['api_username']) ? $options['api_username'] : '';
        $this->password = isset($options['api_password']) ? $options['api_password'] : '';
    }

    protected function format_response($response, $data_key = null) {
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message(),
                'data' => null
            ];
        }

        if (!is_array($response)) {
            return [
                'success' => false,
                'error' => 'Invalid response format',
                'data' => null
            ];
        }

        // Check for API error responses
        if (isset($response['error']) || isset($response['message'])) {
            return [
                'success' => false,
                'error' => $response['error'] ?? $response['message'],
                'data' => null
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'data' => $data_key ? ($response[$data_key] ?? null) : $response
        ];
    }

    /**
     * Constructs and sends an API request.
     *
     * @param string $method   HTTP method (GET, POST, etc.).
     * @param string $endpoint API endpoint relative to the base URL.
     * @param array  $args     Additional arguments.
     * @return array|WP_Error  API response data or a WP_Error on failure.
     */
    public function request($method, $endpoint, $args = [], $data_key = null) {
        $url = $this->base_url . '/' . ltrim($endpoint, '/');
        $default_args = [
            'method'  => strtoupper($method),
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
                'Content-Type'  => 'application/json',
            ],
        ];
        $response = wp_remote_request($url, wp_parse_args($args, $default_args));
        return $this->format_response(json_decode(wp_remote_retrieve_body($response), true), $data_key);
    }

    public function get_instance_info() {
        $response = $this->request('GET', 'instance/info');
        if (!$response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'error' => null,
            'data' => [
                'total_products' => $response['data']['total_products'] ?? 0,
                'total_customers' => $response['data']['total_customers'] ?? 0,
                'companies' => $response['data']['companies'] ?? [],
                'inventory_item_udfs' => $response['data']['inventory_item_udfs'] ?? []
            ]
        ];
    }

    /**
     * Fetches companies from the Spire instance.
     * 
     * @return array
     */
    public function get_companies() {
        return $this->request('GET', 'companies', [], 'companies');
    }

    /**
     * Fetches products from Spire for a given company.
     * 
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_products($query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/inventory/items';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'items');
    }

    public function get_products_count($query_params = []) {
        $response = $this->request('GET', 'companies/' . $this->company_name . '/inventory/items');
        if (!$response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'error' => null,
            'data' => [
                'count' => $response['data']['count'] ?? 0
            ]
        ];
    }

    /**
     * Fetches customers from the Spire instance.
     * 
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_customers($query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/customers';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'customers');
    }

    /**
     * Fetches a specific customer by ID.
     * 
     * @param string $customer_id The ID of the customer to fetch
     * @return array
     */
    public function get_customer($customer_id) {
        return $this->request('GET', 'companies/' . $this->company_name . '/customers/' . $customer_id);
    }

    /**
     * Fetches contacts for a specific customer.
     * 
     * @param string $customer_id The ID of the customer
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_customer_contacts($customer_id, $query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/customers/' . $customer_id . '/contacts';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'contacts');
    }

    /**
     * Fetches inventory items from the Spire instance.
     * 
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_inventory_items($query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/inventory/items';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'items');
    }

    /**
     * Fetches a specific inventory item by ID.
     * 
     * @param string $item_id The ID of the inventory item
     * @return array
     */
    public function get_inventory_item($item_id) {
        return $this->request('GET', 'companies/' . $this->company_name . '/inventory/items/' . $item_id);
    }

    /**
     * Fetches payment methods from the Spire instance.
     * 
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_payment_methods($query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/payment_methods';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'payment_methods');
    }

    /**
     * Fetches sales orders from the Spire instance.
     * 
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_sales_orders($query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/sales/orders';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'orders');
    }

    /**
     * Fetches a specific sales order by ID.
     * 
     * @param string $order_id The ID of the sales order
     * @return array
     */
    public function get_sales_order($order_id) {
        return $this->request('GET', 'companies/' . $this->company_name . '/sales/orders/' . $order_id);
    }

    /**
     * Creates a new sales order.
     * 
     * @param array $order_data The order data to create
     * @return array
     */
    public function create_sales_order($order_data) {
        return $this->request('POST', 'companies/' . $this->company_name . '/sales/orders', [
            'body' => json_encode($order_data)
        ]);
    }

    /**
     * Updates an existing sales order.
     * 
     * @param string $order_id The ID of the sales order to update
     * @param array $order_data The updated order data
     * @return array
     */
    public function update_sales_order($order_id, $order_data) {
        return $this->request('PUT', 'companies/' . $this->company_name . '/sales/orders/' . $order_id, [
            'body' => json_encode($order_data)
        ]);
    }

    /**
     * Fetches shipping methods from the Spire instance.
     * 
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_shipping_methods($query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/shipping_methods';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'shipping_methods');
    }

    /**
     * Fetches territories from the Spire instance.
     * 
     * @param array $query_params Optional query parameters for filtering and pagination
     * @return array
     */
    public function get_territories($query_params = []) {
        $endpoint = 'companies/' . $this->company_name . '/territories';
        
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $endpoint .= '?' . $query_string;
        }

        return $this->request('GET', $endpoint, [], 'territories');
    }

    /**
     * Fetches a specific territory by ID.
     * 
     * @param string $territory_id The ID of the territory
     * @return array
     */
    public function get_territory($territory_id) {
        return $this->request('GET', 'companies/' . $this->company_name . '/territories/' . $territory_id);
    }

    /**
     * Fetches warehouses from the Spire instance.
     * 
     * @return array
     */
    public function get_warehouses() {
        $endpoint = 'companies/' . $this->company_name . '/inventory/warehouses';
        $response = $this->request('GET', $endpoint);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Spire API - Warehouses Response: ' . print_r($response, true));
        }

        if (is_wp_error($response)) {
            return $response;
        }

        // Check if response has records array
        if (!isset($response['records']) || !is_array($response['records'])) {
            return [
                'success' => false,
                'error' => 'Invalid response format: missing records array',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'data' => $response['records']
        ];
    }
}
