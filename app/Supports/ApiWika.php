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

        $response = null;
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

        if ($response && str_contains(implode(' ', $response->getHeader('Content-Type')), 'application/json')) {
            return json_decode($contents, true) ?? $contents;
        }

        return $contents;
    }

    public function getProjects()
    {
        $result2 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym', strtotime('-2 month')),
        ]);
        $result2 = collect($result2['data'] ?? [])->keyBy('kode_spk')->toArray();
        $result1 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym', strtotime('-1 month')),
        ]);
        $result1 = collect($result1['data'] ?? [])->keyBy('kode_spk')->toArray();
        $result0 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym'),
        ]);
        $result0 = collect($result0['data'] ?? [])->keyBy('kode_spk')->toArray();

        $result = array_merge($result2, $result1, $result0);

        return array_values($result);
    }

    public function getKPI($period)
    {
        $result = $this->apiRequest('GET', 'scorecard/kpi', [
            'period' => $period,
        ]);
        
        return $result['data'] ?? [];
    }
    
    public function getKPIRev($tahun, $profit_center)
    {
        $result = $this->apiRequest('GET', 'scorecard/target_akhir_tahun/kpi', [
            'tahun' => $tahun,
            'profit_center' => $profit_center,
        ]);
        
        return $result;
    }
}