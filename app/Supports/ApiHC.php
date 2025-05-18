<?php

namespace App\Supports;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class ApiHC
{
    protected $apiKey = null;
    protected $apiClient = null;
    protected $baseUrl = null;
    protected $client = null;
    
    public function __construct() {
        $this->apiKey = config('api_hc.api_key');
        $this->apiClient = config('api_hc.api_client');
        $this->baseUrl = config('api_hc.base_url');
        $this->client = new Client();
    }

    public function apiRequest($method, $url, $body = [], $headers = [], $requestType = 'json')
    {
        $client = $this->client;

        $url = $this->baseUrl . trim($url, '/');
        $options = [
            'headers' => $headers,
        ];

        if (strtoupper($method) === 'GET') {
            $requestType = 'query';
            $body['key'] = $this->apiKey;
            $body['client'] = $this->apiClient;
        } else {
            $options[RequestOptions::QUERY]['key'] = $this->apiKey;
            $options[RequestOptions::QUERY]['client'] = $this->apiClient;
        }

        switch ($requestType) {
            case 'json':
                $options[RequestOptions::JSON] = $body;
                break;
            case 'form-data':
                $options[RequestOptions::FORM_PARAMS] = $body;
                break;
            case 'query':
                $options[RequestOptions::QUERY] = $body;
                break;
        }

        try {
            $response = $client->request($method, $url, $options);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('API HC Request Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
