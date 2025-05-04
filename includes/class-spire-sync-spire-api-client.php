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

    /**
     * Constructs and sends an API request.
     *
     * @param string $method   HTTP method (GET, POST, etc.).
     * @param string $endpoint API endpoint relative to the base URL.
     * @param array  $args     Additional arguments.
     * @return array|WP_Error  API response data or a WP_Error on failure.
     */
    public function request($method, $endpoint, $args = []) {
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
        if (is_wp_error($response)) {
            return $response;
        }
        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function get_instance_info() {
        $total_products = 0;
        $total_customers = 0;
        $companies = [];
        $inventory_item_udfs = [];
    }

    /**
     * Fetches companies from the Spire instance.
     * 
     * @return array|WP_Error
     */
    public function get_companies() {
        return $this->request('GET', 'companies');
    }

    /**
     * Fetches products from Spire for a given company.
     * 
     *
     * @return array|WP_Error
     */
    public function get_products( $query_params = [] ) {
        return $this->request('GET', 'companies/' . $this->company_name . '/inventory/items');
    }

    public function get_products_count( $query_params = [] ) {
        $response = $this->request('GET', 'companies/' . $this->company_name . '/inventory/items');
        $count = isset($response['count']) ? $response['count'] : 0;
        return $count;
    }
}
