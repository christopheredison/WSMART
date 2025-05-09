<?php

namespace App\Supports;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiPP
{
    protected $apiKey = null;
    protected $email = null;
    protected $password = null;
    protected $baseUrl = null;
    protected $client = null;
    protected $authPath = 'api/auth';

    public function __construct() {
        $this->apiKey = config('api_pp.api_key');
        $this->email = config('api_pp.email');
        $this->password = config('api_pp.password');
        $this->baseUrl = trim(config('api_pp.base_url'), '/') . '/';
        $this->client = new Client();
    }

    public function apiRequest($method, $url, $body = [], $headers = [], $requestType = 'json')
    {
        $client = $this->client;

        if ($url !== $this->authPath) {
            $headers['Authorization'] = 'Bearer ' . $this->getApiToken();
        } else {
            $headers['api-key'] = $this->apiKey;
        }

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
            $statusCode = $response ? $response->getStatusCode() : 500;

            if ($statusCode === 401 && $url !== $this->authPath) {
                $this->forgetApiToken();
                return $this->apiRequest($method, $url, $body, $headers, $requestType);
            }

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

    public function getApiToken()
    {
        $token = Cache::rememberForever('api_pp_token', function () {
            $response = $this->apiRequest('POST', $this->authPath, [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($response['status'] ?? false) {
                return $response['data']['token'];
            }

            throw new \Exception('Failed to get API PP token: ' . ($response['message'] ?? ''));
        });

        return $token;
    }

    public function forgetApiToken()
    {
        Cache::forget('api_pp_token');
    }

    public function getProjects()
    {
        return $this->apiRequest('GET', 'v1/project', [
            'display' => 'all',
        ]);
    }
}