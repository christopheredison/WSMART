<?php

namespace App\Supports;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiWika
{
    protected $username = null;
    protected $password = null;
    protected $baseUrl = null;
    protected $client = null;
    protected $authPath = 'api/auth';

    public function __construct() {
        $this->username = config('api_wika.username');
        $this->password = config('api_wika.password');
        $this->baseUrl = 'https://api.wika.co.id/services/wikapis/';
        $this->client = new Client();
    }

    public function apiRequest($method, $url, $body = [], $headers = [], $requestType = 'json')
    {
        $client = $this->client;
        $headers['Authorization'] = 'Basic ' . base64_encode($this->username . ':' . $this->password);

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
            default:
                $options[RequestOptions::QUERY] = $body;
                break;
        }

        try {
            $response = $client->request($method, $url, $options);
            $contents = $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $contents = $response ? $response->getBody()->getContents() : null;
        } catch (\Exception $e) {
            $contents = null;
            Log::error($e->getMessage());
        }

        if (str_contains(implode(' ', $response->getHeader('Content-Type')), 'application/json')) {
            return json_decode($contents, true) ?? $contents;
        }

        return $contents;
    }

    public function getProjects()
    {
        return $this->apiRequest('GET', 'proyek', [
            'period' => '202505',
        ])['data'] ?? [];
    }
}