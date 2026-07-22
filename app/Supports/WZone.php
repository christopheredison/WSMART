<?php

namespace App\Supports;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class WZone
{
    protected $secret = null;
    protected $baseUrl = null;
    protected $client = null;
    
    public function __construct() {
        $this->secret = config('wzone.secret');
        $this->baseUrl = config('wzone.url');
        $this->client = new Client();
    }

    public function apiRequest($method, $url, $body = [], $headers = [], $requestType = 'form_params')
    {
        $client = $this->client;

        $url = $this->baseUrl . trim($url, '/');
        $options = [
            'headers' => $headers,
        ];

        if (strtoupper($method) === 'GET') {
            $requestType = 'query';
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

    public function getStatusLogin($nip)
    {
        $url = 'sso/status_login/' . $nip;
        return $this->apiRequest('GET', $url);
    }

    public function cekValidToken($token)
    {
        $url = '/app/sso/valid';
        $params = [
            'token' => $token,
            'app_secret' => $this->secret,
        ];
        return $this->apiRequest('GET', $url, $params);
    }
}
